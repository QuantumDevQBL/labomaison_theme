<?php
/**
 * Helper Functions
 *
 * General utility functions used throughout the theme.
 * These are foundation functions with no dependencies.
 *
 * @package Labomaison
 * @subpackage Utilities
 * @since 2.0.0
 *
 * Functions in this file:
 * - generate_star_rating()
 * - generate_card_html()
 * - generate_content_card()
 * - generate_product_card()
 * - generate_content_card_custom()
 * - render_post_item_for_test()
 * - render_post_item_for_marque()
 * - lm_pagination_markup_compat()
 * - lm_get_test_card_title()
 * - lm_render_related_test_card()
 * - initialize_displayed_news_ids()
 * - add_custom_post_type_class()
 * - modify_headline_block_for_tests()
 *
 * Dependencies: None
 * Load Priority: 1 (Must load first)
 * Risk Level: LOW
 *
 * Migrated from: shortcode_list.php
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// STAR RATING GENERATION
// =============================================================================

/**
 * Generate star rating HTML with SVG
 *
 * Creates a visual 5-star rating display using SVG gradients
 * for partial star fills.
 *
 * @since 2.0.0
 * @param float|string $note_globale The rating value (0-5)
 * @return string HTML output for star rating
 */
function generate_star_rating($note_globale) {
    if (empty($note_globale)) return '';

    $note_globale = is_numeric($note_globale) ? floatval($note_globale) : 0.0;
    $unique_id = uniqid('star_');

    $output = "<div class='note-globale'><div class='etoiles-container'>";
    for ($i = 1; $i <= 5; $i++) {
        $fill = $note_globale >= $i ? 100 : ($note_globale > $i - 1 ? ($note_globale - floor($note_globale)) * 100 : 0);
        // ajoute l'unique au gradient ID
        $grad_id = "grad-{$unique_id}-{$i}";
        $output .= "<svg width='20' height='20' viewBox='0 0 50 50' class='etoile'>
            <defs>
                <linearGradient id='{$grad_id}'>
                    <stop offset='{$fill}%' stop-color='gold'/>
                    <stop offset='{$fill}%' stop-color='gray'/>
                </linearGradient>
            </defs>
            <polygon points='25,1 32,19 50,19 35,30 40,48 25,37 10,48 15,30 0,19 18,19' fill='url(#{$grad_id})'/>
        </svg>";
    }
    $output .= "</div></div>";
    return $output;
}

// =============================================================================
// CARD GENERATION FUNCTIONS
// =============================================================================

/**
 * Generate card HTML for post or taxonomy term
 *
 * @since 2.0.0
 * @param WP_Post|WP_Term $post_or_term Post object or term object
 * @param bool $is_category Whether the item is a category/term
 * @return string HTML output for the card
 */
function generate_card_html($post_or_term, $is_category = false) {
    if ($is_category) {
        // $post_or_term est un WP_Term
        $term_id = isset($post_or_term->term_id) ? (int) $post_or_term->term_id : 0;

        $permalink = get_term_link($post_or_term, 'categorie_test');

        // IMPORTANT : pour ACF sur taxonomie, il faut passer "taxonomy_termId"
        $acf_ref = $term_id ? ('categorie_test_' . $term_id) : $post_or_term;

        $title = get_field('titre', $acf_ref);
        $featured_image_id = get_field('featured', $acf_ref);
        $thumbnail_url = $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'medium') : '';
        $content = isset($post_or_term->description) ? wp_trim_words($post_or_term->description, 10) : '';
        $date = ''; // pas de date fiable pour un terme
    } else {
        $permalink = get_permalink($post_or_term->ID);
        $title = get_the_title($post_or_term->ID);
        $thumbnail_url = get_the_post_thumbnail_url($post_or_term->ID, 'medium');
        $content = wp_trim_words(get_the_content(null, false, $post_or_term->ID), 10);
        $date = get_the_date('d/m/Y \à H:i', $post_or_term->ID);
    }

    $output = '<div class="related-content__card">';
    $output .= '<div class="related-content__image">';
    $output .= '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($title) . '">';
    $output .= '</div>';
    $output .= '<div class="related-content__content">';
    $output .= '<span class="related-content__card-title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></span>';
    //$output .= '<p class="related-content__excerpt">' . esc_html($content) . '</p>';
    if ($date) {
        $output .= '<span class="related-content__date">Publié le ' . esc_html($date) . '</span>';
    }
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

/**
 * Generate content card HTML
 *
 * Utility function for generating content cards with category badges.
 *
 * @since 2.0.0
 * @param string $image_html Image HTML
 * @param string $title Card title
 * @param string $link Card permalink
 * @param string $date Optional publication date
 * @param string $category_name Optional category name
 * @param string $category_link Optional category link
 * @param string $category_class Optional category CSS class
 * @return string HTML output for the card
 */
function generate_content_card($image_html, $title, $link, $date = '', $category_name = '', $category_link = '', $category_class = '') {
    $output = '<div class="related-content__card">';

    if ($image_html) {
        $output .= '<div class="related-content__image">';
        $output .= '<a href="' . esc_url($link) . '">' . $image_html . '</a>';
        $output .= '</div>';
    }

    $output .= '<div class="related-content__content">';

    // Afficher la catégorie au-dessus du titre
    if ($category_name && $category_link && $category_class) {
        $output .= '<span class="post-term-item ' . esc_attr($category_class) . '">';
        $output .= '<a href="' . esc_url($category_link) . '" data-original-text="' . esc_attr($category_name) . '" style="display: -webkit-box; -webkit-line-clamp: 1; -moz-box-orient: vertical; overflow: hidden; line-height: 1.5; max-height: 1.5em; word-break: break-word; text-overflow: ellipsis;">';
        $output .= esc_html($category_name);
        $output .= '</a></span>';
    }

    $output .= '<span class="related-content__card-title"><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></span>';

    if ($date) {
        $output .= '<span class="related-content__date">Publié le ' . esc_html($date) . '</span>';
    }

    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

/**
 * Generate product card HTML (WooCommerce)
 *
 * @since 2.0.0
 * @return string HTML output for the product card
 */
function generate_product_card() {
    $product = wc_get_product(get_the_ID());
    if (!$product) return '';

    $image = $product->get_image('medium');
    $name = $product->get_name();
    $price = $product->get_price_html();
    $link = get_permalink();

    $output = '<div class="related-content__card">';
    $output .= '<div class="related-content__image">';
    $output .= '<a href="' . esc_url($link) . '">' . $image . '</a>';
    $output .= '</div>';
    $output .= '<div class="related-content__content">';
    $output .= '<span class="related-content__card-title"><a href="' . esc_url($link) . '">' . esc_html($name) . '</a></span>';
    $output .= '<p class="related-content__price">' . $price . '</p>';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

/**
 * Generate custom content card (wrapper for generate_content_card)
 *
 * @since 2.0.0
 * @param string $image_html Image HTML
 * @param string $title Card title
 * @param string $link Card permalink
 * @return string HTML output for the card
 */
function generate_content_card_custom($image_html, $title, $link) {
    return generate_content_card($image_html, $title, $link);
}

// =============================================================================
// POST ITEM RENDERING
// =============================================================================

/**
 * Render post item card for test posts
 *
 * @since 2.0.0
 * @param int $post_id Post ID
 * @return string HTML output for the article card
 */
function render_post_item_for_test($post_id)
{
    // Obtenir les données de l'article
    $permalink = get_permalink($post_id);
    $title = get_the_title($post_id);
    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
    $last_updated = get_the_modified_time('j F Y à H:i', $post_id);
    $chapeau = get_field('chapeau', $post_id);

    // Récupérer la catégorie de l'article
    $categories = get_the_category($post_id);
    $category_html = '';
    if (!empty($categories) && !is_wp_error($categories)) {
        $selected_category = null;
        // Sélectionner la première catégorie non-parente, ou la première catégorie si toutes sont parentes
        foreach ($categories as $category) {
            if ($category->parent == 0) {
                $selected_category = $category;
                break;
            }
        }
        if (!$selected_category && !empty($categories)) {
            $selected_category = $categories[0];
        }

        if ($selected_category) {
            $term_link = get_category_link($selected_category->term_id);
            if (!is_wp_error($term_link)) {
                $category_html = '<span class="test-category-link term_absolute post-term-item term-' . esc_attr($selected_category->slug) . '">';
                $category_html .= '<a href="' . esc_url($term_link) . '">' . esc_html($selected_category->name) . '</a>';
                $category_html .= '</span>';
            }
        }
    }

	$thumbnail_url_safe = esc_url($thumbnail_url);

    // Construire le HTML
    $html = "
    <div class='article-card'>
        <div class='article-thumbnail' style='background-image: url(\"$thumbnail_url\");'>
            <a href='$permalink' class='overlay-link'></a>
            $category_html
        </div>
        <div class='article-content'>
            <span class='article-title'><a href='$permalink' class='article-title'>$title</a></span>
            <p class='article-excerpt chapeau_post_card'>" . $chapeau . "</p>
            <span class='datetime'>Mis à jour le $last_updated</span>
        </div>
    </div>";
    return $html;
}

/**
 * Render post item card for marque posts
 *
 * @since 2.0.0
 * @param int $post_id Post ID
 * @return string HTML output for the article card
 */
function render_post_item_for_marque($post_id) {
    // Obtenir les données de l'article
    $permalink = get_permalink($post_id);
    $title = get_the_title($post_id);
    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
    $last_updated = get_the_modified_time('j F Y à H:i', $post_id);
    $linked_test_category_html = do_shortcode('[linked_test_category post_id="' . $post_id . '"]');
    $chapeau = get_field('chapeau', $post_id);

    // Construire le HTML
    $html = "
        <div class='article-card' data-post-id='$post_id'>
            <div class='article-thumbnail' style='background-image: url(\"$thumbnail_url\");'>
                <a href='$permalink' class='overlay-link'></a>
                $linked_test_category_html
            </div>
            <div class='article-content'>
                <span class='article-title'><a href='$permalink' class='article-title'>$title</a></span>
                <p class='article-excerpt chapeau_post_card'>" . $chapeau . "</p>
                <span class='datetime'>Mis à jour le $last_updated</span>
            </div>
        </div>";

    return $html;
}

// =============================================================================
// TEST CARD UTILITIES
// =============================================================================

/**
 * Get test card title with marque prefix
 *
 * Builds a title from "marque + nom" ACF fields,
 * falling back to WP title if empty.
 *
 * @since 2.0.0
 * @param int $post_id Post ID
 * @return string The computed title
 */
function lm_get_test_card_title(int $post_id): string
{
    // Fallback WP
    $fallback = (string) get_the_title($post_id);

    if (!function_exists('get_field')) {
        return $fallback;
    }

    $nom = trim((string) get_field('nom', $post_id));

    // ACF relationship : peut être array de posts, ou un post, ou un ID selon réglages
    $marque = get_field('marque', $post_id);
    $marque_title = '';

    if (!empty($marque)) {
        // Cas 1: array (relationship multiple)
        if (is_array($marque)) {
            $first = reset($marque);

            if (is_object($first) && isset($first->ID)) {
                $marque_title = (string) get_the_title((int) $first->ID);
            } elseif (is_numeric($first)) {
                $marque_title = (string) get_the_title((int) $first);
            }
        }
        // Cas 2: objet post (relationship single)
        elseif (is_object($marque) && isset($marque->ID)) {
            $marque_title = (string) get_the_title((int) $marque->ID);
        }
        // Cas 3: ID (relationship return = ID)
        elseif (is_numeric($marque)) {
            $marque_title = (string) get_the_title((int) $marque);
        }
    }

    $title = trim(trim($marque_title) . ' ' . $nom);

    // Si tout est vide, fallback WP
    return $title !== '' ? $title : $fallback;
}

/**
 * Render related test card HTML
 *
 * Generates a complete card for a test post including
 * thumbnail, category badge, title, star rating and CTA.
 *
 * @since 2.0.0
 * @param int $test_id Test post ID
 * @return string HTML output for the card
 */
function lm_render_related_test_card(int $test_id): string
{
    if ($test_id <= 0) return '';

    $permalink = get_permalink($test_id);
    if (!$permalink) return '';

    $thumb = get_the_post_thumbnail($test_id, 'thumbnail');
    $title = get_the_title($test_id);

    // Helper badge categorie_test (copié de ton shortcode)
    $terms = get_the_terms($test_id, 'categorie_test');
    $badge = '';
    if (!empty($terms) && !is_wp_error($terms)) {
        $t = $terms[0];
        $link = get_term_link($t, 'categorie_test');
        if (!is_wp_error($link)) {
            $badge = '<span class="post-term-item term-' . esc_attr($t->slug) . '">'
                . '<a href="' . esc_url($link) . '">' . esc_html($t->name) . '</a>'
                . '</span>';
        }
    }

    $out  = '<div class="related-content__card test type-test">';

    $out .= '<div class="related-content__image">';
    $out .= '<a href="' . esc_url($permalink) . '">' . $thumb . '</a>';
    $out .= '</div>';

    $out .= '<div class="related-content__content">';

    // Badge au-dessus du titre
    if ($badge !== '') {
        $out .= $badge;
    }

    // Titre
    $out .= '<span class="related-content__card-title"><a href="' . esc_url($permalink) . '">'
        . esc_html($title) . '</a></span>';

    // Étoiles
    $note = function_exists('get_field') ? get_field('note_globale', $test_id) : '';
    if (!empty($note) && function_exists('generate_star_rating')) {
        $out .= '<div class="related-content__stars">' . generate_star_rating($note) . '</div>';
    }

    // CTA Affiliz (HTML brut)
    $bouton_affiliz = get_post_meta($test_id, 'bouton_affiliz', true);
    if (!empty($bouton_affiliz)) {
        $out .= '<div class="related-content__cta related-content__cta--affiliz">' . $bouton_affiliz . '</div>';
    }

    $out .= '</div></div>';

    return $out;
}

// =============================================================================
// PAGINATION UTILITIES
// =============================================================================

/**
 * Make pagination markup compatible with theme styles
 *
 * Adds proper CSS classes to pagination HTML for consistent styling.
 *
 * @since 2.0.0
 * @param string $links_html Pagination HTML from paginate_links()
 * @return string Modified pagination HTML with added classes
 */
function lm_pagination_markup_compat($links_html) {
    if (empty($links_html) || !is_string($links_html)) {
        return $links_html;
    }

    // Add class to UL
    // paginate_links(type=list) outputs: <ul class='page-numbers'> ... OR sometimes <ul>...
    if (strpos($links_html, '<ul') !== false) {
        // If UL already has class, append page-numbers if missing
        if (preg_match('#<ul[^>]*class=[\'"][^\'"]*[\'"][^>]*>#', $links_html)) {
            $links_html = preg_replace_callback(
                '#<ul([^>]*?)class=[\'"]([^\'"]*)[\'"]([^>]*?)>#',
                function ($m) {
                    $classes = trim($m[2]);
                    $class_list = preg_split('/\s+/', $classes, -1, PREG_SPLIT_NO_EMPTY);

                    if (!in_array('page-numbers', $class_list, true)) {
                        $class_list[] = 'page-numbers';
                    }

                    return '<ul' . $m[1] . 'class="' . esc_attr(implode(' ', $class_list)) . '"' . $m[3] . '>';
                },
                $links_html,
                1
            );
        } else {
            // No class attribute on ul
            $links_html = preg_replace('#<ul(\s*?)>#', '<ul class="page-numbers">', $links_html, 1);
        }
    }

    // Add class="page-number" to every LI
    if (strpos($links_html, '<li') !== false) {
        $links_html = preg_replace_callback(
            '#<li([^>]*)>#',
            function ($m) {
                $attrs = $m[1];

                if (preg_match('#class=[\'"]([^\'"]*)[\'"]#', $attrs, $cm)) {
                    $classes = trim($cm[1]);
                    $class_list = preg_split('/\s+/', $classes, -1, PREG_SPLIT_NO_EMPTY);

                    if (!in_array('page-number', $class_list, true)) {
                        $class_list[] = 'page-number';
                    }

                    // Replace existing class attr
                    $attrs = preg_replace(
                        '#class=[\'"][^\'"]*[\'"]#',
                        'class="' . esc_attr(implode(' ', $class_list)) . '"',
                        $attrs,
                        1
                    );
                } else {
                    $attrs .= ' class="page-number"';
                }

                return '<li' . $attrs . '>';
            },
            $links_html
        );
    }

    // Add .current to the LI that contains the current span
    // paginate_links renders current as: <span aria-current='page' class='page-numbers current'>2</span>
    // We add class current on the parent LI for easier styling.
    $links_html = preg_replace_callback(
        '#<li([^>]*)>(\s*)<span([^>]*?)aria-current=[\'"]page[\'"]([^>]*?)>(.*?)</span>(\s*)</li>#',
        function ($m) {
            $li_attrs = $m[1];

            if (preg_match('#class=[\'"]([^\'"]*)[\'"]#', $li_attrs, $cm)) {
                $classes = trim($cm[1]);
                $class_list = preg_split('/\s+/', $classes, -1, PREG_SPLIT_NO_EMPTY);

                if (!in_array('current', $class_list, true)) {
                    $class_list[] = 'current';
                }

                $li_attrs = preg_replace(
                    '#class=[\'"][^\'"]*[\'"]#',
                    'class="' . esc_attr(implode(' ', $class_list)) . '"',
                    $li_attrs,
                    1
                );
            } else {
                $li_attrs .= ' class="current"';
            }

            return '<li' . $li_attrs . '>' . $m[2] . '<span' . $m[3] . 'aria-current="page"' . $m[4] . '>' . $m[5] . '</span>' . $m[6] . '</li>';
        },
        $links_html
    );

    return $links_html;
}

// =============================================================================
// GLOBAL STATE INITIALIZATION
// =============================================================================

/**
 * Initialize global array for tracking displayed news IDs
 *
 * Prevents duplicate content display across multiple shortcodes.
 *
 * @since 2.0.0
 * @return void
 */
function initialize_displayed_news_ids() {
    global $displayed_news_ids;
    if (!isset($displayed_news_ids)) {
        $displayed_news_ids = array();
    }
}
add_action('wp', 'initialize_displayed_news_ids');

// =============================================================================
// POST CLASS FILTERS
// =============================================================================

/**
 * Add custom post type class to post containers
 *
 * Adds 'post-type-test' or 'post-type-post' class to article containers
 * for CSS targeting.
 *
 * @since 2.0.0
 * @param array $classes Existing post classes
 * @return array Modified post classes
 */
function add_custom_post_type_class( $classes ) {
    if ( 'test' === get_post_type() ) {
        $classes[] = 'post-type-test';
    } elseif ( 'post' === get_post_type() ) {
        $classes[] = 'post-type-post';
    }
    return $classes;
}
add_filter( 'post_class', 'add_custom_post_type_class' );

// =============================================================================
// BLOCK MODIFICATIONS
// =============================================================================

/**
 * Modify headline blocks for test posts
 *
 * Adds star rating and Affiliz button to specific headline blocks
 * on test post types.
 *
 * @since 2.0.0
 * @param string $block_content The block content
 * @param array $block The block data
 * @return string Modified block content
 */
function modify_headline_block_for_tests( $block_content, $block ) {
    // Vérifier si on est sur une publication de type 'test'
    if ( 'test' === get_post_type() ) {
        // Vérifier si la classe 'home_tests_fields_stars' est présente dans le bloc
        if ( isset( $block['attrs']['className'] ) && strpos( $block['attrs']['className'], 'home_tests_fields_stars' ) !== false ) {
            // Ajouter le shortcode pour afficher les étoiles
            $shortcode_stars = do_shortcode('[afficher_note_globale_card post_id="' . get_the_ID() . '"]');
            $block_content = $shortcode_stars . $block_content;
        }

        // Vérifier si la classe 'home_tests_fields_affiliz' est présente dans le bloc
        if ( isset( $block['attrs']['className'] ) && strpos( $block['attrs']['className'], 'home_tests_fields_affiliz' ) !== false ) {
            // Récupérer le champ personnalisé 'bouton_affiliz'
            $bouton_affiliz = get_post_meta( get_the_ID(), 'bouton_affiliz', true );

            // Vérifier que la valeur n'est pas vide
            if ( !empty($bouton_affiliz) ) {


                // Ajouter directement le contenu HTML récupéré sans le modifier
                $block_content = $bouton_affiliz . $block_content;
            }
        }
    } else {
        // Si ce n'est pas un 'test', masquer ces blocs
        if ( isset( $block['attrs']['className'] ) &&
             (strpos( $block['attrs']['className'], 'home_tests_fields_stars' ) !== false || strpos( $block['attrs']['className'], 'home_tests_fields_affiliz' ) !== false) ) {
            return ''; // Ne rien afficher
        }
    }

    return $block_content;
}
add_filter( 'render_block', 'modify_headline_block_for_tests', 10, 2 );
