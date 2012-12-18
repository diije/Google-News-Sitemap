<?php

/**
* This class handles the addition of the News XML section to the XML Sitemaps page.
*/
class WPSEO_XML_News_Sitemap_Global {
	public function __construct() {
		global $pagenow;

		add_action( 'admin_init', array( $this, 'options_init' ) );

		if ( $pagenow != 'admin.php' )
			add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
	}

	/**
	 * Register the wpseo_news option to store our variables.
	 */
	function options_init() {
		register_setting( 'yoast_wpseo_news_options', 'wpseo_news' );
	}

	/**
	 * Register the News SEO submenu.
	 */
	function register_settings_page() {
		add_submenu_page( 'wpseo_dashboard', __( 'News SEO', 'wordpress-seo' ), __( 'News SEO', 'wordpress-seo' ),
			'manage_options', 'wpseo_news', array( $this, 'admin_panel' ) );
	}

	/**
	 * Fake admin_panel function, the real one is only loaded on admin pages as that's when the admin class from
	 * WordPress SEO is available.
	 *
	 * @return void
	 */
	function admin_panel() {
		return;
	}
}
$wpseo_news_admin_global = new WPSEO_XML_News_Sitemap_Global();