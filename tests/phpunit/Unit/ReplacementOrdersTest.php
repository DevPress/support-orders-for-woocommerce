<?php

namespace ReplacementOrders\Test\Unit;

use WP_UnitTestCase;
use WC_Helper_Order;
use ReplacementOrders;

class Replacement_Orders_Test extends WP_UnitTestCase {
	/**
	 * Verifies replacement order "created_via" meta.
	 */
	public function test_replacement_order_data() {
		$order = WC_Helper_Order::create_order();
		$replacement = ReplacementOrders::create_replacement_order( $order );
		$this->assertEquals( '0.00', $replacement->get_total() );
	}

	/**
	 * Verifies is_replacement_order method.
	 */
	public function test_is_replacement_order() {
		$order = WC_Helper_Order::create_order();
		$this->assertFalse( ReplacementOrders::is_replacement_order( $order ) );

		$replacement = ReplacementOrders::create_replacement_order( $order );
		$this->assertTrue( ReplacementOrders::is_replacement_order( $replacement ) );
	}
}
