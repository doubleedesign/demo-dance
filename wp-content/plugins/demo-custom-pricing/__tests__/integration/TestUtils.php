<?php
namespace Doubleedesign\Pricing\Tests\Integration;
use GuzzleHttp\Client;
use Dotenv\Dotenv;

// Load .env from current directory
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class TestUtils {
	public static Client $client;
	public static array $TEST_PRODUCT_IDS = [
		'no_sale_price' => [
			'no_member_price' => 127, // Bloch Performa
			'member_price_is_higher' => [
				'than_regular' => 221, // Capezio knit wrap
			],
			'member_price_is_lower' => [
				'than_regular' => 122, // Capezio legwarmers
			],
			'member_price_is_equal' => [
				'to_regular' => 217, // Ouch pouch
			]
		],
		// Note: This assumes regular price is never higher than sale price
		// WooCommerce admin doesn't allow you to do that anyway - you'd have to go out of you way to do it
		'with_sale_price' => [
			'no_member_price' => 182, // Energetiks Delta leotard
			'member_price_is_higher' => [
				'than_regular' => 199, // Energetiks Natalia skirt
				'than_sale' => 117, // Capezio duffle bag
			],
			'member_price_is_lower' => [
				'than_regular' => 64, // Bloch Raffine
				'than_sale' => 64, // Bloch Raffine
			],
			'member_price_is_equal' => [
				'to_regular' => 215, // Energetiks sewing kit
				'to_sale' => 181, // Energetiks keyring
			]
		],
	];

	static function setUp(): void {
		self::$client = new Client([
			'base_uri' => 'https://demo-dance.test/wp-json/wc/v3/',
		]);
	}

	static function getCustomerCredentials(string $userEnvPrefix): array {
		return [
			$_ENV[$userEnvPrefix . '_CUSTOMER_USERNAME'],
			$_ENV[$userEnvPrefix . '_CUSTOMER_PASSWORD'],
		];
	}
}
