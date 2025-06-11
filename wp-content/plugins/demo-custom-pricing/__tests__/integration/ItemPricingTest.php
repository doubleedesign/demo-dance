<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Doubleedesign\Pricing\Tests\Integration;

describe('Item pricing', function()  {

	beforeEach(function() {
		TestUtils::setUp();
	});

	describe('Sale price handling', function() {

		test('it should maintain the sale price if the member price is lower but the user is not a member', function() {
			$response = TestUtils::$client->request('GET', 'products/117', [
				'auth' => TestUtils::getCustomerCredentials('TEST_BASIC'),
				'headers' => [
					'Accept' => 'application/json',
				]
			]);

			// First, we should check that this product meets our test case criteria in case anyone has messed with the test data
			$data = json_decode($response->getBody(), true);
			$basic_price = $data['price'];
			$member_price = array_find($data['meta_data'], function($meta) {
				return $meta['key'] === '_member_price';
			})['value'] ?? null;
			expect($member_price)->toBeLessThan($basic_price);

			// Then assert that the final price this customer will receive is the sale price
			expect($data['price'])->toBe($data['sale_price']);
		});

		test('it should set the sale price to empty if the member price is cheaper', function() {
			$response = TestUtils::$client->request('GET', 'products/117', [
				'auth' => TestUtils::getCustomerCredentials('TEST_MEMBER'),
				'headers' => [
					'Accept' => 'application/json',
				]
			]);

			$data = json_decode($response->getBody(), true);
			$basic_price = $data['price'];
			$member_price = array_find($data['meta_data'], function($meta) {
				return $meta['key'] === '_member_price';
			})['value'] ?? null;
			expect($member_price)->toBeLessThanOrEqual($basic_price);

			// Then assert that the sale price is empty for members
			expect($data['sale_price'])->toBeEmpty();
		});

		test('it should maintain the sale price for a member if the member price is higher', function() {
			$response = TestUtils::$client->request('GET', 'products/117', [
				'auth' => TestUtils::getCustomerCredentials('TEST_MEMBER_HIGHER_PRICE'),
				'headers' => [
					'Accept' => 'application/json',
				]
			]);

			$data = json_decode($response->getBody(), true);
			$basic_price = $data['price'];
			$member_price = array_find($data['meta_data'], function($meta) {
				return $meta['key'] === '_member_price';
			})['value'] ?? null;
			expect($member_price)->toBeGreaterThan($basic_price);

			// Then assert that the sale price is maintained for members
			expect($data['sale_price'])->toBe($data['price']);
		});
	});

	describe('Final price result', function() {

		describe('Member price is lower than regular price, no sale price is set', function() {

			test('non-member gets regular price', function() {

			});

			test('member gets member price', function() {

			});
		});

		// Edge case!
		// Assumption: Member prices should always be lower or equal to regular prices.
		// What happens if the site admin accidentally updates a regular price and causes it to be lower than the member price?
		describe('Member price is higher than regular price, no sale price is set', function() {

			test('non-member gets regular price', function() {

			});

			test('member gets regular price', function() {

			});
		});

		describe('Member price higher than sale price', function() {

			test('non-member gets sale price', function() {

			});

			test('member gets sale price', function() {

			});
		});

		describe('member price is lower than sale price', function() {


			test('non-member gets sale price', function() {

			});

			test('member gets member price', function() {

			});
		});
	});
});
