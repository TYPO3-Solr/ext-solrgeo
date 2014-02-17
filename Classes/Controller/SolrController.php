<?php
namespace TYPO3\Solrgeo\Controller;

class SolrController {

	/**
	 * an instance of Tx_solr_Search
	 *
	 * @var \Tx_solr_Search
	 */
	protected $search;

	/**
	 * The plugin's query
	 *
	 * @var \Tx_solr_Query
	 */
	protected $query = NULL;


	/**
	 * The currently selected Site.
	 *
	 * @var \Tx_Solr_Site
	 */
	protected $site;

	/**
	 * A Solr service instance to interact with the Solr server
	 *
	 * @var \Tx_Solr_SolrService
	 */
	protected $solr;

	/**
	 * @var \Tx_Solr_ConnectionManager
	 */
	protected $connectionManager;

	/**
	 * @var array
	 */
	protected $conf = array();

	/**
	 * @var string
	 */
	protected $searchQuery = '';

	/**
	 * @var string
	 */
	const GEO_LOCATION_FIELD = "geo_location";

	/**
	 * @var string
	 */
	const ADDRESS_FIELD = "address_textS";

	/**
	 * Determines whether the solr server is available or not.
	 */
	protected $solrAvailable = false;

	public function __construct(\tx_solr_Site $site) {
		$this->site = $site;
		$this->connectionManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_solr_ConnectionManager');
		if(!$this->solrAvailable) {
			$this->initializeConfiguration();
		}
	}

	/**
	 * Initializes the controller
	 *
	 * @param integer	A page ID.
	 * @param integer The language ID to get the configuration for as the path may differ. Optional, defaults to 0.
	 * @return void
	 */
	public function initialize($pageId = 1, $languageId = 0) {
		$this->connectionManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_solr_ConnectionManager');
		if(!$this->solrAvailable) {
			$this->initializeConfiguration();
			$this->initializeSearch($this->site->getRootPageId(), $this->site->getDefaultLanguage());
		}
	}


	/**
	 * Initializes the Solr connection and tests the connection through a ping. Also gets all the solr cores.
	 *
	 * @param	integer	A page ID.
	 * @param integer The language ID to get the configuration for as the path may differ. Optional, defaults to 0.
	 * @return void
	 */
	protected function initializeSearch($pageId = 1, $languageId = 0) {
		$solr = $this->connectionManager->getConnectionByPageId($pageId,$languageId);
		$this->search = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Solr_Search', $solr);
		$this->solrAvailable = $this->search->ping();
	}

	/**
	 * Initializes the Solr configuration using the page uid 1
	 */
	protected function initializeConfiguration() {
		$this->conf = \Tx_Solr_Util::getSolrConfiguration();
	}


	/**
	 * Search for Solr Document by given UID of page
	 *
	 * @param The uid of a page
	 * @return \Apache_Solr_Document
	 */
	public function search($uid) {
		$solrDocument = null;
		if ($this->solrAvailable) {
			$query = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_solr_Query', '');
			$query->setAlternativeQuery('*:*');
			$query->addFilter('(type:pages AND uid:' . $uid. ')');
			$this->query = $query;
			$this->search->search($this->query, 0, NULL);
			$solrResults = $this->search->getResultDocuments();

			if(count($solrResults) == 1) {
				$solrDocument = $solrResults[0];
			}
		}
		return $solrDocument;
	}

	/**
	 * Checks whether Solr Document has the given field
	 *
	 * @param \Apache_Solr_Document The Solr Document for checking
	 * @param string The name of the field
	 * @return boolean
	 */
	public function solrDocumentHasFieldByName(\Apache_Solr_Document $solrDocument, $fieldName) {
		$hasGeolocationField = false;
		$fields   = $solrDocument->getFieldNames();

		foreach ($fields as $field) {
			if($field == $fieldName) {
				$hasGeolocationField = true;
				break;
			}
		}
		return $hasGeolocationField;
	}

	/**
	 * Update the Solr Document with the address and location values
	 *
	 * @param integer The UID of the page
	 * @param \TYPO3\Solrgeo\Domain\Model\Location The Search Object holds the information about address and Geolocation
	 * @return boolean Returns the Status of Update
	 */
	public function updateSolrDocument($uid, \TYPO3\Solrgeo\Domain\Model\Location $locationObject) {

		$updateSolrDocument = true;
		$address = ($locationObject->getAddress() != "") ?
			$locationObject->getAddress().", ".$locationObject->getCity() : $locationObject->getCity();

		$solrConnections = $this->connectionManager->getAllConnections();
		foreach ($solrConnections as $systemLanguageUid => $solrConnection) {
			$this->initializeSearch($this->site->getRootPageId(), $systemLanguageUid);
			$solrDocument = $this->search($uid);

			if($solrDocument != null) {
				$updateFlag = true;
				if($this->solrDocumentHasFieldByName($solrDocument, self::GEO_LOCATION_FIELD)) {
					$geoField = $solrDocument->getField(self::GEO_LOCATION_FIELD);
					$addressField = $solrDocument->getField(self::ADDRESS_FIELD);
					if( $geoField['value'] == $locationObject->getGeolocation() &&
						$addressField['value'] == $address) {
						$updateFlag = false;
					}
				}
				if($updateFlag) {
					// Prepare Solr Document
					$solrDocument->setField(self::GEO_LOCATION_FIELD, $locationObject->getGeolocation());
					$solrDocument->setField(self::ADDRESS_FIELD, $address);
					// Need to unset this field otherwise the copyfield function adds teaser text as multivalue!
					unset($solrDocument->teaser);
					if(!$this->solrDocumentHasFieldByName($solrDocument, 'appKey')) {
						$solrDocument->setField('appKey', 'EXT:solr');
					}

					// Update the Solr Document
					$response = $solrConnection->addDocument($solrDocument);
					if ($response->getHttpStatus() == 200) {
						$updateSolrDocument = true;
					}
				}
			}
		}

		return $updateSolrDocument;
	}

	public function dumpSolrDocument(\Apache_Solr_Document $solrDocument) {
		$fields   = $solrDocument->getFieldNames();
		$document = array();
		foreach ($fields as $field) {
			$fieldValue       = $solrDocument->getField($field);
			$document[$field] = $fieldValue["value"];
		}
		print_r($document);
	}

	/**
	 * Check the Solr status
	 *
	 * @return boolean Returns TRUE on successful ping.
	 */
	public function getSolrstatus() {
		return $this->solrAvailable;
	}

	/**
	 * Search by the given keyword
	 *
	 * @param string The search keyword
	 * @return array Array contains the results
	 */
	public function searchByKeyword($keyword) {
		$resultDocuments = array();
		if ($this->solrAvailable && $keyword != '') {
			$helper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Utility\\Helper');
			$geocoder = $helper->getGeoCoder();
			$geolocation = $geocoder->getGeolocationFromKeyword($keyword);
			if($geolocation != '') {

				$query = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_solr_Query', '');
				$limit = 10;
				if (!empty($this->conf['search.']['results.']['resultsPerPage'])) {
					$limit = $this->conf['search.']['results.']['resultsPerPage'];
				}
				$query->setResultsPerPage($limit);

				$configuration = $helper->getConfiguration('tx_solrgeo');
				$filters = $configuration['search.']['filter.'];
				$distance = '5';
				$filterType = 'bbox';
				if(!empty($filters)){
					$distance = $filters['d'];
					$filterType = $filters['type'];
				}

				$query->setAlternativeQuery('*:*');
				$query->setHighlighting();

				// query string for spartialSearch
				// {!bbox pt=50.1109221,8.6821267 sfield=geo_location d=5}
				$query->addFilter('{!'.$filterType.' pt='.$geolocation.' sfield=geo_location d='.$distance.'}');

				$this->query = $query;

				$offSet = 0;
				$this->search->search($this->query, $offSet, NULL);
				$solrResults = $this->search->getResultDocuments();
				foreach ($solrResults as $result) {
					$fields   = $result->getFieldNames();
					$document = array();
					foreach ($fields as $field) {
						$fieldValue       = $result->getField($field);
						$document[$field] = $fieldValue["value"];
					}
					$resultDocuments[] = $document;
				}

			}
		}
		return $resultDocuments;
	}
}
?>