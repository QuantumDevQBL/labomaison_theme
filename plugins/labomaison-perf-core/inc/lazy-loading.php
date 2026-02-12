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
 * Enable lazy loading for all images
 */
add_filter('wp_lazy_loading_enabled', '__return_true');

/**
 * Force lazy load for images that don't have it
 */
function lm_perf_force_lazy_load_images( $content ) {
    $content = preg_replace('/<img(?![^>]+loading=["\'](?:lazy|eager|auto)["\'])([^>]+)>/', '<img loading="lazy" $1>', $content);
    return $content;
}
add_filter('the_content', 'lm_perf_force_lazy_load_images');
