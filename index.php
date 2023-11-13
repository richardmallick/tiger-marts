<?php
/*
Plugin Name: Tiger Marts Custom Plugins
Plugin URI: http://www.oxilab.org
Version: 1.0.0
Author: biplob018
Author URI: http://www.oxilab.org
Text Domain: tiger-marts
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'You can\'t access this page', 'tiger-marts' ) );
}

// add_action( 'admin_enqueue_scripts', 'enqueue_assets' );
// function enqueue_assets() {
//  wp_enqueue_style( 'tiger-mart-style', plugins_url( '', __FILE__ ) . '/assets/tiger-marts.css', null, filemtime( __DIR__ . '/assets/tiger-marts.css' ) );
// }

// // Change add to cart text on single product page
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_add_to_cart_button_text_single' );
function woocommerce_add_to_cart_button_text_single() {
	return __( 'অর্ডার করুন', 'woocommerce' );
}

// Change add to cart text on product archives page
add_filter( 'woocommerce_product_add_to_cart_text', 'woocommerce_add_to_cart_button_text_archives' );
function woocommerce_add_to_cart_button_text_archives() {
	return __( 'অর্ডার করুন', 'woocommerce' );
}

add_action( 'woocommerce_after_add_to_cart_button', 'additional_single_product_button', 20 );
function additional_single_product_button() {
	$post_id    = get_the_ID();
	$custom_phn = get_post_meta( $post_id, 'tiger_mart_custom_phn', true );
	$custom_phn = isset( $custom_phn ) && $custom_phn ? $custom_phn : '01944272470';
	echo '<a href="tel:' . $custom_phn . '" class="button alt wp-element-button oxi-product-call-now-button" >
                                      কল করুন: ' . $custom_phn . '
                                    </a>';
}

// Add a filter to modify the new order email recipients for admin
add_filter( 'woocommerce_email_recipient_new_order', 'custom_admin_order_confirmation_recipients', 10, 2 );
function custom_admin_order_confirmation_recipients( $recipient, $order ) {
	if ( $order && is_a( $order, 'WC_Order' ) ) {
		$is_custom_email = false;
		$custom_emails   = '';
		foreach ( $order->get_items() as $item ) {
			$product_id      = $item->get_product_id();
			$product_post_id = wc_get_product( $product_id )->get_id();
			$custom_email    = get_post_meta( $product_post_id, 'tiger_mart_custom_email', true );

			if ( $custom_email ) {
				$is_custom_email = true;
				$custom_emails  .= $custom_email;
			}
		}

		if ( $is_custom_email ) {
			$recipient = $custom_emails;
		}
	}

	return $recipient;
}

// Display Tabs in Product.
add_filter( 'woocommerce_product_data_tabs', 'contact_product_tab', 10, 1 );
function contact_product_tab( $tiger_marts ) {
	$tiger_marts['custom_tab'] = array(
		'label'    => __( 'Tiger Marts', 'tiger-marts' ),
		'target'   => 'tiger_mart_tab_data',
		'priority' => 50,
	);
	return $tiger_marts;
}

// Display Fields in Product Tabs.
add_action( 'woocommerce_product_data_panels', 'contact_tab_data' );
function contact_tab_data() {
	global $post;
	$post_id = $post->ID;

	$nonce        = wp_create_nonce( 'tiger_mart_product_update_nonce' );
	$custom_email = get_post_meta( $post_id, 'tiger_mart_custom_email', true );
	$custom_phn   = get_post_meta( $post_id, 'tiger_mart_custom_phn', true );
	?>

			<div id="tiger_mart_tab_data" class="panel woocommerce_options_panel tiger_mart_tab_data">
				<div class='tab-content' style="padding: 20px;">
					<form action="" method="POST">
						<div class="tiger-mart-input-wrapper">
							<p class="form-field">
								<label for="tiger-mart-custom-email"><?php echo esc_html__( 'Email Address', 'tiger-marts' ); ?></label>
								<input type="text" class="tiger-mart-custom-email" name="tiger-mart-custom-email" placeholder="Email Address" value="<?php echo esc_attr( $custom_email ); ?>">
							</p>
							<p class="form-field">
								<label for="tiger-mart-custom-phn"><?php echo esc_html__( 'Phone Number', 'tiger-marts' ); ?></label>
								<input type="text"  class="tiger-mart-custom-phn" name="tiger-mart-custom-phn" placeholder="Phone Number" value="<?php echo esc_attr( $custom_phn ); ?>">
							</p>
						</div> 
						<input type="hidden" name="tiger_mart_product_update_nonce" value="<?php echo esc_attr( $nonce ); ?>" />
					</form>
					<!-- <input type="submit" name="submit" id="submit" class="button button-primary" value="SAVE CHANGES"  style="margin-top: 60px"/> -->
				</div>
			</div>
		<?php
}

// Save Tiger Marts Data.
add_action( 'woocommerce_process_product_meta', 'save_contact_data' );
function save_contact_data( $post_id ) {

	$nonce = isset( $_POST['tiger_mart_product_update_nonce'] ) ? sanitize_text_field( $_POST['tiger_mart_product_update_nonce'] ) : '';

	if ( ! wp_verify_nonce( $nonce, 'tiger_mart_product_update_nonce' ) ) {
		return;
	}

	// Email Address.
	$email_address = isset( $_POST['tiger-mart-custom-email'] ) ? sanitize_text_field( $_POST['tiger-mart-custom-email'] ) : '';
	if ( isset( $email_address ) ) {
		update_post_meta( $post_id, 'tiger_mart_custom_email', $email_address );
	} else {
		$email_address = '';
	}

	// Phone Number.
	$phn_number = isset( $_POST['tiger-mart-custom-phn'] ) ? wc_sanitize_phone_number( $_POST['tiger-mart-custom-phn'] ) : '';
	if ( isset( $email_address ) ) {
		update_post_meta( $post_id, 'tiger_mart_custom_phn', $phn_number );
	} else {
		$phn_number = '';
	}
}
