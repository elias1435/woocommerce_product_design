<?php
namespace PDESIGN;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {
	
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
        // common for backend
        add_action( 'admin_enqueue_scripts', array( $this, 'common_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_meta_data' ) );
        // common for frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'common_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_meta_data' ) );

		 
	}
	private function get_default_localized_data() {
		return array(
			'ajaxurl'                      => admin_url('admin-ajax.php'),
			'home_url'                     => get_home_url(),
			'site_title'                   => get_bloginfo( 'title' ),
			'base_path'                    => pdesign()->basepath,
			'pdesign_url'                  => pdesign()->url,
			'nonce_key'                    => pdesign()->nonce,
			 pdesign()->nonce              => wp_create_nonce( pdesign()->nonce_action ),
			'current_user'                 => wp_get_current_user(),
			'is_ssl'                       => is_ssl(),
		);
	}	

	public function admin_scripts() {
		wp_enqueue_style('pdesign-backend',pdesign()->url.'assets/css/backend.css', array(), PDESIGN_VERSION );
		wp_enqueue_script('pdesign-backend',pdesign()->url . 'assets/js/backend.js', array(), PDESIGN_VERSION,true);
		
	}
	public function frontend_scripts() {
		wp_enqueue_style('pdesign-frontend',pdesign()->url.'assets/css/frontend.css', array(), PDESIGN_VERSION );
		
		wp_enqueue_script('pdesign-html2canvas',pdesign()->url.'assets/js/html2canvas.min.js', array(), PDESIGN_VERSION, true );
		
		//wp_enqueue_script('pdesign-jq-mobile-touch',pdesign()->url.'assets/js/jquery.mobile.custom.min.js',array(), PDESIGN_VERSION, true);

		wp_enqueue_script('pdesign-jq-ui','https://code.jquery.com/ui/1.13.3/jquery-ui.js',array(), PDESIGN_VERSION,true);
		wp_enqueue_script('pdesign-jq-ui-mobile','https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js',array(), PDESIGN_VERSION,true);
	
		wp_enqueue_script('pdesign-frontend',pdesign()->url.'assets/js/frontend.js',array(), PDESIGN_VERSION, true);
		
		
	}
	public function load_meta_data() {
		$localize_data = apply_filters('pdesign_localize_data', $this->get_default_localized_data() );
		wp_localize_script('pdesign-common', '_pdesignobject', $localize_data );
		wp_localize_script('pdesign-frontend', 'pdnFrontend', $localize_data );
	}
	public function common_scripts() {
	   wp_enqueue_script('pdesign-common',pdesign()->url.'assets/js/common.min.js',array('jquery','wp-i18n'), PDESIGN_VERSION,true);
	}

}
