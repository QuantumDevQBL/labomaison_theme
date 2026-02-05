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
 * - add_image_dimensions()
 * - add_dimensions_to_affilizz_images()
 * - render_block query loop modification
 * - generate_archive_title filter
 * - generate_after_loop filter
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
// IMAGE DIMENSION FILTERS
// =============================================================================

/**
 * Add dimensions to images missing width/height attributes
 *
 * Improves CLS (Cumulative Layout Shift) by ensuring all images
 * have explicit dimensions.
 *
 * @since 2.0.0
 * @param string $content Post content
 * @return string Modified content
 */
function add_image_dimensions($content) {
    // Ajouter automatiquement des dimensions aux images
    $content = preg_replace_callback('/<img (.*?)src=["\'](.*?)["\'](.*?)>/', function($matches) {
        $attrs = $matches[1] . $matches[3];

        // Extrait les dimensions si elles existent
        preg_match('/width=["\'](\d+)["\']/i', $attrs, $width);
        preg_match('/height=["\'](\d+)["\']/i', $attrs, $height);

        // Si les dimensions manquent, on ajoute des valeurs par défaut ou calculées
        if (empty($width) || empty($height)) {
            // Utiliser une approche plus fiable pour obtenir le chemin des images
            $upload_dir = wp_upload_dir();
            $image_path = str_replace(home_url(), $upload_dir['basedir'], $matches[2]);

            if (file_exists($image_path) && exif_imagetype($image_path)) {
                $image_size = getimagesize($image_path);
                if ($image_size) {
                    $attrs .= ' width="' . esc_attr($image_size[0]) . '" height="' . esc_attr($image_size[1]) . '"';
                }
            }
        }

        return '<img ' . $attrs . ' src="' . esc_url($matches[2]) . '">';
    }, $content);

    return $content;
}
add_filter('the_content', 'add_image_dimensions');

/**
 * Add dimensions to Affilizz images
 *
 * Specifically targets images with the affilizz-icon class.
 *
 * @since 2.0.0
 * @param string $content Post content
 * @return string Modified content
 */
function add_dimensions_to_affilizz_images($content) {
    $content = preg_replace_callback('/<img(.*?)class=["\']affilizz-icon["\'](.*?)src=["\'](.*?)["\'](.*?)>/', function($matches) {
        $attrs = $matches[1] . $matches[4];

        // Utiliser une approche plus fiable pour obtenir le chemin des images
        $upload_dir = wp_upload_dir();
        $image_path = str_replace(home_url(), $upload_dir['basedir'], $matches[3]);

        if (file_exists($image_path) && exif_imagetype($image_path)) {
            $image_size = getimagesize($image_path);
            if ($image_size) {
                $attrs .= ' width="' . esc_attr($image_size[0]) . '" height="' . esc_attr($image_size[1]) . '"';
            }
        }

        return '<img' . $attrs . ' src="' . esc_url($matches[3]) . '" class="affilizz-icon">';
    }, $content);

    return $content;
}
add_filter('the_content', 'add_dimensions_to_affilizz_images');

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
// GENERATEPRESS ARCHIVE FILTERS
// =============================================================================

/**
 * Add ACF chapeau field to archive titles
 *
 * Displays the 'chapeau' ACF field below category/tag archive titles.
 *
 * @since 2.0.0
 */
add_filter('generate_archive_title', function ($title) {

    if (is_category() || is_tag()) {
        $term_id = get_queried_object_id(); // Récupérer l'ID de la catégorie ou de l'étiquette actuelle
        $chapeau = get_field('chapeau', 'category_' . $term_id); // Pour les catégories
        $chapeau_etiquette = get_field('chapeau', 'post_tag_' . $term_id); // Pour les étiquettes

        // Vérifiez si un champ existe pour construire le contenu
        if (!empty($chapeau)) {
            echo '<div class="acf-chapeau">' . wp_kses_post($chapeau) . '</div>'; // Affiche le chapeau pour la catégorie
        } elseif (!empty($chapeau_etiquette)) {
            echo '<div class="acf-chapeau">' . wp_kses_post($chapeau_etiquette) . '</div>'; // Affiche le chapeau pour l'étiquette
        } else {
            return;
        }
    }

    return $title; // Retourne le titre sans modification
});

/**
 * Add ACF content and FAQ fields after archive loops
 *
 * Displays the 'contenu' and 'faq' ACF fields after category/tag archive loops.
 *
 * @since 2.0.0
 */
add_filter('generate_after_loop', function ($title) {

    if (is_category() || is_tag()) {
        $term_id = get_queried_object_id(); // Récupérer l'ID de la catégorie ou de l'étiquette actuelle
        $contenu = get_field('contenu', 'category_' . $term_id); // Pour les catégories
        $contenu_etiquette = get_field('contenu', 'post_tag_' . $term_id); // Pour les étiquettes

        $faq = get_field('faq', 'category_' . $term_id); // Pour les catégories
        $faq_etiquette = get_field('faq', 'post_tag_' . $term_id); // Pour les étiquettes

        // Vérifiez si un champ existe pour construire le contenu
        if (!empty($contenu)) {
            echo '<div class="acf-contenu">' . wp_kses_post($contenu) . '</div>'; // Affiche le chapeau pour la catégorie
        } elseif (!empty($contenu_etiquette)) {
            echo '<div class="acf-contenu">' . wp_kses_post($contenu_etiquette) . '</div>'; // Affiche le chapeau pour l'étiquette
        }

        // Vérifiez si un champ existe pour construire le contenu
        if (!empty($faq)) {
            echo '<div class="acf-contenu">' . wp_kses_post($faq) . '</div>'; // Affiche le chapeau pour la catégorie
        } elseif (!empty($faq_etiquette)) {
            echo '<div class="acf-contenu">' . wp_kses_post($faq_etiquette) . '</div>'; // Affiche le chapeau pour l'étiquette
        }
    }

    return $title; // Retourne le titre sans modification
});

// =============================================================================
// MARQUE COMMENTS DISABLE
// =============================================================================

/**
 * Disable comments specifically for marque post type
 *
 * @since 2.0.0
 */
add_action('init', function () {
    if (is_singular('marque')) {
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
    }
});
