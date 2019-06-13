<!-- Slider main container -->
<div class=="swiper-gallery">
  <div id="<?= $id; ?>" class="swiper-container">
    <div class="swiper-wrapper">
      <?php while( have_posts()) : the_post() ?>
        <!-- Slides -->
        <div class="swiper-slide">
          <img src="<?= wp_get_attachment_image_src($post->ID, $size)[0] ?>"/>
        </div>
      <?php endwhile; ?>
    </div>

    <?php if ($pagination): ?>
      <div class="swiper-pagination <?= $theme['classes']['swiper-pagination']; ?>"></div>
    <?php endif; ?>

    <?php if ($scrollbar): ?>
      <div class="swiper-scrollbar <?= $theme['classes']['swiper-scrollbar']; ?>"></div>
    <?php endif; ?>

    <?php if ($navigation): ?>
      <div class="swiper-button-next <?= $theme['classes']['swiper-button-next']; ?>"></div>
      <div class="swiper-button-prev <?= $theme['classes']['swiper-button-prev']; ?>"></div>
    <?php endif; ?>
  </div>
  <?php if ($thumbs): ?>
    <?php get_swiper($template, 'thumbs', $thumbs); ?>
  <?php endif; ?>
</div>
