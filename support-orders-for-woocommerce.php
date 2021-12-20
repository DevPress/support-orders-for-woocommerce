<?php
/**
 * Plugin Name: Support Orders for WooCommerce
 * Plugin URI: https://devpress.com
 * Description: Easily create WooCommerce replacement support orders from existing orders.
 * Version: 1.0.0
 * Author: DevPress
 * Author URI: https://devpress.com
 * Developer: Devin Price
 * Developer URI: https://devpress.com
 *
 * WC requires at least: 5.6.0
 * WC tested up to: 5.8.0
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


/**
 * Class SupportOrders
 * @package SupportOrders
 */
class SupportOrders {

	/**
	 * The single instance of the class.
	 *
	 * @var mixed $instance
	 */
	protected static $instance;

	/**
	 * Main SupportOrders Instance.
	 *
	 * Ensures only one instance of the SupportOrders is loaded or can be loaded.
	 *
	 * @return SupportOrders - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_order_actions', [ $this, 'add_support_order_action' ] );
		add_action( 'woocommerce_order_action_create_support_order', array( $this, 'create_support_order_and_redirect' ) );
	}

	/**
	 * Checks if the order is a replacement support order.
	 * Support orders all have _created_via meta of "support".
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @return bool
	 */
	public static function is_support_order( \WC_Order $order ) {
		return 'support' === $order->get_created_via();
	}

	/**
	 * Add order action.
	 *
	 * @param array $actions
	 *
	 * @return mixed
	 */
	public function add_support_order_action( $actions ) {
		global $post;

		if ( ! isset( $post->ID ) ) {
			return $actions;
		}

		$order = wc_get_order( $post->ID );
		if ( ! $order ) {
			return $actions;
		}

		// Do not offer replacement orders from replacement orders.
		if ( self::is_support_order( $order ) ) {
			return $actions;
		}

		$actions['create_support_order'] = __( 'Create a support order', 'support-orders' );

		return $actions;
	}

	/**
	 * Create the replacement order and redirect to it.
	 *
	 * @param \WC_Order $order
	 * @param array     $copy_meta
	 * @param bool      $add_items
	 *
	 * @throws \WC_Data_Exception
	 */
	public static function create_support_order_and_redirect( $order ) {
		$support_order_order = self::create_support_order( $order);

		$support_order_order_admin_url = admin_url( sprintf( 'post.php?action=edit&post=%s', $support_order_order->get_id() ) );

		// Redirect to the newly created support order.
		wp_safe_redirect( $support_order_order_admin_url );
		die();
	}

	/**
	 * Duplicate the order. Make sure all the products are free of charge.
	 *
	 * @param \WC_Order $order
	 * @param array     $copy_meta
	 * @param bool      $add_items
	 * @param string    $post_content
	 *
	 * @throws \WC_Data_Exception
	 */
	public static function create_support_order( $order, $copy_meta = [], $add_items = true ) {
		$support_order = wc_create_order(
			[
				'created_via' => 'support',
			]
		);

		$order_admin_url         = admin_url( sprintf( 'post.php?action=edit&post=%s', $order->get_id() ) );
		$support_order_admin_url = admin_url( sprintf( 'post.php?action=edit&post=%s', $support_order->get_id() ) );

		if ( $add_items ) {
			self::add_order_items( $order, $support_order );
		}

		// Copy meta.
		foreach ( $copy_meta as $meta_key ) {
			$support_order->update_meta_data( $meta_key, $order->get_meta( $meta_key ) );
		}

		// Copy user details.
		$support_order->set_billing_first_name( $order->get_billing_first_name() );
		$support_order->set_billing_last_name( $order->get_billing_last_name() );
		$support_order->set_billing_address_1( $order->get_billing_address_1() );
		$support_order->set_billing_address_2( $order->get_billing_address_2() );
		$support_order->set_billing_city( $order->get_billing_city() );
		$support_order->set_billing_state( $order->get_billing_state() );
		$support_order->set_billing_postcode( $order->get_billing_postcode() );
		$support_order->set_billing_country( $order->get_billing_country() );
		$support_order->set_billing_email( $order->get_billing_email() );
		$support_order->set_shipping_first_name( $order->get_shipping_first_name() );
		$support_order->set_shipping_last_name( $order->get_shipping_last_name() );
		$support_order->set_shipping_address_1( $order->get_shipping_address_1() );
		$support_order->set_shipping_address_2( $order->get_shipping_address_2() );
		$support_order->set_shipping_city( $order->get_shipping_city() );
		$support_order->set_shipping_state( $order->get_shipping_state() );
		$support_order->set_shipping_postcode( $order->get_shipping_postcode() );
		$support_order->set_shipping_country( $order->get_shipping_country() );

		// Order note.
		$support_order->add_order_note(
			sprintf( 'This is a support order created from <a href="%s">#%s</a>.', $order_admin_url, $order->get_id() ),
			0,
			true
		);
		$support_order->calculate_totals();
		$support_order->save();

		// Order note to the original order.
		$order->add_order_note(
			sprintf( 'Support order created: <a href="%s">#%s</a>.', $support_order_admin_url, $support_order->get_id() ),
			0,
			true
		);
		$order->save();

		return $support_order;
	}

	/**
	 * Add Order Items to the Support Order
	 *
	 * @param \WC_Order $order Original order.
	 * @param \WC_Order $support_order Support order.
	 */
	public static function add_order_items( $order, $support_order) {
		foreach ( $order->get_items() as $item ) {

			$support_order->add_product(
				$item->get_product(),
				$item->get_quantity(),
				[
					'subtotal' => 0,
					'total'    => 0,
				]
			);
		}
	}

}

SupportOrders::instance();
