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
