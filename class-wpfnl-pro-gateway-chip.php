<?php

namespace WPFunnelsPro\Frontend\Gateways;

class Wpfnl_Pro_Gateway_Chip {
  public $refund_support;
  public $key;
  public $token;

  public function __construct() {
    $this->key = 'wc_gateway_chip';
    $this->refund_support = true;
    $this->token = true;
  }

  public function process_payment( $order, $offer_product ) {

    $payment_requery_status = false;

    if ( $this->key === $order->get_payment_method() && $token = $this->get_token( $order ) ) {

      $gateway    = \Chip_Woocommerce::get_chip_gateway_class( $order->get_payment_method() );
      $secret_key = $gateway->get_option( 'secret_key' );
      $brand_id   = $gateway->get_option( 'brand_id' );

      $params = [
        'creator_agent'    => 'WC Funnel: 1.0.0',
        'reference'        => $order->get_id(),
        'platform'         => 'woocommerce',
        'purchase' => [
          'total_override' => round( $offer_product['total'] * 100 ),
          'timezone'       => $gateway->get_option( 'purchase_time_zone', 'Asia/Kuala_Lumpur' ),
          'currency'       => $order->get_currency(),
          'products'       => [
            [
              'name'  => substr( $offer_product['name'], 0, 256 ),
              'price' => round( $offer_product['total'] * 100 ),
            ]
          ],
        ],
        'brand_id' => $brand_id,
        'client' => [
          'email'                   => $order->get_billing_email(),
          'phone'                   => substr( $order->get_billing_phone(), 0, 32 ),
          'full_name'               => $gateway->filter_customer_full_name( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
          'street_address'          => substr( $order->get_billing_address_1() . ' ' . $order->get_billing_address_2(), 0, 128 ) ,
          'country'                 => substr( $order->get_billing_country(), 0, 2 ),
          'city'                    => substr( $order->get_billing_city(), 0, 128 ) ,
          'zip_code'                => substr( $order->get_shipping_postcode(), 0, 32 ),
          'shipping_street_address' => substr( $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2(), 0, 128 ) ,
          'shipping_country'        => substr( $order->get_shipping_country(), 0, 2 ),
          'shipping_city'           => substr( $order->get_shipping_city(), 0, 128 ),
          'shipping_zip_code'       => substr( $order->get_shipping_postcode(), 0, 32 ),
        ],
      ];

      $chip = $gateway->api();

      if ( is_user_logged_in() AND $gateway->get_option( 'disable_clients_api' ) != 'yes' ) {
        $params['client']['email'] = wp_get_current_user()->user_email;
        $client_with_params = $params['client'];
        $old_client_records = true;
        unset( $params['client'] );
  
        $params['client_id'] = get_user_meta( $order->get_user_id(), '_' . $gateway->id . '_client_id_' . substr( $secret_key, -8, -2 ), true );
  
        if ( empty( $params['client_id'] ) ) {
          $get_client = $chip->get_client_by_email( $client_with_params['email'] );
  
          if ( array_key_exists( '__all__', $get_client ) ) {
            return array(
              'result' => 'failure',
            );
          }
  
          if ( is_array($get_client['results']) AND !empty( $get_client['results'] ) ) {
            $client = $get_client['results'][0];
          } else {
            $old_client_records = false;
            $client = $chip->create_client( $client_with_params );
          }
  
          update_user_meta( $order->get_user_id(), '_' . $gateway->id . '_client_id_' . substr( $secret_key, -8, -2 ), $client['id'] );
  
          $params['client_id'] = $client['id'];
        }
  
        if ( $gateway->get_option( 'update_client_information' ) == 'yes' AND $old_client_records ) {
          $chip->patch_client( $params['client_id'], $client_with_params );
        }
      }

      $payment = $chip->create_payment( $params );

      $chip->charge_payment( $payment['id'], array( 'recurring_token' => $token ) );

      $get_payment = $chip->get_payment( $payment['id'] );
      $payment_requery_status = $get_payment['status'];
    }

    if ( $payment_requery_status == 'paid' ) {
      return array(
        'is_success' => true,
        'message' => 'Success'
      );
    }

    return array(
      'is_success' => false,
      'message' => __( 'The purchase does not contain valid token.', 'woocommerce_chip_funnel' )
    );
  }

  public function get_token( $order ) {
    
    $purchase = $this->get_purchase( $order );

    if ( !empty( $purchase['recurring_token'] ) ) {
      return $purchase['recurring_token'];
    } elseif ( $purchase['is_recurring_token'] ) {
      return $purchase['id'];
    }

    return false;
  }

  public function get_purchase( $order ) {

    $gateway_id = $order->get_payment_method();
    return $order->get_meta( '_' . $gateway_id . '_purchase', true );
  }
}

class Wpfnl_Pro_Gateway_Chip_2 extends Wpfnl_Pro_Gateway_Chip {
  public function __construct() {
    parent::__construct();

    $this->key = 'wc_gateway_chip_2';
  }
};

class Wpfnl_Pro_Gateway_Chip_3 extends Wpfnl_Pro_Gateway_Chip {
  public function __construct() {
    parent::__construct();

    $this->key = 'wc_gateway_chip_3';
  }
};

class Wpfnl_Pro_Gateway_Chip_4 extends Wpfnl_Pro_Gateway_Chip {
  public function __construct() {
    parent::__construct();

    $this->key = 'wc_gateway_chip_4';
  }
};