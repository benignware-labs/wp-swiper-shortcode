<?php
function swiper_post_gallery($output, $attr) {
  global $post;
  $output = "";

    if (isset($attr['orderby'])) {
        $attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
        if (!$attr['orderby'])
            unset($attr['orderby']);
    }

    extract(shortcode_atts(array(
        'order' => 'ASC',
        'orderby' => 'menu_order ID',
        'id' => $post->ID,
        'itemtag' => 'dl',
        'icontag' => 'dt',
        'captiontag' => 'dd',
        'columns' => 3,
        'size' => 'thumbnail',
        'include' => '',
        'exclude' => ''
    ), $attr));


    // Override gallery size
    $size = 'gallery';

    // get image size
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
    if (!empty($include)) {
        $include = preg_replace('/[^0-9,]+/', '', $include);
        $_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
        $attachments = array();
        foreach ($_attachments as $key => $val) {
            $attachments[$val->ID] = $_attachments[$key];
        }
    }

    if (empty($attachments)) return '';

    $gallery_id = uniqid();

    $col_value = floor(12 / $columns);

    $style = $ratio > 0 ? "padding-bottom: " . ($ratio * 100) . "%; height: 0;" : "";
    // style=\"$style\"

    // $output.= "        <div id=\"gallery-$gallery_id\" class=\"swiper-container\">";
    // $output.= '          <div class="swiper-wrapper">';
    $output.= "[swiper]";
    $image_index = 0;

    foreach ($attachments as $id => $attachment) {

      $img_large = wp_get_attachment_image_src($id, $size);

      $active = $image_index === 0 ? 'active' : '';

      $output.= "          [swiper_slide]";
      $output.= "            <img src=\"{$img_large[0]}\" title=\"{$attachment->post_excerpt}\" alt=\"{$attachment->post_excerpt}\" />\n";
      //$output.= '            <div class="gallery-item-caption">';
      //$output.= "              {$attachment->post_excerpt}";
      //$output.= '            </div>';
      $output.= '          [/swiper_slide]';
      $image_index++;
    }

    $output.= "[/swiper]";
  //   $output.= '          </div>';
	// $output.= "          <div class=\"swiper-pagination\"></div>";
  //   $output.= "          <div class=\"swiper-button-prev\"><svg class=\"arrow-prev\"><use xlink:href=\"#arrow-prev\" /></svg></div>";
  //   $output.= "          <div class=\"swiper-button-next\"><svg class=\"arrow-next\"><use xlink:href=\"#arrow-next\" /></svg></div>";
  //   $output.= '        </div>';


	 // Script
    /*$output.= "<script>\n";
    $output.= "(function($) {\n";
    $output.= "  var\n";
    $output.= "    galleryId = '$gallery_id',\n";
    $output.= "    \$gallery = $('#gallery-' + galleryId),\n";
    $output.= "    console.log('script: ', \$gallery);";
    $output.= "})(jQuery)";*/


    return do_shortcode($output);
}

add_filter( 'post_gallery', 'swiper_post_gallery', 10, 2 );


?>
