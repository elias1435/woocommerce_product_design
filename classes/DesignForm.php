<?php
namespace PDESIGN;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use PDESIGN\colorVariation;
use TCPDF;
class DesignForm{
 	public function __construct(){
 		add_action( 'rest_api_init', array($this,'prodesign_create_cart_design_end'));
 		add_action('woocommerce_before_add_to_cart_form', array($this,'product_design_form'));
 		add_action('woocommerce_before_add_to_cart_form', array($this,'product_design_form_apply'));

 		add_filter( 'woocommerce_add_cart_item_data', array($this,'prodesign_add_cart_item_data'), 10, 3 );
 	     
 	     //add_filter( 'woocommerce_get_item_data', array($this,'prodesign_get_item_data'), 10, 2 );
 	     
		add_action('woocommerce_checkout_create_order_line_item',array($this,'prodesign_create_order_line_item'),10,4);
 	     add_filter('woocommerce_order_item_display_meta_key', array($this,'prodesign_order_item_meta_title'),20,3);
 	     add_filter( 'woocommerce_order_item_display_meta_value', array($this,'prodesign_order_item_meta_value'),20,3);
 		
 		add_action('woocommerce_before_cart_item_quantity_zero',array($this,'prodesign_quantity_zero_remove'),1,1);
 		
 		add_filter( 'woocommerce_email_attachments', array($this,'prodesign_attach_png_to_emails'), 10, 4 );
 		/*----test--*/
 		//add_action( 'woocommerce_variation_options', array( $this, 'add_additional_variation_image_ids' ), 10, 3 );
 		
 		//add_action( 'woocommerce_admin_process_variation_object', array( $this, 'save_additional_variation_image_ids' ), 10, 2 );

 		add_action( 'woocommerce_variation_options', array( $this, 'pdn_product_image_backend' ), 10, 3 );
 		add_action( 'woocommerce_admin_process_variation_object', array( $this, 'pdn_product_image_backend_save' ), 10, 2 );

 
		/*-----*/
		add_action( 'admin_init', array( $this, 'add_attribute_meta' ) );

		/*----*/
		add_filter( 'woocommerce_dropdown_variation_attribute_options_args', static function( $args ) {
			$color_term = get_option( 'product_design_color_taxonomies' );
			if( $color_term == $args['attribute'] ){
				$args['class'] = 'prodesign-color-variation-select';
			}
		    
		    return $args;
		}, 2 );

		add_filter( 'product_attributes_type_selector', array( $this, 'attribute_types' ) );
		add_action( 'woocommerce_product_option_terms', array( $this, 'product_option_terms' ), 10, 3 );

		// gallery html
		
		add_action( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'pdn_gallery_hook_template' ), 10, 2 );
 	}
 	// @see: file woocommerce/includes/admin/meta-boxes/views/html-product-attribute-inner.php
	// @see: file woocommerce/includes/admin/meta-boxes/views/html-product-attribute.php
	public function product_option_terms( $attribute_taxonomy, $i, $attribute ) {

		if ( 'select' !== $attribute_taxonomy->attribute_type && in_array( $attribute_taxonomy->attribute_type, array_keys( $this->attribute_types() ), true ) ) {

			$attribute_orderby = ! empty( $attribute_taxonomy->attribute_orderby ) ? $attribute_taxonomy->attribute_orderby : 'name';
			/**
			 * Filter the length (number of terms) rendered in the list.
			 *
			 * @since 8.8.0
			 * @param int $term_limit The maximum number of terms to display in the list.
			 */
			$term_limit = absint( apply_filters( 'woocommerce_admin_terms_metabox_datalimit', 50 ) );
			?>

		<select multiple="multiple"
				data-minimum_input_length="0"
				data-limit="<?php echo esc_attr( $term_limit ); ?>" data-return_id="id"
				data-placeholder="<?php esc_attr_e( 'Select values', 'woo-variation-swatches' ); ?>"
				data-orderby="<?php echo esc_attr( $attribute_orderby ); ?>"
				class="multiselect attribute_values wc-taxonomy-term-search"
				name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
				data-taxonomy="<?php echo esc_attr( $attribute->get_taxonomy() ); ?>">
			<?php
			$selected_terms = $attribute->get_terms();
			if ( $selected_terms ) {
				foreach ( $selected_terms as $selected_term ) {
					/**
					 * Filter the selected attribute term name.
					 *
					 * @since 3.4.0
					 * @param string  $name Name of selected term.
					 * @param array   $term The selected term object.
					 */
					echo '<option value="' . esc_attr( $selected_term->term_id ) . '" selected="selected">' . esc_html( apply_filters( 'woocommerce_product_attribute_term_name', $selected_term->name, $selected_term ) ) . '</option>';
				}
			}
			?>
		</select>
				<button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'woo-variation-swatches' ); ?></button>
				<button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'woo-variation-swatches' ); ?></button>
				<button class="button fr plus add_new_attribute"><?php esc_html_e( 'Create value', 'woo-variation-swatches' ); ?></button>

				<?php
		}

	}
 	public function attribute_types( ) {
		return array(
				'select' => esc_html__( 'Select', 'prodesign' ),
				'color'  => esc_html__( 'Color', 'prodesign' ),
			);
	}
	
 	public function prodesign_create_cart_design_end(){
 		register_rest_route(
 			'wp/v2',
 			'/prodesign-cart-file',
 			array(
 				'methods' => 'GET',
 				'callback'=> array( $this, 'prodesign_get_file_in_cart' ),
 				'permission_callback' => '__return_true' 
 			)
 		);
 	}
 	public function prodesign_get_file_in_cart(){
 	   
 		return rest_ensure_response( array("test") );
 	}
 	public function product_design_form(){
 		ob_start();
		pdesign_load_template('form', array('display' => true));
 		echo apply_filters( 'product_design_form/index', ob_get_clean() );
 	}
 	public function product_design_form_apply(){
 		ob_start();
		pdesign_load_template('apply', array('display' => true));
 		echo apply_filters( 'product_design_apply/index', ob_get_clean() );
 	}
 	public function prodesign_add_cart_item_data($cart_item_data, $product_id, $variation_id){
 		if( class_exists( 'TCPDF' ) ){
 			$attachments = array();
 			$attachment_print = array();
 			if ( isset( $_POST['pdn_design'] ) ) {
	        		$dfile = base64_decode( sanitize_mime_type( wp_slash( $_POST['pdn_design'] ) ) );
	        		$print_dfile = base64_decode( sanitize_mime_type( wp_slash( $_POST['pdn_print_design'] ) ) );
	        		$attachments[] = $dfile;
	        		$attachment_print[] = $print_dfile;
        		}
	        	if( isset( $_POST['pdn_front'] ) ){
	        		$dfile = base64_decode( sanitize_mime_type( wp_slash( $_POST['pdn_front'] ) ) );
	        		$print_dfile = base64_decode( sanitize_mime_type( wp_slash( $_POST['pdn_print_front'] ) ) );
	        		$attachments[] = $dfile;
	        		$attachment_print[] = $print_dfile;
	        	}
	        	if( isset( $_POST['pdn_back'] ) ){
	        		$dfile = base64_decode( sanitize_mime_type( wp_slash( $_POST['pdn_back'] ) ) );
	        		$print_dfile = base64_decode( sanitize_mime_type( wp_slash( $_POST['pdn_print_back'] ) ) );
	        		$attachments[] = $dfile;
	        		$attachment_print[] = $print_dfile;
	        	}
	        	if( isset( $_POST['pdn_rsleeve'] ) ){
	        		$dfile = base64_decode( sanitize_mime_type( wp_slash( $_POST['pdn_rsleeve'] ) ) ); 
	        		$print_dfile = base64_decode( sanitize_mime_type( wp_slash( $_POST['pdn_print_rsleeve'] ) ) ); 
	        		$attachments[] = $dfile;
	        		$attachment_print[] = $print_dfile;
	        	}
	        	if( isset( $_POST['pdn_lsleeve'] ) ){
	        		$dfile = base64_decode( sanitize_mime_type( wp_slash( $_POST['pdn_lsleeve'] ) ) ); 
	        		$print_dfile = base64_decode( sanitize_mime_type( wp_slash( $_POST['pdn_print_lsleeve'] ) ) ); 
	        		$attachments[] = $dfile;
	        		$attachment_print[] = $print_dfile; 
	        	}

	        	$cart_item_data['product_design_pdf'] = $this->pdn_convert_images_to_pdf( $attachments );
	        	$cart_item_data['product_design_print_pdf'] = $this->pdn_convert_images_to_pdf( $attachment_print );
 		}
        	
    	return $cart_item_data;
 	}
 	public function prodesign_get_item_data($item_data, $cart_item_data){
 		 
    		if ( isset( $cart_item_data['product_design_pdf'] ) ) {
	        $item_data[] = array(
	        	'key'   => 'product_design_tpdf',
	        	'value' => $cart_item_data['product_design_pdf'],
	        );
    		} 
    		if ( isset( $cart_item_data['product_design_print_pdf'] ) ) {
	        $item_data[] = array(
	        	'key'   => 'product_design_print_tpdf',
	        	'value' => $cart_item_data['product_design_print_pdf'],
	        );
    		} 

    		return $item_data;
 	}
 	public function prodesign_create_order_line_item($item, $cart_item_key, $values, $order){
    		if ( isset( $values['product_design_pdf'] ) ) {
	        $item->add_meta_data('product_design_tpdf', $values['product_design_pdf'], true);
    		}
    		if ( isset( $values['product_design_print_pdf'] ) ) {
	        $item->add_meta_data('product_design_print_tpdf', $values['product_design_print_pdf'], true);
    		}
 	}
 	public function prodesign_attach_png_to_emails($attachments, $email_id, $order, $email){
 		 $email_ids = array( 'new_order', 'customer_processing_order' );

 		    if ( !is_a($order,'WC_Order') || !isset($email_id) ) {
       			 return $attachments;
    		}
            
           if ( in_array ( $email_id, $email_ids ) ) {
			foreach ( $order->get_items() as $item_id => $item ) {

			    $pdf = wc_get_order_item_meta( $item_id, 'product_design_tpdf', true ); 
			    if($pdf){
			    	$attachments[] = get_attached_file($pdf);
			    }

			}
		}

		return $attachments;
 	}
 
   public function prodesign_quantity_zero_remove($cart_item_key){
        global $woocommerce;
        $cart = $woocommerce->cart->get_cart();
        foreach($cart as $key=>$values){
	        if($values['product_design_pdf']==$cart_item_key){
	            unset( $woocommerce->cart->cart_contents[ $key ] );
	        }
	        if($values['product_design_print_pdf']==$cart_item_key){
	            unset( $woocommerce->cart->cart_contents[ $key ] );
	        }
        }
    }
    public function prodesign_order_item_meta_title( $key, $meta, $item){
    		if('product_design_tpdf' === $meta->key){ 
        		$key = 'Product Design File'; 
    		}
    		if('product_design_print_tpdf' === $meta->key){ 
        		$key = 'Product Print File'; 
    		}
    		
     
    		return $key;
    }
    public function prodesign_order_item_meta_value($value, $meta, $item ){

    		if('product_design_tpdf' === $meta->key){ 
        		$value = "<a href='".wp_get_attachment_url($value)."' download>Download</a>"; 
    		}
    		if('product_design_print_tpdf' === $meta->key){ 
        		$value = "<a href='".wp_get_attachment_url($value)."' id='pdn-print-file-wp' download>Download</a>"; 
    		}

    		return $value;
    }
    public function add_additional_variation_image_ids( $loop, $variation_data, $variation ) {
	    $variation_id     = $variation->ID;
	    $variation_object = wc_get_product( $variation_id );
	    $color_term 	  = 'attribute_' . get_option( 'product_design_color_taxonomies' );
	    $attribute_color  = wc_get_product_variation_attributes( $variation_id );

	    if( array_key_exists( $color_term, $attribute_color ) ):
		    $attachment_ids   = $variation_object->get_meta( 'additional_img_ids' ); // Get Attachments Ids
		    $attachment_ids   = empty($attachment_ids) ? array( 0 => '' ) : $attachment_ids;
		    $count            = count($attachment_ids);
		    $placeholder      = esc_url( wc_placeholder_img_src() );

		    $upload_img_txt   = esc_html__( 'Upload an image', 'woocommerce' );
		    $remove_img_txt   = esc_html__( 'Remove this image', 'woocommerce' );
		    $add_txt          = esc_html__( 'Add', 'woocommerce' );
		    $remove_txt       = esc_html__( 'Remove', 'woocommerce' );

		    echo '<div class="custom-uploads">
		    <h4 class="pd-variontion-upload-title">' . __( 'Attachment Front, back and sleeves:', 'woocommerce' ) . '</h4><div class="pd-variation-image-flex">';
		    // Loop through each existing attachment image ID
		    foreach( $attachment_ids as $index => $image_id ) {
		        // Add an Image field
		        printf('<div class="image-box"><p class="form-row form-row-wide upload_image">
		            <a href="#" class="upload_image_button tips %s" data-tip="%s" rel="%s"><img src="%s" />
		            <input type="hidden" name="additional_img_ids-%s-%s" class="upload_image_id" value="%s" /></a>
		            <p></div>', 
		            $image_id ? 'remove' : '', $image_id ? $remove_img_txt : $upload_img_txt, $variation_id,
		            $image_id ? esc_url( wp_get_attachment_thumb_url( $image_id ) ) : $placeholder, $loop, $index, $image_id
		        );
		    }
		    // Add the buttons
		    printf('<div class="buttons-box"><p>
		        <button type="button" class="add-slot" data-loop="%d">%s</button>
		        <button type="button" class="remove-slot" data-loop="%d"%s>%s</button>
		        <input type="hidden" name="slot-index-%d" value="%s" /><input type="hidden" name="ph-img-%s" value="%s" /></a><p></div>', 
		        $loop, $add_txt, $loop, $count == 1 ? ' style="display:none;"' : '', $remove_txt, $loop, $count, $loop, $placeholder
		    );

		    echo '</div></div>';
	    endif;
	}
	public function save_additional_variation_image_ids( $variation, $i ) {
		$attribute_color = wc_get_product_variation_attributes( $variation->get_id() );
		$color_term 	  = 'attribute_' . get_option( 'product_design_color_taxonomies' );
	    	if ( isset( $_POST["slot-index-{$i}"] ) && $_POST["slot-index-{$i}"] > 1 && array_key_exists( $color_term, $attribute_color ) ) {
	        $attachment_ids = array(); // Initialize

	        // Loop through each posted attachment Id for the current variation
	        for( $k = 0; $k < $_POST["slot-index-{$i}"]; $k++ ) {
	            if ( isset( $_POST["additional_img_ids-{$i}-{$k}"] ) && ! empty( $_POST["additional_img_ids-{$i}-{$k}"] ) ) {
	                $attachment_ids[$k] = esc_attr( $_POST["additional_img_ids-{$i}-{$k}"] ); // Set it in the array
	            }
	        }
	        if( ! empty( $attachment_ids ) ) {
	            $variation->update_meta_data( 'additional_img_ids', $attachment_ids ); // save
	        }
	    	}
	}
	public function add_attribute_meta(){
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $taxonomy ) {
				$attribute_name = wc_attribute_taxonomy_name( $taxonomy->attribute_name );
				$attribute_type = $taxonomy->attribute_type; 
				if ( 'color' === $attribute_type ) {
					update_option( 'product_design_color_taxonomies', $attribute_name );
					new colorVariation( $attribute_name, 'product', $attribute_type );
				}
			}
		}
	}
	public function pdn_product_image_backend( $loop, $variation_data, $variation ){
		$variation_id     = $variation->ID;
	    $variation_object = wc_get_product( $variation_id );
	    $color_term 	  = 'attribute_' . get_option( 'product_design_color_taxonomies' );
	    $attribute_color  = wc_get_product_variation_attributes( $variation_id );

	    if( array_key_exists( $color_term, $attribute_color ) ):
		    $front_id   = $variation_object->get_meta( 'pdn_front_image_id' );
		    $back_id    = $variation_object->get_meta( 'pdn_back_image_id' );
		    $r_sleeve_id  = $variation_object->get_meta( 'pdn_r_sleeve_image_id' );
		    $l_sleeve_id  = $variation_object->get_meta( 'pdn_l_sleeve_image_id' );
		   
		    $placeholder      = esc_url( wc_placeholder_img_src() );

		    $upload_img_txt   = esc_html__( 'Upload an image', 'woocommerce' );
		    $remove_img_txt   = esc_html__( 'Remove this image', 'woocommerce' );
		    $add_txt          = esc_html__( 'Add', 'woocommerce' );
		    $remove_txt       = esc_html__( 'Remove', 'woocommerce' );
		    ?>
		    <div class="custom-uploads">
		    <div class="pd-variation-image-flex">
		    	 <div class="pdn-flex-box-backend">
		    		<h4 class="pdn-upload-title-bknd"><?php echo __( 'Front', 'woocommerce' ); ?></h4>
		    
		  <?php  
		        // Add an Image field /Loop through each existing attachment image ID
		        printf('<div class="image-box"><p class="form-row form-row-wide upload_image">
		            <a href="#" class="upload_image_button tips %s" data-tip="%s" rel="%s"><img src="%s" />
		            <input type="hidden" name="front_image_id" class="upload_image_id" value="%s" /></a>
		            <p></div>', 
		            $front_id ? 'remove' : '', $front_id ? $remove_img_txt : $upload_img_txt, $variation_id,
		            $front_id ? esc_url( wp_get_attachment_thumb_url( $front_id ) ) : $placeholder, $front_id
		        );
		    ?>
		    </div>
		    <div class="pdn-flex-box-backend">
		    		<h4 class="pdn-upload-title-bknd"><?php echo __( 'Back', 'woocommerce' ); ?></h4>
		    
		  <?php  
		        // Add an Image field /Loop through each existing attachment image ID
		        printf('<div class="image-box"><p class="form-row form-row-wide upload_image">
		            <a href="#" class="upload_image_button tips %s" data-tip="%s" rel="%s"><img src="%s" />
		            <input type="hidden" name="back_image_id" class="upload_image_id" value="%s" /></a>
		            <p></div>', 
		            $back_id ? 'remove' : '', $back_id ? $remove_img_txt : $upload_img_txt, $variation_id,
		            $back_id ? esc_url( wp_get_attachment_thumb_url( $back_id ) ) : $placeholder, $back_id
		        );
		    ?>
		    </div>

		    <div class="pdn-flex-box-backend">
		    		<h4 class="pdn-upload-title-bknd"><?php echo __( 'R-sleeve', 'woocommerce' ); ?></h4>
		    
		  <?php  
		        // Add an Image field /Loop through each existing attachment image ID
		        printf('<div class="image-box"><p class="form-row form-row-wide upload_image">
		            <a href="#" class="upload_image_button tips %s" data-tip="%s" rel="%s"><img src="%s" />
		            <input type="hidden" name="r_sleeve_image_id" class="upload_image_id" value="%s" /></a>
		            <p></div>', 
		            $r_sleeve_id ? 'remove' : '', $r_sleeve_id ? $remove_img_txt : $upload_img_txt, $variation_id,
		            $r_sleeve_id ? esc_url( wp_get_attachment_thumb_url( $r_sleeve_id ) ) : $placeholder, $r_sleeve_id
		        );
		    ?>
		    </div>
		    <div class="pdn-flex-box-backend">
		    		<h4 class="pdn-upload-title-bknd"><?php echo __( 'L-sleeve', 'woocommerce' ); ?></h4>
		    
		  <?php  
		        // Add an Image field /Loop through each existing attachment image ID
		        printf('<div class="image-box"><p class="form-row form-row-wide upload_image">
		            <a href="#" class="upload_image_button tips %s" data-tip="%s" rel="%s"><img src="%s" />
		            <input type="hidden" name="l_sleeve_image_id" class="upload_image_id" value="%s" /></a>
		            <p></div>', 
		            $l_sleeve_id ? 'remove' : '', $l_sleeve_id ? $remove_img_txt : $upload_img_txt, $variation_id,
		            $l_sleeve_id ? esc_url( wp_get_attachment_thumb_url( $l_sleeve_id ) ) : $placeholder, $l_sleeve_id
		        );
		    ?>
		    </div>

		    </div>
		</div>
		    <?php
	    endif;
	}
	public function pdn_product_image_backend_save( $variation, $i ) {
		$attribute_color = wc_get_product_variation_attributes( $variation->get_id() );
		$color_term 	  = 'attribute_' . get_option( 'product_design_color_taxonomies' );
	    	if ( array_key_exists( $color_term, $attribute_color ) ) {
	        
	        if( isset( $_POST['front_image_id'] ) && ! empty( $_POST['front_image_id'] ) ){
	        	$variation->update_meta_data( 'pdn_front_image_id', esc_attr( $_POST["front_image_id"] ) ); // save
	        }
	        if( isset( $_POST['back_image_id'] ) && ! empty( $_POST['back_image_id'] ) ){
	        	$variation->update_meta_data( 'pdn_back_image_id', esc_attr( $_POST["back_image_id"] ) ); // save
	        }
	        if( isset( $_POST['r_sleeve_image_id'] ) && ! empty( $_POST['r_sleeve_image_id'] ) ){
	        	$variation->update_meta_data( 'pdn_r_sleeve_image_id', esc_attr( $_POST["r_sleeve_image_id"] ) ); // save
	        }
	        if( isset( $_POST['l_sleeve_image_id'] ) && ! empty( $_POST['l_sleeve_image_id'] ) ){
	        	$variation->update_meta_data( 'pdn_l_sleeve_image_id', esc_attr( $_POST["l_sleeve_image_id"] ) ); // save
	        }

	    	}
	}
	public function pdn_gallery_hook_template( $html, $thumbnails_id ){
		global $product;
		$attachment_ids = $product->get_gallery_image_ids();
		$variations = '';
		if( is_a( $product, 'WC_Product_Variable' ) ){
			$variations = $product->get_available_variations();
			$color_term = 'attribute_' . get_option( 'product_design_color_taxonomies' );
		}

		$wrapper_classname = 'pdesign-gallery-image';
		$wrapper_idname    = 'pdesign-gallery-image';
		$html  = sprintf( '<div class="%s">', esc_attr( 'pdesign-gallery-wrap' ) );
		$html  .= sprintf( '<div class="%s" id="%s">', esc_attr( $wrapper_classname ), esc_attr( $wrapper_idname ) );
		$html  .= sprintf( '<img src="%s" alt="%s" class="wp-post-image wp-pd-image-opacity" />', $thumbnails_id ? esc_url( wp_get_attachment_url( $thumbnails_id, 'full' ) ) : esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
		$html .= sprintf( '<div class="%s" id="%s">', esc_attr( 'pdesign-image-can' ), esc_attr( 'pdesign-image-can') );
		$html .= '</div>';
		$html .= sprintf( '<div class="%s"  id="%s">', esc_attr( 'pdesign-text-can pdn-text-active' ), esc_attr( 'pdesign-text-can') );
		$html .= '</div>';
		$html .= sprintf( '<div class="%s" id="%s">', esc_attr( 'pdesign-more-on' ), esc_attr( 'pdesign-more-on') );
		$html .= '</div>';
		$html .= '</div>';

		if( ! empty( $attachment_ids ) ){
			$html .= sprintf( '<div class="%s">', esc_attr( 'pdesign-gallery') );
			$html .= sprintf( '<ul class="%s">', esc_attr( 'pdesign-gallery-list' ) );
			$html .= sprintf( '<li class="pd-active"><img src="%s" alt=""></li>', esc_url( wp_get_attachment_url( $thumbnails_id ) ) );
			foreach( $attachment_ids as $key => $attachment_id ){
				$html .= sprintf( '<li class=""><img src="%s" alt=""></li>', esc_url( wp_get_attachment_url( $attachment_id ) ) );
			}
			$html .= '</ul>'; 
			$html .= '</div>';
		}

		
		if( ! empty( $variations ) ){

			foreach( $variations as $variable ):
			$variation_object = wc_get_product( $variable['variation_id'] );
			$front_image   		= $variation_object->get_meta( 'pdn_front_image_id' );
			$back_image   		= $variation_object->get_meta( 'pdn_back_image_id' );
			$r_sleeve_image   	= $variation_object->get_meta( 'pdn_r_sleeve_image_id' );
			$l_sleeve_image   	= $variation_object->get_meta( 'pdn_l_sleeve_image_id' );
			//esc_url( $variable['image']['url'] );
			$attribbute_color = wc_get_product_variation_attributes( $variation_object->get_id() );
				if ( array_key_exists(  $color_term, $attribbute_color ) ):
					$html .= sprintf( '<div class="prodesign-varibale" id="prodesign-varibale-%s">', esc_attr($attribbute_color[$color_term] ) );
					$html .= sprintf( '<ul class="prodesign-variable-gellery" id="prodesign-variable-gellery-%s">', esc_attr( $attribbute_color[$color_term] ) );
					
					if( $front_image ):
						$html .= sprintf( '<li class="pd-active" id="pdn-select-%s-front" data-set="front"><img src="%s"></li>', esc_attr( $attribbute_color[$color_term] ),  esc_url( wp_get_attachment_url( $front_image ) ) );
					endif;

					if( $back_image ):
						$html .= sprintf( '<li class="" id="pdn-select-%s-back" data-set="back"><img src="%s"></li>', esc_attr( $attribbute_color[$color_term] ),  esc_url( wp_get_attachment_url( $back_image ) ) );
					endif;

					if( $r_sleeve_image ):
						$html .= sprintf( '<li class="" id="pdn-select-%s-rsleeve" data-set="rsleeve"><img src="%s"></li>', esc_attr( $attribbute_color[$color_term] ),  esc_url( wp_get_attachment_url( $r_sleeve_image ) ) );
					endif;

					if( $l_sleeve_image ):
						$html .= sprintf( '<li class="" id="pdn-select-%s-lsleeve" data-set="lsleeve"><img src="%s"></li>', esc_attr( $attribbute_color[$color_term] ),  esc_url( wp_get_attachment_url( $l_sleeve_image ) ) );
					endif;

					$html .= '</ul>';
					$html .= '</div>';
				endif;	
			endforeach;
		}

		$html .= '</div>';	
		echo ( $html );

	}
	public function pdn_convert_images_to_pdf( $images ){
		
		$dir 	 = wp_get_upload_dir();
		$name      = date("dmY").'-'.time().'-product-design.pdf';
 		$dir_path  = $dir['path'] . '/' . $name;
 		$dir_url   = $dir['url'] .'/' . $name;

		$pdf = new TCPDF();
		$pdf->SetTitle( 'Product Design' );
		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		foreach( $images as $img ){
			$pdf->SetMargins(30, 20, 30, true);
			$pdf->AddPage('P','A4');
			$pdf->Image('@'.$img, '', '', '', '', 'PNG', '', 'T', false, 300, 'C', false, false, 0, false, false, false);
		}

		$pdf->Output($dir_path, 'F');

		return $this->pdn_upload_pdf_to_server( array(
			'file' => $dir_path,
			'url'  => $dir_url,
			'type' => 'application/pdf',
		));
	}
	public function pdn_upload_pdf_to_server( $movefile ){

		if ( $movefile && ! isset( $movefile['error'] ) ) {
			$file_path = $movefile['file'];
			$file_url  = $movefile['url'];
			$mime_type = '';
			if ( file_exists( $file_path ) ) {
				$image_info = getimagesize( $file_path );
				$mime_type  = is_array( $image_info ) && count( $image_info ) ? $image_info['mime'] : '';
			}

			$rdfile =  wp_insert_attachment(array(
					'guid'           => $file_path,
					'post_mime_type' => $mime_type,
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_url ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
					'post_type'      =>'pdesign-upload',
					),
				$file_path, 0 );

		}

 		return $rdfile;
	}
}










