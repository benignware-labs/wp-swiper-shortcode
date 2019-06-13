<div id="<?= $id; ?>" class="swiper-container swiper-thumbs">
  <div class="swiper-wrapper">
    <?php while( have_posts()) : the_post() ?>
      <!-- Slides -->
      <div class="swiper-slide">
        <img src="<?= wp_get_attachment_image_src(get_the_ID(), $size)[0] ?>"/>
      </div>
    <?php endwhile; ?>
  </div>
</div>
