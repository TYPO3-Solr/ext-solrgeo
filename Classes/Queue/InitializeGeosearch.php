<?php
namespace TYPO3\Solrgeo\Queue;


/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Phuong Doan <phuong.doan@dkd.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class InitializeGeosearch
 *
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class InitializeGeosearch implements \tx_solr_IndexQueueInitializationPostProcessor {
	/**
	 * Post process Index Queue initialization
	 *
	 * @param \tx_solr_Site $site The site to initialize
	 * @param array $indexingConfigurations Initialized indexing configurations
	 * @param array $initializationStatus Results of Index Queue initializations
	 */
	public function postProcessIndexQueueInitialization(\tx_solr_Site $site, array $indexingConfigurations, array $initializationStatus) {
		$solrconfig = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
								'TYPO3\\Solrgeo\\Configuration\\GeoSearchConfiguration',
								$site);

		$geocoder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
								'TYPO3\\Solrgeo\\Service\\GeoCoderService',
								$solrconfig->getProvider());

		$geocoder->setLocationList($solrconfig->getLocationList());
		$geocoder->processGeocoding($site);

	}
}