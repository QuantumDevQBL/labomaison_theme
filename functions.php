<?php
/**
 * Labomaison Theme Functions
 *
 * This file serves as the entry point for all theme functionality.
 * All actual functions are organized in /inc/ modules.
 *
 * @package Labomaison
 * @version 2.0.0
 *
 * MIGRATION NOTE:
 * This file replaces the original 3,400+ line functions.php
 * All functionality has been organized into the /inc/ directory.
 *
 * Directory Structure:
 * /inc
 * ├── setup/           - Theme setup and configuration
 * ├── admin/           - Admin customizations
 * ├── hooks/           - Content and query filters
 * ├── integrations/    - Plugin integrations
 * ├── shortcodes/      - All shortcodes (replaces shortcode_list.php)
 * ├── ajax/            - AJAX handlers
 * ├── rss/             - RSS feed customizations
 * ├── analytics/       - GA4 tracking
 * └── utilities/       - Helper functions
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// THEME CONSTANTS
// =============================================================================

define( 'LABOMAISON_VERSION', '2.0.0' );
define( 'LABOMAISON_DIR', get_stylesheet_directory() );
define( 'LABOMAISON_URI', get_stylesheet_directory_uri() );
define( 'LABOMAISON_INC', LABOMAISON_DIR . '/inc' );

// CSS Toggle (for testing refactored CSS)
if ( ! defined( 'USE_REFACTORED_CSS' ) ) {
    define( 'USE_REFACTORED_CSS', true );
}

// =============================================================================
// EMERGENCY ROLLBACK
// =============================================================================
// Uncomment the following line to revert to the original functions.php:
// define( 'LABOMAISON_USE_LEGACY_FUNCTIONS', true );

if ( defined( 'LABOMAISON_USE_LEGACY_FUNCTIONS' ) && LABOMAISON_USE_LEGACY_FUNCTIONS ) {
    require_once LABOMAISON_DIR . '/functions.php.backup';
    return; // Stop loading new modules
}

// =============================================================================
// PRIORITY 1: Foundation (MUST LOAD FIRST)
// These utilities are dependencies for other modules
// =============================================================================

require_once LABOMAISON_INC . '/utilities/helpers.php';
require_once LABOMAISON_INC . '/utilities/security.php';

// =============================================================================
// PRIORITY 2: Theme Setup
// Core theme configuration that must be available early
// =============================================================================

require_once LABOMAISON_INC . '/setup/theme-support.php';
require_once LABOMAISON_INC . '/setup/enqueue-assets.php';
require_once LABOMAISON_INC . '/setup/image-sizes.php';

// =============================================================================
// PRIORITY 3: Admin Only
// These only load in the WordPress admin area
// =============================================================================

if ( is_admin() ) {
    require_once LABOMAISON_INC . '/admin/admin-columns.php';
    require_once LABOMAISON_INC . '/admin/editor-config.php';
}

// =============================================================================
// PRIORITY 4: Hooks & Filters
// Query and content modifications
// =============================================================================

require_once LABOMAISON_INC . '/hooks/query-modifications.php';
require_once LABOMAISON_INC . '/hooks/template-redirects.php';
require_once LABOMAISON_INC . '/hooks/content-filters.php';

// =============================================================================
// PRIORITY 5: Plugin Integrations
// Conditional loading based on plugin availability
// =============================================================================

// WP Grid Builder
if ( class_exists( 'WP_Grid_Builder' ) ) {
    require_once LABOMAISON_INC . '/integrations/wpgridbuilder.php';
}

// Rank Math SEO
if ( class_exists( 'RankMath' ) ) {
    require_once LABOMAISON_INC . '/integrations/rankmath.php';
}

// WP Rocket
if ( defined( 'WP_ROCKET_VERSION' ) ) {
    require_once LABOMAISON_INC . '/integrations/wprocket.php';
}

// LiteSpeed Cache
if ( defined( 'LSCWP_V' ) ) {
    require_once LABOMAISON_INC . '/integrations/litespeed.php';
}

// Affilizz
if ( function_exists( 'affilizz_init' ) || class_exists( 'Affilizz\Core' ) ) {
    require_once LABOMAISON_INC . '/integrations/affilizz.php';
}

// =============================================================================
// PRIORITY 6: Shortcodes
// All shortcode definitions (replaces shortcode_list.php)
// =============================================================================

require_once LABOMAISON_INC . '/shortcodes/index.php';

// =============================================================================
// PRIORITY 7: AJAX Handlers
// Must load unconditionally so wp_ajax hooks are registered
// =============================================================================

require_once LABOMAISON_INC . '/ajax/ajax-views.php';
require_once LABOMAISON_INC . '/ajax/ajax-filters.php';

// =============================================================================
// PRIORITY 8: RSS Feed Customization
// =============================================================================

require_once LABOMAISON_INC . '/rss/feed-customization.php';

// =============================================================================
// PRIORITY 9: Analytics
// Non-blocking, can load last
// =============================================================================

require_once LABOMAISON_INC . '/analytics/ga4-tracking.php';

// =============================================================================
// THEME ACTIVATION
// =============================================================================

/**
 * Theme activation hook
 * Flushes rewrite rules when theme is activated
 */
function labomaison_theme_activation() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'labomaison_theme_activation' );

// =============================================================================
// DEBUG MODE
// =============================================================================

if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'LABOMAISON_DEBUG' ) && LABOMAISON_DEBUG ) {
    add_action( 'init', function() {
        error_log( '[Labomaison] Theme v' . LABOMAISON_VERSION . ' initialized successfully' );
    }, 999 );
}
