<?php

class WPSEO_XML_News_Sitemap {

	public function __construct() {
		$options = get_option('wpseo');
		
		add_action( 'wpseo_dashboard', array(&$this, 'admin_panel' ), 10, 1 );
		if ( !isset($options['enablexmlnewssitemap']) || !$options['enablexmlnewssitemap'])
			return;

		add_action( 'init', array( $this, 'init' ) );
		if ( is_admin() ) {
			//add_action( 'publish_post', array(&$this, 'ping') ); FIXME

			add_filter( 'wpseo_save_metaboxes', array(&$this, 'save_meta_boxes' ), 10, 1 );
			
			add_action( 'wpseo_tab_header', array(&$this, 'tab_header') );
			add_action( 'wpseo_tab_content', array(&$this, 'tab_content') );
		} else {
			add_action( 'wpseo_head', array(&$this, 'head') );	
			add_filter( 'wpseo_sitemap_index', array( $this, 'add_to_index' ) );		
		}
	}

	public function tab_header() {
		global $post;

		$options = get_option("wpseo");
		if ( isset ($options['newssitemap_posttypes']) && $options['newssitemap_posttypes'] != '' ) {
			foreach ($options['newssitemap_posttypes'] as $post_type) {
				if ($post->post_type == $post_type)
					echo '<li class="news"><a href="javascript:void(null);">'.__('Google News').'</a></li>';
			}
		} else {
			if ($post->post_type == 'post')
				echo '<li class="news"><a href="javascript:void(null);">'.__('Google News').'</a></li>';
		}
	}

	public function tab_content() {
		global $wpseo_metabox, $post;
		
		$options = get_option("wpseo");
		if ( isset($options['newssitemap_posttypes']) && $options['newssitemap_posttypes'] != '' ) {
			if ( ! in_array($post->post_type, $options['newssitemap_posttypes']) )
				return;
		} else {
			if ($post->post_type != 'post')
				return;
		}

		$content = '';
		foreach( $this->get_meta_boxes() as $meta_box) {
			$content .= $wpseo_metabox->do_meta_box( $meta_box );
		}
		$wpseo_metabox->do_tab( 'news', __('Google News'), $content );
	}
	
	public function ping() {
		// Ping Google. Just do it. Not optional because if you don't want to ping Google you don't need no freaking news sitemap.
		wp_remote_get( 'http://www.google.com/webmasters/tools/ping?sitemap=' . home_url('news_sitemap.xml') );
	}

    public function init() {
    	$GLOBALS['wpseo_sitemaps']->register_sitemap( 'wpseo_news', array( $this, 'build_news_sitemap' ), 'news_sitemap\.xml/?$' );
    }

    function add_to_index( $str ) {
		$result 	= strtotime( get_lastpostmodified( 'gmt' ) );
		$date 		= date( 'c', $result );
	
		$str .= '<sitemap>' . "\n";
		$str .= '<loc>' . home_url( 'news_sitemap.xml' ) . '</loc>' . "\n";
		$str .= '<lastmod>' . $date . '</lastmod>' . "\n";
		$str .= '</sitemap>' . "\n";
		return $str;
    }

	public function build_news_sitemap() {
		global $wpdb;

		$options = get_option('wpseo');

		if ( isset( $options['newssitemap_posttypes'] ) && $options['newssitemap_posttypes'] != '' ) {
			$post_types = array_map( 'sanitize_key', $options['newssitemap_posttypes'] );
			$post_types = "'".implode( "','", $post_types )."'";
		} else {
			$post_types = "'post'";
		}

		// Get posts for the last two days only, credit to Alex Moss for this code.
		$items = $wpdb->get_results("SELECT ID, post_content, post_name, post_author, post_parent, post_modified_gmt, post_date, post_date_gmt, post_title, post_type
									FROM $wpdb->posts
									WHERE post_status='publish'
									AND (DATEDIFF(CURDATE(), post_date_gmt)<=2)
									AND post_type IN ($post_types)
									ORDER BY post_date_gmt DESC
									LIMIT 0, 1000");


		$output = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
		xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" 
		xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

		if ( !empty($items) ) {
			foreach ($items as $item) {
				$item->post_status = 'publish';
				
				if ( false != wpseo_get_value( 'newssitemap-include', $item->ID ) && wpseo_get_value( 'newssitemap-include', $item->ID ) == 'off' ) 
					continue;

				if ( false != wpseo_get_value( 'meta-robots', $item->ID ) && strpos( wpseo_get_value( 'meta-robots', $item->ID ), 'noindex' ) !== false )
					continue;

				if ( 'post' == $item->post_type ) {
					$cats = get_the_terms( $item->ID, 'category' );
					$exclude = 0;
					foreach ( $cats as $cat ) {
						if ( isset( $options['newssitemap_excludecats'][$cat->slug] ) ) {
							$exclude++;
						}
					}
					if ( $exclude >= count($cats) )
						continue;
				}
				
				$publication_name = ! empty( $options['newssitemapname'] ) ? $options['newssitemapname'] : get_bloginfo('name');				
				$publication_lang = substr(get_locale(),0,2);

				$keywords = array();
				$tags = get_the_terms( $item->ID, 'post_tag' );
				if ( $tags )
					foreach ( $tags as $tag )
						$keywords[] = $tag->name;

				// TODO: add suggested keywords to each post based on category, next to the entire site
				if ( isset( $options['newssitemap_default_keywords'] ) && $options['newssitemap_default_keywords'] != '' )
					array_merge( $keywords, explode( ',', $options['newssitemap_default_keywords'] ) );
				$keywords = strtolower( implode( ', ', $keywords ) );

				$genre = wpseo_get_value( 'newssitemap-genre', $item->ID );
				if ( is_array( $genre ) )
					$genre = implode( ',', $genre );

				if ( $genre == '' && isset( $options['newssitemap_default_genre'] ) && $options['newssitemap_default_genre'] != '' )
					$genre = $options['newssitemap_default_genre'];
				$genre = trim( preg_replace('/^none,?/','',$genre) );

				$stock_tickers = trim( wpseo_get_value('newssitemap-stocktickers') );
				if ( $stock_tickers != '' )
					$stock_tickers = "\t\t<stock_tickers>" . htmlspecialchars( $stock_tickers ) . '</stock_tickers>' . "\n";

				$output .= '<url>' . "\n";
				$output .= "\t<loc>" . get_permalink( $item ) . '</loc>' . "\n";
				$output .= "\t<news:news>\n";
				$output .= "\t\t<news:publication>" . "\n";
				$output .= "\t\t\t<news:name>" . htmlspecialchars( $publication_name ) . '</news:name>' . "\n";
				$output .= "\t\t\t<news:language>" . htmlspecialchars( $publication_lang ) . '</news:language>' . "\n";
				$output .= "\t\t</news:publication>\n";
				$output .= "\t\t<news:genres>" . htmlspecialchars( $genre ) . '</news:genres>' . "\n";
				$output .= "\t\t<news:publication_date>" . mysql2date( 'c', $item->post_date_gmt ) . '</news:publication_date>' . "\n";
				$output .= "\t\t<news:title>" . htmlspecialchars( $item->post_title ) . '</news:title>' . "\n";
				$output .= "\t\t<news:keywords>" . htmlspecialchars( $keywords ) . '</news:keywords>' . "\n";
				$output .= $stock_tickers;
				$output .= "\t</news:news>\n";

				$images = array();
				if ( preg_match_all( '/<img [^>]+>/', $item->post_content, $matches ) ) {
					foreach ( $matches[0] as $img ) {
						// FIXME: get true caption instead of alt / title
						if ( preg_match( '/src=("|\')([^"|\']+)("|\')/', $img, $match ) ) {
							$src = $match[2];
							if ( strpos($src, 'http') !== 0 ) {
								if ( $src[0] != '/' )
									continue;
								$src = get_bloginfo('url') . $src;
							}

							if ( $src != esc_url( $src ) )
								continue;

							if ( isset( $url['images'][$src] ) )
								continue;

							$image = array();
							if ( preg_match( '/title=("|\')([^"\']+)("|\')/', $img, $match ) )
								$image['title'] = str_replace( array('-','_'), ' ', $match[2] );

							if ( preg_match( '/alt=("|\')([^"\']+)("|\')/', $img, $match ) )
								$image['alt'] = str_replace( array('-','_'), ' ', $match[2] );

							$images[$src] = $image;
						}
					}
				}
					
				if ( isset($images) && count($images) > 0 ) {
					foreach( $images as $src => $img ) {
						$output .= "\t\t<image:image>\n";
						$output .= "\t\t\t<image:loc>".htmlspecialchars( $src )."</image:loc>\n";
						if ( isset($img['title']) )
							$output .= "\t\t\t<image:title>".htmlspecialchars( $img['title'] )."</image:title>\n";
						if ( isset($img['alt']) )
							$output .= "\t\t\t<image:caption>".htmlspecialchars( $img['alt'] )."</image:caption>\n";
						$output .= "\t\t</image:image>\n";
					}
				}

				$output .= '</url>' . "\n";
     		}
		}

		$output .= '</urlset>';
		$GLOBALS['wpseo_sitemaps']->set_sitemap( $output );
		$GLOBALS['wpseo_sitemaps']->set_stylesheet(
			'<?xml-stylesheet type="text/xsl" href="'.WP_PLUGIN_URL.'/wordpress-seo-modules/wpseo-news/xml-news-sitemap.xsl"?>'
		);
	}

	public function get_meta_boxes() {
		$mbs = array();
		$options = get_option('wpseo');
		$stdgenre = ( isset( $options['newssitemap_default_genre'] ) ) ? $options['newssitemap_default_genre'] : 'blog';
		$mbs['newssitemap-include'] = array(
			"name" => "newssitemap-include",
			"type" => "checkbox",
			"std" => 'on',
			"title" => __("Include in News Sitemap")
		);
		$mbs['newssitemap-genre'] = array(
			"name" => "newssitemap-genre",
			"type" => "multiselect",
			"std" => $stdgenre,
			"title" => __("Google News Genre", 'yoast-wpseo'),
			"description" => __("Genre to show in Google News Sitemap.", 'yoast-wpseo'),
			"options" => array(
				"pressrelease" => __("Press Release", 'yoast-wpseo'),
				"satire" => __("Satire", 'yoast-wpseo'),
				"blog" => __("Blog", 'yoast-wpseo'),
				"oped" => __("Op-Ed", 'yoast-wpseo'),
				"opinion" => __("Opinion", 'yoast-wpseo'),
				"usergenerated" => __("User Generated", 'yoast-wpseo'),
			),
		);
		$mbs['newssitemap-original'] = array(
			"name" => "newssitemap-original",
			"std" => "",
			"type" => "text",
			"title" => __("Original Source", 'yoast-wpseo'),
			"description" => __('Is this article the original source of this news? If not, please enter the URL of the original source here. If there are multiple sources, please separate them by a pipe symbol: | .', 'yoast-wpseo'),
		);
		$mbs['newssitemap-stocktickers'] = array(
			"name" => "newssitemap-stocktickers",
			"std" => "",
			"type" => "text",
			"title" => __("Stock Tickers", 'yoast-wpseo'),
			"description" => __('A comma-separated list of up to 5 stock tickers of the companies, mutual funds, or other financial entities that are the main subject of the article. Each ticker must be prefixed by the name of its stock exchange, and must match its entry in Google Finance. For example, "NASDAQ:AMAT" (but not "NASD:AMAT"), or "BOM:500325" (but not "BOM:RIL").', 'yoast-wpseo'),
		);
		return $mbs;
	}
	
	public function save_meta_boxes( $mbs ) {
		$mbs = array_merge( $mbs, $this->get_meta_boxes() );
		return $mbs;
	}
	
	public function admin_panel( $wpseo_admin ) {
		$options = get_option('wpseo');

		// echo '<pre>'.print_r($options,1).'</pre>';
		
		$content = '<p>'.__('You will generally only need XML News sitemap when your website is included in Google News. If it is, check the box below to enable the XML News Sitemap functionality.').'</p>';
		$content .= $wpseo_admin->checkbox('enablexmlnewssitemap',__('Enable  XML News sitemaps functionality.'));
		$content .= '<div id="newssitemapinfo">';
		$content .= $wpseo_admin->textinput('newssitemapname',__('Google News Publication Name', 'yoast-wpseo'));
		$content .= $wpseo_admin->select('newssitemap_default_genre', __('Default Genre', 'yoast-wpseo'), 
			array(
				"none" => __("None", 'yoast-wpseo'),
				"pressrelease" => __("Press Release", 'yoast-wpseo'),
				"satire" => __("Satire", 'yoast-wpseo'),
				"blog" => __("Blog", 'yoast-wpseo'),
				"oped" => __("Op-Ed", 'yoast-wpseo'),
				"opinion" => __("Opinion", 'yoast-wpseo'),
				"usergenerated" => __("User Generated", 'yoast-wpseo'),
			));

		$content .= $wpseo_admin->textinput('newssitemap_default_keywords',__('Default Keywords', 'yoast-wpseo'));
		$content .= '<p>'.__('It might be wise to add some of Google\'s suggested keywords to all of your posts, add them as a comma separated list. Find the list here: ').make_clickable('http://www.google.com/support/news_pub/bin/answer.py?answer=116037').'</p>';

		$content .= '<h4>'.__( 'Post Types to include in News Sitemap' ).'</h4>';
		
		$content .= '<p>';
		foreach (get_post_types(array(), 'objects') as $posttype) {
			$sel = '';
			if ( in_array($posttype->name, array('revision','nav_menu_item') ) )
				continue;
			if ( isset( $options['newssitemap_posttypes'] ) && in_array( $posttype->name, $options['newssitemap_posttypes'] ) )
				$sel = 'checked="checked" ';
			$content .= '<input class="checkbox" id="include'.$posttype->name.'" type="checkbox" name="wpseo[newssitemap_posttypes]['.$posttype->name.']" '.$sel.'value="'.$posttype->name.'"/> <label for="include'.$posttype->name.'">'.$posttype->labels->name.'</label><br class="clear">';
		}
		$content .= '</p>';
		
		if ( isset( $options['newssitemap_posttypes']['post'] ) ) {
			$content .= '<h4>'.__('Post categories to exclude').'</h4>';
			$content .= '<p>';
			foreach ( get_categories() as $cat ) {
				// echo '<pre>'.print_r($cat,1).'</pre>';
				$sel = '';
				if ( isset( $options['newssitemap_excludecats'] ) && in_array( $cat->slug, $options['newssitemap_excludecats'] ) )
					$sel = 'checked="checked" ';
				$content .= '<input class="checkbox" id="catexclude_'.$cat->slug.'" type="checkbox" name="wpseo[newssitemap_excludecats]['.$cat->slug.']" '.$sel.'value="'.$cat->slug.'"/> <label for="catexclude_'.$cat->slug.'">'.$cat->name.' ('.$cat->count.' posts)</label><br class="clear">';
			}
			$content .= '</p>';
		}
		
		$content .= '</div>';
		
		$wpseo_admin->postbox('xmlnewssitemaps',__('XML News Sitemap', 'yoast-wpseo'),$content);
		
	}
	
	public function head() {
		global $post;
		if ( is_single() ) {
			$original_source = wpseo_get_value( 'newssitemap-original', $post->ID );
			if ( $original_source == '' ) {
				echo "\t".'<link rel="original-source" href="'.get_permalink( $post->ID ).'" />'."\n";
			} else {
				$sources = explode( '|', $original_source );
				foreach ( $sources as $source )
					echo "\t".'<link rel="original-source" href="'.$source.'" />'."\n";
			}
		}
	}
}

$wpseo_news_xml = new WPSEO_XML_News_Sitemap();
