<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('lisg_Function_Class')):

    class lisg_Function_Class {

        protected static $_instance = null;

        public function __construct() {
            add_action('admin_menu', array($this, 'lisg_woocommerce_order_splitter_menu'));
            if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
                $this->frontend_css_js();
            }
        }

        function lisg_woocommerce_order_splitter_menu() {
            add_submenu_page('woocommerce', 'order_splitter', 'Order_Splitter', 'manage_options', 'order-splitter', array($this, 'lisg_order_splitter_menu_callback'));
        }

        public function lisg_order_splitter_menu_callback() {
          require_once lisg_liyanitsolution_path . '/inc/configuration.php';
        }

        public function frontend_css_js() {
            //add_action('wp_enqueue_scripts', array($this, 'LIYANITSOLUTION_frontend_scripts'));
            add_action('admin_enqueue_scripts', array($this, 'lisg_frontend_scripts'));
            add_action('wp_head', array($this, 'lisg_custom_ajax_url'));
            add_action('wp_ajax_lisg_select_variation', array($this, 'lisg_select_variation'));
        }

        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function lisg_select_variation() {
			
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            $taxonomy_terms = array();

            if ($attribute_taxonomies) :
                foreach ($attribute_taxonomies as $tax) :
                    if (taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name))) :
                        if ($tax->attribute_name == sanitize_text_field($_POST['id'])) {
                            $taxonomy_terms = get_terms(wc_attribute_taxonomy_name($tax->attribute_name), 'orderby=name&hide_empty=0');
                        }
                    endif;
                endforeach;
            endif;
            foreach ($taxonomy_terms as $term) {
                ?>
                <option value="<?php echo esc_attr($term->slug); ?>" <?php if (in_array($term->slug, $color)) echo 'selected'; ?>>
                    <?php echo esc_attr($term->name); ?>
                </option>
                <?php
            }
            die;
        }

        public function lisg_frontend_scripts() {
            wp_enqueue_style('woocommerce_admin_styles');
            wp_enqueue_style('lisg-custom-style-css', plugins_url('/assets/css/custom_style.css', dirname(__FILE__)), lisg_liyanitsolution_version);
            wp_enqueue_script('lisg-frontend-js', lisg_liyanitsolution_url . 'assets/js/custom.js', array('jquery', 'wp-color-picker'), lisg_liyanitsolution_version, true);
        }

        public function lisg_custom_ajax_url() {
            $html = '<script type="text/javascript">';
            $html .= 'var ajaxurl = "' . admin_url('admin-ajax.php') . '"';
            $html .= '</script>';
            echo esc_attr($html);
        }

    }

    endif;
?>