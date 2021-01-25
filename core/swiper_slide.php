<?php

function swiper_slide($params = array(), $content = "") {
  $html_atts = array('id', 'class');
  $options = array();

  $params = shortcode_atts(array(
    'class' => 'swiper-slide',
  ), $params, 'swiper_slide');

  $params['class'].= strpos($params['class'], 'swiper-slide') === false ? ' swiper-slide' : '';

  // Create output
  $output = "";
  $output.= "<div";
  foreach ($params as $name => $value) {
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
  $output.= do_shortcode( $content );
  $output.= $caption;

  $output.= "</div>";
  return $output;
}

add_shortcode('swiper_slide', 'swiper_slide_shortcode');
