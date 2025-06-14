// Load .env file from this directory
import dotenv from 'dotenv';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
dotenv.config({ path: join(__dirname, '.env') });

export const TEST_PRODUCT_IDS = {
	'no_sale_price': {
		'no_member_price': 127, // Bloch Performa
		'member_price_is_higher': {
			'than_regular': 221, // Capezio knit wrap
		},
		'member_price_is_lower': {
			'than_regular': 122, // Capezio legwarmers
		},
		'member_price_is_equal': {
			'to_regular': 217, // Ouch pouch
		}
	},
	'with_sale_price': {
		'no_member_price': 182, // Energetiks Delta leotard
		'member_price_is_higher': {
			'than_regular': 199, // Energetiks Natalia skirt
			'than_sale': 117, // Capezio duffle bag
		},
		'member_price_is_lower': {
			'than_regular': 64, // Bloch Raffine
			'than_sale': 64, // Bloch Raffine
		},
		'member_price_is_equal': {
			'to_regular': 215, // Energetiks sewing kit
			'to_sale': 181, // Energetiks keyring
		}
	},
};

/**
 * Utility function to log a user in for Playwright tests.
 * Very basic for now, going to the login page and submitting the form.
 * TODO: Needs improvement to better handle different users, protect credentials, etc.
 * @param page - Playwright page object
 * @param userType - Type of user to log in as, matching the TEST_{userType}_CUSTOMER_ environment variables.
 */
export async function log_user_in(page, userType: string = 'member') {
	let username = process.env[`TEST_${userType.toUpperCase()}_CUSTOMER_USERNAME`];
	let password = process.env[`TEST_${userType.toUpperCase()}_CUSTOMER_PASSWORD`];

	if (!username || !password) {
		throw new Error(`Missing credentials for user type: ${userType}`);
	}

	// Navigate to establish context
	await page.goto('/my-account');
	const baseUrl = new URL(page.url()).origin;

	const loginResponse = await page.request.post('/wp-login.php', {
		form: {
			'log': username,
			'pwd': password,
			'wp-submit': 'Log In',
			'redirect_to': '/',
			'testcookie': '1'
		},
		ignoreHTTPSErrors: true
	});

	const setCookieHeaders = loginResponse.headersArray().filter(h => h.name.toLowerCase() === 'set-cookie');

	if (setCookieHeaders.length === 0) {
		throw new Error(`No cookies returned from login for user ${username}`);
	}

	const cookies = [];
	for (const header of setCookieHeaders) {
		const cookieString = header.value;
		const [nameValue] = cookieString.split(';');
		const [name, value] = nameValue.split('=');

		if (name && (name.includes('wordpress') || name.includes('wp-'))) {
			cookies.push({
				name: name.trim(),
				value: value ? value.trim() : '',
				url: baseUrl
			});
		}
	}

	if (cookies.length > 0) {
		await page.context().addCookies(cookies);
	}
	else {
		throw new Error(`No WordPress cookies found in login response`);
	}
}

export function price_string_to_number(price: string): number {
	return parseFloat(price.replace(/[^0-9.-]+/g, ''));
}
