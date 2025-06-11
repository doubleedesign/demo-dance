<?php
namespace Doubleedesign\Pricing;

class CartPricing {

	public function __construct() {
		add_action('woocommerce_before_calculate_totals', [$this, 'calculate_cart_discounts'], 10, 1);
	}

	public function calculate_cart_discounts($cart): void {
		if (is_admin() && !defined('DOING_AJAX')) return;


		// TODO Check if cart is eligible for case pricing

		// TODO Find the lowest price per item in the cart and adjust the total
	}
}
