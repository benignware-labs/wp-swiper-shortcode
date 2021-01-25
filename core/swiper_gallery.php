<?php

require_once('swiper_shortcode.php');

function swiper_gallery($params = array(), $content = null) {
  $params = array_merge($params, shortcode_atts(array(
    // Default gallery params
    'format' => 'gallery',
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'post_mime_type' => 'image',
    'ids' => is_array($params['ids']) ? implode(',', $params['ids']) : $params['ids'],
    'size' => 'post-thumbnail',
    'fit' => 'cover',
    'itemtag' => 'figure',
    'captiontag' => 'figcaption'
  ), $params, 'swiper-gallery'));

	$content = do_shortcode($content);

	return swiper_shortcode($params, $content, 'swiper_gallery');
}

add_filter('post_gallery', function($content, $params) {
  return swiper_gallery($params, $content);
}, 10, 2);
