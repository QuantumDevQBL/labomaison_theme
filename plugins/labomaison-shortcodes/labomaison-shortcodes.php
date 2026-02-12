<?php
/**
 * Plugin Name: Labomaison Shortcodes
 * Plugin URI:  https://quantumdev.fr
 * Description: All shortcodes for Labomaison content rendering — product display, ratings, navigation, grids.
 * Version:     1.0.0
 * Author:      QBL
 * Author URI:  https://quantumdev.fr
 * Requires PHP: 7.4
 * License:     GPL-2.0+
 *
 * MIGRATION GUIDE:
 * 1. Copy this plugin to wp-content/plugins/labomaison-shortcodes/
 * 2. Copy theme's inc/shortcodes/*.php files to this plugin's inc/ folder
 * 3. Copy helper functions from theme's inc/utilities/helpers.php that
 *    shortcodes depend on (generate_star_rating, generate_card_html, etc.)
 * 4. Activate the plugin
 * 5. Remove inc/shortcodes/ from the theme (keep index.php as empty fallback)
 *
 * COMPATIBILITY:
 * The theme's shortcodes/index.php should check if this plugin is active
 * before registering shortcodes, to avoid double-registration.
 *
 * @package Labomaison_Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LM_SC_VERSION', '1.0.0' );
define( 'LM_SC_DIR', plugin_dir_path( __FILE__ ) );
define( 'LM_SC_FILE', __FILE__ );

/**
 * Compatibility: signals that the shortcodes plugin is active.
 * Theme checks this before registering its own shortcodes.
 */
if ( ! function_exists( 'lm_shortcodes_plugin_active' ) ) {
    function lm_shortcodes_plugin_active(): bool {
        return true;
    }
}

/**
 * Load helpers (star ratings, card generators) — skip if theme already loaded them.
 */
if ( ! function_exists( 'generate_star_rating' ) ) {
    require_once LM_SC_DIR . 'inc/helpers.php';
}

/**
 * Load all shortcode modules.
 * These are the refactored modules from the Labomaison theme.
 */
require_once LM_SC_DIR . 'inc/product-display.php';
require_once LM_SC_DIR . 'inc/ratings.php';
require_once LM_SC_DIR . 'inc/taxonomy-display.php';
require_once LM_SC_DIR . 'inc/related-content.php';
require_once LM_SC_DIR . 'inc/archive-grids.php';
require_once LM_SC_DIR . 'inc/comparison.php';
require_once LM_SC_DIR . 'inc/navigation.php';
require_once LM_SC_DIR . 'inc/misc.php';

/**
 * Plugin activation
 */
register_activation_hook( __FILE__, function() {
    update_option( 'lm_shortcodes_version', LM_SC_VERSION );
} );
