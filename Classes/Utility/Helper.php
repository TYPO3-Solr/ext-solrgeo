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

	private $configuration = array();

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
	 * @var TYPO3\Solrgeo\Service\GeoCoderService
	 */
	private $geocoder = NULL;


	public function __construct() {
		$this->setDomain();
		$this->setConfiguration();
		$this->setSolrSite();
		$this->setGeoSearchConfiguration();
	}

	private function setDomain() {
		$sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_pageSelect');
		$rootLine = $sys_page->getRootLine($GLOBALS['TSFE']->id);
		$sys_befunc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_BEfunc');
		$this->domain = $sys_befunc->firstDomainRecord($rootLine);
	}

	public function getDomain() {
		return $this->domain;
	}

	private function setConfiguration() {
		$objectManager =
			\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$configurationManager =
			$objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');

		$this->configuration =
			$configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
	}

	public function getConfiguration($extensionKey) {
		return $this->configuration['plugin.'][$extensionKey.'.'];
	}

	public function setQuery(\Tx_Solr_Query $query) {
		$this->query = $query;
	}

	public function getQuery() {
		return $this->query;
	}

	private function setSolrSite() {
		$sites = \tx_solr_Site::getAvailableSites();
		$site = array_shift($sites);
		$this->site = $site;
	}

	public function getSolrSite() {
		return $this->site;
	}

	private function setGeoSearchConfiguration() {
		$solrGeoConfig = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			'TYPO3\\Solrgeo\\Configuration\\GeoSearchConfiguration',
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

	public function getGeoCoder() {
		return $this->geocoder;
	}

}