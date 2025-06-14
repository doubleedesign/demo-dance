<?php
namespace Doubleedesign\Pricing;
use WC_Product;

class ItemPricing {

	public function __construct() {
		add_filter('woocommerce_product_get_sale_price', [$this, 'update_sale_price'], 10, 2);
		add_filter('woocommerce_product_is_on_sale', [$this, 'update_sale_status'], 10, 2);
		add_filter('woocommerce_product_get_price', [$this, 'calculate_item_price'], 10, 2);
		add_action('woocommerce_product_read', [$this, 'calculate_item_price_variable'], 25, 2);
	}

	/**
	 * Alter the sale price of an individual product for members.
	 * This alters the sale_price field.
	 * @param $price
	 * @param WC_Product $product
	 * @return float|string
	 */
	public function update_sale_price($price, WC_Product $product): float|string {
		if (is_admin() && !defined('DOING_AJAX')) return $price;
		$product_id = $product->get_id();

		// If the member price is lower than the sale price, the user should not see a sale price.
		// Otherwise, the final price field would reflect the sale price even if the member price is lower;
		// and we don't want to set the sale price to the member price, as that would be misleading.
		if(current_user_can('member')) {
			$member_price = get_post_meta($product_id, '_member_price', true);
			if ($member_price && $member_price <= $price) {
				return "";
			}
		}

		return $price;
	}


	/**
	 * Alter the sale status of an individual product for members.
	 * This alters the on_sale field.
	 * @param $is_on_sale
	 * @param WC_Product $product
	 * @return bool
	 */
	function update_sale_status($is_on_sale, WC_Product $product): bool {
		if (is_admin() && !defined('DOING_AJAX')) return $is_on_sale;

		if(current_user_can('member') && $product->get_sale_price() == '') {
			return false;
		}

		return $is_on_sale;
	}


	/**
	 * Calculate the final item price for a product, taking into account member pricing.
	 * This alters the price field, not the regular_price field. This is to allow for front-end usage of the regular price to show members the difference.
	 * @param $price
	 * @param WC_Product $product
	 * @return float|string
	 */
	function calculate_item_price($price, WC_Product $product): float|string {
		if (is_admin() && !defined('DOING_AJAX')) return $price;
		$product_id = $product->get_id();

		if(current_user_can('member')) {
			$member_price = get_post_meta($product_id, '_member_price', true);
			if ($member_price && $member_price < $price) {
				return $member_price;
			}
		}

		return $price;
	}


	/**
	 * The code run on the product meta processing hook in PricingFields.php saves the regular and sale prices to the postmeta table for variable products,
	 * but there is another function that overrides it to empty at runtime somewhere that affects the admin, REST API, and other wc_get_product() calls.
	 * Running this on woocommerce_product_read overrides that and ensures the correct values are displayed on the front-end and in REST API responses.
	 *
	 * @param $post_id
	 * @param WC_Product $product
	 * @return void
	 */
	function calculate_item_price_variable($post_id, WC_Product $product): void {
		if (is_admin() && !defined('DOING_AJAX')) return;

		if ($product->is_type('variable')) {
			$regular_price = get_post_meta($post_id, '_regular_price', true);
			$sale_price = get_post_meta($post_id, '_sale_price', true);

			$product->set_regular_price($regular_price);
			$product->set_sale_price($sale_price);

			// If there is a sale price, use that to calculate the final price
			if(floatval($sale_price) > 0) {
				$lower_price = min($regular_price, $sale_price);
				$price = $this->calculate_item_price($lower_price, $product);
				$product->set_price($price);
			}
			// Otherwise, use the regular price to calculate the final price
			else {
				$price = $this->calculate_item_price($regular_price, $product);
				$product->set_price($price);
			}
		}
	}

}
