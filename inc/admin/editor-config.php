<?php
/**
 * Editor Configuration
 *
 * Block editor and publishing validations.
 *
 * @package Labomaison
 * @subpackage Admin
 * @since 2.0.0
 *
 * Functions in this file:
 * - check_category_before_publishing()
 *
 * Dependencies: None
 * Load Priority: 3 (Admin only)
 * Risk Level: MEDIUM
 *
 * Migrated from: functions.php L649-683
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// PUBLISHING VALIDATIONS
// =============================================================================

/**
 * Check category before publishing a post
 *
 * Prevents publishing posts without a category or with the 'Blog' category.
 *
 * @since 2.0.0
 * @param WP_Post $prepared_post The prepared post object
 * @param WP_REST_Request $request The REST request
 * @return WP_Post|WP_Error The post object or error
 */
function check_category_before_publishing($prepared_post, $request) {
    // Autosaves et brouillons peuvent ne pas exposer post_status : on sort sans rien faire
    if (!is_object($prepared_post) || !property_exists($prepared_post, 'post_status')) {
        return $prepared_post;
    }

    $post_id = isset($prepared_post->ID) ? $prepared_post->ID : null;

    if ($prepared_post->post_status === 'publish') {
        // L'ID de la catégorie 'Blog'
        $blog_category_id = get_cat_ID('Blog'); // Utilisez le nom exact de votre catégorie "Blog"

        // Récupérer les catégories de l'article
        $categories = wp_get_post_categories($post_id);

        // Vérifier si aucune catégorie n'est sélectionnée ou si la catégorie 'Blog' est sélectionnée
        if (empty($categories)) {
            return new WP_Error(
                'rest_post_invalid_category',
                __("Vous devez sélectionner au moins une catégorie avant de pouvoir publier cet article.", 'text-domain'),
                array('status' => 400)
            );
        } elseif (in_array($blog_category_id, $categories)) {
            return new WP_Error(
                'rest_post_invalid_category',
                __("Vous ne pouvez pas publier un article dans la catégorie 'Blog'. Veuillez choisir une autre catégorie.", 'text-domain'),
                array('status' => 400)
            );
        }
    }

    return $prepared_post;
}
add_filter('rest_pre_insert_post', 'check_category_before_publishing', 10, 2);
