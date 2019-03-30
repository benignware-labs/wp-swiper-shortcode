<?php
  global $post;
?>
<!-- Slider main container -->

<div class="swiper-gallery">
  <div
    <?php foreach ($html_atts as $name => $value): ?>
      <?= $name ?>="<?= $value ?>"
    <?php endforeach; ?>
  >
    <div class="swiper-wrapper">
      <?php while( have_posts()) : the_post() ?>
        <div class="swiper-slide">
          <div class="swiper-slide" style="background-image:url(<?= wp_get_attachment_image_src( $post->ID, 'post-thumbnail')[0] ?>)"></div>
        </div>
      <?php endwhile; ?>
    </div>
    <!-- If we need pagination -->
    <div class="swiper-pagination"></div>

    <!-- If we need navigation buttons -->
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
  <?php if ($options['thumbs']): ?>
    <div
      <?php foreach ($thumbs_html_atts as $name => $value): ?>
        <?= $name ?>="<?= $value ?>"
      <?php endforeach; ?>
    >
      <div class="swiper-wrapper">
        <?php while( have_posts()) : the_post() ?>
          <div class="swiper-slide" style="background-image:url(<?= wp_get_attachment_image_src( $post->ID, 'thumbnail')[0] ?>)"></div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
