<?php 
namespace PDESIGN;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ProductDesign{
	public $version = PDESIGN_VERSION;
	public $path;
	public $url;
	public $basename;
	private $assets;
	public $admin;
	public $design_form;
	public $doajax;
	
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	public function __construct() {
		
		if( ! is_admin() ){
			add_action( 'init', array( $this, 'pdn_remove_my_action' ) );
		}
		$this->path     = plugin_dir_path(PDESIGN_FILE);
		$this->url      = plugin_dir_url(PDESIGN_FILE);
		$this->basename = plugin_basename(PDESIGN_FILE);
		$this->includes();
		spl_autoload_register( array( $this, 'loader' ) );
		$this->assets = new Assets();
		$this->admin  = new Admins();
		$this->design_form = new DesignForm();
		$this->doajax = new doAjax();


	
		
	}
	private function loader( $className ) {
		if ( ! class_exists( $className ) ) {
			$className=preg_replace(array( '/([a-z])([A-Z])/', '/\\\/' ),array('$1$2',DIRECTORY_SEPARATOR),
				$className
			);

			$className=str_replace( 'PDESIGN' . DIRECTORY_SEPARATOR, 'classes' . DIRECTORY_SEPARATOR, $className );
			$file_name = $this->path . $className . '.php';

			if ( file_exists( $file_name ) ) {
				require_once $file_name;
			}
		}
	}
	public function includes() {
		include pdesign()->path . 'includes/general.php';
		include pdesign()->path . 'includes/templates.php';
		include pdesign()->path . 'includes/colorVariation.php';
		
	}
	public static function pdesign_activate() {
		self::create_database();
	}
	public static function create_database() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$pet_order = "CREATE TABLE {$wpdb->prefix}pet_order (
			  	ID bigint NOT NULL AUTO_INCREMENT,
				user bigint DEFAULT NULL,
				author bigint DEFAULT NULL,
			  	PRIMARY KEY  (ID)
			) $charset_collate;";

		

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		//dbDelta( $pet_order );
	}
	public static function pdesign_deactivation(){

	}
	public function pdn_remove_my_action(){
		//remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
		remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
	}
	
}