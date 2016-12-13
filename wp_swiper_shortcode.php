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


function wp_swiper_shortcode($atts = array(), $content = "") {
  $html_atts = array('id', 'class');

  $atts = shortcode_atts(array(
    'id' => 'swiper-' . uniqid(),
    'class' => '',
    'pagination' => '.swiper-pagination',
    'paginationClickable' =>  true,
    'nextButton' => '.swiper-button-next',
    'prevButton' => '.swiper-button-prev',
    'scrollbar' => false,
    'loop' => true,
    'before' => '<div class="swiper">',
    'after' => '</div>'
  ), $atts, 'swiper');

  $atts['class'].= strpos($atts['class'], 'swiper-container') === false ? ' swiper-container' : '';

  $options = array();

  // Create output
  $output = "";

  $output.= $atts['before'];
  $output.= "<div";

  foreach ($atts as $name => $value) {
    if (in_array($name, $html_atts)) {
      $output.= ' ' . $name . '="' . $value . '"';
    } else {
      $options[$name] = $value;
    }
  }
  $output.= ">";

  $output.= "<div class=\"swiper-wrapper\">";
  $output.= do_shortcode($content);
  $output.= "</div>";

  if ($atts['pagination']) {
    $output.= '<div class="swiper-pagination"></div>';
  }
  if ($atts['nextButton']) {
    $output.= '<div class="swiper-button-next"></div>';
  }
  if ($atts['prevButton']) {
    $output.= '<div class="swiper-button-prev"></div>';
  }
  if ($atts['scrollbar']) {
    $output.= '<div class="swiper-scrollbar"></div>';
  }
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
    'before_content' => "<div class='swiper-slide-content'>",
    'after_content' => '</div>',
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
