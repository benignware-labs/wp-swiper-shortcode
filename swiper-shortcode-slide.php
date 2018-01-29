<?php

function wp_swiper_shortcode_slide($atts = array(), $content = "") {
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

add_shortcode('swiper_slide', 'wp_swiper_shortcode_slide');

?>
