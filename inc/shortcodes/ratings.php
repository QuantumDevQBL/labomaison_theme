<?php
/**
 * Rating Shortcodes
 *
 * Shortcodes for displaying ratings and stars.
 *
 * @package Labomaison
 * @subpackage Shortcodes
 * @since 2.0.0
 *
 * Shortcodes:
 * - [afficher_notes]
 * - [afficher_note_globale]
 * - [afficher_note_globale_card]
 *
 * Dependencies: ACF, utilities/helpers.php
 * Load Priority: 6
 * Risk Level: LOW
 *
 * Migrated from: shortcode_list.php L88-166, L933-964
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// [afficher_notes] - Detailed sub-ratings with stars
// =============================================================================

function afficher_notes_shortcode($atts) {
    $atts = shortcode_atts(['post_id' => get_the_ID()], $atts);
    $post_id = $atts['post_id'];

    $contenu = "<div class='notes-produit'>";
    if (have_rows('notes_du_produit', $post_id)) {
        $contenu .= "<h2 id='notes_du_produit_title' class='notes-produit-title'>Sous-notes</h2><div class='notes_container'>";

        while (have_rows('notes_du_produit', $post_id)) {
            the_row();

            foreach (get_row(true) as $sub_field_key => $sub_field_value) {
                if ($sub_field_key !== 'acf_fc_layout' && !empty($sub_field_value)) {
                    $sub_field_object = get_sub_field_object($sub_field_key);
                    if ($sub_field_object) {
                        $label = esc_html($sub_field_object['label']);
                        $valeur = (float) $sub_field_object['value'];

                        $contenu .= "<div class='note-critere'><span>{$label}</span>";
                        $contenu .= "<div class='etoiles-container'>";

                        for ($i = 1; $i <= 5; $i++) {
                            $fill = $valeur >= $i ? 100 : ($valeur > $i - 1 ? ($valeur - floor($valeur)) * 100 : 0);
                            $contenu .= "<svg class='etoile' width='20' height='20' viewBox='0 0 50 50'>
                                <defs>
                                    <linearGradient id='grad-{$sub_field_key}-{$i}'>
                                        <stop offset='{$fill}%' stop-color='gold'/>
                                        <stop offset='{$fill}%' stop-color='gray'/>
                                    </linearGradient>
                                </defs>
                                <polygon points='25,1 32,19 50,19 35,30 40,48 25,37 10,48 15,30 0,19 18,19' fill='url(#grad-{$sub_field_key}-{$i})'/>
                            </svg>";
                        }

                        $contenu .= "</div></div>";
                    }
                }
            }
        }
        $contenu .= "</div>";
    } else {
        return '';
    }

    $contenu .= "</div>";
    return $contenu;
}
add_shortcode('afficher_notes', 'afficher_notes_shortcode');

// =============================================================================
// [afficher_note_globale] - Global rating with stars
// =============================================================================

function afficher_note_globale_shortcode($atts) {
    global $post;
    $atts = shortcode_atts(['post_id' => $post->ID], $atts);
    $note_globale = get_field('note_globale', $atts['post_id']);

    if (empty($note_globale)) {
        return '';
    }

    $note_globale = is_numeric($note_globale) ? (float) $note_globale : 0.0;
    $contenu = "<div class='note-globale'><div class='etoiles-container'>";

    for ($i = 1; $i <= 5; $i++) {
        $fill = $note_globale >= $i ? 100 : ($note_globale > $i - 1 ? ($note_globale - floor($note_globale)) * 100 : 0);
        $contenu .= "<svg class='etoile' width='20' height='20' viewBox='0 0 50 50'>
            <defs>
                <linearGradient id='grad-global-{$i}'>
                    <stop offset='{$fill}%' stop-color='gold'/>
                    <stop offset='{$fill}%' stop-color='gray'/>
                </linearGradient>
            </defs>
            <polygon points='25,1 32,19 50,19 35,30 40,48 25,37 10,48 15,30 0,19 18,19' fill='url(#grad-global-{$i})'/>
        </svg>";
    }

    $contenu .= "</div></div>";
    return $contenu;
}
add_shortcode('afficher_note_globale', 'afficher_note_globale_shortcode');

// =============================================================================
// [afficher_note_globale_card] - Global rating for card context
// =============================================================================

function afficher_note_globale_shortcode_card($atts)
{
  global $post;
  $atts = shortcode_atts(['post_id' => $post->ID], $atts);
  $post_id = $atts['post_id'];

  if (property_exists($post, 'note_globale') && is_numeric($post->note_globale)) {
    $note_globale = (float)$post->note_globale;
  } else {
    $note_globale = is_numeric(get_field('note_globale', $post_id)) ? (float)get_field('note_globale', $post_id) : 0.0;
  }

  if (empty($note_globale)) {
    return '';
  }

  $contenu = "<div class='note-globale'><div class='etoiles-container'>";
  for ($i = 1; $i <= 5; $i++) {
    $fill = $note_globale >= $i ? 100 : ($note_globale > $i - 1 ? ($note_globale - floor($note_globale)) * 100 : 0);
    $gradient_id = "grad-{$post_id}-{$i}";
    $contenu .= "<svg width='20' height='20' viewbox='0 0 50 50' class='etoile'><defs><linearGradient id='{$gradient_id}'><stop offset='{$fill}%' stop-color='gold' /><stop offset='{$fill}%' stop-color='gray' /></linearGradient></defs><polygon points='25,1 32,19 50,19 35,30 40,48 25,37 10,48 15,30 0,19 18,19' fill='url(#{$gradient_id})'/></svg>";
  }

  $contenu .= "</div></div>";

  return $contenu;
}
add_shortcode('afficher_note_globale_card', 'afficher_note_globale_shortcode_card');
