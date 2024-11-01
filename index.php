<?php if ( ! defined( 'ABSPATH' ) ) exit; 
/*
	Plugin Name: Splitter Orders For Woocommerce
	Plugin URI: 
	Description: This plugin splits single order into multiple orders.
	Version: 1.0
	Author: Liyanitsolution
	Author URI: https://liyanitsolution.com/
	Text Domain: splitter-orders-for-woocommerce
	License: GPLv3

*/

global $wpdb;
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} else {

    clearstatcache();
}


require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
define('lisg_liyanitsolution_plugin_dir', dirname(__FILE__));


register_activation_hook(__FILE__, 'lisg_plugin_activate');

function lisg_plugin_activate() {
    $option_name = 'lisg_ordersplitterpro';
    $new_value = 'default';
    update_option($option_name, $new_value);
	
}

// Deactivation Pluign 
function lisg_deactivation() {
    $option_name = 'lisg_auto_forced';
    $new_value = 'no';
    update_option($option_name, $new_value);
    $option_name = 'lisg_ordersplitterpro';
    $new_value = '';
    update_option($option_name, $new_value);
}

register_deactivation_hook(__FILE__, 'lisg_deactivation');

// Uninstall Pluign 
function lisg_uninstall() {
    $option_name = 'lisg_auto_forced';
    $new_value = 'no';
    update_option($option_name, $new_value);
    $option_name = 'lisg_ordersplitterpro';
    $new_value = '';
    update_option($option_name, $new_value);
}


$LIYANITSOLUTION_all_plugins = get_plugins();

$LIYANITSOLUTION_activate_all_plugins = apply_filters('active_plugins', get_option('active_plugins'));

if (array_key_exists('woocommerce/woocommerce.php', $LIYANITSOLUTION_all_plugins) && in_array('woocommerce/woocommerce.php', $LIYANITSOLUTION_activate_all_plugins)) {
    $optionVal = get_option('lisg_auto_forced');
    $splitterDefault = get_option('lisg_ordersplitterpro');
    if ($optionVal == 'yes' && $splitterDefault == 'default') {
        require_once lisg_liyanitsolution_plugin_dir . '/inc/ordersplitter.php';
    } 
	else {
        
    }
}





add_action( 'woocommerce_email', 'lisg_remove_hooks' );

function lisg_remove_hooks( $email_class ) {
		remove_action( 'woocommerce_low_stock_notification', array( $email_class, 'low_stock' ) );
		remove_action( 'woocommerce_no_stock_notification', array( $email_class, 'no_stock' ) );
		remove_action( 'woocommerce_product_on_backorder_notification', array( $email_class, 'backorder' ) );
		
		// New order emails
		remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		
		// Processing order emails
		remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
		
		// Completed order emails
		remove_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
			
		// Note emails
		remove_action( 'woocommerce_new_customer_note_notification', array( $email_class->emails['WC_Email_Customer_Note'], 'trigger' ) );
}


	

add_action('woocommerce_checkout_create_order', 'lisg_before_checkout_create_order', 20, 2);
function lisg_before_checkout_create_order( $order, $data ) {
 $order->update_meta_data( '_custom_meta_hide', 'yes' );
 

}

add_action( 'woocommerce_thankyou', 'Lisg_Save_flag_for_order', 20, 1);
function Lisg_Save_flag_for_order( $order_id ){
  $order = wc_get_order($order_id);
  $order->update_meta_data('Lisg_Save_flag_for_order', 'yes');
  $order->save();
}


function lisg_action_woocommerce_checkout_order_processed( $order_id, $posted_data, $order ) {
 $optionVal = get_option('lisg_auto_forced');
	 $splitterDefault = get_option('lisg_ordersplitterpro');
	 $ordersids =  get_post_meta($order_id, 'order_ids',true);
	 	if($optionVal=='yes' ){
	if($splitterDefault =='splitattributeexist' &&  $ordersids ==''){
	update_post_meta($order_id,'order_status_result','Main Order');
	
	}else
	{
	
		update_post_meta($order_id,'_order_total',0);  
		update_post_meta($order_id,'order_status_result','Main Order');  
		}
	}
}; 
add_action( 'woocommerce_checkout_order_processed', 'lisg_action_woocommerce_checkout_order_processed', 10, 3 ); 

function lisg_wc_new_order_column( $columns ) {
    $columns['Liyanitsolution_Status'] = 'Liyanitsolution Status';
    return $columns;
}
add_filter( 'manage_edit-shop_order_columns', 'lisg_wc_new_order_column' );

function lisg_wc_cogs_add_order_profit_column_content( $column ) {
    global $post;

    if ( 'Liyanitsolution_Status' === $column ) {
		
		echo get_post_meta($post->ID,'order_status_result',true);
    
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'lisg_wc_cogs_add_order_profit_column_content' );


	 
add_filter( 'woocommerce_endpoint_order-received_title', 'liyanitsolution_thank_you_title' );
 
function liyanitsolution_thank_you_title( $old_title ){
	$optionVal = get_option('lisg_auto_forced');
	 $splitterDefault = get_option('lisg_ordersplitterpro');
	 if ($optionVal == 'yes' && $splitterDefault == 'splitaccordingattribute') {
  $order_id = wc_get_order_id_by_order_key( $_GET['key'] ); 
  update_post_meta($order_id,'_order_total',0);  
 	} ?>
	<script>
	jQuery(document).ready(function () {
    jQuery('.woocommerce-order-details__title').text('Main Order details');
});
</script>
	<?php
	 
}


if (!class_exists('lisg_main_cls')) {

    class lisg_main_cls {

        public function __construct() {
            add_action('init', array($this, 'init_sunarc'));
        }

        public function init_sunarc() {
			 define('lisg_liyanitsolution_version', '1.0.1');
            !defined('lisg_liyanitsolution_path') && define('lisg_liyanitsolution_path', plugin_dir_path(__FILE__));
            !defined('lisg_liyanitsolution_url') && define('lisg_liyanitsolution_url', plugins_url('/', __FILE__));

            require_once(lisg_liyanitsolution_path . 'classes/function-class.php' );

            lisg_Function_Class::instance();
        }

    }

}
new lisg_main_cls();