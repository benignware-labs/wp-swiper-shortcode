<?php

require_once('get_swiper.php');
require_once('swiper_slide_shortcode.php');
require_once('swiper_gallery.php');

function swiper_shortcode_get_html_keys() {
  return [
    'id',
    'class',
    'title'
  ];
}

function swiper_shortcode_get_query_keys() {
  return [
    // Author parameters
    'author',
    'author_name',
    'author__in',
    'author__not_in',
    // Category parameters
    'cat',
    'category_name',
    'category__and',
    'category__in',
    'category__not_in',
    // Tag parameters
    'tag',
    'tag_id',
    'tag__and',
    'tag__in',
    'tag__not_in',
    'tag_slug__and',
    'tag_slug__in',
    // Taxonomy parameters
    'tax_query',
    // Search parameters
    's',
    // Post & Page parameters
    'p',
    'name',
    'page_id',
    'pagename',
    'post_parent' ,
    'post_parent__in',
    'post_parent__not_in',
    'post__in',
    'post__not_in',
    'post_name__in',
    // Password Parameters
    'has_password',
    'post_password',
    // Post Type Parameters
    'post_type',
    // Status Parameters
    'post_status',
    // Comment Parameters
    'comment_count',
    // Pagination Parameters
    'nopaging',
    'posts_per_page',
    'posts_per_archive_page',
    'offset',
    'paged',
    'page',
    'ignore_sticky_posts',
    // Order & Orderby Parameters
    'order',
    'orderby',
    // Date Parameters
    'year',
    'monthnum',
    'w',
    'day',
    'hour',
    'minute',
    'second',
    'm',
    'date_query',
    // Custom Field (post meta) Parameters
    'meta_key',
    'meta_value',
    'meta_value_num',
    'meta_compare',
    'meta_query',
    // Permission Parameter
    'perm',
    // Mime Type Parameters
    'post_mime_type',
    // Caching Parameters
    'cache_results',
    'update_post_meta_cache',
    'update_post_term_cache',
    // Return Fields Parameter
    'fields'
  ];
}

add_action( 'wp_enqueue_scripts', function() {
  // Swiper CDN
  wp_register_style('swiper', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/6.4.8/swiper-bundle.css' );
  wp_enqueue_style('swiper');
  wp_register_script('swiper', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/6.4.8/swiper-bundle.min.js', null, null, true );
  wp_enqueue_script('swiper');

  // Init script
  // echo plugin_dir_url( __FILE__ );
  wp_enqueue_script('swiper-shortcode', plugin_dir_url( __FILE__ ) . 'swiper-shortcode.js', array('swiper') );
});

// Fix empty paragraphs
add_filter('the_content', function( $content ) {
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
});

function swiper_shortcode($params = array(), $content = '') {
  global $wp, $wp_query;

  $input_params = $params;
  $query_keys = swiper_shortcode_get_query_keys();
  $html_keys = swiper_shortcode_get_html_keys();

  $params = shortcode_atts(
    array_merge(
      array_fill_keys($html_keys, ''),
      array_fill_keys($query_keys, ''),
      array(
        'id' => 'swiper-shortcode-' . uniqid(),
        'include' => '',
        'exclude' => '',
        'ids' => '',
        'before' => '',
        'after' => '',
        'template' => 'swiper',
        'format' => '',
        // Swiper parameters
        'pagination' => array(
          'clickable' =>  true
        ),
        'navigation' => true,
        'scrollbar' => false,
        'loop' => false,
        'autoplay' => false,
        'thumbs' => false,
      )
    ),
    $params,
    'swiper'
  );

  // Parse booleans and numbers
  $params = array_map(function($item) {
    if ($item === 'true') {
      return true;
    }
    if ($item === 'false') {
      return false;
    }
    if (is_numeric($item)) {
      return floatval($item);
    }

    return $item;
  }, $params);

  // Retrieve slides from shortcode
  $slides = array();
  $pattern = get_shortcode_regex();
  if ( preg_match_all( '/'. $pattern .'/s', $content, $matches )
    && array_key_exists( 2, $matches )
    && in_array( 'swiper_slide', $matches[2] ) ) {
      // Shortcode is being used
      foreach ($matches[2] as $index => $slide) {
        $slide = array_merge(
          shortcode_parse_atts($matches[3][$index]) ?: array(),
          array(
            'content' => $matches[5][$index]
          )
        );
        $slides[] = $slide;
      }
  }

  $posts = array();

  if (count($slides) > 0) {
    // Create fake posts from slides
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
  } else {
    $include = is_array($params['include']) ? $params['include'] : explode(',', $params['include']);
    $include = array_map('trim', $include);
    $include = array_values(array_filter($include));

		$query_args = array_merge(
			array_intersect_key($params, array_flip($query_keys)),
      count($include) ? array(
        'post__in' => $include,
        'orderby' => $params['order'] === 'RAND' ? 'none' : $params['orderby']
      ) : array()
    );

    $wp_query = new WP_QUERY($query_args);
  }

  $html_atts = array_intersect_key($params, array_flip($html_atts));
  $custom_atts = array_intersect_key($params, array_flip($custom_keys));

  // All the rest goes to Swiper
  $options = array_diff_assoc($params, array_merge($html_atts, $custom_atts));

  // Create output
  ob_start();
  get_swiper(
    $params['template'],
    $params['format'],
    array_merge(
      $input_params,
      $params,
      [
        'options' => $options,
        'attrs' => $html_atts
      ]
    )
  );

  $output = ob_get_contents();

  ob_end_clean();

  wp_reset_query();

  return $output;
}

add_shortcode('swiper', 'swiper_shortcode');
