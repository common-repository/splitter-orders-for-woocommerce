<?php
if (!defined('ABSPATH')) {
    exit;
}
/*
  Slipt according to exist attribute
 * */

if (!class_exists('lisg_default_class')) {

    class lisg_default_class {

        /**
         * @var string 
         */
        public $name = "Splitter Orders For Woocommerce";

        /**
         * @var string 
         */
        public $description = "Order splitter";

        public function __construct() {
            $this->default_init();
        }

        public function default_init() {
            if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
                $this->default_frontend_variation();
            }
        }

        public function default_frontend_variation() {
            add_action('woocommerce_checkout_order_processed', array($this, 'change_total_on_checking'), 20, 1);
            remove_filter('woocommerce_thankyou_order_received_text', 'filter_woocommerce_thankyou_order_received_text', 10, 2);
            add_filter('woocommerce_thankyou_order_received_text', array($this, 'liyanitsolution_change_order_received_text'), 10, 2);
            //add_filter('woocommerce_my_account_my_orders_query', array($this, 'change_formatted_meta_data'), 20, 1);
            add_filter('woocommerce_locate_template', array($this, 'liyanitsolution_thank_you_page_template'), 10, 3);
        }

        public function change_total_on_checking($order_id) {
		    $parent_order = wc_get_order($order_id);
            $paymentMethod = $parent_order->get_payment_method_title();
            $method = $parent_order->get_payment_method();
            $parent_order->update_status('processing');

            $items = WC()->cart->get_cart();

            $orderIds = array();
            foreach ($items as $item => $values) {
                $address = array(
                    'first_name' => sanitize_text_field($_POST['billing_first_name']),
                    'last_name' => sanitize_text_field($_POST['billing_last_name']),
                    'company' => sanitize_text_field($_POST['billing_company']),
                    'email' => sanitize_email($_POST['billing_email']),
                    'phone' => sanitize_text_field($_POST['billing_phone']),
                    'address_1' => sanitize_text_field($_POST['billing_address_1']),
                    'address_2' => sanitize_text_field($_POST['billing_address_2']),
                    'city' => sanitize_text_field($_POST['billing_city']),
                    'state' => sanitize_text_field($_POST['billing_state']),
                    'postcode' =>sanitize_text_field($_POST['billing_postcode']),
                    'country' => sanitize_text_field($_POST['billing_country'])
                    
                );
                $shippingaddress = array(
                    'first_name' =>sanitize_text_field($_POST['shipping_first_name']),
                    'last_name' =>sanitize_text_field($_POST['shipping_last_name']),
                    'company' =>sanitize_text_field($_POST['shipping_company']),
                    'email' => sanitize_email($_POST['shipping_email']),
                    'phone' => sanitize_text_field($_POST['shipping_phone']),
                    'address_1' =>sanitize_text_field($_POST['shipping_address_1']),
                    'address_2' =>sanitize_text_field($_POST['shipping_address_2']),
                    'city' => sanitize_text_field($_POST['shipping_city']),
                    'state' => sanitize_text_field($_POST['shipping_state']),
                    'postcode' =>sanitize_text_field($_POST['shipping_postcode']),
                    'country' =>sanitize_text_field($_POST['shipping_country'])
			
                );
				
                $user_id = get_current_user_id();
				
//                 create sub order //
                $order = wc_create_order();
                $order->update_status('processing');
				$note = sanitize_text_field($_POST['order_comments']);
				$order->set_customer_note( $note );
                $order->set_address($address, 'billing');
                $order->set_address($shippingaddress, 'shipping');
                update_post_meta($order->id, '_customer_user', $user_id);
				$my_post = array(
								  'ID'           => $order->id,
								  'post_excerpt'   => $note,
								   );
							 
								wp_update_post( $my_post );
				update_post_meta($order->id,'order_status_result','Order Splitter');
                update_post_meta($order->id, '_order_ispliter', 'yes');
                update_post_meta($order->id, '_payment_method_title', $paymentMethod);
                update_post_meta($order->id, '_payment_method', $method);
                update_post_meta($order->id, '_order_shipping', wc_format_decimal(WC()->cart->shipping_total));
                update_post_meta($order->id, '_order_discount', wc_format_decimal(WC()->cart->get_order_discount_total()));
                update_post_meta($order->id, '_cart_discount', wc_format_decimal(WC()->cart->get_cart_discount_total()));
                update_post_meta($order->id, '_order_tax', wc_format_decimal(WC()->cart->tax_total));
                update_post_meta($order->id, '_order_shipping_tax', wc_format_decimal(WC()->cart->shipping_tax_total));
                update_post_meta($order->id, '_order_key', 'wc_' . apply_filters('woocommerce_generate_order_key', uniqid('order_')));
                update_post_meta($order->id, '_order_currency', get_woocommerce_currency());
                update_post_meta($order->id, '_prices_include_tax', get_option('woocommerce_prices_include_tax'));
                update_post_meta($order->id, '_customer_ip_address', sanitize_url(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? sanitize_url($_SERVER['HTTP_X_FORWARDED_FOR']) : sanitize_url($_SERVER['REMOTE_ADDR']));
                update_post_meta($order->id, '_customer_user_agent', sanitize_url(isset($_SERVER['HTTP_USER_AGENT'])) ? sanitize_url($_SERVER['HTTP_USER_AGENT']) : '');
                $sum = $values['line_total'];
                $_product = $values['data']->post;
                $product_title = $_product->post_title;
                $qty = $values['quantity'];
                $price = get_post_meta($values['product_id'], '_price', true);
                $variation_id = $values['variation_id'];
                $item_id = wc_add_order_item($order->id, array(
                    'order_item_name' => $product_title,
                    'order_item_type' => 'line_item'
                ));
                if ($item_id) {
                    // add item meta data
                    wc_add_order_item_meta($item_id, '_qty', $qty);
                    wc_add_order_item_meta($item_id, '_tax_class', $values['tax_class']);
                    wc_add_order_item_meta($item_id, '_product_id', $values['product_id']);
                    wc_add_order_item_meta($item_id, '_variation_id', $variation_id);
                    wc_add_order_item_meta($item_id, '_line_subtotal', $values['line_subtotal']);
                    wc_add_order_item_meta($item_id, '_line_subtotal_tax', $values['line_subtotal_tax']);
                    wc_add_order_item_meta($item_id, '_line_total', $values['line_total']);
                    wc_add_order_item_meta($item_id, '_line_tax', $values['line_tax']);
                    wc_add_order_item_meta($item_id, '_line_tax_data', array('total' => array($values['total']), 'subtotal' => array($values['subtotal'])));
                    if ($values['variation_data'] && is_array($item['variation_data'])) {
                        foreach ($item['variation_data'] as $key => $value) {
                            wc_add_order_item_meta($item_id, str_replace('attribute_', '', $key), $value);
                        }
                    }
                }
                update_post_meta($order->id, '_order_total', wc_format_decimal($sum, get_option('woocommerce_price_num_decimals')));
                $orderIds[] = $order->id;
            }
            update_post_meta($order_id, 'order_ids', serialize($orderIds));
        }

        function liyanitsolution_change_order_received_text($str, $order) {
            $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
            $str .= $this->lisg_change_order_received_text($order_id);
            return $str;
        }

        function lisg_change_order_received_text($order_id, $deleted = true) {
		    include('emails_all.php');
		   
            $woocommerceCurrency = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));

            ob_start();
            $parent_order = wc_get_order($order_id);
            $paymentMethod = $parent_order->get_payment_method_title();

            $singlePost = get_post($order_id);
            $cart_total = 0;
            if (!empty($parent_order)) {
                $cart_total = $parent_order->get_total();
            }			
            $lisg_cart_notices = get_option('woo_liyanitsolution_cart_notices', true);			if($lisg_cart_notices!='')			{				$lisg_cart_notices = $lisg_cart_notices;			}			  return $lisg_cart_notices;			
            $co_total = $lisg_cart_notices['co_total'];			if($co_total!='')			{				$co_total = $co_total;			}			  return $co_total;
            $posts_array = unserialize(get_post_meta($order_id, 'order_ids', true));

            if (!empty($posts_array)) {
                $first_order = current($posts_array);
                $order_total = 0;
                foreach ($posts_array as $post_data) {
                    $this_order = wc_get_order($post_data);
                    $total_amount = $this_order->get_total();
                    $order_total += $total_amount;

                    if (!get_option('woo_liyanitsolution_shipping_cost', 0)) {
                        $child_order_shipping_items = $this_order->get_items('shipping');
                        if (!empty($child_order_shipping_items)) {
                            foreach ($child_order_shipping_items as $item_id => $item_data) {
                                wc_delete_order_item($item_id);
                            }
                        }
                    }
                    $this_order->calculate_totals();
                }
                ?>
                <section class="woocommerce-order-details child_orderss">
                    <h2 class="woocommerce-order-details-title"><?php echo esc_attr($lisg_cart_notices['co_heading'] ? $lisg_cart_notices['co_heading'] : 'Order Splitter' . (count($posts_array) > 1 ? 's' : '')); ?></h2>				  
                    <?php
                    $cart_total += $order_total;
                    foreach ($posts_array as $post_data) {
                        $child_order = wc_get_order($post_data);
                        $_payment_method = $child_order->get_payment_method_title();
                        $child_order_data = $child_order->get_data();
                        ?>

                        <h3 class="child_order_heading"><?php echo esc_attr($lisg_cart_notices['co_number'] ? $lisg_cart_notices['co_number'] : 'Order number'); ?> <?php echo esc_attr($post_data); ?></h3>
                        <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
                            <thead>
                                <tr>
                                    <th class="woocommerce-table-product-name product-name">Product</th>
                                    <th class="woocommerce-table-product-table product-total">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($child_order->get_items() as $order_items) { ?>    
                                    <tr class="woocommerce-table-line-item order_item">

                                        <td class="woocommerce-table-product-name product-name">
                                            <a href="<?php echo esc_attr(get_permalink($order_items->get_product_id())); ?>"><?php echo esc_attr($order_items['name']); ?></a> <strong class="product-quantity">× — <?php echo esc_attr($order_items->get_quantity()); ?></strong>	</td>

                                        <td class="woocommerce-table-product-total product-total">
                                            <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">
                                                    <?php echo esc_attr($woocommerceCurrency); ?>
                                                </span><?php echo number_format($order_items->get_total(), 2); ?></span>	</td>

                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td>Tax:	</td>	
                                    <td><span class="woocommerce-Price-currencySymbol">
                                            <?php echo esc_attr($woocommerceCurrency); ?>
                                        </span>
                                        <?php echo number_format($child_order_data['total_tax'], 2); ?>
                                    </td>	
                                </tr>
                                <tr>
                                    <td>Payment method:	</td>	
                                    <td><?php echo esc_attr($paymentMethod); ?></td>	
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="row">Total:</th>
                                    <td>
                                        <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">
                                                <?php echo esc_attr($woocommerceCurrency); ?>
                                            </span>
                                            <?php echo number_format($child_order->get_total(), 2); ?>
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <?php
                    }
                    ?>


                    <?php if ($co_total): ?>
                        <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
                            <thead>
                                <tr>
                                    <th class="woocommerce-table-product-name product-name">Order Total</th>
                                    <th class="woocommerce-table-product-table product-total">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="woocommerce-table-line-item order_item">
                                    <td class="woocommerce-table-product-name product-name">
                                        Total amount charged to billing method	</td>
                                    <td class="woocommerce-table-product-total product-total">
                                        <span class="woocommerce-Price-amount amount">
                                            <span class="woocommerce-Price-currencySymbol">
                                                <?php echo esc_attr($woocommerceCurrency); ?>
                                            </span>
                                            <?php echo number_format($cart_total, 2); ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </section>
                <?php
            }
            $str = ob_get_contents();
            ob_end_clean();
            return $str;
        }


	public function change_formatted_meta_data($orders) {
            $orders = array(
                'limit' => -1,
                'offset' => null,
                'page' => 1,
                'meta_key' => '_order_ispliter', //meta type is plain string and i need results alphabetically.
                'orderby' => 'meta_value', //meta_value_num
                'order' => 'DESC', //ASC
                'customer' => get_current_user_id(),
                'paginate' => true,
                'meta_query' => array(
                    array(
                        'key' => '_order_ispliter', //meta type is plain string and i need results alphabetically.
                        'value' => 'yes',
                        'compare' => '=',
                    ),
                ),
            );

            return $orders;
        }
       
        public function liyanitsolution_thank_you_page_template($template, $template_name, $template_path) {
            if ('checkout/thankyou.php' == $template_name) {
                $template = lisg_liyanitsolution_plugin_dir . '/inc/checkout/thankyou.php';
            }
            return $template;
			
        }
		
			
    }
	 	

}
new lisg_default_class();
?>