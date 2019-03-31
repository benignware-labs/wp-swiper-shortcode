<!-- Slider main container -->
<div
  <?php foreach ($html_atts as $name => $value): ?>
    <?= $name ?>="<?= $value ?>"
  <?php endforeach; ?>
>
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

  <?php if ($options['pagination']): ?>
    <div class="swiper-pagination"></div>
  <?php endif; ?>

  <?php if ($options['navigation']): ?>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
  <?php endif; ?>
</div>
