<?php
  // Render post gallery using swiper shortcode
  $post_gallery_ids = get_post_gallery_ids($post->ID);
?>
<?php if (count($post_gallery_ids)): ?>
  <?php ob_start('do_shortcode'); ?>
  [swiper]
  <?php foreach ($post_gallery_ids as $id) : ?>
    [swiper_slide]
      <?php echo wp_get_attachment_image( $id, 'post-thumbnail' ); ?>
    [/swiper_slide]
  <?php endforeach; ?>
  [/swiper]
  <?php ob_end_flush(); ?>
<?php endif; ?>
