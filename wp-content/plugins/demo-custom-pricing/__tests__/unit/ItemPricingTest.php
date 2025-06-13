<?php
namespace Doubleedesign\Pricing\Tests\Unit;
use Doubleedesign\Pricing\{ItemPricing};
use Mockery;
use WP_Mock;

/**
 * Utility function to reduce duplicate code in test cases and keep them concise and consistent
 * This function arranges the necessary objects and values for the test case actions and assertions.
 * @param float $regPrice - the regular price of the product
 * @param float|null $salePrice - the sale price of the product, if set
 * @param float|null $memberPrice - the member price of the product, if set
 * @param string $productType - the type of product (simple or variable)
 * @param bool $userIsMember - whether the current user is a member
 */
function arrange_item_pricing_unit_test_case(float $regPrice, ?float $salePrice, ?float $memberPrice, bool $userIsMember, string $productType = 'simple'): object {
	// Arrange: Instantiate the class we're testing, a mock product, whether the current user is a member, and member price postmeta
	$instance = new ItemPricing();
	$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice, 'sale_price' => $salePrice, 'type' => $productType]);
	MockUtils::mock_user_role_or_cap('member', $userIsMember);
	$memberPricePostmetaSpy = MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

	return (object) [
		'instance' => $instance,
		'product' => $product,
		'memberPricePostmetaSpy' => $memberPricePostmetaSpy
	];
}

describe('Item pricing (unit)', function() {

	beforeEach(function() {
		WP_Mock::setUp();
		MockUtils::mock_front_end_context();
	});

	afterEach(function() {
		WP_Mock::tearDown();
		Mockery::close();
	});

	describe('Hook application', function() {

		test('it should register the sale price function on the expected WooCommerce hook', function() {
			$case = arrange_item_pricing_unit_test_case(25.00, 20.00, 17.50, true);

			// Set up the expectation before creating the instance, because that's how WP_Mock works for some reason
			WP_Mock::expectFilterAdded('woocommerce_product_get_sale_price', [Mockery::anyOf(ItemPricing::class), 'update_sale_price'], 10, 2);

			// Create the instance so the constructor runs and applies the filter
			$case->instance = new ItemPricing();
		});

		test('it should register the price calculation function on the expected WooCommerce hook', function() {
			$case = arrange_item_pricing_unit_test_case(25.00, 20.00, 17.50, true);

			// Set up the hooks expectation before creating the instances, because that's how WP_Mock works for some reason
			WP_Mock::expectFilterAdded('woocommerce_product_get_price', [Mockery::anyOf(ItemPricing::class), 'calculate_item_price'], 10, 2);

			// Create the instance so the constructor runs and adds the filter
			$case->instance = new ItemPricing();
		});

		test('it should register the variable product price calculation function on the expected WooCommerce hook', function() {
			$case = arrange_item_pricing_unit_test_case(25.00, 20.00, 17.50, true);

			// Set up the hooks expectation before creating the instances, because that's how WP_Mock works for some reason
			WP_Mock::expectActionAdded('woocommerce_product_read', [Mockery::anyOf(ItemPricing::class), 'calculate_item_price_variable'], 25, 2);

			// Create the instance so the constructor runs and adds the filter
			$case->instance = new ItemPricing();
		});
	});

	describe('Sale price calculation', function() {
		$regPrice = 25.00;
		$salePrice = 20.00;
		$memberPrice = 17.50;

		test('it should maintain the sale price if the member price is lower but the user is not a member', function() use ($salePrice, $memberPrice, $regPrice) {
			// Arrange the scenario using the utility function
			$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, $memberPrice, false);

			// Act: Run the function we're testing
			$result = $case->instance->update_sale_price($salePrice, $case->product);

			// Optional: Assert that the member price was not fetched
			// Not really necessary for our fairly basic functionality,
			// but may be useful in more complex situations where we want to ensure no unnecessary database calls are made
			$case->memberPricePostmetaSpy->shouldNotHaveReceived('__invoke', [Mockery::any(), '_member_price', Mockery::any()]);

			// Assert that the sale price has not changed
			expect($result)->toBe($salePrice);
		});

		test('it should set the sale price to empty if the member price is cheaper', function() use ($salePrice, $memberPrice, $regPrice) {
			// Arrange the scenario using the utility function
			$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, $memberPrice, true);

			// Act: run the function we're testing
			$result = $case->instance->update_sale_price($salePrice, $case->product);

			// Optional: Assert that the member price was not fetched
			// Not really necessary for our fairly basic functionality,
			// but may be useful in more complex situations where we want to ensure no unnecessary database calls are made
			$case->memberPricePostmetaSpy->shouldHaveReceived('__invoke', [Mockery::any(), '_member_price', Mockery::any()]);

			// Assert that the sale price is empty
			expect($result)->toBe("");
		});
	});

	describe('Final price calculation', function() {

		describe('Member price is the same as the regular price, no sale price is set', function() {
			$regPrice = 25.00;
			$memberPrice = 25.00;

			test('non-member gets regular price', function() use ($regPrice, $memberPrice) {
				// Arrange the scenario using the utility function
				$case = arrange_item_pricing_unit_test_case($regPrice, null, $memberPrice, false);

				// Act: run the function we're testing
				$result = $case->instance->calculate_item_price(25.00, $case->product);

				// Assert that the regular price is returned
				expect($result)->toBe($regPrice);
			});

			test('Member gets regular price', function() use ($memberPrice, $regPrice) {
				// Arrange the scenario using the utility function
				$case = arrange_item_pricing_unit_test_case($regPrice, null, $memberPrice, true);

				// Act: run the function we're testing
				$result = $case->instance->calculate_item_price($regPrice, $case->product);

				// Assert that the regular price is returned
				expect($result)->toBe($regPrice);
			});
		});

		describe('Member price is lower than regular price, no sale price is set', function() {
			$regPrice = 25.00;
			$memberPrice = 20.00;

			test('non-member gets regular price', function() use ($regPrice, $memberPrice) {
				$case = arrange_item_pricing_unit_test_case($regPrice, null, $memberPrice, false);

				$result = $case->instance->calculate_item_price($regPrice, $case->product);

				expect($result)->toBe($regPrice);
			});

			test('member gets member price', function() use ($memberPrice, $regPrice) {
				$case = arrange_item_pricing_unit_test_case($regPrice, null, $memberPrice, true);

				$result = $case->instance->calculate_item_price($regPrice, $case->product);

				expect($result)->toBe($memberPrice);
			});
		});

		// Edge case!
		// Assumption: Member prices should always be lower or equal to regular prices.
		// What happens if the site admin accidentally updates a regular price and causes it to be lower than the member price?
		describe('Member price is higher than regular price, no sale price is set', function() {
			$regPrice = 25.00;
			$memberPrice = 30.00;

			test('non-member gets regular price', function() use ($memberPrice, $regPrice) {
				$case = arrange_item_pricing_unit_test_case($regPrice, null, $memberPrice, false);
				$result = $case->instance->calculate_item_price($regPrice, $case->product);

				// Assert that the regular price is returned
				expect($result)->toBe($regPrice);
			});

			test('member gets regular price', function() use ($regPrice, $memberPrice) {
				$case = arrange_item_pricing_unit_test_case($regPrice, null, $memberPrice, true);

				$result = $case->instance->calculate_item_price($regPrice, $case->product);

				expect($result)->toBe($regPrice);
			});
		});

		describe('Member price higher than sale price', function() {
			$regPrice = 25.00;
			$salePrice = 15.00;
			$memberPrice = 20.00;

			test('non-member gets sale price', function() use ($regPrice, $salePrice, $memberPrice) {
				$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, $memberPrice, false);

				$result = $case->instance->calculate_item_price($salePrice, $case->product);

				expect($result)->toBe($salePrice);
			});

			test('member gets sale price', function() use ($regPrice, $salePrice, $memberPrice) {
				$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, $memberPrice, true);

				$result = $case->instance->calculate_item_price($salePrice, $case->product);

				expect($result)->toBe($salePrice);
			});
		});

		describe('member price is lower than sale price', function() {
			$regPrice = 25.00;
			$salePrice = 15.00;
			$memberPrice = 10.00;

			test('non-member gets sale price', function() use ($regPrice, $salePrice, $memberPrice) {
				$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, $memberPrice, false);

				$result = $case->instance->calculate_item_price($salePrice, $case->product);

				expect($result)->toBe($salePrice);
			});

			test('member gets member price', function() use ($regPrice, $salePrice, $memberPrice) {
				$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, $memberPrice, true);

				$result = $case->instance->calculate_item_price($salePrice, $case->product);

				expect($result)->toBe($memberPrice);
			});
		});

		describe('Member price is the same as sale price', function() {
			$regPrice = 25.00;
			$salePrice = 15.00;
			$memberPrice = 15.00;

			test('non-member gets sale price', function() use ($regPrice, $salePrice, $memberPrice) {
				$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, $memberPrice, false);

				$result = $case->instance->calculate_item_price($salePrice, $case->product);

				expect($result)->toBe($salePrice);
			});

			test('member gets member price', function() use ($regPrice, $salePrice, $memberPrice) {
				$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, $memberPrice, true);

				$result = $case->instance->calculate_item_price($salePrice, $case->product);

				expect($result)->toBe($memberPrice);
			});
		});
	});

	describe('Variable product handling', function() {
		$regPrice = 25.00;
		$salePrice = 20.00;
		$memberPrice = 17.50;

		test('it should set the regular price', function() use ($regPrice) {
			// Arrange the scenario using the utility function
			$case = arrange_item_pricing_unit_test_case($regPrice, null, null, false, 'variable');

			// Act: run the function we're testing
			$case->instance->calculate_item_price_variable(123, $case->product);

			// Assert that the regular price is set
			$case->product->shouldHaveReceived('set_regular_price')->with($regPrice);
		});

		test('it should set the price field to the regular price', function() use ($regPrice) {
			// Arrange the scenario using the utility function
			$case = arrange_item_pricing_unit_test_case($regPrice, null, null, false, 'variable');

			// Act: run the function we're testing
			$case->instance->calculate_item_price_variable(123, $case->product);

			// Assert that the price field is set to the regular price
			$case->product->shouldHaveReceived('set_price')->with($regPrice);
		});

		test('it should set the sale price', function() use ($regPrice, $salePrice) {
			// Arrange the scenario using the utility function
			$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, null, false, 'variable');

			// Act: run the function we're testing
			$case->instance->calculate_item_price_variable(123, $case->product);

			// Assert that the sale price is set
			$case->product->shouldHaveReceived('set_sale_price')->with($salePrice);
		});

		test('it should set the price field to the sale price', function() use ($regPrice, $salePrice) {
			// Arrange the scenario using the utility function
			$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, null, false, 'variable');

			// Act: run the function we're testing
			$case->instance->calculate_item_price_variable(123, $case->product);

			// Assert that the price field is set to the sale price
			$case->product->shouldHaveReceived('set_price')->with($salePrice);
		});

		// At this point,coverage reporting was telling me everything was covered.
		// However, I am not convinced of that because we are not actually testing member pricing on variable products.
		// At the time of writing, the calculate_item_price_variable method calls the calculate_item_price method, which has its own tests below.
		// What if that changes in the future? How can we prevent a change from breaking member pricing?
		// In our case, the integration tests would pick it up, but what if we didn't have those?
		// We have two options here: Directly test for member price + variable product, or assert that the calculate_item_price method is called.
		// The latter is testing an implementation detail and is brittle; the implementation could change and the test would fail, but the functionality could still be correct.
		// So even though it's more verbose, adding a couple of tests for member price + variable product is the more robust option.
		describe('user is a member and there is a member price', function() {
			$regPrice = 25.00;
			$salePrice = 22.00;
			$memberPrice = 20.00;

			test('it sets the price to the member price if it is lower than the regular price', function() use ($regPrice, $memberPrice) {
				// Arrange the scenario using the utility function
				$case = arrange_item_pricing_unit_test_case($regPrice, null, $memberPrice, true, 'variable');

				// Act: run the function we're testing
				$case->instance->calculate_item_price_variable(123, $case->product);

				// Assert that the price field is set to the member price
				$case->product->shouldHaveReceived('set_price')->with($memberPrice);
			});

			test('it sets the price to the member price if it is lower than the sale price', function() use ($salePrice, $regPrice, $memberPrice) {
				// Arrange the scenario using the utility function
				$case = arrange_item_pricing_unit_test_case($regPrice, $salePrice, $memberPrice, true, 'variable');

				// Act: run the function we're testing
				$case->instance->calculate_item_price_variable(123, $case->product);

				// Assert that the price field is set to the member price
				$case->product->shouldHaveReceived('set_price')->with($memberPrice);
			});
		});
	});
});
