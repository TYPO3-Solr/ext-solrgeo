<?php
namespace TYPO3\Solrgeo\ViewHelpers;

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
class DistanceFilterViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper{

	/**
	 * @var string
	 */
	protected $linkUrl = '';

	/**
	 * Arguments Initialization
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('showDistanceFilter', 'boolean', 'Determines to show the distance filter or not', TRUE);
		$this->registerArgument('linkUrl', 'string', 'The built link for solrgeo', FALSE, '');
		$this->registerArgument('keyword', 'string', 'The search keyword', TRUE);
		$this->registerArgument('defaultDistance', 'string', 'The default distance configured in TS', TRUE);
		$this->registerArgument('configuredDistanceRanges', 'array', 'The distances to show configured in TS', FALSE, '');
		$this->registerArgument('currentRange', 'string', 'The current clicked distance range', FALSE, '');
		$this->registerArgument('language', 'string', 'The current language', FALSE, '0');
	}


	/**
	 * Renders the Distance Filter
	 */
	public function render() {
		$distanceFilterBox = "";

		if($this->arguments['showDistanceFilter']  && $this->arguments['keyword'] != '') {
			$this->linkUrl = $this->arguments['linkUrl'].$this->arguments['keyword'].'&L='.$this->arguments['language'];
			if(!empty($this->arguments['configuredDistanceRanges']) && $this->arguments['keyword'] != "") {
				$distanceFilterBox = '
				<div class="secondaryContentSection">
					<div class="csc-header">
						<h3 class="csc-firstHeader">'.
							$GLOBALS['TSFE']->sL('LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.filter.distance').
						'</h3>
					</div>';

				$distanceFilterBox .= $this->buildTopDefaultDistanceBox();
				$distanceFilterBox .= "<ul class='facets'>";
				foreach($this->arguments['configuredDistanceRanges'] as $range) {
					$distanceFilterBox .= '<li><a href="'.$this->linkUrl.'&tx_solrgeo_search[r]='.$range['value'].'">';
					$markStart = '';
					$markEnd = '';
					if($this->arguments['currentRange'] == $range['value']) {
						$markStart = '<strong>';
						$markEnd = '</strong>';
					}
					$distanceFilterBox .= $markStart.$range['value'].' km'.$markEnd;
					$distanceFilterBox .= '</a></li>';
				}
				$distanceFilterBox .= "</ul>";
				$distanceFilterBox .= $this->buildLinkForRemoveFilter();
				$distanceFilterBox .= "</div>";
			}
		}

		return $distanceFilterBox;
	}

	/**
	 * Build the default Distance Filter Box on top
	 *
	 * @param string $distanceFilter
	 * @param string $defaultDistance
	 * @return string
	 */
	public function buildTopDefaultDistanceBox() {
		$topDistanceFilterBox	= '';
		$markStart 				= '';
		$markEnd 				= '';

		if($this->arguments['currentRange'] == '') {
			$markStart = '<strong>';
			$markEnd = '</strong>';
		}

		$topDistanceFilterBox .= '<ul>';
		$topDistanceFilterBox .= '<li>';
		$topDistanceFilterBox .= '<a href="'.$this->linkUrl.'">'.$markStart.'Default <br>(0-'.$this->arguments['defaultDistance'].' km)'.$markEnd.'</a>';
		$topDistanceFilterBox .= '</li>';
		$topDistanceFilterBox .= '</ul>';

		return $topDistanceFilterBox;
	}

	/**
	 * Build the link for removing the current active distance filter
	 *
	 * @return string
	 */
	public function buildLinkForRemoveFilter() {
		$linkForRemoveFilter = '';
		if($this->arguments['currentRange'] != '') {
			$linkForRemoveFilter .= '<a href="'.$this->linkUrl.'">'.
				$GLOBALS['TSFE']->sL('LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.faceting_removeFilter').
				'</a>';
		}
		return $linkForRemoveFilter;
	}
}