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
class ResultLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper{

	/**
	 * render a customized link
	 *
	 * @param array $array
	 * @param string $keyword
	 * @param string $linktype
	 * @param string $lng
	 * @return string
	 */
	public function render($array, $keyword, $linktype = NULL, $lng = NULL) {
		$link = '';
		$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_cObj');
		if(!empty($array)) {
			if($linktype == 'resultentry') {
				$linkConfiguration = array(
					'useCacheHash'     => FALSE,
					'no_cache'         => FALSE,
					'parameter'        =>  ($array['type'] == 'tx_solr_file') ? $array['fileRelativePath'] : $array['uid']
				);
				$link = $cObj->typoLink($array['title'], $linkConfiguration);
			}
			else if($linktype == 'distancefilter') {
				$linkConfiguration = array(
					'useCacheHash'     => FALSE,
					'no_cache'         => FALSE,
					'parameter'        => $GLOBALS['TSFE']->id,
					'additionalParams' => '&tx_solrgeo_search[q]='.$keyword.'&L='.$lng
				);
				$link = $cObj->typoLink($GLOBALS['TSFE']->sL('LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.default').
							' (0-'.$array[0].' km)', $linkConfiguration);
			}
			else if($linktype == 'distancerange') {
				$linkConfiguration = array(
					'useCacheHash'     => FALSE,
					'no_cache'         => FALSE,
					'parameter'        => $GLOBALS['TSFE']->id,
					'additionalParams' => '&tx_solrgeo_search[q]='.$keyword.'&tx_solrgeo_search[r]='.$array['value'].'&L='.$lng
				);
				$link = $cObj->typoLink($array['value'].' km', $linkConfiguration);
			}
			else if($linktype == 'city-facet') {
				$linkConfiguration = array(
					'useCacheHash'     => FALSE,
					'no_cache'         => FALSE,
					'parameter'        => $GLOBALS['TSFE']->id,
					'additionalParams' => '&tx_solrgeo_search[q]='.$array['city'].'&L='.$lng
				);
				$link = $cObj->typoLink($array['city'].' ('.$array['numFound'].')', $linkConfiguration);
			}
			else if($linktype == 'country-facet') {
				$linkConfiguration = array(
					'useCacheHash'     => FALSE,
					'no_cache'         => FALSE,
					'parameter'        => $GLOBALS['TSFE']->id,
					'additionalParams' => '&tx_solrgeo_search[q]=country,'.$array['country'].'&L='.$lng
				);
				$link = $cObj->typoLink($array['country'].' ('.$array['numFound'].')', $linkConfiguration);
			}
		}
		else if($linktype == 'removefilter') {
			$linkConfiguration = array(
				'useCacheHash'     => FALSE,
				'no_cache'         => FALSE,
				'parameter'        => $GLOBALS['TSFE']->id,
				'additionalParams' => '&tx_solrgeo_search[q]='.$keyword.'&L='.$lng
			);
			$link = $cObj->typoLink($GLOBALS['TSFE']->sL('LLL:EXT:solrgeo/Resources/Private/Language/locallang_search.xml:tx_solrgeo.faceting_removeFilter'),
						$linkConfiguration);
		}
		return $link;
	}
}