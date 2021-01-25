<?php

function swiper_shortcode_render_template($template, $format = '', $data = array()) {
	$is_absolute_path = $template[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $template) > 0;
	$path_parts = pathinfo($template);
  $template_name = $format ? $path_parts['filename'] . '-' . $format : $path_parts['filename'];
	$template_ext = isset($path_parts['extension']) ? $path_parts['extension'] : 'php';
	$template_base = $template_name . '.' . $template_ext;
	$template_dir = $path_parts['dirname'];

	if (!$is_absolute_path) {
		// Resolve template
		$directories = array(
			get_template_directory(),
      get_stylesheet_directory(),
			realpath(plugin_dir_path( __FILE__ ) . '../templates')
		);

		$template_dir = array_values(array_filter($directories, function($dir) use ($template_base) {
			return file_exists($dir . DIRECTORY_SEPARATOR . $template_base);
		}))[0];
	}

  $template_file = $template_dir . DIRECTORY_SEPARATOR . $template_base;

  foreach($data as $key => $value) {
    $$key = $data[$key];
  }
  ob_start();
  include $template_file;

  $output = ob_get_contents();

  ob_end_clean();
  return $output;
}

function swiper_shortcode_camelize_keys($array) {
  $result = array();

  foreach ($array as $key => $value) {
    $camelized_key = lcfirst(join(array_map('ucfirst', explode('_', $key))));

    $result[$camelized_key] = is_array($value) ? swiper_shortcode_camelize_keys($value) : $value;
  }

  return $result;
}

add_filter('swiper_options', function($options) {
  $slides_per_view = $options['slides_per_view'];
  $breakpoints = $options['breakpoints'] ?: [];

  if ($options['breakpoints'] && $slides_per_view > 4) {
    $breakpoints = $breakpoints + [
      '576' => [
        'slides_per_view' => 1.5,
        'centered_slides' => true,
        'slides_per_column' => 1
      ]
    ];
    $breakpoints = $breakpoints + [
      '768' => [
        'slides_per_view' => 2,
        'centered_slides' => !! $options['centered_slides']
      ],
      '992' => [
        'slides_per_view' => 4,
        'slides_per_column' => $options['slides_per_column'] ?: 1
      ]
    ];
  } else if ($slides_per_view > 1) {
    $breakpoints = $breakpoints + [
      '576' => [
        'slides_per_view' => 1,
        'slides_per_column' => 1
      ]
    ];
    $breakpoints = $breakpoints + [
      '768' => [
        'slides_per_view' => 2,
        'slides_per_column' => $options['slides_per_column'] ?: 1
      ]
    ];
  }

	$options = array_merge(array(
    'watch_overflow' => true,
    'theme' => 'primary',
    'breakpoints' => $breakpoints
  ), $options);

  return $options;
});

function get_swiper($template, $format = '', $vars = array()) {
  $defaults = [
    'space_between' => 0,
    'slides_per_view' => 1,
    'slides_per_column' => 1,
    'navigation' => true,
    'pagination' => true,
    'scrollbar' => false,
    'autoplay' => false,
    'loop' => false,
    'parallax' => false,
    'thumbs' => null,
    'captions' => array(),
    'breakpoints' => null
  ];

  $options = array_merge(
    $defaults,
    isset($vars['options']) ? array_intersect_key($vars['options'], array_flip(array_keys($defaults))) : [],
  );

  $attrs = array_merge(
    array(
      'id' => uniqid('swiper-'),
    ),
    isset($vars['attrs']) ? $vars['attrs'] : []
  );

  // Add swiper-container class
  $classes = array_filter(array_map('trim', explode(',', $atts['class'])));
  if (!in_array('swiper-container', $classes)) {
    $classes[] = 'swiper-container';
  }
  $attrs['class'] = implode(',', $classes);

  // Inject navigation selectors
  if ($options['navigation']) {
    if ($options['navigation'] === false) {
      unset($options['navigation']);
    } else {
      $options['navigation'] = is_array($options['navigation']) ? $options['navigation'] : array();
      $options['navigation'] = array_merge(
        $options['navigation'],
        array(
          'next_el' => '.swiper-button-next',
          'prev_el' => '.swiper-button-prev'
        )
      );
    }
  }

  // Inject pagination selectors
  if ($options['pagination']) {
    if ($options['pagination'] === false) {
      unset($options['pagination']);
    } else {
      $options['pagination'] = is_array($options['pagination']) ? $options['pagination'] : array();
      $options['pagination'] = array_merge(
        $options['pagination'],
        array(
          'el' => '.swiper-pagination'
        )
      );
    }
  }

  // Autoplay Shorthands
  $options['autoplay'] = $options['autoplay'] === true ? 5000 : $options['autoplay'];
  $options['autoplay'] = is_numeric($options['autoplay']) ? array(
    'delay' => $options['autoplay']
  ) : $options['autoplay'];

  $output = swiper_shortcode_render_template($template, $format, [
    'attrs' => $attrs,
    'options' => $options
  ]);

  $json_options = swiper_shortcode_camelize_keys($options);
  $json_options = json_encode($json_options, JSON_UNESCAPED_SLASHES);

  $output.= '<script type="text/javascript">__initSwiper(\'#' . $attrs['id'] . '\', ' . $json_options . ');</script>';

  echo $output;
}