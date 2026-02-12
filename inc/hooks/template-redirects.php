<?php
/**
 * Template Redirects
 *
 * HTTP redirects, 410 responses, and URL normalization.
 * CRITICAL: Hook priorities must be preserved exactly.
 *
 * Note: The core guard functions (lm_guard_send_410, lm_guard_send_301,
 * lm_guard_early_410, lm_guard_redirect_over_max_pagination, lm_guard_brand_unused_410)
 * are in utilities/security.php as they are foundational utilities.
 *
 * @package Labomaison
 * @subpackage Hooks
 * @since 2.0.0
 *
 * Functions in this file:
 * - Trailing slash redirect [priority 0] (skipped if MU-plugin handles it)
 * - Marque feed redirect
 * - custom_pre_handle_404()
 * - Canonical hardening for redacteur
 *
 * Dependencies: utilities/security.php
 * Load Priority: 4
 * Risk Level: HIGH
 *
 * Migrated from: functions.php L513-545, L995-1017, L2859-2907, L3278-3299
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// MARQUE FEED & PAGINATION REDIRECTS
// =============================================================================

/**
 * Redirect marque feeds and excessive pagination
 *
 * - Redirects /marques/ feed URLs to homepage
 * - Handles pagination over 200 pages (commented out)
 *
 * @since 2.0.0
 */
if (!function_exists('lm_seo_core_active') || !lm_seo_core_active('redirects')) {
    add_action('template_redirect', function() {
        // Check if the current URL is a feed for the 'marques' custom post type
        if (is_feed() && get_query_var('post_type') === 'marque') {
            // Perform the redirect to the homepage
            wp_redirect(home_url(), 301); // 301 indicates a permanent redirect
            exit;
        }

        if (is_paged()) {
            $paged = get_query_var('paged');
            $redirect = false;

            // Redirection pour les pages de pagination générales
            if ($paged > 200) {
                $redirect = true;
            }

            // Redirection pour les catégories spécifiques
            if (is_category() && $paged > 200) {
                $redirect = true;
            }

            // Redirection pour les archives spéciales
            if ((is_tax('promotion') || is_tax('soldes')) && $paged > 200) {
                $redirect = true;
            }

            /*if ($redirect) {
                wp_redirect(get_pagenum_link(1), 301); // Redirige vers la première page
                exit;
            }*/
        }
    });
}

// =============================================================================
// TRAILING SLASH NORMALIZATION
// =============================================================================

/**
 * Force trailing slash on all frontend URLs
 *
 * Redirects URLs without trailing slash to their slashed versions.
 * Priority 0 to run before other redirects.
 *
 * IMPORTANT:
 * - After ajout: purge cache (Kinsta + Cloudflare) sinon tu verras encore des 200.
 *
 * @since 2.0.0
 */
add_action('template_redirect', function () {

    // Skip if MU-plugin already handles trailing slash
    if ( function_exists( 'lm_mu_send_301' ) ) return;

    // Front only + sécurité
    if (is_admin()) return;
    if (defined('WP_CLI') && WP_CLI) return;
    if (defined('DOING_AJAX') && DOING_AJAX) return;
    if (defined('DOING_CRON') && DOING_CRON) return;
    if (defined('REST_REQUEST') && REST_REQUEST) return;

    // Ne gérer que GET/HEAD (évite d'impacter POST)
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, ['GET', 'HEAD'], true)) return;

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if ($uri === '/' || $uri === '') return;

    $parts = parse_url($uri);
    $path  = $parts['path'] ?? $uri;
    $query = $parts['query'] ?? '';

    // Ignore endpoints techniques
    $skip_prefixes = [
        '/wp-json',
        '/wp-admin',
        '/wp-login.php',
        '/xmlrpc.php',
    ];
    foreach ($skip_prefixes as $pfx) {
        if (strpos($path, $pfx) === 0) return;
    }

    // Ignore fichiers (extensions) + sitemaps/robots
    if (preg_match('#\.[a-z0-9]{1,6}$#i', $path)) return; // ex: .png .js .xml ...
    if (preg_match('#/(robots\.txt|sitemap\.xml|sitemap_index\.xml)$#i', $path)) return;

    // Si déjà avec slash => ok
    if (substr($path, -1) === '/') return;

    // Cible: ajoute slash + conserve query string
    $target = $path . '/';
    if ($query !== '') {
        $target .= '?' . $query;
    }

    // 301
    wp_safe_redirect($target, 301);
    exit;

}, 0);

// =============================================================================
// 404 HANDLING
// =============================================================================

/**
 * Handle 404 for actualites pagination
 *
 * Redirects /actualites/page/N/ to /actualites/ when N exceeds max pages.
 *
 * @since 2.0.0
 * @param bool $false Whether to preempt the 404
 * @param WP_Query $wp_query The WP_Query object
 * @return bool
 */
if (!function_exists('lm_seo_core_active') || !lm_seo_core_active('redirects')) {
    add_filter('pre_handle_404', 'custom_pre_handle_404', 10, 2);
    function custom_pre_handle_404($false, $wp_query) {
        $current_url = $_SERVER['REQUEST_URI'];

        // Vérifier les pages de pagination d'actualités
        if (preg_match('/\/actualites\/page\/(\d+)\/?/', $current_url, $matches)) {
            $page_number = (int) $matches[1];

            if ($page_number > 0) {
                $posts_per_page = get_option('posts_per_page');
                $total_posts = wp_count_posts()->publish;
                $max_pages = ceil($total_posts / $posts_per_page);

                // Rediriger si la page demandée dépasse le nombre maximum de pages
                if ($page_number > $max_pages) {
                    wp_redirect(home_url('/actualites/'), 301);
                    exit;
                }
            }
        }

        return $false;
    }
}

// =============================================================================
// CANONICAL URL HARDENING
// =============================================================================

/**
 * Inject canonical tag if missing on /redacteur/ pages
 *
 * Uses output buffering to add canonical link tag to head
 * if one doesn't already exist.
 *
 * @since 2.0.0
 */
add_action('template_redirect', function () {
    if (!lm_can_is_redacteur_scope()) return;

    ob_start(function ($html) {

        // Si déjà un canonical, on ne touche pas (anti-doublon)
        if (stripos($html, 'rel="canonical"') !== false || stripos($html, "rel='canonical'") !== false) {
            return $html;
        }

        $canonical = esc_url(lm_can_current_url_clean());
        $tag = "<link rel=\"canonical\" href=\"{$canonical}\" />\n";

        // Injecte juste avant </head>
        if (stripos($html, '</head>') !== false) {
            $html = str_ireplace('</head>', $tag . '</head>', $html);
        }

        return $html;
    });
});
