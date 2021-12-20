<?php

namespace SupportOrders\Test\Unit;

use WP_UnitTestCase;
use WC_Helper_Order;
use SupportOrders;

class Replacement_Orders_Test extends WP_UnitTestCase {
	/**
	 * Verifies replacement order "created_via" meta.
	 */
	public function test_support_order_data() {
		$order = WC_Helper_Order::create_order();
		$replacement = SupportOrders::create_support_order( $order );
		$this->assertEquals( '0.00', $replacement->get_total() );
	}

	/**
	 * Verifies is_replacement_order method.
	 */
	public function test_is_support_order() {
		$order = WC_Helper_Order::create_order();
		$this->assertFalse( SupportOrders::is_support_order( $order ) );

		$replacement = SupportOrders::create_support_order( $order );
		$this->assertTrue( SupportOrders::is_support_order( $replacement ) );
	}
}
