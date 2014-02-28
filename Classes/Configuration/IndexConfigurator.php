<?php
namespace TYPO3\Solrgeo\Configuration;

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
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */


class IndexConfigurator {

	/**
	 * The Default Adapter
	 */
	const DEFAULT_ADAPTER = 'CurlHttpAdapter';

	/**
	 * The Default Provider
	 */
	const DEFAULT_PROVIDER = 'GoogleMapsProvider';

	/**
	 * @var \tx_solr_Site
	 */
	protected $site;

	/**
	 * @var array
	 */
	protected $siteConfiguration = array();

	/**
	 * @var array
	 * */
	private $supportedAdapter = array(
		'buzzhttpadapter',
		'curlhttpadapter',
		'guzzlehttpadapter',
		'sockethttpadapter',
		'zendhttpadapter'
	);

	/**
	 * @var array
	 * */
	private $supportedProvider = array(
		'googlemapsprovider',
		'googlemapsbusinessprovider',
		'openstreetmapprovider'
	);

	/**
	 * @var \Geocoder\HttpAdapter\HttpAdapterInterface
	*/
	private $adapter;

	/**
	 * @var \Geocoder\Provider\ProviderInterface
	 */
	private $provider;

	/**
	 * @var array
	 * */
	private $locationList = array();


	/**
	 * @param \tx_solr_Site $site
	 */
	public function __construct(\tx_solr_Site $site) {
		$this->site = $site;
		$this->setAdapter();
		$this->setProvider();
	}

	/**
	 * Sets the configuration of given plugin
	 */
	public function setConfiguration(array $config) {
		$this->siteConfiguration = $config;
	}

	/*
	 * Sets the site configuration for EXT:solrgeo
	 * */
	public function checkSiteConfiguration() {
		if(empty($this->siteConfiguration)) {
			$objectManager =
				\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
			$configurationManager =
				$objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');

			$configuration =
				$configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
			$this->siteConfiguration = $configuration['plugin.']['tx_solrgeo.'];
		}
	}

	/**
	 * Sets the adapter
	 */
	private function setAdapter() {
		// Default Adapter
		$adapterName = self::DEFAULT_ADAPTER;
		$this->checkSiteConfiguration();

		if(isset($this->siteConfiguration['index.']['adapter'])) {
			$adapterName = strtolower($this->siteConfiguration['index.']['adapter']);
			if(!in_array($adapterName, $this->supportedAdapter)) {
				$adapterName = self::DEFAULT_ADAPTER;
			}
		}

		switch ($adapterName) {
			case 'buzzhttpadapter':
				$adapter = new \Geocoder\HttpAdapter\BuzzHttpAdapter();
				break;
			case 'guzzlehttpadapter':
				$adapter = new \Geocoder\HttpAdapter\GuzzleHttpAdapter();
				break;
			case 'sockethttpadapter':
				$adapter = new \Geocoder\HttpAdapter\SocketHttpAdapter();
				break;
			case 'zendhttpadapter':
				$adapter = new \Geocoder\HttpAdapter\ZendHttpAdapter();
				break;
			case 'curlhttpadapter':
			default:
				$adapter  = new \Geocoder\HttpAdapter\CurlHttpAdapter();
				break;
		}

		$this->adapter = $adapter;
	}

	/**
	 *
	 * @return \Geocoder\HttpAdapter\HttpAdapterInterface Returns the Adapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}



	/**
	 * Sets the provider
	 */
	private function setProvider() {
		// Default Provider
		$providerName = self::DEFAULT_PROVIDER;
		$provider = null;
		$this->checkSiteConfiguration();

		if(!empty($this->siteConfiguration['index.']['provider.'])) {
			$providerName = strtolower($this->siteConfiguration['index.']['provider.']['name']);
			if(!in_array($providerName, $this->supportedProvider)) {
				$providerName = self::DEFAULT_PROVIDER;
			}
		}

		$locale = (isset($this->siteConfiguration['index.']['provider.']['locale'])) ?
			$this->siteConfiguration['index.']['provider.']['locale'] : null;
		$region = (isset($this->siteConfiguration['index.']['provider.']['region'])) ?
			$this->siteConfiguration['index.']['provider.']['region'] : null;
		$useSsl = false;
		if(isset($this->siteConfiguration['index.']['provider.']['useSsl'])) {
			if($this->siteConfiguration['index.']['provider.']['useSsl'] == '1') {
				$useSsl = true;
			}
		}
		$clientId = (isset($this->siteConfiguration['index.']['provider.']['clientId'])) ?
			$this->siteConfiguration['index.']['provider.']['clientId'] : null;
		$privateKey = (isset($this->siteConfiguration['index.']['provider.']['privateKey'])) ?
			$this->siteConfiguration['index.']['provider.']['privateKey'] : null;

		$this->provider = $this->createProvider($providerName, $locale, $region, $useSsl, $clientId, $privateKey);
	}

	/**
	 *
	 * @return \Geocoder\Provider\ProviderInterface Returns the Provider
	 */
	public function getProvider() {
		return $this->provider;
	}

	/**
	 * Following providers are currently supported with their optional parameters:
	 * GoogleMapsProvider: locale, region, useSsl
	 * GoogleMapsBusinessProvider: clientId (required), privateKey, locale, region, useSsl
	 * OpenStreetMapProvider: locale
	 *
	 * @param string $providerName
	 * @param string $locale
	 * @param string $region
	 * @param bool $useSsl
	 * @param string $clientId
	 * @param string $privateKey
	 *
	 * @return \Geocoder\Provider\ProviderInterface
	 */
	public function createProvider($providerName, $locale = null, $region = null, $useSsl = false,
								   $clientId = null, $privateKey= null) {
		$provider = null;
		switch ($providerName) {
			case 'openstreetmapprovider';
				$provider = new \Geocoder\Provider\OpenStreetMapProvider($this->getAdapter(), $locale);
				break;
			case 'googlemapsbusinessprovider';
				if($clientId == null || $clientId == '') {
					throw new \InvalidArgumentException(
						'For using GoogleMapsBusinessProvider please ensure that your Client ID is valid (e.g. not null, not empty, ...)',
						1393405937);
				}
				else {
					$provider = new \Geocoder\Provider\GoogleMapsBusinessProvider(
									$this->getAdapter(), $clientId, $privateKey, $locale, $region, $useSsl);
				}
				break;
			case 'googlemapsprovider';
			default:
				$provider = new \Geocoder\Provider\GoogleMapsProvider($this->getAdapter(), $locale, $region, $useSsl);
				break;
		}
		return $provider;
	}

	/**
	 * Save the defined location configured with Typoscript.
	 * Required values are the uid of a page/tca-table and the city.
	 */
	public function setLocationList() {
		if(!empty($this->siteConfiguration['index.']['geocode.'])) {
			$configuredLocations = $this->siteConfiguration['index.']['geocode.'];
			$locationList = array();
			foreach ($configuredLocations as $table => $locations) {
				// per table
				$table = str_replace(".","",$table);
				if($table == 'files' || $table == 'file') {
					$table = 'tx_solr_file';
				}
				foreach ($locations as $location) {
					if(!isset($location['uid'])) {
						throw new \BadMethodCallException(
							'Required field uid for "plugin.tx_solrgeo.index.geocode.'.$table.'" is missing',
							1392978390);
					}
					else if(!isset($location['city'])) {
						throw new \BadMethodCallException(
							'Required field city for "plugin.tx_solrgeo.index.geocode.'.$table.'" is missing',
							1392978390);
					}

					$uidList 		= \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $location['uid']);
					$city 			= trim($location['city']);
					$address 		= (isset($location['address'])) ? trim($location['address']) : '';
					$country 		= (isset($location['country'])) ? trim($location['country']) : '';
					$geolocation 	= (isset($location['geolocation'])) ? trim($location['geolocation']) : '';

					foreach($uidList as $uid) {
						$tmp = array();
						$tmp['type'] = $table;
						$tmp['uid'] = $uid;
						$tmp['city'] = $city;
						$tmp['address'] = $address;
						$tmp['country'] = $country;
						$tmp['geolocation'] = $geolocation;
						$locationList[] = $tmp;
					}
				}
			}
			$this->locationList = $locationList;
		}

	}

	/**
	 * @return array Array contains the defined location to add to Solrdocument
	 */
	public function getLocationList() {
		return $this->locationList;
	}

}