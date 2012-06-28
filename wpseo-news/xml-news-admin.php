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

		$options = get_option( 'wpseo_xml' );

		$content = '<p>' . __( 'You will generally only need XML News sitemap when your website is included in Google News. If it is, check the box below to enable the XML News Sitemap functionality.' ) . '</p>';
		$content .= $this->checkbox( 'enablexmlnewssitemap', __( 'Enable  XML News sitemaps functionality.' ) );
		$content .= '<div id="newssitemapinfo">';
		$content .= $this->textinput( 'newssitemapname', __( 'Google News Publication Name', 'yoast-wpseo' ) );
		$content .= $this->select( 'newssitemap_default_genre', __( 'Default Genre', 'yoast-wpseo' ),
			array(
				"none"          => __( "None", 'yoast-wpseo' ),
				"pressrelease"  => __( "Press Release", 'yoast-wpseo' ),
				"satire"        => __( "Satire", 'yoast-wpseo' ),
				"blog"          => __( "Blog", 'yoast-wpseo' ),
				"oped"          => __( "Op-Ed", 'yoast-wpseo' ),
				"opinion"       => __( "Opinion", 'yoast-wpseo' ),
				"usergenerated" => __( "User Generated", 'yoast-wpseo' ),
			) );

		$content .= $this->textinput( 'newssitemap_default_keywords', __( 'Default Keywords', 'yoast-wpseo' ) );
		$content .= '<p>' . __( 'It might be wise to add some of Google\'s suggested keywords to all of your posts, add them as a comma separated list. Find the list here: ' ) . make_clickable( 'http://www.google.com/support/news_pub/bin/answer.py?answer=116037' ) . '</p>';

		$content .= '<h4>' . __( 'Post Types to include in News Sitemap' ) . '</h4>';

		$content .= '<p>';
		foreach ( get_post_types( array(), 'objects' ) as $posttype ) {
			$sel = '';
			if ( in_array( $posttype->name, array( 'revision', 'nav_menu_item' ) ) )
				continue;
			if ( isset( $options['newssitemap_posttypes'] ) && in_array( $posttype->name, $options['newssitemap_posttypes'] ) )
				$sel = 'checked="checked" ';
			$content .= '<input class="checkbox" id="include' . $posttype->name . '" type="checkbox" name="wpseo_xml[newssitemap_posttypes][' . $posttype->name . ']" ' . $sel . 'value="' . $posttype->name . '"/> <label for="include' . $posttype->name . '">' . $posttype->labels->name . '</label><br class="clear">';
		}
		$content .= '</p>';

		if ( isset( $options['newssitemap_posttypes']['post'] ) ) {
			$content .= '<h4>' . __( 'Post categories to exclude' ) . '</h4>';
			$content .= '<p>';
			foreach ( get_categories() as $cat ) {
				// echo '<pre>'.print_r($cat,1).'</pre>';
				$sel = '';
				if ( isset( $options['newssitemap_excludecats'] ) && in_array( $cat->slug, $options['newssitemap_excludecats'] ) )
					$sel = 'checked="checked" ';
				$content .= '<input class="checkbox" id="catexclude_' . $cat->slug . '" type="checkbox" name="wpseo_xml[newssitemap_excludecats][' . $cat->slug . ']" ' . $sel . 'value="' . $cat->slug . '"/> <label for="catexclude_' . $cat->slug . '">' . $cat->name . ' (' . $cat->count . ' posts)</label><br class="clear">';
			}
			$content .= '</p>';
		}

		$content .= '</div>';

		$this->postbox( 'xmlnewssitemaps', __( 'XML News Sitemap', 'yoast-wpseo' ), $content );

	}

}

$wpseo_news_xml_admin = new WPSEO_XML_News_Sitemap_Admin();