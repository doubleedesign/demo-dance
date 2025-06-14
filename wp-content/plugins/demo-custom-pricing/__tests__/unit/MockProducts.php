<?php
namespace Doubleedesign\Pricing\Tests\Unit;
use Mockery;
use Mockery\MockInterface;
use WP_Mock;

class MockProducts {
	public static function create(array $attributes = []): MockInterface {
		$defaults = [
			'id'            => 123,
			'name'          => 'Test Product',
			'regular_price' => 25.00,
			'sale_price'    => null,
			'type'          => 'simple',
			'stock_status'  => 'instock',
			'is_on_sale'    => false,
		];

		$attrs = array_merge($defaults, $attributes);

		// Create mock object
		$product = Mockery::mock('WC_Product');

		// Add methods that are expected to be called, with return values as appropriate
		WP_Mock::userFunction('get_post_meta', [
			'args' => [$attrs['id'], '_regular_price', true],
			'return' => $attrs['regular_price'],
		]);
		WP_Mock::userFunction('get_post_meta', [
			'args' => [$attrs['id'], '_sale_price', true],
			'return' => $attrs['sale_price'],
		]);
		$product->shouldReceive('get_id')->andReturn($attrs['id']);
		$product->shouldReceive('is_type')->with('variable')->andReturn($attrs['type'] === 'variable');
		$product->shouldReceive('is_type')->with('simple')->andReturn($attrs['type'] === 'simple');
		$product->shouldReceive('get_regular_price')->andReturn($attrs['regular_price']);
		$product->shouldReceive('get_sale_price')->andReturn($attrs['sale_price']);
		$product->shouldReceive('is_on_sale')->andReturn(!empty($attrs['sale_price'] || $attrs['is_on_sale']));
		$product->shouldReceive('set_regular_price');
		$product->shouldReceive('set_sale_price');
		$product->shouldReceive('set_price');

		return $product;
	}
}
