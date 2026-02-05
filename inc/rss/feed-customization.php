<?php
/**
 * RSS Feed Customization
 *
 * Custom RSS feed modifications.
 *
 * @package Labomaison
 * @subpackage RSS
 * @since 2.0.0
 *
 * Functions in this file:
 * - labomaison_custom_rss_description()
 * - labomaison_add_content_encoded()
 * - labomaison_add_enclosure()
 * - labomaison_clean_guid()
 * - lm_force_custom_rss_template()
 * - adjust_rss_pubdate_timezone()
 * - custom_rss_pubdate()
 * - RSS hook filters
 *
 * Dependencies: ACF
 * Load Priority: 8
 * Risk Level: LOW-MEDIUM
 *
 * Migrated from: functions.php L685-712, L1029-1170
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// RSS LINK OVERRIDE
// =============================================================================

/**
 * Force RSS feed link to point to homepage
 *
 * @since 2.0.0
 */
add_filter('bloginfo_rss', function($output, $show) {
    if ($show === 'url') {
        return home_url('/');
    }
    return $output;
}, 10, 2);

// =============================================================================
// RSS DESCRIPTION
// =============================================================================

/**
 * Custom RSS description using ACF chapeau field
 *
 * Priority: ACF chapeau > WP excerpt > trimmed content
 *
 * @since 2.0.0
 * @param string $content Original content
 * @return string Cleaned description
 */
function labomaison_custom_rss_description($content) {
    global $post;

    // Récupérer champ ACF "chapeau" si dispo
    if (function_exists('get_field') && get_field('chapeau', $post->ID)) {
        $excerpt = get_field('chapeau', $post->ID);
    } elseif (has_excerpt($post->ID)) {
        $excerpt = get_the_excerpt($post->ID);
    } else {
        $excerpt = wp_trim_words(strip_tags($post->post_content), 50, '…');
    }

    // Nettoyer le texte parasite WordPress
    $excerpt = preg_replace('/The post.*appeared first on.*/i', '', $excerpt);
    // Supprimer le deuxième <p> et son contenu
    $excerpt = preg_replace('/(<p[^>]*>.*?<\/p>).*?(<p[^>]*>.*?<\/p>)/is', '$1', $excerpt);

    return esc_html(wp_trim_words($excerpt, 55, '…')); // ~300 caractères
}
add_filter('the_excerpt_rss', 'labomaison_custom_rss_description');
add_filter('the_content_feed', 'labomaison_custom_rss_description');

// =============================================================================
// RSS CONTENT ENCODED
// =============================================================================

/**
 * Add content:encoded with full image, formatted content and CTA
 *
 * @since 2.0.0
 * @return void
 */
function labomaison_add_content_encoded() {
    global $post;

    $content = '';

    // Image à la une en taille full
    if (has_post_thumbnail($post->ID)) {
        $img_id  = get_post_thumbnail_id($post->ID);
        $img_url = wp_get_attachment_image_url($img_id, 'full');
        $meta    = wp_get_attachment_metadata($img_id);

        $width  = !empty($meta['width'])  ? $meta['width']  : 1200;
        $height = !empty($meta['height']) ? $meta['height'] : 800;
        $alt    = get_post_meta($img_id, '_wp_attachment_image_alt', true);

        $content .= '<p><img src="'.$img_url.'" alt="'.esc_attr($alt).'" width="'.$width.'" height="'.$height.'" /></p>';
    }

    //  Contenu complet formaté
    $content .= apply_filters('the_content', $post->post_content);

    //  CTA en dernier
    $content .= '<p><strong><a href="'.get_permalink($post->ID).'">Lire l\'article complet sur LaboMaison</a></strong></p>';

    //  Nettoyage :
    // Supprimer blocs Affilizz
    $content = preg_replace('/<div class="wp-block-affilizz-publication.*?<\/div>/is', '', $content);
    // Supprimer le 2e <p> et son contenu dans la description
    $content = preg_replace('/(<p[^>]*>.*?<\/p>).*?(<p[^>]*>.*?<\/p>)/is', '$1', $content);
    // Supprimer <div> orphelins
    $content = preg_replace('/<div[^>]*>.*?<\/div>/is', '', $content);
    // Supprimer paragraphes vides
    $content = preg_replace('/<p>\s*<\/p>/', '', $content);

    //  Protection CDATA
    $content = str_replace(']]>', ']]]]><![CDATA[>', $content);

    echo '<content:encoded><![CDATA['.$content.']]></content:encoded>'."\n";
}
add_action('rss2_item', 'labomaison_add_content_encoded');

// =============================================================================
// RSS ENCLOSURE (IMAGE)
// =============================================================================

/**
 * Add enclosure tag with image dimensions for RSS items
 *
 * @since 2.0.0
 * @return void
 */
function labomaison_add_enclosure() {
    global $post;

    if (!has_post_thumbnail($post->ID)) {
        return;
    }

    $img_url = get_the_post_thumbnail_url($post->ID, 'full');

    // Convertit l'URL publique en chemin serveur
    $upload_dir = wp_upload_dir();
    $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $img_url);

    // Vérifie la taille et le type du fichier
    $length = file_exists($file_path) ? filesize($file_path) : 0;
    $mime   = wp_check_filetype($file_path)['type'] ?? 'image/jpeg';

    printf(
        "<enclosure url=\"%s\" type=\"%s\" length=\"%d\" />\n",
        esc_url($img_url),
        esc_attr($mime),
        $length
    );
}
add_action('rss2_item', 'labomaison_add_enclosure');

// =============================================================================
// RSS GUID CLEANUP
// =============================================================================

/**
 * Clean GUID to use canonical permalink
 *
 * @since 2.0.0
 * @param string $guid Original GUID
 * @param int $post_id Post ID
 * @return string Clean permalink
 */
function labomaison_clean_guid($guid, $post_id) {
    return get_permalink($post_id);
}
add_filter('get_the_guid', 'labomaison_clean_guid', 10, 2);

// =============================================================================
// RSS PUBDATE TIMEZONE
// =============================================================================

/**
 * Adjust RSS publication date to site timezone
 *
 * @since 2.0.0
 * @param string $post_date_gmt GMT date string
 * @return string Formatted date with timezone
 */
function adjust_rss_pubdate_timezone($post_date_gmt) {
    // Obtenez le fuseau horaire du site WordPress
    $timezone_string = get_option('timezone_string');
    if (empty($timezone_string)) {
        $offset = get_option('gmt_offset');
        $timezone_string = timezone_name_from_abbr('', $offset * 3600, 0);
    }

    // Créez un objet DateTime avec le fuseau horaire de votre site
    $timezone = new DateTimeZone($timezone_string);

    // Convertir la date GMT au fuseau horaire spécifié
    $date = new DateTime($post_date_gmt, new DateTimeZone('UTC'));
    $date->setTimezone($timezone);

    // Formater la date pour correspondre au format RSS
    return $date->format('D, d M Y H:i:s O');
}

/**
 * Output custom RSS pubdate
 *
 * @since 2.0.0
 */
function custom_rss_pubdate() {
    global $post;
    $pub_date = adjust_rss_pubdate_timezone($post->post_date_gmt);
    echo "<pubDate>$pub_date</pubDate>\n";
}
//add_action('rss2_item', 'custom_rss_pubdate');

// =============================================================================
// RSS TEMPLATE OVERRIDE
// =============================================================================

/**
 * Force WordPress to use child theme RSS template
 *
 * @since 2.0.0
 * @param string $template Template path
 * @return string Modified template path
 */
function lm_force_custom_rss_template($template) {
    $custom = get_stylesheet_directory() . '/feed-rss2.php';
    if ( file_exists( $custom ) ) {
        return $custom;
    }
    return $template;
}
add_filter('feed_template', 'lm_force_custom_rss_template');

/**
 * Force main RSS 2.0 feed to use child theme template
 *
 * @since 2.0.0
 */
add_action('template_redirect', function() {
    if ( is_feed() && !is_feed('comments-rss2') ) {
        include get_stylesheet_directory() . '/feed-rss2.php';
        exit;
    }
}, 0);
