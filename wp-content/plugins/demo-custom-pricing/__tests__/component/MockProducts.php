<?php
namespace Doubleedesign\Pricing\Tests\Component;
use Mockery;
use Mockery\MockInterface;

class MockProducts {
	public static function create(array $attributes = []): MockInterface {
		$defaults = [
			'id'            => 123,
			'name'          => 'Test Product',
			'regular_price' => 25.00,
			'sale_price'    => null,
			'price'         => 25.00,
			'type'          => 'simple',
			'stock_status'  => 'instock',
			'is_on_sale'    => false,
		];

		$attrs = array_merge($defaults, $attributes);

		// Create mock object
		$product = Mockery::mock('WC_Product');

		// Add methods that are expected to be called, with return values as appropriate
		// Important note: For component tests, we pass the expected values into the mock via the $attributes directly
		$product->shouldReceive('get_price')->andReturn($attrs['price']);
		$product->shouldReceive('get_regular_price')->andReturn($attrs['regular_price']);
		$product->shouldReceive('get_sale_price')->andReturn($attrs['sale_price']);
		$product->shouldReceive('is_on_sale')->andReturn(!empty($attrs['sale_price'] || $attrs['is_on_sale']));

		return $product;
	}
}
