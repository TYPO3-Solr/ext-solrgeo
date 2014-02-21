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
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FrontendGeoSearchController extends SolrController {

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

		if($range != '') {
			$tmp = explode('-',$range);
			$lowerLimit = $tmp[0];
			if(!\TYPO3\Solrgeo\Utility\String::contains($lowerLimit,'.')){
				$lowerLimit = bcadd($lowerLimit, '0.001', 3);
			}
			$upperLimit = $tmp[1];
			$query->addFilter('{!frange l='.$lowerLimit.' u='.$upperLimit.'}geodist()');
			$query->addQueryParameter('sfield', 'geo_location');
			$query->addQueryParameter('pt', $geolocation);
		}
		else {
			if($distance == '') {
				$distance = $this->distance;
			}

			$filterType = $this->filterType;
			$direction = $this->direction;

			$query->addFilter('{!'.$filterType.' pt='.$geolocation.' sfield=geo_location d='.$distance.'}');
			$query->addQueryParameter('sort', 'geodist(geo_location,'.$geolocation.') '.$direction);
		}
		$this->query = $query;

		$offSet = 0;
		$this->search->search($this->query, $offSet, NULL);
		$solrResults = $this->search->getResultDocuments();
		foreach ($solrResults as $result) {
			$fields   = $result->getFieldNames();
			$document = array();
			foreach ($fields as $field) {
				$fieldValue       = $result->getField($field);
				$document[$field] = $fieldValue["value"];
			}
			$resultDocuments[] = $document;
		}

		if(count($resultDocuments) == 0) {
			$additionInformation = '"'.$keyword.'"';
			if($range != '') {
				$additionInformation .= $GLOBALS['LANG']->sL(
						'LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.within').$range.' km';
			}
			$additionInformation .= '.';
			$resultDocuments[] = $this->getErrorResult('no_results_nothing_found',$additionInformation);
		}
		return $resultDocuments;
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
		$document['title'] = $GLOBALS['LANG']->sL(
				'LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.'.$error_key).$additionalErrorInfo;
		$document['content'] = "";
		return $document;
	}
}
?>