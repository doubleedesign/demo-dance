<?php
namespace Doubleedesign\Pricing\Tests\Unit;
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
	 * @param $post_id
	 * @param $key
	 * @param $value
	 * @return void
	 */
	public static function mock_postmeta($post_id, $key, $value): void {
		WP_Mock::userFunction('get_post_meta', [
			'args' => [$post_id, $key, true],
			'return' => $value
		]);
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
