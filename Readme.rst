============================================================
Solrgeo for Apache Solr for TYPO3
============================================================

EXT:solrgeo is an add-on to EXT:sor to provide the geo search with Apache Solr Spatial Search (https://wiki.apache.org/solr/SpatialSearch) and the geocoder PHP library. For more information about this library, visit http://geocoder-php.org.

(by dkd Internet Service GmbH, 2014-04-17)


------------------------
Requirements
------------------------

The add-on solrgeo was tested successfully with this following installations:
* EXT:solr 3.0.0-dev
* Solr Server 4.6.1 and 4.7.0
* TYPO3 6.1.1 and 6.2.0

For using solrgeo it is currently needed that your content is already indexed.


------------------------
Features
------------------------

* Almost all search functionalities are configured with TS.
* Supports Provider for Google Maps, Google Maps Business or OpenStreetMap
* Supports several adapters: 
  ** CurlHttpAdapter
  ** SocketHttpAdapter
  ** BuzzHttpAdapter
  ** GuzzleHttpAdapter
  ** ZendHttpAdapter
  For using Buzz,, Guzzle and Zend please ensure you have installed the respectively libraries
* Working this filters for Spatial Search: 
  ** Distance filter: geofilt (for an exact search result)
  ** Bounding-box filter: bbox (loose search)
* Location data can be added to several tables: pages, files (indexed with extension solrfile / solrfal), tx_news_domain_model_news, etc.
* Results are grouped by city and country for facets
* Results are marked in Google Maps


------------------------
Installation
------------------------

* Include solrgeo as static template
* Create a new page which includes solrgeo as plugin
* Customize TypoScript
  ** Set the new page id you created before if you will link the solr page to the solrgeo page
  ** Important is the geocode-part: plugin.tx_solrgeo.index.geocode. Define at first your table (pages, files, tx_solrfile, tx_news_domain_model_news, etc) and within this table you need at least the uid and city. Optional fields are address and geolocation.
  ** All other configuration options are set with default values.
* Create a scheduler task with solrgeo (class), single (type) and run it.
* Optional: Customize the results 
  ** EXT:solrgeo/Resources/Private/Templates/Search/Search.html
  ** EXT:solrgeo/Resources/Public/Stylesheets/search.css


------------------------
TO DO
------------------------

* Provide adding location data to Solr documents while EXT:solr will index your content
* Provide geo searching with tt_address
