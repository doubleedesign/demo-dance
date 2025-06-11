<?php
/**
 * Plugin Name: Custom Pricing Demo
 * Description: A plugin to demo implementing and testing complex pricing rules in WooCommerce.
 * Author: Leesa Ward
 * Author URI: https://timefortesting.net
 * Requires plugins: woocommerce
 */

require_once __DIR__ . '/vendor/autoload.php';

use Doubleedesign\Pricing\{AdminUi,CustomRoles,PricingFields,ItemPricing,CartPricing,FrontendDisplay};
new AdminUi();
new CustomRoles();
new PricingFields();
new ItemPricing();
new CartPricing();
new FrontendDisplay();

if(defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local') {
	// Load integration tests bootstrap
	require_once __DIR__ . '/__tests__/integration/Bootstrap.php';
	Doubleedesign\Pricing\Tests\Integration\Bootstrap::setUp();
}
