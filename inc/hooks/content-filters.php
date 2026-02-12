<?php
/**
 * Content Filters
 *
 * Filters applied to post content (the_content, render_block).
 *
 * @package Labomaison
 * @subpackage Hooks
 * @since 2.0.0
 *
 * Functions in this file:
 * - render_block query loop modification
 * - show-post-date block filter
 * - generate_archive_title filter (fallback if SEO plugin inactive)
 * - generate_after_loop filter (fallback if SEO plugin inactive)
 *
 * Note: Image dimensions (add_image_dimensions, add_dimensions_to_affilizz_images)
 * are handled by labomaison-perf-core plugin. Comments are disabled globally
 * in theme-support.php.
 *
 * Dependencies: utilities/helpers.php
 * Load Priority: 4
 * Risk Level: MEDIUM
 *
 * Migrated from: functions.php L432-504, L760-811
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// BLOCK RENDERING FILTERS
// =============================================================================

/**
 * Modify render_block for query loop shortcode injection
 *
 * Replaces specific block content with shortcodes based on className.
 *
 * @since 2.0.0
 */
add_filter('render_block', function ($block_content, $block) {

    if (!empty($block['attrs']['className']) && 'query_loop_headline' === $block['attrs']['className']) {
        $block_content = '[linked_test_category post_id="' . get_the_id() . '"]';
    } elseif (!empty($block['attrs']['className']) && 'query_loop_headline_post_thumbnail' === $block['attrs']['className']) {
        $block_content = '[linked_test_category_post_thumbnail post_id="' . get_the_id() . '"]';
    } elseif (!empty($block['attrs']['className']) && 'query_loop_headline_search_thumbnail' === $block['attrs']['className']) {
        $block_content = '[display_linked_info post_id="' . get_the_id() . '"]';
    }

    return $block_content;
}, 10, 2);

// =============================================================================
// SHOW POST DATE ON BLOCKS
// =============================================================================

/**
 * Show post dates on blocks with 'show-post-date' class
 *
 * Displays publish or modified date on blocks that have the
 * 'show-post-date' CSS class (used in query loops).
 *
 * @since 2.0.1
 */
add_filter('render_block', function($block_content, $block) {
    if ( strpos( $block['attrs']['className'] ?? '', 'show-post-date' ) !== false ) {
        $post_id       = get_the_ID();
        $publish_date  = get_the_date( 'd/m/Y \à H:i', $post_id );
        $modified_date = get_the_modified_date( 'd/m/Y \à H:i', $post_id );

        if ( $publish_date === $modified_date ) {
            $date_content = "<span class='datetime'>Publié le " . esc_html( $publish_date ) . "</span>";
        } else {
            $date_content = "<span class='datetime'>Mis à jour le " . esc_html( $modified_date ) . "</span>";
        }

        $block_content = $date_content . $block_content;
    }
    return $block_content;
}, 10, 2);

// =============================================================================
// GENERATEPRESS ARCHIVE FILTERS (fallback if SEO plugin inactive)
// =============================================================================

/**
 * Add ACF chapeau field to archive titles
 *
 * Displays the 'chapeau' ACF field below category/tag archive titles.
 *
 * @since 2.0.0
 */
add_filter('generate_archive_title', function ($title) {
    // Skip if SEO plugin handles archive content
    if ( function_exists( 'lm_seo_core_active' ) ) return $title;

    if (is_category() || is_tag()) {
        $term_id = get_queried_object_id();
        $chapeau = get_field('chapeau', 'category_' . $term_id);
        $chapeau_etiquette = get_field('chapeau', 'post_tag_' . $term_id);

        if (!empty($chapeau)) {
            echo '<div class="acf-chapeau">' . wp_kses_post($chapeau) . '</div>';
        } elseif (!empty($chapeau_etiquette)) {
            echo '<div class="acf-chapeau">' . wp_kses_post($chapeau_etiquette) . '</div>';
        } else {
            return;
        }
    }

    return $title;
});

/**
 * Add ACF content and FAQ fields after archive loops
 *
 * Displays the 'contenu' and 'faq' ACF fields after category/tag archive loops.
 *
 * @since 2.0.0
 */
add_filter('generate_after_loop', function ($title) {
    // Skip if SEO plugin handles archive content
    if ( function_exists( 'lm_seo_core_active' ) ) return $title;

    if (is_category() || is_tag()) {
        $term_id = get_queried_object_id();
        $contenu = get_field('contenu', 'category_' . $term_id);
        $contenu_etiquette = get_field('contenu', 'post_tag_' . $term_id);

        $faq = get_field('faq', 'category_' . $term_id);
        $faq_etiquette = get_field('faq', 'post_tag_' . $term_id);

        if (!empty($contenu)) {
            echo '<div class="acf-contenu">' . wp_kses_post($contenu) . '</div>';
        } elseif (!empty($contenu_etiquette)) {
            echo '<div class="acf-contenu">' . wp_kses_post($contenu_etiquette) . '</div>';
        }

        if (!empty($faq)) {
            echo '<div class="acf-contenu">' . wp_kses_post($faq) . '</div>';
        } elseif (!empty($faq_etiquette)) {
            echo '<div class="acf-contenu">' . wp_kses_post($faq_etiquette) . '</div>';
        }
    }

    return $title;
});
