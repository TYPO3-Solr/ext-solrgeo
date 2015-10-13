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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\Solrgeo\Utility\String;


/**
 *
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FrontendGeoSearchController extends SolrController {

	/**
	 * @var \TYPO3\Solrgeo\Configuration\GeoSearchConfiguration
	 */
	protected $geoSearchObject = null;

	/**
	 * @var string
	 */
	protected $geolocation = '-1';

	/**
	 * @var boolean
	 */
	protected $searchHasResults = false;

	/**
	 * @var \TYPO3\Solrgeo\Utility\Helper
	 */
	protected $helper;


	/**
	 * @param \TYPO3\Solrgeo\Utility\Helper $helper
	 */
	public function setHelper($helper) {
		$this->helper = $helper;
	}

	public function initializeGeoSearchConfiguration() {
		$this->createGeoSearchObject();
	}

	/**
	 * @param \TYPO3\Solrgeo\Configuration\GeoSearchConfiguration $geoSearchObject
	 */
	public function setGeoSearchObject($geoSearchObject) {
		$this->geoSearchObject = $geoSearchObject;
	}

	/**
	 * @return \TYPO3\Solrgeo\Configuration\GeoSearchConfiguration
	 */
	public function getGeoSearchObject() {
		return $this->geoSearchObject;
	}

	/**
	 * Gets the geo location
	 *
	 * @return string the latitude and longitude as string, comma separated
	 */
	public function getGeoLocation() {
		return $this->geolocation;
	}

	/**
	 * Sets the geolocation from given search keyword
	 *
	 * @return string the latitude and longitude as string, comma separated
	 */
	public function setGeoLocation($keyword) {
		$geocoder = $this->helper->getGeoCoder();
		$this->geolocation = $geocoder->getGeoLocationFromKeyword($keyword);
	}

	/**
	 * @param boolean $hasResults
	 */
	public function setSearchHasResults($hasResults) {
		$this->searchHasResults = $hasResults;
	}

	/**
	 * @return bool
	 */
	public function getSearchHasResults() {
		return $this->searchHasResults;
	}

	/**
	 * Sets the configured filter type, sort direction and distance for query / facets
	 *
	 * @return void
	 */
	protected function createGeoSearchObject() {
		$this->geoSearchObject = GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Configuration\\GeoSearchConfiguration');
		$configuration         = $this->helper->getConfiguration('tx_solrgeo');
		$searchConf            = $configuration['search.'];

		// query configuration
		if (!empty($searchConf['query.']['filter.']['d'])){
			$this->geoSearchObject->setDistance($searchConf['query.']['filter.']['d']);
		}

		if (!empty($searchConf['query.']['filter.']['type'])){
			$this->geoSearchObject->setFilterType($searchConf['query.']['filter.']['type']);
		}

		if (!empty($searchConf['query.']['sort.']['direction'])) {
			$this->geoSearchObject->setSortDirection(strtolower($searchConf['query.']['sort.']['direction']));
		}

		if (!empty($searchConf['faceting.']['distance']) && $searchConf['faceting.']['distance'] == '1') {
			$this->geoSearchObject->setDistanceFilterEnable(true);
		}

		// facet.city configuration
		if (!empty($searchConf['faceting.']['city']) && $searchConf['faceting.']['city'] == '1') {
			$this->geoSearchObject->setCityFacetEnable(true);
		}

		if (!empty($searchConf['faceting.']['city.']['sort.']['direction'])) {
			$this->geoSearchObject->setFacetCitySortDirection(strtolower($searchConf['faceting.']['city.']['sort.']['direction']));
		}

		if (!empty($searchConf['faceting.']['city.']['sort.']['type'])) {
			$this->geoSearchObject->setFacetCitySortType(strtolower($searchConf['faceting.']['city.']['sort.']['type']));
		}

		// facet.country configuration
		if (!empty($searchConf['faceting.']['country']) && $searchConf['faceting.']['country'] == '1') {
			$this->geoSearchObject->setCountryFacetEnable(true);
		}

		if (!empty($searchConf['faceting.']['country.']['sort.']['direction'])) {
			$this->geoSearchObject->setFacetCountrySortDirection(strtolower($searchConf['faceting.']['country.']['sort.']['direction']));
		}

		if (!empty($searchConf['faceting.']['country.']['sort.']['type'])) {
			$this->geoSearchObject->setFacetCountrySortType(strtolower($searchConf['faceting.']['country.']['sort.']['type']));
		}

		if (!empty($searchConf['faceting.']['distance.']['ranges.'])) {
			$this->geoSearchObject->setConfiguredRanges($searchConf['faceting.']['distance.']['ranges.']);
		}

		// Zoom for google maps
		if (!empty($searchConf['maps.']['zoom.']['city'])) {
			$this->geoSearchObject->setCityZoom($searchConf['maps.']['zoom.']['city']);
		}

		if (!empty($searchConf['maps.']['zoom.']['country'])) {
			$this->geoSearchObject->setCountryZoom($searchConf['maps.']['zoom.']['country']);
		}
	}


	/**
	 * Search by the given keyword
	 *
	 * @param string $keyword The search keyword
	 * @param string $distance Distance
	 * @param string $range Range interval
	 * @return array Array contains the results
	 */
	public function searchByKeyword($keyword, $distance = '', $range = '') {
		$resultDocuments = array();

		if ($keyword == '') {
			$resultDocuments[] = $this->getErrorResult('error_emptyQuery');
		} elseif ($this->solrAvailable) {
			$geoLocation = $this->getGeoLocation();

			if ($geoLocation == '-1') {
				$resultDocuments[] = $this->getErrorResult('searchFailed');
			} else {
				$resultDocuments = $this->processGeoSearch($keyword, $geoLocation, $distance, $range);
			}
		} else {
			$resultDocuments[] = $this->getErrorResult('searchUnavailable');
		}

		return $resultDocuments;
	}


	/**
	 * Process the geo search
	 *
	 * @param string $keyword The search keyword
	 * @param string $geoLocation Data for geo location
	 * @param string $distance Distance
	 * @param string $range Range interval
	 * @return array Array contains the results
	 */
	protected function processGeoSearch($keyword, $geoLocation, $distance = '', $range = '') {
		$resultDocuments = array();
		$query           = $this->getDefaultQuery();
		$limit           = 10;

		if (!empty($this->conf['search.']['results.']['resultsPerPage'])) {
			$limit = $this->conf['search.']['results.']['resultsPerPage'];
		}

		$query->setResultsPerPage($limit);
		$query->setHighlighting();

		$query = $this->modifyQuery($query, $keyword, $geoLocation, $distance, $range);
		$this->query = $query;

		$this->search->search($this->query, 0, NULL);
		$solrResults = $this->search->getResultDocuments();

		$settings = $this->helper->getConfiguration('tx_solrgeo');

		if (!empty($solrResults)) {
			$this->setSearchHasResults(true);

			foreach ($solrResults as $result) {
				$fields   = $result->getFieldNames();
				$document = array();

				if (
					!$this->geoSearchObject->isSearchByCountry() ||
					($this->geoSearchObject->isSearchByCountry() && in_array(self::COUNTRY_FIELD, $fields)))
				{

					foreach ($fields as $field) {
						$fieldValue       = $result->getField($field);
						$document[$field] = $fieldValue["value"];
					}

					// modify results for FE
					$cropViewHelper = GeneralUtility::makeInstance('ApacheSolrForTypo3\Solr\ViewHelper\Crop');
					$cropMaxLength  = (isset($settings['search.']['results.']['crop.']['maxLength'])) ?  $settings['search.']['results.']['crop.']['maxLength'] : 200;
					$cropIndicator  = (isset($settings['search.']['results.']['crop.']['indicator'])) ?  $settings['search.']['results.']['crop.']['indicator'] : '...';
					$cropFullWords  = (isset($settings['search.']['results.']['crop.']['fullWords'])) ?  $settings['search.']['results.']['crop.']['fullWords'] : 1;
					$contentArray   = array($document['content'], $cropMaxLength, $cropIndicator, $cropFullWords);

					$address = $document['city_textS'];
					if($document['address_textS'] != '') {
						$address = $document['address_textS'].', '.$document['city_textS'];
					}

					$document['address'] = $address;
					$document['cropped_content'] = $cropViewHelper->execute($contentArray);
					$resultDocuments[] = $document;
				}
			}
		} else {
			$this->setSearchHasResults(false);
			$additionInformation = '"'.$keyword.'"';

			if ($range != '') {
				$additionInformation .= $GLOBALS['TSFE']->sL(
					'LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.within'
				) . $range . ' km';
			}

			$additionInformation .= '.';
			$resultDocuments[] = $this->getErrorResult('no_results_nothing_found',$additionInformation);
		}

		return $resultDocuments;
	}


	/**
	 * Modifies the Query depends on keyword, range or distance
	 *
	 * @param \ApacheSolrForTypo3\Solr\Query $query
	 * @param string $keyword
	 * @param string $geoLocation
	 * @param string $distance
	 * @param string $range
	 * @return \ApacheSolrForTypo3\Solr\Query
	 */
	public function modifyQuery(\ApacheSolrForTypo3\Solr\Query $query, $keyword, $geoLocation, $distance, $range) {
		if (GeneralUtility::isFirstPartOfStr($keyword, 'country,')) {
			$this->geoSearchObject->setSearchByCountry(true);
			$keyword = str_replace('country,', '', $keyword);
			$query->setQueryField(self::COUNTRY_FIELD, 1.0);
			$query->setKeywords($keyword);
		} elseif ($range != '') {
			$tmp = explode('-',$range);
			$lowerLimit = $tmp[0];

			if ($lowerLimit != '0' && !String::contains($lowerLimit, '.')){
				$lowerLimit = bcadd($lowerLimit, '0.001', 3);
			}

			$upperLimit = $tmp[1];
			$query->addFilter('{!frange l='.$lowerLimit . ' u=' . $upperLimit . '}geodist()');
			$query->addQueryParameter('sfield', self::GEO_LOCATION_FIELD);
			$query->addQueryParameter('pt', $geoLocation);
		} else {
			if ($distance == '') {
				$distance = $this->geoSearchObject->getDistance();
			}

			$query->addFilter('{!' . $this->geoSearchObject->getFilterType()
				. ' pt=' . $geoLocation
				. ' sfield=' . self::GEO_LOCATION_FIELD
				. ' d=' . $distance . '}'
			);
			$query->addQueryParameter(
				'sort',
				'geodist(' . self::GEO_LOCATION_FIELD . ',' . $geoLocation . ') '
					. $this->geoSearchObject->getDirection()
			);
		}

		return $query;
	}


	/**
	 * Sets the error.
	 *
	 * @param string $errorKey The error key defined in /Resources/Private/Language/locallang_search.xml
	 * @param string $additionalErrorInfo Additional error information
	 * @return array Array contains the error
	 */
	protected function getErrorResult($errorKey, $additionalErrorInfo = '') {
		$document = array();
		$document['title'] = $GLOBALS['TSFE']->sL(
			'LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.' . $errorKey
		) . $additionalErrorInfo;
		$document['content'] = '';

		return $document;
	}

	/**
	 * @param string $keyword The search keyword
	 * @param string $facetType
	 * @param string $language
	 * @return array
	 */
	public function getFacetGrouping($keyword, $facetType, $language) {
		$resultDocuments = array();
		if (($facetType == self::CITY_FIELD && $this->geoSearchObject->isCityFacetEnable())
			|| ($facetType == self::COUNTRY_FIELD && $this->geoSearchObject->isCountryFacetEnable())
			&& $keyword != ''
		) {
			$geoLocation = $this->getGeoLocation();
			if($geoLocation != '-1') {
				$resultDocuments = $this->processFacet($geoLocation, $facetType, $language);
			}
		}

		return $resultDocuments;
	}

	/**
	 * @param string $geoLocation Latitude and longitude of the search keyword
	 * @param string $facetType
	 * @param string $language
	 */
	protected function processFacet($geoLocation, $facetType, $language) {
		$resultDocuments = array();

		if (($facetType == self::CITY_FIELD && $this->geoSearchObject->isCityFacetEnable())
			|| ($facetType == self::COUNTRY_FIELD && $this->geoSearchObject->isCountryFacetEnable())
		) {
			$query = $this->getDefaultQuery();
			$query->setFieldList(array($facetType));
			$query->setGrouping(true);
			$query->addGroupField($facetType);

			// Facet.City
			if ($facetType == self::CITY_FIELD) {
				if($this->geoSearchObject->getFacetCitySortType() == 'distance') {
					// Sort by distance
					$query->addQueryParameter(
						'sort',
						'geodist(' . self::GEO_LOCATION_FIELD . ',' . $geoLocation . ') '
							.$this->geoSearchObject->getFacetCitySortDirection()
					);
				} else {
					// Sort by city
					$query->addQueryParameter(
						'sort',
						$facetType . ' ' . $this->geoSearchObject->getFacetCitySortDirection()
					);
				}
			} else {
				// Facet.Country

				if($this->geoSearchObject->getFacetCountrySortType() == 'distance') {
					// Sort by distance
					$query->addQueryParameter(
						'sort',
						'geodist(' . self::GEO_LOCATION_FIELD . ',' . $geoLocation . ') '
							. $this->geoSearchObject->getFacetCountrySortDirection()
					);
				} else {
					// Sort by country
					$query->addQueryParameter(
						'sort',
						$facetType . ' ' . $this->geoSearchObject->getFacetCountrySortDirection()
					);
				}
			}

			$this->query = $query;
			$this->search->search($this->query, 0, NULL);
			$response = $this->search->getResponse();
			$resultDocuments = $this->getGroupedResults($response, $facetType, $language);
		}

		return $resultDocuments;
	}

	/**
	 * Add the the grouped value
	 *
	 * @param \Apache_Solr_Response
	 */
	protected function getGroupedResults(\Apache_Solr_Response $response, $facetType, $language) {
		$resultDocuments = array();
		$groupKey = ($facetType == self::CITY_FIELD) ? 'city' : 'country';

		foreach ($response->grouped as $groupCollectionKey => $groupCollection) {
			if ($groupCollectionKey == $facetType && isset($groupCollection->groups)) {
				foreach ($groupCollection->groups as $group) {
					$doclist = $group->doclist;
					$docs    = $doclist->docs;

					if (!empty($docs)){
						$groupedValue = $docs[0]->$facetType;

						if($groupedValue != '') {
							$result = array();
							$result['numFound'] = $doclist->numFound;
							$result[$groupKey] = $groupedValue;
							$resultDocuments[] = $result;
						}
					}
				}
			}
		}

		// Because numFound is not a Solr document field we have to sort it manually
		if (($facetType == self::CITY_FIELD && $this->geoSearchObject->getFacetCitySortType() == 'numfound')
			|| ($facetType == self::COUNTRY_FIELD && $this->geoSearchObject->getFacetCountrySortType() == 'numfound')
		) {
			$numFound = array();
			foreach ($resultDocuments as $key => $row) {
				$numFound[$key] = $row['numFound'];
			}

			if($facetType == self::CITY_FIELD) {
				array_multisort($numFound,(($this->geoSearchObject->getFacetCitySortDirection() == 'asc') ? SORT_ASC : SORT_DESC), $resultDocuments);
			}
			else {
				array_multisort($numFound,(($this->geoSearchObject->getFacetCountrySortDirection() == 'asc') ? SORT_ASC : SORT_DESC), $resultDocuments);
			}
		}

		return $resultDocuments;
	}

	/**
	 * Adds address and geo location information for drawing google maps in frontend
	 *
	 * @param array $resultDocuments
	 */
	public function prepareSolrDocumentsForGoogleMaps(array $resultDocuments) {
		$googleMapsLocations = array();

		if (!empty($resultDocuments)) {
			foreach($resultDocuments as $resultDocument) {
				$tmp = array();

				if ($resultDocument['address_textS'] != '') {
					$tmp[] = $resultDocument['address_textS'] . ', ' . $resultDocument['city_textS'];
				} else {
					$tmp[] = $resultDocument['city_textS'];
				}

				$latLong = explode(',', $resultDocument['geo_location']);
				$tmp[] = $latLong[0];
				$tmp[] = $latLong[1];

				if (!in_array($tmp, $googleMapsLocations)) {
					$googleMapsLocations[] = $tmp;
				}
			}
		}

		return $googleMapsLocations;
	}

	/**
	 * Gets the geo location for the keyword and saves it into an array
	 *
	 * @param string $keyword
	 * @return array contains latitude and longitude
	 */
	public function getGeoLocationAsArray($keyword) {
		$geoLocationArray = array();

		if($keyword != '') {
			$this->setGeoLocation($keyword);
			$geoLocation = $this->getGeoLocation();

			if ($geoLocation != '-1') {
				$geoLocationArray = explode(',', $geoLocation);
			}
		}

		return $geoLocationArray;
	}

}
?>