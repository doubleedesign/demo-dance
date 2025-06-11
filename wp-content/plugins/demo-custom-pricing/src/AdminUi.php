<?php
namespace Doubleedesign\Pricing;

class AdminUi {
	public function __construct() {
		add_action('edit_form_after_title', [$this,'setup_after_title_meta_boxes'], 100);
		add_action('do_meta_boxes', [$this, 'custom_meta_box_positions'], 10, 1);
	}

	/**
	 * Ensure there is an after_title context for meta boxes
	 * @return void
	 */
	function setup_after_title_meta_boxes(): void {
		global $post, $wp_meta_boxes;
		do_meta_boxes(get_current_screen(), 'after_title', $post);
	}

	/**
	 * Move the Product Data box to the top
	 * Note: The after_title context is custom and has to be run on the edit_form_after_title hook
	 * @param $screen
	 *
	 * @return void
	 */
	function custom_meta_box_positions($screen): void {
		if($screen === 'product') {
			add_meta_box('woocommerce-product-data', __('Product data', 'woocommerce'), 'WC_Meta_Box_Product_Data::output', 'product', 'after_title', 'high');
			remove_meta_box('woocommerce-product-data', 'product', 'normal'); // remove original AFTER adding new copy
		}
	}
}
