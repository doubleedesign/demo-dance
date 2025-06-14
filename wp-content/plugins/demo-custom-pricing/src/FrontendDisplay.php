<?php
namespace Doubleedesign\Pricing;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\{EngineResolver, CompilerEngine};
use Illuminate\View\{Factory as BladeViewFactory, FileViewFinder};
use WC_Product;

class FrontendDisplay extends BladeViewFactory {

	public function __construct() {
		// Set up to use Blade templating for custom HTML output
		// Overkill? Yes. Tech/dependency debt? Also yes.
		// But this is an experimental project and that's what I felt like doing ¯\_(ツ)_/¯
		$bladeCacheDir = dirname(__DIR__, 1) . '\src\cache\\';
		$templatePartsDir = dirname(__DIR__, 1) . '\src\template-parts\\';
		$filesystem = new Filesystem();
		$compiler = new BladeCompiler($filesystem, $bladeCacheDir);
		$resolver = new EngineResolver();
		$resolver->register('blade', fn() => new CompilerEngine($compiler));
		$viewFinder = new FileViewFinder($filesystem, [$templatePartsDir]);
		parent::__construct($resolver, $viewFinder, new Dispatcher());

		add_filter('woocommerce_get_price_html', [$this, 'get_custom_price_display_html'], 100, 2);
		add_filter('woocommerce_sale_flash', [$this, 'get_custom_sale_badge_html'], 10, 2);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_default_css'], 20);
		add_action('enqueue_block_assets', [$this, 'enqueue_default_css'], 50);
	}

	public function get_custom_price_display_html($html, WC_Product $product): string {
		return self::make('price-display', [
			'currency_symbol'        => get_woocommerce_currency_symbol(),
			'regular_price'          => $product->get_regular_price(),
			'is_on_sale'             => $product->is_on_sale(),
			'sale_price'             => $product->get_sale_price(),
			'current_price'          => $product->get_price(),
			'customer_pricing_group' => $this->get_customer_pricing_group(),
		])->render();
	}

	public function get_custom_sale_badge_html($html, $product): string {
		return "<span class='onsale' data-testid='onsale-badge'>" . esc_html__('Sale!', 'woocommerce') . "</span>";
	}

	private function get_customer_pricing_group(): ?string {
		if(current_user_can('member')) {
			return 'member';
		}

		// TODO: Add other customer types here, e.g., dance school

		return null; // anonymous user or customer without role-specific pricing
	}

	/**
	 * Enqueue the default CSS for our custom output.
	 * @return void
	 */
	public function enqueue_default_css(): void {
		wp_enqueue_style('demo-custom-price-display', plugins_url('src/assets/style.css', __DIR__));
	}
}
