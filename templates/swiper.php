<!-- Slider main container -->
<div id="<?= $id; ?>" class="swiper-container">
  <div class="swiper-wrapper">
    <?php while( have_posts()) : the_post() ?>
      <!-- Slides -->
      <div class="swiper-slide">
        <?php if (get_the_title()): ?>
          <h3><? the_title(); ?></h3>
        <?php endif; ?>
        <?= get_the_content(); ?>
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
