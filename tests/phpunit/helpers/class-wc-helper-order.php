<?php
/**
 * Order helpers.
 *
 * @package SupportOrders\Tests
 */

/**
 * Class WC_Helper_Order.
 *
 * This helper class should ONLY be used for unit tests!
 */
class WC_Helper_Order {

	/**
	 * Create a test order with line items.
	 *
	 * @param int   $customer_id Customer ID to associate with the order.
	 * @param array $props       Optional. Additional order properties.
	 * @return WC_Order
	 */
	public static function create_order( $customer_id = 0, $props = [] ) {
		// Create a product for the order.
		$product = new WC_Product_Simple();
		$product->set_props(
			[
				'name'          => 'Test Product',
				'regular_price' => 10,
				'price'         => 10,
				'sku'           => 'TEST-SKU-' . wp_generate_uuid4(),
				'stock_status'  => 'instock',
			]
		);
		$product->save();

		// Create the order.
		$order = wc_create_order(
			array_merge(
				[
					'customer_id' => $customer_id,
					'status'      => 'pending',
				],
				$props
			)
		);

		// Add product to the order.
		$order->add_product( $product, 1 );

		// Set billing address.
		$order->set_billing_first_name( 'Test' );
		$order->set_billing_last_name( 'Customer' );
		$order->set_billing_address_1( '123 Test Street' );
		$order->set_billing_city( 'Test City' );
		$order->set_billing_state( 'CA' );
		$order->set_billing_postcode( '12345' );
		$order->set_billing_country( 'US' );
		$order->set_billing_email( 'test@example.com' );

		// Set shipping address.
		$order->set_shipping_first_name( 'Test' );
		$order->set_shipping_last_name( 'Customer' );
		$order->set_shipping_address_1( '123 Test Street' );
		$order->set_shipping_city( 'Test City' );
		$order->set_shipping_state( 'CA' );
		$order->set_shipping_postcode( '12345' );
		$order->set_shipping_country( 'US' );

		// Calculate totals and save.
		$order->calculate_totals();
		$order->save();

		return wc_get_order( $order->get_id() );
	}

	/**
	 * Delete an order.
	 *
	 * @param int $order_id Order ID to delete.
	 */
	public static function delete_order( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order->delete( true );
		}
	}
}
