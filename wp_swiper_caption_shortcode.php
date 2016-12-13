<?php



/** Swiper Caption
    Adds caption to slides
*/

function wp_swiper_caption_shortcode($atts = array(), $content = "") {
  $html_atts = array('id', 'class');
  $options = array();

  $atts = shortcode_atts(array(
    'class' => 'swiper-slide-caption',
    'before_content' => "",
    'after_content' => '',
  ), $atts, 'swiper_caption');

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

  $output.= $options['before_content'];
  $output.= do_shortcode( $content );
  $output.= $options['after_content'];

  $output.= "</div>";
  return $output;
}

add_shortcode('swiper_caption', 'wp_swiper_caption_shortcode');

?>
