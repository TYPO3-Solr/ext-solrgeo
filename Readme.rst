============================================================
Solrgeo for Apache Solr for TYPO3
============================================================

EXT:solrgeo is an add-on to EXT:sor to provide the geo search with Apache Solr Spatial Search (https://wiki.apache.org/solr/SpatialSearch) and the geocoder PHP library. For more information about this library, visit http://geocoder-php.org.


------------------------
Requirement:
------------------------
* EXT:solr 3.0.0
* Solr Server 4.x
* TYPO3 6.1+


------------------------
Features:
------------------------

* Almost all search functionalities are configured with TS.
* Supports Provider for Google Maps, Google Maps Business or OpenStreetMap
* Working this filters for Spatial Search: 
  ** Distance filter: geofilt (for an exact search result)
  ** Bounding-box filter: bbox (loose search)
* Location data can be added to several tables: pages, files (indexed with EXT:solrfile or EXT:solrfal), tx_news_domain_model_news, etc.
* Results are grouped by city and country for facets
* Results are marked in Google Maps