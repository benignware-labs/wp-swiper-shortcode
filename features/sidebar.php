<?php

function swiper_sidebar($sidebar_id, $params = array()) {
  ob_start();
  dynamic_sidebar($sidebar_id);
  $html = ob_get_contents();
  ob_end_clean();

  // Parse DOM
  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html );
  $doc_xpath = new DOMXpath($doc);
  $body = $doc->getElementsByTagName('body')->item(0);

  $content = '';

  foreach ($body->childNodes as $node) {
    if ($node->nodeType === 1) {
      $node_content = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML($node));
      $class = $node->getAttribute('class');
      $content.= '[swiper_slide]' . $node_content . '[/swiper_slide]';
    }
  }


  $output = swiper_shortcode($params, $content);

  echo $output;
}
