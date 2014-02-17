<?php
namespace TYPO3\Solrgeo\Domain\Repository;

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
class LocationRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Sets the querysettings global
	 */
	public function initializeObject() {
    	/** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
		$querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

		// don't add the pid constraint
		$querySettings->setRespectStoragePage(FALSE);

		$this->setDefaultQuerySettings($querySettings);
	}

	/**
	 * Find the stored location
	 *
	 * @param array $location
	 * @return void
	 */
	public function findByConfiguredLocation($location) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('city',$location['city']),
				$query->equals('address',$location['address'])
			)
		);
		$query->setLimit((integer)1);

		return $query->execute();
	}

	// Create new location object
	public function createLocation($location) {
		$locationObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Domain\\Model\\Location');
		$locationObject->setPid($location['uid']);
		$locationObject->setAddress($location['address']);
		$locationObject->setZip($location['zip']);
		$locationObject->setCity($location['city']);
		return $locationObject;
	}



}
?>