const { test, expect } = require( '@playwright/test' );
const { wpCli } = require( './helpers' );

const API_KEY = process.env.LEMONINK_API_KEY;

test.describe( 'LemonInk integration settings', () => {
	test.skip( ! API_KEY, 'LEMONINK_API_KEY not set' );

	test.beforeEach( async () => {
		// Start each run disconnected so the API-key field is shown.
		wpCli( [ 'option', 'update', 'woocommerce_lemonink_settings', '--format=json', '{"api_key":""}' ] );
	} );

	test( 'linking the store with a valid API key', async ( { page } ) => {
		await page.goto(
			'/wp-admin/admin.php?page=wc-settings&tab=integration&section=lemonink'
		);
		await page.getByLabel( 'API key' ).fill( API_KEY );
		await page.getByRole( 'button', { name: 'Save changes' } ).click();

		await expect(
			page.getByText( 'Your store is now properly linked with LemonInk.' )
		).toBeVisible();
	} );
} );
