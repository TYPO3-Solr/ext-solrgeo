<?php
namespace TYPO3\Solrgeo\Search;

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
use Tx_Solr_PluginBase_CommandPluginBase;

/**
 *
 *
 * @package solrgeo
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class SpartialSearchCommand implements \Tx_Solr_PluginCommand{

	/**
	 * Instance of the caching frontend used to cachethis command's output.
	 *
	 * @var t3lib_cache_frontend_AbstractFrontend
	 */
	protected $cacheInstance;

	/**
	 * Parent plugin
	 *
	 * @var Tx_Solr_PiResults_Results
	 */
	protected $parentPlugin;

	/**
	 * Configuration
	 *
	 * @var	array
	 */
	protected $configuration;

	public function execute() {
		$marker = array();
		return $marker;
	}

	/**
	 * Constructor.
	 *
	 * @param Tx_Solr_pluginbase_CommandPluginBase Parent plugin object.
	 */
	public function __construct(Tx_Solr_PluginBase_CommandPluginBase $parentPlugin) {
		$this->parentPlugin  = $parentPlugin;
		$this->configuration = $parentPlugin->conf;
	}
}