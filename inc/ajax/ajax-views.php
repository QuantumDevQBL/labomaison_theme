<?php
/**
 * AJAX View Tracking
 *
 * AJAX handlers for post view tracking using daily/weekly windows.
 *
 * @package Labomaison
 * @subpackage AJAX
 * @since 2.0.0
 *
 * Functions in this file:
 * - lm_track_view()
 * - Views tracker script enqueue
 *
 * AJAX Actions:
 * - wp_ajax_lm_track_view
 * - wp_ajax_nopriv_lm_track_view
 *
 * Metas:
 * - lm_views_daily : array('YYYYMMDD' => int) (daily storage)
 * - post_views_7d  : int (7-day sliding window sum)
 *
 * Dependencies: None
 * Load Priority: 7 (AJAX only)
 * Risk Level: HIGH
 *
 * Migrated from: functions.php L2696-2778
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// AJAX HANDLER
// =============================================================================

/**
 * Track post view via AJAX
 *
 * Records a view in daily buckets and maintains a 7-day sliding window sum.
 * Skips logged-in users. Keeps 35 days of history.
 *
 * @since 2.0.0
 * @return void Sends JSON response
 */
function lm_track_view() {
    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    if (!$post_id) {
        wp_send_json_error(['message' => 'missing_post_id']);
    }

    $pt = get_post_type($post_id);
    if (!in_array($pt, ['post', 'test'], true)) {
        wp_send_json_error(['message' => 'invalid_post_type']);
    }

    // Ne pas compter admin/loggés (optionnel)
    if (is_user_logged_in()) {
        wp_send_json_success(['skipped' => true]);
    }

    $day_key = wp_date('Ymd'); // timezone WP

    $daily = get_post_meta($post_id, 'lm_views_daily', true);
    if (!is_array($daily)) $daily = [];

    $daily[$day_key] = isset($daily[$day_key]) ? ((int)$daily[$day_key] + 1) : 1;

    // Garde 35 jours d'historique
    $cutoff = (int) wp_date('Ymd', strtotime('-35 days'));
    foreach ($daily as $k => $v) {
        if ((int)$k < $cutoff) unset($daily[$k]);
    }

    update_post_meta($post_id, 'lm_views_daily', $daily);

    // 7 jours glissants
    $sum7 = 0;
    for ($i = 0; $i < 7; $i++) {
        $k = wp_date('Ymd', strtotime("-{$i} days"));
        $sum7 += isset($daily[$k]) ? (int)$daily[$k] : 0;
    }
    update_post_meta($post_id, 'post_views_7d', $sum7);

    wp_send_json_success(['post_id' => $post_id, 'post_views_7d' => $sum7]);
}
add_action('wp_ajax_nopriv_lm_track_view', 'lm_track_view');
add_action('wp_ajax_lm_track_view', 'lm_track_view');

// =============================================================================
// FRONTEND TRACKER SCRIPT
// =============================================================================

/**
 * Enqueue view tracking script on singular posts/tests
 *
 * Uses sendBeacon for non-blocking tracking with session deduplication.
 * Only loads for non-logged-in users on published posts/tests.
 *
 * @since 2.0.0
 */
add_action('wp_enqueue_scripts', function () {
    if (!is_singular(['post', 'test'])) return;
    if (is_admin()) return;
    if (is_user_logged_in()) return;

    wp_register_script('lm-views-tracker', '', [], null, true);

    wp_add_inline_script('lm-views-tracker', 'window.LM_VIEWS=' . wp_json_encode([
        'ajaxurl' => admin_url('admin-ajax.php'),
        'post_id' => (int) get_queried_object_id(),
    ]) . ';', 'before');

    wp_add_inline_script('lm-views-tracker', <<<JS
document.addEventListener('DOMContentLoaded', function () {
  try {
    if (!window.LM_VIEWS || !LM_VIEWS.post_id) return;

    // 1 hit max / day / session
    var day = (new Date()).toISOString().slice(0,10);
    var key = 'lm_viewed_' + LM_VIEWS.post_id + '_' + day;
    if (sessionStorage.getItem(key)) return;
    sessionStorage.setItem(key, '1');

    var form = new FormData();
    form.append('action', 'lm_track_view');
    form.append('post_id', LM_VIEWS.post_id);

    if (navigator.sendBeacon) {
      navigator.sendBeacon(LM_VIEWS.ajaxurl, form);
    } else {
      fetch(LM_VIEWS.ajaxurl, { method: 'POST', body: form, credentials: 'same-origin' });
    }
  } catch(e) {}
});
JS, 'after');

    wp_enqueue_script('lm-views-tracker');
}, 20);
