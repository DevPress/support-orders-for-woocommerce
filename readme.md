# Support Orders for WooCommerce ![Testing status](https://github.com/devpress/support-orders-for-woocommerce/actions/workflows/php-tests.yml/badge.svg?branch=main)

* Requires PHP: 8.2
* WP requires at least: 6.4
* WP tested up to: 6.9
* WC requires at least: 8.0.0
* WC tested up to: 10.4.3
* Stable tag: 1.1.0
* License: [GPLv3 or later License](http://www.gnu.org/licenses/gpl-3.0.html)

## Description

This extension allows you to quickly create replacement orders for an existing order. All line items are set to $0 automatically and no charges are made to the customer. This allows you to send out order replacements if the first shipment did not arrive or arrived damaged.

### Details

**Create a replacement support order**

1. Find the order that you wish to create a replacement support order for.
2. Choose "Create support order" from the "Order Actions" dropdown on the order edit screen.
3. Make any edits you may need for the new replacement support order.
4. Switch the status to "processing".

![Screenshot of the order actions dropdown.](https://github.com/devpress/support-orders-for-woocommerce/raw/main/assets/order-actions.png)

When a support order is created, an order note will be added to the original order and the replacement support order.
