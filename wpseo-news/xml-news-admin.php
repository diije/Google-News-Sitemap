<?php

/**
 * This class handles the addition of the News XML section to the XML Sitemaps page.
 */
class WPSEO_XML_News_Sitemap_Admin extends WPSEO_Admin_Pages {

	/**
	 * Class constructor hooking the main function to the action on the XML Sitemaps page.
	 */
	public function __construct() {
		add_action( 'wpseo_xmlsitemaps_config', array( &$this, 'admin_panel' ), 10 );
	}

	/**
	 * Display the admin panel.
	 */
	public function admin_panel() {
		$this->currentoption = 'wpseo_xml';

		$options = get_option( $this->currentoption );

		echo '<h2>'.__('Google News Sitemaps','wordpress-seo').'</h2>';
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

		echo '<h4>' . __( 'Post Types to include in News Sitemap' ) . '</h4>';

		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $posttype ) {
			echo $this->checkbox( 'newssitemap_include_'.$posttype->name, $posttype->labels->name , false );
		}

		if ( isset( $options['newssitemap_include_post'] ) ) {
			echo '<h4>' . __( 'Post categories to exclude' ) . '</h4>';
			foreach ( get_categories() as $cat ) {
				echo $this->checkbox( 'catexclude_' . $cat->slug, $cat->name . ' (' . $cat->count . ' posts)', false );
			}
		}

		echo '</div>';

	}

}

$wpseo_news_xml_admin = new WPSEO_XML_News_Sitemap_Admin();