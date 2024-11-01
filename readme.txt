=== Splitter Orders For Woocommerce ===
Contributors: Liyanitsolution
Tags:  WooCommerce, Orders, Split, Split Order
Tested up to: 6.1.1
Requires PHP: 5.3
Stable tag: 1.0License: GPLv3

== Description ==
"Splitter Orders For Woocommerce" plugin, splits an order into separate orders based on the different conditions set by the admin. This extension automatically split an order into multiple orders based on some product attributes or the number of items in the cart. The customer will receive different order ids for their ordered cart.
With different order ids, customers can view all the order ids in their Order History and track each item separately. The admin can generate separate invoices and shipments for each order id.

==== Use Cases of Splitter Orders For Woocommerce ====
Default Condition
When the condition is Default then the order is split irrespective of any attribute. Example-1: If an order has 4 products then the order is split into 4 different orders no matter what attribute is chosen.Example-2: If the order is of 3 same T-Shirt but of different color or size(different attribute), in this case also order is split into 3 different orders.Note-   For example-1: In the backend there will be 5 orders. One order being a parent order that shows all the products under one order, and rest 4 orders will be of individual items.
 
== Installation ==
1. Upload the plugin files to the /wp-content/plugins/' directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the /Plugins/ screen in WordPress
3. Split sub menu will show under the "Woocommerce" menu.
 1. Enable split order(Keep it on yes if you want to split your orders) 2. Split order condition(it will remain on Default)