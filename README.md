# wp-swiper-shortcode

Swiper integration for Wordpress

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

#### Use programmatically

```php
<?php
	echo do_shortcode(<<<EOT
		[swiper]
			[swiper_slide]
				Slide 1
			[/swiper_slide]
			[swiper_slide]
				Slide 2
			[/swiper_slide]
		[/swiper]
	EOT);
?>
```

### Attributes

Customize the default behaviour by using the `shortcode_atts_swiper`-filter

```php
add_filter( 'shortcode_atts_swiper', function custom_shortcode_atts_swiper($out, $pairs, $atts, $shortcode) {
  return array_merge($out, array(
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
});
```

## Recipes

### Featured Galleries Integration

If you like to show a swiper-driven gallery instead of a single featured image, the recommended approach is as follows.

Download, install and activate [Featured Galleries Wordpress Plugin](https://wordpress.org/plugins/featured-galleries/).

Paste the following code into your theme's `function.php`:

```php
/**
 * Render featured galleries as post thumbnail
 */
function featured_galleries_post_thumbnail_html($html, $post_id) {
	$post_gallery_ids = get_post_gallery_ids($post_id, 'string');
	if (strlen($post_gallery_ids) > 0) {
		$html = do_shortcode('[swiper_gallery ids="' . $post_gallery_ids . '"]');
	}
	return $html;
}
add_filter( 'post_thumbnail_html', 'featured_galleries_post_thumbnail_html', 99, 5 );
```

This will render a `swiper_gallery` shortcode with every call to `the_post_thumbnail` in case the current post actually has a featured gallery assigned.

# Gutenberg breaks Wordpress galleries

Simple fix for now is to just deactivate it:

```php
add_filter('use_block_editor_for_post', function() {
  return false;
});
```

## Development

Download [Docker CE](https://www.docker.com/get-docker) for your OS.

### Environment

Point terminal to your project root and start up the container.

```cli
docker-compose up -d
```

Open your browser at [http://localhost:3000](http://localhost:8030).

Go through Wordpress installation and activate Swiper Shortcode wordpress plugin or install via wpcli:

```cli
docker-compose run --rm wp install-wp
```

### Useful docker commands

#### Startup services

```cli
docker-compose up -d
```
You may omit the `-d`-flag for verbose output.

#### Shutdown services

In order to shutdown services, issue the following command

```cli
docker-compose down
```

#### List containers

```cli
docker-compose ps
```

#### Remove containers

```cli
docker-compose rm
```

#### Open bash

Open bash at wordpress directory

```cli
docker-compose exec wordpress bash
```

#### Update composer dependencies

If it's complaining about the composer.lock file, you probably need to update the dependencies.

```cli
docker-compose run composer update
```

###### List all globally running docker containers

```cli
docker ps
```

###### Globally stop all running docker containers

```cli
docker stop $(docker ps -a -q)
```

###### Globally remove all containers

```cli
docker rm $(docker ps -a -q)
```

##### Remove all docker related stuff

```cli
docker system prune
```
