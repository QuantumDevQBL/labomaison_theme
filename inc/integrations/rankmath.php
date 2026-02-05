<?php
/**
 * Rank Math SEO Integration
 *
 * All hooks for Rank Math SEO plugin.
 *
 * @package Labomaison
 * @subpackage Integrations
 * @since 2.0.0
 *
 * Functions in this file:
 * - Sitemap image enrichment (wpseo_sitemap_urlimages)
 * - Sitemap term images (wpseo_sitemap_term_image)
 * - Custom variables (lm_nom, lm_chapeau, etc.)
 * - CollectionPage JSON-LD schema
 * - Product cleanup JSON-LD schema
 * - Breadcrumb helpers (get_univers, pick_primary_category, etc.)
 * - Breadcrumb items filter
 * - Breadcrumb HTML filter
 * - Author breadcrumbs
 * - Sitemap entry cleanup
 * - Canonical handlers (pagination, tracking params, marques, redacteur)
 * - Title/Description pagination
 *
 * Dependencies: Rank Math plugin, ACF
 * Load Priority: 5
 * Condition: class_exists('RankMath')
 * Risk Level: HIGH
 *
 * Migrated from: functions.php L1471-1595, L1970-3400
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// SITEMAP IMAGE ENRICHMENT
// =============================================================================

/**
 * Enrich sitemaps with all relevant images
 *
 * Compatible with: post, test, promotion, page, marque + categorie_test
 * Handles: featured image, Gutenberg content, ACF galleries, HTML fields, logos
 *
 * @since 2.0.0
 */
add_filter('wpseo_sitemap_urlimages', function ($images, $post_id) {

    $post_type = get_post_type($post_id);
    $allowed_types = ['post', 'test', 'promotion', 'page', 'marque'];
    if (!in_array($post_type, $allowed_types)) {
        return $images;
    }

    // 1. Featured image
    $thumb_url = get_the_post_thumbnail_url($post_id, 'full');
    if ($thumb_url) {
        $images[] = ['src' => esc_url($thumb_url)];
    }

    // 2. Gutenberg / GenerateBlocks / ACF content
    $post = get_post($post_id);
    if ($post && !empty($post->post_content)) {

        global $post;
        $GLOBALS['post'] = $post;
        setup_postdata($post);

        $raw_content = get_post_field('post_content', $post_id);
        $content = do_blocks($raw_content);
        $content = apply_filters('the_content', $content);

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches_img);
        preg_match_all('/<source[^>]+srcset=["\']([^"\']+)["\']/i', $content, $matches_source);

        $sources = array_merge($matches_img[1], $matches_source[1]);

        if (!empty($sources)) {
            foreach ($sources as $src) {
                if (strpos($src, ',') !== false) {
                    $src = trim(explode(' ', explode(',', $src)[0])[0]);
                } else {
                    $src = trim(explode(' ', $src)[0]);
                }

                $src = strtok($src, '?');

                if (preg_match('/-\d{2,4}x\d{2,4}\.(jpg|jpeg|png|webp)$/i', $src)) continue;
                if (strpos($src, 'logo') !== false || strpos($src, '.svg') !== false) continue;

                if (strpos($src, 'http') !== 0) {
                    $src = home_url($src);
                }

                $images[] = ['src' => esc_url($src)];
            }
        }

        wp_reset_postdata();
    }

    // 3. ACF fields (tests, marques, etc.)
    if (function_exists('get_field')) {

        // Product gallery
        $gallery = get_field('gallerie_produit', $post_id, false);
        if (is_array($gallery)) {
            foreach ($gallery as $image_id) {
                $src = wp_get_attachment_url($image_id);
                if ($src) $images[] = ['src' => esc_url($src)];
            }
        }

        // HTML fields with images
        $fields_html = ['ou_acheter_contenu', 'presentation_contenu', 'contenu_du_test', 'conclusion'];
        foreach ($fields_html as $field) {
            $html = get_field($field, $post_id);
            if ($html && is_string($html)) {
                preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches);
                foreach ($matches[1] as $src) {
                    $images[] = ['src' => esc_url($src)];
                }
            }
        }

        // Brand logos
        if ($post_type === 'marque') {
            $logo = get_field('logo_marque', $post_id);
            if ($logo && isset($logo['url'])) {
                $images[] = ['src' => esc_url($logo['url'])];
            }
        }
    }

    // Deduplicate
    $unique = [];
    $filtered = [];
    foreach ($images as $img) {
        if (!in_array($img['src'], $unique)) {
            $unique[] = $img['src'];
            $filtered[] = $img;
        }
    }

    // Limit to 10 images max
    return array_slice($filtered, 0, 10);

}, 10, 2);

/**
 * Enrich sitemaps for taxonomies (categorie_test)
 *
 * @since 2.0.0
 */
add_filter('wpseo_sitemap_term_image', function ($images, $term_id, $taxonomy) {
    if (!function_exists('get_field')) return $images;
    if ($taxonomy === 'categorie_test') {
        $image = get_field('image_categorie', $taxonomy . '_' . $term_id);
        if ($image && isset($image['url'])) {
            $images[] = ['src' => esc_url($image['url'])];
        }
    }
    return $images;
}, 10, 3);

// =============================================================================
// RANK MATH VARIABLE REPLACEMENTS
// =============================================================================

/**
 * Register custom Rank Math variables (%lm_*%)
 *
 * Variables: lm_nom, lm_chapeau, lm_conclusion, lm_note, lm_prix,
 * lm_marque, lm_article_section, lm_product_name, lm_product_id,
 * lm_offer_url, lm_article_title
 *
 * @since 2.0.0
 */
add_action('rank_math/vars/register_extra_replacements', function () {

    if (!function_exists('rank_math_register_var_replacement')) {
        return;
    }

    $register = function(string $slug, string $label, callable $cb, string $example = '') {
        rank_math_register_var_replacement(
            $slug,
            [
                'name'        => esc_html__($label, 'labomaison'),
                'description' => esc_html__($label, 'labomaison'),
                'variable'    => $slug,
                'example'     => $example ?: $slug,
            ],
            $cb
        );
    };

    $get_post_id = function(): int {
        $id = (int) get_queried_object_id();
        if ($id) return $id;
        $id = (int) get_the_ID();
        return $id ?: 0;
    };

    $clean_text = function($raw): string {
        $raw = wp_strip_all_tags((string) $raw);
        $raw = preg_replace('/\s+/', ' ', $raw);
        return trim((string) $raw);
    };

    $get_marque_nom = function(int $post_id): string {
        if (!function_exists('get_field')) return '';
        $marque_field = get_field('marque', $post_id);
        if (!$marque_field) return '';

        $first = is_array($marque_field) ? reset($marque_field) : $marque_field;
        if ($first instanceof WP_Post) {
            return (string) get_the_title($first->ID);
        }
        return '';
    };

    $get_article_section = function(int $post_id): string {
        $cats = get_the_terms($post_id, 'categorie_test');
        if (!is_wp_error($cats) && $cats) {
            return (string) $cats[0]->name;
        }
        return '';
    };

    // Strict rating 1..5 (empty otherwise)
    $get_note_val = function(int $post_id): string {
        $note_globale = get_post_meta($post_id, 'note_globale', true);
        if ($note_globale === '' || !is_numeric($note_globale)) return '';
        $note = (float) $note_globale;
        if ($note < 1.0 || $note > 5.0) return '';
        $out = rtrim(rtrim(number_format($note, 1, '.', ''), '0'), '.');
        return $out;
    };

    // Strict price >0 (empty otherwise)
    $get_prix = function(int $post_id): string {
        $prix = get_post_meta($post_id, 'prix', true);
        if ($prix === '' || !is_numeric($prix)) return '';
        $p = (float) $prix;
        if ($p <= 0) return '';
        $out = rtrim(rtrim(number_format($p, 2, '.', ''), '0'), '.');
        return $out;
    };

    $get_product_name = function(int $post_id) use ($get_marque_nom): string {
        $nom   = (string) get_post_meta($post_id, 'nom', true);
        $titre = (string) get_the_title($post_id);

        $marque = $get_marque_nom($post_id);
        $product_name = ($nom !== '') ? $nom : $titre;

        if ($marque !== '' && stripos($product_name, $marque) === false) {
            $product_name = $marque . ' ' . $product_name;
        }
        return trim($product_name);
    };

    // Raw variables
    $register('lm_nom', 'ACF: nom', function() use ($get_post_id) {
        $post_id = $get_post_id();
        return $post_id ? (string) get_post_meta($post_id, 'nom', true) : '';
    }, 'Dyson V15');

    $register('lm_chapeau', 'ACF: chapeau (nettoyé)', function() use ($get_post_id, $clean_text) {
        $post_id = $get_post_id();
        $raw = $post_id ? get_post_meta($post_id, 'chapeau', true) : '';
        return $clean_text($raw);
    }, 'Résumé court…');

    $register('lm_conclusion', 'ACF: conclusion (nettoyée)', function() use ($get_post_id, $clean_text) {
        $post_id = $get_post_id();
        $raw = $post_id ? get_post_meta($post_id, 'conclusion', true) : '';
        return $clean_text($raw);
    }, 'Conclusion…');

    $register('lm_note', 'ACF: note_globale (1..5, sinon vide)', function() use ($get_post_id, $get_note_val) {
        $post_id = $get_post_id();
        return $post_id ? $get_note_val($post_id) : '';
    }, '4.5');

    $register('lm_prix', 'ACF: prix (>0, sinon vide)', function() use ($get_post_id, $get_prix) {
        $post_id = $get_post_id();
        return $post_id ? $get_prix($post_id) : '';
    }, '299');

    $register('lm_marque', 'ACF: marque (nom)', function() use ($get_post_id, $get_marque_nom) {
        $post_id = $get_post_id();
        return $post_id ? $get_marque_nom($post_id) : '';
    }, 'Dyson');

    $register('lm_article_section', 'Taxo: categorie_test (1er terme)', function() use ($get_post_id, $get_article_section) {
        $post_id = $get_post_id();
        return $post_id ? $get_article_section($post_id) : '';
    }, 'Aspirateurs');

    // Derived variables
    $register('lm_product_name', 'Produit: marque + nom (fallback titre)', function() use ($get_post_id, $get_product_name) {
        $post_id = $get_post_id();
        return $post_id ? $get_product_name($post_id) : '';
    }, 'Dyson V15');

    $register('lm_product_id', 'Produit: @id (permalink + #presentation_title)', function() use ($get_post_id) {
        $post_id = $get_post_id();
        return $post_id ? (get_permalink($post_id) . '#presentation_title') : '';
    }, 'https://labomaison.com/.../#presentation_title');

    $register('lm_offer_url', 'Offer: url (permalink + #ou_acheter_title)', function() use ($get_post_id) {
        $post_id = $get_post_id();
        return $post_id ? (get_permalink($post_id) . '#ou_acheter_title') : '';
    }, 'https://labomaison.com/.../#ou_acheter_title');

    $register('lm_article_title', 'Article: headline (Test : {Produit} si note)', function() use ($get_post_id, $get_product_name, $get_note_val) {
        $post_id = $get_post_id();
        if (!$post_id) return '';
        $product_name = $get_product_name($post_id);
        $note = $get_note_val($post_id);
        return $note !== '' ? ('Test : ' . $product_name) : $product_name;
    }, 'Test : Dyson V15');
});

// =============================================================================
// JSON-LD SCHEMA MODIFICATIONS
// =============================================================================

/**
 * CollectionPage on categorie_test archives
 *
 * Clean list without Review, Offer, rating/price
 *
 * @since 2.0.0
 */
add_filter('rank_math/json_ld', function($data) {

    if (!is_tax('categorie_test')) {
        return $data;
    }

    $term = get_queried_object();
    if (!$term || empty($term->term_id)) {
        return $data;
    }

    $term_link = get_term_link($term);
    if (is_wp_error($term_link)) {
        return $data;
    }

    $headline    = single_term_title('', false);
    $description = wp_strip_all_tags((string) term_description($term));
    $parent_name = '';

    if (!empty($term->parent)) {
        $parent = get_term((int) $term->parent, 'categorie_test');
        if ($parent && !is_wp_error($parent)) {
            $parent_name = (string) $parent->name;
        }
    }

    $query = new WP_Query([
        'post_type'      => 'test',
        'posts_per_page' => 20,
        'post_status'    => 'publish',
        'tax_query'      => [[
            'taxonomy' => 'categorie_test',
            'field'    => 'term_id',
            'terms'    => (int) $term->term_id,
        ]],
    ]);

    $itemListElements = [];
    $position = 1;

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $itemListElements[] = array_filter([
                '@type'    => 'ListItem',
                'position' => $position++,
                'url'      => get_permalink($post_id),
                'name'     => get_the_title($post_id),
            ], function($v){
                return !($v === null || $v === '' || (is_array($v) && empty($v)));
            });
        }
        wp_reset_postdata();
    }

    $collection = array_filter([
        '@context'         => 'https://schema.org',
        '@type'            => 'CollectionPage',
        'inLanguage'       => 'fr-FR',
        'mainEntityOfPage' => $term_link,
        'headline'         => $headline,
        'description'      => $description,
        'publisher'        => [
            '@type' => 'Organization',
            'name'  => 'Labo Maison',
            'url'   => 'https://labomaison.com',
            'logo'  => [
                '@type'  => 'ImageObject',
                'url'    => 'https://labomaison.com/wp-content/uploads/2025/09/logo-labomaison-512.png',
                'width'  => 512,
                'height' => 512,
            ]
        ],
        'mainEntity'       => [
            '@type'           => 'ItemList',
            'itemListOrder'   => 'https://schema.org/ItemListOrderDescending',
            'numberOfItems'   => count($itemListElements),
            'itemListElement' => $itemListElements,
        ]
    ], function($v){
        return !($v === null || $v === '' || (is_array($v) && empty($v)));
    });

    if ($parent_name !== '') {
        $collection['about'] = [
            '@type' => 'Thing',
            'name'  => $parent_name,
        ];
    }

    $data['lm_collectionpage'] = $collection;
    return $data;

}, 99);

/**
 * Product cleanup on singular tests
 *
 * Removes review + aggregateRating if rating invalid,
 * removes offers if price invalid
 *
 * @since 2.0.0
 */
add_filter('rank_math/json_ld', function($data) {

    if (!is_singular('test')) {
        return $data;
    }

    $post_id = (int) get_queried_object_id();
    if (!$post_id) {
        return $data;
    }

    $note_raw = get_post_meta($post_id, 'note_globale', true);
    $prix_raw = get_post_meta($post_id, 'prix', true);

    $note = (is_numeric($note_raw)) ? (float) $note_raw : null;
    $prix = (is_numeric($prix_raw)) ? (float) $prix_raw : null;

    $has_valid_note  = ($note !== null && $note >= 1.0 && $note <= 5.0);
    $has_valid_price = ($prix !== null && $prix > 0);

    $is_type = function($type, $needle) {
        if (is_string($type)) return $type === $needle;
        if (is_array($type))  return in_array($needle, $type, true);
        return false;
    };

    $clean_product = function(array &$product) use ($has_valid_note, $has_valid_price) {
        if (!$has_valid_note) {
            unset($product['review'], $product['aggregateRating']);
        } else {
            $product['aggregateRating']['reviewCount'] = '1';
        }
        if (!$has_valid_price) {
            unset($product['offers']);
        }
    };

    foreach ($data as $key => $node) {
        if (!is_array($node)) continue;

        // Direct node
        if (isset($node['@type']) && $is_type($node['@type'], 'Product')) {
            $clean_product($data[$key]);
            continue;
        }

        // Nested graph
        if (isset($node['@graph']) && is_array($node['@graph'])) {
            foreach ($node['@graph'] as $gk => $gNode) {
                if (!is_array($gNode)) continue;
                if (isset($gNode['@type']) && $is_type($gNode['@type'], 'Product')) {
                    $clean_product($data[$key]['@graph'][$gk]);
                }
            }
        }
    }

    return $data;

}, 999);

// =============================================================================
// BREADCRUMB HELPER FUNCTIONS
// =============================================================================

/**
 * Get root (univers) term by walking up the hierarchy
 *
 * @since 2.0.0
 * @param WP_Term $term Starting term
 * @return WP_Term|null Root term or null
 */
function lm_breadcrumb_get_univers(\WP_Term $term): ?\WP_Term {
    while (!empty($term->parent)) {
        $parent = get_term((int) $term->parent, $term->taxonomy);
        if (!$parent || is_wp_error($parent)) return null;
        $term = $parent;
    }
    return $term;
}

/**
 * Pick primary category for a post
 *
 * Priority: Yoast primary > Rank Math meta > Rank Math function > deepest category
 *
 * @since 2.0.0
 * @param int $post_id Post ID
 * @return WP_Term|null Primary category or null
 */
function lm_breadcrumb_pick_primary_category(int $post_id): ?\WP_Term {
    // Yoast primary
    $yoast = get_post_meta($post_id, '_yoast_wpseo_primary_category', true);
    if ($yoast) {
        $t = get_term((int) $yoast, 'category');
        if ($t && !is_wp_error($t)) return $t;
    }

    // Rank Math primary (meta)
    $rank = get_post_meta($post_id, 'rank_math_primary_category', true);
    if ($rank) {
        $t = get_term((int) $rank, 'category');
        if ($t && !is_wp_error($t)) return $t;
    }

    // Rank Math primary (function)
    if (function_exists('rank_math_get_primary_term')) {
        $primary = rank_math_get_primary_term('category', $post_id);
        if ($primary && !is_wp_error($primary)) return $primary;
    }

    // Fallback: deepest category
    $cats = get_the_terms($post_id, 'category');
    if (empty($cats) || is_wp_error($cats)) return null;

    $depth = function (\WP_Term $t): int {
        $d = 0; $p = (int) $t->parent;
        while ($p) {
            $pt = get_term($p, 'category');
            if (!$pt || is_wp_error($pt)) break;
            $d++; $p = (int) $pt->parent;
        }
        return $d;
    };

    usort($cats, function ($a, $b) use ($depth) {
        $da = $depth($a); $db = $depth($b);
        if ($da === $db) return strcasecmp($a->name, $b->name);
        return ($da > $db) ? -1 : 1; // deepest first
    });

    return $cats[0] ?? null;
}

/**
 * Get featured test data for a post (produit_vedette ACF field)
 *
 * @since 2.0.0
 * @param int $post_id Post ID
 * @return array|null Array with test_id, label, url or null
 */
function lm_breadcrumb_get_featured_test_data(int $post_id): ?array {
    if (!function_exists('get_field')) return null;

    $ref = get_field('produit_vedette', $post_id);
    if (empty($ref)) return null;

    // ACF normalization
    if (is_array($ref)) {
        $ref = reset($ref);
    }

    $test_id = 0;
    if ($ref instanceof \WP_Post) {
        $test_id = (int) $ref->ID;
    } else {
        $test_id = (int) $ref;
    }

    if ($test_id <= 0 || get_post_type($test_id) !== 'test') {
        return null;
    }

    $url = get_permalink($test_id);
    if (empty($url) || is_wp_error($url)) return null;

    $brand = get_field('marque', $test_id);
    $name  = get_field('nom', $test_id);

    // Brand can be relationship to CPT "marque" (WP_Post) or array of WP_Post
    $brand_label = '';
    if ($brand instanceof \WP_Post) {
        $brand_label = trim(get_the_title($brand));
    } elseif (is_array($brand) && !empty($brand)) {
        $first = reset($brand);
        if ($first instanceof \WP_Post) {
            $brand_label = trim(get_the_title($first));
        } else {
            $brand_label = trim((string) $first);
        }
    } else {
        $brand_label = trim((string) $brand);
    }

    $name = trim((string) $name);

    $label = trim($brand_label . ' ' . $name);
    if ($label === '') {
        $label = trim((string) get_the_title($test_id));
        if ($label === '') return null;
    }

    return [
        'test_id' => $test_id,
        'label'   => $label,
        'url'     => $url,
    ];
}

/**
 * Map a WP category to its corresponding categorie_test term (by slug)
 *
 * @since 2.0.0
 * @param WP_Term $cat WP category term
 * @return WP_Term|null Matching categorie_test term or null
 */
function lm_map_post_category_to_test_term(\WP_Term $cat): ?\WP_Term {
    $t = get_term_by('slug', $cat->slug, 'categorie_test');
    if ($t && !is_wp_error($t) && $t instanceof \WP_Term) {
        return $t;
    }
    return null;
}

// =============================================================================
// BREADCRUMB ITEMS FILTER
// =============================================================================

/**
 * Build breadcrumb items
 *
 * Rules:
 * - Category: Accueil > Univers > Categorie
 * - Post (actu): Accueil > Univers > Categorie > TEST vedette (clickable)
 * - Test: Accueil > Univers > Categorie
 * - categorie_test: Accueil > Level 1 > Level 2 > ... (last non-clickable)
 * - Tags/other: Accueil > Term (non-clickable)
 *
 * @since 2.0.0
 */
add_filter('rank_math/frontend/breadcrumb/items', function (array $crumbs, $class = null): array {
    global $post;

    $out = [];

    // Home: keep Rank Math if present
    if (!empty($crumbs[0])) {
        $out[] = $crumbs[0];
    } else {
        $out[] = ['Accueil', home_url('/')];
    }

    /* =========================
     * ARCHIVE CATEGORY
     * Accueil > Univers > Categorie
     * ========================= */
    if (is_category()) {
        $cat = get_queried_object();
        if ($cat instanceof \WP_Term) {
            $univers = lm_breadcrumb_get_univers($cat);

            if ($univers instanceof \WP_Term) {
                $out[] = [$univers->name, get_term_link($univers)];
            }

            // Current non-clickable only if different from univers
            if (!$univers || (int) $univers->term_id !== (int) $cat->term_id) {
                $out[] = [$cat->name, ''];
            }
        }
        return $out;
    }

    /* =========================
     * ACTU (post)
     * Accueil > Univers > Categorie > TEST vedette (clickable)
     * ========================= */
    if (is_singular('post') && $post instanceof \WP_Post) {

        $cat = lm_breadcrumb_pick_primary_category((int) $post->ID);
        if ($cat instanceof \WP_Term) {

            // Strict mapping to categorie_test (by slug)
            $test_term = lm_map_post_category_to_test_term($cat);

            if ($test_term instanceof \WP_Term) {

                // Univers = root of categorie_test
                $univers = lm_breadcrumb_get_univers($test_term);

                if ($univers instanceof \WP_Term) {
                    $out[] = [$univers->name, get_term_link($univers)];
                }

                // Category = mapped term (categorie_test)
                if (!$univers || (int) $univers->term_id !== (int) $test_term->term_id) {
                    $out[] = [$test_term->name, get_term_link($test_term)];
                }
            }
        }

        // Featured test
        $featured = lm_breadcrumb_get_featured_test_data((int) $post->ID);
        if (is_array($featured)) {
            $out[] = [$featured['label'], $featured['url']];
            return $out;
        }

        // No featured test: stop at Accueil > Univers > Categorie
        return $out;
    }

    /* =========================
     * TESTS
     * Accueil > Univers > Categorie
     * No test title
     * ========================= */
    if (is_singular('test') || is_post_type_archive('test')) {

        $term = null;

        if (is_singular('test') && $post instanceof \WP_Post) {
            $terms = wp_get_object_terms($post->ID, 'categorie_test');
            if (!empty($terms) && !is_wp_error($terms)) {
                $term = $terms[0];
            }
        }

        if ($term instanceof \WP_Term) {
            $univers = lm_breadcrumb_get_univers($term);

            if ($univers instanceof \WP_Term) {
                $out[] = [$univers->name, get_term_link($univers)];
            }

            // Category clickable only if different from univers
            if (!$univers || (int) $univers->term_id !== (int) $term->term_id) {
                $out[] = [$term->name, get_term_link($term)];
            }
        }

        return $out;
    }

    /* =========================
     * TAXONOMY categorie_test (hub pages)
     * Accueil > Level 1 > Level 2 > ... (last non-clickable)
     * ========================= */
    if (is_tax('categorie_test')) {
        $term = get_queried_object();

        if ($term instanceof \WP_Term) {

            $chain = [];
            $current = $term;

            // Walk up the hierarchy (root > ... > current)
            while ($current instanceof \WP_Term) {
                array_unshift($chain, $current);

                if (empty($current->parent)) {
                    break;
                }

                $parent = get_term((int) $current->parent, 'categorie_test');
                if (!$parent || is_wp_error($parent)) {
                    break;
                }

                $current = $parent;
            }

            // Inject chain: all clickable except last
            $last_index = count($chain) - 1;

            foreach ($chain as $i => $t) {
                if ($i === $last_index) {
                    $out[] = [$t->name, '']; // current page
                } else {
                    $link = get_term_link($t);
                    $out[] = [$t->name, is_wp_error($link) ? '' : $link];
                }
            }
        }

        return $out;
    }

    /* =========================
     * TAGS / OTHER TAXONOMIES
     * Accueil > Term (non-clickable)
     * ========================= */
    if (is_tag() || is_tax()) {
        $term = get_queried_object();
        if ($term instanceof \WP_Term) {
            $out[] = [$term->name, ''];
        }
        return $out;
    }

    // Default: let Rank Math handle
    return $crumbs;

}, 10, 2);

// =============================================================================
// BREADCRUMB HTML FILTER
// =============================================================================

/**
 * Force last breadcrumb clickable for ACTU > TEST vedette
 *
 * @since 2.0.0
 */
add_filter('rank_math/frontend/breadcrumb/html', function ($html, $crumbs, $class) {

    if (!(is_singular('post') || is_singular('test')) || !is_array($crumbs) || count($crumbs) < 2) {
        return $html;
    }

    $last = end($crumbs);
    if (!is_array($last) || empty($last[0]) || empty($last[1])) {
        return $html; // last crumb without URL => don't force
    }

    $label = (string) $last[0];
    $url   = (string) $last[1];

    // Never link to current page
    $current = home_url(add_query_arg([]));
    $norm = function ($u) {
        $u = preg_replace('#\?.*$#', '', (string) $u);
        return rtrim($u, '/');
    };
    if ($norm($url) === $norm($current)) {
        return $html;
    }

    $safe_label = esc_html($label);
    $safe_url   = esc_url($url);

    // Replace <span class="last">LABEL</span> with a link (once)
    $pattern = '#<span([^>]*)class="([^"]*\blast\b[^"]*)"([^>]*)>\s*' . preg_quote($safe_label, '#') . '\s*</span>#i';
    if (preg_match($pattern, $html)) {
        $html = preg_replace(
            $pattern,
            '<a href="' . $safe_url . '" class="lm-breadcrumb-last-link">' . $safe_label . '</a>',
            $html,
            1
        );
        return $html;
    }

    // Fallback: replace last occurrence of label with a link
    $pos = strripos($html, $safe_label);
    if ($pos !== false) {
        $html = substr_replace(
            $html,
            '<a href="' . $safe_url . '" class="lm-breadcrumb-last-link">' . $safe_label . '</a>',
            $pos,
            strlen($safe_label)
        );
    }

    return $html;

}, 10, 3);

// =============================================================================
// AUTHOR BREADCRUMBS
// =============================================================================

/**
 * Author breadcrumbs: Accueil > La rédaction > {Author} > Page X
 *
 * @since 2.0.0
 */
add_filter('rank_math/frontend/breadcrumb/items', function($crumbs, $class) {

    if (!is_author()) {
        return $crumbs;
    }

    $author    = get_queried_object();
    $author_id = isset($author->ID) ? (int) $author->ID : 0;
    $name      = $author ? $author->display_name : '';
    $paged     = max(1, (int) get_query_var('paged'));

    $new = array();

    $new[] = array('Accueil', home_url('/'));
    $new[] = array('La rédaction', home_url('/la-redaction/'));

    if ($author_id && $name) {
        $new[] = array($name, get_author_posts_url($author_id));
    }

    // Pagination (last crumb without URL)
    if ($paged > 1) {
        $new[] = array('Page ' . $paged, '');
    }

    return $new;

}, 10, 2);

// =============================================================================
// SITEMAP ENTRY CLEANUP
// =============================================================================

/**
 * Clean sitemap entries
 *
 * Removes: query strings, pagination paths, external URLs, fragments
 *
 * @since 2.0.0
 */
add_filter('rank_math/sitemap/entry', function ($entry, $type, $object) {

    if (empty($entry['loc'])) {
        return $entry;
    }

    $url = (string) $entry['loc'];

    // Remove fragments
    $url = preg_replace('/#.*/', '', $url);

    // Validate URL
    if (!wp_http_validate_url($url)) {
        return false;
    }

    $parts = wp_parse_url($url);
    if (empty($parts['scheme']) || empty($parts['host'])) {
        return false;
    }

    $scheme = strtolower($parts['scheme']);
    if (!in_array($scheme, ['http', 'https'], true)) {
        return false;
    }

    // Allow home_url() and site_url() hosts
    $allowed_hosts = array_filter(array_unique([
        wp_parse_url(home_url('/'), PHP_URL_HOST),
        wp_parse_url(site_url('/'), PHP_URL_HOST),
    ]));

    if (empty($allowed_hosts)) {
        return false;
    }

    $host_ok = false;
    foreach ($allowed_hosts as $h) {
        if ($h && strcasecmp($parts['host'], $h) === 0) {
            $host_ok = true;
            break;
        }
    }
    if (!$host_ok) {
        return false;
    }

    // Query strings out
    if (strpos($url, '?') !== false) {
        return false;
    }

    // Pagination paths out
    if (preg_match('#/page/\d+/?$#', $url)) {
        return false;
    }

    $entry['loc'] = $url;
    return $entry;

}, 99, 3);

// =============================================================================
// CANONICAL HANDLERS
// =============================================================================

/**
 * Pagination canonical: self-referent for /page/N/
 *
 * @since 2.0.0
 */
add_filter('rank_math/frontend/canonical', function ($canonical) {

    if (!is_string($canonical) || $canonical === '') {
        return $canonical;
    }

    if (!is_paged()) {
        return $canonical;
    }

    $current = (function_exists('wp_get_canonical_url') ? wp_get_canonical_url() : '');

    if (!$current) {
        $scheme = is_ssl() ? 'https' : 'http';
        $host   = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $path   = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $current = $scheme . '://' . $host . $path;
    }

    $current = preg_replace('/#.*/', '', $current);
    $current = preg_replace('/\?.*/', '', $current);
    $current = trailingslashit($current);

    if (preg_match('#/page/\d+/?$#', $current)) {
        return $current;
    }

    return $canonical;

}, 99);

/**
 * Tracking params canonical: strip tracking query params
 *
 * Detects: utm_*, gclid, fbclid, msclkid, gbraid, wbraid, yclid, dclid
 *
 * @since 2.0.0
 */
add_filter('rank_math/frontend/canonical', function ($canonical) {

    if (!is_string($canonical) || $canonical === '') {
        return $canonical;
    }

    $current = (function_exists('wp_get_canonical_url') ? wp_get_canonical_url() : '');

    if (!$current) {
        $scheme = is_ssl() ? 'https' : 'http';
        $host   = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $path   = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $current = $scheme . '://' . $host . $path;
    }

    $current = preg_replace('/#.*/', '', $current);

    $parts = wp_parse_url($current);
    if (empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])) {
        return $canonical;
    }

    // Host guardrail
    $allowed_hosts = array_filter(array_unique([
        wp_parse_url(home_url('/'), PHP_URL_HOST),
        wp_parse_url(site_url('/'), PHP_URL_HOST),
    ]));
    $host_ok = false;
    foreach ($allowed_hosts as $h) {
        if ($h && strcasecmp($parts['host'], $h) === 0) {
            $host_ok = true;
            break;
        }
    }
    if (!$host_ok) {
        return $canonical;
    }

    // No query => nothing to do
    if (empty($parts['query'])) {
        return $canonical;
    }

    parse_str($parts['query'], $query);

    if (empty($query) || !is_array($query)) {
        return $canonical;
    }

    // Tracking detectors
    $tracking_prefixes = ['utm_'];
    $tracking_keys     = [
        'gclid', 'fbclid', 'msclkid',
        'gbraid', 'wbraid',
        'yclid', 'dclid',
    ];

    $has_tracking = false;

    foreach ($query as $k => $v) {
        $key = strtolower((string) $k);

        if (in_array($key, $tracking_keys, true)) {
            $has_tracking = true;
            break;
        }

        foreach ($tracking_prefixes as $p) {
            if (strpos($key, $p) === 0) {
                $has_tracking = true;
                break 2;
            }
        }
    }

    if (!$has_tracking) {
        return $canonical;
    }

    // Target canonical = URL without query, normalized trailing slash
    $target = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
    $target = preg_replace('#/+$#', '/', $target);
    $target = trailingslashit($target);

    return $target;

}, 99);

/**
 * Marques canonical: self-referent for /marques/ URLs
 *
 * @since 2.0.0
 */
add_filter('rank_math/frontend/canonical', function ($canonical) {

    if (!is_string($canonical) || $canonical === '') {
        return $canonical;
    }

    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    if ($uri === '' || stripos($uri, '/marques/') !== 0) {
        return $canonical;
    }

    $current = (function_exists('wp_get_canonical_url') ? wp_get_canonical_url() : '');

    if (!$current) {
        $scheme = is_ssl() ? 'https' : 'http';
        $host   = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $current = $scheme . '://' . $host . $uri;
    }

    $current = preg_replace('/#.*/', '', $current);
    $current = preg_replace('/\?.*/', '', $current);

    // Host guardrail
    $allowed_hosts = array_filter(array_unique([
        wp_parse_url(home_url('/'), PHP_URL_HOST),
        wp_parse_url(site_url('/'), PHP_URL_HOST),
    ]));
    $parts = wp_parse_url($current);
    if (empty($parts['host']) || !in_array($parts['host'], $allowed_hosts, true)) {
        return $canonical;
    }

    $current = trailingslashit($current);

    // Only force if still within /marques/
    if (!preg_match('#^https?://[^/]+/marques/#i', $current)) {
        return $canonical;
    }

    return $current;

}, 99);

/**
 * Redacteur canonical: normalize for /redacteur/ URLs
 *
 * Note: lm_can_is_redacteur_scope() and lm_can_current_url_clean()
 * are defined in utilities/security.php
 *
 * @since 2.0.0
 */
add_filter('rank_math/frontend/canonical', function ($canonical) {
    if (!lm_can_is_redacteur_scope()) return $canonical;
    return lm_can_current_url_clean();
}, 99);

// =============================================================================
// TITLE & DESCRIPTION PAGINATION
// =============================================================================

/**
 * Standardize paginated titles to " - Page X" format
 *
 * Scope: Blog index (is_home), tag/category archives
 *
 * @since 2.0.0
 */
add_filter('rank_math/frontend/title', function ($title) {

    $p = (int) get_query_var('paged');
    if ($p <= 1) {
        return $title;
    }

    // Blog index (posts page)
    if (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');
        if ($posts_page_id > 0) {
            if (stripos($title, 'Page ' . $p) === false) {
                $title .= ' – Page ' . $p;
            }
            return $title;
        }
    }

    // Tag / Category archives
    if (is_tag() || is_category()) {

        // Clean existing pagination (RM/theme)
        $title = preg_replace('~\s*[-–]\s*Page\s+\d+\s*(?:à|of)\s*\d+~iu', '', $title);
        $title = preg_replace('~\s*[-–]\s*Page\s+\d+~iu', '', $title);

        if (stripos($title, 'Page ' . $p) === false) {
            $title = rtrim($title) . ' – Page ' . $p;
        }

        return $title;
    }

    return $title;

}, 99);

/**
 * Tag archives: suffix pagination on meta description
 *
 * Avoids duplicate descriptions on /tag/.../page/N/
 *
 * @since 2.0.0
 */
add_filter('rank_math/frontend/description', function ($description) {

    if (!is_tag()) {
        return $description;
    }

    $p = (int) get_query_var('paged');
    if ($p <= 1) {
        return $description;
    }

    if (stripos($description, 'Page ' . $p) !== false) {
        return $description;
    }

    $description = trim((string) $description);

    if ($description === '') {
        $term = get_queried_object();
        $name = !empty($term->name) ? $term->name : 'Actualités';
        $description = $name;
    }

    return $description . ' – Page ' . $p;

}, 99);
