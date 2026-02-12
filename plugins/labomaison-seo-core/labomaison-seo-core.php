<?php
/**
 * Plugin Name: Labomaison SEO Core
 * Plugin URI:  https://quantumdev.fr
 * Description: SEO layer for Labomaison — Rank Math integration, breadcrumbs, canonical, sitemap, schema.
 * Version:     1.0.0
 * Author:      QBL
 * Author URI:  https://quantumdev.fr
 * Requires PHP: 7.4
 * License:     GPL-2.0+
 *
 * MIGRATION GUIDE:
 * 1. Copy this plugin to wp-content/plugins/labomaison-seo-core/
 * 2. Activate the plugin
 * 3. In the theme, the rankmath.php integration is conditionally loaded
 *    via class_exists('RankMath'). Once this plugin handles Rank Math,
 *    remove inc/integrations/rankmath.php from the theme.
 * 4. The theme's security.php canonical helpers (lm_can_*) should remain
 *    in the theme as they serve multiple purposes.
 *
 * @package Labomaison_SEO_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LM_SEO_VERSION', '1.0.0' );
define( 'LM_SEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'LM_SEO_FILE', __FILE__ );

/**
 * Compatibility layer: signals that the SEO plugin is active.
 * Theme code checks function_exists('lm_seo_core_active') before
 * registering its own SEO hooks.
 *
 * @param string $module Optional module name to check
 * @return bool Always true when plugin is active
 */
if ( ! function_exists( 'lm_seo_core_active' ) ) {
    function lm_seo_core_active( string $module = '' ): bool {
        return true;
    }
}

/**
 * Load Rank Math integration after plugins are loaded.
 * Only loads if Rank Math is active.
 */
add_action( 'plugins_loaded', function() {

    if ( ! class_exists( 'RankMath' ) ) {
        return;
    }

    // Full Rank Math integration (sitemap, variables, schema, breadcrumbs, canonical, etc.)
    require_once LM_SEO_DIR . 'inc/rankmath.php';

}, 10 );

/**
 * Plugin activation
 */
register_activation_hook( __FILE__, function() {
    // Nothing to do for now, but ready for future setup
    update_option( 'lm_seo_core_version', LM_SEO_VERSION );
} );
