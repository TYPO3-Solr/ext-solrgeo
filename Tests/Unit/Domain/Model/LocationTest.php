<?php

namespace TYPO3\Solrgeo\Tests;
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
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Test case for class \TYPO3\Solrgeo\Domain\Model\Location.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage solrgeo
 *
 * @author Phuong Doan <phuong.doan@dkd.de>
 */
class LocationTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \TYPO3\Solrgeo\Domain\Model\Location
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new \TYPO3\Solrgeo\Domain\Model\Location();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getAddressReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setAddressForStringSetsAddress() { 
		$this->fixture->setAddress('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getAddress()
		);
	}
	
	/**
	 * @test
	 */
	public function getZipReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setZipForStringSetsZip() { 
		$this->fixture->setZip('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getZip()
		);
	}
	
	/**
	 * @test
	 */
	public function getRegionReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setRegionForStringSetsRegion() { 
		$this->fixture->setRegion('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getRegion()
		);
	}
	
	/**
	 * @test
	 */
	public function getCountryReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setCountryForStringSetsCountry() { 
		$this->fixture->setCountry('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getCountry()
		);
	}
	
	/**
	 * @test
	 */
	public function getCityReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setCityForStringSetsCity() { 
		$this->fixture->setCity('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getCity()
		);
	}
	
	/**
	 * @test
	 */
	public function getGeolocationReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setGeolocationForStringSetsGeolocation() { 
		$this->fixture->setGeolocation('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getGeolocation()
		);
	}
	
}
?>