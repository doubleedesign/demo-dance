import { defineConfig, devices } from '@playwright/test';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

// Get the equivalent of __dirname in ES modules
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
	testDir: resolve(__dirname, '__tests__', 'e2e'),
	testMatch: '*.spec.ts',
	fullyParallel: true,
	timeout: 120000,
	expect: {
		timeout: 10000, // Assertion timeout
	},
	use: {
		//headless: true,
		viewport: { width: 1440, height: 1080},
		navigationTimeout: 120000,
		baseURL: 'https://demo-dance.test',
		ignoreHTTPSErrors: true,
	},
	projects: [
		{
			name: 'firefox',
			use: { ...devices['Desktop Firefox'] },
		},
	],
});
