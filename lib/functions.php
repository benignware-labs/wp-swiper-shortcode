<?php


function get_swiper($template, $format = '', $params = array()) {
  global $registered_swiper_themes;

  $options = array_filter($params);
  $options = apply_filters('swiper_options', $options, $params);

  $theme = isset($options['theme']) ? $options['theme'] : $params['theme'];

  $params = array_merge(
    $params,
		array(
			'id' => 'swiper-' . uniqid(),
      'theme' => $registered_swiper_themes[$theme] ?: array(
				'classes' => array(
					'swiper-button-next' => '',
					'swiper-button-prev' => '',
					'swiper-pagination' => '',
					'swiper-scrollbar' => ''
				)
			)
		)
	);

  if (is_array($params['thumbs']) || $params['thumbs']) {
    $params['thumbs'] = swiper_shortcode_array_merge_rec(
      array(
        'id' => $params['id'] . '-thumbs',
        'size' => 'thumbnail'
      ),
      is_array($params['thumbs']) ? $params['thumbs'] : array()
    );
    $options['thumbs'] = swiper_shortcode_array_merge_rec(
      is_array($options['thumbs']) ? $options['thumbs'] : array(),
      array(
        'swiper' => '#' . $params['thumbs']['id']
      )
    );
  }

  // Merge back options into params
  $params = swiper_shortcode_array_merge_rec(
    swiper_shortcode_snakeify_keys($options),
    $params
  );

	$output = swiper_shortcode_render_template($template, $format, $params);

	// Create script
	$script = '';

	$camelized = swiper_shortcode_camelize_keys($options);

	// ... and serialize to JSON
	$json = json_encode($camelized, JSON_UNESCAPED_SLASHES);

	$id = $params['id'];

  $script.= "<script type=\"text/javascript\">//<![CDATA[\n(function($, Swiper) {\n";
  $script.= "\tvar options = " . $json . ";\n";
  // $script.= "console.log('INIT SWIPER', options);\n";
  $script.= "\tif (options.thumbs) {\n";
  $script.= "\t\tif (typeof options.thumbs.swiper === 'string') {\n";
  $script.= "\t\t\tvar data = $(options.thumbs.swiper).data('swiper-shortcode');\n";
  $script.= "\t\t\tif (data) {\n";
  $script.= "\t\t\t\toptions.thumbs.swiper = data.instance;\n";
  // $script.= "\t\tconsole.log('.......****', options.thumbs.swiper)\n";
  $script.= "\t\t\t}\n";
  $script.= "\t\t}\n";
  $script.= "\t}\n";

  // $script.= "console.log(Swiper, JSON.stringify(options, null, 2));\n";
  $script.= "\tvar swiperElement = document.getElementById('{$id}');\n";
  $script.= "\tvar swiper = new Swiper(swiperElement, options);\n";
  $script.= "\t$(swiperElement).data('swiper-shortcode', { instance: swiper });\n";
  $script.= "})(window.jQuery, window.Swiper)\n//]]></script>\n";

	// Append script
	$output.= $script;

	echo $output;
}

function register_swiper_theme($name, $theme = array()) {
  global $registered_swiper_themes;

  // echo 'register swiper theme' . $name . '<br/>';

  $registered_swiper_themes[$name] = array_merge(
    $theme,
    array(
      'classes' => array_merge(
        array(
  				'swiper-button-next' => '',
  				'swiper-button-prev' => '',
  				'swiper-pagination' => '',
  				'swiper-scrollbar' => ''
  			),
        $theme['classes'] ?: array()
      )
    ),
    array(
      'name' => $name
    )
  );
}

function swiper_shortcode($params, $content = null) {
	global $wp, $wp_query;
	global $registered_swiper_themes;

	$attributes = array_combine(array_keys($params), $params);

	$params = shortcode_atts(array(
		'template' => 'swiper',
		'format' => '',
		'theme' => '',
		// Swiper params
		'slides_per_view' => 1,
		'navigation' => true,
		'pagination' => true,
		'scrollbar' => false,
		'autoplay' => false,
		'loop' => false,
		'parallax' => false,
		'thumbs' => null,
		// Query params
		'ids' => '',
		// 'order' => 'ASC',
    // 'orderby' => 'menu_order ID',
		'order' => '',
    'orderby' => '',
    'post_status' => 'inherit',
    'post_type' => null,
    'post_mime_type' => null,
    'include' => '',
    'exclude' => '',
	), $params, 'swiper');

	if ( ! empty( $params['ids'] ) ) {
    // 'ids' is explicitly ordered, unless you specify otherwise.
    if ( empty( $params['orderby'] ) ) {
      $params['orderby'] = 'post__in';
    }

    $params['include'] = $params['ids'];
  }

	extract($params);

	$is_query = false;

	// Retrieve slides from nested shortcode
  $slides = array();
  $pattern = get_shortcode_regex();

  if ( preg_match_all( '/'. $pattern .'/s', $content, $matches )
    && array_key_exists( 2, $matches )
    && in_array( 'swiper_slide', $matches[2] ) ) {
      // Our shortcode is being used
      foreach ($matches[2] as $index => $slide) {
        $slide = array_merge(
          shortcode_parse_atts($matches[3][$index]) ?: array(),
          array(
            'content' => do_shortcode($matches[5][$index])
          )
        );
        $slides[] = $slide;
      }

			$posts = array();

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


			$is_query = true;
  } else {

		$query_params = array_merge(
			array_intersect_key($params, array_flip([
				'order',
		    'orderby',
		    'include',
		    'exclude',
		    'post_type',
		    'post_mime_type',
		    'post_status'
			])),
      array(
        'post__in' => is_array($params['include']) ? $params['include'] : explode(',', $params['include']),
        'orderby' => $params['order'] === 'RAND' ? 'none' : $params['orderby']
      )
    );

		$wp_query = new WP_QUERY($query_params);

		$is_query = true;
	}

	ob_start();

  get_swiper($template, $format, array_merge(
		$attributes,
		$params
	));

  $output = ob_get_contents();

  ob_end_clean();

	if ($is_query) {
		wp_reset_query();
	}

	return $output;
}


function swiper_slide_shortcode($params, $content = null) {
  return '<div class="swiper-slide">' . trim(do_shortcode($content), ' \n') . '</div>';
}

function swiper_gallery_shortcode($params, $content = null) {
  $params = array_merge($params, shortcode_atts(array(
    // Default gallery params
    'format' => 'gallery',
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'ids' => is_array($params['ids']) ? implode(',', $params['ids']) : $params['ids'],
    'size' => 'large',
    'fit' => 'cover'
  ), $params, 'swiper-gallery'));


	$content = do_shortcode($content);

	return swiper_shortcode($params, $content);
}
