<?php
namespace PDESIGN;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class doAjax{
 	public function __construct(){
 		add_action("wp_ajax_pdesign_file_upload",array($this,"pdesign_file_upload"));
 		add_action("wp_ajax_nopriv_pdesign_file_upload",array($this,"pdesign_file_upload"));

 		add_action("wp_ajax_pdesign_html2canvas_upload",array($this,"pdesign_html2canvas_upload"));
 		add_action("wp_ajax_nopriv_pdesign_html2canvas_upload",array($this,"pdesign_html2canvas_upload"));

 		// test ---
 		add_action("wp_ajax_pdn_html_to_canva_upload",array($this,"pdn_html_to_canva_upload"));
 		add_action("wp_ajax_nopriv_pdn_html_to_canva_upload",array($this,"pdn_html_to_canva_upload"));

 		add_action("wp_ajax_cart_put_hold",array($this,"cart_put_hold"));
 		add_action("wp_ajax_nopriv_cart_put_hold",array($this,"cart_put_hold"));

 		add_action("wp_ajax_cart_get_hold",array($this,"cart_get_hold"));
 		add_action("wp_ajax_nopriv_cart_get_hold",array($this,"cart_get_hold"));

 		add_action("wp_ajax_prodesign_media_link_url",array($this,"prodesign_media_link_url"));
 		add_action("wp_ajax_nopriv_prodesign_media_link_url",array($this,"prodesign_media_link_url"));

 		add_filter( 'woocommerce_single_product_image_gallery_classes', function( $classes ) {
		    $classes[] = 'pdn-prodesign-class';

		    return $classes;
		} );
 		
 	}
 	public function pdesign_file_upload(){
 		$uploadedfile = $_FILES['pdesign_file'];
 		$upload_overrides = array('test_form' => false);
		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

		if ( $movefile && ! isset( $movefile['error'] ) ) {
				$file_path = $movefile['file'];
				$file_url  = $movefile['url'];
				$mime_type = '';
				if ( file_exists( $file_path ) ) {
					$image_info = getimagesize( $file_path );
					$mime_type  = is_array( $image_info ) && count( $image_info ) ? $image_info['mime'] : '';
				}

				$media_id = wp_insert_attachment(array(
						'guid'           => $file_path,
						'post_mime_type' => $mime_type,
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_url ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
						'post_type'      =>'pdesign-upload-custom',
						),
					$file_path, 0 );

				if ( $media_id ) {
					exit(wp_json_encode(array( 
						'filename' => $uploadedfile['name'],
						'file_url' => $file_url,
						'media_id' => $media_id )
				        )
				    );
				}
			}

 		wp_die();
 	}
 	public function pdesign_html2canvas_upload(){
 		$type = isset( $_POST['select_type'] ) ? $_POST['select_type'] : false;
 		$dir= wp_get_upload_dir();
 		$date=date("Ymd");
 		$uploadedfile=$_POST['pdesign_file'];
 		$string = str_replace('data:image/png;base64,', '', $uploadedfile);
 		$filename=$_POST['post_ID'].'-'.$date.'-'.time().'.png';
 		$dir_path=$dir['path'].'/'.$filename;
 		$dir_url=$dir['url'].'/'.$filename;
	    file_put_contents($dir_path, base64_decode($string));

		$movefile = array(
			'file'=>$dir_path,
			'url' =>$dir_url,
			'type'=>'image/png',
		);

		if ( $movefile && ! isset( $movefile['error'] ) ) {
				$file_path = $movefile['file'];
				$file_url  = $movefile['url'];
				$mime_type = '';
				if ( file_exists( $file_path ) ) {
					$image_info = getimagesize( $file_path );
					$mime_type  = is_array( $image_info ) && count( $image_info ) ? $image_info['mime'] : '';
				}

				$media_id = wp_insert_attachment(array(
						'guid'           => $file_path,
						'post_mime_type' => $mime_type,
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_url ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
						'post_type'      =>'pdesign-upload',
						),
					$file_path, 0 );

				if ( $media_id ) {
					$pdn = array( 
						'filename' => $filename,
						'file_url' => $file_url,
						'media_id' => $media_id,
						'type'	   =>  $type,
					);
					ob_start();
					if( $type ){
						pdesign_load_template( 'designed', $pdn );
					}
					$type_html = ob_get_clean();
					$output_array = array( 
						'filename'  => $filename,
						'file_url'  => $file_url,
						'media_id'  => $media_id,
						'type'	    => $type,
						'type_html' => $type_html,
					);
					wp_send_json_success( $output_array );
				}
			}

 		wp_die();
 	}
 	public function cart_put_hold(){
       $html=$_POST['cart_html'];
       $transient=$_POST['transient'];
       if ( false === ( $get_trans = get_transient($transient) ) ) {
     	set_transient($transient, $html, DAY_IN_SECONDS );
	   }
	  exit(wp_json_encode( array('success'=>'done')));
 	 wp_die();
 	}
 	public function cart_get_hold(){
     $key=$_POST['key'];
     $cart_html_transient = get_transient($key);
     exit(wp_json_encode( array('pdcart_html'=>wp_kses_stripslashes($cart_html_transient))));
 	 wp_die();	
 	}
 	public function prodesign_media_link_url(){
 		$media_id=$_POST['media_id'];
 		exit(wp_json_encode( array('media_url'=>wp_get_attachment_url($media_id))));
 		wp_die();
 	}
 	public function pdn_html_to_canva_upload(){
 		
 		$type 	  = sanitize_title( wp_slash( $_POST['type'] ) );
 		$random   = sanitize_title( wp_slash( $_POST['random'] ) );
 		$filename = sanitize_file_name( wp_slash( $_POST['name'] ) );
 		$dir 	  = wp_get_upload_dir();
 		$dir_path = $dir['path'] . '/' . $filename;
 		$dir_url  = $dir['url'] .'/' . $filename;

	    file_put_contents( $dir_path, base64_decode( sanitize_mime_type( wp_slash( $_POST['image'] ) ) ) );

		$movefile = array(
			'file'	=> $dir_path,
			'url'   => $dir_url,
			'type'  => 'image/png',
		);

		if ( $movefile && ! isset( $movefile['error'] ) ) {
				$file_path = $movefile['file'];
				$file_url  = $movefile['url'];
				$mime_type = '';
				if ( file_exists( $file_path ) ) {
					$image_info = getimagesize( $file_path );
					$mime_type  = is_array( $image_info ) && count( $image_info ) ? $image_info['mime'] : '';
				}

				$media_id = wp_insert_attachment(array(
						'guid'           => $file_path,
						'post_mime_type' => $mime_type,
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_url ) ),
						'post_content'   => '',
						'post_status'    => 'inherit',
						'post_type'      =>'pdesign-upload',
						),
					$file_path, 0 );

				if ( $media_id ) {
			
					wp_send_json_success( 
						array( 
							'filename'  => $filename,
							'file_url'  => $file_url,
							'media_id'  => $media_id,
							'type'	    => $type,
							'random'	=> $random,
						) 
					);
				}
			}

 		wp_die();
 	}
 }