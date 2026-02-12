<?php
/**
 * Theme Support & Configuration
 *
 * Core theme setup: excerpts, comments disable, lazy loading.
 *
 * @package Labomaison
 * @subpackage Setup
 * @since 2.0.0
 *
 * Functions in this file:
 * - wpse325327_add_excerpts_to_pages()
 * - force_lazy_load_images()
 * - Comments disable hooks
 *
 * Dependencies: None
 * Load Priority: 2
 * Risk Level: LOW
 *
 * Migrated from: functions.php L63-66, L821-916
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// EXCERPT SUPPORT
// =============================================================================

/**
 * Add excerpt support to pages
 *
 * Enables the excerpt meta box for pages in Gutenberg.
 *
 * @since 2.0.0
 * @return void
 */
function wpse325327_add_excerpts_to_pages() {
    add_post_type_support('page', 'excerpt');
}
add_action('init', 'wpse325327_add_excerpts_to_pages');

// =============================================================================
// LAZY LOADING
// =============================================================================

/**
 * Lazy loading — skip if perf plugin handles this
 */
if ( ! function_exists( 'lm_perf_core_active' ) ) {
    add_filter('wp_lazy_loading_enabled', '__return_true');

    function force_lazy_load_images($content) {
        $content = preg_replace('/<img(?![^>]+loading=["\'](?:lazy|eager|auto)["\'])([^>]+)>/', '<img loading="lazy" $1>', $content);
        return $content;
    }
    add_filter('the_content', 'force_lazy_load_images');
}

// =============================================================================
// COMMENTS DISABLE
// =============================================================================

/**
 * Disable comments site-wide
 *
 * Closes comments and pings for all content types.
 */
add_action('init', function() {
    // Fermer les commentaires pour les articles et les pages
    update_option('default_comment_status', 'closed');
    update_option('default_ping_status', 'closed');

    // Supprimer les commentaires des types de publication
    remove_post_type_support('post', 'comments');
    remove_post_type_support('page', 'comments');
});

/**
 * Remove comments menu from admin
 */
add_action('admin_menu', function() {
    remove_menu_page('edit-comments.php');
});

/**
 * Remove comments widget from dashboard
 */
add_action('wp_dashboard_setup', function() {
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
});

/**
 * Redirect comments page to admin home
 */
add_action('admin_init', function() {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }
});

/**
 * Close comments and pings on frontend
 */
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

/**
 * Return empty array for comments
 */
add_filter('comments_array', '__return_empty_array', 10, 2);

/**
 * Remove comment feed links from header
 */
add_action('init', function() {
    remove_action('wp_head', 'feed_links_extra', 3);
});

/**
 * Remove discussion meta box from posts/pages
 */
add_action('admin_init', function() {
    remove_meta_box('commentstatusdiv', 'post', 'normal');
    remove_meta_box('commentstatusdiv', 'page', 'normal');
});

// =============================================================================
// TEXT TRANSLATIONS
// =============================================================================

/**
 * Translate search results text to French
 */
add_filter('gettext', function($text) {
    if ('Search Results for: %s' === $text) {
        $text = 'Résultats pour : %s';
    }
    return $text;
});

// =============================================================================
// PLUGIN LOAD ORDER FIXES
// =============================================================================

/**
 * Plugin load order fixes — skip if perf plugin handles this
 */
if ( ! function_exists( 'lm_perf_core_active' ) ) {
    add_action('plugins_loaded', function() {
        if (class_exists('WPSP_PRO')) {
            remove_action('plugins_loaded', ['WPSP_PRO', 'init'], 10);
            add_action('init', ['WPSP_PRO', 'init']);
        }

        if (class_exists('WPSP')) {
            remove_action('plugins_loaded', ['WPSP', 'init'], 10);
            add_action('init', ['WPSP', 'init']);
        }

        if (class_exists('Affilizz\Core')) {
            $affilizz_core = Affilizz\Core::get_instance();
            remove_action('plugins_loaded', [$affilizz_core, 'init'], 10);
            add_action('init', [$affilizz_core, 'init']);
        }
    });
}
