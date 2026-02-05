<?php
/**
 * GeneratePress Integration
 *
 * Hooks for GeneratePress parent theme.
 *
 * Note: The main GeneratePress archive filters (generate_archive_title,
 * generate_after_loop) are in hooks/content-filters.php as they are
 * content-related filters.
 *
 * @package Labomaison
 * @subpackage Integrations
 * @since 2.0.0
 *
 * Functions in this file:
 * - GeneratePress-specific configurations
 *
 * Dependencies: GeneratePress theme
 * Load Priority: 5
 * Risk Level: LOW
 *
 * Migrated from: functions.php
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// GENERATEPRESS CONFIGURATION
// =============================================================================

/**
 * GeneratePress theme integration hooks
 *
 * Most GeneratePress content filters are in hooks/content-filters.php:
 * - generate_archive_title: Adds ACF chapeau field to archive titles
 * - generate_after_loop: Adds ACF content and FAQ fields after archive loops
 *
 * This file is reserved for GeneratePress-specific configurations
 * that don't fit elsewhere.
 */

// Additional GeneratePress integrations can be added here as needed
