# Installation

Download zip file and install it as a plugin in WordPress, then activate. Make sure you have the [WordPress SEO Plugin](http://yoast.com/wordpress/seo/) active and have enabled XML Sitemaps, otherwise it won't work. Then configure it further using the options.

Note: the XML sitemap it creates will be `news-sitemap.xml`, a previous version of this script used `news_sitemap.xml` (with an underscore instead of a dash), but doing it this way means we have to register one rewrite less, which is good for performance.

# Changelog

## 1.1

* Changed dir structure
* Implemented meta news keywords
* Added a settings link on the plugins page
* Moved News SEO settings to its own submenu
* Fixed a few bugs & notices

## 1.0

* Initial version on Github