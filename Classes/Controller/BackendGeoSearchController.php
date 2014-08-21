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

use TYPO3\Solrgeo\Domain\Model\Location;


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
	 * @param string $type The type (pages, tx_solr_file) or TCA table
	 * @param integer $uid The UID of the page
	 * @param \TYPO3\Solrgeo\Domain\Model\Location The Search Object holds the information about address and Geo location
	 * @return boolean Status of Update
	 */
	public function updateSolrDocument($type, $uid, Location $locationObject) {
		$solrDocumentUpdated = false;
		$solrConnections     = $this->connectionManager->getAllConnections();

		foreach ($solrConnections as $solrConnection) {
			$solrResults = $this->search($solrConnection, $type, $uid);

			if (!empty($solrResults)) {
				foreach ($solrResults as $solrDocument) {
					if ($solrDocument instanceof \Apache_Solr_Document) {

						// Prepare Solr Document
						$solrDocument->setField(self::ADDRESS_FIELD, $locationObject->getAddress());
						$solrDocument->setField(self::GEO_LOCATION_FIELD, $locationObject->getGeolocation());
						$solrDocument->setField(self::CITY_FIELD, $locationObject->getCity());
						$solrDocument->setField(self::COUNTRY_FIELD, $locationObject->getCountry());
						$solrDocument->setField(self::REGION_FIELD, $locationObject->getRegion());

						// Need to unset this field otherwise the copy field function adds teaser text as multivalue!
						unset($solrDocument->teaser);
						if (!$this->solrDocumentHasFieldByName($solrDocument, 'appKey')) {
							$solrDocument->setField('appKey', 'EXT:solr');
						}

						// Update the Solr Document
						$response = $solrConnection->addDocument($solrDocument);
						if ($response->getHttpStatus() == 200) {
							$solrDocumentUpdated = true;
						}
					}
				}
			}
		}

		return $solrDocumentUpdated;
	}
}