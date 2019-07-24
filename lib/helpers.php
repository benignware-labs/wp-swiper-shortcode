<?php

/*
function swiper_shortcode_get_image_sizes() {
  global $_wp_additional_image_sizes;

	$intermediate_image_sizes = get_intermediate_image_sizes();
	$images_sizes = array();

	foreach ( $intermediate_image_sizes as $size ) {
    $image_data = array(
      'name' => $size
    );

    if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
      $image_data = array_merge($image_data, array(
        'width' => get_option( "{$_size}_size_w" ),
        'height' => get_option( "{$_size}_size_h" );
      ));
		} elseif ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
      $image_data = array_merge($image_data, $_wp_additional_image_sizes[ $size ]);
		}

    $images_sizes[$size] = $image_data;
	}

  return $images_sizes;
}
*/

function swiper_shortcode_snakeify_keys($array, $arrayHolder = array()) {
  $result = !empty($arrayHolder) ? $arrayHolder : array();

  foreach ($array as $key => $val) {
    $str = $key;
    $str = strtolower($str);
    $func = function($c) {return "_" . strtolower($c[1]);};
    $newKey = preg_replace_callback('/([A-Z])/', $func, $str);

    if (!is_array($val)) {
      $result[$newKey] = $val;
    } else {
      $result[$newKey] = swiper_shortcode_snakeify_keys($val, (isset($result[$newKey]) ? $result[$newKey] : null ));
    }
  }
  return $result;
}

function swiper_shortcode_camelize_keys($array, $arrayHolder = array()) {
  $result = !empty($arrayHolder) ? $arrayHolder : array();

  foreach ($array as $key => $val) {
    $newKey = @explode('_', $key);
    array_walk($newKey, function(&$v) {$v = ucwords($v);});
    $newKey = @implode('', $newKey);
    $newKey{0} = strtolower($newKey{0});
    if (!is_array($val)) {
      $result[$newKey] = $val;
    } else {
      $result[$newKey] = swiper_shortcode_camelize_keys($val, (isset($result[$newKey]) ? $result[$newKey] : null ));
    }
  }
  return $result;
}

function swiper_shortcode_render_template($template, $format = '', $data = array()) {
	$is_absolute_path = $template[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $template) > 0;
	$path_parts = pathinfo($template);
  	$template_name = $format ? $path_parts['filename'] . '-' . $format : $path_parts['filename'];
	$template_ext = isset($path_parts['extension']) ?: 'php';
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

function swiper_shortcode_array_merge_rec($array1 = array(), $array2 = array()) {
  $merged = $array1;

  foreach ($array2 as $key => & $value) {
    if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
      $merged[$key] = swiper_shortcode_array_merge_rec($merged[$key], $value);
    } else if (is_numeric($key)) {
       if (!in_array($value, $merged)) {
          $merged[] = $value;
       }
    } else {
      $merged[$key] = $value;
    }
  }

  return $merged;
}
