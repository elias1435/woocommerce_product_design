<?php
namespace PDESIGN;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'colorVariation' ) ) :
class colorVariation{
	private $taxonomy;
	private $post_type;
	private $fields = array();

 	public function __construct( $taxonomy, $post_type, $fields ){
 		$this->taxonomy  = $taxonomy;
		$this->post_type = $post_type;
		$this->fields    = $fields;

		add_action( 'delete_term', array( $this, 'delete_term' ), 5, 3 );

		add_action( "{$this->taxonomy}_add_form_fields", array( $this, 'add' ) );
		add_action( "{$this->taxonomy}_edit_form_fields", array( $this, 'edit' ), 10 );
		add_action( 'created_term', array( $this, 'save' ), 10, 3 );
		add_action( 'edited_term', array( $this, 'save' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( "manage_edit-{$this->taxonomy}_columns", array( $this, 'taxonomy_columns' ) );
		add_filter( "manage_{$this->taxonomy}_custom_column", array( $this, 'taxonomy_column_preview' ), 10, 3 );

 	}
 	public function taxonomy_columns( $columns ) {
		$new_columns = array();

		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
		}

		$new_columns['pdsn-meta-preview'] = '';

		if ( isset( $columns['cb'] ) ) {
			unset( $columns['cb'] );
		}

		return array_merge( $new_columns, $columns );
	}
	public function taxonomy_column_preview( $columns, $column, $term_id ) {
		if ( 'pdsn-meta-preview' !== $column ) {
			return $columns;
		}

		$attribute      = $this->get_attribute_taxonomy( $this->taxonomy );
		$attribute_type = $attribute->attribute_type;
		$this->preview( $attribute_type, $term_id );

		return $columns;
	}
	public function preview( $attribute_type, $term_id ) {
		$meta_key = 'pdesign_child_color'; // take first key for preview
		$this->color_preview( $attribute_type, $term_id, $meta_key );
		
	}

	public function color_preview( $attribute_type, $term_id, $key ) {
		if ( 'color' === $attribute_type ) {
			$primary_color = sanitize_hex_color( get_term_meta( $term_id, $key, true ) );
			if ( $primary_color ) {
				printf( '<div class="wvs-preview wvs-color-preview" style="background-color:%s;width:30px;height:30px;border: rgba(0, 0, 0, 0.2) 1px solid;border-radius:2px;"></div>', esc_attr( $primary_color ) );
			}
		}
	}
	public function delete_term( $term_id, $tt_id, $taxonomy ) {
		global $wpdb;

		$term_id = absint( $term_id );
		if ( $term_id && $taxonomy === $this->taxonomy ) {
			$wpdb->delete( $wpdb->termmeta, array( 'term_id' => $term_id ), array( '%d' ) );
		}
	}
	public function enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}
 	public function add() {
		$this->generate_fields();
	}

	private function generate_fields( $term = false ) {
		$screen           = get_current_screen();
		$screen_post_type = $screen ? $screen->post_type : '';
		$screen_taxonomy  = $screen ? $screen->taxonomy : '';

		if ( ( $screen_post_type === $this->post_type ) && ( $screen_taxonomy === $this->taxonomy ) ) {
			$this->generate_form_fields( $this->fields, $term );
		}
	}
	public function generate_form_fields( $fields, $term ) {
		if ( empty( $fields ) ) { return; }
		?>
		<div class="form-field term-<?php echo esc_attr( $fields ); ?>-wrap">
			<label for="tag-<?php echo esc_attr( $fields ); ?>">Select Color</label>
			 <input name="pd_child_color" type="color" id="tag-<?php echo esc_attr( $fields ); ?>" value="#f6b73c" style="width: 30%;height: 30px;" />
			<p id="<?php echo esc_attr( $fields ); ?>-description">Choose a color</p>
		</div>
		<?php
		wp_nonce_field('pd_swatches_term_meta', 'pd_swatches_term_meta_nonce');
	}
	public function edit( $term ) {
		$this->generate_fields_edit( $term );
	}
	private function generate_fields_edit( $term = false ) {
		$screen           = get_current_screen();
		$screen_post_type = $screen ? $screen->post_type : '';
		$screen_taxonomy  = $screen ? $screen->taxonomy : '';

		if ( ( $screen_post_type === $this->post_type ) && ( $screen_taxonomy === $this->taxonomy ) ) {
			$this->generate_form_fields_edit( $this->fields, $term );
		}
	}
	public function generate_form_fields_edit( $fields, $term ) {
		if ( empty( $fields ) ) { return; }
		$post_color = get_term_meta( $term->term_id, 'pdesign_child_color', true );
		?>
		<tr class="form-field term-<?php echo esc_attr( $fields ); ?>-wrap">
			<th scope="row">
				<label for="<?php echo esc_attr( $fields ); ?>">Slug</label></th>
			<td>
				<input name="pd_child_color" type="color" id="<?php echo esc_attr( $fields ); ?>" value="<?php echo ! empty( $post_color ) ? $post_color : '#f6b73c'; ?>" style="width: 30%;height: 30px;" />
				<p class="description" id="<?php echo esc_attr( $fields ); ?>-description">Choose a color</p>
			</td>
		</tr>
		<?php
		wp_nonce_field('pd_swatches_term_meta', 'pd_swatches_term_meta_nonce');
	}
	public function save( $term_id, $tt_id = '', $taxonomy = '' ) {
		
		if ( $taxonomy === $this->taxonomy ) {

			if ( ! isset( $_POST['pd_swatches_term_meta_nonce'] ) ) {
				return;
			}

			check_admin_referer('pd_swatches_term_meta', 'pd_swatches_term_meta_nonce');

			if( isset( $_POST['pd_child_color'] ) ){
				$post_value = $_POST['pd_child_color'];
				update_term_meta( $term_id, 'pdesign_child_color', $post_value );
			}
					
		}
	}
	public function get_attribute_taxonomy( $attribute_name ) {

		$taxonomy_attributes = wc_get_attribute_taxonomies();

		// $attribute_name = str_ireplace( 'pa_', '', wc_sanitize_taxonomy_name( $attribute_name ) );
		if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
			$attribute_name = str_replace( 'pa_', '', wc_sanitize_taxonomy_name( $attribute_name ) );
		}

		foreach ( $taxonomy_attributes as $attribute ) {

			// Skip taxonomy attributes that didn't match the query.
			/*if ( false === stripos( $attribute->attribute_name, $attribute_name ) ) {
				continue;
			}*/

			if ( $attribute->attribute_name !== $attribute_name ) {
				continue;
			}

			return $attribute;
		}

		return false;
	}
}
endif;