<?php
/**
 * Query Modifications
 *
 * Filters on pre_get_posts to modify WordPress queries.
 *
 * @package Labomaison
 * @subpackage Hooks
 * @since 2.0.0
 *
 * Functions in this file:
 * - adjust_main_query_based_on_ratings()
 * - add_custom_post_types_to_rss_feed()
 * - pm_change_author_base()
 *
 * Dependencies: None
 * Load Priority: 4
 * Risk Level: HIGH
 *
 * Migrated from: functions.php L548-603, L606-612, L715-721
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// MAIN QUERY MODIFICATIONS
// =============================================================================

/**
 * Adjust main query on test taxonomy archives
 *
 * On categorie_test and etiquette_test taxonomy archives:
 * - If posts with note_globale > 0 exist, sort by note_globale DESC
 * - Otherwise, sort by post_views_count DESC, then modified date
 *
 * @since 2.0.0
 * @param WP_Query $query The WP_Query object
 * @return void
 */
function adjust_main_query_based_on_ratings($query)
{
    if (!is_admin() && $query->is_main_query() && (is_tax('categorie_test') || is_tax('etiquette_test'))) {
        // Premièrement, déterminons si des posts avec une note_globale > 0 existent dans cette taxonomie
        $has_rated_posts = false; // Supposez initialement qu'il n'y a pas de posts avec note > 0

        $rated_posts_query = new WP_Query(array(
            'post_type' => 'test', // Assurez-vous que c'est le bon type de post
            'tax_query' => array(
                array(
                    'taxonomy' => 'categorie_test',
                    'field'    => 'term_id',
                    'terms'    => get_queried_object_id(),
                ),
            ),
            'meta_query' => array(
                array(
                    'key'     => 'note_globale',
                    'value'   => 0,
                    'compare' => '>',
                    'type'    => 'NUMERIC',
                ),
            ),
            'posts_per_page' => 1,
        ));

        if ($rated_posts_query->have_posts()) {
            $has_rated_posts = true;
        }

        // Si des posts avec une note_globale > 0 existent, on ajuste la requête principale pour trier par note_globale d'abord
        if ($has_rated_posts) {
            $query->set('meta_key', 'note_globale');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'DESC');
        } else {
            // Sinon, on trie par post_views_count si disponible, sinon par date
            $query->set('meta_query', array(
                'relation' => 'OR',
                array(
                    'key' => 'post_views_count',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key' => 'post_views_count',
                    'compare' => 'NOT EXISTS',
                ),
            ));
            $query->set('orderby', array(
                'post_views_count' => 'DESC',
                'modified' => 'DESC'
            ));
        }
    }
}
add_action('pre_get_posts', 'adjust_main_query_based_on_ratings');

// =============================================================================
// RSS FEED MODIFICATIONS
// =============================================================================

/**
 * Include custom post types in RSS feed
 *
 * Adds 'test' post type to the main RSS feed alongside regular posts.
 *
 * @since 2.0.0
 * @param WP_Query $query The WP_Query object
 * @return WP_Query Modified query
 */
function add_custom_post_types_to_rss_feed($query) {
    if ($query->is_feed() && !isset($query->query_vars['post_type'])) {
        $query->set('post_type', array('post', 'test'));
    }
    return $query;
}
add_filter('pre_get_posts', 'add_custom_post_types_to_rss_feed');

// =============================================================================
// AUTHOR URL REWRITE
// =============================================================================

/**
 * Change author base URL to /redacteur/
 *
 * Changes default /author/ to /redacteur/ for author archive URLs.
 *
 * @since 2.0.0
 * @return void
 */
function pm_change_author_base()
{
    global $wp_rewrite;
    $wp_rewrite->author_structure = 'redacteur/%author%';
}
add_action('init', 'pm_change_author_base', 10);
