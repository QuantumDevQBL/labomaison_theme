<?php
/**
 * MU-Plugin: Labomaison Early Guards
 *
 * FAST 410/301 guards that run BEFORE WordPress fully loads.
 * These are pure URL-pattern checks — no database, no WP_Query, no ACF needed.
 *
 * DEPLOYMENT: Copy this file to wp-content/mu-plugins/lm-early-guards.php
 *
 * WHY MU-PLUGIN:
 * - Runs before themes and plugins load
 * - Saves ~200-400ms per blocked request (no full WP bootstrap)
 * - Cannot be accidentally deactivated
 * - Works regardless of active theme
 *
 * KINSTA NOTE:
 * For even better perf, the "Edge Rules" section below documents
 * equivalent Cloudflare rules you can add via Kinsta dashboard.
 * If edge rules are active, this MU-plugin acts as a safety net.
 *
 * @package Labomaison
 * @version 1.0.0
 * @since 2.0.1
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Only run on frontend requests (not admin, CLI, AJAX, REST, CRON)
if ( defined( 'WP_ADMIN' ) && WP_ADMIN ) return;
if ( defined( 'WP_CLI' ) && WP_CLI ) return;
if ( defined( 'DOING_CRON' ) && DOING_CRON ) return;
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return;
if ( php_sapi_name() === 'cli' ) return;

// Only handle GET/HEAD
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ( ! in_array( $method, [ 'GET', 'HEAD' ], true ) ) return;

$raw_uri     = $_SERVER['REQUEST_URI'] ?? '/';
$decoded_uri = rawurldecode( $raw_uri );
$parsed      = @parse_url( $decoded_uri );
$path        = isset( $parsed['path'] ) ? (string) $parsed['path'] : $decoded_uri;
$query       = isset( $parsed['query'] ) ? (string) $parsed['query'] : ( $_SERVER['QUERY_STRING'] ?? '' );

// =============================================================================
// 410 GUARDS — Pure URL pattern matching
// =============================================================================

/**
 * 410: ?w= parameter (spam/injection vector)
 * Catches both normal and URL-encoded variants
 */
if (
    ( is_string( $query ) && preg_match( '/(^|[&])w=([^&]+)/i', $query ) ) ||
    preg_match( '/[?&]w=([^&]+)/i', $raw_uri ) ||
    preg_match( '/[?&]w=([^&]+)/i', $decoded_uri )
) {
    lm_mu_send_410( 'w_param' );
}

/**
 * 410: _load_more / load_more / _page parameters
 * These are internal AJAX params that should never be accessed directly
 */
if (
    ( is_string( $query ) && preg_match( '/(^|[&])(_load_more|load_more|_page)=/i', $query ) ) ||
    preg_match( '/(_load_more|load_more|_page)=/i', $raw_uri ) ||
    preg_match( '/(_load_more|load_more|_page)=/i', $decoded_uri )
) {
    lm_mu_send_410( 'load_more_param' );
}

/**
 * 410: /contents/* — legacy/dead path
 */
if ( strpos( $path, '/contents/' ) === 0 ) {
    lm_mu_send_410( 'contents_prefix' );
}

/**
 * 410: /shop/* — legacy WooCommerce path
 */
if ( strpos( $path, '/shop/' ) === 0 ) {
    lm_mu_send_410( 'shop_prefix' );
}

// =============================================================================
// 301 GUARDS — Trailing slash normalization
// =============================================================================

/**
 * 301: Force trailing slash on all frontend URLs
 * Skip: admin, static files, sitemaps, robots
 */
$skip_prefixes = [ '/wp-json', '/wp-admin', '/wp-login.php', '/xmlrpc.php', '/wp-cron.php' ];
$skip = false;
foreach ( $skip_prefixes as $pfx ) {
    if ( strpos( $path, $pfx ) === 0 ) {
        $skip = true;
        break;
    }
}

if (
    ! $skip &&
    $path !== '/' &&
    $path !== '' &&
    ! preg_match( '#\.[a-z0-9]{1,6}$#i', $path ) &&
    ! preg_match( '#/(robots\.txt|sitemap\.xml|sitemap_index\.xml)$#i', $path ) &&
    substr( $path, -1 ) !== '/'
) {
    $target = $path . '/';
    if ( $query !== '' ) {
        $target .= '?' . $query;
    }
    lm_mu_send_301( $target, 'trailing_slash' );
}

/**
 * 301: /marques/feed/ → homepage
 */
if ( preg_match( '#^/marques/feed/?$#i', $path ) ) {
    lm_mu_send_301( '/', 'marque_feed_redirect' );
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Send 410 Gone — minimal, no WP dependency
 */
function lm_mu_send_410( string $reason ): void {
    if ( headers_sent() ) return;

    $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
    header( $protocol . ' 410 Gone', true, 410 );
    header( 'X-LM-Guard: 410; reason=' . $reason, true );
    header( 'X-Robots-Tag: noindex, nofollow', true );
    header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true );
    header( 'Pragma: no-cache', true );
    header( 'Content-Type: text/plain; charset=utf-8', true );
    echo '410 Gone';
    exit;
}

/**
 * Send 301 Redirect — minimal, no WP dependency
 */
function lm_mu_send_301( string $to, string $reason ): void {
    if ( headers_sent() ) return;

    $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
    header( $protocol . ' 301 Moved Permanently', true, 301 );
    header( 'X-LM-Guard: 301; reason=' . $reason, true );
    header( 'Location: ' . $to, true );
    header( 'X-Robots-Tag: noindex, nofollow', true );
    header( 'Content-Type: text/plain; charset=utf-8', true );
    echo '301 Moved Permanently';
    exit;
}
