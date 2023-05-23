<?php

/**
 *
 * Plugin Name: CHIP for WPFunnels Pro
 * Description: This to add support for WPFunnels Pro with CHIP for WooCommerce.
 * Version: 1.0.0
 * 
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

add_filter( 'wpfunnels/supported_payment_gateways', 'chip_woocommerce_funnel_add' );

function chip_woocommerce_funnel_add( $gateways ) {

  // if ( !class_exists( 'Chip_Woocommerce' ) ) {
  //   return;
  // }

  $chip_ids = [
    'wc_gateway_chip' => 'Wpfnl_Pro_Gateway_Chip',
    'wc_gateway_chip_2' => 'Wpfnl_Pro_Gateway_Chip_2',
    'wc_gateway_chip_3' => 'Wpfnl_Pro_Gateway_Chip_3',
    'wc_gateway_chip_4' => 'Wpfnl_Pro_Gateway_Chip_4',
  ];

  foreach ( $chip_ids as $key => $value ) {
    // $chip_gateway = Chip_Woocommerce::get_chip_gateway_class( $key );

    // $payment_method = $chip_gateway->get_option( 'payment_method_whitelist' );

    // if ( !is_array( $payment_method ) ) {
    //   continue;
    // }

    // if ( in_array( 'visa', $payment_method ) OR in_array( 'mastercard', $payment_method ) ) {
      $gateways[$key] = $value;
    // }
  }

  return $gateways;
}

add_action( 'plugins_loaded', 'wc_chip_funnel_include_files' );

function wc_chip_funnel_include_files() {
  include 'class-wpfnl-pro-gateway-chip.php';
}

$chip_ids = ['wc_gateway_chip', 'wc_gateway_chip_2', 'wc_gateway_chip_3', 'wc_gateway_chip_4'];

foreach ( $chip_ids as $chip_id ) {
  add_filter( "wc_{$chip_id}_purchase_params", 'wc_chip_funnel_purchase_parameter', 10, 2);
}

function wc_chip_funnel_purchase_parameter( $params, $gateway ) {
  
  $params['force_recurring'] = true;

  return $params;
}