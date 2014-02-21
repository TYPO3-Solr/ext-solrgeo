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
class BackendGeoSearchController extends SolrController {

	/**
	 * Update the Solr Document with the address and location values
	 *
	 * @param string The type (pages, tx_solr_file) or TCA table
	 * @param integer The UID of the page
	 * @param \TYPO3\Solrgeo\Domain\Model\Location The Search Object holds the information about address and Geolocation
	 * @return boolean Returns the Status of Update
	 */
	public function updateSolrDocument($type, $uid, \TYPO3\Solrgeo\Domain\Model\Location $locationObject) {
		$updateSolrDocument = true;
		$address = ($locationObject->getAddress() != "") ?
			$locationObject->getAddress().", ".$locationObject->getCity() : $locationObject->getCity();

		$solrConnections = $this->connectionManager->getAllConnections();
		foreach ($solrConnections as $systemLanguageUid => $solrConnection) {
			$this->initializeSearch($this->site->getRootPageId(), $systemLanguageUid);
			$solrResults = $this->search($type, $uid);

			if(!empty($solrResults)) {
				foreach($solrResults as $solrDocument) {
					if($solrDocument instanceof \Apache_Solr_Document) {
						$updateFlag = true;
						if($this->solrDocumentHasFieldByName($solrDocument, self::GEO_LOCATION_FIELD)) {
							$geoField = $solrDocument->getField(self::GEO_LOCATION_FIELD);
							$addressField = $solrDocument->getField(self::ADDRESS_FIELD);
							if( $geoField['value'] == $locationObject->getGeolocation() &&
								$addressField['value'] == $address) {
								$updateFlag = false;
							}
						}
						if($updateFlag) {
							// Prepare Solr Document
							$solrDocument->setField(self::GEO_LOCATION_FIELD, $locationObject->getGeolocation());
							$solrDocument->setField(self::ADDRESS_FIELD, $address);
							// Need to unset this field otherwise the copyfield function adds teaser text as multivalue!
							unset($solrDocument->teaser);
							if(!$this->solrDocumentHasFieldByName($solrDocument, 'appKey')) {
								$solrDocument->setField('appKey', 'EXT:solr');
							}

							// Update the Solr Document
							$response = $solrConnection->addDocument($solrDocument);
							if ($response->getHttpStatus() == 200) {
								$updateSolrDocument = true;
							}
						}
					}
				}
			}
		}

		return $updateSolrDocument;
	}
}