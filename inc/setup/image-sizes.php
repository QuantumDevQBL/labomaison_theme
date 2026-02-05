<?php
/**
 * Image Sizes
 *
 * Custom image size definitions for the theme.
 *
 * @package Labomaison
 * @subpackage Setup
 * @since 2.0.0
 *
 * Image sizes defined:
 * - author-thumbnail (30x30)
 * - grid_size (150x150)
 *
 * Dependencies: None
 * Load Priority: 2
 * Risk Level: LOW
 *
 * Migrated from: functions.php L865-866
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// CUSTOM IMAGE SIZES
// =============================================================================

/**
 * Author thumbnail size
 * Used for author avatars in bylines
 */
add_image_size('author-thumbnail', 30, 30, true);

/**
 * Grid size
 * Used for grid layouts and thumbnails
 */
add_image_size('grid_size', 150, 150, true);
