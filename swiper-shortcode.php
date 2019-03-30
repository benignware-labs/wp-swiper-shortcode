<?php
/*
Plugin Name: Swiper Shortcode
Plugin URI: https://github.com/benignware-labs/wp-swiper-shortcode
Description: Swiper integration for Wordpress
Version: 0.0.10
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

// Gutenberg breaks Wordpress galleries
// add_filter('use_block_editor_for_post', `__return_false`);

require_once('swiper-shortcode-helpers.php');
require_once('swiper-shortcode-slide.php');
require_once('swiper-shortcode-caption.php');
require_once('swiper-shortcode-gallery.php');

function wp_swiper_shortcode_enqueue_scripts() {
  wp_enqueue_script( 'swiper-shortcode', plugin_dir_url( __FILE__ ) . "dist/swiper-shortcode.js", array('jquery') );
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

function wp_swiper_shortcode($atts = array(), $content = '') {
  global $wp, $wp_query;

  $html_att_names = array(
    'id',
    'class',
    'title'
  );

  // Custom attributes
  $custom_att_names = array(
    'include',
    'exclude',
    'ids',
    'before',
    'after',
    'template'
  );


  if ( ! empty( $attr['ids'] ) ) {
    // 'ids' is explicitly ordered, unless you specify otherwise.
    if ( empty( $attr['orderby'] ) ) {
      $attr['orderby'] = 'post__in';
    }
    $attr['include'] = $attr['ids'];
  }


  $atts = shortcode_atts(array(
    # Query attributes
    // 'order' => 'ASC',
    // 'orderby' => 'menu_order ID',
    'ids' => '',
    'include' => '',
    'exclude' => '',
    # Element attributes
    'id' => 'swiper-container-' . uniqid(),
    'class' => '',
    'title' => '',
    # Custom attributes
    'before' => '',
    'after' => '',
    'template' => rtrim(__DIR__, '/') . '/templates/swiper.php',
    'thumbs' => false,
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
  // print_r($atts);

  $is_custom_query = !empty($atts['include']);

  if ($is_custom_query) {
    $args['include'] = $atts['include'];

    if ('RAND' === $order) {
      $orderby = 'none';
    }

    $query_args = array(
      'include' => $atts['include'],
      'post_status' => 'inherit',
      'post_type' => 'attachment',
      'post_mime_type' => 'image',
      'order' => $atts['order'],
      'orderby' => $atts['orderby']
    );

    $wp_query = new WP_QUERY($query_args);
  }

  $classes = array_merge(array(
    'swiper-container'
  ), array(
    $atts['class']
  ));

  $atts['class'].= strpos($atts['class'], 'swiper-container') === false ? ' swiper-container' : '';

  $html_atts = wp_swiper_shortcode_sanitize_atts($atts, $html_att_names);
  $custom_atts = wp_swiper_shortcode_sanitize_atts($atts, $custom_att_names);

  // All the rest goes to Swiper
  $options = array_diff_assoc($atts, array_merge($html_atts, $custom_atts));

  // Camelize Swiper options
  $options = wp_swiper_shortcode_map_keys('wp_swiper_shortcode_camelize', $options);

  // Retrieve slides from shortcode
  $slides = array();
  $pattern = get_shortcode_regex();
  if ( preg_match_all( '/'. $pattern .'/s', $content, $matches )
    && array_key_exists( 2, $matches )
    && in_array( 'swiper_slide', $matches[2] ) ) {
      // Shortcode is being used
      foreach ($matches[2] as $index => $slide) {
        $slide = array_merge(
          shortcode_parse_atts($matches[3][$index]),
            array(
            'content' => $matches[5][$index]
          )
        );

        $slides[] = $slide;
      }
  }

  if (count($slides) > 0) {
    // Create fake posts from slides
    $posts = array();
    foreach ($slides as $index => $slide) {
      $post_id = (100 + $index) * -1;

      $p = new stdClass();
      $p->ID = $post_id;  // negative ID, to avoid clash with a valid post
      $p->post_author = 1;
      $p->post_date = current_time( 'mysql' );
      $p->post_date_gmt = current_time( 'mysql', 1 );
      $p->post_title = $slide['title'] ?: '';
      $p->post_content = $slide['content'] ?: '';
      $p->post_status = 'publish';
      $p->comment_status = 'closed';
      $p->ping_status = 'closed';
      $p->post_name = 'slide-' . rand( 1, 99999 ); // append random number to avoid clash
      $p->post_type = 'page';
      $p->filter = 'raw'; // important!

      // Convert to WP_Post object
      $wp_post = new WP_Post( $p );

      // Add the fake post to the cache
      wp_cache_add( $post_id, $wp_post, 'posts' );

      $posts[] = $wp_post;
    }

    $GLOBALS['wp_the_query'] = $wp_query;

    $wp_query = new WP_QUERY();

    // Update the main query
    $wp_query->post = null;
    $wp_query->posts = $posts;
    $wp_query->queried_object = null;
    $wp_query->queried_object_id = null;
    $wp_query->found_posts = count($posts);
    $wp_query->post_count = count($posts);
    $wp_query->max_num_pages = 1;
    $wp_query->is_page = true;
    $wp_query->is_singular = false;
    $wp_query->is_single = false;
    $wp_query->is_attachment = false;
    $wp_query->is_archive = true;
    $wp_query->is_category = false;
    $wp_query->is_tag = false;
    $wp_query->is_tax = false;
    $wp_query->is_author = false;
    $wp_query->is_date = false;
    $wp_query->is_year = false;
    $wp_query->is_month = false;
    $wp_query->is_day = false;
    $wp_query->is_time = false;
    $wp_query->is_search = false;
    $wp_query->is_feed = false;
    $wp_query->is_comment_feed = false;
    $wp_query->is_trackback = false;
    $wp_query->is_home = false;
    $wp_query->is_embed = false;
    $wp_query->is_404 = false;
    $wp_query->is_paged = false;
    $wp_query->is_admin = false;
    $wp_query->is_preview = false;
    $wp_query->is_robots = false;
    $wp_query->is_posts_page = false;
    $wp_query->is_post_type_archive = false;

    $GLOBALS['wp_query'] = $wp_query;
    $wp->register_globals();
  }

  $is_fake_query = count($posts) > 0;

  // Thumbnails
  $thumbs_html_atts = array(
    'id' => 'swiper-thumbs-' . uniqid(),
    'class' => 'swiper-thumbs'
  );

  $thumbs_html_atts['class'].= ' ' . $thumbs_html_atts['id'];

  if ($options['thumbs']) {
    // Take care for truthy values
    if (!is_array($options['thumbs'])) {
      $options['thumbs'] = array();
    }

    if (!isset($options['thumbs']['swiper'])) {
      $options['thumbs']['swiper'] = array();
    }

    // Merge options
    $options['thumbs']['swiper'] = array_merge(
      array(
        'spaceBetween' => 10,
        'slidesPerView' => 4,
        'freeMode' => true,
        'watchSlidesVisibility' => true,
        'watchSlidesProgress' => true
      ),
      $options['thumbs']['swiper']
    );

    // The selector couldn't be changed
    $options['thumbs']['swiper']['el'] = '.' . $thumbs_html_atts['id'];
  }


  // Get template
  $template = $atts['template'];

  // Create output
  $output = '';

  if ($template) {
    $output = wp_swiper_shortcode_render($template, array(
      'options' => $options,
      'html_atts' => $html_atts,
      'thumbs_html_atts' => $thumbs_html_atts,
      'slides' => $slides
    ));
  } else {
    $output.= $atts['before'];
    foreach ($html_atts as $name => $value) {
      $output.= ' ' . $name . '="' . $value . '"';
    }
    $output.= ">";
    $output.= "<div class=\"swiper-wrapper\">";
    $output.= do_shortcode($content);
    $output.= "</div>";
    $output.= '<div class="swiper-pagination"></div>'
      . '<div class="swiper-button-next"></div>'
      . '<div class="swiper-button-prev"></div>';
    $output.= "</div>";

    $output.= $atts['after'];
  }

  // Create script tag
  $output.= "<script type=\"text/javascript\">//<![CDATA[\n(function(Swiper) {\n";
  $output.= "var options = " . json_encode($options, JSON_UNESCAPED_SLASHES) . ";\n";
  $output.= "console.log(Swiper, JSON.stringify(options, null, 2));";
  $output.= "\tvar swiper = new Swiper('#{$html_atts['id']}', options);\n";

  $output.= "})(window.Swiper)\n//]]></script>\n";


  if ($is_fake_query || $is_custom_query) {

    wp_reset_query();
    /*
    $wp_query = $current_wp_query;
    if ($is_fake_query) {
      $wp_query = $current_wp_query;
    } else {
      wp_reset_query();
    }
    */
  }

  return $output;
}

add_shortcode('swiper', 'wp_swiper_shortcode');



?>
