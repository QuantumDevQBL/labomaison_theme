<?php
/**
 * Plugin Load Order Fixes
 *
 * Delays WPSP and Affilizz initialization to avoid conflicts.
 * Source: theme inc/setup/theme-support.php
 *
 * Uses priority 1 on plugins_loaded to run before the default
 * priority 10 init hooks of these plugins.
 *
 * @package Labomaison_Perf_Core
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('plugins_loaded', function() {
    if (class_exists('WPSP_PRO')) {
        remove_action('plugins_loaded', ['WPSP_PRO', 'init'], 10);
        add_action('init', ['WPSP_PRO', 'init']);
    }

    if (class_exists('WPSP')) {
        remove_action('plugins_loaded', ['WPSP', 'init'], 10);
        add_action('init', ['WPSP', 'init']);
    }

    if (class_exists('Affilizz\Core')) {
        $affilizz_core = Affilizz\Core::get_instance();
        remove_action('plugins_loaded', [$affilizz_core, 'init'], 10);
        add_action('init', [$affilizz_core, 'init']);
    }
}, 1); // Priority 1: run before the target hooks at priority 10
