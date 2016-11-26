<?php
/*
* Plugin Name: NT product quantities for Woocommerce
* Plugin URI: http://webstudionovetrendy.eu/
* Description: Add funcionality min/max and multiple steps to Woocommerce
* Version: 160928
* Author: Webstudio Nove Trendy
* Author URI: http://webstudionovetrendy.eu/
* License: Free to use and adapt
* WC requires at least: 2.3
* WC tested up to: 2.6
* GitHub Plugin URI: https://github.com/novetrendy/NT-min-max-quantities
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {



/* **************************************************************************************** */

/** Add MIN/MAX/STEP fields to product */
// Display Fields - MIN/MAX/STEP


            add_action( 'woocommerce_product_options_general_product_data', 'nt_add_min_field', 40 ); //minimální množství
            add_action( 'woocommerce_product_options_general_product_data', 'nt_add_multiple_field', 41 ); // násobky množství
            add_action( 'woocommerce_product_options_general_product_data', 'nt_add_max_field', 42 ); //maximální množství



/** Add MIN field to product */
function nt_add_min_field() {

global $woocommerce, $post;
// Text Field
woocommerce_wp_text_input(
    array(
        'id' => 'minimum_allowed_quantity',
        'label' => __( 'Minimální množství'),
        'placeholder' => __('Zadejte minimální množství'),  // DOPLNIT
        'desc_tip' => 'true',
        'description' => __( 'Zadejte minimální množství, které může zákazník nakoupit')
        )
    );
}

/** Add MAX field to product */
function nt_add_max_field() {

global $woocommerce, $post;

// Text Field

woocommerce_wp_text_input(
    array(
        'id' => 'maximum_allowed_quantity',
        'label' => __( 'Maximální množství'),
        'placeholder' => __('Zadejte maximální množství'), // DOPLNIT
        'desc_tip' => 'true',
        'description' => __( 'Zadejte maximální množství, které může zákazník nakoupit')
        )
    );
}

/** Add MULTIPLE field to product */
function nt_add_multiple_field() {

global $woocommerce, $post;

// Text Field

woocommerce_wp_text_input(
    array(
        'id' => 'group_of_quantity',
        'label' => __( 'Násobky množství'),
        'placeholder' => __('Zadejte násobky množství'),
        'desc_tip' => 'true',
        'description' => __( 'Zadejte násobky množství, které může zákazník nakoupit')
        )
    );
}

        // Save Fields - MIN,MAX,MULTIPLE
        add_action( 'save_post', 'nt_min_max_fields_save',1 ,2 );

function nt_min_max_fields_save($post_id, $post) {
    // minimum_allowed_quantity
    if ( get_post_meta($post->ID, 'minimum_allowed_quantity', FALSE ) ) {
        update_post_meta($post->ID, 'minimum_allowed_quantity', $_POST['minimum_allowed_quantity']);
    }
    else { add_post_meta($post->ID, 'minimum_allowed_quantity', $_POST['minimum_allowed_quantity']);
    }
    if ( $_POST['minimum_allowed_quantity'] == '' ) {
        delete_post_meta($post->ID, 'minimum_allowed_quantity');
    }
    // maximum_allowed_quantity
    if ( get_post_meta($post->ID, 'maximum_allowed_quantity', FALSE ) ) {
        update_post_meta($post->ID, 'maximum_allowed_quantity', $_POST['maximum_allowed_quantity']);
    }
    else { add_post_meta($post->ID, 'maximum_allowed_quantity', $_POST['maximum_allowed_quantity']);
    }
    if ( $_POST['maximum_allowed_quantity'] == '' ) {
        delete_post_meta($post->ID, 'maximum_allowed_quantity');
    }
    // group_of_quantity
    if ( get_post_meta($post->ID, 'group_of_quantity', FALSE ) ) {
        update_post_meta($post->ID, 'group_of_quantity', $_POST['group_of_quantity']);
    }
    else { add_post_meta($post->ID, 'group_of_quantity', $_POST['group_of_quantity']);
    }
    if ( $_POST['group_of_quantity'] == '' ) {
        delete_post_meta($post->ID, 'group_of_quantity');
    }
   }

      // Simple products
add_filter( 'woocommerce_quantity_input_args', 'nt_woocommerce_quantity_input_args', 10, 2 );

function nt_woocommerce_quantity_input_args( $args, $product ) {
    $min = get_post_meta( $product->id, 'minimum_allowed_quantity', true );
    $max = get_post_meta( $product->id, 'maximum_allowed_quantity', true );
    $multiple = get_post_meta( $product->id, 'group_of_quantity', true );

    if ( is_singular( 'product' ) ) {
        $args['input_value'] 	= $min;	// Starting value (we only want to affect product pages, not cart)
    }
    $args['max_value'] 	= $max; 	// Maximum value
    $args['min_value'] 	= $min;   	// Minimum value
    $args['step'] 		= $multiple;    // Quantity steps
    return $args;
}

// Variations
add_filter( 'woocommerce_available_variation', 'nt_woocommerce_available_variation' );

function nt_woocommerce_available_variation( $args ) {
    $args['max_qty'] = $max; 		// Maximum value (variations)
    $args['min_qty'] = $min;   	// Minimum value (variations)
    $args['step'] 	 = $multiple;    // Quantity steps
    return $args;
}




/***************** Woocommerce min/max quantities show in product details ***************/

        add_action( 'woocommerce_single_product_summary'/*woocommerce_after_shop_loop_item_title*/, 'nt_min_max', 11 );


        function nt_min_max() {
            global $product;
            $nt_min_quantities = get_post_meta( $product->id, 'minimum_allowed_quantity', true );
            $nt_group_of_quantity = get_post_meta( $product->id, 'group_of_quantity', true );
            if ($nt_min_quantities >= 1){
                $nt_min_quantities = number_format($nt_min_quantities, 0, ',', ' ');
            echo '<span>' . 'Minimální odběr: <strong>' . $nt_min_quantities . ' ks</strong>'.'<br /></span>';
            }
            if ($nt_group_of_quantity >= 1){
                $nt_group_of_quantity = number_format($nt_group_of_quantity, 0, ',', ' ');
            echo '<span>' . 'Prodej po: <strong>' . $nt_group_of_quantity . ' ks</strong>'.'<br /><br /></span>';
            }
            }
/****************/


}
else  {
 // If WooCommerce not active, deactivate plugin
 if ( is_admin() ) {
          add_action( 'admin_init', 'my_plugin_deactivate' );
          add_action( 'admin_notices', 'my_plugin_admin_notice' );
          function my_plugin_deactivate() {
              deactivate_plugins( plugin_basename( __FILE__ ) );
          }
          function my_plugin_admin_notice() {
               echo '<div class="updated"><p>' . __('<strong>EAN, Delivery Date, Short Description and Free Shipping</strong> requires active Woocommerce plugin. Because Woocommerce plugin is not installed or active <strong>plugin has been deactivated</ strong>.', '') . '</p></div>';
               if ( isset( $_GET['activate'] ) )
                    unset( $_GET['activate'] );
          }
        }
    }
?>
