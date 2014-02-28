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
class GeoSearchConfiguration {
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
	 * @var boolean
	 */
	protected $distanceFilterEnable = false;

	/**
	 * @var array
	 */
	protected $configuredRanges = array();

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

	/**
	 * @var int
	 */
	protected $cityZoom = 8;

	/**
	 * @var int
	 */
	protected $countryZoom = 4;

	/**
	 * @param string Distance
	 */
	public function setDistance($distance) {
		$this->distance = $distance;
	}

	/**
	 * @param string Filter type: geofilt oder bbox
	 */
	public function setFilterType($filterType) {
		$this->filterType = $filterType;
	}

	/**
	 * @param string Sort direction: asc or desc
	 */
	public function setSortDirection($direction) {
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
	 * @param mixed $showDistanceFilter
	 */
	public function setDistanceFilterEnable($showDistanceFilter) {
		$this->distanceFilterEnable = $showDistanceFilter;
	}

	/**
	 * @return mixed
	 */
	public function isDistanceFilterEnable() {
		return $this->distanceFilterEnable;
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
	 * @param array $ranges
	 */
	public function setConfiguredRanges($ranges) {
		$this->configuredRanges = $ranges;
	}

	/**
	 * @return array
	 */
	public function getConfiguredRanges(){
		return $this->configuredRanges;
	}

	/**
	 * @param string $direction
	 */
	public function setDirection($direction) {
		$this->direction = $direction;
	}

	/**
	 * @return string
	 */
	public function getDirection() {
		return $this->direction;
	}

	/**
	 * @param boolean $searchByCountry
	 */
	public function setSearchByCountry($searchByCountry) {
		$this->searchByCountry = $searchByCountry;
	}

	/**
	 * @return boolean
	 */
	public function isSearchByCountry() {
		return $this->searchByCountry;
	}

	/**
	 * @param int $zoom
	 */
	public function setCityZoom($cityZoom) {
		$this->cityZoom = $cityZoom;
	}

	/**
	 * @return int
	 */
	public function getCityZoom() {
		return $this->cityZoom;
	}

	/**
	 * @param int $countryZoom
	 */
	public function setCountryZoom($countryZoom) {
		$this->countryZoom = $countryZoom;
	}

	/**
	 * @return int
	 */
	public function getCountryZoom() {
		return $this->countryZoom;
	}

} 