<?php

require_once 'swiper-shortcode-helpers.php';

function wp_swiper_shortcode_gallery($content = '', $attr = array()) {
  global $post;
  $output = "";

  if ( ! empty( $attr['ids'] ) ) {
    // 'ids' is explicitly ordered, unless you specify otherwise.
    if ( empty( $attr['orderby'] ) ) {
      $attr['orderby'] = 'post__in';
    }
    $attr['include'] = $attr['ids'];
  }

  $atts = shortcode_atts(array(
    'order' => 'ASC',
    'orderby' => 'menu_order ID',
    'ids' => '',
    'id' => $post->ID,
    // 'itemtag' => 'dl',
    // 'icontag' => 'dt',
    'captiontag' => 'dd',
    'columns' => 3,
    // 'size' => 'post-thumbnail',
    'include' => '',
    'exclude' => '',
    'template' => rtrim(__DIR__, '/') . '/templates/swiper-gallery.php',
    'thumbs' => true
  ), $attr, 'swiper_gallery');

  $swiper_atts = array_merge($atts, array(
    'template' => addslashes($atts['template']),
    'post_type' => 'attachment',
    'post_mime_type' => 'image'
  ));

  $output = '';

  if ($atts['template']) {
    $output = "[swiper";
    foreach ($swiper_atts as $key => $value) {
      $output.= ' ' . $key . '="' . $value . '"';
    }
    $output.= "]";
    $output.= "[/swiper]";
  }

  return do_shortcode($output);
}
add_shortcode('swiper_gallery', 'wp_swiper_shortcode_gallery');


add_filter( 'post_gallery', 'wp_swiper_shortcode_gallery', 10, 2 );

?>
