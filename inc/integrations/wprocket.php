<?php
/**
 * WP Rocket Integration
 *
 * Exclusions for WP Rocket optimization.
 *
 * @package Labomaison
 * @subpackage Integrations
 * @since 2.0.0
 *
 * Functions in this file:
 * - rocket_delay_js_exclusions
 * - rocket_exclude_js
 * - rocket_exclude_css
 * - rocket_rucss_inline_content_exclusions
 *
 * Dependencies: WP Rocket plugin
 * Load Priority: 5
 * Condition: defined('WP_ROCKET_VERSION')
 * Risk Level: LOW
 *
 * Migrated from: functions.php L1603-1652
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// WP ROCKET EXCLUSIONS
// =============================================================================

/**
 * Configure WP Rocket exclusions for PhotoSwipe lightbox
 *
 * Prevents Delay/Defer/Minify/Combine from breaking PhotoSwipe + payload.
 *
 * @since 2.0.0
 */
add_action('init', function () {

    // 1) Exclure du "Delay JS"
    add_filter('rocket_delay_js_exclusions', function ($excluded) {
        $excluded = is_array($excluded) ? $excluded : [];
        return array_merge($excluded, [
            'labomaison-article-lightbox',
            'lmal-lightbox.js',
            'lmal-bootstrap.js',
            'photoswipe',
            'photoswipe-lightbox',
            'PhotoSwipe',
            'PhotoSwipeLightbox',
            'labomaisonArticleLightbox',
            'window.labomaisonArticleLightbox',
        ]);
    });

    // 2) Exclure de l'optimisation JS (minify/combine/defer selon config Rocket)
    add_filter('rocket_exclude_js', function ($excluded) {
        $excluded = is_array($excluded) ? $excluded : [];
        return array_merge($excluded, [
            'labomaison-article-lightbox/assets/js/lmal-lightbox.js',
            'labomaison-article-lightbox/assets/js/lmal-bootstrap.js',
            'labomaison-article-lightbox/assets/vendor/photoswipe/photoswipe.umd.min.js',
            'labomaison-article-lightbox/assets/vendor/photoswipe/photoswipe-lightbox.umd.min.js',
            'photoswipe.umd.min.js',
            'photoswipe-lightbox.umd.min.js',
            'labomaison-article-lightbox/assets/',
        ]);
    });

    // 3) Exclure le CSS du plugin des optimisations agressives (RUC/unused CSS)
    add_filter('rocket_exclude_css', function ($excluded) {
        $excluded = is_array($excluded) ? $excluded : [];
        $excluded[] = 'labomaison-article-lightbox/assets/css/lmal-lightbox.css';
        return $excluded;
    });

    // 4) Si "Remove Unused CSS" est actif, on force l'inclusion de notre CSS
    // (WP Rocket a plusieurs filtres selon versions, on couvre le plus courant)
    add_filter('rocket_rucss_inline_content_exclusions', function ($excluded) {
        $excluded = is_array($excluded) ? $excluded : [];
        return array_merge($excluded, [
            '.pswp.lmal-pswp',
            '.lmal-thumbs',
            '.lmal-thumb',
        ]);
    });

}, 1);
