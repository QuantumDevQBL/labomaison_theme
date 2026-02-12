<?php
/**
 * Miscellaneous Shortcodes
 *
 * Various utility shortcodes.
 *
 * @package Labomaison
 * @subpackage Shortcodes
 * @since 2.0.0
 *
 * Shortcodes:
 * - [archive_filtre]
 * - [display_post_dates]
 * - [promotion_link]
 * - [show_acf_promotion_data]
 * - [social_share]
 * - [qd_video]
 * - [lm_seo_only_first]
 * - [search_title]
 * - [author_featured_image]
 * - [c2s_widget]
 * - [show_term_c2s_widget]
 *
 * Dependencies: Varies
 * Load Priority: 6
 * Risk Level: LOW-MEDIUM
 *
 * Migrated from: shortcode_list.php L270-323, L773-822, L1132-1169, L1326-1361,
 *                L1579-1614, L3133-3169, L3673-3695, L3912-3927
 *                functions.php L1870-1953
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// [archive_filtre] - Archive filter with categories and authors
// =============================================================================

function afficher_archive_filtre_shortcode($atts)
{
  $atts = shortcode_atts(
    array(
      'post_type' => 'post',
      'posts_per_page' => 2,
    ),
    $atts,
    'archive_filtre'
  );

  $coin_term = get_term_by('slug', 'coin', 'category');
  $coin_term_id = $coin_term ? $coin_term->term_id : 0;

  $html = '<div id="filtres">';

  $categories = get_terms(['taxonomy' => 'category', 'hide_empty' => false, 'exclude' => [$coin_term_id]]);
  foreach ($categories as $category) {
    if (strpos($category->slug, 'coin') !== false) {
      $html .= '<button class="filtre-categorie-btn" data-slug="' . esc_attr($category->slug) . '"><div class="color-container color-' . $category->slug . '"></div>' . esc_html($category->name) . '</button>';
    }
  }

  $html .= '<select id="filtre-auteur">';
  $html .= '<option value="">Rédacteurs</option>';

  $users = get_users(['who' => 'authors']);
  foreach ($users as $user) {
    $html .= '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
  }

  $html .= '</select>';
  $html .= '<input type="text" id="filtre-recherche" placeholder="Recherche...">';
  $html .= '<button style="display:none;" id="filtre-submit">Filtrer</button>';
  $html .= '</div>';

  $html .= '<div id="archive-filtre-container" data-post-type="' . esc_attr($atts['post_type']) . '">';
  $html .= '<div id="filtres"><!-- Les filtres seront chargés ici --></div>';
  $html .= '<div id="posts-container"><!-- Les posts seront chargés ici --></div>';
  $html .= '</div>';
  $html .= '<div id="pagination-container"></div>';

  wp_enqueue_script('archive-filtre-js');
  wp_enqueue_style('archive-filtre-css');

  return $html;
}
add_shortcode('archive_filtre', 'afficher_archive_filtre_shortcode');

// =============================================================================
// [display_post_dates] - Show publish/modified dates
// =============================================================================

function display_post_dates_shortcode()
{
  global $post;

  $output = '';

  $publish_date = get_the_date('d/m/Y \à H:i', $post->ID);
  $modified_date = get_the_modified_date('d/m/Y \à H:i', $post->ID);

  if ($publish_date == $modified_date) {
    $output .= "<span class='datetime'>Publié le " . $publish_date . "</span>";
  } else {
    $output .= "<span class='datetime'>Mis à jour le " . $modified_date . "</span>";
  }

  return $output;
}
add_shortcode('display_post_dates', 'display_post_dates_shortcode');

// render_block filter for show-post-date class
add_filter('render_block', function($block_content, $block) {
  if (strpos($block['attrs']['className'] ?? '', 'show-post-date') !== false) {
      $post_id = get_the_ID();
      $publish_date = get_the_date('d/m/Y \à H:i', $post_id);
      $modified_date = get_the_modified_date('d/m/Y \à H:i', $post_id);

      if ($publish_date == $modified_date) {
          $date_content = "<span class='datetime'>Publié le " . $publish_date . "</span>";
      } else {
          $date_content = "<span class='datetime'>Mis à jour le " . $modified_date . "</span>";
      }

      $block_content = $date_content . $block_content;
  }

  return $block_content;
}, 10, 2);

// =============================================================================
// [search_title] - Search page title
// =============================================================================

add_shortcode('search_title', 'get_search_title');
function get_search_title()
{
  if (is_search()) {
    return '<div class="search_title_container"><h3 class="search-for">Résultats de recherche pour</h3><h1 class="search-title">' . get_search_query() . '</h1></div>';
  } elseif (is_archive()) {
    return '<h1 class="search-title">' . get_the_archive_title() . '</h1>';
  }
}

// =============================================================================
// [author_featured_image] - Author thumbnail with link
// =============================================================================

function display_author_featured_image()
{
  global $post;

  $author_id = $post->post_author;
  $author_name = get_the_author_meta('display_name', $author_id);
  $author_url = get_author_posts_url($author_id);
  $author_image_id = get_user_meta($author_id, 'featured_image', true);

  if (!empty($author_image_id)) {
    $image_html = wp_get_attachment_image($author_image_id, 'author-thumbnail');
    return '<div style="width: 30px; height: 30px;" class="author-featured-image-banner"><a href="' . esc_url($author_url) . '" aria-label="Visitez la page de l\'auteur ' . esc_attr($author_name) . '">' . $image_html . '</a></div>';
  }

  return '';
}
add_shortcode('author_featured_image', 'display_author_featured_image');

// =============================================================================
// [author_list] - Author team cards grid
// =============================================================================

/**
 * Display author profile cards in a grid
 *
 * Shows author cards with photo, name, bio, and social/email icons.
 * Defaults to the two site admins (Marco Mosca & Pierre-Jean Alzieu).
 *
 * Usage: [author_list] or [author_list ids="5,6"]
 *
 * @since 2.0.0
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function lm_author_list_shortcode( $atts ) {
    wp_enqueue_style('font-awesome');

    $atts = shortcode_atts( array(
        'ids' => '6,5', // Marco Mosca, Pierre-Jean Alzieu
    ), $atts, 'author_list' );

    $author_ids = array_map( 'absint', array_filter( explode( ',', $atts['ids'] ) ) );

    if ( empty( $author_ids ) ) {
        return '';
    }

    $output = '<div class="lm-authorList">';

    foreach ( $author_ids as $author_id ) {
        $user = get_userdata( $author_id );
        if ( ! $user ) {
            continue;
        }

        $display_name = esc_html( $user->display_name );
        $description  = wp_kses_post( get_the_author_meta( 'description', $author_id ) );
        $author_url   = get_author_posts_url( $author_id );
        $email        = $user->user_email;

        // Image: ACF featured_image field, fallback to Gravatar
        $image_id  = get_user_meta( $author_id, 'featured_image', true );
        $image_html = '';
        if ( ! empty( $image_id ) ) {
            $image_html = wp_get_attachment_image( (int) $image_id, 'medium', false, array(
                'class'   => 'lm-authorCard__img',
                'loading' => 'lazy',
                'alt'     => $display_name,
            ) );
        }
        if ( empty( $image_html ) ) {
            $image_html = '<img class="lm-authorCard__img" src="' . esc_url( get_avatar_url( $author_id, array( 'size' => 300 ) ) ) . '" alt="' . esc_attr( $display_name ) . '" loading="lazy" />';
        }

        // Social links from user meta (standard WP + Rank Math fields)
        $socials = array();

        $linkedin = get_the_author_meta( 'linkedin', $author_id );
        if ( empty( $linkedin ) ) {
            $linkedin = get_user_meta( $author_id, 'linkedin', true );
        }
        if ( ! empty( $linkedin ) ) {
            $socials[] = array( 'url' => $linkedin, 'icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn' );
        }

        $twitter = get_the_author_meta( 'twitter', $author_id );
        if ( empty( $twitter ) ) {
            $twitter = get_user_meta( $author_id, 'twitter', true );
        }
        if ( ! empty( $twitter ) ) {
            $url = ( strpos( $twitter, 'http' ) === 0 ) ? $twitter : 'https://x.com/' . ltrim( $twitter, '@' );
            $socials[] = array( 'url' => $url, 'icon' => 'fab fa-x-twitter', 'label' => 'X / Twitter' );
        }

        $facebook = get_the_author_meta( 'facebook', $author_id );
        if ( empty( $facebook ) ) {
            $facebook = get_user_meta( $author_id, 'facebook', true );
        }
        if ( ! empty( $facebook ) ) {
            $socials[] = array( 'url' => $facebook, 'icon' => 'fab fa-facebook-f', 'label' => 'Facebook' );
        }

        $instagram = get_the_author_meta( 'instagram', $author_id );
        if ( empty( $instagram ) ) {
            $instagram = get_user_meta( $author_id, 'instagram', true );
        }
        if ( ! empty( $instagram ) ) {
            $socials[] = array( 'url' => $instagram, 'icon' => 'fab fa-instagram', 'label' => 'Instagram' );
        }

        // Email always present
        $socials[] = array( 'url' => 'mailto:' . antispambot( $email ), 'icon' => 'fas fa-envelope', 'label' => 'Email' );

        // Build card
        $output .= '<article class="lm-authorCard">';
        $output .= '  <a href="' . esc_url( $author_url ) . '" class="lm-authorCard__mediaLink" aria-label="' . esc_attr( $display_name ) . '">';
        $output .= '    <div class="lm-authorCard__media">' . $image_html . '</div>';
        $output .= '  </a>';
        $output .= '  <div class="lm-authorCard__body">';
        $output .= '    <h3 class="lm-authorCard__name"><a href="' . esc_url( $author_url ) . '">' . $display_name . '</a></h3>';

        if ( ! empty( $description ) ) {
            $output .= '    <p class="lm-authorCard__bio">' . $description . '</p>';
        }

        if ( ! empty( $socials ) ) {
            $output .= '    <div class="lm-authorCard__socials">';
            foreach ( $socials as $s ) {
                $output .= '<a href="' . esc_url( $s['url'] ) . '" class="lm-authorCard__socialIcon" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $s['label'] ) . '"><i class="' . esc_attr( $s['icon'] ) . '"></i></a>';
            }
            $output .= '    </div>';
        }

        $output .= '  </div>';
        $output .= '</article>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode( 'author_list', 'lm_author_list_shortcode' );

// =============================================================================
// [promotion_link] - Promotion archive link
// =============================================================================

function display_promotion_link() {
    if (!is_single()) {
        return '';
    }

    global $post;

    $promotion_terms = wp_get_post_terms($post->ID, 'promotion', array('parent' => 0));

    if (empty($promotion_terms) || is_wp_error($promotion_terms)) {
        return '';
    }

    $promotion_term = $promotion_terms[0];
    $promotion_link = get_term_link($promotion_term);

    if (is_wp_error($promotion_link)) {
        return '';
    }

    $current_year = date('Y');

    $markup = '<blockquote style="margin-bottom: 0px;">';
    $markup .= '&gt;&gt; <a style="font-size: inherit;" href="' . esc_url($promotion_link) . '">';
    $markup .= '<span style="font-size: inherit;">' . esc_html($promotion_term->name) . ' : Retrouvez tous nos articles en promotion dans notre sélection ' . $current_year .'</span>';
    $markup .= '</a>';
    $markup .= '</blockquote>';

    return $markup;
}
add_shortcode('promotion_link', 'display_promotion_link');

// =============================================================================
// [show_acf_promotion_data] - Promotion taxonomy ACF data
// =============================================================================

function show_acf_promotion_data_shortcode() {
    $output = '';

    if (is_tax('promotion')) {
        $term_id = get_queried_object_id();
        $taxonomy = get_queried_object()->taxonomy;

        $contenu = get_field('contenu', $taxonomy . '_' . $term_id);
        $faq = get_field('faq', $taxonomy . '_' . $term_id);

        if ($contenu || $faq) {
            $output .= '<div class="acf-promotion-content">';
            if ($contenu) {
                $output .= '<div class="promotion-contenu">' . $contenu . '</div>';
            }
            if ($faq) {
                $output .= '<div class="promotion-faq">' . $faq . '</div>';
            }
            $output .= '</div>';
        } else {
            $output = '<p>Informations promotionnelles non disponibles.</p>';
        }
    } else {
        $output = 'Cette information n\'est pas disponible dans ce contexte.';
    }

    return $output;
}
add_shortcode('show_acf_promotion_data', 'show_acf_promotion_data_shortcode');

// =============================================================================
// [social_share] - Social sharing buttons
// =============================================================================

function custom_social_share_buttons($atts)
{
    wp_enqueue_style('font-awesome');
    $atts = shortcode_atts(
        array(
            'whatsapp'  => 'true',
            'facebook'  => 'true',
            'instagram' => 'true',
            'linkedin'  => 'true',
            'email'     => 'true',
        ),
        $atts,
        'social_share'
    );

    $output = '<div class="social-share-buttons">';

    if ($atts['whatsapp'] === 'true') {
        $output .= '<a href="https://api.whatsapp.com/send?text=' . get_permalink() . '" target="_blank" class="social-icon whatsapp"><i class="fab fa-whatsapp"></i></a>';
    }
    if ($atts['facebook'] === 'true') {
        $output .= '<a href="https://www.facebook.com/sharer/sharer.php?u=' . get_permalink() . '" target="_blank" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>';
    }
    if ($atts['instagram'] === 'true') {
        $output .= '<a href="https://www.instagram.com/?url=' . get_permalink() . '" target="_blank" class="social-icon instagram"><i class="fab fa-instagram"></i></a>';
    }
    if ($atts['linkedin'] === 'true') {
        $output .= '<a href="https://www.linkedin.com/shareArticle?mini=true&url=' . get_permalink() . '" target="_blank" class="social-icon linkedin"><i class="fab fa-linkedin-in"></i></a>';
    }
    if ($atts['email'] === 'true') {
        $output .= '<a href="mailto:?subject=' . get_the_title() . '&body=' . get_permalink() . '" class="social-icon email"><i class="fas fa-envelope"></i></a>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('social_share', 'custom_social_share_buttons');

// =============================================================================
// [qd_video] - Video embed (YouTube/TikTok) from ACF or URL param
// =============================================================================

add_shortcode('qd_video', function($atts) {
  $url = '';

  if (!empty($atts['url'])) {
    $url = $atts['url'];
  } elseif (function_exists('get_field')) {
    $url = get_field('video_url');
  }

  if (!$url) return '';

  return '<div class="qd-video" style="aspect-ratio:16/9;">
    <iframe
      src="'.esc_url($url).'"
      width="100%"
      height="100%"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen
      loading="lazy">
    </iframe>
  </div>';
});

// =============================================================================
// [lm_seo_only_first] - Show content only on page 1 of pagination
// =============================================================================

function lm_seo_only_first_shortcode($atts, $content = null) {

    if (is_admin()) {
        return '';
    }

    $paged = max(1, get_query_var('paged'));

    if ($paged > 1) {
        return '';
    }

    return do_shortcode($content);
}
add_shortcode('lm_seo_only_first', 'lm_seo_only_first_shortcode');

// =============================================================================
// [c2s_widget] - Clic2Shop widget (from functions.php)
// =============================================================================

add_action('init', function () {

    // Add async+defer to c2s scripts
    add_filter('script_loader_tag', function ($tag, $handle, $src) {
        if (strpos($handle, 'c2s-') !== 0) {
            return $tag;
        }

        if (strpos($tag, ' async') === false) {
            $tag = str_replace(' src=', ' async src=', $tag);
        }
        if (strpos($tag, ' defer') === false) {
            $tag = str_replace(' async src=', ' async defer src=', $tag);
        }
        return $tag;
    }, 10, 3);

    add_shortcode('c2s_widget', function ($atts) {
        $atts = shortcode_atts([
            'wid'    => '',
            'method' => 'div',
            'class'  => 'c2s-widget',
        ], $atts, 'c2s_widget');

        $wid = sanitize_text_field($atts['wid']);
        if (empty($wid)) {
            return '<!-- c2s_widget: wid manquant -->';
        }

        $method = strtolower(sanitize_text_field($atts['method']));

        // Method 2: DIV + wmain.js (recommended)
        if ($method !== 'script') {
            $handle = 'c2s-wmain';
            $src    = 'https://lbm.clic2shop.com/widget/wmain.js';

            if (!wp_script_is($handle, 'enqueued')) {
                wp_enqueue_script($handle, $src, [], null, true);
            }

            return sprintf(
                '<div class="%s" data-c2s-wid="%s"></div>',
                esc_attr($atts['class']),
                esc_attr($wid)
            );
        }

        // Method 1: Direct script affichage.js?wid=...
        $wid_enc = rawurlencode($wid);
        $src     = 'https://lbm.clic2shop.com/widget/affichage.js?wid=' . $wid_enc;

        $handle = 'c2s-affichage-' . substr(md5($src), 0, 10);

        if (!wp_script_is($handle, 'enqueued')) {
            wp_enqueue_script($handle, $src, [], null, true);
        }

        return '<!-- c2s_widget: affichage.js chargé -->';
    });
});

// =============================================================================
// [show_term_c2s_widget] - Show C2S widget for taxonomy term
// =============================================================================

add_shortcode('show_term_c2s_widget', function () {
    if (!is_tax() && !is_category() && !is_tag()) {
        return '';
    }

    $term_id = get_queried_object_id();
    if (!$term_id) {
        return '';
    }

    $sc = get_field('c2s_shortcode', 'term_' . $term_id);
    if (empty($sc)) {
        return '';
    }

    return do_shortcode($sc);
});
