<?php
namespace TYPO3\Solrgeo\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Phuong Doan <phuong.doan@dkd.de>, dkd Internet Service GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 *
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class GeoSearchController {

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

	/**
	 * @var \TYPO3\Solrgeo\Utility\Helper
	 */
	protected $helper;

	/**
	 * @var string
	 */
	protected $distance = '5';

	/**
	 * @var string
	 */
	protected $filterType = 'bbox';

	/**
	 * @var string
	 */
	protected $direction = 'asc';

	public function __construct(\tx_solr_Site $site) {
		$this->site = $site;
		$this->connectionManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_solr_ConnectionManager');
		if(!$this->solrAvailable) {
			$this->initializeConfiguration();
		}
		$this->helper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Utility\\Helper');
		$this->setGeosearchConfiguration();
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

	protected function setGeosearchConfiguration() {
		$configuration = $this->helper->getConfiguration('tx_solrgeo');
		$queryConf = $configuration['search.']['query.'];

		// Set distance
		if(!empty($queryConf['filter.']['d'])){
			$this->setDistance($queryConf['filter.']['d']);
		}

		if(!empty($queryConf['filter.']['type'])){
			$this->setFilterType($queryConf['filter.']['type']);
		}


		if(!empty($queryConf['sort.']['direction'])) {
			$this->setSortDirection(strtolower($queryConf['sort.']['direction']));
		}
	}

	/**
	 * @param string Distance
	 */
	protected function setDistance($distance) {
		$this->distance = $distance;
	}

	/**
	 * @param string Filter type: geofilt oder bbox
	 */
	protected function setFilterType($filterType) {
		$this->filterType = $filterType;
	}

	/**
	 * @param string Sort direction: asc or desc
	 */
	protected function setSortDirection($direction) {
		$this->direction = $direction;
	}

	public function getDistance() {
		return $this->distance;
	}

	public function getFilterType() {
		return $this->filterType;
	}

	public function getSortDirection() {
		return $this->direction;
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
	 * @param string Distance
	 * @param string Range interval
	 * @return array Array contains the results
	 */
	public function searchByKeyword($keyword, $distance = '', $range = '') {
		$resultDocuments = array();
		if($keyword == '') {
			$resultDocuments[] = $this->getErrorResult('error_emptyQuery');
		}
		else if ($this->solrAvailable) {
			$geocoder = $this->helper->getGeoCoder();
			$geolocation = $geocoder->getGeolocationFromKeyword($keyword);
			if($geolocation == "-1") {
				$resultDocuments[] = $this->getErrorResult('searchFailed');
			}
			else {
				$resultDocuments = $this->processGeosearch($keyword, $geolocation, $distance, $range);
			}
		}
		else {
			$resultDocuments[] = $this->getErrorResult('searchUnavailable');
		}

		return $resultDocuments;
	}

	/**
	 * Process the geo search
	 *
	 * @param string The search keyword
	 * @param string Data for geo location
	 * @param string Distance
	 * @param string Range interval
	 * @return array Array contains the results
	 */
	private function processGeosearch($keyword, $geolocation, $distance = '', $range = '') {
		$resultDocuments = array();
		$query = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_solr_Query', '');
		$limit = 10;
		if (!empty($this->conf['search.']['results.']['resultsPerPage'])) {
			$limit = $this->conf['search.']['results.']['resultsPerPage'];
		}
		$query->setResultsPerPage($limit);
		$query->setAlternativeQuery('*:*');
		$query->setHighlighting();

		$configuration = $this->helper->getConfiguration('tx_solrgeo');
		$queryConf = $configuration['search.']['query.'];
		if($range != '') {
			$tmp = explode('-',$range);
			$lowerLimit = $tmp[0];
			if(!\TYPO3\Solrgeo\Utility\String::contains($lowerLimit,'.')){
				$lowerLimit = bcadd($lowerLimit, '0.001', 3);
			}
			$upperLimit = $tmp[1];
			$query->addFilter('{!frange l='.$lowerLimit.' u='.$upperLimit.'}geodist()');
			$query->addQueryParameter('sfield', 'geo_location');
			$query->addQueryParameter('pt', $geolocation);
		}
		else {
			if($distance == '') {
				$distance = $this->distance;
			}

			$filterType = $this->filterType;
			$direction = $this->direction;

			$query->addFilter('{!'.$filterType.' pt='.$geolocation.' sfield=geo_location d='.$distance.'}');
			$query->addQueryParameter('sort', 'geodist(geo_location,'.$geolocation.') '.$direction);
		}
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

		if(count($resultDocuments) == 0) {
			$additionInformation = '"'.$keyword.'"';
			if($range != '') {
				$additionInformation .= $GLOBALS['LANG']->sL(
						'LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.within').$range.' km';
			}
			$additionInformation .= '.';
			$resultDocuments[] = $this->getErrorResult('no_results_nothing_found',$additionInformation);
		}
		return $resultDocuments;
	}

	/**
	 * Sets the error.
	 *
	 * @param string The error key defined in /Resources/Private/Language/locallang_search.xml
	 * @param string Additional error information
	 * @return array Array contains the error
	 */
	private function getErrorResult($error_key, $additionalErrorInfo = "") {
		$document = array();
		$document['title'] = $GLOBALS['LANG']->sL(
				'LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.'.$error_key).$additionalErrorInfo;
		$document['content'] = "";
		return $document;
	}
}
?>