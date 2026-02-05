<?php
/**
 * Related Content Shortcodes
 *
 * Shortcodes for displaying related posts and associated content.
 *
 * @package Labomaison
 * @subpackage Shortcodes
 * @since 2.0.0
 *
 * Shortcodes:
 * - [linked_test_category]
 * - [linked_test_category_post_thumbnail]
 * - [linked_test_category_complete]
 * - [display_related_news]
 * - [display_related_test_categories]
 * - [display_related_news_for_test]
 * - [display_associated_tests]
 * - [display_related_buying_guides]
 * - [related_articles]
 * - [associated_news]
 * - [associated_products]
 * - [buying_guides_and_comparisons]
 *
 * Dependencies: ACF, WP_Query, utilities/helpers.php
 * Load Priority: 6
 * Risk Level: MEDIUM
 *
 * Migrated from: shortcode_list.php L823-931, L1172-1548, L1835-1944, L1977-2145,
 *                L2311-2463, L2518-2574, L2577-2708
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// [linked_test_category] - Display categorie_test link for post or test
// =============================================================================

function display_linked_test_category($atts) {
    global $post;
    $atts = shortcode_atts(['post_id' => $post ? $post->ID : null], $atts);
    $post_id = $atts['post_id'];
    if (empty($post_id)) return '';

    $output = '';
    $post_type = get_post_type($post_id);

    if ($post_type === 'post') {
        $categories = get_the_category($post_id);
        if (!empty($categories) && !is_wp_error($categories)) {
            $selected_category = $categories[0];

            $test_category = get_term_by('slug', $selected_category->slug, 'categorie_test');

            if ($test_category && !is_wp_error($test_category)) {
                $term_link = get_term_link($test_category, 'categorie_test');
                if (!is_wp_error($term_link)) {
                    $output .= '<span class="test-category-link term_absolute post-term-item term-' . esc_attr($test_category->slug) . '">';
                    $output .= '<a href="' . esc_url($term_link) . '">' . esc_html($test_category->name) . '</a>';
                    $output .= '</span> ';
                }
            }
        }
    } elseif ($post_type === 'test') {
        $test_categories = get_the_terms($post_id, 'categorie_test');
        if (!empty($test_categories) && !is_wp_error($test_categories)) {
            $selected_category = $test_categories[0];
            $term_link = get_term_link($selected_category, 'categorie_test');
            if (!is_wp_error($term_link)) {
                $output .= '<span class="test-category-link term_absolute post-term-item term-' . esc_attr($selected_category->slug) . '">';
                $output .= '<a href="' . esc_url($term_link) . '">' . esc_html($selected_category->name) . '</a>';
                $output .= '</span> ';
            }
        }
    }

    return $output;
}
add_shortcode('linked_test_category', 'display_linked_test_category');

// =============================================================================
// [linked_test_category_post_thumbnail] - Category badge for post thumbnails
// =============================================================================

function display_linked_test_category_post_thumbnail($atts)
{
  global $post;
  $atts = shortcode_atts(['post_id' => isset($post) ? $post->ID : null], $atts);
  $post_id = $atts['post_id'];

  if (empty($post_id)) {
    return '';
  }

  $output = '';

  if ('post' == get_post_type($post_id)) {
    $categories = get_the_category($post_id);

    if (!empty($categories) && !is_wp_error($categories)) {
      foreach ($categories as $category) {
        $test_term = get_term_by('slug', $category->slug, 'categorie_test');

        if ($test_term && !is_wp_error($test_term)) {
          $term_link = get_term_link($test_term, 'categorie_test');

          if (!is_wp_error($term_link)) {
            $output .= '<span class="post-term-item term-' . esc_attr($test_term->slug) . '">';
            $output .= '<a href="' . esc_url($term_link) . '">' . esc_html($test_term->name) . '</a>';
            $output .= '</span> ';
          }
        }
      }
    }
  }

  return $output;
}
add_shortcode('linked_test_category_post_thumbnail', 'display_linked_test_category_post_thumbnail');

// =============================================================================
// [linked_test_category_complete] - All categorie_test terms for a test
// =============================================================================

function display_linked_test_category_complete($atts)
{
  $atts = shortcode_atts(['post_id' => get_the_ID()], $atts);
  $post_id = $atts['post_id'];

  if (!$post_id) return '';

  $output = '';
  $categories = get_the_terms($post_id, 'categorie_test');

  if (!empty($categories) && !is_wp_error($categories)) {
    foreach ($categories as $category) {
      $term_link = get_term_link($category, 'categorie_test');
      if (!is_wp_error($term_link)) {
        $output .= sprintf(
          '<span class="test-category-link term_absolute post-term-item term-%1$s"><a href="%2$s">%3$s</a></span> ',
          esc_attr($category->slug),
          esc_url($term_link),
          esc_html($category->name)
        );
      }
    }
  }

  return $output;
}
add_shortcode('linked_test_category_complete', 'display_linked_test_category_complete');

// =============================================================================
// [display_related_news] - Related news posts on categorie_test archives
// =============================================================================

function display_related_news_posts($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10
    ), $atts);
    $limit = intval($atts['limit']);

    if (is_tax('categorie_test')) {
        $term = get_queried_object();
        $test_category_slug = $term->slug;

        $args = [
            'category_name' => $test_category_slug,
            'posts_per_page' => $limit,
            'post__not_in' => array(get_the_ID())
        ];

        $related_posts = new WP_Query($args);

        if ($related_posts->have_posts()) {
            $output = '<div class="related-content related-articles">';

	$output .= '
<span class="gb-headline-shortcode">
    <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">.st1{display:none}<path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
    <span>Articles associés</span>
</span>
';
            $output .= '<div class="related-content__grid">';

          while ($related_posts->have_posts()) {
				$related_posts->the_post();

				$post_id      = get_the_ID();
				$permalink    = get_permalink($post_id);
				$title        = get_the_title($post_id);
				$thumbnail    = get_the_post_thumbnail($post_id, 'medium');
				$last_updated = get_the_modified_time('j F Y à H:i', $post_id);
				$chapeau      = get_field('chapeau', $post_id);

				$test_term = get_term_by('slug', $test_category_slug, 'categorie_test');

				$term_link = '';
				if ($test_term && !is_wp_error($test_term)) {
					$term_link = get_term_link($test_term, 'categorie_test');
					if (is_wp_error($term_link)) {
						$term_link = '';
					}
				}

				$output .= '<div class="related-content__card">';
					$output .= '<div class="related-content__image">';
						$output .= '<a href="' . esc_url($permalink) . '">' . $thumbnail . '</a>';
					$output .= '</div>';

					$output .= '<div class="related-content__content">';

						if ($test_term && $term_link) {
							$output .= '<span class="post-term-item term-' . esc_attr($test_term->slug) . '">';
								$output .= '<a href="' . esc_url($term_link) . '">' . esc_html($test_term->name) . '</a>';
							$output .= '</span>';
						}

						$output .= '<span class="related-content__card-title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></span>';
						$output .= '<span class="related-content__date">Mis à jour le ' . esc_html($last_updated) . '</span>';

					$output .= '</div>';
				$output .= '</div>';
			}


            $output .= '</div>';
            $output .= '</div>';

            wp_reset_postdata();
            return $output;
        }
    }
    return '';
}
add_shortcode('display_related_news', 'display_related_news_posts');

// =============================================================================
// [display_related_test_categories] - Sibling test categories
// =============================================================================

function display_related_test_categories($atts) {
    $atts = shortcode_atts(array(
        'limit' => 4
    ), $atts);
    $limit = intval($atts['limit']);

    if (is_tax('categorie_test')) {
        $current_term = get_queried_object();
        $parent_id = $current_term->parent;

        $parent_term_id = $parent_id ? $parent_id : $current_term->term_id;

        $args = array(
            'taxonomy' => 'categorie_test',
            'child_of' => $parent_term_id,
            'hide_empty' => false,
            'exclude' => $current_term->term_id,
        );

        $related_categories = get_terms($args);

        if (!empty($related_categories) && !is_wp_error($related_categories)) {
            $output = '<div class="related-content related-categories">';

	$output .= '
<span class="gb-headline-shortcode">
    <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">.st1{display:none}<path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
    <span>Catégories associées</span>
</span>
';
            $output .= '<div class="related-content__grid">';

            $count = 0;
            foreach ($related_categories as $category) {
                if ($count >= $limit) break;

                $term_link = get_term_link($category, 'categorie_test');
				$title = get_field('titre', $category);

                $featured_image_id = get_field('featured', $category);

                $output .= '<div class="related-content__card">';
                $output .= '<div class="related-content__image">';
                if ($featured_image_id) {
                    $image = wp_get_attachment_image($featured_image_id, 'medium');
                    $output .= '<a href="' . esc_url($term_link) . '">' . $image . '</a>';
                }
                $output .= '</div>';
                $output .= '<div class="related-content__content">';
                $output .= '<span class="related-content__card-title"><a href="' . esc_url($term_link) . '">' . esc_html($title) . '</a></span>';
                $output .= '</div>';
                $output .= '</div>';

                $count++;
            }

            $output .= '</div>';
            $output .= '</div>';

            return $output;
        }
    }
    return '';
}
add_shortcode('display_related_test_categories', 'display_related_test_categories');

// =============================================================================
// [display_related_news_for_test] - Related news for test CPT
// =============================================================================

// Helper: render a post item card for test context
if ( ! function_exists( 'render_post_item_for_test' ) ) {
function render_post_item_for_test($post_id)
{
  $permalink = get_permalink($post_id);
  $title = get_the_title($post_id);
  $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
  $last_updated = get_the_modified_time('j F Y à H:i', $post_id);
  $chapeau = get_field('chapeau', $post_id);

  $categories = get_the_category($post_id);
  $category_html = '';
  if (!empty($categories) && !is_wp_error($categories)) {
    $selected_category = null;
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
}

function display_related_news_posts_for_test()
{
  global $post;

  $output = '';
  $displayed_posts_count = 0;
  $needed_posts = 6;
  $directly_linked_articles = get_field('articles_associes', $post->ID);
  $excluded_article_ids = [];

  // Affichage des articles directement liés
  if ($directly_linked_articles) {
    usort($directly_linked_articles, function($a, $b) {
      return strtotime(get_the_modified_date('Y-m-d H:i:s', $b->ID)) - strtotime(get_the_modified_date('Y-m-d H:i:s', $a->ID));
    });

    foreach ($directly_linked_articles as $article) {
      if ($displayed_posts_count < $needed_posts && $article->post_status == 'publish') {
        $output .= render_post_item_for_test($article->ID);
        $displayed_posts_count++;
        $excluded_article_ids[] = $article->ID;
      }
    }
  }

  // Compléter les articles à partir de la catégorie de test
  if ($displayed_posts_count < $needed_posts) {
    $test_categories = get_the_terms($post->ID, 'categorie_test');
    $category_slugs_for_query = wp_list_pluck($test_categories, 'slug');

    $args = [
      'post_type' => 'post',
      'posts_per_page' => $needed_posts - $displayed_posts_count,
      'tax_query' => [
        [
          'taxonomy' => 'category',
          'field' => 'slug',
          'terms' => $category_slugs_for_query,
        ]
      ],
      'post__not_in' => $excluded_article_ids,
      'post_status' => 'publish',
      'orderby' => 'date',
      'order' => 'DESC'
    ];

    $query = new WP_Query($args);
    while ($query->have_posts()) {
      $query->the_post();
      $output .= render_post_item_for_test(get_the_ID());
      $displayed_posts_count++;
      $excluded_article_ids[] = get_the_ID();
    }
    wp_reset_postdata();
  }

  // Si moins de 6 articles, chercher sans restriction de catégorie
  if ($displayed_posts_count < $needed_posts) {
    $args = [
      'post_type' => 'post',
      'posts_per_page' => $needed_posts - $displayed_posts_count,
      'post__not_in' => $excluded_article_ids,
      'post_status' => 'publish',
      'orderby' => 'date',
      'order' => 'DESC'
    ];

    $query = new WP_Query($args);
    while ($query->have_posts()) {
      $query->the_post();
      $output .= render_post_item_for_test(get_the_ID());
      $displayed_posts_count++;
    }
    wp_reset_postdata();
  }

  return $output ? "<div class='related-news-posts'>$output</div>" : '';
}
add_shortcode('display_related_news_for_test', 'display_related_news_posts_for_test');

// =============================================================================
// [display_associated_tests] - Associated tests from ACF relationship
// =============================================================================

function display_associated_tests_shortcode()
{
  global $post;

  $output = '';

  $associated_tests = get_field('produit_associe', $post->ID);

  if ($associated_tests) {
    $output .= '<div class="related-news-posts related-news__items--count-' . min(count($associated_tests), 4) . '">';

    foreach ($associated_tests as $test_post) {
      $test_thumbnail = get_the_post_thumbnail_url($test_post->ID, 'large');
      $test_title = get_the_title($test_post->ID);
      $test_permalink = get_permalink($test_post->ID);
      $terms = get_the_terms($test_post->ID, 'your_taxonomy_name');

      $output .= '<div class="related-news__item" style="background-image: url(' . esc_url($test_thumbnail) . ');">';
      $output .= '<a href="' . esc_url($test_permalink) . '" class="related-news__full-link"></a>';
      $output .= '<div class="related-news__overlay">';
      $output .= '<div class="related-news__content">';
      $output .= '<h4 class="related-news__headline">' . esc_html($test_title) . '</h4>';

      if (!is_wp_error($terms) && !empty($terms)) {
        $output .= '<div class="related-news__terms">';
        foreach ($terms as $term) {
          $term_link = get_term_link($term);
          if (!is_wp_error($term_link)) {
            $output .= '<a href="' . esc_url($term_link) . '" class="related-news__term-link">' . esc_html($term->name) . '</a> ';
          }
        }
        $output .= '</div>';
      }

      $output .= '</div>';
      $output .= '</div>';
      $output .= '</div>';
    }

    $output .= '</div>';
  }

  return $output;
}
add_shortcode('display_associated_tests', 'display_associated_tests_shortcode');

// =============================================================================
// [display_related_buying_guides] - Universal buying guides
// =============================================================================

function display_universal_buying_guides($atts) {
    $atts = shortcode_atts(array(
        'limit' => 4
    ), $atts);

    $limit = intval($atts['limit']);
    $category = null;
    $current_object_id = null;

    $queried_test_term = null;
    $associated_test_term = null;

    global $post;

    // Déterminer le contexte et trouver la catégorie appropriée
    if (is_single()) {
        $post_type = get_post_type();
        if ($post_type === 'post') {
            $categories = get_the_category();
            $category = !empty($categories) ? $categories[0] : null;
        } elseif ($post_type === 'test') {
            $test_categories = get_the_terms(get_the_ID(), 'categorie_test');
            if (!empty($test_categories) && !is_wp_error($test_categories)) {
                $test_term = reset($test_categories);
                $category = get_term_by('slug', $test_term->slug, 'category');
            }
        }
        $current_object_id = $post ? $post->ID : null;

    } elseif (is_category()) {
        $category = get_queried_object();
        $current_object_id = $category ? $category->term_id : null;

    } elseif (is_tax('categorie_test')) {
        $queried_test_term = get_queried_object();
        $category = $queried_test_term ? get_term_by('slug', $queried_test_term->slug, 'category') : null;
        $current_object_id = $queried_test_term ? $queried_test_term->term_id : null;
    }

    if (!$category || is_wp_error($category)) {
        return '';
    }

    // Terme categorie_test associé à la catégorie WP (slug partagé)
    $associated_test_term = get_term_by('slug', $category->slug, 'categorie_test');
    if (is_wp_error($associated_test_term)) {
        $associated_test_term = null;
    }

    // Rechercher les guides d'achat
    $buying_guides_args = array(
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'post__not_in' => (is_single() && $post) ? array($post->ID) : array(),
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => $category->term_id,
            ),
            array(
                'taxonomy' => 'post_tag',
                'field' => 'slug',
                'terms' => 'guide-dachat',
            ),
        ),
    );

    $buying_guides = new WP_Query($buying_guides_args);

    if (!$buying_guides->have_posts() && (!$associated_test_term || $associated_test_term->term_id === $current_object_id)) {
        wp_reset_postdata();
        return '';
    }

    // Titre conditionnel basé sur l'existence du terme associé
    $title_text = ($associated_test_term) ? 'Guide(s) d\'achat' : 'Comparatif et guide(s) d\'achat';

    $output = '<div class="related-content buying-guides">';
    $output .= '
    <span class="gb-headline-shortcode">
        <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">.st1{display:none}<path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
        <span>' . esc_html($title_text) . '</span>
    </span>
    ';

    $count = 0;
    while ($buying_guides->have_posts() && $count < $limit - 1) {
        $buying_guides->the_post();
        $output .= generate_card_html($buying_guides->post, false);
        $count++;
    }

    // Ajouter la catégorie de test associée si elle existe et n'est pas l'objet courant
    if ($associated_test_term && $associated_test_term->term_id !== $current_object_id && $count < $limit) {
        $output .= generate_card_html($associated_test_term, true);
    }

    $output .= '</div>';
    wp_reset_postdata();

    return $output;
}
add_shortcode('display_related_buying_guides', 'display_universal_buying_guides');

// =============================================================================
// [related_articles] - Related articles (mixed tests + posts)
// =============================================================================

function display_related_articles($atts) {
    $current_post_id = get_the_ID();
    $items = array();

    // 1) Catégorie du post courant
    $current_categories = get_the_category($current_post_id);
    if (empty($current_categories) || is_wp_error($current_categories)) {
        return '';
    }
    $current_category = $current_categories[0];

    // 2) Articles de la même catégorie
    $articles = get_posts(array(
        'post_type'      => 'post',
        'posts_per_page' => 10,
        'post__not_in'   => array($current_post_id),
        'category__in'   => array($current_category->term_id),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ));

    // 3) Produit vedette en premier (si présent)
    $produit_vedette = get_field('produit_vedette', $current_post_id);
    if (!empty($produit_vedette[0])) {
        $vedette_id = is_object($produit_vedette[0]) ? (int) $produit_vedette[0]->ID : (int) $produit_vedette[0];
        if ($vedette_id > 0) {
            $items[] = array(
                'type' => 'test',
                'post' => get_post($vedette_id),
            );

            for ($i = 0; $i < 2 && $i < count($articles); $i++) {
                $items[] = array('type' => 'article', 'post' => $articles[$i]);
            }
        }
    }

    // 4) Produits associés
    $produits_associes = get_field('produit_associe', $current_post_id);
    if (!empty($produits_associes)) {
        if (!is_array($produits_associes)) {
            $produits_associes = array($produits_associes);
        }

        $article_index = 2;
        foreach ($produits_associes as $produit) {
            $pid = 0;
            if (is_object($produit) && isset($produit->ID)) {
                $pid = (int) $produit->ID;
            } elseif (is_numeric($produit)) {
                $pid = (int) $produit;
            }
            if ($pid <= 0) continue;

            $items[] = array('type' => 'test', 'post' => get_post($pid));

            for ($i = 0; $i < 2 && $article_index < count($articles); $i++) {
                $items[] = array('type' => 'article', 'post' => $articles[$article_index]);
                $article_index++;
            }
        }
    }

    // Fallback : uniquement des articles si aucun test
    if (empty($items) && !empty($articles)) {
        foreach ($articles as $a) {
            $items[] = array('type' => 'article', 'post' => $a);
        }
    }

    if (empty($items)) {
        return '';
    }

    // Helper badge categorie_test pour un test
    $render_test_badge = function($test_id) {
        $terms = get_the_terms($test_id, 'categorie_test');
        if (empty($terms) || is_wp_error($terms)) return '';
        $t = $terms[0];
        $link = get_term_link($t, 'categorie_test');
        if (is_wp_error($link)) return '';

        return '<span class="post-term-item term-' . esc_attr($t->slug) . '">'
             . '<a href="' . esc_url($link) . '">' . esc_html($t->name) . '</a>'
             . '</span>';
    };

    // Helper badge categorie_test correspondant au slug de la catégorie WP
    $render_article_badge = function($category_slug, $category_name) {
        $test_term = get_term_by('slug', $category_slug, 'categorie_test');
        if (!$test_term || is_wp_error($test_term)) return '';
        $link = get_term_link($test_term, 'categorie_test');
        if (is_wp_error($link)) return '';

        return '<span class="post-term-item term-' . esc_attr($test_term->slug) . '">'
             . '<a href="' . esc_url($link) . '">' . esc_html($category_name) . '</a>'
             . '</span>';
    };

    // Rendu
    $output  = '<div class="related-content related-articles">';
    $output .= '<span class="gb-headline-shortcode">
        <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
        <span>Articles associés</span>
    </span>';

    $output .= '<div class="related-content__grid mixed_list_homepage">';

    foreach ($items as $item) {
        $p = $item['post'];
        if (!$p || !isset($p->ID)) continue;

        $pid = (int) $p->ID;
        if ($pid === (int) $current_post_id) continue;

        $is_test = ($item['type'] === 'test');

        $permalink = get_permalink($pid);
        if (!$permalink) continue;

        $thumb = get_the_post_thumbnail($pid, 'thumbnail');
        $title = get_the_title($pid);

        $output .= $is_test
            ? '<div class="related-content__card test type-test">'
            : '<div class="related-content__card article type-post">';

        $output .= '<div class="related-content__image">';
        $output .= '<a href="' . esc_url($permalink) . '">' . $thumb . '</a>';
        $output .= '</div>';

        $output .= '<div class="related-content__content">';

        if ($is_test) {
            $output .= $render_test_badge($pid);
        } else {
            $output .= $render_article_badge($current_category->slug, $current_category->name);
        }

        $output .= '<span class="related-content__card-title"><a href="' . esc_url($permalink) . '">'
                . esc_html($title) . '</a></span>';

        if ($is_test) {
            $note = get_field('note_globale', $pid);
            if (!empty($note)) {
                $output .= '<div class="related-content__stars">' . generate_star_rating($note) . '</div>';
            }

            $bouton_affiliz = get_post_meta($pid, 'bouton_affiliz', true);
            if (!empty($bouton_affiliz)) {
                $output .= '<div class="related-content__cta related-content__cta--affiliz">' . $bouton_affiliz . '</div>';
            }
        } else {
            $output .= '<span class="related-content__date">Publié le ' . esc_html(get_the_date('', $pid)) . '</span>';
        }

        $output .= '</div></div>';
    }

    $output .= '</div></div>';

    return $output;
}
add_shortcode('related_articles', 'display_related_articles');

// =============================================================================
// [associated_news] - Associated news for test CPT (mixed tests + articles)
// =============================================================================

function display_associated_news($atts)
{
    $current_post_id = get_the_ID();
    $items = array();

    // 1. Catégorie du test courant
    $test_categories = get_the_terms($current_post_id, 'categorie_test');
    if (empty($test_categories) || is_wp_error($test_categories)) {
        return '';
    }

    $current_category = $test_categories[0];

    // 2. Récupérer les articles de la catégorie correspondante via le slug
    $matched_cat = get_category_by_slug($current_category->slug);
    if (!$matched_cat) {
        return '';
    }

    $articles = get_posts(array(
        'post_type'      => 'post',
        'posts_per_page' => 6,
        'post__not_in'   => array($current_post_id),
        'category__in'   => array($matched_cat->term_id),
        'orderby'        => 'date',
        'order'          => 'DESC'
    ));

    // 3. Produit associé en premier bloc
    $produit_associe = get_field('produit_associe', $current_post_id);

    $produit_associe_id = 0;
    if (is_array($produit_associe) && !empty($produit_associe[0])) {
        $produit_associe_id = is_object($produit_associe[0]) ? (int) $produit_associe[0]->ID : (int) $produit_associe[0];
    } elseif (is_object($produit_associe) && isset($produit_associe->ID)) {
        $produit_associe_id = (int) $produit_associe->ID;
    } elseif (is_numeric($produit_associe)) {
        $produit_associe_id = (int) $produit_associe;
    }

    if ($produit_associe_id > 0 && $produit_associe_id !== (int) $current_post_id) {
        $items[] = array(
            'type' => 'test',
            'post' => get_post($produit_associe_id)
        );
    }

    // 4. Deux autres tests dans la même catégorie
    $tests_in_category = get_posts(array(
        'post_type'      => 'test',
        'posts_per_page' => 2,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'tax_query'      => array(
            array(
                'taxonomy' => 'categorie_test',
                'field'    => 'term_id',
                'terms'    => array($current_category->term_id)
            )
        ),
        'post__not_in'   => array($produit_associe_id, $current_post_id)
    ));

    foreach ($tests_in_category as $test) {
        if ($test && isset($test->ID)) {
            $items[] = array(
                'type' => 'test',
                'post' => $test
            );
        }
    }

    // 5. Intercaler les articles après chaque test (2 articles après chaque test)
    $final_items = array();
    $article_index = 0;

    foreach ($items as $test_item) {
        $final_items[] = $test_item;

        for ($i = 0; $i < 2 && $article_index < count($articles); $i++) {
            $final_items[] = array(
                'type' => 'article',
                'post' => $articles[$article_index]
            );
            $article_index++;
        }
    }

    // Fallback: afficher uniquement les articles
    if (empty($final_items) && !empty($articles)) {
        foreach ($articles as $a) {
            $final_items[] = array(
                'type' => 'article',
                'post' => $a
            );
        }
    }

    // 6. Rendu
    $output = '<div class="related-content related-articles">';
    $output .= '<span class="gb-headline-shortcode">
        <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">.st1{display:none}<path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
        <span>Articles associés</span>
    </span>';
    $output .= '<div class="related-content__grid mixed_list_homepage">';

    foreach ($final_items as $item) {
        $p = $item['post'];
        if (!$p || !isset($p->ID) || (int) $p->ID === (int) $current_post_id) {
            continue;
        }

        $is_test = ($item['type'] === 'test');

        $output .= $is_test
            ? '<div class="related-content__card test type-test">'
            : '<div class="related-content__card article">';

        $output .= '<div class="related-content__image">';
        $output .= '<a href="' . esc_url(get_permalink($p->ID)) . '">' . get_the_post_thumbnail($p->ID, 'thumbnail') . '</a>';
        $output .= '</div>';

        $output .= '<div class="related-content__content">';
        $output .= '<span class="related-content__card-title"><a href="' . esc_url(get_permalink($p->ID)) . '">' . esc_html(get_the_title($p->ID)) . '</a></span>';

        if ($is_test) {
            $note = floatval(get_post_meta($p->ID, 'note_globale', true));
            if ($note > 0) {
                $output .= '<div class="related-content__stars">' . generate_star_rating($note) . '</div>';
            }

            $bouton_affiliz = get_post_meta($p->ID, 'bouton_affiliz', true);

            if (!empty($bouton_affiliz)) {
                $output .= '<div class="related-content__cta related-content__cta--affiliz">' . $bouton_affiliz . '</div>';
            }
        }

        $output .= '</div></div>';
    }

    $output .= '</div></div>';
    return $output;
}
add_shortcode('associated_news', 'display_associated_news');

// =============================================================================
// [associated_products] - Associated WooCommerce products
// =============================================================================

function display_associated_products($atts) {
    $atts = shortcode_atts(array(
        'limit' => 6
    ), $atts);
    $limit = intval($atts['limit']);
    $current_product = wc_get_product(get_the_ID());

    if (!$current_product) {
        return '';
    }

    $brand = $current_product->get_attribute('pa_marque');

    // Récupérer 3 produits de la même marque
    $brand_products_args = array(
        'post_type' => 'product',
        'posts_per_page' => 3,
        'post__not_in' => array(get_the_ID()),
        'tax_query' => array(
            array(
                'taxonomy' => 'pa_marque',
                'field' => 'slug',
                'terms' => $brand
            )
        ),
        'meta_key' => 'total_sales',
        'orderby' => 'meta_value_num'
    );
    $brand_products = new WP_Query($brand_products_args);

    // Récupérer 3 autres produits populaires
    $popular_products_args = array(
        'post_type' => 'product',
        'posts_per_page' => 3,
        'post__not_in' => array_merge(array(get_the_ID()), wp_list_pluck($brand_products->posts, 'ID')),
        'meta_key' => 'total_sales',
        'orderby' => 'meta_value_num'
    );
    $popular_products = new WP_Query($popular_products_args);

    $output = '<div class="related-content associated-products">';
    $output .= '<div class="related-content__grid">';

    while ($brand_products->have_posts()) {
        $brand_products->the_post();
        $output .= generate_product_card();
    }
    while ($popular_products->have_posts()) {
        $popular_products->the_post();
        $output .= generate_product_card();
    }

    $output .= '</div></div>';
    wp_reset_postdata();
    return $output;
}
add_shortcode('associated_products', 'display_associated_products');

// =============================================================================
// [buying_guides_and_comparisons] - Guides & comparisons for test CPT
// =============================================================================

function display_buying_guides_and_comparisons($atts) {
    $atts = shortcode_atts(array(
        'limit' => 6
    ), $atts);
    $limit = intval($atts['limit']);
    $current_post_id = get_the_ID();

    $test_categories = get_the_terms($current_post_id, 'categorie_test');
    if (empty($test_categories) || is_wp_error($test_categories)) {
        return '';
    }

    $category = $test_categories[0];
    $category_slug = $category->slug;

    $category_link = get_term_link($category, 'categorie_test');
    if (is_wp_error($category_link)) {
        $category_link = '';
    }

    $featured_id = get_field('featured', 'categorie_test_' . $category->term_id);
    $featured_image = $featured_id ? wp_get_attachment_image($featured_id, 'medium') : '';

    $guides_limit = floor($limit / 2);
    $comparisons_limit = $limit - $guides_limit;

    // Requête pour les guides d'achat
    $guides_args = array(
        'post_type' => 'post',
        'posts_per_page' => $guides_limit,
        'tax_query' => array(
            array(
                'taxonomy' => 'category',
                'field' => 'slug',
                'terms' => $category_slug
            ),
        ),
        'tag' => 'guide-dachat',
        'post__not_in' => array($current_post_id),
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $guides = new WP_Query($guides_args);

    // Requête pour les comparatifs
    $comparisons_args = array(
        'post_type' => 'test',
        'posts_per_page' => $comparisons_limit,
        'tax_query' => array(
            array(
                'taxonomy' => 'categorie_test',
                'field' => 'slug',
                'terms' => $category_slug
            ),
        ),
        'tag' => 'comparatif',
        'post__not_in' => array($current_post_id),
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $comparisons = new WP_Query($comparisons_args);

	$category_title = get_field('titre', 'categorie_test_' . $category->term_id);
    $category_title = $category_title ? $category_title : $category->name;

    $has_content = ($guides->have_posts() || $comparisons->have_posts());

    if (!$has_content && empty($featured_image)) {
        wp_reset_postdata();
        return '';
    }

    $output = '<div class="related-content guides-and-comparisons">';

	$output .= '
<span class="gb-headline-shortcode">
    <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">.st1{display:none}<path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
    <span>Comparatif et guide(s) d\'achat</span>
</span>
';

    // Carte de l'archive de la catégorie
    if ($category_link && $featured_image) {
        $output .= generate_content_card_custom(
            $featured_image,
            $category_title,
            $category_link
        );
    }

    $output .= '<div class="related-content__grid">';

    // Guides d'achat
    if ($guides->have_posts()) {
        while ($guides->have_posts()) {
            $guides->the_post();
            $thumbnail = get_the_post_thumbnail(null, 'medium');
            $title = get_the_title();
            $permalink = get_permalink();
            $date = get_the_date();
            $output .= generate_content_card($thumbnail, $title, $permalink, $date);
        }
    }

    // Comparatifs
    if ($comparisons->have_posts()) {
        while ($comparisons->have_posts()) {
            $comparisons->the_post();
            $thumbnail = get_the_post_thumbnail(null, 'medium');
            $title = get_the_title();
            $permalink = get_permalink();
            $date = get_the_date();
            $output .= generate_content_card($thumbnail, $title, $permalink, $date);
        }
    }

    $output .= '</div></div>';

    wp_reset_postdata();
    return $output;
}
add_shortcode('buying_guides_and_comparisons', 'display_buying_guides_and_comparisons');
