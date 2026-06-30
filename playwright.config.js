const { defineConfig, devices } = require( '@playwright/test' );

// E2E tests run against the local wp-env dev environment (bin/dev).
const BASE_URL = process.env.WP_BASE_URL || 'http://localhost:8888';

module.exports = defineConfig( {
	testDir: './tests/e2e',
	timeout: 90_000,
	expect: { timeout: 15_000 },
	fullyParallel: false,
	workers: 1,
	reporter: [ [ 'list' ], [ 'html', { open: 'never' } ] ],
	globalSetup: require.resolve( './tests/e2e/global-setup.js' ),
	use: {
		baseURL: BASE_URL,
		storageState: 'tests/e2e/.auth/admin.json',
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	projects: [
		{ name: 'chromium', use: { ...devices[ 'Desktop Chrome' ] } },
	],
} );
