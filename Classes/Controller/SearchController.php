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
class SearchController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\Solrgeo\Controller\SolrController
	 */
	protected $solr;

	/**
	 * @var \TYPO3\Solrgeo\Utility\Helper
	 */
	protected $helper;

	/**
	 * default langauge
	 * @var integer
	 */
	protected $defaultLanguage = 0;

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
		$uri =  explode("&",$this->uriBuilder->getRequest()->getRequestUri());
		foreach($uri as $t) {
			if(\TYPO3\Solrgeo\Utility\String::startsWith($t,'L=')) {
				$this->defaultLanguage = str_replace('L=','',$t);
				break;
			}
		}

		if(!$this->solr->getSolrstatus()) {
			$this->solr->initialize($this->helper->getSolrSite()->getRootPageId(), $this->defaultLanguage);
		}

		$resultDocuments = $this->solr->searchByKeyword($this->request->getArgument('q'));
		$this->view->assign('resultDocuments',$resultDocuments);
	}

	protected function initializeSolr() {
		$this->helper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Utility\\Helper');
		$this->solr = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Controller\\SolrController',
						$this->helper->getSolrSite());
	}

}
?>