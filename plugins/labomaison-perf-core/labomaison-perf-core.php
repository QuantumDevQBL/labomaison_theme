<?php
/**
 * Plugin Name: Labomaison Performance Core
 * Plugin URI:  https://quantumdev.fr
 * Description: Performance optimizations — WP Rocket, LiteSpeed, image dimensions, lazy loading, Affilizz.
 * Version:     1.0.0
 * Author:      QBL
 * Author URI:  https://quantumdev.fr
 * Requires PHP: 7.4
 * License:     GPL-2.0+
 *
 * MIGRATION GUIDE:
 * 1. Copy this plugin to wp-content/plugins/labomaison-perf-core/
 * 2. Copy theme files to plugin inc/:
 *    - inc/integrations/wprocket.php → inc/wprocket.php
 *    - inc/integrations/litespeed.php → inc/litespeed.php
 *    - inc/integrations/affilizz.php → inc/affilizz.php
 *    - add_image_dimensions() from content-filters.php → inc/image-dimensions.php
 *    - force_lazy_load_images() from theme-support.php → inc/lazy-loading.php
 *    - WPSP delay logic from theme-support.php → inc/plugin-load-order.php
 * 3. Activate the plugin
 * 4. Remove the corresponding code from theme files
 * 5. Update theme conditional loading to skip if plugin is active
 *
 * @package Labomaison_Perf_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LM_PERF_VERSION', '1.0.0' );
define( 'LM_PERF_DIR', plugin_dir_path( __FILE__ ) );
define( 'LM_PERF_FILE', __FILE__ );

/**
 * Compatibility: signals that the perf plugin is active.
 */
if ( ! function_exists( 'lm_perf_core_active' ) ) {
    function lm_perf_core_active(): bool {
        return true;
    }
}

// =============================================================================
// Always load (not conditional on specific plugins)
// =============================================================================

require_once LM_PERF_DIR . 'inc/image-dimensions.php';
require_once LM_PERF_DIR . 'inc/lazy-loading.php';
require_once LM_PERF_DIR . 'inc/plugin-load-order.php';

// =============================================================================
// Conditional on plugin availability
// =============================================================================

if ( defined( 'WP_ROCKET_VERSION' ) ) {
    require_once LM_PERF_DIR . 'inc/wprocket.php';
}

// LiteSpeed: load only if plugin active AND file exists
if ( defined( 'LSCWP_V' ) ) {
    $lm_litespeed_file = LM_PERF_DIR . 'inc/litespeed.php';
    if ( file_exists( $lm_litespeed_file ) ) {
        require_once $lm_litespeed_file;
    }
}

// Affilizz: safe even without plugin (uses is_singular() guard inside)
require_once LM_PERF_DIR . 'inc/affilizz.php';

/**
 * Plugin activation
 */
register_activation_hook( __FILE__, function() {
    update_option( 'lm_perf_core_version', LM_PERF_VERSION );
} );
