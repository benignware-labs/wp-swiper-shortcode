<!-- Slider main container -->
<div
  <?php foreach ($html_atts as $name => $value): ?>
    <?= $name ?>="<?= $value ?>"
  <?php endforeach; ?>
>
    <!-- Additional required wrapper -->
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
    <!-- If we need pagination -->
    <div class="swiper-pagination"></div>

    <!-- If we need navigation buttons -->
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
</div>
