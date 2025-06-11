<?php
namespace Doubleedesign\Pricing;

class CustomRoles {

	public function __construct() {
        add_action('init', [$this, 'create_custom_roles']);
	}

	/**
	 * Create a custom user roles for customer types that will have custom pricing rules, based on WooCommerce customer role
	 */
	public function create_custom_roles(): void {
		$customer = get_role('customer');
		$customer_caps = $customer ? $customer->capabilities : [];

		// If WooCommerce customer role doesn't exist yet, use subscriber as fallback
		if (empty($customer_caps)) {
			$customer = get_role('subscriber');
			$customer_caps = $customer ? $customer->capabilities : [];
		}

		$roles = ['member', 'school'];
		foreach ($roles as $role) {
			// Add the role if it doesn't exist
			if (!get_role($role)) {
				add_role(
					$role,
					__(ucfirst($role), 'doubleedesign-pricing'),
					$customer_caps
				);
			}
			// If it exists, update existing role
			else {
				$existing_role = get_role($role);
				foreach ($customer_caps as $cap => $grant) {
					$existing_role->add_cap($cap, $grant);
				}
			}
		}
	}

}
