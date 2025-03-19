<?php
/**
 * Plugin Name: Product Design
 * Description: Product Design
 * Version: 1.3.1
 * Author: Product Design API Developer
 * Requires Plugins: woocommerce
 **/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

 require __DIR__ . '/tcpdf/vendor/autoload.php';

define( 'PDESIGN_VERSION', '1.3.1' );
define( 'PDESIGN_FILE', __FILE__ );
define( 'PDESIGN_PLIGIN_FOLDER', plugin_dir_path( __FILE__ ));


if ( ! function_exists( 'pdesign' ) ) {
  
    function pdesign() {
        if ( isset( $GLOBALS['pdesign_plugin_info'] ) ) {
            return $GLOBALS['pdesign_plugin_info'];
        }

        $path    = plugin_dir_path( PDESIGN_FILE );
        $home_url = get_home_url();
        $parsed = parse_url($home_url);
        $base_path = (is_array($parsed) && isset($parsed['path'])) ? $parsed['path'] : '/';
        $base_path = rtrim($base_path, '/') . '/';
        // Get current URL.
        $current_url = trailingslashit( $home_url ) . substr($_SERVER['REQUEST_URI'], strlen($base_path));

        $info = array(
            'path'                 => $path,
            'url'                  => plugin_dir_url( PDESIGN_FILE ),
            'icon_dir'             => plugin_dir_url( PDESIGN_FILE ) . 'assets/img/',
            'font_dir'             => plugin_dir_url( PDESIGN_FILE ) . 'assets/font/',
            'current_url'          => $current_url,
            'basename'             => plugin_basename( PDESIGN_FILE ),
            'basepath'             => $base_path,
            'version'              => PDESIGN_VERSION,
            'nonce_action'         => 'pdesign_nonce_action',
            'nonce'                => '_pdesign_nonce',
            'template_path'        => apply_filters('pdesign_template_path', 'product-design/' ),
        );

        $GLOBALS['pdesign_plugin_info'] = (object) $info;
        return $GLOBALS['pdesign_plugin_info'];
    }
}


if (!class_exists('ProductDesign')){
    include_once 'classes/ProductDesign.php';
}

if ( ! function_exists( 'pdesign_load' ) ) {
    function pdesign_load() {
        return \PDESIGN\ProductDesign::instance();
    }
}

register_activation_hook( PDESIGN_FILE, array( '\PDESIGN\ProductDesign', 'pdesign_activate' ) );
register_deactivation_hook( PDESIGN_FILE, array( '\PDESIGN\ProductDesign', 'pdesign_deactivation' ) );

global $wp_filesystem;
if (empty($wp_filesystem)) {
    require_once (ABSPATH .'/wp-admin/includes/file.php');
    WP_Filesystem();
}

$GLOBALS['pdesign'] = pdesign_load();


add_filter( 'woocommerce_cart_item_name', function( $cart_item_name, $cart_item, $cart_item_key  ) {
   return $cart_item_name . ' - test';
},99);
 
