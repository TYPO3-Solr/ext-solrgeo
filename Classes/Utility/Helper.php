<?php
namespace ApacheSolrForTypo3\Solrgeo\Utility;

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

use ApacheSolrForTypo3\Solr\Query;
use ApacheSolrForTypo3\Solr\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;


/**
 *
 *
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Helper {

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @var string
	 */
	protected $domain = '';

	/**
	 * The search query
	 *
	 * @var Query
	 */
	protected $query = NULL;

	/**
	 * The Site
	 *
	 * @var Site
	 */
	protected $site = NULL;

	/**
	 * @var \ApacheSolrForTypo3\Solrgeo\Service\GeoCoderService
	 */
	protected $geocoder = NULL;


	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->initializeDomain();
		$this->initializeConfiguration();
		$this->initializeSolrSite();
		$this->initializeGeoCoderService();
	}

	/**
	 * Initializes the domain
	 *
	 */
	protected function initializeDomain() {
		$sys_page = GeneralUtility::makeInstance('t3lib_pageSelect');
		$rootLine = $sys_page->getRootLine($GLOBALS['TSFE']->id);
		$sys_befunc = GeneralUtility::makeInstance('t3lib_BEfunc');

		$this->domain = $sys_befunc->firstDomainRecord($rootLine);
	}

	/**
	 * Returns the domain without http-prefix
	 *
	 * @return string
	 */
	public function getDomain() {
		if ($this->domain == '') {
			$this->initializeDomain();
		}

		return $this->domain;
	}

	/**
	 * Initializes the TS Configuration
	 *
	 */
	protected function initializeConfiguration() {
		$objectManager        = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');

		$this->configuration = $configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);
	}

	/**
	 * Returns the TS configuration for a given extension key
	 *
	 * @param string $extensionKey
	 * @return array
	 */
	public function getConfiguration($extensionKey) {
		if(empty($this->configuration)) {
			$this->initializeConfiguration();
		}

		return $this->configuration['plugin.'][$extensionKey.'.'];
	}

	/**
	 * @param Query $query
	 */
	public function setQuery(Query $query) {
		$this->query = $query;
	}

	/**
	 * @return Query
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Sets the Solr Site
	 */
	protected function initializeSolrSite() {
		$sites = Site::getAvailableSites();
		$site = array_shift($sites);

		$this->site = $site;
	}

	/**
	 * @return Site
	 */
	public function getSolrSite() {
		return $this->site;
	}

	/**
	 *  Prepare same values for the geocoding process
	 */
	protected function initializeGeoCoderService() {
		$solrGeoConfig = GeneralUtility::makeInstance(
			'ApacheSolrForTypo3\\Solrgeo\\Configuration\\IndexConfigurator',
			$this->getSolrSite()
		);
		$solrGeoConfig->setConfiguration($this->getConfiguration('tx_solrgeo'));
		$solrGeoConfig->setLocationList();

		$this->locationCount = count($solrGeoConfig->getLocationList());

		$geoCoder = GeneralUtility::makeInstance(
			'ApacheSolrForTypo3\\Solrgeo\\Service\\GeoCoderService',
			$solrGeoConfig->getProvider()
		);
		$geoCoder->setLocationList($solrGeoConfig->getLocationList());

		$this->geocoder = $geoCoder;
	}

	/**
	 *
	 * @return \ApacheSolrForTypo3\Solrgeo\Service\GeoCoderService
	 */
	public function getGeoCoder() {
		return $this->geocoder;
	}

}
