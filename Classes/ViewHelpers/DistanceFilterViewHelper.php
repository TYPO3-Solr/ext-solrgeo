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
	 * @param string $keyword
	 * @param string $defaultDistance
	 * @param string $distanceFilter
	 */
	public function render($keyword, $defaultDistance = '', $distanceFilter='') {
		$distanceFilterBox = "";
		$helper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\Solrgeo\\Utility\\Helper');
		$configuration = $helper->getConfiguration('tx_solrgeo');
		if($configuration['search.']['faceting.']['distance'] == '1' && $keyword != '') {
			$ranges = $configuration['search.']['faceting.']['distance.']['ranges.'];
			$showRemoveDistanceFilter = false;
			if($distanceFilter != '') {
				$showRemoveDistanceFilter = true;
			}

			$linkUrl = $helper->getLinkUrl(true).$keyword;
			if(!empty($ranges) && $keyword != "") {
				$distanceFilterBox = '
				<div class="secondaryContentSection">
					<div class="csc-header">
						<h3 class="csc-firstHeader">'.
							$GLOBALS['TSFE']->sL('LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.filter.distance').
						'</h3>
					</div>';

				if($distanceFilter == '') {
					$markStart = '<strong>';
					$markEnd = '</strong>';
				}

				$distanceFilterBox .= '<ul>';
				$distanceFilterBox .= '<li>';
				$distanceFilterBox .= '<a href="'.$linkUrl.'">'.$markStart.'Default <br>(0-'.$defaultDistance.' km)'.$markEnd.'</a>';
				$distanceFilterBox .= '</li>';
				$distanceFilterBox .= '</ul>';

				$distanceFilterBox .= "<ul class='facets'>";
				foreach($ranges as $range) {
					$distanceFilterBox .= '<li><a href="'.$linkUrl.'&tx_solrgeo_search[r]='.$range['value'].'">';
					$markStart = '';
					$markEnd = '';
					if($distanceFilter == $range['value']) {
						$markStart = '<strong>';
						$markEnd = '</strong>';
					}
					$distanceFilterBox .= $markStart.$range['value'].' km'.$markEnd;
					$distanceFilterBox .= '</a></li>';
				}
				$distanceFilterBox .= "</ul>";

				if($showRemoveDistanceFilter) {
					$distanceFilterBox .= '<a href="'.$linkUrl.'">'.
						$GLOBALS['TSFE']->sL('LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.faceting_removeFilter').
						'</a>';
				}

				$distanceFilterBox .= "</div>";
			}
		}

		return $distanceFilterBox;
	}
} 