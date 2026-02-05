<?php
/**
 * WP Grid Builder Integration
 *
 * All hooks for WP Grid Builder plugin.
 *
 * @package Labomaison
 * @subpackage Integrations
 * @since 2.0.0
 *
 * Functions in this file:
 * - generateblocks_query_loop_args filter
 * - wp_grid_builder/blocks (affilizz button)
 * - wp_grid_builder/grid/the_object (author, category)
 * - grid_query_related_products_fill() [Grid 23]
 * - grid_query_related_products_test_fill() [Grid 24]
 * - exclude_empty_content_or_no_associations() [Grid 29]
 * - Grid 6, 26, 30 query modifications
 * - wpgb_lazy_load disable
 *
 * Dependencies: WP Grid Builder plugin, ACF
 * Load Priority: 5
 * Condition: class_exists('WP_Grid_Builder')
 * Risk Level: HIGH
 *
 * Migrated from: functions.php L76-110, L114-234, L262-426, L723-757, L928-939, L2785-2798
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// GENERATEBLOCKS QUERY INTEGRATION
// =============================================================================

/**
 * Modify GenerateBlocks query loop args for WPGB
 *
 * Adds pagination support and WPGB grid integration.
 *
 * @since 2.0.0
 */
add_filter('generateblocks_query_loop_args', function ($query_args, $attributes) {

    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $query_args['paged'] = $paged;

    if (!is_admin() && !empty($attributes['className']) && strpos($attributes['className'], 'wpgb-query') !== false) {
        $query_args['wp_grid_builder'] = 'wpgb-content-2';
    }

    return $query_args;
}, 10, 2);

/**
 * Multi-post-type query loop support
 */
add_filter('generateblocks_query_loop_args', function ($query_args, $attributes) {
    if (!empty($attributes['className']) && strpos($attributes['className'], 'multi-post-type') !== false) {
        return array_merge($query_args, array(
            'post_type' => array('post', 'test'),
        ));
    }
    return $query_args;
}, 10, 2);

// =============================================================================
// CUSTOM BLOCKS
// =============================================================================

/**
 * Register Affilizz button block for WP Grid Builder
 *
 * @since 2.0.0
 */
add_filter('wp_grid_builder/blocks', function($blocks) {
    $blocks['affilizz_button'] = [
        'name' => __('Bouton Affilizz', 'text-domain'),
        'render_callback' => function() {
            global $post;
            $post = wpgb_get_post();
            setup_postdata($post);

            // Récupérer le contenu du champ ACF
            $bouton_affiliz = get_field('bouton_affiliz', $post->ID);

            if ($bouton_affiliz) {
                // Afficher le contenu HTML sans échappement, encapsulé dans une div avec classe
                echo '<div class="affilizz-container">' . $bouton_affiliz . '</div>';
            }

            wp_reset_postdata();
        },
    ];

    return $blocks;
});

// =============================================================================
// GRID OBJECT MODIFICATIONS
// =============================================================================

/**
 * Grid 19: Author featured image from ACF
 *
 * @since 2.0.0
 */
add_filter('wp_grid_builder/grid/the_object', function ($object) {
    $grid = wpgb_get_grid_settings();

    if (19 === (int) $grid->id) {
        $image = get_field('featured_image', 'user_' . $object->ID);

        if (!empty($image)) {
            $object->post_thumbnail = $image;
        }
    }

    return $object;
}, 10, 1);

/**
 * Grid 10: Category featured image from ACF
 *
 * @since 2.0.0
 */
add_filter('wp_grid_builder/grid/the_object', function ($object) {
    $grid = wpgb_get_grid_settings();

    // Vérifiez si nous sommes dans le grid ID souhaité
    if (10 === (int) $grid->id) {

        // ID en dur pour la catégorie 'categorie_test' avec tag_ID 195
        $category_id = $object->term_id;
        $taxonomy = 'categorie_test';

        // Récupération du champ ACF pour la catégorie spécifique
        // Assurez-vous que le champ 'featured' est configuré correctement dans ACF
        $image = get_field('featured', $taxonomy . '_' . $category_id);

        // S'il existe une URL d'image, affectez-la à l'objet
        if (!empty($image)) {
            $object->post_thumbnail = $image;
        }
    }

    return $object;
}, 10, 1);

// =============================================================================
// GRID QUERY MODIFICATIONS
// =============================================================================

/**
 * Grid 23: Related products with fill from same category
 *
 * Shows ACF-selected products first, then fills with posts from same category.
 *
 * @since 2.0.0
 * @param array $query_args Query arguments
 * @param int $grid_id Grid ID
 * @return array Modified query arguments
 */
function grid_query_related_products_fill($query_args, $grid_id)
{
    if ('23' !== (string) $grid_id) {
        return $query_args;
    }

    global $post;

    // 1. Récupérer les produits associés avec ACF
    $associated_products = get_field('produit_associe', $post->ID);

    // 2. Récupérer les IDs des produits associés
    $product_ids = !empty($associated_products) ? array_map(function ($product) {
        return is_object($product) ? $product->ID : $product; // Ajustement pour gérer les objets ou les IDs
    }, $associated_products) : [];

    // Calcul du nombre de produits supplémentaires nécessaires pour atteindre 6
    $count_needed = 6 - count($product_ids);

    // 3. Ajouter des produits supplémentaires si nécessaire
    if ($count_needed > 0) {
        // Récupérer les catégories de l'article
        $article_categories = get_the_category($post->ID);
        $target_category_ids = [];
        foreach ($article_categories as $category) {
            // Trouver la correspondance dans la catégorie de test
            $test_term = get_term_by('slug', $category->slug, 'categorie_test');
            if ($test_term) {
                $target_category_ids[] = $test_term->term_id;
                break; // On arrête après avoir trouvé une correspondance
            }
        }

        // Récupérer des posts supplémentaires dans la catégorie de test si besoin
        if (!empty($target_category_ids)) {
            $additional_args = [
                'post_type' => 'test',
                'tax_query' => [[
                    'taxonomy' => 'categorie_test',
                    'field'    => 'term_id',
                    'terms'    => $target_category_ids,
                ]],
                'posts_per_page' => $count_needed,
                'fields' => 'ids',
                'post__not_in' => $product_ids, // Exclure les produits déjà sélectionnés
            ];

            // Obtenir les produits supplémentaires
            $additional_posts = get_posts($additional_args);

            if (!empty($additional_posts)) {
                $product_ids = array_merge($product_ids, $additional_posts);
                $count_needed = 6 - count($product_ids);
            }
        }
    }

    // 4. Si on a encore besoin de produits pour compléter
    if ($count_needed > 0) {
        $additional_args = [
            'post_type'      => 'test',
            'posts_per_page' => $count_needed,
            'fields'         => 'ids',
            'post__not_in'   => $product_ids, // Exclure les produits déjà sélectionnés
        ];

        // Obtenir des posts supplémentaires sans filtrer par catégorie
        $additional_posts = get_posts($additional_args);

        if (!empty($additional_posts)) {
            $product_ids = array_merge($product_ids, $additional_posts);
        }
    }

    // 5. Mise à jour des arguments de requête pour inclure tous les produits (associés et supplémentaires)
    $query_args['post__in'] = $product_ids;
    $query_args['orderby'] = 'post__in';
    $query_args['posts_per_page'] = 6; // Afficher 6 produits maximum

    return $query_args;
}
add_filter('wp_grid_builder/grid/query_args', 'grid_query_related_products_fill', 10, 2);

/**
 * Grid 6: Sort by note_globale
 *
 * @since 2.0.0
 */
add_filter('wp_grid_builder/grid/query_args', function ($query_args, $grid_id) {
    // Target a specific grid by its ID
    if (6 === $grid_id) {
        // Add or modify query arguments
        $query_args['meta_key'] = 'note_globale'; // Assuming 'note_globale' is the correct meta key
        $query_args['orderby'] = 'meta_value_num'; // Order by the numerical value of the meta key
        $query_args['order'] = 'DESC'; // Sort from highest to lowest
    }

    return $query_args;
}, 10, 2);

/**
 * Grid 24: Related products for test posts
 *
 * @since 2.0.0
 * @param array $query_args Query arguments
 * @param int $grid_id Grid ID
 * @return array Modified query arguments
 */
function grid_query_related_products_test_fill($query_args, $grid_id)
{
    if ('24' !== (string) $grid_id) {
        return $query_args;
    }

    global $post;
    $associated_products = get_field('produit_associe', $post->ID);
    $product_ids = !empty($associated_products) ? array_map(function ($product) {
        return $product->ID;
    }, $associated_products) : [];

    $count_needed = 6 - count($product_ids);

    if ($count_needed > 0) {
        $test_terms = [];
        if ('test' === get_post_type($post)) {
            // Pour les singles cpt test, utiliser directement sa ou ses catégories
            $test_terms = get_the_terms($post->ID, 'categorie_test');
        } else {
            // Pour les posts, trouver la catégorie de test correspondante aux catégories d'articles
            $article_categories = get_the_category($post->ID);
            foreach ($article_categories as $category) {
                $test_term = get_term_by('slug', $category->slug, 'categorie_test');
                if ($test_term) {
                    $test_terms[] = $test_term;
                    break; // Utilisez uniquement la première correspondance pour simplifier
                }
            }
        }

        if (!empty($test_terms)) {
            $test_term_ids = wp_list_pluck($test_terms, 'term_id');
            $additional_args = [
                'post_type' => 'test',
                'tax_query' => [[
                    'taxonomy' => 'categorie_test',
                    'field'    => 'term_id',
                    'terms'    => $test_term_ids,
                ]],
                'posts_per_page' => $count_needed,
                'fields' => 'ids',
                'post__not_in' => $product_ids, // Exclude already selected products
            ];

            $additional_posts = get_posts($additional_args);

            if (!empty($additional_posts)) {
                $product_ids = array_merge($product_ids, $additional_posts);
            }
        }
    }

    $query_args['post__in'] = $product_ids;
    $query_args['orderby'] = 'post__in';
    $query_args['posts_per_page'] = count($product_ids); // Ensure the grid shows the exact number of products

    return $query_args;
}
add_filter('wp_grid_builder/grid/query_args', 'grid_query_related_products_test_fill', 10, 2);

/**
 * Grid 29: Exclude posts without content or associations
 *
 * @since 2.0.0
 * @param array $query_args Query arguments
 * @param int $grid_id Grid ID
 * @return array Modified query arguments
 */
function exclude_empty_content_or_no_associations($query_args, $grid_id) {
    if ('29' !== (string) $grid_id) {
        return $query_args;
    }

    // Ajouter une condition pour exclure les posts sans contenu
    $query_args['meta_query'] = array(
        'relation' => 'OR',
        array(
            'key'     => 'post_content',
            'value'   => '',
            'compare' => '!=',
        ),
        array(
            'key'     => 'post_content',
            'compare' => 'EXISTS',
        ),
    );

    // Ajouter une condition pour exclure les posts sans tests ou articles associés
    $query_args['meta_query'][] = array(
        'relation' => 'OR',
        array(
            'key'     => 'produit_associe',
            'compare' => 'EXISTS',
        ),
        array(
            'key'     => 'articles_associes',
            'compare' => 'EXISTS',
        ),
    );

    return $query_args;
}
add_filter('wp_grid_builder/grid/query_args', 'exclude_empty_content_or_no_associations', 10, 2);

/**
 * Grid 26: Filter by author on author pages
 *
 * @since 2.0.0
 */
add_filter('wp_grid_builder/grid/query_args', function ($query_args, $grid_id) {
    if (26 === $grid_id && is_author()) {
        $author_id = get_queried_object_id();

        // Auteur courant
        $query_args['author'] = $author_id;
    }

    return $query_args;
}, 10, 2);

/**
 * Grid 30: Sort by post_views_7d (weekly views)
 *
 * IMPORTANT: pas de meta_query "EXISTS" (sinon grid vide au début)
 * Fallback: si pas de post_views_7d encore, tri par modified.
 *
 * @since 2.0.0
 */
add_filter('wp_grid_builder/grid/query_args', function ($query_args, $grid_id) {
    if (30 === (int) $grid_id) {
        $query_args['meta_key'] = 'post_views_7d';
        $query_args['orderby']  = [
            'meta_value_num' => 'DESC',
            'modified'       => 'DESC',
        ];
        $query_args['order'] = 'DESC';

        // On ne filtre pas par existence du meta => jamais vide
        unset($query_args['date_query']);
    }
    return $query_args;
}, 50, 2);

// =============================================================================
// PERFORMANCE SETTINGS
// =============================================================================

/**
 * Disable WPGB lazy loading (using native lazy load instead)
 *
 * @since 2.0.0
 */
add_filter('wpgb_lazy_load', '__return_false');
