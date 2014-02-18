<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Autoload for Geocoder-php Library
require_once ExtensionManagementUtility::extPath('solrgeo').'Resources/Private/PHP/Geocoder/'.'src/autoload.php';


// Register initializing of the Index Queue for Geosearch
//$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['solr']['postProcessIndexQueueInitialization']['solrgeo'] = 'TYPO3\\Solrgeo\\Queue\\InitializeGeosearch';

// adding scheduler tasks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['TYPO3\\Solrgeo\\Scheduler\\GeoCoderTask'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'LLL:EXT:solrgeo/Resources/Private/Language/locallang.xlf:scheduler.title',
	'description'      => 'LLL:EXT:solrgep/Resources/Private/Language/locallang.xlf:scheduler.description'
);

// Extending the SortingCommand for "Sort By"-Box
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['Tx_Solr_PiResults_SortingCommand'] = array(
	'className' => 'TYPO3\\Solrgeo\\Search\\GeoSearchSortingCommand'
);
/*
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'TYPO3.' . $_EXTKEY,
	'Search',
	array(
		'list, show, update, create, delete, udpate' => '',

	),
	// non-cacheable actions
	array(
		'create, delete, udpate' => '',

	)
);
*/
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'TYPO3.' . $_EXTKEY,
	'search',
	array('Search' => 'search'),
	array('Search' => 'search')
);


/*
Tx_Solr_Search_SearchComponentManager::registerSearchComponent(
	'geosearch',
	'TYPO3\\Solrgeo\\Search\\SpartialSearchCommand'
);
*/
?>