const { execFileSync } = require( 'child_process' );

const ADMIN_USER = 'admin';
const ADMIN_PASSWORD = 'password';

// Run wp-cli inside the wp-env container and return trimmed stdout.
function wpCli( args ) {
	const out = execFileSync(
		'npx',
		[ 'wp-env', 'run', 'cli', 'wp', ...args ],
		{ encoding: 'utf8' }
	);
	// wp-env prefixes a "Starting '...'" line and appends a "Ran ... in" line
	// around the command's real output; keep only the wp-cli stdout.
	return out
		.split( '\n' )
		.filter(
			( l ) =>
				! /^\s*(ℹ|✔|Starting '|Ran )/.test( l ) &&
				! /zoxide|configuration issue|ensure that|persists|filing an|Disable this/.test( l )
		)
		.join( '\n' )
		.trim();
}

// Log into wp-admin through the UI (used by global setup to persist auth state).
async function login( page, baseURL ) {
	await page.goto( `${ baseURL }/wp-login.php` );
	await page.fill( '#user_login', ADMIN_USER );
	await page.fill( '#user_pass', ADMIN_PASSWORD );
	await page.click( '#wp-submit' );
	await page.waitForURL( /wp-admin/ );
}

module.exports = { wpCli, login, ADMIN_USER, ADMIN_PASSWORD };
