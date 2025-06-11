<?php
namespace Doubleedesign\Pricing\Tests\Unit;
use Mockery;
use Mockery\MockInterface;

class MockProducts {
	public static function create(array $attributes = []): MockInterface {
		$defaults = [
			'id'            => 123,
			'name'          => 'Test Product',
			'regular_price' => 25.00,
			'sale_price'    => null,
			'type'          => 'simple',
			'stock_status'  => 'instock',
		];

		$attrs = array_merge($defaults, $attributes);

		// Create mock object
		$product = Mockery::mock('WC_Product');

		// Add results for the methods that are called in the code being tested
		$product->shouldReceive('get_id')->andReturn($attrs['id']);

		return $product;
	}
}
