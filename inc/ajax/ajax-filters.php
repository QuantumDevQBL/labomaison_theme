<?php
/**
 * AJAX Filter Handlers
 *
 * AJAX handlers for load more and filtering.
 *
 * @package Labomaison
 * @subpackage AJAX
 * @since 2.0.0
 *
 * Functions in this file:
 * - load_more_articles()
 * - load_more_articles_by_marque()
 *
 * Dependencies: utilities/helpers.php (render_post_item_for_marque)
 * Load Priority: 7 (AJAX only)
 * Risk Level: HIGH
 *
 * Migrated from: shortcode_list.php L1701-1775
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// LOAD MORE ARTICLES BY MARQUE
// =============================================================================

/**
 * Query and render additional articles for a given marque
 *
 * Returns rendered HTML for paginated article cards
 * filtered by marque ACF relationship field.
 *
 * @since 2.0.0
 * @param int $marque_id The marque post ID
 * @param int $paged Page number
 * @param array $exclude_ids Post IDs to exclude
 * @return string HTML output
 */
function load_more_articles_by_marque($marque_id, $paged, $exclude_ids) {
    // Créer une requête pour récupérer les articles associés à cette marque
    $args = [
        'post_type' => 'post',
        'meta_query' => [
            [
                'key' => 'marque', // Champ de relation
                'value' => '"' . $marque_id . '"', // ID de la marque courante
                'compare' => 'LIKE',
            ]
        ],
        'posts_per_page' => 9, // Nombre d'articles par page
        'paged' => $paged,
        'post_status' => 'publish',
        'post__not_in' => $exclude_ids // Exclure les articles déjà affichés
    ];

    $query = new WP_Query($args);

    // Initialiser la variable de sortie
    $output = '';

    if ($query->have_posts()) {
        // Boucle à travers les articles et les afficher
        while ($query->have_posts()) {
            $query->the_post();
            $exclude_ids[] = get_the_ID(); // Ajouter l'ID de l'article à la liste des exclus
            $output .= render_post_item_for_marque(get_the_ID());
        }

        $output .= '<div class="no-more-posts" style="margin-top: 35px;"><a href="' . get_post_type_archive_link('test') . '">Voir tous les tests</a></div>';
    }

    // Réinitialiser les données de post
    wp_reset_postdata();

    return $output;
}

/**
 * AJAX handler for load more articles
 *
 * @since 2.0.0
 * @return void
 */
function load_more_articles() {
    $marque_id = intval($_POST['marque_id']);
    $paged = intval($_POST['paged']);
    $exclude = isset($_POST['exclude']) ? array_map('intval', explode(',', $_POST['exclude'])) : [];

    echo load_more_articles_by_marque($marque_id, $paged, $exclude);
    wp_die();
}
add_action('wp_ajax_load_more_articles', 'load_more_articles');
add_action('wp_ajax_nopriv_load_more_articles', 'load_more_articles');
