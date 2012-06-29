<?php
/*
Plugin Name: WordPress SEO News
Version: 1.0
Plugin URI: http://yoast.com/wordpress/seo/news/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=wpseonewsplugin
Description: Google News plugin for the WordPress SEO plugin
Author: Joost de Valk
Author URI: http://yoast.com/
License: GPL v3

WordPress SEO Plugin
Copyright (C) 2008-2012, Joost de Valk - joost@yoast.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * This function loads the required classes if and when required.
 *
 * @return void
 */
function load_news_classes() {
	$options = get_option( 'wpseo_xml' );

	$enabled = true;
	if ( !isset( $options['enablexmlnewssitemap'] ) || !$options['enablexmlnewssitemap'] )
		$enabled = false;

	if ( is_admin() ) {
		global $pagenow;
		if ( $enabled && in_array( $pagenow, array( 'edit.php', 'post.php', 'post-new.php' ) ) ) {
			require_once 'xml-news-metabox.php';
		}
		if ( in_array( $pagenow, array( 'admin.php' ) ) ) {
			require_once 'xml-news-admin.php';
		}
	} else if ( $enabled ) {
		require_once 'xml-news-sitemap-class.php';
	}
}

add_action( 'plugins_loaded', 'load_news_classes', 20 );