import { TEST_PRODUCT_IDS, log_user_in, price_string_to_number } from './test-utils.ts';
import { test, expect } from '@playwright/test';

const singleProductPriceSelector = '[data-is-descendent-of-single-product-template="true"] .woocommerce-price';

type ItemPricingTestCaseOptions = {
	shouldBeOnSale?: boolean;
	// Other roles with custom prices should be added here and tested for, with first letter capitalised as per the expected aria-label
	userRole?: 'Member' | 'School';
}

/**
 * Utility function to run a single item pricing test case with all common steps/assertions in one place.
 * @param page - Playwright page object
 * @param ItemPricingTestCaseOptions options
 */
async function run_item_pricing_e2e_test_case(page, { shouldBeOnSale, userRole = null }: ItemPricingTestCaseOptions) {
	const priceElement = page.locator(singleProductPriceSelector);
	let regPrice = null;
	let salePrice = null;
	let customPrice = null;
	let singlePrice = null;

	// Assert whether sale badge is present on the page for the single product (not related products)
	const saleBadge = page.locator('.wp-block-woocommerce-product-image-gallery [data-testid="onsale-badge"]');
	if(shouldBeOnSale) {
		await expect(saleBadge).toBeVisible();
	}
	else {
		await expect(saleBadge).toHaveCount(0);
	}


	if (shouldBeOnSale) {
		regPrice = priceElement.getByLabel('Regular price');
		await expect(regPrice).toBeVisible();
		await expect(regPrice).toHaveRole('deletion');

		salePrice = priceElement.getByLabel('Current sale price');
		await expect(salePrice).toBeVisible();
		await expect(salePrice).toHaveRole('insertion');
	} else {
		await expect(priceElement.getByLabel('Current sale price')).toHaveCount(0);
	}

	if (userRole) {
		if(!shouldBeOnSale) {
			await expect(priceElement.locator('.woocommerce-price__custom-badge')).toBeVisible();
			customPrice = priceElement.getByLabel(`${userRole} price`);
			await expect(customPrice).toBeVisible();
			await expect(customPrice).toHaveRole('insertion');
		}
	}
	else {
		await expect(priceElement.locator('.woocommerce-price__custom-badge')).toHaveCount(0);
		await expect(priceElement.getByLabel(`${userRole} price`)).toHaveCount(0);
	}

	// If there is no sale price and no custom price, we should only see the regular price and without any custom badge
	if (!shouldBeOnSale && !userRole) {
		singlePrice = priceElement.getByLabel('Current price');
		await expect(singlePrice).toBeVisible();
	}

	return { page, regPrice, salePrice, customPrice, singlePrice };
}

/**
 * Test cases for item pricing on the single product page.
 * These are very simple for demo purposes and comparability to the integration tests.
 * When the bulk pricing feature is implemented, the real power of e2e tests will be shown because we will be able to test the entire flow
 * of a user adding the bulk quanity to the cart and seeing the correct price in the cart,
 * altering the cart and seeing the price update correctly, etc.
 *
 * Note: For simplicity and efficiency, these test cases use an anonymous user for the non-member case.
 * For completeness, it may be ideal to test both an anonymous user and a logged-in non-member to protect against future regressions
 * if the code changes (e.g., someone uses is_user_logged_in() instead of a role check by default.
 */
test.describe('Item pricing (e2e)', () => {

	test.describe('Sale price handling', () => {

		// Note: Unless we modify the front-end output to show member prices to non-members,
		// we can't actually test anything about the member price in this case like we can with the integration test
		test('it should maintain the sale price if the member price is lower but the user is not a member', async ({ page }) => {
			await page.goto('/?p=' + TEST_PRODUCT_IDS['with_sale_price']['member_price_is_lower']['than_sale']);

			const result = await run_item_pricing_e2e_test_case(page, {
				shouldBeOnSale: true,
			});

			const regAmount = price_string_to_number(await result.regPrice.textContent());
			const saleAmount = price_string_to_number(await result.salePrice.textContent());

			expect(saleAmount).toBeLessThan(regAmount);

			// await page.pause(); // for debugging - stop the browser from closing when running with --headed --debug
		});

		test('it should not show the sale price to a member if the member price is lower', async ({ page }) => {
			await log_user_in(page);
			await page.goto('/?p=' + TEST_PRODUCT_IDS['with_sale_price']['member_price_is_lower']['than_sale']);

			const result = await run_item_pricing_e2e_test_case(page, {
				shouldBeOnSale: false,
				userRole: 'Member'
			});

			expect(result.salePrice).toBeNull();
		});

		test('it should maintain the sale price for a member if the member price is higher', async ({ page }) => {
			await log_user_in(page);
			await page.goto('/?p=' + TEST_PRODUCT_IDS['with_sale_price']['member_price_is_higher']['than_sale']);

			await run_item_pricing_e2e_test_case(page, {
				shouldBeOnSale: true,
				userRole: 'Member'
			});
		});

		test('it should maintain the member price for a member if it is equal to the sale price', async ({ page }) => {
			await log_user_in(page);
			await page.goto('/?p=' + TEST_PRODUCT_IDS['with_sale_price']['member_price_is_equal']['to_sale']);

			const result = await run_item_pricing_e2e_test_case(page, {
				shouldBeOnSale: false,
				userRole: 'Member'
			});

			await expect(result.customPrice).toBeVisible();
			expect(result.salePrice).toBeNull();
		});
	});

	test.describe('Final price result', () => {

		test.describe('Member price is lower than regular price, no sale price is set', () => {

			test('non-member gets regular price', async ({ page }) => {
				await page.goto('/?p=' + TEST_PRODUCT_IDS['no_sale_price']['member_price_is_lower']['than_regular']);

				const result = await run_item_pricing_e2e_test_case(page, {
					shouldBeOnSale: false
				});

				expect(result.singlePrice).not.toBeNull();
				expect(result.customPrice).toBeNull();
			});

			test('member gets member price', async ({ page }) => {
				await log_user_in(page);
				await page.goto('/?p=' + TEST_PRODUCT_IDS['no_sale_price']['member_price_is_lower']['than_regular']);

				const result = await run_item_pricing_e2e_test_case(page, {
					shouldBeOnSale: false,
					userRole: 'Member'
				});

				expect(result.customPrice).not.toBeNull();
			});
		});

		test.describe('Member price higher than sale price', () => {

			test('non-member gets sale price', async ({ page }) => {
				await page.goto('/?p=' + TEST_PRODUCT_IDS['with_sale_price']['member_price_is_higher']['than_sale']);

				const result = await run_item_pricing_e2e_test_case(page, {
					shouldBeOnSale: true
				});

				expect(result.salePrice).not.toBeNull();
			});

			test('member gets sale price', async ({ page }) => {
				await log_user_in(page);

				await page.goto('/?p=' + TEST_PRODUCT_IDS['with_sale_price']['member_price_is_higher']['than_sale']);

				const result = await run_item_pricing_e2e_test_case(page, {
					shouldBeOnSale: true,
					userRole: 'Member'
				});

				expect(result.salePrice).not.toBeNull();
			});
		});

		test.describe('member price is lower than sale price', () => {

			test('non-member gets sale price', async ({ page }) => {
				await page.goto('/?p=' + TEST_PRODUCT_IDS['with_sale_price']['member_price_is_lower']['than_sale']);

				const result = await run_item_pricing_e2e_test_case(page, {
					shouldBeOnSale: true
				});

				expect(result.salePrice).not.toBeNull();
			});

			test('member gets member price', async ({ page }) => {
				await log_user_in(page);
				await page.goto('/?p=' + TEST_PRODUCT_IDS['with_sale_price']['member_price_is_lower']['than_sale']);

				const salePrice = page.locator(singleProductPriceSelector).getByLabel('Current sale price');
				await expect(salePrice).toHaveCount(0);

				const memberPrice = page.locator(singleProductPriceSelector).getByLabel('Member price');
				await expect(memberPrice).toBeVisible();

				// Also assert that the "Member price" badge is visible
				await expect(page.locator('.woocommerce-price__custom-badge')).toBeVisible();

				// And that the "on sale" badge is not visible
				const saleBadge = page.locator('.wp-block-woocommerce-product-image-gallery [data-testid="onsale-badge"]');
				await expect(saleBadge).toHaveCount(0);
			});
		});
	});
});
