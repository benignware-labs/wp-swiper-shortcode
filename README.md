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
[/siper]
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
[/siper]
<?php ob_end_flush(); ?>
```

### Attributes

Customize the default behaviour by using the `shortcode_atts_swiper`-filter

```php
<?php
# functions.php
function shortcode_atts_swiper($atts) {
  return array_merge($atts, array(
    # Swiper options
    'autoplay' => '4000',
    # Custom attributes
    'id' => 'my-swiper' # Override auto-generated id
    'class' => 'my-swiper-container' # Custom css class
    'before' => '<div class="my-swiper">', # Prepend content
    'after' => '</div>' # Append content
  ));
}
add_filter( 'shortcode_atts_swiper', 'shortcode_atts_swiper' );
?>
```
