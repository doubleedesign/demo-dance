<?php
namespace Doubleedesign\Pricing\Tests\Unit;
use Mockery;
use Mockery\MockInterface;
use WP_Mock;

class MockUtils {

	/**
	 * Shortcut function to mock the current_user_can function
	 * to set the value of the given role or capability for the user in the test context
	 * @param string $role_or_capability
	 * @param bool $user_has
	 * @return void
	 */
	public static function mock_user_role_or_cap(string $role_or_capability, bool $user_has): void {
		WP_Mock::userFunction('current_user_can', [
			'args' => [$role_or_capability],
			'return' => $user_has
		]);
	}

	/**
	 * Shortcut function to mock the get_post_meta function to return the value for the given key
	 * Returns a spy so we can assert stuff about the function call itself
	 * @param $post_id
	 * @param $key
	 * @param $value
	 * @return MockInterface
	 */
	public static function mock_postmeta($post_id, $key, $value): MockInterface {
		$postmetaSpy = Mockery::spy(function($id, $meta_key, $single = true) use ($post_id, $key, $value) {
			if ($meta_key === $key && $id === $post_id) {
				return $value;
			}
			return null; // Return null for any other key
		});
		WP_Mock::alias('get_post_meta', $postmetaSpy);

		return $postmetaSpy;
	}

	/**
	 * Mock any functions that are run to ensure the code being tested is only run in the front-end context
	 * @return void
	 */
	public static function mock_front_end_context(): void {
		//when('is_admin')->justReturn(false); // Alternative using BrainMonkey
		WP_Mock::userFunction('is_admin')->andReturn(false);
	}
}
