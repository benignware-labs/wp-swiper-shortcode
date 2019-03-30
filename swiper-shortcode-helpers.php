<?php

function wp_swiper_shortcode_sanitize_atts($atts, $permitted = array()) {
  $result = array();
  foreach ($atts as $key => $value) {
    if (in_array($key, $permitted)) {
      $result[$key] = $value;
    }
  }
  return $result;
}


function wp_swiper_shortcode_render($template, $template_data = array()) {
  foreach($template_data as $key => $value) {
    $$key = $template_data[$key];
  }
  ob_start();
  include $template;
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}
