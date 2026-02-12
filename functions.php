<?php
/**
 * Labomaison Theme Functions
 *
 * Entry point for theme functionality.
 * Shortcodes, SEO, and performance are handled by dedicated plugins.
 *
 * @package Labomaison
 * @version 2.1.0
 *
 * Plugins:
 * - labomaison-shortcodes    → all shortcodes
 * - labomaison-seo-core      → Rank Math, breadcrumbs, canonical, sitemap
 * - labomaison-perf-core     → WP Rocket, LiteSpeed, Affilizz, image dims
 *
 * Theme handles:
 * - /inc/utilities/           → helpers, security guards
 * - /inc/setup/               → enqueue, theme support, image sizes
 * - /inc/admin/               → columns, editor config
 * - /inc/hooks/               → query mods, redirects, content filters
 * - /inc/ajax/                → AJAX handlers
 * - /inc/rss/                 → RSS feed customization
 * - /inc/analytics/           → GA4 tracking
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// THEME CONSTANTS
// =============================================================================

define( 'LABOMAISON_VERSION', '2.1.0' );
define( 'LABOMAISON_DIR', get_stylesheet_directory() );
define( 'LABOMAISON_URI', get_stylesheet_directory_uri() );
define( 'LABOMAISON_INC', LABOMAISON_DIR . '/inc' );

if ( ! defined( 'USE_REFACTORED_CSS' ) ) {
    define( 'USE_REFACTORED_CSS', true );
}

// =============================================================================
// PRIORITY 1: Foundation
// =============================================================================

require_once LABOMAISON_INC . '/utilities/security.php';

// =============================================================================
// PRIORITY 2: Theme Setup
// =============================================================================

require_once LABOMAISON_INC . '/setup/theme-support.php';
require_once LABOMAISON_INC . '/setup/enqueue-assets.php';
require_once LABOMAISON_INC . '/setup/image-sizes.php';

// =============================================================================
// PRIORITY 3: Admin Only
// =============================================================================

if ( is_admin() ) {
    require_once LABOMAISON_INC . '/admin/admin-columns.php';
    require_once LABOMAISON_INC . '/admin/editor-config.php';
}

// =============================================================================
// PRIORITY 4: Hooks & Filters
// =============================================================================

require_once LABOMAISON_INC . '/hooks/query-modifications.php';
require_once LABOMAISON_INC . '/hooks/template-redirects.php';
require_once LABOMAISON_INC . '/hooks/content-filters.php';

// =============================================================================
// PRIORITY 5: AJAX Handlers
// =============================================================================

require_once LABOMAISON_INC . '/ajax/ajax-views.php';
require_once LABOMAISON_INC . '/ajax/ajax-filters.php';

// =============================================================================
// PRIORITY 6: RSS Feed
// =============================================================================

require_once LABOMAISON_INC . '/rss/feed-customization.php';

// =============================================================================
// PRIORITY 7: Analytics
// =============================================================================

require_once LABOMAISON_INC . '/analytics/ga4-tracking.php';

// =============================================================================
// THEME ACTIVATION
// =============================================================================

add_action( 'after_switch_theme', function() {
    flush_rewrite_rules();
} );

// =============================================================================
// DEBUG MODE
// =============================================================================

if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'LABOMAISON_DEBUG' ) && LABOMAISON_DEBUG ) {
    add_action( 'init', function() {
        error_log( '[Labomaison] Theme v' . LABOMAISON_VERSION . ' initialized successfully' );
    }, 999 );
}


