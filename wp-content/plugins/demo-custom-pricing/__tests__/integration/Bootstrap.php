<?php
namespace Doubleedesign\Pricing\Tests\Integration;

class Bootstrap {

	static function setUp(): void {
		add_filter('woocommerce_rest_check_permissions', [self::class, 'grant_local_api_access'], 10, 4);
	}

	static function grant_local_api_access($permission, $context, $object_id, $post_type) {
		$is_local = defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local';
		if($is_local && $context === 'read') {
			return true;
		}

		return $permission;
	}
}
