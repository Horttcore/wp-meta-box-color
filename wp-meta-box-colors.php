<?php
if ( !class_exists( 'WP_Meta_Box_Colors' ) ) :

/**
 *
 */
class WP_Meta_Box_Colors
{



	function __construct()
	{

		add_action( 'admin_print_styles-post.php', array( $this, 'enqueue_colorpicker' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );

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

			add_meta_box( 'page-colors', __( 'Colors' ), array( $this, 'meta_box' ), $post_type );

		endforeach;

	} // END add_meta_boxes



	/**
	 * Enqueue colorpicker
	 *
	 * @param type var Description
	 * @return return type
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

		$fields = apply_filters( 'wp-meta-box-colors-fields', array(
			'page-color' => __( 'Color' ),
		), $post );

		?>
		<table class="form-table">
			<?php foreach ( $fields as $field => $label ) : ?>
				<tr>
					<td><label for="wp-meta-box-colors-<?php echo esc_attr( $field ) ?>"><?php echo $label ?></label></td>
					<td>
						<input type="text" name="wp-meta-box-colors[<?php echo esc_attr( $field ) ?>]" value="<?php if ( isset( $colors[$field] ) ) echo esc_attr( $colors[$field] ) ?>" id="wp-meta-box-colors-<?php echo esc_attr( $field ) ?>">
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

		$colors = array_map( 'sanitize_text_field', $_POST['wp-meta-box-colors'] );
		$colors = array_filter( $colors );

		if ( !empty( $colors ) )
			update_post_meta( $post_id, '_colors', $colors );
		else
			delete_post_meta( $post_id, '_colors' );

	} // END save_post



} // END class WP_Meta_Box_Colors
new WP_Meta_Box_Colors;

endif;
