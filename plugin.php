<?php

/**
 * Plugin Name: Swiper Shortcode
 * Plugin URI: https://github.com/benignware-labs/wp-swiper-shortcode
 * Description: Swiper Integration for Wordpress
 * Author: Rafael Nowrotek
 * Author URI: http://benignware.com/
 * Version: 0.1.0-beta.2
 *
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functions
 */

require_once plugin_dir_path( __FILE__ ) . 'lib/helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'lib/functions.php';


/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';


add_filter('swiper_options', function($options = array(), $params = array()) {
	$options = array_merge(
		array_filter($options, function ($key) {
			return in_array($key, array(
				'speed',
				'space_between',
				'slides_per_view',
				'centered_slides',
				'parallax',
				'autoplay',
				'loop',
				'free_mode',
				'watch_slides_progress',
				'watch_slides_visibility'
			));
		}, ARRAY_FILTER_USE_KEY),
		array(
			'navigation' => $options['navigation'] ? array_merge(
				array(
					'nextEl' => '.swiper-button-next',
					'prevEl' => '.swiper-button-prev'
				),
				is_array($options['navigation']) ? $options['navigation'] : array()
			) : null,
			'pagination' => $options['pagination'] ? array_merge(
				array(
					'el' => '.swiper-pagination',
					'clickable' => false
				),
				is_array($options['pagination']) ? $options['pagination'] : array()
			) : null,
			'scrollbar' => $options['scrollbar'] ? array_merge(
				array(
					'el' => '.swiper-scrollbar'
				),
				is_array($options['scrollbar']) ? $options['scrollbar'] : array()
			) : null,
			'thumbs' => (is_array($options['thumbs']) || $options['thumbs']) ? array_merge(
				array(
					'slides_per_view' => 3,
					'space_between' => 0,
      		'free_mode' => true,
      		'watch_slides_visibility' => true,
      		'watch_slides_progress' => true
				),
				is_array($options['thumbs']) ? $options['thumbs'] : array()
			) : null
		)
	);

	$options = array_filter($options, function($value) {
		return $value !== null;
	});

	return $options;
}, 10, 2);

// Swiper Shortcodes
add_shortcode('swiper', 'swiper_shortcode');
add_shortcode('swiper_slide', 'swiper_slide_shortcode');
add_shortcode('swiper_gallery', 'swiper_gallery_shortcode');

register_swiper_theme('light', array(
	'classes' => array(
		'swiper-button-next' => 'swiper-button-white',
		'swiper-button-prev' => 'swiper-button-white',
		'swiper-pagination' => 'swiper-pagination-white',
		'swiper-scrollbar' => 'swiper-scrollbar-white'
	)
));

register_swiper_theme('dark', array(
	'classes' => array(
		'swiper-button-next' => 'swiper-button-black',
		'swiper-button-prev' => 'swiper-button-black',
		'swiper-pagination' => 'swiper-pagination-black',
		'swiper-scrollbar' => 'swiper-scrollbar-black'
	)
));

add_filter( 'post_gallery', function($output = '', $atts = null) {
	$params = array_merge(array(
		'fit' => 'cover'
	), $atts);

	return swiper_gallery_shortcode($params, $output);
}, 11, 2 );
