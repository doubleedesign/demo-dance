<?php
namespace Doubleedesign\Pricing;

class PricingFields {

	public function __construct() {
		add_action('admin_head', [$this, 'admin_css_hacks'], 10, 1);
		add_action('admin_head', [$this, 'admin_js_hacks'], 10, 1);

		// Enable use of simple pricing fields for variable products, syncing them to variations
		add_action('woocommerce_process_product_meta_variable', [$this, 'use_simple_prices_for_variable_products'], 25, 1);
		add_action('woocommerce_product_read', [$this, 'really_use_simple_prices_for_variable_products'], 25, 2);

		// Custom field addition and saving
		$this->create_custom_price_field('_member_price', 'Member price', 'Regular price for members');
		$this->create_custom_price_field('_bulk_price', 'Bulk price', 'Price for customers eligible for bulk order discount');
	}

	public function admin_css_hacks(): void {
		echo <<<HTML
			<style>
				#woocommerce-product-data .options_group.pricing {
					/* Show simple pricing fields for all products */
					display: block !important;	
				}
			</style>
		HTML;
	}

	public function admin_js_hacks(): void {
		echo <<<HTML
			<script>
				// Disable price fields in variations when editing, because the simple price fields will overwrite them on save
				document.addEventListener('click', function(event) {
					if(event.target.classList.contains('edit_variation') && event.target.closest('.woocommerce_variation')) {
						const priceSection = event.target.closest('.woocommerce_variation').querySelector('.variable_pricing');
						const fields = priceSection.querySelectorAll('.wc_input_price');
						fields.forEach(field => {
							field.disabled = true;
						});
					}
				})
			</script>
		HTML;
	}

	function use_simple_prices_for_variable_products($post_id): void {
		if(get_post_type($post_id) !== 'product') return;

		$product = wc_get_product($post_id);
		if(!$product || !$product->is_type('variable')) return;
		$variations = $product->get_children();

		$regular_price = isset($_POST['_regular_price']) ? wc_clean(wp_unslash($_POST['_regular_price'])) : '';
		$sale_price = isset($_POST['_sale_price']) ? wc_clean(wp_unslash($_POST['_sale_price'])) : '';

		if (!empty($regular_price)) {
			$product->set_regular_price($regular_price);
			foreach ($variations as $variation_id) {
				update_post_meta($variation_id, '_regular_price', $regular_price);
				wc_delete_product_transients($variation_id);
			}
		}

		if (!empty($sale_price)) {
			$product->set_sale_price($sale_price);
			foreach ($variations as $variation_id) {
				update_post_meta($variation_id, '_sale_price', $sale_price);
				wc_delete_product_transients($variation_id);
			}
		}

		$product->save();
		wc_delete_product_transients($post_id);
	}

	/**
	 * While use_simple_prices_for_variable_products saves the meta fields correctly for a variable product,
	 * they get overwritten to empty before the price is returned in the admin and REST API (they stay in the db fine).
	 * (See read_product_data() in WC_Product_Variable_Data_Store_CPT)
	 * This method, when run on the woocommerce_product_read filter, uses that correctly stored meta to
	 * put it back before being returned to the admin, ensuring the stored values are displayed in the input fields.
	 *
	 * @param $post_id
	 * @param $product
	 * @return void
	 */
	function really_use_simple_prices_for_variable_products($post_id, $product): void {
		if($product->is_type('variable')) {
			$product->set_regular_price(get_post_meta($post_id, '_regular_price', true));
			$product->set_sale_price(get_post_meta($post_id, '_sale_price', true));
		}
	}

	/**
	 * Common method to create custom price fields, setting up the admin display and saving
	 * @param string $field_key
	 * @param string $label
	 * @param string $tooltip
	 * @return void
	 */
	private function create_custom_price_field(string $field_key, string $label, string $tooltip): void {
		add_action('woocommerce_product_options_general_product_data', function() use ($tooltip, $label, $field_key) {
			$this->display_custom_price_field($field_key, $label, $tooltip);
		});
		add_action('woocommerce_process_product_meta', function($post_id) use ($field_key) {
			$this->save_custom_price_field($post_id, $field_key);
		});
	}

	/**
	 * Inner method to handle the admin display of a custom price field
	 * @param string $field_key
	 * @param string $label
	 * @param string $tooltip
	 * @return void
	 */
	private function display_custom_price_field(string $field_key, string $label, string $tooltip): void {
		global $post, $woocommerce;

		woocommerce_wp_text_input(
			array(
				'id'          => $field_key,
				'label'       => __($label, 'woocommerce'),
				'placeholder' => '',
				'desc_tip'    => true,
				'description' => __($tooltip, 'woocommerce'),
				'type'        => 'text',
				'data_type'   => 'price',
				'class'       => 'wc_input_price short',
			)
		);
	}

	/**
	 * Inner method to handle saving a custom price field
	 * @param string $post_id
	 * @param string $field_key
	 * @return void
	 */
	private function save_custom_price_field(string $post_id, string $field_key): void {
		$price = isset($_POST[$field_key]) ? wc_clean($_POST[$field_key]) : '';
		update_post_meta($post_id, $field_key, $price);
	}
}
