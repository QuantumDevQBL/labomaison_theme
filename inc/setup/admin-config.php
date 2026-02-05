<?php
/**
 * Admin Configuration
 *
 * Admin-specific configurations and plugin init delays.
 *
 * Note: The plugin init delays (WPSP, Affilizz) are now in
 * setup/theme-support.php. Marque comment disabling is in
 * hooks/content-filters.php.
 *
 * @package Labomaison
 * @subpackage Setup
 * @since 2.0.0
 *
 * Dependencies: None
 * Load Priority: 2 (Admin only)
 * Risk Level: CRITICAL
 *
 * Migrated from: functions.php L814-819, L845-861
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// ADMIN CONFIGURATION
// =============================================================================

/**
 * Admin-specific configurations
 *
 * The following have been migrated to their respective modules:
 * - Plugin init delays (WPSP, Affilizz) -> setup/theme-support.php
 * - Disable comments on marque -> hooks/content-filters.php
 *
 * This file is reserved for additional admin-only configurations.
 */
