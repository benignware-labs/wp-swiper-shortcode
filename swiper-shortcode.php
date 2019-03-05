<?php
/*
Plugin Name: Swiper Shortcode
Plugin URI: https://github.com/benignware-labs/wp-swiper-shortcode
Description: Swiper integration for Wordpress
Version: 0.0.8
Author: Rafael Nowrotek
Author URI: http://benignware.com
Author Email: mail@benignware.com
Text Domain: swiper
Domain Path: /lang/
Network: false
License: MIT
License URI: https://opensource.org/licenses/MIT

Copyright 2016-2019 benignware.com
*/

require_once('swiper-shortcode-helpers.php');
require_once('swiper-shortcode-slide.php');
require_once('swiper-shortcode-caption.php');
require_once('swiper-shortcode-gallery.php');

function wp_swiper_shortcode_enqueue_scripts() {
  wp_enqueue_script( 'swiper-shortcode', plugin_dir_url( __FILE__ ) . "dist/wp-swiper-shortcode.js" );
  wp_enqueue_style( 'swiper-shortcode', plugin_dir_url( __FILE__ ) . "dist/swiper-shortcode.css" );
}

add_action( 'wp_enqueue_scripts', 'wp_swiper_shortcode_enqueue_scripts' );

function wp_swiper_shortcode_camelize($string) {
  return lcfirst(join(array_map('ucfirst', explode('_', $string))));
}

function wp_swiper_shortcode_map_keys($f, $xs) {
  $out = array();
  foreach ($xs as $key => $value) {
    $out[$f($key)] = is_array($value) ? wp_swiper_shortcode_map_keys($f, $value) : $value;
  }
  return $out;
}

function wp_swiper_shortcode_empty_paragraph_fix( $content ) {
  $shortcodes = array( 'swiper', 'swiper_slide' );
  foreach ( $shortcodes as $shortcode ) {
    $array = array (
      '<p>[' . $shortcode => '[' .$shortcode,
      '<p>[/' . $shortcode => '[/' .$shortcode,
      $shortcode . ']</p>' => $shortcode . ']',
      $shortcode . ']<br />' => $shortcode . ']'
    );
    $content = strtr( $content, $array );
  }
  return $content;
}

add_filter( 'the_content', 'wp_swiper_shortcode_empty_paragraph_fix' );

function wp_swiper_shortcode($atts = array(), $content = "") {
  $html_att_names = array('id', 'class', 'title');
  $custom_att_names = array('before', 'before_content', 'after', 'after_content');
  $atts = shortcode_atts(array(
    # Element attributes
    'id' => 'swiper-shortcode-' . uniqid(),
    'class' => '',
    'title' => '',
    # Custom attributes
    'before' => '',
    'before_content' => '',
    'after' => '',
    'after_content' => '<div class="swiper-pagination"></div>'
      . '<div class="swiper-button-next"></div>'
      . '<div class="swiper-button-prev"></div>',
    # Swiper options
    'pagination' => array(
      'el' => '.swiper-pagination',
      'clickable' =>  true
    ),
    'navigation' => array(
      'next_el' => '.swiper-button-next',
      'prev_el' => '.swiper-button-prev'
    ),
    'scrollbar' => false,
    'loop' => true
  ), $atts, 'swiper');

  $atts['class'].= strpos($atts['class'], 'swiper-container') === false ? ' swiper-container' : '';

  $html_atts = wp_swiper_shortcode_sanitize_atts($atts, $html_att_names);
  $custom_atts = wp_swiper_shortcode_sanitize_atts($atts, $custom_att_names);

  // All the rest goes to Swiper
  $options = array_diff_assoc($atts, array_merge($html_atts, $custom_atts));

  // Camelize Swiper options
  $options = wp_swiper_shortcode_map_keys('wp_swiper_shortcode_camelize', $options);

  // Create output
  $output = "";
  $output.= $atts['before'];
  $output.= "<div";
  foreach ($html_atts as $name => $value) {
    $output.= ' ' . $name . '="' . $value . '"';
  }
  $output.= ">";
  $output.= $custom_atts['before_content'];
  $output.= "<div class=\"swiper-wrapper\">";
  $output.= do_shortcode($content);
  $output.= "</div>";
  $output.= $custom_atts['after_content'];
  $output.= "</div>";

  $output.= $atts['after'];

  $output.= "<script type=\"text/javascript\">//<![CDATA[\n(function() {\n";
  $output.= "\tnew Swiper('#{$html_atts['id']}', " . json_encode($options, JSON_UNESCAPED_SLASHES) . ");\n";
  $output.= "})()\n//]]></script>\n";
  return $output;
}

add_shortcode('swiper', 'wp_swiper_shortcode');

?>
