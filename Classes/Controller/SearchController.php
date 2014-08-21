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
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\Solrgeo\Controller\FrontendGeoSearchController;


/**
 *
 *
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class SearchController extends ActionController {

	/**
	 * @var FrontendGeoSearchController
	 */
	protected $geoSearchController;

	/**
	 * @var \TYPO3\Solrgeo\Utility\Helper
	 */
	protected $helper;

	/**
	 * @var integer
	 */
	protected $defaultLanguage = 0;

	/**
	 * @var string
	 */
	protected $keyword = '';

	/**
	 * @var string
	 */
	protected $distance = '';

	/**
	 * @var string
	 */
	protected $range = '';

	/**
	 * @var array
	 */
	protected $cityFacetResults = array();

	/**
	 * @var array
	 */
	protected $resultDocuments = array();

	/**
	 * @var array
	 */
	protected $countryFacetResults = array();


	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		$this->initializeSolr();
	}

	/**
	 * Search Action
	 */
	public function searchAction() {
		$this->setRequestLanguage();
		$this->checkSolr();
		$this->setValuesFromRequest();
		$this->assignValuesToSearchTemplate();
	}

	/**
	* Initializes Solr
	*/
	protected function initializeSolr() {
		$this->helper = GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Utility\\Helper');
		$this->geoSearchController = GeneralUtility::makeInstance(
			'TYPO3\\Solrgeo\\Controller\\FrontendGeoSearchController',
			$this->helper->getSolrSite()
		);
		$this->geoSearchController->initializeGeoSearchConfiguration();
	}

	/**
	 * Sets the language from Request-URI
	 */
	public function setRequestLanguage() {
		$uri =  explode("&", $this->uriBuilder->getRequest()->getRequestUri());

		foreach($uri as $parameter) {
			if(GeneralUtility::isFirstPartOfStr($parameter, 'L=')) {
				$this->defaultLanguage = str_replace('L=', '', $parameter);
				if ($this->defaultLanguage == '') {
					$this->defaultLanguage = 0;
				}
				break;
			}
		}
	}

	/**
	 * Checks the Solr status and initialize if not enable
	 */
	public function checkSolr() {
		if(!$this->geoSearchController->getSolrStatus()) {
			$this->geoSearchController->initialize($this->helper->getSolrSite()->getRootPageId(), $this->defaultLanguage);
		}
	}

	/*
	 * Sets some values from search request
	 * */
	public function setValuesFromRequest() {
		if ($this->request->hasArgument('d')) {
			$this->distance = $this->request->getArgument('d');
		}

		if ($this->request->hasArgument('r')) {
			$this->range = $this->request->getArgument('r');
		}

		$this->keyword = $this->request->getArgument('q');
		$this->geoSearchController->setGeoLocation($this->keyword);

		$this->geoSearchController->setHelper($this->helper);
		$this->resultDocuments = $this->geoSearchController->searchByKeyword(
			$this->keyword,
			$this->distance,
			$this->range
		);
		$this->cityFacetResults = $this->geoSearchController->getFacetGrouping(
			$this->keyword,
			FrontendGeoSearchController::CITY_FIELD,
			$this->defaultLanguage
		);
		$this->countryFacetResults = $this->geoSearchController->getFacetGrouping(
			$this->keyword,
			FrontendGeoSearchController::COUNTRY_FIELD,
			$this->defaultLanguage
		);
	}

	/**
	 * Assigns the values to the search template
	 */
	public function assignValuesToSearchTemplate() {
		if (GeneralUtility::isFirstPartOfStr($this->keyword, 'country,')) {
			$countryKeyword = str_replace('country,', '', $this->keyword);
			$this->keyword = '';
		}

		$geoSearchObject     = $this->geoSearchController->getGeoSearchObject();
		$googleMapsLocations = $this->geoSearchController->prepareSolrDocumentsForGoogleMaps($this->resultDocuments);

		$currentGeoLocation = ($this->keyword != '') ?
			$this->geoSearchController->getGeoLocationAsArray($this->keyword) :
			$this->geoSearchController->getGeoLocationAsArray($countryKeyword);

		// general values
		$this->view->assign('language', $this->defaultLanguage);
		$this->view->assign('keyword', $this->keyword);
		$this->view->assign('countryKeyword', $countryKeyword);

		// distance filter
		$this->view->assign('showDistanceFilter', (($geoSearchObject->isDistanceFilterEnable() && $this->keyword != '') ? true : false));
		$this->view->assign('distanceFilterContent', array($geoSearchObject->getDistance()));
		$this->view->assign('configuredDistanceRanges', $geoSearchObject->getConfiguredRanges());
		$this->view->assign('currentRange', $this->range);
		$this->view->assign('removeFilterContent', array());

		// results
		$this->view->assign('resultDocuments', $this->resultDocuments);
		$this->view->assign('countryFacetResults', $this->countryFacetResults);
		$this->view->assign('cityFacetResults', $this->cityFacetResults);

		// Google maps
		$this->view->assign('dataForGoogleMaps', $googleMapsLocations);
		$this->view->assign('currentGeolocation', $currentGeoLocation);
		$this->view->assign('zoom', (($countryKeyword == '') ? $geoSearchObject->getCityZoom() : $geoSearchObject->getCountryZoom()));
		$this->view->assign('searchHasResults', $this->geoSearchController->getSearchHasResults());
	}
}
