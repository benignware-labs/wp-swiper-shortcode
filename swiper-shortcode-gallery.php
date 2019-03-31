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
    'itemtag' => 'dl',
    'icontag' => 'dt',
    'captiontag' => 'dd',
    'columns' => 3,
    'size' => 'post-thumbnail',
    'include' => '',
    'exclude' => '',
    'template' => rtrim(__DIR__, '/') . '/templates/swiper-gallery.php',
    'thumbs' => true
  ), $attr, 'swiper_gallery');

  extract($atts);

  // Get image size
  $image_size = null;
  global $_wp_additional_image_sizes;

  if ($size) {
    $sizes = get_intermediate_image_sizes();
    foreach ( $sizes as $_size ) {
      if ($_size === $size) {
        if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
          // Default image size
          $image_size = array(
            get_option( "{$_size}_size_w" ), get_option( "{$_size}_size_h" )
          );
        } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
          // Custom image size
          $image_size = array(
            $_wp_additional_image_sizes[ $_size ]['width'], $_wp_additional_image_sizes[ $_size ]['height']
          );
        }
      }
    }
  }

  $ratio = $image_size ? $image_size[1] / $image_size[0] : 0;

  $id = intval($id);
  if ('RAND' == $order) $orderby = 'none';

  if (!empty($atts['include'])) {
    $args['include'] = $atts['include'];

    $_attachments = get_posts( $query_args );
    $attachments = array();
    foreach ($_attachments as $key => $val) {
      $attachments[$val->ID] = $_attachments[$key];
    }
  }

  if (empty($attachments)) return '';

  $gallery_id = uniqid();

  $col_value = floor(12 / $columns);

  $style = $ratio > 0 ? "padding-bottom: " . ($ratio * 100) . "%; height: 0;" : "";

  $swiper_atts = array_merge($atts, array(
    'template' => addslashes($atts['template'])
  ));

  $output = '';

  if ($template) {
    $output = "[swiper";
    foreach ($swiper_atts as $key => $value) {
      $output.= " $key='$value'";
    }
    $output.= "]";
  } else {
    $output = "[swiper]";
    $image_index = 0;

    foreach ($attachments as $id => $attachment) {

      $img_large = wp_get_attachment_image_src($id, $size);

      $active = $image_index === 0 ? 'active' : '';
      $output.= "          [swiper_slide]";
      $output.= "            <img src=\"{$img_large[0]}\" title=\"{$attachment->post_excerpt}\" alt=\"{$attachment->post_excerpt}\" />\n";
      $output.= '          [/swiper_slide]';
      $image_index++;
    }

    $output.= "[/swiper]";
  }

  return do_shortcode($output);
}
add_shortcode('swiper_gallery', 'wp_swiper_shortcode_gallery');


add_filter( 'post_gallery', 'wp_swiper_shortcode_gallery', 10, 2 );

?>
