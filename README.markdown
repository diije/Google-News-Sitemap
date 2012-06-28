# INSTALLATION

Copy the `wpseo-news` directory to your plugins directory and activate the plugin on the plugins tab. Make sure you have the [WordPress SEO Plugin](http://yoast.com/wordpress/seo/) active and have enabled XML Sitemaps, otherwise it won't work. Then configure it further using the options.

Note: the XML sitemap it creates will be `news-sitemap.xml`, a previous version of this script used `news_sitemap.xml` (with an underscore instead of a dash), but doing it this way means we have to register one rewrite less, which is good for performance.