<?php

/**
 * This class handles the addition of the News XML section to the XML Sitemaps page.
 */
class WPSEO_XML_News_Sitemap_Admin extends WPSEO_Admin_Pages {

	/**
	 * @var array Options array
	 */
	private $options = array();

	/**
	 * Class constructor hooking the main function to the action on the XML Sitemaps page.
	 */
	public function __construct() {
		$this->options = $this->set_defaults();

		add_filter( 'wpseo_admin_pages', array( $this, 'add_settings_page' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
	}

	/**
	 * Register the News SEO submenu.
	 */
	function register_settings_page() {
		add_submenu_page( 'wpseo_dashboard', __( 'News SEO', 'wordpress-seo' ), __( 'News SEO', 'wordpress-seo' ),
			'manage_options', 'wpseo_news', array( $this, 'admin_panel' ) );
	}

	/**
	 * Register defaults for the news sitemap
	 *
	 * @since 1.1
	 */
	function set_defaults() {
		$options = get_option( 'wpseo_news' );

		if ( !is_array( $options ) ) {
			$xml_options = get_option( 'wpseo_xml ' );
			if ( is_array( $xml_options ) && $xml_options['enablexmlnewssitemap'] ) {
				$options = $xml_options;
				foreach ( array( 'enablexmlnewssitemap', 'newssitemapname', 'newssitemap_default_genre', 'newssitemap_default_keywords' ) as $opt ) {
					$options[$opt] = $xml_options[$opt];
					unset( $xml_options[$opt] );
				}
				foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $posttype ) {
					if ( isset( $xml_options['newssitemap_include_' . $posttype->name] ) ) {
						$options['newssitemap_include_' . $posttype->name] = $xml_options['newssitemap_include_' . $posttype->name];
						unset( $xml_options['newssitemap_include_' . $posttype->name] );
					}
				}
				update_option( 'wpseo_xml', $xml_options );
			} else {
				$options                             = array();
				$options['newssitemap_include_post'] = 'on';
			}

			$options['dbversion'] = '1.1';
			update_option( 'wpseo_news', $options );
		}

		return $options;
	}

	/**
	 * Make sure the wpseo_news admin page has all the stylesheets etc. from the SEO plugin.
	 *
	 * @param array $admin_pages
	 * @return array
	 */
	function add_settings_page( $admin_pages ) {
		array_push( $admin_pages, 'wpseo_news' );
		return $admin_pages;
	}

	/**
	 * Display the admin panel.
	 */
	public function admin_panel() {
		$this->currentoption = 'wpseo_news';

		echo '<div class="wrap">';

		echo '<a href="http://yoast.com/wordpress/video-seo/">
            <div id="yoast-icon"
                 style="background: url(' . WPSEO_URL . 'images/wordpress-SEO-32x32.png) no-repeat;"
                 class="icon32">
                <br/>
            </div>
        </a>';

		echo '<h2>' . __( 'Google News Sitemaps', 'wordpress-seo' ) . '</h2>';

		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
			$msg = __( 'WordPress SEO News settings updated.', 'wordpress-seo' );

			echo '<div id="message" class="message updated"><p><strong>' . esc_html( $msg ) . '</strong></p></div>';
		}

		echo '<form action="' . admin_url( 'options.php' ) . '" method="post" id="wpseo-conf">';

		settings_fields( 'yoast_wpseo_news_options' );
		echo '<p>' . __( 'You will generally only need XML News sitemap when your website is included in Google News. If it is, check the box below to enable the XML News Sitemap functionality.' ) . '</p>';
		echo $this->checkbox( 'enablexmlnewssitemap', __( 'Enable  XML News sitemaps functionality.' ) );
		echo '<div id="newssitemapinfo">';
		echo $this->textinput( 'newssitemapname', __( 'Google News Publication Name', 'yoast-wpseo' ) );
		echo $this->select( 'newssitemap_default_genre', __( 'Default Genre', 'yoast-wpseo' ),
			array(
				"none"          => __( "None", 'yoast-wpseo' ),
				"pressrelease"  => __( "Press Release", 'yoast-wpseo' ),
				"satire"        => __( "Satire", 'yoast-wpseo' ),
				"blog"          => __( "Blog", 'yoast-wpseo' ),
				"oped"          => __( "Op-Ed", 'yoast-wpseo' ),
				"opinion"       => __( "Opinion", 'yoast-wpseo' ),
				"usergenerated" => __( "User Generated", 'yoast-wpseo' ),
			) );

		echo $this->textinput( 'newssitemap_default_keywords', __( 'Default Keywords', 'yoast-wpseo' ) );
		echo '<p>' . __( 'It might be wise to add some of Google\'s suggested keywords to all of your posts, add them as a comma separated list. Find the list here: ' ) . make_clickable( 'http://www.google.com/support/news_pub/bin/answer.py?answer=116037' ) . '</p>';

		echo '<h3>' . __( 'Post Types to include in News Sitemap' ) . '</h3>';

		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $posttype ) {
			echo $this->checkbox( 'newssitemap_include_' . $posttype->name, $posttype->labels->name, false );
		}

		if ( isset( $this->options['newssitemap_include_post'] ) ) {
			echo '<h3>' . __( 'Post categories to exclude' ) . '</h3>';
			foreach ( get_categories() as $cat ) {
				echo $this->checkbox( 'catexclude_' . $cat->slug, $cat->name . ' (' . $cat->count . ' posts)', false );
			}
		}

		echo '</div>';

		echo '<div class="clear"></div>';

		echo '<div class="submit">';
		echo '<input type="submit" class="button-primary" value="' . __( 'Save Settings', 'wordpress-seo' ) . '"/>';
		echo '</div>';

		echo '</form>';

		echo '</div>';

	}

}

$wpseo_news_xml_admin = new WPSEO_XML_News_Sitemap_Admin();