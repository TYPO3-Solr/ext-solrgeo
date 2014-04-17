<?php
namespace TYPO3\Solrgeo\Utility;

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
class Helper {

	/**
	 * @var array
	 */
	private $configuration = array();

	/**
	 * @var string
	 */
	private $domain = "";

	/**
	 * The search query
	 *
	 * @var \Tx_Solr_Query
	 */
	private $query = NULL;

	/**
	 * The Site
	 *
	 * @var \Tx_Solr_Site
	 */
	private $site = NULL;

	/**
	 * @var \TYPO3\Solrgeo\Service\GeoCoderService
	 */
	private $geocoder = NULL;


	public function __construct() {
		$this->setDomain();
		$this->setConfiguration();
		$this->setSolrSite();
		$this->prepareForGeocoderService();
	}

	/**
	 * Sets the domain
	 */
	private function setDomain() {
		$sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_pageSelect');
		$rootLine = $sys_page->getRootLine($GLOBALS['TSFE']->id);
		$sys_befunc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_BEfunc');
		$this->domain = $sys_befunc->firstDomainRecord($rootLine);
	}

	/**
	 * Returns the domain without http-prefix
	 *
	 * @return string
	 */
	public function getDomain() {
		if($this->domain == '') {
			$this->setDomain();
		}
		return $this->domain;
	}

	/**
	 * Sets the TS Configuration
	 */
	public function setConfiguration() {
		$objectManager =
			\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$configurationManager =
			$objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');

		$this->configuration =
			$configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
	}

	/**
	 * Returns the TS configuration from given extension key
	 *
	 * @param string $extensionKey
	 * @return array
	 */
	public function getConfiguration($extensionKey) {
		if(empty($this->configuration)) {
			$this->setConfiguration();
		}
		return $this->configuration['plugin.'][$extensionKey.'.'];
	}

	/**
	 * @param \Tx_Solr_Query $query
	 */
	public function setQuery(\Tx_Solr_Query $query) {
		$this->query = $query;
	}

	/**
	 * @return \Tx_Solr_Query
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Sets the Solr Site
	 */
	private function setSolrSite() {
		$sites = \Tx_solr_Site::getAvailableSites();
		$site = array_shift($sites);
		$this->site = $site;
	}

	/**
	 * @return \Tx_Solr_Site
	 */
	public function getSolrSite() {
		return $this->site;
	}

	/**
	 *  Prepare same values for the geocoding process
	 */
	private function prepareForGeocoderService() {
		$solrGeoConfig = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			'TYPO3\\Solrgeo\\Configuration\\IndexConfigurator',
			$this->getSolrSite());
		$solrGeoConfig->setConfiguration($this->getConfiguration('tx_solrgeo'));
		$solrGeoConfig->setLocationList();

		$this->locationCount = count($solrGeoConfig->getLocationList());

		$geocoder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			'TYPO3\\Solrgeo\\Service\\GeoCoderService',
			$solrGeoConfig->getProvider());

		$geocoder->setLocationList($solrGeoConfig->getLocationList());

		$this->geocoder = $geocoder;
	}

	/**
	 *
	 * @return \TYPO3\Solrgeo\Service\GeoCoderService
	 */
	public function getGeoCoder() {
		return $this->geocoder;
	}

}