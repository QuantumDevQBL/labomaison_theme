<?php
/**
 * Lazy Loading Enforcement
 *
 * Source: theme inc/setup/theme-support.php
 *
 * @package Labomaison_Perf_Core
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Enable lazy loading for all images (WP native)
 */
add_filter('wp_lazy_loading_enabled', '__return_true');

/**
 * Force lazy load for images that don't have a loading attribute.
 *
 * Skip if WP Rocket is active — it handles lazy loading natively
 * and excludes above-the-fold images (LCP). Running both would
 * hurt Core Web Vitals.
 */
if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
    function lm_perf_force_lazy_load_images( $content ) {
        $content = preg_replace('/<img(?![^>]+loading=["\'](?:lazy|eager|auto)["\'])([^>]+)>/', '<img loading="lazy" $1>', $content);
        return $content;
    }
    add_filter('the_content', 'lm_perf_force_lazy_load_images');
}
