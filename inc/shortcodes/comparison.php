<?php
/**
 * Comparison Shortcodes
 *
 * Shortcodes for product comparison functionality.
 *
 * @package Labomaison
 * @subpackage Shortcodes
 * @since 2.0.0
 *
 * Shortcodes:
 * - [comparaison_form]
 * - [show_comparison]
 * - [show_acf_relationship_data]
 *
 * Dependencies: ACF, other shortcodes
 * Load Priority: 6
 * Risk Level: MEDIUM
 *
 * Migrated from: shortcode_list.php L326-456
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// [comparaison_form] - Comparison form with checkboxes
// =============================================================================

function wpb_comparaison_form_shortcode()
{
  $form = '<form action="/comparatif" method="get" id="comparaison-form">';
  $form .= '<input type="hidden" id="selectedTest1" name="test1">';
  $form .= '<input type="hidden" id="selectedTest2" name="test2">';
  $form .= '<button type="submit" id="btn-comparer">Comparer</button>';
  $form .= '</form>';

  $form .= '<script>
        document.getElementById("comparaison-form").addEventListener("submit", function(event) {
            var checkedBoxes = document.querySelectorAll(".custom-checkbox:checked");
            if (checkedBoxes.length !== 2) {
                alert("Veuillez sélectionner exactement deux produits pour comparer.");
                event.preventDefault();
            }
        });
    </script>';

  return $form;
}
add_shortcode('comparaison_form', 'wpb_comparaison_form_shortcode');

// =============================================================================
// [show_comparison] - Display comparison between two tests
// =============================================================================

function display_comparison_shortcode()
{
  $output = '';

  if (isset($_GET['test1']) && isset($_GET['test2'])) {
    $test1_id = intval($_GET['test1']);
    $test2_id = intval($_GET['test2']);

    if ($test1_id > 0 && $test2_id > 0) {
      $output .= '<div class="comparaison-container">';

      $tests = [$test1_id, $test2_id];
      foreach ($tests as $test_id) {
        $post = get_post($test_id);
        if ($post) {

          $image_url = get_the_post_thumbnail_url($post, 'full');

          $nom = get_field('nom', $test_id);
          $auteur_id = get_field('auteur', $test_id);
          $auteur_obj = get_userdata($auteur_id);
          $auteur_name = $auteur_obj->display_name;
          $auteur_url = get_author_posts_url($auteur_id);
          $points_forts = get_field('points_forts', $test_id);
          $points_faibles = get_field('points_faibles', $test_id);
          $type_de_produit = get_field('type_de_produit', $test_id);
          $note_globale = get_field('note_globale', $test_id);
          $gallerie_produit = get_field('gallerie_produit', $test_id);
          $prix = get_field('prix', $test_id);

          $output .= '<div class="test">';
          $output .= do_shortcode('[afficher_caracteristiques post_id="' . $test_id . '"]');
          $output .= do_shortcode('[afficher_notes post_id="' . $test_id . '"]');
          $output .= do_shortcode('[afficher_pros_cons post_id="' . $test_id . '"]');

          $output .= '<h2>' . esc_html(get_the_title($post)) . '</h2>';
          $output .= '<p>Auteur: <a href="' . esc_url($auteur_url) . '">' . esc_html($auteur_name) . '</a></p>';
          if ($image_url) {
            $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($nom) . '">';
          }

          $output .= '<p>Nom: ' . esc_html($nom) . '</p>';
          $output .= '<p>Points Forts: ' . esc_html($points_forts) . '</p>';
          $output .= '<p>Points Faibles: ' . esc_html($points_faibles) . '</p>';

          $output .= '<div>' . apply_filters('the_content', $post->post_content) . '</div>';
          $output .= '</div>';
        }
      }

      $output .= '</div>';
    } else {
      $output .= '<p>Les ID de tests fournis ne sont pas valides.</p>';
    }
  } else {
    $output .= '<p>Veuillez sélectionner des produits pour les comparer.</p>';
  }

  return $output;
}
add_shortcode('show_comparison', 'display_comparison_shortcode');

// =============================================================================
// [show_acf_relationship_data] - Display ACF relationship fields
// =============================================================================

function display_acf_relationship_data()
{
  if (!function_exists('get_field')) return;

  $output = '';
  $post_id = get_the_ID();

  $types = get_field('type_de_produit', $post_id);

  if ($types) {
    $output .= 'Type de produit: ';
    foreach ($types as $type) {
      $output .= get_the_title($type) . ', ';
    }
    $output = rtrim($output, ', ') . '<br>';
  }

  $brands = get_field('marque', $post_id);
  if ($brands) {
    $output .= 'Marque: ';
    foreach ($brands as $brand) {
      $output .= get_the_title($brand->ID) . ', ';
    }
    $output = rtrim($output, ', ');
  }

  return $output;
}
add_shortcode('show_acf_relationship_data', 'display_acf_relationship_data');
