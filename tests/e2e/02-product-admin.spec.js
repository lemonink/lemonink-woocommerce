const { test, expect } = require( '@playwright/test' );
const { wpCli } = require( './helpers' );

const API_KEY = process.env.LEMONINK_API_KEY;
const MASTER_ID = process.env.LEMONINK_MASTER_ID;

test.describe( 'Product watermark configuration', () => {
	test.skip( ! API_KEY || ! MASTER_ID, 'LEMONINK_API_KEY / LEMONINK_MASTER_ID not set' );

	let productId;

	test.beforeAll( () => {
		wpCli( [ 'option', 'update', 'woocommerce_lemonink_settings', '--format=json', `{"api_key":"${ API_KEY }"}` ] );
		productId = wpCli( [
			'wc', 'product', 'create', '--name=E2E Ebook', '--type=simple',
			'--virtual=1', '--downloadable=1', '--regular_price=10',
			'--status=draft', '--user=1', '--porcelain',
		] );
	} );

	test.afterAll( () => {
		if ( productId ) {
			wpCli( [ 'post', 'delete', productId, '--force' ] );
		}
	} );

	test( 'the LemonInk fields render and save without a "master not found" error', async ( { page } ) => {
		await page.goto( `/wp-admin/post.php?post=${ productId }&action=edit` );

		// Open the Downloadable/LemonInk options and configure them.
		const watermark = page.getByLabel( 'Watermark downloads using LemonInk' );
		await expect( watermark ).toBeVisible();
		await watermark.check();
		await page.getByLabel( 'Master file ID' ).fill( MASTER_ID );

		await page.getByRole( 'button', { name: 'Update', exact: true } ).click();
		await page.waitForLoadState( 'networkidle' );

		await expect(
			page.getByText( `The master file with ID ${ MASTER_ID } does not exist on the server.` )
		).toHaveCount( 0 );
	} );
} );
