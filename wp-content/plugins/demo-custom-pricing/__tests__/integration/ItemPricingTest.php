<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Doubleedesign\Pricing\Tests\Integration;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Utility function to reduce duplicate code in test cases and keep them concise and consistent
 * This function runs an integration test case for item pricing
 * by fetching a product's data via the REST API using the .env credentials of the given test user
 * and returning the bits we need for concise usage
 * @param string $userEnvPrefix
 * @param int $productId
 * @return object
 * @throws GuzzleException
 */
function run_item_pricing_integration_test_case(string $userEnvPrefix, int $productId): object {
	$response = TestUtils::$client->request('GET', "products/$productId", [
		'auth' => TestUtils::getCustomerCredentials($userEnvPrefix),
		'headers' => [
			'Accept' => 'application/json',
		]
	]);

	$data = json_decode($response->getBody(), true);

	return (object)[
		'price' => $data['price'], // default WC field that uses regular and sale prices to work out the current basic price
		'regular_price' => $data['regular_price'],
		'sale_price' => $data['sale_price'],
		'member_price' => array_find($data['meta_data'], fn($meta) => $meta['key'] === '_member_price')['value'] ?? null
	];
}

describe('Item pricing (integration)', function()  {

	beforeEach(function() {
		TestUtils::setUp();
	});

	describe('Sale price handling', function() {

		test('it should maintain the sale price if the member price is lower but the user is not a member', function() {
			$data = run_item_pricing_integration_test_case(
				'TEST_BASIC',
				TestUtils::$TEST_PRODUCT_IDS['with_sale_price']['member_price_is_lower']['than_sale']
			);

			// First, we should check that this product meets our test case criteria in case anyone has messed with the test data
			expect($data->member_price)->toBeLessThan($data->price);

			// Then assert that the final price this customer will receive is the sale price
			expect($data->price)->toBe($data->sale_price);
		});

		test('it should set the sale price to empty for a member if the member price is lower', function() {
			$data = run_item_pricing_integration_test_case(
				'TEST_MEMBER',
				TestUtils::$TEST_PRODUCT_IDS['with_sale_price']['member_price_is_lower']['than_sale']
			);

			// First, we should check that this product meets our test case criteria in case anyone has messed with the test data
			expect($data->member_price)->toBeLessThanOrEqual($data->price);

			// Then assert that the sale price is empty for members
			expect($data->sale_price)->toBeEmpty();
		});

		test('it should maintain the sale price for a member if the member price is higher', function() {
			$data = run_item_pricing_integration_test_case(
				'TEST_MEMBER',
				TestUtils::$TEST_PRODUCT_IDS['with_sale_price']['member_price_is_higher']['than_sale']
			);

			// Confirm that the criteria for the test case were actually met by the given product
			expect($data->member_price)->toBeGreaterThan($data->price);

			// Then assert the result we're looking at is correct
			expect($data->sale_price)->toBe($data->price);
		});

		test('it should maintain the member price for a member if it is equal to the sale price', function() {
			$data = run_item_pricing_integration_test_case(
				'TEST_MEMBER',
				TestUtils::$TEST_PRODUCT_IDS['with_sale_price']['member_price_is_equal']['to_sale']
			);

			// Confirm that the criteria for the test case were actually met by the given product
			expect($data->member_price)->toBe($data->sale_price);

			// Then assert that the member price is maintained for members
			expect($data->price)->toBe($data->member_price);
		});
	});

	describe('Final price result', function() {

		describe('Member price is lower than regular price, no sale price is set', function() {

			test('non-member gets regular price', function() {
				$data = run_item_pricing_integration_test_case(
					'TEST_BASIC',
					TestUtils::$TEST_PRODUCT_IDS['no_sale_price']['member_price_is_lower']['than_regular']
				);

				// Confirm that the criteria for the test case were actually met by the given product
				expect($data->member_price)->toBeLessThan($data->regular_price);

				// Then assert that the non-member gets the regular price
				expect($data->price)->toBe($data->regular_price);
			});

			test('member gets member price', function() {
				$data = run_item_pricing_integration_test_case(
					'TEST_MEMBER',
					TestUtils::$TEST_PRODUCT_IDS['no_sale_price']['member_price_is_lower']['than_regular']
				);

				// Confirm that the criteria for the test case were actually met by the given product
				expect($data->member_price)->toBeLessThanOrEqual($data->regular_price);

				// Then assert that the member gets the member price
				expect($data->price)->toBe($data->member_price);
			});
		});

		// Edge case!
		// Assumption: Member prices should always be lower or equal to regular prices.
		// What happens if the site admin accidentally updates a regular price and causes it to be lower than the member price?
		describe('Member price is higher than regular price, no sale price is set', function() {

			test('non-member gets regular price', function() {
				$data = run_item_pricing_integration_test_case(
					'TEST_BASIC',
					TestUtils::$TEST_PRODUCT_IDS['no_sale_price']['member_price_is_higher']['than_regular']
				);

				// Confirm that the criteria for the test case were actually met by the given product
				expect($data->member_price)->toBeGreaterThan($data->regular_price);

				// Then assert that the non-member gets the regular price
				expect($data->price)->toBe($data->regular_price);
			});

			test('member gets regular price', function() {
				$data = run_item_pricing_integration_test_case(
					'TEST_MEMBER',
					TestUtils::$TEST_PRODUCT_IDS['no_sale_price']['member_price_is_higher']['than_regular']
				);

				// Confirm that the criteria for the test case were actually met by the given product
				expect($data->member_price)->toBeGreaterThan($data->regular_price);

				// Then assert that the member gets the regular price
				expect($data->price)->toBe($data->regular_price);
			});
		});

		describe('Member price higher than sale price', function() {

			test('non-member gets sale price', function() {
				$data = run_item_pricing_integration_test_case(
					'TEST_BASIC',
					TestUtils::$TEST_PRODUCT_IDS['with_sale_price']['member_price_is_higher']['than_sale']
				);

				// Confirm that the criteria for the test case were actually met by the given product
				expect($data->member_price)->toBeGreaterThan($data->sale_price);

				// Then assert that the non-member gets the sale price
				expect($data->price)->toBe($data->sale_price);

			});

			test('member gets sale price', function() {
				$data = run_item_pricing_integration_test_case(
					'TEST_MEMBER',
					TestUtils::$TEST_PRODUCT_IDS['with_sale_price']['member_price_is_higher']['than_sale']
				);

				// Confirm that the criteria for the test case were actually met by the given product
				expect($data->member_price)->toBeGreaterThan($data->sale_price);

				// Then assert that the member gets the sale price
				expect($data->price)->toBe($data->sale_price);
			});
		});

		describe('member price is lower than sale price', function() {

			test('non-member gets sale price', function() {
				$data = run_item_pricing_integration_test_case(
					'TEST_BASIC',
					TestUtils::$TEST_PRODUCT_IDS['with_sale_price']['member_price_is_lower']['than_sale']
				);

				// Confirm that the criteria for the test case were actually met by the given product
				expect($data->member_price)->toBeLessThan($data->sale_price);

				// Then assert that the non-member gets the sale price
				expect($data->price)->toBe($data->sale_price);
			});

			test('member gets member price', function() {
				$data = run_item_pricing_integration_test_case(
					'TEST_MEMBER',
					TestUtils::$TEST_PRODUCT_IDS['with_sale_price']['member_price_is_lower']['than_sale']
				);

				// Confirm that the criteria for the test case were actually met by the given product
				expect($data->member_price)->not->toBeEmpty()
					->and($data->sale_price)->toBeEmpty();

				// Then assert that the member gets the member price
				expect($data->price)->toBe($data->member_price);
			});
		});
	});
});
