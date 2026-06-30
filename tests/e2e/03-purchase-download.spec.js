const { test, expect, request } = require( '@playwright/test' );
const { wpCli } = require( './helpers' );

const API_KEY = process.env.LEMONINK_API_KEY;
const MASTER_ID = process.env.LEMONINK_MASTER_ID;
const BUYER_EMAIL = 'buyer@example.com';

// Exercises the purchase side: completing an order for a watermarked product
// must create a LemonInk transaction, and the customer's download link must be
// handed off to LemonInk (redirect) rather than served from local disk.
test.describe( 'Purchase and watermarked download', () => {
	test.skip( ! API_KEY || ! MASTER_ID, 'LEMONINK_API_KEY / LEMONINK_MASTER_ID not set' );

	let productId, orderId, downloadId, orderKey;

	test.beforeAll( () => {
		wpCli( [ 'option', 'update', 'woocommerce_lemonink_settings', '--format=json', `{"api_key":"${ API_KEY }"}` ] );

		productId = wpCli( [
			'wc', 'product', 'create', '--name=E2E Purchase Ebook', '--type=simple',
			'--virtual=1', '--downloadable=1', '--regular_price=10',
			'--status=publish', '--user=1', '--porcelain',
		] );
		wpCli( [ 'post', 'meta', 'update', productId, '_li_lemoninkable', 'yes' ] );
		wpCli( [ 'post', 'meta', 'update', productId, '_li_master_id', MASTER_ID ] );
		wpCli( [ 'eval-file', 'wp-content/lemonink-dev/generate-files.php', productId ] );

		downloadId = wpCli( [ 'eval', `echo array_key_first( wc_get_product( ${ productId } )->get_downloads() );` ] );

		// Completing the order grants download access, firing the transaction hook.
		const created = wpCli( [
			'eval-file', 'wp-content/lemonink-dev/create-order.php', productId, BUYER_EMAIL,
		] );
		[ orderId, orderKey ] = created.split( /\s+/ );
	} );

	test.afterAll( () => {
		if ( orderId ) wpCli( [ 'post', 'delete', orderId, '--force' ] );
		if ( productId ) wpCli( [ 'post', 'delete', productId, '--force' ] );
	} );

	test( 'completing the order creates a LemonInk transaction', () => {
		const transactionId = wpCli( [
			'eval',
			`echo wc_get_order( ${ orderId } )->get_meta( '_li_product_${ productId }_transaction_id' );`,
		] );
		expect( transactionId.length ).toBeGreaterThan( 0 );
	} );

	test( 'the download link is handed off to LemonInk (redirect, not local file)', async ( { baseURL } ) => {
		const url = wpCli( [
			'eval',
			`echo add_query_arg( array( 'download_file' => ${ productId }, 'order' => '${ orderKey }', 'email' => rawurlencode( '${ BUYER_EMAIL }' ), 'key' => '${ downloadId }' ), home_url( '/' ) );`,
		] );

		const ctx = await request.newContext( { baseURL } );
		const res = await ctx.get( url, { maxRedirects: 0 } );
		// A LemonInk-managed download responds with a redirect to the remote
		// watermarked file rather than a 200 local-file stream.
		expect( res.status() ).toBeGreaterThanOrEqual( 300 );
		expect( res.status() ).toBeLessThan( 400 );
		expect( res.headers()[ 'location' ] || '' ).toMatch( /lemonink/i );
		await ctx.dispose();
	} );
} );
