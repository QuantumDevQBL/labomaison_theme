<?php
/**
 * Product Display Shortcodes
 *
 * Shortcodes for displaying product information.
 *
 * @package Labomaison
 * @subpackage Shortcodes
 * @since 2.0.0
 *
 * Shortcodes:
 * - [afficher_caracteristiques]
 * - [ou_acheter]
 * - [afficher_galerie]
 * - [afficher_pros_cons]
 * - [display_faq]
 * - [display_conclusion]
 * - [display_contenu]
 * - [display_presentation]
 * - [display_chapeau]
 * - [afficher_custom_data]
 *
 * Dependencies: ACF, utilities/helpers.php
 * Load Priority: 6
 * Risk Level: LOW
 *
 * Migrated from: shortcode_list.php L3-84, L169-267, L613-628, L966-1129
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// [afficher_caracteristiques] - Product specifications table
// =============================================================================

function afficher_caracteristiques_shortcode($atts)
{
  $atts = shortcode_atts(array(
    'post_id' => get_the_ID(),
  ), $atts);
  $post_id = $atts['post_id'];

  // Initialiser le contenu
  $contenu = "";
  $caracteristiques_existent = false;
  // Vérifier d'abord si des caractéristiques valides existent
  if (have_rows('caracteristiques_du_produit', $post_id)) {
    while (have_rows('caracteristiques_du_produit', $post_id) && !$caracteristiques_existent) {
      the_row();
      foreach (get_row(true) as $sub_field_key => $sub_field_value) {
        if ($sub_field_key !== 'acf_fc_layout' && !empty($sub_field_value)) {
          $caracteristiques_existent = true;
          break;
        }
      }
    }
  }

  if (!$caracteristiques_existent) {
    return "";
  }

  $contenu .= "<div class='caracteristiques_container'>";
  $contenu .= "<h2 id='caracteristiques_title' class='gb-headline gb-headline-ef57cddc gb-headline-text'>Fiche Technique / Caractéristiques</h2>";
  $contenu .= "<div class='fiche-technique' id='caracteristiques-{$post_id}'>";
  $contenu .= "<table class='table-striped'>";

  while (have_rows('caracteristiques_du_produit', $post_id)) {
    the_row();

    foreach (get_row(true) as $sub_field_key => $sub_field_value) {
      if ($sub_field_key !== 'acf_fc_layout' && !empty($sub_field_value)) {
        $caracteristiques_existent = true;
        $sub_field_object = get_sub_field_object($sub_field_key);

        if ($sub_field_object) {
          $label = $sub_field_object['label'];
          $append = isset($sub_field_object['append']) ? " {$sub_field_object['append']}" : '';

          // Gestion des valeurs booléennes
          if (is_bool($sub_field_value)) {
            $sub_field_value = $sub_field_value ? 'Oui' : 'Non';
          }

          // Traitement des tableaux pour les afficher en pills
          if (is_array($sub_field_value)) {
            $i = 0;
            $sub_field_value_formatted = array_map(function ($value) use (&$i) {
              $class = $i % 2 == 0 ? 'value-pill-even' : 'value-pill-odd';
              $i++;
              return "<span class='$class'>" . htmlspecialchars($value) . "</span>";
            }, $sub_field_value);
            $sub_field_value = implode(' ', $sub_field_value_formatted);
          }

          // Décodage des chaînes de caractères
          if (is_string($sub_field_value)) {
            $sub_field_value = html_entity_decode($sub_field_value, ENT_QUOTES, "UTF-8");
          }

          $value_display = $sub_field_value . $append;
          if (!empty(trim($value_display))) {
            $contenu .= "<tr class='caracteristique'><td class='nom'>{$label}</td><td class='valeur'>{$value_display}</td></tr>";
          }
        }
      }
    }
  }
  $contenu .= "</table></div></div>";

  return $contenu;
}
add_shortcode('afficher_caracteristiques', 'afficher_caracteristiques_shortcode');

// =============================================================================
// [ou_acheter] - Where to buy section
// =============================================================================

function afficher_ou_acheter_shortcode()
{
  global $post;

  $ou_acheter_titre = get_field('ou_acheter_titre', $post->ID);
  $contenu_acheter = get_field('ou_acheter_contenu');

  if (!$ou_acheter_titre || !$contenu_acheter) {
    return '';
  }

  $contenu = "<div class='ou_acheter_container'>";
  $contenu .= "<h2 id='ou_acheter_title' class='ou_acheter_title'>$ou_acheter_titre</h2>";
  if ($contenu_acheter) {
    $contenu .= "<div class='ou-acheter-contenu'>" . $contenu_acheter . "</div>";
  } else {
    $contenu .= "<p>Contenu à venir...</p>";
  }

  $contenu .= "</div>";

  return $contenu;
}
add_shortcode('ou_acheter', 'afficher_ou_acheter_shortcode');

// =============================================================================
// [afficher_galerie] - Product image gallery
// =============================================================================

function afficher_galerie_shortcode($atts)
{
  $atts = shortcode_atts(array(
    'position' => 'debut',
  ), $atts);

  $images = get_field('gallerie_produit', false, false);
  $image_feature_id = get_post_thumbnail_id();
  $contenu = '<div class="galerie-images" id="gallerie_produit_title">';

  if ($images) {
    $selected_images = ($atts['position'] === 'fin') ? array_slice($images, -2, 2) : array_slice($images, 0, 2);

    $size_class_first = ($atts['position'] === 'fin') ? 'small' : 'large';
    $size_class_second = ($atts['position'] === 'fin') ? 'large' : 'small';

    foreach ($selected_images as $index => $image_id) {
      $image_url = wp_get_attachment_url($image_id);
      $size_class = ($index === 0) ? $size_class_first : $size_class_second;
      $contenu .= "<img src='" . esc_url($image_url) . "' class='" . $size_class . "' alt='' />";
    }
  } else {
    $contenu .= '<p>Aucune image trouvée.</p>';
  }

  $contenu .= '</div>';
  return $contenu;
}
add_shortcode('afficher_galerie', 'afficher_galerie_shortcode');

// =============================================================================
// [afficher_pros_cons] - Pros and cons display
// =============================================================================

function afficher_pros_cons_shortcode($atts)
{
  $atts = shortcode_atts(array(
    'post_id' => get_the_ID(),
  ), $atts);

  $post_id = $atts['post_id'];

  $points_forts = get_field('points_forts', $post_id);
  $points_faibles = get_field('points_faibles', $post_id);

  if (!$points_forts || !$points_faibles) {
    return "";
  }

  $html = '<div id="pros-cons-container_title" class="pros-cons-container_shortcode" style="display: flex; flex-wrap: wrap; gap: 20px;">';

  // Points forts
  $html .= '<div class="pros square-box">';
  $html .= '<div class="content"><h3>Points forts</h3>';
  $html .= $points_forts ? $points_forts : 'Pas de points forts définis.';
  $html .= '</div></div>';

  // Points faibles
  $html .= '<div class="cons square-box">';
  $html .= '<div class="content"><h3>Points faibles</h3>';
  $html .= $points_faibles ? $points_faibles : 'Pas de points faibles définis.';
  $html .= '</div></div>';

  $html .= '</div>';

  return $html;
}
add_shortcode('afficher_pros_cons', 'afficher_pros_cons_shortcode');

// =============================================================================
// [display_chapeau] - Chapeau/intro text
// =============================================================================

function display_chapeau_shortcode()
{
  $chapeau_content = get_field('chapeau');

  if (!$chapeau_content) {
    return '';
  }

  $output = '<div class="chapeau-content">' . wp_kses_post($chapeau_content) . '</div>';

  return $output;
}
add_shortcode('display_chapeau', 'display_chapeau_shortcode');

// =============================================================================
// [afficher_custom_data] - Custom product title (marque + nom)
// =============================================================================

function afficher_custom_data_shortcode($atts)
{
  global $post;
  $atts = shortcode_atts(['post_id' => $post->ID], $atts);
  $post_id = $atts['post_id'];

  $nom = get_field('nom', $post_id);

  $marque_posts = get_field('marque', $post_id);
  $marque_title = '';

  if (!empty($marque_posts) && is_array($marque_posts)) {
    $marque_post = reset($marque_posts);
    $marque_title = get_the_title($marque_post->ID);
  }

  $test_card_title = trim($marque_title . ' ' . $nom);

  if (empty($test_card_title)) {
    $test_card_title = get_the_title($post_id);
  }

  return esc_html($test_card_title);
}
add_shortcode('afficher_custom_data', 'afficher_custom_data_shortcode');

// =============================================================================
// [display_faq] - FAQ section
// =============================================================================

function display_faq_section()
{
  $faq_title = get_field('faq_titre');
  $faq_content = get_field('faq_contenu');

  if (!$faq_title || !$faq_content) {
    return '';
  }

  $output = '';

  if (!empty($faq_title) && !empty($faq_content)) {
    $output .= '<div class="faq-section">';
    $output .= '<h2 id="faq_title">' . esc_html($faq_title) . '</h2>';
    $output .= '<div>' . wp_kses_post($faq_content) . '</div>';
    $output .= '</div>';
  }

  return $output;
}
add_shortcode('display_faq', 'display_faq_section');

// =============================================================================
// [display_conclusion] - Conclusion section with global rating
// =============================================================================

function display_conclusion_section()
{
  $conclusion = get_field('conclusion');

  if (!$conclusion) {
    return '';
  }

  $output = '';

  if ($conclusion) {
    $output .= '<div class="conclusion-section">';
    $output .= '<div class="title_container">';
    $output .= '<h2 id="conclusion_title">Conclusion</h2>';
    $output .= do_shortcode('[afficher_note_globale]');
    $output .= '</div>';
    $output .= '<div>' . wp_kses_post($conclusion) . '</div>';
    $output .= '</div>';
  }

  return $output;
}
add_shortcode('display_conclusion', 'display_conclusion_section');

// =============================================================================
// [display_contenu] - Test content/review section
// =============================================================================

function display_contenu_section()
{
  $avis_de_labomaison = get_field('avis_de_labomaison');
  $contenu_du_test = get_field('contenu_du_test');

  if (!$avis_de_labomaison || !$contenu_du_test) {
    return '';
  }

  $output = '';

  if ($avis_de_labomaison && $contenu_du_test) {
    $output .= '<div class="contenu-section">';
    $output .= '<h2 id="contenu_title">' . esc_html($avis_de_labomaison) . '</h2>';
    $output .= '<div>' . wp_kses_post($contenu_du_test) . '</div>';
    $output .= '</div>';
  }

  return $output;
}
add_shortcode('display_contenu', 'display_contenu_section');

// =============================================================================
// [display_presentation] - Presentation section with iframe filtering
// =============================================================================

function display_presentation_section()
{
  $presentation_title = get_field('presentation_titre');
  $presentation_content = get_field('presentation_contenu');

  if (!$presentation_title || !$presentation_content) {
    return '';
  }

  // Autoriser uniquement les balises sûres + iframe YouTube/TikTok
  $allowed_tags = wp_kses_allowed_html('post');
  $allowed_tags['iframe'] = [
    'src'             => true,
    'width'           => true,
    'height'          => true,
    'frameborder'     => true,
    'allow'           => true,
    'allowfullscreen' => true,
    'title'           => true,
  ];

  $output = '';

  // Filtrage renforcé : on autorise seulement iframe YouTube et TikTok
  $filtered_content = preg_replace_callback(
    '/<iframe.*?src=["\'](.*?)["\'].*?>.*?<\/iframe>/is',
    function ($matches) use ($allowed_tags) {
      $src = $matches[1];
      if (
        strpos($src, 'youtube.com/embed/') !== false ||
        strpos($src, 'tiktok.com/embed') !== false ||
        strpos($src, 'tiktok.com/') !== false
      ) {
        return wp_kses($matches[0], $allowed_tags);
      }
      return '';
    },
    $presentation_content
  );

  $filtered_content = wp_kses($filtered_content, $allowed_tags);

  $output .= '<div class="presentation-section">';
  $output .= '<h2 id="presentation_title">' . esc_html($presentation_title) . '</h2>';
  $output .= '<div>' . $filtered_content . '</div>';
  $output .= '</div>';

  return $output;
}
add_shortcode('display_presentation', 'display_presentation_section');
