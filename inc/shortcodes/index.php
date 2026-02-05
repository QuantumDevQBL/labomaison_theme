<?php
/**
 * Shortcodes Loader
 *
 * Loads all shortcode files.
 *
 * @package Labomaison
 * @subpackage Shortcodes
 * @since 2.0.0
 *
 * Load Priority: 6
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// SHORTCODE FILE LOADING
// =============================================================================

// Product display shortcodes (specs, gallery, pros/cons)
require_once __DIR__ . '/product-display.php';

// Rating shortcodes (notes, stars)
require_once __DIR__ . '/ratings.php';

// Taxonomy display shortcodes (category info, terms)
require_once __DIR__ . '/taxonomy-display.php';

// Related content shortcodes (related posts, associated)
require_once __DIR__ . '/related-content.php';

// Archive/grid shortcodes (listings, pagination)
require_once __DIR__ . '/archive-grids.php';

// Comparison shortcodes
require_once __DIR__ . '/comparison.php';

// Navigation shortcodes (menus, TOC)
require_once __DIR__ . '/navigation.php';

// Miscellaneous shortcodes
require_once __DIR__ . '/misc.php';
