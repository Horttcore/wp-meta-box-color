<?php
/*
Plugin Name: WP Meta Box Colors
Plugin URI: https://horttcore.de
Description: Add color picker to posts
Version: 2.0
Author: Ralf Hortt
Author URI: https://horttcore.de
License: GPL2
*/

if ( !class_exists( 'WP_Meta_Box_Colors' ) ) :

/**
 *
 */
class WP_Meta_Box_Colors
{


	function __construct()
	{

		add_action( 'admin_print_styles-post.php', [$this, 'enqueue_colorpicker'] );
		add_action( 'add_meta_boxes', [$this, 'add_meta_boxes'] );
		add_action( 'save_post', [$this, 'save_post'] );
		add_action( 'register_meta', [$this, 'register_meta'] );

	} // END __construct


	/**
	 * Register meta box
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	public function add_meta_boxes()
	{

		$post_types = get_post_types_by_support( 'colors' );

		foreach ( $post_types as $post_type ) :

			add_meta_box( 'page-colors', __( 'Colors' ), array( $this, 'meta_box' ), $post_type, 'side' );

		endforeach;

	} // END add_meta_boxes


	/**
	 * Get registered colors
	 *
	 * @param string $post_type Post type
	 * @return void
	 */
	public function get_colors( $post_type = '' )
	{

		$colors = apply_filters( 'wp-meta-box-colors-fields', [] );

		if ( !$post_type )
			return $colors;

		return apply_filters( "wp-meta-box-colors-fields-$post_type", $colors );

	} // END get_colors


	/**
	 * Enqueue colorpicker
	 *
	 * @return void
	 */
	public function enqueue_colorpicker()
	{

		$screen = get_current_screen();

		if ( 'post' != $screen->base )
			return;

		if ( !post_type_supports( $screen->post_type, 'colors' ) )
			return;

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

	} // END enqueue_colorpicker


	/**
	 * Meta box
	 *
	 * @param WP_Post $post Post object
	 * @return void
	 */
	public function meta_box( $post )
	{

		$colors = get_post_meta( $post->ID, '_colors', TRUE );

		$fields = $this->get_colors( $post->post_type );
		?>
		<table class="form-table">
			<?php foreach ( $fields as $field => $label ) : ?>
				<tr>
					<td><label for="wp-meta-box-colors-<?php echo esc_attr( $field ) ?>"><?php echo $label ?></label></td>
					<td>
						<input type="text" name="wp-meta-box-colors[<?php echo esc_attr( $field ) ?>]" value="<?php echo esc_attr( get_post_meta( $post->ID, "wp-meta-box-colors-$field", TRUE ) ) ?>" id="wp-meta-box-colors-<?php echo esc_attr( $field ) ?>">
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<script>
			jQuery(document).ready(function(){
				jQuery('input[name^="wp-meta-box-colors["]').wpColorPicker();
			});
		</script>
		<?php

		wp_nonce_field( 'save-wp-meta-box-colors', 'wp-meta-box-colors-nonce' );

	} // END meta_box


	/**
	 * Register meta field
	 *
	 * @return void
	 */
	public function register_meta()
	{

		$post_types = get_post_types_by_support( 'colors' );

		foreach ( $post_types as $post_type ) :

			foreach ( $this->get_colors( $post_type ) as $key => $value ) :

				register_meta( $post_type, $key, [
					'type' => 'string',
					'show_in_rest' => TRUE,
				] );

			endforeach;

		endforeach;

	}


	/**
	 * Save post meta
	 *
	 * @access public
	 * @param int $post_id Post ID
	 * @author Ralf Hortt
	 **/
	public function save_post( $post_id )
	{

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( !isset( $_POST['wp-meta-box-colors-nonce'] ) || !wp_verify_nonce( $_POST['wp-meta-box-colors-nonce'], 'save-wp-meta-box-colors' ) )
			return;

		foreach ( $this->get_colors() as $key => $value) :

			if ( !isset( $_POST['wp-meta-box-colors'][$key] ) )
				continue;

			update_post_meta( $post_id, "wp-meta-box-colors-$key", sanitize_text_field( $_POST['wp-meta-box-colors'][$key] ) );

		endforeach;

	} // END save_post


} // END class WP_Meta_Box_Colors
new WP_Meta_Box_Colors;

endif;
