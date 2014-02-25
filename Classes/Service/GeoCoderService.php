<?php
namespace TYPO3\Solrgeo\Service;

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
 * GeoCoder Service
 *
 * @package	solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GeoCoderService extends \Geocoder\Geocoder{

	/**
	 * @var integer
	 */
	const MAX_RESULTS = 5;

	/**
	 * @var \Geocoder\Result\ResultInterface
	 */
	private $georesult = NULL;

	/**
	 * @var array
	 * */
	private $locationList = array();

	public function __construct(\Geocoder\Provider\ProviderInterface $provider = null,
								\Geocoder\Result\ResultFactoryInterface $resultFactory = null,
								$maxResults = self::MAX_RESULTS) {
		parent::__construct($provider, $resultFactory, $maxResults);
	}

	/**
	 *
	 * * @param array $location holds the defined location to add to a Solr document
	 */
	public function setLocationList(array $location) {
		$this->locationList = $location;
	}


	public function processGeocoding(\tx_solr_Site $site) {
		if(!empty($this->locationList)) {
			$locationRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Domain\\Repository\\LocationRepository');
			$locationRepository->initializeObject();
			$persistenceManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
			foreach($this->locationList as $location){
				$locationObject = null;
				$result = $locationRepository->findByConfiguredLocation($location);
				$this->setGeoResult($location['address'],$location['city'],$location['country']);
				if($result->count() == 0) {
					$geolocation = $this->getGeolocation($location['geolocation']);
					// ensure that the record is unique: second search by geolocation
					$resultGeoLocation = $locationRepository->findByGeoLocation($geolocation);
					$locationObject = $locationRepository->createLocation($location);
					$locationObject = $this->fillLocation($locationObject, $geolocation);
					if($resultGeoLocation->count() == 0) {
						// Create new location object
						$locationRepository->add($locationObject);
						$persistenceManager->persistAll();
					}
				}
				else {
					// Update the location object with geolocation data
					$locationObject = $result->getFirst();
					if($locationObject->getGeolocation() == '') {
						$locationObject = $this->fillLocation($locationObject, $this->getGeolocation($location['geolocation']));
						$locationRepository->update($locationObject);
					}
				}

				if($locationObject != null) {
					$solrController = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Controller\\BackendGeoSearchController', $site);
					$solrController->updateSolrDocument($location['type'], $location['uid'],$locationObject);
				}
			}
		}
	}

	public function fillLocation($locationObject, $geolocation) {
		$locationObject->setGeolocation($geolocation);
		// For a uniform notation
		$locationObject->setCity($this->georesult->getCity());
		$locationObject->setCountry($this->georesult->getCountry());
		$locationObject->setRegion($this->georesult->getRegion());
		return $locationObject;
	}

	/**
	 *
	 * @param string Address
	 * @param string City
	 */
	public function setGeoResult($address, $city,$country) {
		$this->georesult = $this->geocode($address.','.$city,','.$country);
	}

	/**
	 *
	 * @param string latitude and longitude
	 * @return string Modified latitude and longitude as string, comma separated
	 */
	public function getGeolocation($configuredGeolocation) {
		if($configuredGeolocation == '') {
			$geolocation = $this->georesult->getLatitude().','.$this->georesult->getLongitude();
		}
		else {
			$geolocation = str_replace(' ','',$configuredGeolocation);
		}
		return $geolocation;
	}

	/**
	 *
	 * @param string The search keyword for geocoding
	 * @return string the latitude and longitude as string, comma separated
	 */
	public function getGeolocationFromKeyword($keyword) {
		try {
			$geocode = $this->geocode($keyword);
			$geocoded = $geocode->getLatitude().','.$geocode->getLongitude();
		} catch (\Geocoder\Exception\ExceptionInterface $e) {
			//$geocode = $e->getMessage();
			$geocoded = "-1";
		}

		return $geocoded;
	}


}