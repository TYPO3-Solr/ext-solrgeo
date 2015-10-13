<?php
namespace ApacheSolrForTypo3\Solrgeo\Service;

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
use Geocoder\Exception\ExceptionInterface;
use Geocoder\Geocoder;
use Geocoder\Provider\ProviderInterface;
use Geocoder\Result\ResultFactoryInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use ApacheSolrForTypo3\Solrgeo\Domain\Model\Location;


/**
 * GeoCoder Service
 *
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GeoCoderService extends Geocoder{

	/**
	 * @var integer
	 */
	const MAX_RESULTS = 5;

	/**
	 * @var \Geocoder\Result\ResultInterface
	 */
	protected $geoResult = NULL;

	/**
	 * @var array
	 * */
	protected $locationList = array();


	/**
	 * Constructor
	 *
	 * @param ProviderInterface $provider
	 * @param ResultFactoryInterface $resultFactory
	 * @param int $maxResults
	 */
	public function __construct(ProviderInterface $provider = null,
								ResultFactoryInterface $resultFactory = null,
								$maxResults = self::MAX_RESULTS) {
		parent::__construct($provider, $resultFactory, $maxResults);
	}

	/**
	 *
	 * @param array $locations holds the defined location to add to a Solr document
	 */
	public function setLocationList(array $locations) {
		$this->locationList = $locations;
	}

	/**
	 * @param Site $site
	 * @throws \RuntimeException
	 */
	public function processGeoCoding(Site $site) {
		if(!empty($this->locationList)) {
			/** @var $objectManager \TYPO3\CMS\Extbase\Object\ObjectManager */
			$objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
			/** @var $locationRepository \ApacheSolrForTypo3\Solrgeo\Domain\Repository\LocationRepository' */
			$locationRepository = $objectManager->get('ApacheSolrForTypo3\\Solrgeo\\Domain\\Repository\\LocationRepository');
			$locationRepository->initializeObject();
			$persistenceManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');

			foreach($this->locationList as $location){
				$locationObject = null;
				$result = $locationRepository->findByConfiguredLocation($location);
				$this->setGeoResult($location['address'], $location['city'], $location['country']);

				if ($result->count() == 0) {
					$geoLocation = $this->getGeoLocation($location['geolocation']);

					// ensure that the record is unique: second search by geolocation
					$resultGeoLocation = $locationRepository->findByGeoLocation($geoLocation);
					$locationObject = $locationRepository->createLocation($location);
					$locationObject = $this->fillLocation($locationObject, $geoLocation);

					if ($resultGeoLocation->count() == 0) {
						// Create new location object
						$locationRepository->add($locationObject);
						$persistenceManager->persistAll();
					}
				} else {
					// Update the location object with geo location data
					$locationObject = $result->getFirst();

					if($locationObject->getGeolocation() == '') {
						$locationObject = $this->fillLocation(
							$locationObject,
							$this->getGeoLocation($location['geolocation'])
						);
						$locationRepository->update($locationObject);
					}
				}

				if($locationObject != null) {
					$solrController = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Solrgeo\\Controller\\BackendGeoSearchController', $site);
					$updateStatus = $solrController->updateSolrDocument($location['type'], $location['uid'], $locationObject);

					if (!$updateStatus) {
						throw new \RuntimeException(
							'Could not update Solr Document for ' . $location['type'] . ' with uid ' . $location['uid'] . '.
								For further information please see in devlog or tomcat log.',
							1393586058
						);
					}
				}
			}
		}
	}

	/**
	 *
	 * @param \ApacheSolrForTypo3\Solrgeo\Domain\Model\Location $locationObject
	 * @param string $geoLocation
	 * @return \ApacheSolrForTypo3\Solrgeo\Domain\Model\Location
	 */
	public function fillLocation(Location $locationObject, $geoLocation) {
		$locationObject->setGeolocation($geoLocation);

		// For a uniform notation
		$locationObject->setCity($this->geoResult->getCity());
		$locationObject->setCountry($this->geoResult->getCountry());
		$locationObject->setRegion($this->geoResult->getRegion());

		return $locationObject;
	}

	/**
	 *
	 * @param string $address Address
	 * @param string $city City
	 * @param string $country Country
	 */
	public function setGeoResult($address, $city, $country) {
		$this->geoResult = $this->geocode($address . ',' . $city, ',' . $country);
	}

	/**
	 *
	 * @param string $configuredGeoLocation Latitude and longitude
	 * @return string Modified latitude and longitude as string, comma separated
	 */
	public function getGeoLocation($configuredGeoLocation) {
		if ($configuredGeoLocation == '') {
			$geoLocation = $this->geoResult->getLatitude() . ',' . $this->geoResult->getLongitude();
		} else {
			$geoLocation = str_replace(' ', '', $configuredGeoLocation);
		}

		return $geoLocation;
	}

	/**
	 *
	 * @param string $keyword The search keyword for geo-coding
	 * @return string the latitude and longitude as string, comma separated
	 */
	public function getGeoLocationFromKeyword($keyword) {
		try {
			$geoCode  = $this->geocode($keyword);
			$geoCoded = $geoCode->getLatitude() . ',' . $geoCode->getLongitude();
		} catch (ExceptionInterface $e) {
			$geoCoded = '-1';
		}

		return $geoCoded;
	}


}
