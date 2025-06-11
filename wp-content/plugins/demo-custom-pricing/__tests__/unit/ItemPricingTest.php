<?php
namespace Doubleedesign\Pricing\Tests\Unit;
use Doubleedesign\Pricing\{ItemPricing};
use Mockery;
use WP_Mock;

describe('Item pricing', function() {

	beforeEach(function() {
		WP_Mock::setUp();
		MockUtils::mock_front_end_context();
	});

	afterEach(function() {
		WP_Mock::tearDown();
		Mockery::close();
	});

	describe('Sale price calculation', function() {

		test('it should maintain the sale price if the member price is lower but the user is not a member', function() {
			$regPrice = 25.00;
			$salePrice = 20.00;
			$memberPrice = 17.50;

			// Instantiate the class here so it's after the mocks have been set up
			$instance = new ItemPricing();

			// Create a minimal mock of a product and the relevant post meta
			$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
			MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

			// Mock the result of current_user_can for the member role
			MockUtils::mock_user_role_or_cap('member', false);

			// Run the function we're testing
			$result = $instance->update_sale_price($salePrice, $product);

			// TODO: Can we assert that get_post_meta was not called?

			// Assert that the sale price has not changed
			expect($result)->toBe($salePrice);
		});

		test('it should set the sale price to empty if the member price is cheaper', function() {
			$regPrice = 25.00;
			$salePrice = 20.00;
			$memberPrice = 17.50;

			// Instantiate the class here so it's after the mocks have been set up
			$instance = new ItemPricing();

			// Create a minimal mock of a product and the relevant post meta
			$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
			MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

			// Mock the result of current_user_can for the member role
			MockUtils::mock_user_role_or_cap('member', true);

			// Run the function we're testing
			$result = $instance->update_sale_price($salePrice, $product);

			// Assert that the result is no sale price
			expect($result)->toBe("");
		});
	});

	describe('Final price calculation', function() {

		describe('Member price is the same as the regular price, no sale price is set', function() {

			test('non-member gets regular price', function() {
				$regPrice = 25.00;
				$memberPrice = 25.00;

				// Instantiate the class here so it's after the mocks have been set up
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', false);

				// Run the function we're testing
				$result = $instance->calculate_item_price($regPrice, $product);

				// TODO: Can we assert that get_post_meta was not called?

				// Assert that the regular price is returned
				expect($result)->toBe($regPrice);
			});

			test('Member gets regular price', function() {
				$regPrice = 25.00;
				$memberPrice = 25.00;

				// Instantiate the class here so it's after the mocks have been set up
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', true);

				// Run the function we're testing
				$result = $instance->calculate_item_price($regPrice, $product);

				// TODO: Can we assert that get_post_meta was not called?

				// Assert that the regular price is returned
				expect($result)->toBe($regPrice);
			});
		});

		describe('Member price is lower than regular price, no sale price is set', function() {

			test('non-member gets regular price', function() {
				$regPrice = 25.00;
				$memberPrice = 20.00;

				// Instantiate the class here so it's after the mocks have been set up
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', false);

				// Run the function we're testing
				$result = $instance->calculate_item_price($regPrice, $product);

				// Assert that the regular price is returned
				expect($result)->toBe($regPrice);
			});

			test('member gets member price', function() {
				$regPrice = 25.00;
				$memberPrice = 20.00;

				// Instantiate the class here so it's after the mocks have been set up
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', true);

				// Run the function we're testing
				$result = $instance->calculate_item_price($regPrice, $product);

				// Assert that the member price is returned
				expect($result)->toBe($memberPrice);
			});
		});

		// Edge case!
		// Assumption: Member prices should always be lower or equal to regular prices.
		// What happens if the site admin accidentally updates a regular price and causes it to be lower than the member price?
		describe('Member price is higher than regular price, no sale price is set', function() {

			test('non-member gets regular price', function() {
				$regPrice = 25.00;
				$memberPrice = 30.00;

				// Instantiate the class here so it's after the mocks have been set up
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', false);

				// Run the function we're testing
				$result = $instance->calculate_item_price($regPrice, $product);

				// Assert that the regular price is returned
				expect($result)->toBe($regPrice);
			});

			test('member gets regular price', function() {
				$regPrice = 25.00;
				$memberPrice = 30.00;

				// Instantiate the class here so it's after the mocks have been set up
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', true);

				// Run the function we're testing
				$result = $instance->calculate_item_price($regPrice, $product);

				// Assert that the regular price is returned
				expect($result)->toBe($regPrice);
			});
		});

		describe('Member price higher than sale price', function() {
			$regPrice = 25.00;
			$salePrice = 15.00;
			$memberPrice = 20.00;

			test('non-member gets sale price', function() use ($regPrice, $salePrice, $memberPrice) {
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', false);

				// Run the function we're testing
				$result = $instance->calculate_item_price($salePrice, $product);

				// Assert that the sale price is returned
				expect($result)->toBe($salePrice);
			});

			test('member gets sale price', function() use ($regPrice, $salePrice, $memberPrice) {
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', true);

				// Run the function we're testing
				$result = $instance->calculate_item_price($salePrice, $product);

				// Assert that the sale price is returned
				expect($result)->toBe($salePrice);
			});
		});

		describe('member price is lower than sale price', function() {
			$regPrice = 25.00;
			$salePrice = 15.00;
			$memberPrice = 10.00;

			test('non-member gets sale price', function() use ($regPrice, $salePrice, $memberPrice) {
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', false);

				// Run the function we're testing
				$result = $instance->calculate_item_price($salePrice, $product);

				// Assert that the sale price is returned
				expect($result)->toBe($salePrice);
			});

			test('member gets member price', function() use ($regPrice, $salePrice, $memberPrice) {
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', true);

				// Run the function we're testing
				$result = $instance->calculate_item_price($salePrice, $product);

				// Assert that the member price is returned
				expect($result)->toBe($memberPrice);
			});
		});

		describe('Member price is the same as sale price', function() {
			$regPrice = 25.00;
			$salePrice = 15.00;
			$memberPrice = 15.00;

			test('non-member gets sale price', function() use ($regPrice, $salePrice, $memberPrice) {
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', false);

				// Run the function we're testing
				$result = $instance->calculate_item_price($salePrice, $product);

				// Assert that the sale price is returned
				expect($result)->toBe($salePrice);
			});

			test('member gets member price', function() use ($regPrice, $salePrice, $memberPrice) {
				$instance = new ItemPricing();

				// Create a minimal mock of a product and the relevant post meta
				$product = MockProducts::create(['id' => 123, 'regular_price' => $regPrice]);
				MockUtils::mock_postmeta(123, '_member_price', $memberPrice);

				// Mock the result of current_user_can for the member role
				MockUtils::mock_user_role_or_cap('member', true);

				// Run the function we're testing
				$result = $instance->calculate_item_price($salePrice, $product);

				// Assert that the member price is returned
				expect($result)->toBe($memberPrice);
			});
		});
	});

	describe('Hook application', function() {

		test('it should register the sale price function on the expected WooCommerce hook', function() {
			// Set up the expectation before creating the instance, because that's how WP_Mock works for some reason
			WP_Mock::expectFilterAdded('woocommerce_product_get_sale_price', [Mockery::anyOf(ItemPricing::class), 'update_sale_price'], 10, 2);

			// Create the instance so the constructor runs and applies the filter
			$instance = new ItemPricing();
		});

		test('it should register the price calculation function on the expected WooCommerce hook', function() {
			// Set up the hooks expectation before creating the instances, because that's how WP_Mock works for some reason
			WP_Mock::expectFilterAdded('woocommerce_product_get_price', [Mockery::anyOf(ItemPricing::class), 'calculate_item_price'], 10, 2);

			// Create the instance so the constructor runs and adds the filter
			$instance = new ItemPricing();
		});

		test('it should register the variable product price calculation function on the expected WooCommerce hook', function() {
			// Set up the hooks expectation before creating the instances, because that's how WP_Mock works for some reason
			WP_Mock::expectActionAdded('woocommerce_product_read', [Mockery::anyOf(ItemPricing::class), 'calculate_item_price_variable'], 30, 2);

			// Create the instance so the constructor runs and adds the filter
			$instance = new ItemPricing();
		});
	});
});
