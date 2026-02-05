<?php
/**
 * LiteSpeed Cache Integration
 *
 * Hooks for LiteSpeed Cache plugin.
 *
 * @package Labomaison
 * @subpackage Integrations
 * @since 2.0.0
 *
 * Functions in this file:
 * - litespeed_media_ignore_remote_missing_sizes
 * - litespeed_optm_img_attr
 *
 * Dependencies: LiteSpeed Cache plugin
 * Load Priority: 5
 * Condition: defined('LSCWP_V')
 * Risk Level: LOW
 *
 * Migrated from: functions.php L835, L918-924
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// LITESPEED MEDIA OPTIMIZATION
// =============================================================================

/**
 * Ignore remote missing sizes for LiteSpeed media optimization
 *
 * Prevents errors when LiteSpeed can't find remote image sizes.
 *
 * @since 2.0.0
 */
add_filter('litespeed_media_ignore_remote_missing_sizes', '__return_true');

/**
 * Remove high fetchpriority from LiteSpeed optimized images
 *
 * Prevents fetchpriority="high" from being added to images,
 * which can cause performance issues.
 *
 * @since 2.0.0
 */
add_filter('litespeed_optm_img_attr', function($attr) {
    // Supprime le fetchpriority="high" des images
    if (isset($attr['fetchpriority']) && $attr['fetchpriority'] === 'high') {
        unset($attr['fetchpriority']);
    }
    return $attr;
});
