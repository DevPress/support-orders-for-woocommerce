=== Support Orders for WooCommerce ===

Contributors: developer_name
Tags: woocommerce, orders, support, replacement
Requires at least: 6.4
Tested up to: 6.9
Stable tag: 1.1.0
Requires PHP: 8.2
WC requires at least: 8.0.0
WC tested up to: 10.4.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Easily create WooCommerce replacement support orders from existing orders.

== Description ==

This extension allows you to quickly create replacement orders for an existing order. All line items are set to $0 automatically and no charges are made to the customer. This allows you to send out order replacements if the first shipment did not arrive or arrived damaged.

**How to use:**

1. Find the order that you wish to create a replacement support order for.
2. Choose "Create support order" from the "Order Actions" dropdown on the order edit screen.
3. Make any edits you may need for the new replacement support order.
4. Switch the status to "processing".

When a support order is created, an order note will be added to the original order and the replacement support order.

== Screenshots ==

1. Screenshot of the order actions dropdown.

== Changelog ==

= 1.1.0 =

* Updated compatibility for WooCommerce 10.4.3 and WordPress 6.9.
* Updated minimum PHP requirement to 8.2.
* Updated minimum WooCommerce requirement to 8.0.0.
* Fixed text domain for translation strings.
* Added HPOS (High-Performance Order Storage) compatibility.
* Added additional unit tests.
* Standardized license to GPL-3.0.

= 1.0.0 =

* Initial release.
