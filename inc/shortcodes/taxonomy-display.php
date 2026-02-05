<?php
/**
 * Taxonomy Display Shortcodes
 *
 * Shortcodes for displaying taxonomy and category information.
 *
 * @package Labomaison
 * @subpackage Shortcodes
 * @since 2.0.0
 *
 * Shortcodes:
 * - [show_acf_titre]
 * - [show_acf_contenu]
 * - [show_acf_faq]
 * - [show_acf_chapeau]
 * - [display_category]
 * - [display_last_updated_category]
 * - [test_archive_field]
 * - [category_link]
 * - [post_category_link]
 *
 * Dependencies: ACF
 * Load Priority: 6
 * Risk Level: LOW-MEDIUM
 *
 * Migrated from: shortcode_list.php L458-770, L1616-1677
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// [show_acf_titre] - Taxonomy title from ACF
// =============================================================================

function display_acf_titre_shortcode()
{
  $output = '';

  if (is_tag() || is_tax() || is_category()) {
    $term_id = get_queried_object_id();
    $taxonomy = get_queried_object()->taxonomy;

    $titre = get_field('titre', $taxonomy . '_' . $term_id);

    if ($titre) {
      $output = '<h1>' . esc_html($titre) . '</h1>';
    } else {
      $term = get_queried_object();
      $output = '<h1>' . esc_html($term->name) . '</h1>';
    }
  } else {
    $output = 'Ce n\'est pas une taxonomie.';
  }

  return $output;
}
add_shortcode('show_acf_titre', 'display_acf_titre_shortcode');

// =============================================================================
// [show_acf_contenu] - Taxonomy content from ACF
// =============================================================================

function display_acf_contenu_shortcode()
{
  $output = '';

  if (is_tag() || is_tax() || is_category()) {
    $term_id = get_queried_object_id();
    $taxonomy = get_queried_object()->taxonomy;

    $contenu = get_field('contenu', $taxonomy . '_' . $term_id);

    if ($contenu) {
      $output = '<div class="tax_content">' . wp_kses_post($contenu) . '</div>';
    }
  } else {
    return;
  }

  return $output;
}
add_shortcode('show_acf_contenu', 'display_acf_contenu_shortcode');

// =============================================================================
// [show_acf_faq] - Taxonomy FAQ from ACF
// =============================================================================

function display_acf_faq_shortcode()
{
  $output = '';

  if (is_tag() || is_tax() || is_category()) {
    $term_id = get_queried_object_id();
    $taxonomy = get_queried_object()->taxonomy;

    $faq = get_field('faq', $taxonomy . '_' . $term_id);

    if ($faq) {
      $output = '<div id="faq_title" class="tax_faq">' . wp_kses_post($faq) . '</div>';
    }
  } else {
    return;
  }

  return $output;
}
add_shortcode('show_acf_faq', 'display_acf_faq_shortcode');

// =============================================================================
// [show_acf_chapeau] - Taxonomy chapeau from ACF
// =============================================================================

function display_acf_chapeau_shortcode()
{
  $output = '';

  if (is_tag() || is_tax() || is_category()) {
    $term_id = get_queried_object_id();
    $taxonomy = get_queried_object()->taxonomy;

    $chapeau = get_field('chapeau', $taxonomy . '_' . $term_id);

    if ($chapeau) {
      $output = $chapeau;
    } else {
      $term = get_queried_object();
      $output = wp_kses_post($term->description);
    }
  } else {
    $output = 'Not a taxonomy term context.';
  }

  return $output;
}
add_shortcode('show_acf_chapeau', 'display_acf_chapeau_shortcode');

// =============================================================================
// [display_category] - Display category info card
// =============================================================================

function display_category_info_shortcode($atts)
{
    $atts = shortcode_atts(['term_id' => 0], $atts);
    $term_id = intval($atts['term_id']);

    if (!$term_id) {
        return 'Veuillez spécifier un ID de terme valide.';
    }

    $term = get_term($term_id);

    if (is_wp_error($term) || !$term) {
        return 'Le terme spécifié est introuvable.';
    }

    $image_id = get_field('featured', $term);
    $titre = get_field('titre', $term) ?: $term->name;
    $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
    $term_url = get_term_link($term);

    $output = '<div class="category_container publication_template_container small_image mobile_image">';
    $output .= '<a href="' . esc_url($term_url) . '" class="category_featured_link" style="background-image: url(' . esc_url($image_url) . ');">';
    $output .= '<div class="header_thumbnail_container">';
    $output .= '<h2 class="gb-headline gb-headline-text">' . esc_html($titre) . '</h2>';
    $output .= '</div>';
    $output .= '</a>';
    $output .= '</div>';

    return $output;
}
add_shortcode('display_category', 'display_category_info_shortcode');

// =============================================================================
// [display_last_updated_category] - Last updated test category
// =============================================================================

function display_last_updated_category_info_shortcode()
{
  $category_id = get_option('last_updated_test_category_id');

  if (!$category_id) {
    $args = [
      'taxonomy' => 'categorie_test',
      'orderby' => 'modified',
      'order' => 'DESC',
      'number' => 1,
    ];
    $categories = get_terms($args);

    if (!is_wp_error($categories) && !empty($categories)) {
      $category = array_shift($categories);
      $category_id = $category->term_id;
    }
  }

  if (!$category_id) {
    $default_category = get_terms([
      'taxonomy' => 'categorie_test',
      'number' => 1,
    ]);

    if (!is_wp_error($default_category) && !empty($default_category)) {
      $category = array_shift($default_category);
      $category_id = $category->term_id;
    }
  }

  $category = get_term($category_id, 'categorie_test');
  if (is_wp_error($category) || !$category) {
    $fallback_category = get_terms([
      'taxonomy' => 'categorie_test',
      'number' => 1,
    ]);

    if (!is_wp_error($fallback_category) && !empty($fallback_category)) {
      $category = array_shift($fallback_category);
      $category_id = $category->term_id;
    } else {
      return 'Catégories non trouvées.';
    }
  }

  $image_id = get_field('featured', 'categorie_test_' . $category_id);
  $categorie_titre = get_field('titre', 'categorie_test_' . $category_id) ?: $category->name;
  $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
  $category_url = get_term_link($category);

  $output = '<div class="category_container publication_template_container small_image mobile_image">';
  $output .= '<a href="' . esc_url($category_url) . '" class="category_featured_link" style="background-image: url(' . esc_url($image_url) . ');">';
  $output .= '<div class="header_thumbnail_container">';
  $output .= '<h2 class="gb-headline gb-headline-text">' . esc_html($categorie_titre) . '</h2>';
  $output .= '</div>';
  $output .= '</a>';
  $output .= '</div>';

  return $output;
}
add_shortcode('display_last_updated_category', 'display_last_updated_category_info_shortcode');

// =============================================================================
// [test_archive_field] - Display any ACF field
// =============================================================================

function test_archive_acf_field_shortcode($atts)
{
  $atts = shortcode_atts(array(
    'post_id' => get_the_ID(),
    'field_name' => '',
  ), $atts);

  if (empty($atts['field_name'])) {
    return 'ACF field name not specified.';
  }

  $post_id = $atts['post_id'];
  $field_name = $atts['field_name'];
  $field_value = get_field($field_name, $post_id);

  $field_object = get_field_object($field_name, $post_id);
  if (!$field_object) {
    return 'Invalid ACF field name.';
  }

  $field_type = $field_object['type'];
  $content = "<div class='acf-field'>";

  switch ($field_type) {
    case 'text':
    case 'textarea':
    case 'number':
    case 'select':
    case 'radio':
      $content .= "<span class='label'>{$field_object['label']}</span>: <span class='value'>{$field_value}</span>";
      break;

    case 'image':
      $image_url = $field_value['url'];
      $content .= "<span class='label'>{$field_object['label']}</span>: <img src='{$image_url}' alt='{$field_value['alt']}' />";
      break;

    case 'wysiwyg':
      $content .= "<span class='label'>{$field_object['label']}</span>: <div class='value'>{$field_value}</div>";
      break;
  }

  $content .= "</div>";

  return $content;
}
add_shortcode('test_archive_field', 'test_archive_acf_field_shortcode');

// =============================================================================
// [category_link] - Link to test category from test CPT
// =============================================================================

function category_link_shortcode() {
  global $post;

  $test_categories = get_the_terms($post->ID, 'categorie_test');
  if ($test_categories && !is_wp_error($test_categories)) {
      $category = $test_categories[0];
      $category_link = get_term_link($category);

      if (!is_wp_error($category_link)) {
          $current_year = date('Y');

          $markup = '<blockquote style="margin-bottom: 0px;">';
          $markup .= '&gt;&gt; <a style="font-size: inherit;" href="' . esc_url($category_link) . '">';
          $markup .= '<span style="font-size: inherit;">' . esc_html($category->name) . ' : Retrouvez tous nos tests et fiches produits dans notre comparatif ' . $current_year .'</span>';
          $markup .= '</a>';
          $markup .= '</blockquote>';
          return $markup;
      }
  }

  return '';
}
add_shortcode('category_link', 'category_link_shortcode');

// =============================================================================
// [post_category_link] - Link to test category from post (mapped by slug)
// =============================================================================

function post_category_link_shortcode() {
  global $post;

  $post_categories = get_the_terms($post->ID, 'category');
  if ($post_categories && !is_wp_error($post_categories)) {
      $category = $post_categories[0];
      $category_slug = $category->slug;

      $test_category = get_terms([
          'taxonomy' => 'categorie_test',
          'slug' => $category_slug,
          'hide_empty' => false,
      ]);

      if ($test_category && !is_wp_error($test_category) && !empty($test_category)) {
          $test_category = $test_category[0];
          $category_link = get_term_link($test_category);

          if (!is_wp_error($category_link)) {
            $current_year = date('Y');
            $markup = '<blockquote style="margin-bottom: 0px;">';
            $markup .= '&gt;&gt; <a style="font-size: inherit;" href="' . esc_url($category_link) . '">';
            $markup .= '<span style="font-size: inherit;">' . esc_html($category->name) . ' : Retrouvez tous nos tests et fiches produits dans notre comparatif ' . $current_year .'</span>';
            $markup .= '</a>';
            $markup .= '</blockquote>';
            return $markup;
          }
      }
  }

  return '';
}
add_shortcode('post_category_link', 'post_category_link_shortcode');
