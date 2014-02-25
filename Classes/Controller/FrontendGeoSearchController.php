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
 * @property mixed getCityFacetGrouping
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FrontendGeoSearchController extends SolrController {

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

	/**
	* @var string
	*/
	protected $facetCitySortDirection = 'asc';

	/**
	 * @var string
	 */
	protected $facetCitySortType = 'distance';

	/**
	 * @var boolean
	 */
	protected $cityFacetEnable = false;

	/**
	 * @var string
	 */
	protected $facetCountrySortDirection = 'asc';

	/**
	 * @var string
	 */
	protected $facetCountrySortType = 'distance';

	/**
	 * @var boolean
	 */
	protected $searchByCountry = false;

	/**
	 * @var boolean
	 */
	protected $countryFacetEnable = false;


	public function initializeGeoSearchConfiguration() {
		$this->setGeoSearchConfiguration();
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

	/**
	 *
	 * @return string Returns the distance
	 */
	public function getDistance() {
		return $this->distance;
	}

	/**
	 *
	 * @return string Returns the filter type
	 */
	public function getFilterType() {
		return $this->filterType;
	}

	/**
	 *
	 * @return string Returns the sort direction
	 */
	public function getSortDirection() {
		return $this->direction;
	}

	/**
	 * @param string $facetCitySortDirection
	 */
	public function setFacetCitySortDirection($facetCitySortDirection) {
		$this->facetCitySortDirection = $facetCitySortDirection;
	}

	/**
	 * @return string
	 */
	public function getFacetCitySortDirection() {
		return $this->facetCitySortDirection;
	}

	/**
	 * @param string $facetCitySortType
	 */
	public function setFacetCitySortType($facetCitySortType) {
		$this->facetCitySortType = $facetCitySortType;
	}

	/**
	 * @return string
	 */
	public function getFacetCitySortType() {
		return $this->facetCitySortType;
	}

	/**
	 * @param mixed $cityFacetEnable
	 */
	public function setCityFacetEnable($cityFacetEnable) {
		$this->cityFacetEnable = $cityFacetEnable;
	}

	/**
	 * @return mixed
	 */
	public function isCityFacetEnable() {
		return $this->cityFacetEnable;
	}

	/**
	 * @param string $facetCountrySortDirection
	 */
	public function setFacetCountrySortDirection($facetCountrySortDirection) {
		$this->facetCountrySortDirection = $facetCountrySortDirection;
	}

	/**
	 * @return string
	 */
	public function getFacetCountrySortDirection() {
		return $this->facetCountrySortDirection;
	}

	/**
	 * @param string $facetCountrySortType
	 */
	public function setFacetCountrySortType($facetCountrySortType) {
		$this->facetCountrySortType = $facetCountrySortType;
	}

	/**
	 * @return string
	 */
	public function getFacetCountrySortType() {
		return $this->facetCountrySortType;
	}

	/**
	 * @param boolean $countryFacetEnable
	 */
	public function setCountryFacetEnable($countryFacetEnable) {
		$this->countryFacetEnable = $countryFacetEnable;
	}

	/**
	 * @return boolean
	 */
	public function isCountryFacetEnable() {
		return $this->countryFacetEnable;
	}


	/**
	 * Sets the configured filter type, sort direction and distance for query / facets
	 *
	 * @return void
	 */
	protected function setGeoSearchConfiguration() {
		$configuration = $this->helper->getConfiguration('tx_solrgeo');
		$searchConf = $configuration['search.'];

		// query configuration
		if(!empty($searchConf['query.']['filter.']['d'])){
			$this->setDistance($searchConf['query.']['filter.']['d']);
		}

		if(!empty($searchConf['query.']['filter.']['type'])){
			$this->setFilterType($searchConf['query.']['filter.']['type']);
		}

		if(!empty($searchConf['query.']['sort.']['direction'])) {
			$this->setSortDirection(strtolower($searchConf['query.']['sort.']['direction']));
		}

		// fact.city configuration
		if(!empty($searchConf['faceting.']['city']) && $searchConf['faceting.']['city'] == '1') {
			$this->setCityFacetEnable(true);
		}
		if(!empty($searchConf['faceting.']['city.']['sort.']['direction'])) {
			$this->setFacetCitySortDirection(strtolower($searchConf['faceting.']['city.']['sort.']['direction']));
		}

		if(!empty($searchConf['faceting.']['city.']['sort.']['type'])) {
			$this->setFacetCitySortType(strtolower($searchConf['faceting.']['city.']['sort.']['type']));
		}

		// fact.country configuration
		if(!empty($searchConf['faceting.']['country']) && $searchConf['faceting.']['country'] == '1') {
			$this->setCountryFacetEnable(true);
		}
		if(!empty($searchConf['faceting.']['country.']['sort.']['direction'])) {
			$this->setFacetCountrySortDirection(strtolower($searchConf['faceting.']['country.']['sort.']['direction']));
		}

		if(!empty($searchConf['faceting.']['country.']['sort.']['type'])) {
			$this->setFacetCountrySortType(strtolower($searchConf['faceting.']['country.']['sort.']['type']));
		}
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
		$query = $this->getDefaultQuery();
		$limit = 10;
		if (!empty($this->conf['search.']['results.']['resultsPerPage'])) {
			$limit = $this->conf['search.']['results.']['resultsPerPage'];
		}
		$query->setResultsPerPage($limit);
		$query->setHighlighting();

		$query = $this->modifyQuery($query, $keyword, $geolocation, $distance, $range);
		$this->query = $query;

		$this->search->search($this->query, 0, NULL);
		$solrResults = $this->search->getResultDocuments();
		foreach ($solrResults as $result) {
			$fields   = $result->getFieldNames();
			$document = array();
			if(!$this->searchByCountry || ($this->searchByCountry && in_array(self::COUNTRY_FIELD, $fields))) {
				foreach ($fields as $field) {
					$fieldValue       = $result->getField($field);
					$document[$field] = $fieldValue["value"];
				}
				$resultDocuments[] = $document;
			}
		}

		if(count($resultDocuments) == 0) {
			$additionInformation = '"'.$keyword.'"';
			if($range != '') {
				$additionInformation .= $GLOBALS['TSFE']->sL(
						'LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.within').$range.' km';
			}
			$additionInformation .= '.';
			$resultDocuments[] = $this->getErrorResult('no_results_nothing_found',$additionInformation);
		}
		return $resultDocuments;
	}

	public function modifyQuery($query, $keyword, $geolocation, $distance, $range) {
		if(\TYPO3\Solrgeo\Utility\String::startsWith($keyword, 'country,')) {
			$this->searchByCountry = true;
			$keyword = str_replace('country,','',$keyword);
			$query->setQueryField(self::COUNTRY_FIELD, 1.0);
			$query->setKeywords($keyword);
		}
		else if($range != '') {
			$tmp = explode('-',$range);
			$lowerLimit = $tmp[0];
			if($lowerLimit != '0' && !\TYPO3\Solrgeo\Utility\String::contains($lowerLimit,'.')){
				$lowerLimit = bcadd($lowerLimit, '0.001', 3);
			}
			$upperLimit = $tmp[1];
			$query->addFilter('{!frange l='.$lowerLimit.' u='.$upperLimit.'}geodist()');
			$query->addQueryParameter('sfield', self::GEO_LOCATION_FIELD);
			$query->addQueryParameter('pt', $geolocation);
		}
		else {
			if($distance == '') {
				$distance = $this->distance;
			}

			$filterType = $this->filterType;
			$direction = $this->direction;

			$query->addFilter('{!'.$filterType.' pt='.$geolocation.' sfield='.self::GEO_LOCATION_FIELD.' d='.$distance.'}');
			$query->addQueryParameter('sort', 'geodist('.self::GEO_LOCATION_FIELD.','.$geolocation.') '.$direction);
		}
		return $query;
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
		$document['title'] = $GLOBALS['TSFE']->sL(
				'LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.'.$error_key).$additionalErrorInfo;
		$document['content'] = "";
		return $document;
	}

	/**
	 * @param string The search keyword
	 */
	public function getFacetGrouping($keyword, $facetType) {
		$resultDocuments = array();
		if(($facetType == self::CITY_FIELD && $this->isCityFacetEnable()) ||
		   ($facetType == self::COUNTRY_FIELD && $this->isCountryFacetEnable())
			&& $keyword != '') {

			$geocoder = $this->helper->getGeoCoder();
			$geolocation = $geocoder->getGeolocationFromKeyword($keyword);
			if($geolocation != '-1') {
				$resultDocuments = $this->processFacet($geolocation, $facetType);
			}

		}
		return $resultDocuments;
	}

	/**
	 * @param string Latitude and longitude of the search keyword
	 */
	private function processFacet($geolocation, $facetType) {
		$resultDocuments = array();
		if(($facetType == self::CITY_FIELD && $this->isCityFacetEnable()) ||
			($facetType == self::COUNTRY_FIELD && $this->isCountryFacetEnable())) {
			$query = $this->getDefaultQuery();
			$query->setFieldList(array($facetType));
			$query->setGrouping(true);
			$query->addGroupField($facetType);

			// Facet.City
			if($facetType == self::CITY_FIELD) {
				if($this->getFacetCitySortType() == 'distance') {
					// Sort by distance
					$query->addQueryParameter('sort',
						'geodist('.self::GEO_LOCATION_FIELD.','.$geolocation.') '.$this->getFacetCitySortDirection());
				}
				else {
					// Sort by city
					$query->addQueryParameter('sort',$facetType.' '.$this->getFacetCitySortDirection());
				}
			}

			// Facet.Country
			else {
				if($this->getFacetCountrySortType() == 'distance') {
					// Sort by distance
					$query->addQueryParameter('sort',
						'geodist('.self::GEO_LOCATION_FIELD.','.$geolocation.') '.$this->getFacetCountrySortDirection());
				}
				else {
					// Sort by country
					$query->addQueryParameter('sort',$facetType.' '.$this->getFacetCountrySortDirection());
				}
			}

			$this->query = $query;
			$this->search->search($this->query, 0, NULL);
			$response = $this->search->getResponse();
			$resultDocuments = $this->getGroupedResults($response, $facetType);
		}
		return $resultDocuments;
	}

	/**
	 * Add the the grouped value
	 *
	 * @param \Apache_Solr_Response
	 */
	private function getGroupedResults(\Apache_Solr_Response $response, $facetType) {
		$resultDocuments = array();
		$groupKey = ($facetType == self::CITY_FIELD) ? 'city' : 'country';
		foreach ($response->grouped as $groupCollectionKey => $groupCollection) {
			if($groupCollectionKey == $facetType && isset($groupCollection->groups)) {
				foreach($groupCollection->groups as $group) {
					$doclist = $group->doclist;
					$docs = $doclist->docs;
					if(count($docs)>0){
						$groupedValue = $docs[0]->$facetType;
						if($groupedValue != '') {
							$result = array();
							$result['numFound'] = $doclist->numFound;
							$result[$groupKey] = $groupedValue;
							$result['url'] = $this->helper->getLinkUrl(true).
									(($facetType == self::CITY_FIELD) ? $groupedValue : $groupKey.",".$groupedValue) ;
							$resultDocuments[] = $result;
						}
					}
				}
			}
		}

		if(($facetType == self::CITY_FIELD && $this->getFacetCitySortType() == 'numfound') ||
		   ($facetType == self::COUNTRY_FIELD && $this->getFacetCountrySortType() == 'numfound')) {
			$numfound = array();
			foreach ($resultDocuments as $key => $row) {
				$numfound[$key] = $row['numFound'];
			}

			if($facetType == self::CITY_FIELD) {
				array_multisort($numfound,(($this->getFacetCitySortDirection() == 'asc') ? SORT_ASC : SORT_DESC), $resultDocuments);
			}
			else {
				array_multisort($numfound,(($this->getFacetCountrySortDirection() == 'asc') ? SORT_ASC : SORT_DESC), $resultDocuments);
			}
		}

		return $resultDocuments;
	}

}
?>