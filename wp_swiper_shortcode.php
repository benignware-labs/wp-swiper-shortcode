<?php
/*
Plugin Name: WP Swiper Shortcode
Plugin URI: http://wordpress.org/extend/plugins/wp-swiper-shortcode
Description: Integrates swiper with wordpress shortcode
Version: 0.0.1
Author: Rafael Nowrotek
Author URI: http://benignware.com
Author Email: mail@benignware.com
Text Domain: swiper
Domain Path: /lang/
Network: false
License: MIT
License URI: https://opensource.org/licenses/MIT

Copyright 2016 benignware.com
*/


require_once('wp_swiper_caption_shortcode.php');
require_once('wp_swiper_post_gallery.php');

function wp_swiper_shortcode_enqueue_scripts() {
  $vendor_assets_dir = 'assets';
  wp_enqueue_script( 'swiper', plugin_dir_url( __FILE__ ) . "assets/Swiper/dist/js/swiper.jquery.js", array( 'jquery' ) );
  wp_enqueue_style( 'swiper', plugin_dir_url( __FILE__ ) . "assets/Swiper/dist/css/swiper.css");
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

function wp_swiper_shortcode($atts = array(), $content = "") {
  $html_atts = array('id', 'class', 'title');
  $custom_atts = array('before', 'before_content', 'after', 'after_content');
  $atts = shortcode_atts(array(
    # Element attributes
    'id' => 'swiper-' . uniqid(),
    'class' => '',
    'title' => '',
    # Custom attributes
    'before' => '',
    'before_content' => '',
    'after' => '',
    'after_content' => '<div class="swiper-pagination"></div>'
      . '<div class="swiper-button-next"></div>'
      . '<div class="swiper-button-prev"></div>'
      . '<div class="swiper-scrollbar"></div>',
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

  // Get camelized swiper options
  $options = array();
  foreach ($atts as $name => $value) {
    if (!in_array($name, $html_atts) && !in_array($name, $custom_atts)) {
      $options[$name] = $value;
    }
  }
  $options = wp_swiper_shortcode_map_keys('wp_swiper_shortcode_camelize', $options);

  // Create output
  $output = "";
  $output.= $atts['before'];
  $output.= "<div";
  foreach ($atts as $name => $value) {
    if (in_array($name, $html_atts)) {
      $output.= ' ' . $name . '="' . $value . '"';
    }
  }
  $output.= ">";
  $output.= $atts['before_content'];
  $output.= "<div class=\"swiper-wrapper\">";
  $output.= do_shortcode($content);
  $output.= "</div>";
  $output.= $atts['after_content'];
  $output.= "</div>";

  $output.= $atts['after'];

  $output.= "<script type=\"text/javascript\">//<![CDATA[\n(function($, window) {\n";
  $output.= "\t$('#{$atts['id']}').swiper(" . json_encode($options, JSON_UNESCAPED_SLASHES) . ");\n";
  $output.= "})(jQuery, window)\n//]]></script>\n";
  return $output;
}

add_shortcode('swiper', 'wp_swiper_shortcode');

function wp_swiper_slide_shortcode($atts = array(), $content = "") {
  $html_atts = array('id', 'class');
  $options = array();

  $atts = shortcode_atts(array(
    'class' => 'swiper-slide',
    'before_content' => '',
    'after_content' => '',
  ), $atts, 'swiper_slide');

  $atts['class'].= strpos($atts['class'], 'swiper-container') === false ? ' swiper-slide' : '';

  $json = json_encode($options, JSON_UNESCAPED_SLASHES);

  // Create output
  $output = "";
  $output.= "<div";
  foreach ($atts as $name => $value) {
    if (in_array($name, $html_atts)) {
      $output.= ' ' . $name . '="' . $value . '"';
    } else {
      $options[$name] = $value;
    }
  }
  $output.= ">";

  $pattern = "~\[\s*swiper_caption[^\]]*\](.*(?!swiper_caption))~";
  $hit = preg_match($pattern, $content, $match);
  $caption = $match ? do_shortcode($match[0]) : "";
  $content = preg_replace($pattern, "", $content);

  $output.= $options['before_content'];
  $output.= do_shortcode( $content );
  $output.= $options['after_content'];

  $output.= $caption;

  $output.= "</div>";
  return $output;
}

add_shortcode('swiper_slide', 'wp_swiper_slide_shortcode');


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
?>
