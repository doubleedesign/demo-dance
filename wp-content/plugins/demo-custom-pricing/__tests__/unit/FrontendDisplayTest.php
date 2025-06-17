<?php
namespace Doubleedesign\Pricing\Tests\Unit;
use Doubleedesign\Pricing\{FrontendDisplay};
use WP_Mock;
use Mockery;

describe('FrontendDisplay', function() {

	beforeEach(function() {
		WP_Mock::setUp();
		MockUtils::mock_front_end_context();
	});

	afterEach(function() {
		WP_Mock::tearDown();
		Mockery::close();
	});

	test('it adds the filter for the custom sale badge output', function() {
		WP_Mock::expectFilterAdded('woocommerce_sale_flash', [Mockery::anyof(FrontendDisplay::class), 'get_custom_sale_badge_html'], 20, 2);

		$instance = new FrontendDisplay();

		WP_Mock::assertHooksAdded();
	});
});
