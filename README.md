# wp-swiper-shortcode

Swiper Shortcode Integration for Wordpress

## Usage

Place inside post content

```
[swiper autoplay=3000]
  [swiper_slide]
    Slide 1
  [/swiper_slide]
  [swiper_slide]
    Slide 2
  [/swiper_slide]
[/swiper]
```

#### Embed in a template

Use output buffering in order to embed in a php-template

```php
<?php ob_start('do_shortcode'); ?>
[swiper autoplay=3000]
  [swiper_slide]
    Slide 1
  [/swiper_slide]
  [swiper_slide]
    Slide 2
  [/swiper_slide]
[/swiper]
<?php ob_end_flush(); ?>
```

### Attributes

Customize the default behaviour by using the `shortcode_atts_swiper`-filter

```php
<?php
function shortcode_atts_swiper($out, $pairs, $atts, $shortcode) {
  return array_merge($out, array(
    # Custom attributes
    'id' => 'swiper-' . uniqid(),
    'class' => '',
    'before' => '',
    'before_content' => '',
    'after' => '',
    'after_content' => '<div class="swiper-pagination"></div>'
      . '<div class="swiper-button-next"></div>'
      . '<div class="swiper-button-prev"></div>'
      . '<div class="swiper-scrollbar"></div>',
    # Swiper options
    'pagination' => array(
      'el' => '.swiper-pagination',
      'clickable' =>  true
    ),
    'navigation' => array(
      'next_el' => '.swiper-button-next',
      'prev_el' => '.swiper-button-prev'
    ),
    'scrollbar' => false,
    'loop' => true
  ), $atts);
}
add_filter( 'shortcode_atts_swiper', 'shortcode_atts_swiper' );
?>
```
