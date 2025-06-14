{{-- TODO: This has a lot of repetition, could it be refactored without losing too much clarity? --}}
<div class="woocommerce-price">
	@if ($is_on_sale && $sale_price)
		<del aria-label="{{ __('Regular price', 'demo-custom-pricing') }}">
			<span class="woocommerce-price__currency-symbol">{{ html_entity_decode($currency_symbol) }}</span>
			<span class="woocommerce-price__amount">{{ $regular_price }}</span>
		</del>
		<ins aria-label="{{ __('Current sale price', 'demo-custom-pricing') }}">
			<span class="woocommerce-price__currency-symbol">{{ html_entity_decode($currency_symbol) }}</span>
			<span class="woocommerce-price__amount">{{ $sale_price }}</span>
		</ins>
	@elseif(!empty($customer_pricing_group) && $regular_price !== $current_price)
		<del aria-label="{{ __('Regular price', 'demo-custom-pricing') }}">
			<span class="woocommerce-price__currency-symbol">{{ html_entity_decode($currency_symbol) }}</span>
			<span class="woocommerce-price__amount">{{ $regular_price }}</span>
		</del>
		{{-- aria-label is here even though there is also a specific label element for accessibility tree and screen reader announcement consistency --}}
		<ins aria-label="{{ __(sprintf("%s price", ucfirst($customer_pricing_group)), 'demo-custom-pricing') }}">
			<span class="woocommerce-price__currency-symbol">{{ html_entity_decode($currency_symbol) }}</span>
			<span class="woocommerce-price__amount">{{ $current_price }}</span>
		</ins>
	@else
		<span aria-label="{{__('Current price', 'demo-custom-pricing')}}">
			<span class="woocommerce-price__currency-symbol">{{ html_entity_decode($currency_symbol) }}</span>
			<span class="woocommerce-price__amount">{{ $current_price }}</span>
		</span>
	@endif

	@if($customer_pricing_group && !$is_on_sale && ($regular_price != $current_price))
		{{-- aria-hidden here is so that screen reader users don't get a double announcement --}}
        <span class="woocommerce-price__custom-badge woocommerce-price__custom-badge--{{ $customer_pricing_group }}" aria-hidden="true">
            {{ __(sprintf("%s price", $customer_pricing_group), 'demo-custom-pricing') }}
        </span>
    @endif
</div>
