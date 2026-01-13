<?php
/**
 * Support Orders Tests.
 *
 * @package SupportOrders\Test\Unit
 */

namespace SupportOrders\Test\Unit;

use WP_UnitTestCase;
use WC_Helper_Order;
use SupportOrders;

/**
 * Class Replacement_Orders_Test
 *
 * Tests for the Support Orders functionality.
 */
class Replacement_Orders_Test extends WP_UnitTestCase {

	/**
	 * Verifies support order has $0 total.
	 */
	public function test_support_order_data() {
		$order = WC_Helper_Order::create_order();
		$replacement = SupportOrders::create_support_order( $order );
		$this->assertEquals( '0.00', $replacement->get_total() );
	}

	/**
	 * Verifies is_support_order method.
	 */
	public function test_is_support_order() {
		$order = WC_Helper_Order::create_order();
		$this->assertFalse( SupportOrders::is_support_order( $order ) );

		$replacement = SupportOrders::create_support_order( $order );
		$this->assertTrue( SupportOrders::is_support_order( $replacement ) );
	}

	/**
	 * Verifies support order copies billing information.
	 */
	public function test_support_order_copies_billing_info() {
		$order = WC_Helper_Order::create_order();
		$replacement = SupportOrders::create_support_order( $order );

		$this->assertEquals( $order->get_billing_first_name(), $replacement->get_billing_first_name() );
		$this->assertEquals( $order->get_billing_last_name(), $replacement->get_billing_last_name() );
		$this->assertEquals( $order->get_billing_address_1(), $replacement->get_billing_address_1() );
		$this->assertEquals( $order->get_billing_city(), $replacement->get_billing_city() );
		$this->assertEquals( $order->get_billing_state(), $replacement->get_billing_state() );
		$this->assertEquals( $order->get_billing_postcode(), $replacement->get_billing_postcode() );
		$this->assertEquals( $order->get_billing_country(), $replacement->get_billing_country() );
		$this->assertEquals( $order->get_billing_email(), $replacement->get_billing_email() );
	}

	/**
	 * Verifies support order copies shipping information.
	 */
	public function test_support_order_copies_shipping_info() {
		$order = WC_Helper_Order::create_order();
		$replacement = SupportOrders::create_support_order( $order );

		$this->assertEquals( $order->get_shipping_first_name(), $replacement->get_shipping_first_name() );
		$this->assertEquals( $order->get_shipping_last_name(), $replacement->get_shipping_last_name() );
		$this->assertEquals( $order->get_shipping_address_1(), $replacement->get_shipping_address_1() );
		$this->assertEquals( $order->get_shipping_city(), $replacement->get_shipping_city() );
		$this->assertEquals( $order->get_shipping_state(), $replacement->get_shipping_state() );
		$this->assertEquals( $order->get_shipping_postcode(), $replacement->get_shipping_postcode() );
		$this->assertEquals( $order->get_shipping_country(), $replacement->get_shipping_country() );
	}

	/**
	 * Verifies each line item in the support order has $0 price.
	 */
	public function test_support_order_items_have_zero_price() {
		$order = WC_Helper_Order::create_order();
		$replacement = SupportOrders::create_support_order( $order );

		foreach ( $replacement->get_items() as $item ) {
			$this->assertEquals( 0, $item->get_total() );
			$this->assertEquals( 0, $item->get_subtotal() );
		}
	}

	/**
	 * Verifies support order has the correct created_via meta.
	 */
	public function test_support_order_created_via_meta() {
		$order = WC_Helper_Order::create_order();
		$replacement = SupportOrders::create_support_order( $order );

		$this->assertEquals( 'support', $replacement->get_created_via() );
	}

	/**
	 * Verifies order notes are added to both orders.
	 */
	public function test_support_order_adds_order_notes() {
		$order = WC_Helper_Order::create_order();
		$replacement = SupportOrders::create_support_order( $order );

		// Reload the original order to get updated notes.
		$order = wc_get_order( $order->get_id() );

		$original_notes = wc_get_order_notes( [ 'order_id' => $order->get_id() ] );
		$replacement_notes = wc_get_order_notes( [ 'order_id' => $replacement->get_id() ] );

		// Check that notes were added.
		$this->assertNotEmpty( $original_notes );
		$this->assertNotEmpty( $replacement_notes );

		// Verify the original order has a note referencing the support order.
		$found_support_note = false;
		foreach ( $original_notes as $note ) {
			if ( strpos( $note->content, 'Support order created' ) !== false ) {
				$found_support_note = true;
				break;
			}
		}
		$this->assertTrue( $found_support_note, 'Original order should have a note about the support order.' );

		// Verify the support order has a note referencing the original order.
		$found_original_note = false;
		foreach ( $replacement_notes as $note ) {
			if ( strpos( $note->content, 'support order created from' ) !== false ) {
				$found_original_note = true;
				break;
			}
		}
		$this->assertTrue( $found_original_note, 'Support order should have a note about the original order.' );
	}
}
