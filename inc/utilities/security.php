<?php
/**
 * Security Functions
 *
 * HTTP response helpers and URL validation functions.
 * Critical for 410/301 guard system.
 *
 * @package Labomaison
 * @subpackage Utilities
 * @since 2.0.0
 *
 * Functions in this file:
 * - lm_guard_send_410()
 * - lm_guard_send_301()
 * - lm_guard_early_410()
 * - lm_guard_redirect_over_max_pagination()
 * - lm_guard_brand_unused_410()
 * - lm_can_is_redacteur_scope()
 * - lm_can_current_url_clean()
 *
 * Dependencies: None
 * Load Priority: 1 (Must load first)
 * Risk Level: CRITICAL
 *
 * NOTE: If the MU-plugin mu-plugins/lm-early-guards.php is active,
 * the URL-pattern guards (lm_guard_early_410, trailing slash) already
 * ran before this file loads. The function_exists() guards prevent
 * double execution. The DB-dependent guards (pagination, brand 410)
 * can only run here since they need WP_Query.
 *
 * Migrated from: functions.php L1669-1861, L3243-3262
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// HTTP RESPONSE HELPERS
// =============================================================================

/**
 * Send a 410 Gone response and exit
 *
 * Used by guard functions to permanently reject requests
 * to deprecated or invalid URLs.
 *
 * @since 2.0.0
 * @param string $reason Reason code for logging/debugging
 * @return void
 */
if (!function_exists('lm_guard_send_410')) {
    function lm_guard_send_410(string $reason): void {
        header('X-LM-Guard: 410; reason=' . $reason);
        header('X-Robots-Tag: noindex, nofollow', true);
        if (function_exists('nocache_headers')) {
            nocache_headers();
        } else {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true);
            header('Pragma: no-cache', true);
        }
        if (function_exists('status_header')) {
            status_header(410);
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 410 Gone', true, 410);
        }
        header('Content-Type: text/plain; charset=utf-8', true);
        echo "410 Gone";
        exit;
    }
}

/**
 * Send a 301 Permanent Redirect and exit
 *
 * Used by guard functions to redirect requests to proper URLs.
 *
 * @since 2.0.0
 * @param string $to Target URL
 * @param string $reason Reason code for logging/debugging
 * @return void
 */
if (!function_exists('lm_guard_send_301')) {
    function lm_guard_send_301(string $to, string $reason): void {
        header('X-LM-Guard: 301; reason=' . $reason);
        header('X-Robots-Tag: noindex, nofollow', true);
        if (function_exists('nocache_headers')) {
            nocache_headers();
        }
        wp_safe_redirect($to, 301);
        exit;
    }
}

// =============================================================================
// URL VALIDATION GUARDS
// =============================================================================

/**
 * Early 410 guard - blocks invalid URL patterns ASAP
 *
 * Blocks requests with:
 * - ?w= parameter (normal or encoded)
 * - _load_more / load_more / _page parameters
 * - /contents/* paths
 * - /shop/* paths
 *
 * IMPORTANT: Only runs on frontend, not admin/AJAX/REST/CLI/CRON
 *
 * @since 2.0.0
 * @return void
 */
if (!function_exists('lm_guard_early_410')) {
    function lm_guard_early_410(): void {
        // HARD BLOCK admin/editor
        if (defined('WP_ADMIN') && WP_ADMIN) return;
        if (is_admin()) return;

        if (defined('WP_CLI') && WP_CLI) return;
        if (defined('DOING_CRON') && DOING_CRON) return;
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (defined('REST_REQUEST') && REST_REQUEST) return;

        $raw_uri     = $_SERVER['REQUEST_URI'] ?? '/';
        $decoded_uri = rawurldecode($raw_uri);

        $parsed = @parse_url($decoded_uri);
        $path   = isset($parsed['path']) ? (string) $parsed['path'] : $decoded_uri;
        $query  = isset($parsed['query']) ? (string) $parsed['query'] : ($_SERVER['QUERY_STRING'] ?? '');

        // 410: ?w= (normal / encodé)
        if (
            (is_string($query) && preg_match('/(^|[&])w=([^&]+)/i', $query)) ||
            preg_match('/[?&]w=([^&]+)/i', $raw_uri) ||
            preg_match('/[?&]w=([^&]+)/i', $decoded_uri)
        ) {
            lm_guard_send_410('w_param');
        }

        // 410: _load_more / load_more / _page (normal / encodé)
        if (
            (is_string($query) && preg_match('/(^|[&])(_load_more|load_more|_page)=/i', $query)) ||
            preg_match('/(_load_more|load_more|_page)=/i', $raw_uri) ||
            preg_match('/(_load_more|load_more|_page)=/i', $decoded_uri)
        ) {
            lm_guard_send_410('load_more_param_410');
        }

        // 410: /contents/*
        if (strpos($path, '/contents/') === 0) {
            lm_guard_send_410('contents_prefix');
        }

        // 410: /shop/*
        if (strpos($path, '/shop/') === 0) {
            lm_guard_send_410('shop_prefix');
        }
    }
}
// FIX: plus de parse_request
add_action('template_redirect', 'lm_guard_early_410', 0);

/**
 * Redirect pagination beyond max pages to page 1
 *
 * When /page/N/ exceeds max_num_pages, 301 redirects to first page.
 * Only applies to archives, home, and search.
 *
 * @since 2.0.0
 * @return void
 */
if (!function_exists('lm_guard_redirect_over_max_pagination')) {
    function lm_guard_redirect_over_max_pagination(): void {
        if (defined('WP_ADMIN') && WP_ADMIN) return;
        if (is_admin()) return;
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (defined('REST_REQUEST') && REST_REQUEST) return;

        if (!(is_archive() || is_home() || is_search())) {
            return;
        }

        $paged = (int) get_query_var('paged');
        if ($paged <= 1) {
            return;
        }

        global $wp_query;
        $max = isset($wp_query->max_num_pages) ? (int) $wp_query->max_num_pages : 0;

        if ($max <= 0) {
            return;
        }

        if ($paged > $max) {
            $target = get_pagenum_link(1);
            if (!$target) {
                $target = home_url('/');
            }
            lm_guard_send_301($target, 'paged_over_max');
        }
    }
}
add_action('template_redirect', 'lm_guard_redirect_over_max_pagination', 1);

/**
 * 410 for unused brand pages (marque CPT)
 *
 * Returns 410 Gone for brand pages that have no associated
 * tests or posts linked via the 'marque' ACF field.
 *
 * @since 2.0.0
 * @return void
 */
if (!function_exists('lm_guard_brand_unused_410')) {
    function lm_guard_brand_unused_410(): void {
        if (defined('WP_ADMIN') && WP_ADMIN) return;
        if (is_admin()) return;
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (defined('REST_REQUEST') && REST_REQUEST) return;

        $marque_post = null;

        if (is_singular('marque')) {
            $marque_post = get_queried_object();
        } else {
            $raw_uri     = $_SERVER['REQUEST_URI'] ?? '/';
            $decoded_uri = rawurldecode($raw_uri);
            $path        = trim((string) parse_url($decoded_uri, PHP_URL_PATH), '/');

            if ($path !== 'marques' && preg_match('#^marques/([^/]+)/?$#i', $path, $m)) {
                $slug = sanitize_title($m[1]);
                $candidate = get_page_by_path($slug, OBJECT, 'marque');
                if ($candidate && !empty($candidate->ID)) {
                    $marque_post = $candidate;
                }
            }
        }

        if (!$marque_post || empty($marque_post->ID)) {
            return;
        }

        $marque_id = (int) $marque_post->ID;
        $cache_key = 'lm_brand_used_' . $marque_id;
        $has_any   = get_transient($cache_key);

        if ( $has_any === false ) {
            $meta_like = '"' . $marque_id . '"';

            $q_tests = new WP_Query([
                'post_type'      => 'test',
                'posts_per_page' => 1,
                'no_found_rows'  => true,
                'fields'         => 'ids',
                'meta_query'     => [[
                    'key'     => 'marque',
                    'value'   => $meta_like,
                    'compare' => 'LIKE',
                ]],
            ]);

            $q_posts = new WP_Query([
                'post_type'      => 'post',
                'posts_per_page' => 1,
                'no_found_rows'  => true,
                'fields'         => 'ids',
                'meta_query'     => [[
                    'key'     => 'marque',
                    'value'   => $meta_like,
                    'compare' => 'LIKE',
                ]],
            ]);

            $has_any = ($q_tests->have_posts() || $q_posts->have_posts()) ? 'yes' : 'no';
            wp_reset_postdata();
            set_transient($cache_key, $has_any, 12 * HOUR_IN_SECONDS);
        }

        if ($has_any !== 'yes') {
            lm_guard_send_410('brand_unused_410');
        }
    }
}
add_action('template_redirect', 'lm_guard_brand_unused_410', 2);

// =============================================================================
// URL SCOPE HELPERS
// =============================================================================

/**
 * Check if current request is in /redacteur/ scope
 *
 * Used for canonical URL handling on author pages.
 *
 * @since 2.0.0
 * @return bool True if path starts with /redacteur/
 */
function lm_can_is_redacteur_scope(): bool {
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    if ($uri === '') return false;

    // Retire query pour matcher strictement le path
    $path = preg_replace('/\?.*/', '', $uri);
    return (stripos($path, '/redacteur/') === 0);
}

/**
 * Get clean current URL without query string or hash
 *
 * Used for generating canonical URLs.
 *
 * @since 2.0.0
 * @return string Clean URL with trailing slash
 */
function lm_can_current_url_clean(): string {
    $scheme = is_ssl() ? 'https' : 'http';
    $host   = wp_parse_url(home_url('/'), PHP_URL_HOST);
    $uri    = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';

    $url = $scheme . '://' . $host . $uri;
    $url = preg_replace('/#.*/', '', $url);
    $url = preg_replace('/\?.*/', '', $url);

    // Normalisation trailing slash
    return trailingslashit($url);
}
