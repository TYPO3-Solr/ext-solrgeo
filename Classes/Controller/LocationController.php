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
class LocationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * locationRepository
	 *
	 * @var \TYPO3\Solrgeo\Domain\Repository\LocationRepository
	 * @inject
	 */
	protected $locationRepository;

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$locations = $this->locationRepository->findAll();
		$this->view->assign('locations', $locations);
	}

	/**
	 * action show
	 *
	 * @param \TYPO3\Solrgeo\Domain\Model\Location $location
	 * @return void
	 */
	public function showAction(\TYPO3\Solrgeo\Domain\Model\Location $location) {
		$this->view->assign('location', $location);
	}

	/**
	 * action new
	 *
	 * @param \TYPO3\Solrgeo\Domain\Model\Location $newLocation
	 * @dontvalidate $newLocation
	 * @return void
	 */
	public function newAction(\TYPO3\Solrgeo\Domain\Model\Location $newLocation = NULL) {
		$this->view->assign('newLocation', $newLocation);
	}

	/**
	 * action create
	 *
	 * @param \TYPO3\Solrgeo\Domain\Model\Location $newLocation
	 * @return void
	 */
	public function createAction(\TYPO3\Solrgeo\Domain\Model\Location $newLocation) {
		$this->locationRepository->add($newLocation);
		$this->flashMessageContainer->add('Your new Location was created.');
		$this->redirect('list');
	}

	/**
	 * action edit
	 *
	 * @param \TYPO3\Solrgeo\Domain\Model\Location $location
	 * @return void
	 */
	public function editAction(\TYPO3\Solrgeo\Domain\Model\Location $location) {
		$this->view->assign('location', $location);
	}

	/**
	 * action update
	 *
	 * @param \TYPO3\Solrgeo\Domain\Model\Location $location
	 * @return void
	 */
	public function updateAction(\TYPO3\Solrgeo\Domain\Model\Location $location) {
		$this->locationRepository->update($location);
		$this->flashMessageContainer->add('Your Location was updated.');
		$this->redirect('list');
	}

	/**
	 * action delete
	 *
	 * @param \TYPO3\Solrgeo\Domain\Model\Location $location
	 * @return void
	 */
	public function deleteAction(\TYPO3\Solrgeo\Domain\Model\Location $location) {
		$this->locationRepository->remove($location);
		$this->flashMessageContainer->add('Your Location was removed.');
		$this->redirect('list');
	}

	/**
	 * action
	 *
	 * @return void
	 */
	public function Action() {

	}

}
?>