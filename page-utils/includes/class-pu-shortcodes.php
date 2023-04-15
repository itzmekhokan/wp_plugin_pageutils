<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * PU_Shortcodes class.
 */
class PU_Shortcodes {
	/**
	 * Init shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'faq'                    	=> __CLASS__ . '::shortcode_faq_output',
			'current_date'           	=> __CLASS__ . '::shortcode_current_date_output',
			'simple_sitemap'         	=> __CLASS__ . '::shortcode_simple_sitemap_output',
			'star'         				=> __CLASS__ . '::shortcode_star_output',
			'wp-svg-icons'				=> __CLASS__ . '::shortcode_wp_svg_icons_output',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "pu_{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * FAQ (shortcode).
	 *
	 * @param  array $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode_faq_output( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => '',
				'order' => 'ASC',
				'output' => 'html',
			), $atts, 'faq'
		);

		if( !$atts['id'] ) return;

		// scripts
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'page_utils_js', PU()->plugin_url() . '/assets/js/page_utils.js', array( 'jquery' ), PU_VERSION, true );
		wp_enqueue_style( 'page_utils_css', PU()->plugin_url() . '/assets/css/page_utils.css', array(), PU_VERSION );

		$id = $atts['id'];
		$order = $atts['order'];
		$sql = "WHERE faq_id=$id ORDER BY faq_order $order";
		$results = PU()->query->get_all_qna( $sql );
		$faq_data = PU()->query->get_faq( $id );
		$atts['data'] = $results;
		$atts['header'] = $faq_data->faq_name;

		return get_pu_template_html( 'html-faq-shortcode', $atts );
	}

	/**
	 * Current Date (shortcode).
	 *
	 * @param  array $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode_current_date_output( $atts ) {
		$atts = shortcode_atts(
			array(
				'format' => 'Y-m-d',
			), $atts, 'current_date'
		);

		$format = ( !$atts['format'] ) ? 'Y-m-d' : $atts['format'];
		ob_start();
		echo date($format);
		$date = ob_get_contents();
		ob_end_clean();
		return $date;
	}

	/**
	 * Simple sitemap (shortcode).
	 *
	 * @param  array $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode_simple_sitemap_output( $atts ) {
		global $post;
		$atts = shortcode_atts(
			array(
				'id' => uniqid(),
				'post_types' => 'page',
				'orderby' => 'name',
				'order' => 'ASC',
				'exclude' => '',
				'links' => 'true',
				'links_target' => '_blank',
				'wrapper_tag' => 'ul',
				'types_tag' => 'h3',
				'item_tag' => 'li', // this is only valid depending on wrapper_tag
			), $atts, 'simple_sitemap'
		);

		$id = ( $atts['id'] ) ? $atts['id'] : uniqid();
		$post_types = ( $atts['post_types'] ) ? array_map( 'trim', explode( ',', $atts['post_types'] ) ) : array('page');
		$links = ( $atts['links'] ) ? $atts['links'] : false;
		$links_target = ( $atts['links_target'] ) ? $atts['links_target'] : '_blank';
		$orderby = ( $atts['orderby'] ) ? $atts['orderby'] : 'name';
		$order = ( $atts['order'] ) ? $atts['order'] : 'ASC';

		$registered_public_post_types = get_post_types( array( 'public' => true ) );
		
		$enabled_exclude_post = pu_get_settings( 'exclude_current_post_sitemap' );
		$exclude = '';
		if( $enabled_exclude_post ){
			$exclude = ($post && $enabled_exclude_post == 'enabled' ) ? array( $post->ID ) : array();
		}elseif( $post ){
			$exclude = array( $post->ID );
		}
		if( $atts['exclude'] && !empty( $atts['exclude'] ) ){

		}
		if( $exclude ) {
			$exclude = ( $atts['exclude'] && !empty( $atts['exclude'] ) ) ? array_merge( $exclude, explode( ',', $atts['exclude'] ) ) : $exclude;
		} else {
			$exclude = '';
		}
		
		ob_start();

		echo '<div class="pu-sitemap-container sitemap-' . $id . '">';
		// render sitemap for post types 
		foreach ( $post_types as $type ) {
			if( !array_key_exists( $type, $registered_public_post_types ) ) continue;
			$pt_obj = get_post_type_object( $type );
			// types label tag
			echo ( $atts['types_tag'] ) ? '<' . $atts['types_tag'] . ' class="pu-sitemap-types '.$type.'">' : '<h3 class="pu-sitemap-types '.$type.'">';
			echo $pt_obj->labels->name;
			// types label tag end
			echo ( $atts['types_tag'] ) ? '</' . $atts['types_tag'] . '>' : '</h3>';
			$wrapper_class = 'pu-sitemap-wrapper ' . $type;
			// wrapper start
			echo ( $atts['wrapper_tag'] ) ? '<' . $atts['wrapper_tag'] . ' class="'.$wrapper_class.'">' : '<ul class="'.$wrapper_class.'">';
			// fetch all assiciated posts
			$p_query = array(
			    'post_type' 		=> $type,
			    'posts_per_page'	=> -1,
			    'orderby'			=> $orderby,
			    'order'				=> $order,
			    'exclude'			=> $exclude,
			    'fields'			=> 'ids'   
			);
			$p_posts = get_posts( $p_query );
			if( $p_posts){
				foreach ( $p_posts as $post_id ) {

					echo ( $atts['item_tag'] ) ? '<' . $atts['item_tag'] . ' class="pu-map-item '.$post_id.'">' : '<li class="pu-map-item '.$post_id.'">';
	                if( $links ){
	                	echo '<a href="'.get_permalink($post_id).'" target="'.$links_target.'">'.get_the_title($post_id).'</a>';
	                }else{
	                	echo get_the_title($post_id);
	                }
	                echo ( $atts['item_tag'] ) ? '</' . $atts['item_tag'] . '>' : '</li>';
				}
			}
                
			// wrapper end
			echo ( $atts['wrapper_tag'] ) ? '</' . $atts['wrapper_tag'] . '>' : '</ul>';
		}
		
		echo '</div>';

		$sitemap = ob_get_contents();
		ob_end_clean();

		return $sitemap;
	}

	/**
	 * Star (shortcode).
	 *
	 * @param  array $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode_star_output( $atts ) {
		$atts = shortcode_atts(
			array(
				'rating' => '0',
				'type' => 'rating',
				'number' => '0',
				'max' => '5',
				'numeric' => 'no',
			), $atts, 'star'
		);
		extract( $atts );

		// styles & scripts 
		wp_enqueue_style( 'dashicons' );

		if( $max == NULL ) {
			$max = 5;
		}

		/* Display tyle: rating */
		if( $type == "rating" ) {

			if( is_float( $rating ) ) {
				$rating = preg_replace( '/\.?0+$/', '', (int)$rating );
			}
			$empty_rating = $max - $rating;

			if( is_float( $empty_rating ) ) {
				$filled = floor( $rating );
				$half = 1;
				$empty = floor($empty_rating);
			} else {
				$filled = $rating;
				$half = 0;
				$empty = $empty_rating;
			}

			if( $max < $filled ) {
				$filled = $max;
			}
		}

		/* Display tyle: percent */
		if( $type == "percent" ) {
			$fill_percentage = $max * ( $rating * 0.01 );
			$empty_percentage = $max - $fill_percentage;

			if( preg_match( '/^\d+\.\d+$/', $fill_percentage ) ) {
				$filled = floor( $fill_percentage );
				$half = 1;
				$empty = floor( $empty_percentage );
			} else {
				$filled = $fill_percentage;
				$half = 0;
				$empty = $empty_percentage;
			}
		}
		// rating
		if( !ctype_digit( strval( $empty ) ) ) {
			$empty = 0;
		}
		ob_start();
		$ssr_html = "<span class=\"shortcode-star-rating\">";
		$ssr_html .= str_repeat( '<span class="dashicons dashicons-star-filled"></span>', (int)$filled );
		$ssr_html .= str_repeat( '<span class="dashicons dashicons-star-half"></span>', $half );
		$ssr_html .= str_repeat( '<span class="dashicons dashicons-star-empty"></span>', $empty );

		if( $numeric == "yes" ) {
			if( $type == "percent" ) {
				$ssr_html .= "<span class=\"ssr-int\">(" . $rating . "%)</span>";
			} else {
				$ssr_html .= "<span class=\"ssr-int\">(" . $rating . "/" . $max . ")</span>";
			}
		}

		$ssr_html .= "</span>";
		echo $ssr_html;
		$ssr_html = ob_get_contents();
		ob_end_clean();

		return $ssr_html;

	}

	/**
	 * WP SVG icons (shortcode).
	 *
	 * @param  array $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode_wp_svg_icons_output( $atts ) {
		$atts = shortcode_atts(
			array(
				'icon' => '',
				'wrap' => '',
				'class' => '',
				'size' => '',
				'custom_icon' => '',
				'link' => '',
				'new_tab' => '',
				'color' => ''
			), $atts, 'wp-svg-icons'
		);
		extract( $atts );

		// styles & scripts
		wp_enqueue_style( 'page_utils_css', PU()->plugin_url() . '/assets/css/page_utils.css', array(), PU_VERSION );

		// if icon and custom icon is left blank
		if( !isset( $icon ) || empty( $icon ) ) {
			if( !isset( $custom_icon ) || empty( $custom_icon ) ) {
				return __( 'Whoops! It looks like you forgot to specify an icon.' , 'page-utils' );
			}
		}

		// if the user forgot to set a wrap
		if( !isset( $wrap ) || empty( $wrap ) ) {
			return __( 'Whoops! It looks like you forgot to specify your html tag.' , 'page-utils' );
		}

		// if the user has set extra classes for the element
		if( !empty( $class ) ) {
			$classes = ' ' . esc_attr( $class );
		}

		// if the user has set a custom icon
		if( !empty( $custom_icon ) ) { // display a custom icon
			$classes =  ' class="wp-svg-custom-' . trim( esc_attr( $custom_icon . ' ' . $custom_icon . ' ' . $class ) ) . '"';
		} else { // display our default icon
			$classes =  ' class="wp-svg-' . trim( esc_attr( $icon . ' ' . $icon . ' ' . $class ) ) . '"';
		}

		// create an array to populate with some styles
		$styles_array = array();

		// if the user has a set a custom icon size, set up our variable
		if( !empty( $size ) ) {
			$icon_size = 'font-size:' . esc_attr( $size ) . ';';
			$styles_array[0] = $icon_size;
		} else {
			$icon_size = '';
		}

		// if the user has a set a custom icon color, set up our variable
		if( !empty( $color ) ) {
			$icon_color = 'color:' . esc_attr( $color ) . ';';
			$styles_array[1] = $icon_color;
		} else {
			$icon_color = '';
		}

		// build up an array of styles,
		// to pass to our element
		if( !empty( $styles_array ) ) {
			$styles = 'style="' . esc_attr( implode( '' , $styles_array ) ) . '"';
		} else {
			$styles = '';
		}

		$svg_icons = '';
		// check if this icon should be set as a link
		if( !empty( $link ) ) {
			// wrap our element in an anchor tag, for the link
			// don't forget to esc_url
			if( $new_tab == '1' ) {
				$svg_icons = '<a href="' . esc_url( $link ) . '" target="_blank"><' . esc_attr( $wrap ) . $classes . $styles . '></' . esc_attr( $wrap ) . '></a>';
			} else {
				$svg_icons = '<a href="' . esc_url( $link ) . '"><' . esc_attr( $wrap ) . $classes . $styles . '></' . esc_attr( $wrap ) . '></a>';
			}
		} else {
			// return the default icon
			$svg_icons = '<' . esc_attr( $wrap ) . $classes . $styles . '></' . esc_attr( $wrap ) . '>';
		}

		ob_start();
		echo $svg_icons;
		$svg_icons = ob_get_contents();
		ob_end_clean();
		return $svg_icons;
	}
}