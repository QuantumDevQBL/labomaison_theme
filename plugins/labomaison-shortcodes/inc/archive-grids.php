<?php
/**
 * Archive Grid Shortcodes
 *
 * Shortcodes for archive listings and paginated grids.
 *
 * @package Labomaison
 * @subpackage Shortcodes
 * @since 2.0.0
 *
 * Shortcodes:
 * - [latest_articles]
 * - [latest_news]
 * - [marques_list]
 * - [marque_content_list]
 * - [author_content_list]
 * - [lm_terms_grid]
 * - [lm_tests_grid]
 * - [produits_populaires]
 * - [afficher_articles_pour_marque]
 *
 * Helper Functions:
 * - generate_card_html()
 * - clear_marques_cache()
 * - marques_display_pagination()
 * - lm_pagination_markup_compat()
 * - lm_brand_listing_item_compat()
 * - lm_get_test_card_title()
 * - lm_render_related_test_card()
 * - lm_redirect_legacy_page_param()
 * - Pagination rewrite rules
 * - pre_get_posts hooks for author/taxonomy archives
 *
 * Dependencies: WP_Query, ACF, utilities/helpers.php
 * Load Priority: 6
 * Risk Level: MEDIUM-HIGH
 *
 * Migrated from: shortcode_list.php L1679-1698, L2149-2238, L2467-2515,
 *                L3177-3470, L3472-3700, L3704-3905, L3931-4574
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

// generate_card_html() is in utilities/helpers.php (loaded first)

// --- clear_marques_cache: invalidate marques transient ---
function clear_marques_cache() {
    delete_transient('marques_valid_ids');
}
add_action('save_post', 'clear_marques_cache');
add_action('acf/save_post', 'clear_marques_cache', 20);

// --- marques_display_pagination: pagination for marques list ---
if ( ! function_exists( 'marques_display_pagination' ) ) {
function marques_display_pagination($query) {
    $total_pages = $query->max_num_pages;
    $current_page = max(1, get_query_var('paged'));

    if ($total_pages <= 1) return;

    $pages_to_show = 3;
    $half_pages = floor($pages_to_show / 2);

    echo '<div class="marques-pagination">';

    // Page précédente
    if ($current_page > 1) {
        echo '<a href="' . get_pagenum_link($current_page - 1) . '" class="pagination-prev">«</a>';
    }

    // Première page
    if ($current_page > $half_pages + 1) {
        echo '<a href="' . get_pagenum_link(1) . '" class="page-link">1</a>';
        if ($current_page > $half_pages + 2) {
            echo '<span class="pagination-dots">...</span>';
        }
    }

    // Pages numérotées autour de la page courante
    $start_page = max(1, $current_page - $half_pages);
    $end_page = min($total_pages, $current_page + $half_pages);

    if ($start_page <= 1) {
        $end_page = min($total_pages, $pages_to_show);
    }

    if ($end_page >= $total_pages) {
        $start_page = max(1, $total_pages - $pages_to_show + 1);
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<a class="current-page">' . $i . '</a>';
        } else {
            echo '<a href="' . get_pagenum_link($i) . '" class="page-link">' . $i . '</a>';
        }
    }

    // Dernière page
    if ($current_page < $total_pages - $half_pages) {
        if ($current_page < $total_pages - $half_pages - 1) {
            echo '<span class="pagination-dots">...</span>';
        }
        echo '<a href="' . get_pagenum_link($total_pages) . '" class="page-link">' . $total_pages . '</a>';
    }

    // Page suivante
    if ($current_page < $total_pages) {
        echo '<a href="' . get_pagenum_link($current_page + 1) . '" class="pagination-next">»</a>';
    }

    echo '</div>';
}
}

// lm_pagination_markup_compat() is in utilities/helpers.php (loaded first)

// --- lm_brand_listing_item_compat: BEM + legacy markup for brand/author listings ---
if ( ! function_exists( 'lm_brand_listing_item_compat' ) ) {
function lm_brand_listing_item_compat() {
    $post_id   = get_the_ID();
    $post_type = get_post_type($post_id);
    $url       = get_permalink($post_id);
    $title     = get_the_title($post_id);

    // --- Term (cat/tax) ---
    $term_name = '';
    $term_link = '';
    $term_slug = '';

    if ($post_type === 'post') {
        $cats = get_the_category($post_id);
        if (!empty($cats) && !is_wp_error($cats)) {
            $term_name = $cats[0]->name;
            $term_slug = $cats[0]->slug;
            $term_link = get_category_link($cats[0]->term_id);
        }
    } else {
        $possible_taxonomies = array(
            'test_category',
            'test_cat',
            'category_test',
            'tests_category',
            'categorie_test',
            'categories_test',
        );

        foreach ($possible_taxonomies as $tax) {
            if (taxonomy_exists($tax)) {
                $terms = get_the_terms($post_id, $tax);
                if (!empty($terms) && !is_wp_error($terms)) {
                    $term_name = $terms[0]->name;
                    $term_slug = $terms[0]->slug;
                    $term_link = get_term_link($terms[0]);
                    break;
                }
            }
        }
    }

    // --- Media ---
    if (has_post_thumbnail($post_id)) {
        $thumb_id = get_post_thumbnail_id($post_id);
        $media_html = wp_get_attachment_image(
            $thumb_id,
            'medium',
            false,
            array(
                'class'    => 'query_loop_image lm-card__img',
                'loading'  => 'lazy',
                'decoding' => 'async',
            )
        );
    } else {
        $media_html = '<div class="post-list-placeholder lm-card__placeholder" aria-hidden="true">' . esc_html(mb_substr($title, 0, 2)) . '</div>';
    }

    // --- Datetime ---
    $date_str = get_the_date('d/m/Y', $post_id);
    $time_str = get_the_time('H:i', $post_id);

    $article_classes = array_merge(
        array(
            'dynamic-content-template',
            'resize-featured-image',
            'post-type-' . $post_type,
            'lm-card',
            'lm-card--' . $post_type,
        ),
        get_post_class('', $post_id)
    );

    echo '<article id="post-' . esc_attr($post_id) . '" class="' . esc_attr(implode(' ', $article_classes)) . '">';
        echo '<div class="gb-grid-column gb-grid-column-a223c2a2 gb-query-loop-item lm-card__inner">';
            echo '<div class="gb-container gb-container-a223c2a2 post_archive_container">';
                echo '<div class="gb-container gb-container-3a861d0a lm-card__media">';
                    echo '<figure class="gb-block-image gb-block-image-f0f7190a">';
                        echo '<a class="lm-card__mediaLink" href="' . esc_url($url) . '" aria-label="' . esc_attr($title) . '">';
                            echo $media_html;
                        echo '</a>';
                    echo '</figure>';
                echo '</div>';

                echo '<div class="gb-container gb-container-a84b6e14 content_container_query_loop lm-card__body">';

                    if (!empty($term_name) && !empty($term_link)) {
                        $term_class = !empty($term_slug) ? ' term-' . sanitize_html_class($term_slug) : '';
                        echo '<span class="test-category-link term_absolute post-term-item' . esc_attr($term_class) . ' lm-card__term">';
                            echo '<a class="lm-card__termLink" href="' . esc_url($term_link) . '">' . esc_html($term_name) . '</a>';
                        echo '</span> ';
                    }

                    echo '<p class="gb-headline gb-headline-659923e8 related-content__card-title related-content__card-title_bold gb-headline-text lm-card__title">';
                        echo '<a class="lm-card__titleLink" href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
                    echo '</p>';

                    echo '<span class="datetime lm-card__datetime">Publié le ' . esc_html($date_str) . ' à ' . esc_html($time_str) . '</span>';

                echo '</div>';
            echo '</div>';
        echo '</div>';
    echo '</article>';
}
}

// lm_get_test_card_title() is in utilities/helpers.php (loaded first)
// lm_render_related_test_card() is in utilities/helpers.php (loaded first)

// =============================================================================
// PAGINATION REWRITE RULES & REDIRECTS
// =============================================================================

// --- Redirect ?_page=N to /comparatifs/page/N/ ---
function lm_redirect_legacy_page_param() {

    if (!is_page('comparatifs')) {
        return;
    }

    if (!isset($_GET['_page'])) {
        return;
    }

    $page = (int) $_GET['_page'];

    if ($page < 2) {
        wp_safe_redirect(home_url('/comparatifs/'), 301);
        exit;
    }

    wp_safe_redirect(
        home_url("/comparatifs/page/{$page}/"),
        301
    );
    exit;
}
add_action('template_redirect', 'lm_redirect_legacy_page_param');

// --- Rewrite: /comparatifs/page/N/ ---
function lm_register_comparatifs_pagination_rewrite() {
    add_rewrite_rule(
        '^comparatifs/page/([0-9]+)/?$',
        'index.php?pagename=comparatifs&paged=$matches[1]',
        'top'
    );
}
add_action('init', 'lm_register_comparatifs_pagination_rewrite');

// --- Rewrite: /dernier-test/page/N/ ---
function lm_register_dernier_test_pagination_rewrite() {
    add_rewrite_rule(
        '^dernier-test/page/([0-9]+)/?$',
        'index.php?pagename=dernier-test&paged=$matches[1]',
        'top'
    );
}
add_action('init', 'lm_register_dernier_test_pagination_rewrite');

// --- Rewrite: /cuisine/{term}/page/N/ ---
function lm_register_categorie_test_pagination_rewrite() {
    add_rewrite_rule(
        '^cuisine/(.+?)/page/([0-9]+)/?$',
        'index.php?categorie_test=$matches[1]&paged=$matches[2]',
        'top'
    );
}
add_action('init', 'lm_register_categorie_test_pagination_rewrite');

// =============================================================================
// PRE_GET_POSTS HOOKS
// =============================================================================

// --- Fix 404 on author archive pagination ---
add_action('pre_get_posts', function($q) {
    if (is_admin() || !$q->is_main_query() || !$q->is_author()) return;

    $q->set('post_type', array('post', 'test'));
    $q->set('posts_per_page', 12);
    $q->set('orderby', 'date');
    $q->set('order', 'DESC');

    $q->set('post_status', array('publish'));
    $q->set('ignore_sticky_posts', true);
}, 9);

// --- Fix 404 on category/tax paginated archives ---
add_action('pre_get_posts', function (WP_Query $q) {

    if (is_admin() || !$q->is_main_query()) {
        return;
    }

    $paged = (int) get_query_var('paged');
    if ($paged < 2) {
        return;
    }

    $is_target_archive =
        is_category()
        || is_tax('categorie_test')
        || is_tax('etiquette-test');

    if (!$is_target_archive) {
        return;
    }

    $q->set('posts_per_page', 12);
    $q->set('post_status', 'publish');
    $q->set('ignore_sticky_posts', true);

}, 20);

// =============================================================================
// [latest_articles] - Latest posts grid
// =============================================================================

function display_latest_articles($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10
    ), $atts);
    $limit = intval($atts['limit']);

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $latest_posts = new WP_Query($args);

    if (!$latest_posts->have_posts()) {
        return '';
    }

    $output = '<div class="related-content latest-articles">';
		$output .= '
<span class="gb-headline-shortcode">
    <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">.st1{display:none}<path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
    <span>derniers articles</span>
</span>
';
    $output .= '<div class="related-content__grid">';

    while ($latest_posts->have_posts()) {
        $latest_posts->the_post();
        $output .= '<div class="related-content__card">';
        $output .= '<div class="related-content__image">';
        $output .= '<a href="' . get_permalink() . '">' . get_the_post_thumbnail(null, 'medium') . '</a>';
        $output .= '</div>';
        $output .= '<div class="related-content__content">';
        $output .= '<span class="related-content__card-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></span>';
        $output .= '<span class="related-content__date">Publié le ' . get_the_date() . '</span>';
        $output .= '</div>';
        $output .= '</div>';
    }

    $output .= '</div>';
    $output .= '</div>';

    wp_reset_postdata();
    return $output;
}
add_shortcode('latest_articles', 'display_latest_articles');

// =============================================================================
// [latest_news] - Latest news with deduplication
// =============================================================================

function display_latest_news($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10
    ), $atts);
    $limit = intval($atts['limit']);

    // Initialiser la variable globale
    initialize_displayed_news_ids();
    global $displayed_news_ids;

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'post__not_in' => $displayed_news_ids
    );
    $latest_news = new WP_Query($args);

    $output = '<div class="related-content latest-news">';
	$output .= '
 <span class="gb-headline-shortcode">
        <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">.st1{display:none}<path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
        <span>Dernières actualités</span>
    </span>
';
    $output .= '<div class="related-content__grid">';

    if ($latest_news->have_posts()) {
        while ($latest_news->have_posts()) {
            $latest_news->the_post();
            $thumbnail = get_the_post_thumbnail(null, 'medium');
            $title = get_the_title();
            $permalink = get_permalink();
            $date = get_the_date();
            $output .= generate_content_card($thumbnail, $title, $permalink, $date);
            $displayed_news_ids[] = get_the_ID();
        }
        wp_reset_postdata();
    }

    $output .= '</div></div>';

    // Mettre à jour la variable globale avec les nouveaux IDs affichés
    $GLOBALS['displayed_news_ids'] = $displayed_news_ids;

    return $output;
}
add_shortcode('latest_news', 'display_latest_news');

// =============================================================================
// [afficher_articles_pour_marque] - Articles for current marque CPT
// =============================================================================

function afficher_articles_lies_a_marque_courante() {
    if (!is_singular('marque')) {
        return '';
    }

    $marque_id = get_the_ID();

    $output = '<div id="related-news-container" data-marque-id="' . $marque_id . '">';

    $output .= load_more_articles_by_marque($marque_id, 1, []);

    $output .= '</div>';

    return $output;
}
add_shortcode('afficher_articles_pour_marque', 'afficher_articles_lies_a_marque_courante');

// =============================================================================
// [marques_list] - Paginated marques listing
// =============================================================================

function marques_pagination_shortcode() {
    ob_start();

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    // Utiliser le cache
    $cache_key = 'marques_valid_ids';
    $valid_marques_ids = get_transient($cache_key);

    if (false === $valid_marques_ids) {
        $valid_marques_ids = array();

        $post_types = array('post', 'test');

        foreach ($post_types as $post_type) {
            $posts_with_marque = get_posts(array(
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'marque',
                        'compare' => 'EXISTS'
                    )
                )
            ));

            foreach ($posts_with_marque as $post_id) {
                $marques_associees = get_field('marque', $post_id, false);
                if ($marques_associees && is_array($marques_associees)) {
                    $valid_marques_ids = array_merge($valid_marques_ids, $marques_associees);
                }
            }
        }

        $valid_marques_ids = array_unique(array_filter($valid_marques_ids));

        if (empty($valid_marques_ids)) {
            $valid_marques_ids = array(0);
        }

        set_transient($cache_key, $valid_marques_ids, 12 * HOUR_IN_SECONDS);
    }

    $args_paged = array(
        'post_type'      => 'marque',
        'posts_per_page' => 20,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'Desc',
        'post__in'       => $valid_marques_ids
    );

    $marques_query = new WP_Query($args_paged);

    echo '<div class="marques-container">';

    if ($marques_query->have_posts()) :
        echo '<div class="marques-grid">';
        while ($marques_query->have_posts()) : $marques_query->the_post();
            $marque_id = get_the_ID();
            $marque_title = get_the_title();
            $marque_excerpt = get_the_excerpt();
            $marque_link = get_permalink();
            $marque_thumbnail = get_the_post_thumbnail($marque_id, 'medium', array(
                'class' => 'marque-image',
                'alt' => $marque_title,
                'loading' => 'lazy'
            ));

            echo '<div class="marque-item">';
            echo '<div class="item">';

            echo '<div class="marque-image">';
            echo '<a href="' . esc_url($marque_link) . '" class="marque-link">';
            if ($marque_thumbnail) {
                echo $marque_thumbnail;
            } else {
                echo '<div class="marque-placeholder">' . substr($marque_title, 0, 2) . '</div>';
            }
            echo '</a>';
            echo '</div>';

            echo '<div class="marque-content">';
            echo '<h3 class="marque-title"><a href="' . esc_url($marque_link) . '" class="marque-link">' . esc_html($marque_title) . '</a></h3>';
            if ($marque_excerpt) {
                echo '<div class="marque-excerpt">' . wp_kses_post(wp_trim_words($marque_excerpt, 18, '...')) . '</div>';
            }
            echo '</div>';

            echo '</div>';
            echo '</div>';

        endwhile;
        echo '</div>';

        marques_display_pagination($marques_query);

    else :
        echo '<p>Aucune marque associée à des articles ou tests trouvée.</p>';
    endif;

    echo '</div>';

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('marques_list', 'marques_pagination_shortcode');

// =============================================================================
// [marque_content_list] - Content listing on single marque page
// =============================================================================

function marque_content_list_shortcode($atts) {
    if (!is_singular('marque')) {
        return '<p>Ce shortcode doit être utilisé sur une page de marque.</p>';
    }

    $current_marque_id = get_queried_object_id();
    if (!$current_marque_id) {
        return '<p>Marque introuvable.</p>';
    }

    $paged = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;

    $atts = shortcode_atts(array(
        'posts_per_page' => 12,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ), $atts);

    $args = array(
        'post_type'           => array('post', 'test'),
        'posts_per_page'      => (int) $atts['posts_per_page'],
        'paged'               => $paged,
        'orderby'             => $atts['orderby'],
        'order'               => $atts['order'],
        'ignore_sticky_posts' => true,
        'meta_query'          => array(
            array(
                'key'     => 'marque',
                'value'   => '"' . $current_marque_id . '"',
                'compare' => 'LIKE',
            )
        ),
    );

    $q = new WP_Query($args);

    ob_start();

    echo '<section class="marque-content-list lm-brandListing" id="marques-list">';

    if ($q->have_posts()) {

        echo '<div class="marque-posts-list lm-brandListing__grid" id="marque-list">';

        while ($q->have_posts()) {
            $q->the_post();
            lm_brand_listing_item_compat();
        }

        echo '</div>';

        // Pagination
        if ($q->max_num_pages > 1) {
            $base_url = remove_query_arg('paged');

            $links = paginate_links(array(
                'base'      => esc_url_raw(add_query_arg('paged', '%#%', $base_url)),
                'format'    => '',
                'current'   => $paged,
                'total'     => (int) $q->max_num_pages,
                'mid_size'  => 2,
                'prev_text' => '«',
                'next_text' => '»',
                'type'      => 'list',
            ));

            $links = lm_pagination_markup_compat($links);

            echo '<nav class="marque-posts-pagination lm-brandListing__pagination" aria-label="Pagination">';
            echo $links;
            echo '</nav>';
        }

    } else {
        echo '<p class="no-content lm-brandListing__empty">Aucun contenu trouvé pour cette marque.</p>';
    }

    echo '</section>';

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('marque_content_list', 'marque_content_list_shortcode');

// =============================================================================
// [author_content_list] - Author content listing with pagination
// =============================================================================

function author_content_list_shortcode($atts) {

    $atts = shortcode_atts(array(
        'author_id'      => 0,
        'posts_per_page' => 12,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'debug'          => 0,
    ), $atts);

    $is_author_archive = is_author();

    // Auteur cible
    if ((int) $atts['author_id'] > 0) {
        $author_id = (int) $atts['author_id'];
    } elseif ($is_author_archive) {
        $author_id = (int) get_queried_object_id();
    } else {
        $author_id = (int) get_the_author_meta('ID');
    }
    if (!$author_id) return '';

    $paged = $is_author_archive ? max(1, (int) get_query_var('paged')) : 1;

    $q_args = array(
        'post_type'           => array('post', 'test'),
		'post_status'         => array('publish'),
        'posts_per_page'      => (int) $atts['posts_per_page'],
        'paged'               => $paged,
        'orderby'             => $atts['orderby'],
        'order'               => $atts['order'],
        'ignore_sticky_posts' => true,
        'author'              => $author_id,
        'suppress_filters'    => false,
    );

    $q = new WP_Query($q_args);

    ob_start();

    echo '<section class="marque-content-list lm-brandListing" id="marques-list">';

    if ($q->have_posts()) {

        echo '<div class="marque-posts-list lm-brandListing__grid" id="marque-list">';

        while ($q->have_posts()) {
            $q->the_post();
            lm_brand_listing_item_compat();
        }

        echo '</div>';

        // Pagination UNIQUEMENT sur archive auteur
        if ($is_author_archive && $q->max_num_pages > 1) {

            $base = trailingslashit(get_author_posts_url($author_id)) . '%_%';

            $links = paginate_links(array(
                'base'      => $base,
                'format'    => 'page/%#%/',
                'current'   => $paged,
                'total'     => (int) $q->max_num_pages,
                'mid_size'  => 2,
                'prev_text' => '«',
                'next_text' => '»',
                'type'      => 'list',
            ));

            if (function_exists('lm_pagination_markup_compat')) {
                $links = lm_pagination_markup_compat($links);
            }

            // --- DEBUG DATA ---
            $debug_payload = array(
                'ctx'                => 'author_content_list',
                'is_author_archive'   => (bool) $is_author_archive,
                'author_id'           => (int) $author_id,
                'paged'               => (int) $paged,
                'posts_per_page'      => (int) $q->get('posts_per_page'),
                'post_type'           => $q->get('post_type'),
                'orderby'             => $q->get('orderby'),
                'order'               => $q->get('order'),
                'found_posts'         => (int) $q->found_posts,
                'post_count'          => (int) $q->post_count,
                'max_num_pages'       => (int) $q->max_num_pages,
                'request'             => (string) $q->request,
                'query_vars_diff'     => array(
                    'paged_qv'   => (int) get_query_var('paged'),
                    'paged_get'  => isset($_GET['paged']) ? (int) $_GET['paged'] : null,
                    'page_qv'    => (int) get_query_var('page'),
                    'pagename'   => (string) get_query_var('pagename'),
                ),
            );

            $debug_json = wp_json_encode($debug_payload);

            echo '<nav class="marque-posts-pagination lm-brandListing__pagination" aria-label="Pagination"'
                . ' data-lm-debug=\'' . esc_attr($debug_json) . '\''
                . '>';

            echo $links;
            echo '</nav>';

            // --- DEBUG JS ---
            if (!empty($atts['debug'])) {
                echo "<script>
document.addEventListener('DOMContentLoaded', function () {
  var nav = document.querySelector('nav.marque-posts-pagination[data-lm-debug]');
  if (!nav) return;
  try {
    var data = JSON.parse(nav.getAttribute('data-lm-debug'));
    console.group('[LM DEBUG] author_content_list pagination');
    console.log('DATA:', data);
    console.log('TOTAL PAGES (max_num_pages):', data.max_num_pages);
    console.log('FOUND POSTS:', data.found_posts, 'POST COUNT on this page:', data.post_count);
    console.log('REQUEST SQL:', data.request);
    console.log('TIP: si max_num_pages est trop haut, cherche un pre_get_posts global ou un filtre qui change posts_per_page / post_type.');
    console.groupEnd();
  } catch (e) {
    console.error('[LM DEBUG] JSON parse failed', e);
  }
});
</script>";
            }
        }

    } else {
        echo '<p class="no-content lm-brandListing__empty">Aucun contenu trouvé pour cet auteur.</p>';
    }

    echo '</section>';

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('author_content_list', 'author_content_list_shortcode');

// =============================================================================
// [lm_terms_grid] - Taxonomy terms grid with pagination
// =============================================================================

function lm_terms_grid_shortcode($atts) {

    $atts = shortcode_atts([
        'taxonomy'  => 'categorie_test',
        'per_page'  => 12,
        'base_slug' => 'comparatifs',
    ], $atts);

    $taxonomy  = $atts['taxonomy'];
    $per_page  = (int) $atts['per_page'];
    $base_slug = trim($atts['base_slug'], '/');

    $paged  = max(1, get_query_var('paged'));
    $offset = ($paged - 1) * $per_page;

    $total_terms = wp_count_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ]);

    if (is_wp_error($total_terms) || $total_terms === 0) {
        return '';
    }

    $total_pages = (int) ceil($total_terms / $per_page);

    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'number'     => $per_page,
        'offset'     => $offset,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        return '';
    }

    ob_start();
    ?>

    <section class="lm-terms">
        <div class="lm-terms-grid">

            <?php foreach ($terms as $term) :

                $acf_content = get_field('contenu', $term);
                $acf_image   = get_field('featured', $term);

                $image_url = $acf_image
                    ? wp_get_attachment_image_url($acf_image, 'medium_large')
                    : '';

                $image_alt = $acf_image
                    ? get_post_meta($acf_image, '_wp_attachment_image_alt', true)
                    : '';

                $term_link = get_term_link($term);
            ?>

                <article class="lm-card">
                    <a class="lm-card-link" href="<?php echo esc_url($term_link); ?>">

                        <?php if ($image_url) : ?>
                            <div class="lm-card-media">
                                <img
                                    src="<?php echo esc_url($image_url); ?>"
                                    alt="<?php echo esc_attr($image_alt ?: $term->name); ?>"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>
                        <?php endif; ?>

                        <div class="lm-card-content">

                                <h3 class="lm-card-title">
                                    <?php echo esc_html($term->name); ?>
                                </h3>


                            <?php if ($acf_content) : ?>
                                <p class="lm-card-excerpt">
                                    <?php echo esc_html(wp_trim_words(strip_tags($acf_content), 32)); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                    </a>
                </article>

            <?php endforeach; ?>

        </div>

        <?php if ($total_pages > 1) : ?>
            <nav class="lm-pagination" aria-label="Pagination comparatifs">
                <ul class="lm-pagination-list">

                    <?php for ($i = 1; $i <= $total_pages; $i++) :
                        $url = ($i === 1)
                            ? home_url("/{$base_slug}/")
                            : home_url("/{$base_slug}/page/{$i}/");
                    ?>
                        <li class="lm-pagination-item <?php echo ($i === $paged) ? 'is-active' : ''; ?>">
                            <a href="<?php echo esc_url($url); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                </ul>
            </nav>
        <?php endif; ?>

    </section>

    <?php
    return ob_get_clean();
}
add_shortcode('lm_terms_grid', 'lm_terms_grid_shortcode');

// =============================================================================
// [lm_tests_grid] - Test CPT grid with ACF fields, stars, affiliz CTA
// =============================================================================

function lm_tests_grid_shortcode($atts) {

    $atts = shortcode_atts([
        'post_type' => 'test',
        'per_page'  => 12,
        'mode'      => 'auto',
        'taxonomy'  => '',
        'term_id'   => '',
        'term_slug' => '',
        'auto'      => '1',
        'base_slug' => '',
    ], $atts);

    $post_type = sanitize_key($atts['post_type']);
    $per_page  = max(1, (int) $atts['per_page']);
    $mode      = sanitize_key($atts['mode']);
    $auto      = ($atts['auto'] === '1' || $atts['auto'] === 1 || $atts['auto'] === true);
    $paged     = max(1, (int) get_query_var('paged'));

    $has_acf = function_exists('get_field');

    // Term filter
    $tax_query = [];

    if ($mode !== 'latest') {

        $manual_taxonomy = (!empty($atts['taxonomy']) && (!empty($atts['term_id']) || !empty($atts['term_slug'])));

        if ($manual_taxonomy) {

            $taxonomy = sanitize_key($atts['taxonomy']);
            $term = null;

            if (!empty($atts['term_id'])) {
                $term = get_term((int) $atts['term_id'], $taxonomy);
            } else {
                $term = get_term_by('slug', sanitize_title($atts['term_slug']), $taxonomy);
            }

            if ($term && !is_wp_error($term)) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => [(int) $term->term_id],
                ];
            }

        } elseif ($auto && (is_category() || is_tag() || is_tax())) {

            $queried = get_queried_object();
            if ($queried && !empty($queried->taxonomy) && !empty($queried->term_id)) {
                $tax_query[] = [
                    'taxonomy' => $queried->taxonomy,
                    'field'    => 'term_id',
                    'terms'    => [(int) $queried->term_id],
                ];
            }
        }
    }

    $args = [
    'post_type'           => $post_type,
    'posts_per_page'      => $per_page,
    'paged'               => $paged,
    'ignore_sticky_posts' => true,
    'post_status'         => 'publish',

    'meta_key'            => 'note_globale',
    'orderby'             => [
        'meta_value_num' => 'DESC',
        'date'           => 'DESC',
    ],

    'meta_query'          => [
        [
            'key'     => 'note_globale',
            'compare' => 'EXISTS',
        ],
        [
            'key'     => 'note_globale',
            'value'   => '',
            'compare' => '!=',
        ],
        [
            'key'     => 'note_globale',
            'type'    => 'NUMERIC',
        ],
    ],
];



    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    $q = new WP_Query($args);

    if (!$q->have_posts()) {
        wp_reset_postdata();
        return '';
    }

    $base_slug = trim((string) $atts['base_slug'], '/');
    $is_page_pagination = (!empty($base_slug) && is_page());

    ob_start();
    ?>
    <section class="lm-tests">
        <div class="lm-tests-grid">

            <?php while ($q->have_posts()) : $q->the_post(); ?>
                <?php
                    $post_id   = get_the_ID();
                    $permalink = get_permalink($post_id);

                    $nom            = $has_acf ? (string) get_field('nom', $post_id) : '';
                    $note_globale    = $has_acf ? get_field('note_globale', $post_id) : '';
                    $bouton_affiliz  = $has_acf ? (string) get_field('bouton_affiliz', $post_id) : '';

                    $title = lm_get_test_card_title($post_id);

                    $img_url = get_the_post_thumbnail_url($post_id, 'medium_large');

                    $excerpt = get_the_excerpt($post_id);
                    if (!$excerpt) {
                        $excerpt = wp_trim_words(wp_strip_all_tags(get_the_content(null, false, $post_id)), 24);
                    }

                    $stars_html = '';
                    if (!empty($note_globale)) {
                        if (function_exists('lm_generate_star_rating')) {
                            $stars_html = lm_generate_star_rating($note_globale);
                        } elseif (function_exists('generate_star_rating')) {
                            $stars_html = generate_star_rating($note_globale);
                        }
                    }
                ?>

                <article class="lm-test-card">
                    <div class="lm-test-card-inner">

                        <a class="lm-test-card-overlay"
                           href="<?php echo esc_url($permalink); ?>"
                           aria-label="<?php echo esc_attr($title); ?>"></a>

                        <?php if (!empty($stars_html)) : ?>
                            <div class="lm-test-card-stars">
                                <?php echo $stars_html; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($img_url) : ?>
                            <a class="lm-test-card-media-link" href="<?php echo esc_url($permalink); ?>">
                                <div class="lm-test-card-media">
                                    <img
                                        src="<?php echo esc_url($img_url); ?>"
                                        alt="<?php echo esc_attr($title); ?>"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </div>
                            </a>
                        <?php endif; ?>

                        <div class="lm-test-card-content">

                            <h3 class="lm-test-card-title">
                                <a class="lm-test-card-title-link" href="<?php echo esc_url($permalink); ?>">
                                    <?php echo esc_html($title); ?>
                                </a>
                            </h3>

                            <?php if ($excerpt) : ?>
                                <p class="lm-test-card-excerpt"><?php echo esc_html($excerpt); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($bouton_affiliz)) : ?>
                                <div class="lm-test-card-cta">
                                    <div class="affilizz-container"><?php echo $bouton_affiliz; ?></div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </article>

            <?php endwhile; ?>

        </div>

        <?php
        $total_pages = (int) $q->max_num_pages;

        if ($total_pages > 1) {

            if ($is_page_pagination) {
                $base   = home_url("/{$base_slug}/%_%");
                $format = 'page/%#%/';

                $links = paginate_links([
                    'base'      => $base,
                    'format'    => $format,
                    'current'   => $paged,
                    'total'     => $total_pages,
                    'mid_size'  => 2,
                    'end_size'  => 1,
                    'prev_text' => '← précédent',
                    'next_text' => '<span aria-hidden="true">→</span> suivant',
                    'type'      => 'array',
                ]);
            } else {
                $big = 999999;
                $links = paginate_links([
                    'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                    'format'    => '',
                    'current'   => $paged,
                    'total'     => $total_pages,
                    'mid_size'  => 2,
                    'end_size'  => 1,
                    'prev_text' => '← précédent',
                    'next_text' => '<span aria-hidden="true">→</span> suivant',
                    'type'      => 'array',
                ]);
            }

            if (!empty($links) && is_array($links)) {

                echo '<nav class="lm-pagination" aria-label="Pagination">';
                echo '<ul class="lm-pagination-list">';

                foreach ($links as $link_html) {

                    $is_current = (strpos($link_html, 'current') !== false);
                    $li_class = 'lm-pagination-item' . ($is_current ? ' is-active' : '');

                    $link_html = preg_replace('/>(\d+)</', '><span class="screen-reader-text">Page</span>$1<', $link_html);
                    $link_html = str_replace('class="', 'class="lm-page-numbers ', $link_html);

                    echo '<li class="' . esc_attr($li_class) . '">';
                    echo $link_html;
                    echo '</li>';
                }

                echo '</ul>';
                echo '</nav>';
            }
        }
        ?>

    </section>
    <?php

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('lm_tests_grid', 'lm_tests_grid_shortcode');

// =============================================================================
// [produits_populaires] - Popular products (by 7d views)
// =============================================================================

function lm_produits_populaires_shortcode($atts): string
{
    $atts = shortcode_atts([
        'post_type' => 'test',
        'limit'     => 6,
        'orderby'   => 'date',
        'order'     => 'DESC',
        'title'     => 'Produits populaires',
    ], $atts);

    $post_type = sanitize_key($atts['post_type']);
    $limit     = max(1, (int) $atts['limit']);
    $orderby   = sanitize_key($atts['orderby']);
    $order     = (strtoupper((string) $atts['order']) === 'ASC') ? 'ASC' : 'DESC';
    $title     = (string) $atts['title'];

    $q = new WP_Query([
    'post_type'           => $post_type,
    'posts_per_page'      => $limit,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,

    'meta_key'            => 'post_views_7d',
    'orderby'             => [
        'meta_value_num' => 'DESC',
        'modified'       => 'DESC',
    ],
    'order'               => 'DESC',

    'meta_query'          => [
        'relation' => 'OR',
        [
            'key'     => 'post_views_7d',
            'compare' => 'EXISTS',
        ],
        [
            'key'     => 'post_views_7d',
            'compare' => 'NOT EXISTS',
        ],
    ],
]);


    if (!$q->have_posts()) {
        wp_reset_postdata();
        return '';
    }

    $out  = '<div class="related-content related-articles">';

    $out .= '<span class="gb-headline-shortcode">
        <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
        <span>' . esc_html($title) . '</span>
    </span>';

    $out .= '<div class="related-content__grid mixed_list_homepage">';

    while ($q->have_posts()) {
        $q->the_post();
        $pid = (int) get_the_ID();

        $out .= lm_render_related_test_card($pid);
    }

    $out .= '</div></div>';

    wp_reset_postdata();
    return $out;
}
add_shortcode('produits_populaires', 'lm_produits_populaires_shortcode');
