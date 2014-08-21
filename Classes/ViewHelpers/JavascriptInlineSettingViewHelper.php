<?php
namespace TYPO3\Solrgeo\ViewHelpers;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Timo Webler <timo.webler@dkd.de>
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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;


/**
 * InlineSettingViewHelper.
 *
 * @package solrgeo
 * @author Timo Webler <timo.webler@dkd.de>
 */
class JavascriptInlineSettingViewHelper extends AbstractViewHelper{

	/**
	 * Add inline settings to page renderer.
	 *
	 * @param string $namespace
	 * @param string $key
	 * @param string $value
	 * @param array $array
	 * @param boolean $addNamespace Add namespace javascript code
	 * @return void
	 */
	public function render($namespace, $key = NULL, $value = NULL, array $array = NULL, $addNamespace = TRUE) {
		/* @var $pageRenderer \t3lib_PageRenderer */
		$pageRenderer = $GLOBALS['TSFE']->getPageRenderer();

		if ($addNamespace) {
			$namespaceParts = explode('.', $namespace);
			$javascript = '';
			$currentNamespace = array();

			foreach ($namespaceParts as $part) {
				$currentNamespace[] = $part;
				$javascript .= 'if (typeof(' . implode('.', $currentNamespace) . ') == "undefined") { ' .
					implode('.', $currentNamespace) . ' = {};}' . LF;
			}

			// Add namespace Configuration
			$pageRenderer->addJsInlineCode($namespace . '_Namespace', $javascript, FALSE, TRUE);
		}

		// Add javascript code
		$configuration = array();
		if ($array !== NULL) {
			$configuration = $array;
		} else {
			$configuration[$key] = $value;
		}

		$pageRenderer->addJsInlineCode(
			$namespace . '_' . md5(serialize($configuration)),
			$namespace . ' = ' . json_encode($configuration) . ';'
		);
	}
}
