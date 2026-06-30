const { chromium } = require( '@playwright/test' );
const fs = require( 'fs' );
const path = require( 'path' );
const { login } = require( './helpers' );

// Log in once as admin and persist the session so every spec starts authenticated.
module.exports = async ( config ) => {
	const baseURL =
		config.projects[ 0 ].use.baseURL || 'http://localhost:8888';
	const authFile = path.join( __dirname, '.auth', 'admin.json' );
	fs.mkdirSync( path.dirname( authFile ), { recursive: true } );

	const browser = await chromium.launch();
	const page = await browser.newPage();
	await login( page, baseURL );
	await page.context().storageState( { path: authFile } );
	await browser.close();
};
