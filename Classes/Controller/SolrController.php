<?php
namespace ApacheSolrForTypo3\Solrgeo\Controller;

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

use ApacheSolrForTypo3\Solr\Site;
use ApacheSolrForTypo3\Solr\Util;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 *
 *
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class SolrController {

	/**
	 * An instance of ApacheSolrForTypo3\Solr\Search
	 *
	 * @var \ApacheSolrForTypo3\Solr\Search
	 */
	protected $search;

	/**
	 * The plugin's query
	 *
	 * @var \ApacheSolrForTypo3\Solr\Query
	 */
	protected $query = NULL;

	/**
	 * The currently selected Site.
	 *
	 * @var Site
	 */
	protected $site;

	/**
	 * A Solr service instance to interact with the Solr server
	 *
	 * @var \ApacheSolrForTypo3\Solr\SolrService
	 */
	protected $solr;

	/**
	 * @var \ApacheSolrForTypo3\Solr\ConnectionManager
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
	 * @var string
	 */
	const CITY_FIELD = "city_textS";

	/**
	 * @var string
	 */
	const COUNTRY_FIELD = "country_textS";

	/**
	 * @var string
	 */
	const REGION_FIELD = "region_textS";

	/**
	 * Determines whether the solr server is available or not.
	 */
	protected $solrAvailable = false;

	/**
	 * @var \ApacheSolrForTypo3\Solrgeo\Utility\Helper
	 */
	protected $helper = NULL;


	/**
	 * @param Site $site
	 */
	public function __construct(Site $site) {
		$this->site = $site;
		$this->connectionManager = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solr\\ConnectionManager');

		if (!$this->solrAvailable) {
			$this->initializeConfiguration();
		}

		$this->helper = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solrgeo\\Utility\\Helper');
	}

	/**
	 * Initializes the controller
	 *
	 * @return void
	 */
	public function initialize() {
		// FIXME why is there another initialization besides the constructor?
		// also doing almost the same??
		$this->connectionManager = GeneralUtility::makeInstance('ApacheSolrForTypo3\Solr\ConnectionManager');

		if (!$this->solrAvailable) {
			$this->initializeConfiguration();
			$this->initializeSearch($this->site->getRootPageId(), $this->site->getDefaultLanguage());
		}
	}


	/**
	 * Initializes the Solr connection and tests the connection through a ping. Also gets all the solr cores.
	 *
	 * @param integer $pageId A page ID.
	 * @param integer $languageId The language ID to get the configuration for as the path may differ. Optional, defaults to 0.
	 * @return void
	 */
	protected function initializeSearch($pageId = 1, $languageId = 0) {
		$solr = $this->connectionManager->getConnectionByPageId($pageId,$languageId);
		$this->search = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solrgeo\\Search\\GeoSearch', $solr);
		// FIXME must not/should not set the connection this way, check EXT:solr
		$this->search->setSolrConnection($solr);
		$this->solrAvailable = $this->search->ping();
	}

	/**
	 * Initializes the Solr configuration using the page uid 1
	 */
	protected function initializeConfiguration() {
		$this->conf = Util::getSolrConfiguration();
	}


	/**
	 * Checks whether Solr Document has the given field
	 *
	 * @param \Apache_Solr_Document The Solr Document for checking
	 * @param string $fieldName The name of the field
	 * @return boolean
	 */
	public function solrDocumentHasFieldByName(\Apache_Solr_Document $solrDocument, $fieldName) {
		$hasField = false;
		$fields   = $solrDocument->getFieldNames();

		foreach ($fields as $field) {
			if ($field == $fieldName) {
				$hasField = true;
				break;
			}
		}

		return $hasField;
	}


	/**
	 * Dumps the Solr Document for debugging
	 *
	 * @param \Apache_Solr_Document
	 * @return void
	 */
	public function dumpSolrDocument(\Apache_Solr_Document $solrDocument) {
		$fields   = $solrDocument->getFieldNames();
		$document = array();

		foreach ($fields as $field) {
			$fieldValue       = $solrDocument->getField($field);
			$document[$field] = $fieldValue['value'];
		}

		var_dump($document);
	}

	/**
	 * Check the Solr status
	 *
	 * @return boolean Returns TRUE on successful ping.
	 */
	public function getSolrStatus() {
		return $this->solrAvailable;
	}


	/**
	 * Search for Solr Document by given UID of page
	 *
	 * @param \ApacheSolrForTypo3\Solr\SolrService
	 * @param string $type The type of a solr document
	 * @param string $uid The uid of the type in TYPO3
	 * @return array contains \Apache_Solr_Document
	 */
	public function search(\ApacheSolrForTypo3\Solr\SolrService $solrConnection, $type, $uid) {
		$solrResults = array();
		$search = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solrgeo\\Search\\GeoSearch', $solrConnection);
		// FIXME must not/should not set the connection this way, check EXT:solr
		$search->setSolrConnection($solrConnection);

		if ($search->ping()) {
			$query = $this->getDefaultQuery();
			$queryUid = ($type == 'tx_solr_file') ? 'fileReferenceUid' : 'uid';
			$query->addFilter('(type:'.$type.' AND '.$queryUid.':' . $uid.')');
			$search->search($query, 0, NULL);
			$solrResults = $search->getResultDocuments();
		}

		return $solrResults;
	}

	/**
	 * Get the Query instance with default parameters
	 *
	 * @return \ApacheSolrForTypo3\Solr\Query
	 */
	public function getDefaultQuery() {
		$query = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solr\\Query', '');
		$query->setAlternativeQuery('*:*');
		$query->setSiteHashFilter($this->site->getDomain());

		return $query;
	}
}

