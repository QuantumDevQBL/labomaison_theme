<?php
/**
 * Admin List Columns
 *
 * Custom admin list columns and taxonomy filters for CPTs.
 *
 * @package Labomaison
 * @subpackage Admin
 * @since 2.0.0
 *
 * Functions in this file:
 * - Test CPT Marque column
 * - Taxonomy dropdown filters
 * - Parse query for taxonomy filtering
 * - update_last_updated_test_category()
 *
 * Dependencies: ACF
 * Load Priority: 3 (Admin only)
 * Risk Level: MEDIUM
 *
 * Migrated from: functions.php L130-209, L238-257
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// TEST CPT ADMIN COLUMNS
// =============================================================================

/**
 * Add Marque column to test CPT admin list
 *
 * @since 2.0.0
 */
add_filter('manage_test_posts_columns', function ($columns) {
    $columns['marque'] = __('Marque');
    return $columns;
});

/**
 * Populate Marque column content for test CPT
 *
 * @since 2.0.0
 */
add_action('manage_test_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'marque':

            $marque = get_field('marque', $post_id);
            $edit_link = get_edit_post_link($marque);

            // Si le champ renvoie un seul ID de post
            if (is_numeric($marque)) {
                $edit_link = get_edit_post_link($marque);
                $marque_name = get_the_title($marque);
                echo "<a href='{$edit_link}'>{$marque_name}</a>";
            } elseif (is_array($marque) && !empty($marque)) {
                $links = array_map(function ($id) {
                    $edit_link = get_edit_post_link($id);
                    $marque_name = get_the_title($id);
                    return "<a href='{$edit_link}'>{$marque_name}</a>";
                }, $marque);
                echo implode(', ', $links);
            } else {
                echo 'Aucune marque';
            }
            break;
    }
}, 10, 2);

// =============================================================================
// TAXONOMY DROPDOWN FILTERS
// =============================================================================

/**
 * Add taxonomy dropdown filters to test CPT admin list
 *
 * Adds categorie_test and etiquette-test dropdowns for filtering.
 *
 * @since 2.0.0
 */
add_action('restrict_manage_posts', function ($post_type) {
    if ('test' === $post_type) {
        $taxonomies = ['categorie_test', 'etiquette-test']; // Target taxonomies.

        foreach ($taxonomies as $tax_slug) {
            $tax_obj = get_taxonomy($tax_slug);
            if (!$tax_obj) continue;

            wp_dropdown_categories([
                'show_option_all' => "Show All {$tax_obj->labels->name}",
                'taxonomy'        => $tax_slug,
                'name'            => $tax_slug,
                'orderby'         => 'name',
                'selected'        => isset($_GET[$tax_slug]) ? $_GET[$tax_slug] : '',
                'hierarchical'    => true,
                'depth'           => 3,
                'show_count'      => false,
                'hide_empty'      => false,
                'value_field'     => 'slug', // Use slug as the option value.
            ]);
        }
    }
});

/**
 * Parse taxonomy query for admin filter dropdowns
 *
 * @since 2.0.0
 */
add_filter('parse_query', function ($query) {
    global $pagenow;

    if ('edit.php' === $pagenow && 'test' === $query->query_vars['post_type'] && is_admin()) {
        $tax_query = []; // Initialize taxonomy query array.

        foreach (['categorie_test', 'etiquette-test'] as $tax_slug) {
            if (!empty($_GET[$tax_slug]) && $_GET[$tax_slug] != '0') { // Check for non-empty slug.
                $tax_query[] = [
                    'taxonomy' => $tax_slug,
                    'field'    => 'slug',
                    'terms'    => [$_GET[$tax_slug]],
                ];
            }
        }

        if (!empty($tax_query)) {
            if (count($tax_query) > 1) {
                $tax_query['relation'] = 'AND';
            }
            $query->set('tax_query', $tax_query);
        }
    }
});

// =============================================================================
// CATEGORY TRACKING
// =============================================================================

/**
 * Track last updated test category
 *
 * Saves the category ID of the last updated test post for
 * cache invalidation or display purposes.
 *
 * @since 2.0.0
 * @param int $post_id Post ID being saved
 * @return void
 */
function update_last_updated_test_category($post_id)
{
    // Vérifiez si c'est bien le post type 'test'
    if (get_post_type($post_id) !== 'test') {
        return;
    }

    // Obtenez les catégories liées au post
    $categories = get_the_terms($post_id, 'categorie_test');
    if (empty($categories)) {
        return;
    }

    // Prenez la première catégorie (ou une autre logique selon vos besoins)
    $category = $categories[0];

    // Sauvegardez l'ID de la dernière catégorie mise à jour dans les options
    update_option('last_updated_test_category_id', $category->term_id);
}
add_action('save_post', 'update_last_updated_test_category');
