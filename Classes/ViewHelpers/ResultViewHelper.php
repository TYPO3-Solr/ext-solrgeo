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
class ResultViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper{

	/**
	 * @param array $resultDocument
	 * @param integer $maxLength
	 * @param string $cropIndicator
	 * @param boolean $cropFullWords
	 */
	public function render($resultDocument, $maxLength = 50, $cropIndicator = '...', $cropFullWords = true) {
		$resultString = '';
		if(!empty($resultDocument)) {
			$link = ($resultDocument['type'] == 'tx_solr_file') ?
				$resultDocument['fileRelativePath'] : $resultDocument['url'];
			$cropViewHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Solr_ViewHelper_Crop');
			$contentArray = array($resultDocument['content'], $maxLength, $cropIndicator, $cropFullWords);

			$resultString .= '<li class="results-entry">';
			$resultString .= '<h5 class="results-topic"><a href="'.$link.'">'.$resultDocument['title'].'</a></h5>';
			$resultString .= '<div class="results-teaser">';
			$resultString .= '<p class="result-content">';
			$resultString .=  '<span class="label"><strong>';
			$resultString .= $resultDocument['address_textS'];
			$resultString .=  '</strong></span><br />';
			$resultString .= $cropViewHelper->execute($contentArray);
			$resultString .= '</p>';
			$resultString .= '</div>';
			$resultString .= '</li>';
		}
		return $resultString;
	}
}