<?php
/**
 * A very rudimentary component test for the custom price display - just a page you can visit in a browser to see the output.
 * - For testing/debugging/working on UI display - HTML output, default CSS, etc., only.
 * - Does not check any functionality. Mock product values are assumed to match the expected values.
 * - Could be used for VR tests that check the default visual display.
 * - This shows the default CSS provided by this plugin only.
 *   You might want to also add WooCommerce and theme stylesheets and additional wrapping elements
 *   to more accurately reflect the display of a specific use case.
 *
 * Visit https://demo-dance.test/wp-content/plugins/demo-custom-pricing/__tests__/component/price-display.php to see the output.
 */

include(dirname(__DIR__, 2) . '/vendor/autoload.php');
use Doubleedesign\Pricing\FrontendDisplay;
use Doubleedesign\Pricing\Tests\Component\MockProducts;

/**
 * Utility function to render a test case with a label and HTML output,
 * because we're putting them all on the same page so it's useful to see which test case is which
 * @param $label
 * @param $product
 * @param $userRole
 * @return void
 */
function render_html_test_case($label, $product, $userRole = null): void {
	WP_Mock::setUp();
	WP_Mock::alias('current_user_can', function($role) use ($userRole) {
		return $role === $userRole;
	});
	WP_Mock::userFunction('get_woocommerce_currency_symbol', [
		'return' => '$',
	]);

	$instance = new FrontendDisplay();
	$html = $instance->get_custom_price_display_html('', $product);

	echo <<<HTML
		<figure class="test-case">
			<figcaption class="test-case__label">$label</figcaption>
			<div class="test-case__output">
				$html
			</div>
		</figure>
	HTML;
}

// Begin the HTML page output.
// Loads the default CSS provided by this plugin.
// Styles purely for the purpose of this component test are put in the <head> here so that they aren't loaded into production sites.
// TODO: Clean this up, put it in some kind of reusable template file, etc
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Custom Price Display - Component Tests</title>
	<link href="/wp-content/plugins/demo-custom-pricing/src/assets/style.css" rel="stylesheet" type="text/css">
	<style>
		/** Default colour variables copied over from woocommerce-blocktheme.css */
		:root {
		  --woocommerce: #720eec;
		  --wc-green: #7ad03a;
		  --wc-red: #a00;
		  --wc-orange: #ffba00;
		  --wc-blue: #2ea2cc;
		  --wc-primary: #720eec;
		  --wc-primary-text: #fcfbfe;
		  --wc-secondary: #e9e6ed;
		  --wc-secondary-text: #515151;
		  --wc-highlight: #958e09;
		  --wc-highligh-text: white;
		  --wc-content-bg: #fff;
		  --wc-subtext: #767676;
		}
		.test-case-group {
			max-width: fit-content;
			margin: 0 auto;
		}
		.test-case {
			padding: 1rem;
			box-sizing: border-box;
			font-family: Arial, sans-serif;
			border: 1px solid #ccc;
			width: 100%;
			margin-block: 2rem 1rem;
			position: relative;
		}
		.test-case__label {
			font-size: 0.875rem;
			background: white;
			border: 1px solid #ccc;
			padding: 0.5rem;
			margin-top: -2rem;
		}
		.test-case__output {
			margin-block-start: 1rem;
			padding-block: 0.5rem;
		}
</style>
</head>
<body>
<div class="test-case-group">
HTML;


render_html_test_case(
	'Non-member, no sale price || Regular price only (no custom prices set)',
	MockProducts::create([
		'regular_price' => '25.00',
		'sale_price'    => '',
		'price'         => '25.00',
	])
);

render_html_test_case(
	'Non-member with sale price || Regular price and sale price only (no custom prices set)',
	MockProducts::create([
		'regular_price' => '25.00',
		'sale_price'    => '20.00',
		'price'         => '20.00',
	])
);

render_html_test_case(
	'Member price is lower than regular (no sale price)',
	MockProducts::create([
		'regular_price' => '25.00',
		'sale_price'    => '',
		'price'         => '20.00'
	]),
	'member'
);

render_html_test_case(
	'Member price is equal to regular (no sale price)',
	MockProducts::create([
		'regular_price' => '25.00',
		'sale_price'    => '',
		'price'         => '25.00'
	]),
	'member'
);

render_html_test_case(
	'Member price is lower than sale price',
	MockProducts::create([
		'regular_price' => '25.00',
		'sale_price'    => '',
		'price'         => '20.00',
	]),
	'member'
);

render_html_test_case(
	'Member price is equal to sale price',
	MockProducts::create([
		'regular_price' => '25.00',
		'sale_price'    => '',
		'price'         => '20.00',
	]),
	'member'
);

echo <<<HTML
</div>
</body>
</html>
HTML;




