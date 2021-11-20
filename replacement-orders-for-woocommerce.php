<?php
/**
 * Plugin Name: Replacement Orders for WooCommerce
 * Plugin URI: https://devpress.com
 * Description: Easily create WooCommerce replacement orders for your customers.
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
 * Class ReplacementOrders
 * @package ReplacementOrders
 */
class ReplacementOrders {

	/**
	 * The single instance of the class.
	 *
	 * @var mixed $instance
	 */
	protected static $instance;

	/**
	 * Main ReplacementOrdersForWooCommerce Instance.
	 *
	 * Ensures only one instance of the ReplacementOrdersForWooCommerce is loaded or can be loaded.
	 *
	 * @return ReplacementOrders - Main instance.
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
		add_filter( 'woocommerce_order_actions', [ $this, 'add_replacement_order_action' ] );
		add_action( 'woocommerce_order_action_create_replacement_order', array( $this, 'create_replacement_order_and_redirect' ) );
	}

	/**
	 * Checks if the order is a replacement order.
	 * Support orders all have _created_via meta of "support".
	 *
	 * @param \WC_Order $order Order object.
	 *
	 * @return bool
	 */
	public static function is_replacement_order( \WC_Order $order ) {
		return 'support' === $order->get_created_via();
	}

	/**
	 * Add order action.
	 *
	 * @param array $actions
	 *
	 * @return mixed
	 */
	public function add_replacement_order_action( $actions ) {
		global $post;

		if ( ! isset( $post->ID ) ) {
			return $actions;
		}

		$order = wc_get_order( $post->ID );
		if ( ! $order ) {
			return $actions;
		}

		// Do not offer replacement orders from replacement orders.
		if ( self::is_replacement_order( $order ) ) {
			return $actions;
		}

		$actions['create_replacement_order'] = __( 'Create a replacement order', 'replacement-orders' );

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
	public static function create_replacement_order_and_redirect( $order ) {
		$replacement_order = self::create_replacement_order( $order);

		$replacement_order_admin_url = admin_url( sprintf( 'post.php?action=edit&post=%s', $replacement_order->get_id() ) );

		// Redirect to the newly created replacement order.
		wp_safe_redirect( $replacement_order_admin_url );
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
	public static function create_replacement_order( $order, $copy_meta = [], $add_items = true ) {
		$replacement = wc_create_order(
			[
				'created_via' => 'support',
			]
		);

		$order_admin_url         = admin_url( sprintf( 'post.php?action=edit&post=%s', $order->get_id() ) );
		$support_order_admin_url = admin_url( sprintf( 'post.php?action=edit&post=%s', $replacement->get_id() ) );

		if ( $add_items ) {
			self::add_order_items( $order, $replacement );
		}

		// Copy meta.
		foreach ( $copy_meta as $meta_key ) {
			$replacement->update_meta_data( $meta_key, $order->get_meta( $meta_key ) );
		}

		// Copy user details.
		$replacement->set_billing_first_name( $order->get_billing_first_name() );
		$replacement->set_billing_last_name( $order->get_billing_last_name() );
		$replacement->set_billing_address_1( $order->get_billing_address_1() );
		$replacement->set_billing_address_2( $order->get_billing_address_2() );
		$replacement->set_billing_city( $order->get_billing_city() );
		$replacement->set_billing_state( $order->get_billing_state() );
		$replacement->set_billing_postcode( $order->get_billing_postcode() );
		$replacement->set_billing_country( $order->get_billing_country() );
		$replacement->set_billing_email( $order->get_billing_email() );
		$replacement->set_shipping_first_name( $order->get_shipping_first_name() );
		$replacement->set_shipping_last_name( $order->get_shipping_last_name() );
		$replacement->set_shipping_address_1( $order->get_shipping_address_1() );
		$replacement->set_shipping_address_2( $order->get_shipping_address_2() );
		$replacement->set_shipping_city( $order->get_shipping_city() );
		$replacement->set_shipping_state( $order->get_shipping_state() );
		$replacement->set_shipping_postcode( $order->get_shipping_postcode() );
		$replacement->set_shipping_country( $order->get_shipping_country() );

		// Order note.
		$replacement->add_order_note(
			sprintf( 'This is a replacement order created from <a href="%s">#%s</a>.', $order_admin_url, $order->get_id() ),
			0,
			true
		);
		$replacement->calculate_totals();
		$replacement->save();

		// Order note to the original order.
		$order->add_order_note(
			sprintf( 'Replacement order created: <a href="%s">#%s</a>.', $support_order_admin_url, $replacement->get_id() ),
			0,
			true
		);
		$order->save();

		return $replacement;
	}

	/**
	 * Add Order Items to the Support Order
	 *
	 * @param \WC_Order $order Original order.
	 * @param \WC_Order $support_order Support order.
	 */
	public static function add_order_items( $order, $replacement_order ) {
		foreach ( $order->get_items() as $item ) {

			$replacement_order->add_product(
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

ReplacementOrders::instance();
