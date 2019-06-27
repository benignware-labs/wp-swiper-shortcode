<?php

/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
add_action('init', function() { // phpcs:ignore
	global $registered_swiper_themes;

	// wp_register_script('swiper-js', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.0/js/swiper.min.js');
	// wp_register_style('swiper-css', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.0/css/swiper.min.css');


	wp_register_script('swiper-js', plugins_url( 'dist/swiper/js/swiper.min.js', dirname( __FILE__ ))); // Swiper JS
  wp_register_style('swiper-css', plugins_url( 'dist/swiper/css/swiper.min.css', dirname( __FILE__ ))); // Swiper CSS

  wp_enqueue_script('swiper-js');
  wp_enqueue_style('swiper-css');

	// Register block styles for both frontend + backend.
	wp_register_style(
		'swiper-shortcode-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		array( 'wp-editor' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	);

	// Register block editor script for backend.
	wp_register_script(
		'swiper-shortcode-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
		null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	// Pass image sizes information to the client
	$options = apply_filters('swiper_options', array(), array());

	wp_localize_script( 'swiper-shortcode-js', 'SwiperSettings',
    array(
			'data' => json_encode(array(
				'options' => $options,
				'themes' => $registered_swiper_themes,
				'sizes' => get_intermediate_image_sizes()
			))
    )
  );

	// Register block editor styles for backend.
	wp_register_style(
		'swiper-shortcode-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
	);


	add_filter( 'block_categories', function($categories, $post) {
		return array_merge(
			$categories,
			array(
				array(
					'slug' => 'swiper',
					'title' => __( 'Swiper', 'swiper-shortcode' ),
				),
			)
		);
	}, 10, 2);

	/**
	 * Register Gutenberg block on server-side.
	 *
	 * Register the block on server-side to ensure that the block
	 * scripts and styles for both frontend and backend are
	 * enqueued when the editor loads.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	 * @since 1.16.0
	 */

	register_block_type(
		'swiper/swiper', array(
			// Enqueue blocks.style.build.css on both frontend & backend.
			'style' => 'swiper-shortcode-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script' => 'swiper-shortcode-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'  => 'swiper-shortcode-editor-css',
			'render_callback' => function($attributes, $content) {
				$params = swiper_shortcode_snakeify_keys($attributes);

				return swiper_shortcode($params, $content);
			}
		)
	);

	register_block_type(
		'swiper/swiper-slide', array(
			'render_callback' => function($attributes, $content) {
				$params = swiper_shortcode_snakeify_keys($attributes);

				return '[swiper_slide]' . $content . '[/swiper_slide]';
			}
		)
	);

	register_block_type(
		'swiper/swiper-gallery', array(
			'render_callback' => function($attributes, $content) {
				$params = swiper_shortcode_snakeify_keys($attributes);

				return swiper_gallery_shortcode($params, $content);
			}
		)
	);
});
