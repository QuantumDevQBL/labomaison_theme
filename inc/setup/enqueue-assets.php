<?php
/**
 * Asset Enqueuing
 *
 * All CSS and JavaScript enqueuing for frontend and admin.
 *
 * @package Labomaison
 * @subpackage Setup
 * @since 2.0.0
 *
 * Functions in this file:
 * - enqueue_instagram_embed_script()
 * - enqueue_font_awesome()
 * - my_custom_fonts_enqueue()
 * - load_acf_scripts()
 * - ACF styles dequeue
 * - Refactored CSS toggle
 *
 * Dependencies: None
 * Load Priority: 2
 * Risk Level: LOW
 *
 * Migrated from: functions.php L27-40, L55-58, L68-74, L1142-1150, L3415-3424
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// HEAD SCRIPTS
// =============================================================================

/**
 * Enqueue Instagram and TikTok embed scripts
 *
 * Only loads on singular post, marque, or test pages.
 *
 * @since 2.0.0
 * @return void
 */
function enqueue_instagram_embed_script() {
    // Vérifie si on est sur un type de post spécifique : post, marque, ou test
    if (is_singular(array('post', 'marque', 'test'))) {
        // Ajoute le script d'Instagram dans le head de la page
        echo '<script async src="//www.instagram.com/embed.js"></script><script async src="https://www.tiktok.com/embed.js"></script>';
    }
}
add_action('wp_head', 'enqueue_instagram_embed_script');

/**
 * Load ACF form scripts
 *
 * Required for ACF frontend forms to work properly.
 *
 * @since 2.0.0
 * @return void
 */
function load_acf_scripts() {
    acf_form_head(); // Assurez-vous que cela ne cause pas de problèmes de performance ou de fonctionnement
}
add_action('wp_head', 'load_acf_scripts');

// =============================================================================
// ENQUEUED STYLES
// =============================================================================

/**
 * Enqueue Font Awesome icons
 *
 * @since 2.0.0
 * @return void
 */
function enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
add_action('wp_enqueue_scripts', 'enqueue_font_awesome');

/**
 * Dequeue ACF styles on frontend
 *
 * Removes ACF/ACFE styles that aren't needed on the frontend
 * to improve performance.
 *
 * @since 2.0.0
 */
add_action('wp_enqueue_scripts', function() {
    if (!is_admin()) {
        wp_dequeue_style('acf-input');
        wp_dequeue_style('acf-global');
        wp_dequeue_style('acf-pro-input');
        wp_dequeue_style('acfe-input');
        wp_dequeue_style('acfe');
    }
}, 100);

// =============================================================================
// ENQUEUED SCRIPTS
// =============================================================================

/**
 * Enqueue custom JavaScript
 *
 * @since 2.0.0
 * @return void
 */
function my_custom_fonts_enqueue() {
    wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/inc/js/custom.js', array(), false, true);
}
add_action('wp_enqueue_scripts', 'my_custom_fonts_enqueue');

// =============================================================================
// REFACTORED CSS TOGGLE
// =============================================================================

/**
 * Load refactored CSS when toggle is enabled
 *
 * When USE_REFACTORED_CSS constant is true, loads the refactored
 * stylesheet in addition to the main theme styles for testing.
 *
 * @since 2.0.0
 */
add_action('wp_enqueue_scripts', function() {
    if (defined('USE_REFACTORED_CSS') && USE_REFACTORED_CSS) {
        wp_enqueue_style(
            'labomaison-refactored',
            get_stylesheet_directory_uri() . '/css/style-refactored.css',
            [],
            LABOMAISON_VERSION
        );
    }
}, 20);
