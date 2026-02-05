<?php

/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */

/**
 * ============================================================
 * TOGGLE CSS REFACTORISÉ - PHASE DE TEST
 * ============================================================
 * Mettre à true pour charger css/style-refactored.css
 * en plus de style.css (les deux coexistent pour comparaison).
 *
 * ATTENTION: Désactiver (false) avant mise en production
 * si des régressions visuelles sont détectées.
 * ============================================================
 */
define('USE_REFACTORED_CSS', true);

// Inclure le fichier du shortcode
include_once get_stylesheet_directory() . '/inc/shortcodes/shortcode_list.php';

/*head optimisations*/
function enqueue_instagram_embed_script() {
    // Vérifie si on est sur un type de post spécifique : post, marque, ou test
    if (is_singular(array('post', 'marque', 'test'))) {
        // Ajoute le script d'Instagram dans le head de la page
        echo '<script async src="//www.instagram.com/embed.js"></script><script async src="https://www.tiktok.com/embed.js"></script>';

    }
}
add_action('wp_head', 'enqueue_instagram_embed_script');

function enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
}
add_action('wp_enqueue_scripts', 'enqueue_font_awesome');

function my_track_post_views()
{
    global $post;

    // Vérifiez si vous êtes sur une page de contenu singulier (article, test, ou autre type de post personnalisé)
    if (is_singular() && ('test' === get_post_type($post) || 'post' === get_post_type($post))) {
        $views = (int) get_post_meta($post->ID, 'post_views_count', true);

        update_post_meta($post->ID, 'post_views_count', $views + 1);
    }
}
//add_action('wp_head', 'my_track_post_views');

function load_acf_scripts() {
    acf_form_head(); // Assurez-vous que cela ne cause pas de problèmes de performance ou de fonctionnement
}
add_action('wp_head', 'load_acf_scripts');


/*add excerpt to gutenberg pages*/
add_action('init', 'wpse325327_add_excerpts_to_pages');
function wpse325327_add_excerpts_to_pages()
{
    add_post_type_support('page', 'excerpt');
}

function my_custom_fonts_enqueue()
{

    wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/inc/js/custom.js', array(), false, true);

}
add_action('wp_enqueue_scripts', 'my_custom_fonts_enqueue');

add_filter('generateblocks_query_loop_args', function ($query_args, $attributes) {

	$paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $query_args['paged'] = $paged;

    if (!is_admin() && !empty($attributes['className']) && strpos($attributes['className'], 'wpgb-query') !== false) {
        $query_args['wp_grid_builder'] = 'wpgb-content-2';
    }

    return $query_args;
}, 10, 2);

/*bouton affiliz*/
add_filter('wp_grid_builder/blocks', function($blocks) {
    $blocks['affilizz_button'] = [
        'name' => __('Bouton Affilizz', 'text-domain'),
        'render_callback' => function() {
            global $post;
            $post = wpgb_get_post();
            setup_postdata($post);

            // Récupérer le contenu du champ ACF
            $bouton_affiliz = get_field('bouton_affiliz', $post->ID);

            if ($bouton_affiliz) {
                // Afficher le contenu HTML sans échappement, encapsulé dans une div avec classe
                echo '<div class="affilizz-container">' . $bouton_affiliz . '</div>';
            }

            wp_reset_postdata();
        },
    ];

    return $blocks;
});


//author feautured
add_filter('wp_grid_builder/grid/the_object', function ($object) {
    $grid = wpgb_get_grid_settings();

    if (19 === (int) $grid->id) {
        $image = get_field('featured_image', 'user_' . $object->ID);


        if (!empty($image)) {
            $object->post_thumbnail = $image;
        }
    }

    return $object;
}, 10, 1);

// Ajouter des colonnes personnalisées à la liste d'administration du CPT 'test'
add_filter('manage_test_posts_columns', function ($columns) {
    $columns['marque'] = __('Marque');
    return $columns;
});

// Remplir les colonnes personnalisées pour le CPT 'test'
add_action('manage_test_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'marque':

            $marque = get_field('marque', $post_id);
            $edit_link = get_edit_post_link($marque);


            // Si le champ renvoie un seul ID de post
            if (is_numeric($marque)) {
                $edit_link = get_edit_post_link($marque);
                $marque_name = get_the_title($marque);
                echo "<a href='{$edit_link}'>{$marque_name}</a>";
            } elseif (is_array($marque) && !empty($marque)) {
                $links = array_map(function ($id) {
                    $edit_link = get_edit_post_link($id);
                    $marque_name = get_the_title($id);
                    return "<a href='{$edit_link}'>{$marque_name}</a>";
                }, $marque);
                echo implode(', ', $links);
            } else {
                echo 'Aucune marque';
            }
            break;
    }
}, 10, 2);

add_action('restrict_manage_posts', function ($post_type) {
    if ('test' === $post_type) {
        $taxonomies = ['categorie_test', 'etiquette-test']; // Target taxonomies.

        foreach ($taxonomies as $tax_slug) {
            $tax_obj = get_taxonomy($tax_slug);
            if (!$tax_obj) continue;

            wp_dropdown_categories([
                'show_option_all' => "Show All {$tax_obj->labels->name}",
                'taxonomy'        => $tax_slug,
                'name'            => $tax_slug,
                'orderby'         => 'name',
                'selected'        => isset($_GET[$tax_slug]) ? $_GET[$tax_slug] : '',
                'hierarchical'    => true,
                'depth'           => 3,
                'show_count'      => false,
                'hide_empty'      => false,
                'value_field'     => 'slug', // Use slug as the option value.
            ]);
        }
    }
});
add_filter('parse_query', function ($query) {
    global $pagenow;

    if ('edit.php' === $pagenow && 'test' === $query->query_vars['post_type'] && is_admin()) {
        $tax_query = []; // Initialize taxonomy query array.

        foreach (['categorie_test', 'etiquette-test'] as $tax_slug) {
            if (!empty($_GET[$tax_slug]) && $_GET[$tax_slug] != '0') { // Check for non-empty slug.
                $tax_query[] = [
                    'taxonomy' => $tax_slug,
                    'field'    => 'slug',
                    'terms'    => [$_GET[$tax_slug]],
                ];
            }
        }

        if (!empty($tax_query)) {
            if (count($tax_query) > 1) {
                $tax_query['relation'] = 'AND';
            }
            $query->set('tax_query', $tax_query);
        }
    }
});

// Breadcrumb overrides handled by lm-seo-core (Yoast removed).

add_filter('wp_grid_builder/grid/the_object', function ($object) {
    $grid = wpgb_get_grid_settings();

    // Vérifiez si nous sommes dans le grid ID souhaité
    if (10 === (int) $grid->id) {

        // ID en dur pour la catégorie 'categorie_test' avec tag_ID 195
        $category_id = $object->term_id;
        $taxonomy = 'categorie_test';

        // Récupération du champ ACF pour la catégorie spécifique
        // Assurez-vous que le champ 'featured' est configuré correctement dans ACF
        $image = get_field('featured', $taxonomy . '_' . $category_id);

        // S'il existe une URL d'image, affectez-la à l'objet
        if (!empty($image)) {
            $object->post_thumbnail = $image;
        }
    }

    return $object;
}, 10, 1);

/**/

function update_last_updated_test_category($post_id)
{
    // Vérifiez si c'est bien le post type 'test'
    if (get_post_type($post_id) !== 'test') {
        return;
    }

    // Obtenez les catégories liées au post
    $categories = get_the_terms($post_id, 'categorie_test');
    if (empty($categories)) {
        return;
    }

    // Prenez la première catégorie (ou une autre logique selon vos besoins)
    $category = $categories[0];

    // Sauvegardez l'ID de la dernière catégorie mise à jour dans les options
    update_option('last_updated_test_category_id', $category->term_id);
}
add_action('save_post', 'update_last_updated_test_category');




function grid_query_related_products_fill($query_args, $grid_id)
{
    if ('23' !== (string) $grid_id) {
        return $query_args;
    }

    global $post;

    // 1. Récupérer les produits associés avec ACF
    $associated_products = get_field('produit_associe', $post->ID);

    // 2. Récupérer les IDs des produits associés
    $product_ids = !empty($associated_products) ? array_map(function ($product) {
        return is_object($product) ? $product->ID : $product; // Ajustement pour gérer les objets ou les IDs
    }, $associated_products) : [];

    // Calcul du nombre de produits supplémentaires nécessaires pour atteindre 6
    $count_needed = 6 - count($product_ids);

    // 3. Ajouter des produits supplémentaires si nécessaire
    if ($count_needed > 0) {
        // Récupérer les catégories de l'article
        $article_categories = get_the_category($post->ID);
        $target_category_ids = [];
        foreach ($article_categories as $category) {
            // Trouver la correspondance dans la catégorie de test
            $test_term = get_term_by('slug', $category->slug, 'categorie_test');
            if ($test_term) {
                $target_category_ids[] = $test_term->term_id;
                break; // On arrête après avoir trouvé une correspondance
            }
        }

        // Récupérer des posts supplémentaires dans la catégorie de test si besoin
        if (!empty($target_category_ids)) {
            $additional_args = [
                'post_type' => 'test',
                'tax_query' => [[
                    'taxonomy' => 'categorie_test',
                    'field'    => 'term_id',
                    'terms'    => $target_category_ids,
                ]],
                'posts_per_page' => $count_needed,
                'fields' => 'ids',
                'post__not_in' => $product_ids, // Exclure les produits déjà sélectionnés
            ];

            // Obtenir les produits supplémentaires
            $additional_posts = get_posts($additional_args);

            if (!empty($additional_posts)) {
                $product_ids = array_merge($product_ids, $additional_posts);
                $count_needed = 6 - count($product_ids);
            }
        }
    }

    // 4. Si on a encore besoin de produits pour compléter
    if ($count_needed > 0) {
        $additional_args = [
            'post_type'      => 'test',
            'posts_per_page' => $count_needed,
            'fields'         => 'ids',
            'post__not_in'   => $product_ids, // Exclure les produits déjà sélectionnés
        ];

        // Obtenir des posts supplémentaires sans filtrer par catégorie
        $additional_posts = get_posts($additional_args);

        if (!empty($additional_posts)) {
            $product_ids = array_merge($product_ids, $additional_posts);
        }
    }

    // 5. Mise à jour des arguments de requête pour inclure tous les produits (associés et supplémentaires)
    $query_args['post__in'] = $product_ids;
    $query_args['orderby'] = 'post__in';
    $query_args['posts_per_page'] = 6; // Afficher 6 produits maximum

    return $query_args;
}
add_filter('wp_grid_builder/grid/query_args', 'grid_query_related_products_fill', 10, 2);


add_filter('wp_grid_builder/grid/query_args', function ($query_args, $grid_id) {
    // Target a specific grid by its ID
    if (6 === $grid_id) {
        // Add or modify query arguments
        $query_args['meta_key'] = 'note_globale'; // Assuming 'note_globale' is the correct meta key
        $query_args['orderby'] = 'meta_value_num'; // Order by the numerical value of the meta key
        $query_args['order'] = 'DESC'; // Sort from highest to lowest
    }

    return $query_args;
}, 10, 2);


/**
 * NOTE : Toute la logique HTTP (410/301), pagination WP Grid Builder
 * et gestion SEO des marques a été déplacée dans le plugin
 * "LM Redirect Manager".
 * Ne rien remettre ici, tout se gère côté plugin.
 */


function grid_query_related_products_test_fill($query_args, $grid_id)
{
    if ('24' !== (string) $grid_id) {
        return $query_args;
    }

    global $post;
    $associated_products = get_field('produit_associe', $post->ID);
    $product_ids = !empty($associated_products) ? array_map(function ($product) {
        return $product->ID;
    }, $associated_products) : [];

    $count_needed = 6 - count($product_ids);

    if ($count_needed > 0) {
        $test_terms = [];
        if ('test' === get_post_type($post)) {
            // Pour les singles cpt test, utiliser directement sa ou ses catégories
            $test_terms = get_the_terms($post->ID, 'categorie_test');
        } else {
            // Pour les posts, trouver la catégorie de test correspondante aux catégories d'articles
            $article_categories = get_the_category($post->ID);
            foreach ($article_categories as $category) {
                $test_term = get_term_by('slug', $category->slug, 'categorie_test');
                if ($test_term) {
                    $test_terms[] = $test_term;
                    break; // Utilisez uniquement la première correspondance pour simplifier
                }
            }
        }

        if (!empty($test_terms)) {
            $test_term_ids = wp_list_pluck($test_terms, 'term_id');
            $additional_args = [
                'post_type' => 'test',
                'tax_query' => [[
                    'taxonomy' => 'categorie_test',
                    'field'    => 'term_id',
                    'terms'    => $test_term_ids,
                ]],
                'posts_per_page' => $count_needed,
                'fields' => 'ids',
                'post__not_in' => $product_ids, // Exclude already selected products
            ];

            $additional_posts = get_posts($additional_args);

            if (!empty($additional_posts)) {
                $product_ids = array_merge($product_ids, $additional_posts);
            }
        }
    }

    $query_args['post__in'] = $product_ids;
    $query_args['orderby'] = 'post__in';
    $query_args['posts_per_page'] = count($product_ids); // Ensure the grid shows the exact number of products

    return $query_args;
}
add_filter('wp_grid_builder/grid/query_args', 'grid_query_related_products_test_fill', 10, 2);




/*get the correct id in the query loop*/
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




// Robots handled by lm-seo-core (Yoast removed).

if (!function_exists('lm_seo_core_active') || !lm_seo_core_active('redirects')) {
    // Redirect all /feed/ URLs for the 'marques' custom post type to the homepage
    add_action('template_redirect', function() {
        // Check if the current URL is a feed for the 'marques' custom post type
        if (is_feed() && get_query_var('post_type') === 'marque') {
            // Perform the redirect to the homepage
            wp_redirect(home_url(), 301); // 301 indicates a permanent redirect
            exit;
        }

    	   if (is_paged()) {
            $paged = get_query_var('paged');
            $redirect = false;

            // Redirection pour les pages de pagination générales
            if ($paged > 200) {
                $redirect = true;
            }

            // Redirection pour les catégories spécifiques
            if (is_category() && $paged > 200) {
                $redirect = true;
            }

            // Redirection pour les archives spéciales
            if ((is_tax('promotion') || is_tax('soldes')) && $paged >200) {
                $redirect = true;
            }

            /*if ($redirect) {
                wp_redirect(get_pagenum_link(1), 301); // Redirige vers la première page
                exit;
            }*/
        }
    });
}

function adjust_main_query_based_on_ratings($query)
{
    if (!is_admin() && $query->is_main_query() && (is_tax('categorie_test') || is_tax('etiquette_test'))) {
        // Premièrement, déterminons si des posts avec une note_globale > 0 existent dans cette taxonomie
        $has_rated_posts = false; // Supposez initialement qu'il n'y a pas de posts avec note > 0

        $rated_posts_query = new WP_Query(array(
            'post_type' => 'test', // Assurez-vous que c'est le bon type de post
            'tax_query' => array(
                array(
                    'taxonomy' => 'categorie_test',
                    'field'    => 'term_id',
                    'terms'    => get_queried_object_id(),
                ),
            ),
            'meta_query' => array(
                array(
                    'key'     => 'note_globale',
                    'value'   => 0,
                    'compare' => '>',
                    'type'    => 'NUMERIC',
                ),
            ),
            'posts_per_page' => 1,
        ));

        if ($rated_posts_query->have_posts()) {
            $has_rated_posts = true;
        }

        // Si des posts avec une note_globale > 0 existent, on ajuste la requête principale pour trier par note_globale d'abord
        if ($has_rated_posts) {
            $query->set('meta_key', 'note_globale');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'DESC');
        } else {
            // Sinon, on trie par post_views_count si disponible, sinon par date
            $query->set('meta_query', array(
                'relation' => 'OR',
                array(
                    'key' => 'post_views_count',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key' => 'post_views_count',
                    'compare' => 'NOT EXISTS',
                ),
            ));
            $query->set('orderby', array(
                'post_views_count' => 'DESC',
                'modified' => 'DESC'
            ));
        }
    }
}
add_action('pre_get_posts', 'adjust_main_query_based_on_ratings');


/*change author base*/
function pm_change_author_base()
{
    global $wp_rewrite;
    $wp_rewrite->author_structure = 'redacteur/%author%';
}
add_action('init', 'pm_change_author_base', 10);


function initialize_post_views_for_existing_posts()
{
    // Types de post à traiter
    $post_types = array('post', 'test');

    foreach ($post_types as $post_type) {
        // Récupérer tous les posts du type en question
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'numberposts' => -1, // Sélectionner tous les posts
            'fields' => 'ids', // Récupérer uniquement les IDs pour optimiser la mémoire
        );

        $posts = get_posts($args);

        foreach ($posts as $post_id) {
            // Vérifier si le post a déjà un 'post_views_count' défini
            $views = get_post_meta($post_id, 'post_views_count', true);

            if (empty($views)) {
                // Si aucun nombre de vues n'est défini, l'initialiser à 0
                update_post_meta($post_id, 'post_views_count', 0);
            }
        }
    }

    echo "Initialisation terminée.";
}

// Décommentez la ligne ci-dessous pour exécuter la fonction.
//initialize_post_views_for_existing_posts();

/*check post before publishing*/
function check_category_before_publishing($prepared_post, $request) {
    // Autosaves et brouillons peuvent ne pas exposer post_status : on sort sans rien faire
    if (!is_object($prepared_post) || !property_exists($prepared_post, 'post_status')) {
        return $prepared_post;
    }

    $post_id = isset($prepared_post->ID) ? $prepared_post->ID : null;

    if ($prepared_post->post_status === 'publish') {
        // L'ID de la catégorie 'Blog'
        $blog_category_id = get_cat_ID('Blog'); // Utilisez le nom exact de votre catégorie "Blog"

        // Récupérer les catégories de l'article
        $categories = wp_get_post_categories($post_id);

        // Vérifier si aucune catégorie n'est sélectionnée ou si la catégorie 'Blog' est sélectionnée
        if (empty($categories)) {
            return new WP_Error(
                'rest_post_invalid_category',
                __("Vous devez sélectionner au moins une catégorie avant de pouvoir publier cet article.", 'text-domain'),
                array('status' => 400)
            );
        } elseif (in_array($blog_category_id, $categories)) {
            return new WP_Error(
                'rest_post_invalid_category',
                __("Vous ne pouvez pas publier un article dans la catégorie 'Blog'. Veuillez choisir une autre catégorie.", 'text-domain'),
                array('status' => 400)
            );
        }
    }

    return $prepared_post;
}

add_filter('rest_pre_insert_post', 'check_category_before_publishing', 10, 2);

// Adjust RSS feed publication date to match site timezone and format
// Ajuster le fuseau horaire et le format de la date pour le flux RSS
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

function custom_rss_pubdate() {
    global $post;
    $pub_date = adjust_rss_pubdate_timezone($post->post_date_gmt);
    echo "<pubDate>$pub_date</pubDate>\n";
}

//add_action('rss2_item', 'custom_rss_pubdate');

// Inclure les types de post personnalisés dans le flux RSS
function add_custom_post_types_to_rss_feed($query) {
    if ($query->is_feed() && !isset($query->query_vars['post_type'])) {
        $query->set('post_type', array('post', 'test')); // Remplacez 'your_custom_post_type' par le slug de votre type de post personnalisé
    }
    return $query;
}
add_filter('pre_get_posts', 'add_custom_post_types_to_rss_feed');

function exclude_empty_content_or_no_associations($query_args, $grid_id) {
    if ('29' !== (string) $grid_id) {
        return $query_args;
    }

    // Ajouter une condition pour exclure les posts sans contenu
    $query_args['meta_query'] = array(
        'relation' => 'OR',
        array(
            'key'     => 'post_content',
            'value'   => '',
            'compare' => '!=',
        ),
        array(
            'key'     => 'post_content',
            'compare' => 'EXISTS',
        ),
    );

    // Ajouter une condition pour exclure les posts sans tests ou articles associés
    $query_args['meta_query'][] = array(
        'relation' => 'OR',
        array(
            'key'     => 'produit_associe',
            'compare' => 'EXISTS',
        ),
        array(
            'key'     => 'articles_associes',
            'compare' => 'EXISTS',
        ),
    );

    return $query_args;
}
add_filter('wp_grid_builder/grid/query_args', 'exclude_empty_content_or_no_associations', 10, 2);

/* Ajouter automatiquement des dimensions aux images */
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

/* Ajouter des dimensions aux images Affilizz dans le contenu */
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

/* Désactiver les commentaires pour les types de post 'marque' */
add_action('init', function () {
    if (is_singular('marque')) {
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
    }
});

/* Activer le Lazy Load pour toutes les images */
add_filter('wp_lazy_loading_enabled', '__return_true');

/* Forcer le Lazy Load pour les images ajoutées par des plugins qui n'utilisent pas par défaut */
function force_lazy_load_images($content) {
    // Ajoute loading="lazy" aux balises img sans cet attribut
    $content = preg_replace('/<img(?![^>]+loading=["\'](?:lazy|eager|auto)["\'])([^>]+)>/', '<img loading="lazy" $1>', $content);
    return $content;
}
add_filter('the_content', 'force_lazy_load_images');




add_filter( "litespeed_media_ignore_remote_missing_sizes", "__return_true" );

add_filter( 'gettext', function( $text ) {
    if ( 'Search Results for: %s' === $text ) {
        $text = 'Résultats pour : %s';
    }
    return $text;
});


add_action('plugins_loaded', function () {
    if (class_exists('WPSP_PRO')) {
        remove_action('plugins_loaded', ['WPSP_PRO', 'init'], 10);
        add_action('init', ['WPSP_PRO', 'init']);
    }

    if (class_exists('WPSP')) {
        remove_action('plugins_loaded', ['WPSP', 'init'], 10);
        add_action('init', ['WPSP', 'init']);
    }

    if (class_exists('Affilizz\Core')) {
        $affilizz_core = Affilizz\Core::get_instance();
        remove_action('plugins_loaded', [$affilizz_core, 'init'], 10);
        add_action('init', [$affilizz_core, 'init']);
    }
});


//new image size
add_image_size('author-thumbnail', 30, 30, true); // true pour le crop
add_image_size('grid_size', 150, 150, true); // true pour le crop

//remove comments

// Désactiver les commentaires sur tout le site
add_action('init', function() {
    // Fermer les commentaires pour les articles et les pages
    update_option('default_comment_status', 'closed');
    update_option('default_ping_status', 'closed');

    // Supprimer les commentaires des types de publication
    remove_post_type_support('post', 'comments');
    remove_post_type_support('page', 'comments');
});

// Supprimer les menus liés aux commentaires dans l'administration
add_action('admin_menu', function() {
    remove_menu_page('edit-comments.php');
});

// Supprimer les widgets liés aux commentaires
add_action('wp_dashboard_setup', function() {
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
});

// Supprimer les commentaires du fil d'administration
add_action('admin_init', function() {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }
});

// Supprimer les commentaires du front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Supprimer les champs de formulaire des commentaires
add_filter('comments_array', '__return_empty_array', 10, 2);

// Supprimer les liens vers les commentaires dans l'en-tête
add_action('init', function() {
    remove_action('wp_head', 'feed_links_extra', 3);
});

// Supprimer l'élément "Discussion" dans l'administration des réglages
add_action('admin_init', function() {
    remove_meta_box('commentstatusdiv', 'post', 'normal');
    remove_meta_box('commentstatusdiv', 'page', 'normal');
});

add_filter('litespeed_optm_img_attr', function($attr) {
    // Supprime le fetchpriority="high" des images
    if (isset($attr['fetchpriority']) && $attr['fetchpriority'] === 'high') {
        unset($attr['fetchpriority']);
    }
    return $attr;
});

/*filter grid by author*/

add_filter('wp_grid_builder/grid/query_args', function ($query_args, $grid_id) {
    if (26 === $grid_id && is_author()) {
        $author_id = get_queried_object_id();

        // Auteur courant
        $query_args['author'] = $author_id;
    }

    return $query_args;
}, 10, 2);

add_filter( 'wpgb_lazy_load', '__return_false' );

/**
 * Charge le script Affilizz avant CookieYes pour éviter tout blocage
 * et optimise le chargement pour un affichage rapide du bloc prix.
 * S'applique uniquement sur les articles (post) et les tests (CPT "test").
 */
add_action('wp_head', function() {
    if ( !is_admin() && ( is_singular('post') || is_singular('test') ) ) {
        // Préconnect pour accélérer la connexion TLS
        echo '<link rel="preconnect" href="https://sc.affilizz.com" crossorigin>' . "\n";

        // Charge directement le script Affilizz avec defer (non bloquant mais prioritaire)
        echo '<script type="text/javascript" src="https://sc.affilizz.com/affilizz.js" defer crossorigin="anonymous"></script>' . "\n";
    }
}, 1);

/**
 * Force l'exécution immédiate du bloc Affilizz
 * (utile si WP Rocket ou CookieYes retardent les scripts tiers)
 */
add_action('wp_footer', function() {
    if ( !is_admin() && ( is_singular('post') || is_singular('test') ) ) : ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            function triggerAffilizzRender() {
                if (typeof window.affilizz !== 'undefined' && typeof window.affilizz.renderAll === 'function') {
                    try {
                        window.affilizz.renderAll();
                        console.log('✅ Bloc Affilizz déclenché manuellement');
                        return true;
                    } catch (e) {
                        console.warn('⚠️ Erreur Affilizz:', e);
                    }
                }
                return false;
            }

            if (!triggerAffilizzRender()) {
                // Retente après un court délai
                setTimeout(triggerAffilizzRender, 300);
                // Et au scroll si tout est encore bloqué
                window.addEventListener('scroll', triggerAffilizzRender, { once: true });
            }
        });
        </script>
    <?php endif;
});






// Gestion anticipée des pages de pagination d'actualités qui seraient des 404
if (!function_exists('lm_seo_core_active') || !lm_seo_core_active('redirects')) {
    add_filter( 'pre_handle_404', 'custom_pre_handle_404', 10, 2 );
    function custom_pre_handle_404( $false, $wp_query ) {
        $current_url = $_SERVER['REQUEST_URI'];

        // Vérifier les pages de pagination d'actualités
        if ( preg_match( '/\/actualites\/page\/(\d+)\/?/', $current_url, $matches ) ) {
            $page_number = (int) $matches[1];

            if ( $page_number > 0 ) {
                $posts_per_page = get_option( 'posts_per_page' );
                $total_posts = wp_count_posts()->publish;
                $max_pages = ceil( $total_posts / $posts_per_page );

                // Rediriger si la page demandée dépasse le nombre maximum de pages
                if ( $page_number > $max_pages ) {
                    wp_redirect( home_url( '/actualites/' ), 301 );
                    exit;
                }
            }
        }

        return $false;
    }
}





/**
 * Optimisation du flux RSS LaboMaison
 */

// Forcer le <link> du flux RSS vers la home
add_filter('bloginfo_rss', function($output, $show) {
    if ($show === 'url') {
        return home_url('/');
    }
    return $output;
}, 10, 2);

//  Nettoyer <description> et forcer ACF "chapeau" ou excerpt
add_filter('the_excerpt_rss', 'labomaison_custom_rss_description');
add_filter('the_content_feed', 'labomaison_custom_rss_description');


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

// Ajouter <content:encoded> avec image full + contenu formaté + CTA en dernier
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
    $content .= '<p><strong><a href="'.get_permalink($post->ID).'">👉 Lire l’article complet sur LaboMaison</a></strong></p>';

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

//  Ajouter <enclosure> complet avec length (taille du fichier image)
function labomaison_add_enclosure() {
    global $post;

    if (!has_post_thumbnail($post->ID)) {
        return;
    }

    $img_url = get_the_post_thumbnail_url($post->ID, 'full');

    // Convertit l’URL publique en chemin serveur
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


//  Nettoyer <guid> pour utiliser l’URL canonique
add_filter('get_the_guid', 'labomaison_clean_guid', 10, 2);
function labomaison_clean_guid($guid, $post_id) {
    return get_permalink($post_id);
}




add_action('wp_enqueue_scripts', function() {
    if (!is_admin()) {
        wp_dequeue_style('acf-input');
        wp_dequeue_style('acf-global');
        wp_dequeue_style('acf-pro-input');
        wp_dequeue_style('acfe-input');
        wp_dequeue_style('acfe');
    }
}, 100);


// === Forcer WordPress à utiliser le flux RSS personnalisé du thème enfant ===
function lm_force_custom_rss_template($template) {
    $custom = get_stylesheet_directory() . '/feed-rss2.php';
    if ( file_exists( $custom ) ) {
        return $custom;
    }
    return $template;
}
add_filter('feed_template', 'lm_force_custom_rss_template');


// === Force le flux principal RSS 2.0 à utiliser le template du thème enfant ===
add_action('template_redirect', function() {
    if ( is_feed() && !is_feed('comments-rss2') ) {
        include get_stylesheet_directory() . '/feed-rss2.php';
        exit;
    }
}, 0);

/**
 * GA4 + Google Consent Mode (CookieYes v3.3.1)
 * - Exclut : utilisateurs connectés, prévisualisations, contenus non publiés (si singular), préproduction
 * - Compatible avec tous les événements CookieYes (cookieyes_consent_update, cli_consent_update, cookieyes_event_updated)
 */

if ( ! function_exists('lm_should_load_ga4') ) {
    function lm_should_load_ga4() {
        // Préprod ?
        $is_preprod = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'preprod.') !== false;

        // Brouillon / non publié (uniquement sur les contenus singuliers)
        $not_published = false;
        if ( function_exists('is_singular') && is_singular() && function_exists('get_post_status') ) {
            $status = get_post_status( get_queried_object_id() );
            if ( $status && $status !== 'publish' ) {
                $not_published = true;
            }
        }

        return ! is_user_logged_in()
            && ! is_preview()
            && ! $not_published
            && ! $is_preprod;
    }
}

/**
 * Google Tag (GT-NMCXXG3R) – Consent Mode v2 + CookieYes Bridge
 * Version 2.1 – Compatible GA4 via Google Tag
 * Avantages :
 * ✅ 100% compatible avec le Consent Mode de Google
 * ✅ Récupère les page_view après consentement
 * ✅ Détecte les consentements existants (visiteurs récurrents)
 * ✅ Pas de double comptage
 * ✅ Compatible CookieYes v3.3.1
 */

function lm_enqueue_ga4_head() {
    // Vérifier si GA4 doit être chargé
    if ( ! lm_should_load_ga4() ) {
        add_action('wp_head', function () {
            echo "<!-- 🔒 GA4 désactivé (admin/preview/preprod) -->\n";
        }, 1);
        return;
    }

    // 1) Charger le script Google Tag Manager
    wp_enqueue_script(
        'lm-gtag',
        'https://www.googletagmanager.com/gtag/js?id=GT-NMCXXG3R',
        array(),
        null,
        false // Dans le <head>
    );

    // 2) Configuration principale avec gestion intelligente du consentement
    $inline_head = "
window.dataLayer = window.dataLayer || [];
function gtag(){ dataLayer.push(arguments); }

// --- 🔧 Pré-consentement immédiat (avant tout traitement CookieYes) ---
gtag('consent', 'default', {
  'ad_storage': 'denied',
  'analytics_storage': 'denied',
  'ad_user_data': 'denied',
  'ad_personalization': 'denied',
  'functionality_storage': 'granted',
  'security_storage': 'granted',
  'wait_for_update': 500
});
console.log('🟡 Pré-consentement Google appliqué (denied par défaut)');

// Variables de contrôle essentielles
window.lm_ga4 = {
    initialized: false,
    pageview_sent: false,
    consent_granted: false,
    measurement_id: 'GT-NMCXXG3R'
};


/**
 * Détecte le consentement CookieYes existant
 * Compatible avec v3.x qui stocke dans un cookie 'cookieyes-consent'
 */
function lm_get_cookieyes_consent() {
    try {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const trimmed = cookie.trim();
            if (trimmed.startsWith('cookieyes-consent=')) {
                const value = decodeURIComponent(trimmed.substring(18));
                const data = JSON.parse(value);
                console.log('📦 Consentement CookieYes trouvé:', data);
                return {
                    analytics: data.categories?.analytics === 'yes',
                    ads: data.categories?.advertisement === 'yes'
                };
            }
        }
    } catch(e) {
        console.log('ℹ️ Pas de consentement CookieYes existant');
    }
    return null;
}

// Vérifier le consentement existant
const existingConsent = lm_get_cookieyes_consent();

// Configuration du Consent Mode selon l'état actuel
if (existingConsent && existingConsent.analytics) {
    // Visiteur récurrent avec consentement = granted immédiat
    gtag('consent', 'default', {
        'ad_storage': existingConsent.ads ? 'granted' : 'denied',
        'analytics_storage': 'granted',
        'ad_user_data': existingConsent.ads ? 'granted' : 'denied',
        'ad_personalization': existingConsent.ads ? 'granted' : 'denied',
        'functionality_storage': 'granted',
        'security_storage': 'granted'
    });
    window.lm_ga4.consent_granted = true;
    console.log('✅ Visiteur récurrent - Analytics autorisé');
} else {
    // Nouveau visiteur ou refus = mode restreint
    gtag('consent', 'default', {
        'ad_storage': 'denied',
        'analytics_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'functionality_storage': 'granted',
        'security_storage': 'granted',
        'wait_for_update': 2500  // Attendre 2.5s la bannière
    });
    console.log('🔒 Mode restreint - En attente du consentement');
}

// Initialiser GA4
gtag('js', new Date());

// Configuration avec paramètres optimisés
gtag('config', window.lm_ga4.measurement_id, {
    'anonymize_ip': true,
    'send_page_view': true,  // Envoyé seulement si consent granted
    'page_location': window.location.href,
    'page_title': document.title,
    'page_referrer': document.referrer,
    'cookie_flags': 'SameSite=None;Secure'  // Pour compatibilité cross-domain
});

window.lm_ga4.initialized = true;

// Marquer le page_view comme envoyé si le consentement était déjà accordé
if (window.lm_ga4.consent_granted) {
    window.lm_ga4.pageview_sent = true;
    console.log('📊 Page view envoyée (consentement préexistant)');
} else {
    console.log('⏳ Page view en attente du consentement');
}
";

    wp_add_inline_script('lm-gtag', $inline_head, 'after');

    // 3) Bridge CookieYes → GA4 avec récupération intelligente
    wp_register_script('lm-ga4-bridge', '', array(), null, true);

    $inline_footer = "
/**
 * Mise à jour du consentement et récupération du page_view
 * Point critique pour ne pas perdre d'audience
 */
function lm_handle_consent_update(consent) {
    if (typeof gtag !== 'function' || !consent) {
        console.warn('⚠️ Mise à jour du consentement impossible');
        return;
    }

    // Parser le format CookieYes v3.x
    const accepted = Array.isArray(consent.accepted) ? consent.accepted : [];
    const rejected = Array.isArray(consent.rejected) ? consent.rejected : [];

    const analyticsGranted = accepted.includes('analytics');
    const adsGranted = accepted.includes('advertisement');

    // Sauvegarder l'état précédent
    const wasGrantedBefore = window.lm_ga4.consent_granted;
    const pageviewAlreadySent = window.lm_ga4.pageview_sent;

    // Mettre à jour le Consent Mode
    gtag('consent', 'update', {
        'analytics_storage': analyticsGranted ? 'granted' : 'denied',
        'ad_storage': adsGranted ? 'granted' : 'denied',
        'ad_user_data': adsGranted ? 'granted' : 'denied',
        'ad_personalization': adsGranted ? 'granted' : 'denied'
    });

    // 🔥 CRITIQUE : Récupérer le page_view perdu
    if (analyticsGranted && !wasGrantedBefore && !pageviewAlreadySent) {
        console.log('🚀 Récupération du page_view après acceptation');

        // Envoyer le page_view qui avait été bloqué
        gtag('event', 'page_view', {
            page_location: window.location.href,
            page_title: document.title,
            page_referrer: document.referrer,
            engagement_time_msec: Math.round(performance.now()),
            // Marqueur custom pour analyse
            consent_timing: 'delayed'
        });

        window.lm_ga4.pageview_sent = true;

        // Bonus : envoyer un événement de consentement pour tracking
        gtag('event', 'consent_granted', {
            event_category: 'engagement',
            event_label: 'cookieyes',
            value: Math.round(performance.now() / 1000)  // Temps avant acceptation
        });
    }

    // Mettre à jour l'état
    window.lm_ga4.consent_granted = analyticsGranted;

    console.log('✅ Consentement mis à jour:', {
        analytics: analyticsGranted ? 'granted' : 'denied',
        ads: adsGranted ? 'granted' : 'denied',
        pageview_recovered: !wasGrantedBefore && analyticsGranted && !pageviewAlreadySent
    });
}

// Écouter TOUS les événements CookieYes possibles
const cookieyesEvents = [
    'cookieyes_consent_update',     // Event principal v3.x
    'cli_consent_update',           // Compatibilité ancienne version
    'cookieyes_event_updated',      // Event alternatif
    'cookieYes_consent_update'      // Variante de casse
];

cookieyesEvents.forEach(eventName => {
    document.addEventListener(eventName, function(e) {

		console.log(`📡 Event CookieYes détecté: \${eventName}`, e.detail);
        lm_handle_consent_update(e.detail);
    }, { once: false, passive: true });
});

// Mécanisme de sécurité : vérification après délai
setTimeout(function() {
    if (!window.lm_ga4.pageview_sent && window.lm_ga4.initialized) {
        const currentConsent = lm_get_cookieyes_consent();
        if (currentConsent && currentConsent.analytics) {
            console.warn('⚠️ Récupération forcée du page_view (timeout)');
            gtag('event', 'page_view', {
                page_location: window.location.href,
                page_title: document.title,
                consent_timing: 'timeout_recovery'
            });
            window.lm_ga4.pageview_sent = true;
        } else {
            console.log('🔒 Pas de consentement après timeout - page_view non envoyée');
        }
    }
}, 5000);

// Log de debug en développement
if (window.location.hostname === 'localhost' || window.location.search.includes('debug=ga4')) {
    window.lm_ga4_debug = function() {
        console.table({
            'GA4 Initialisé': window.lm_ga4.initialized,
            'Page View Envoyée': window.lm_ga4.pageview_sent,
            'Consentement Accordé': window.lm_ga4.consent_granted,
            'Measurement ID': window.lm_ga4.measurement_id
        });
    };
    console.log('💡 Debug GA4 : tapez lm_ga4_debug() dans la console');
}

console.log('✅ Bridge CookieYes → GA4 opérationnel (mode récupération actif)');
";

    wp_add_inline_script('lm-ga4-bridge', $inline_footer, 'after');
    wp_enqueue_script('lm-ga4-bridge');
}

add_action('wp_enqueue_scripts', 'lm_enqueue_ga4_head', 20);

// === Google AdSense domain verification ===
add_action('wp_head', function() {
  ?>
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7727728964402200"
    crossorigin="anonymous"></script>
  <?php
});

/**
 * 🧩 Enrichit les sitemaps Yoast SEO avec toutes les images pertinentes
 * Compatible avec : post, test, promotion, page, marque + categorie_test
 * Gère : featured image, contenu Gutenberg, galeries ACF, champs HTML et logos
 */
add_filter('wpseo_sitemap_urlimages', function ($images, $post_id) {

    $post_type = get_post_type($post_id);
    $allowed_types = ['post', 'test', 'promotion', 'page', 'marque'];
    if (!in_array($post_type, $allowed_types)) {
        return $images;
    }

    // --- 🖼️ 1. Image à la une ---
    $thumb_url = get_the_post_thumbnail_url($post_id, 'full');
    if ($thumb_url) {
        $images[] = ['src' => esc_url($thumb_url)];
    }

    // --- 📰 2. Contenu Gutenberg / GenerateBlocks / ACF ---
    $post = get_post($post_id);
    if ($post && !empty($post->post_content)) {

        // ⚙️ Forcer le contexte global
        global $post;
        $GLOBALS['post'] = $post;
        setup_postdata($post);

        // 🔧 Rendre le contenu complet
        $raw_content = get_post_field('post_content', $post_id);
        $content = do_blocks($raw_content);
        $content = apply_filters('the_content', $content);

        // 🧠 Recherche des images dans <img>, <picture> et <source>
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches_img);
        preg_match_all('/<source[^>]+srcset=["\']([^"\']+)["\']/i', $content, $matches_source);

        $sources = array_merge($matches_img[1], $matches_source[1]);

        if (!empty($sources)) {
            foreach ($sources as $src) {
                // Gérer les srcset multiples
                if (strpos($src, ',') !== false) {
                    $src = trim(explode(' ', explode(',', $src)[0])[0]);
                } else {
                    $src = trim(explode(' ', $src)[0]);
                }

                // Nettoyage de l'URL
                $src = strtok($src, '?');

                // Ignorer les miniatures WordPress
                if (preg_match('/-\d{2,4}x\d{2,4}\.(jpg|jpeg|png|webp)$/i', $src)) continue;
                if (strpos($src, 'logo') !== false || strpos($src, '.svg') !== false) continue;

                // Corriger les URL relatives
                if (strpos($src, 'http') !== 0) {
                    $src = home_url($src);
                }

                $images[] = ['src' => esc_url($src)];
            }
        }

        // Nettoyer le contexte global
        wp_reset_postdata();
    }

    // --- 🧪 3. Champs ACF (tests, marques, etc.) ---
    if (function_exists('get_field')) {

        // Galerie produit ACF
        $gallery = get_field('gallerie_produit', $post_id, false);
        if (is_array($gallery)) {
            foreach ($gallery as $image_id) {
                $src = wp_get_attachment_url($image_id);
                if ($src) $images[] = ['src' => esc_url($src)];
            }
        }

        // Champs HTML avec images
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

        // Logo des marques
        if ($post_type === 'marque') {
            $logo = get_field('logo_marque', $post_id);
            if ($logo && isset($logo['url'])) {
                $images[] = ['src' => esc_url($logo['url'])];
            }
        }
    }

    // --- 🧹 Nettoyage des doublons ---
    $unique = [];
    $filtered = [];
    foreach ($images as $img) {
        if (!in_array($img['src'], $unique)) {
            $unique[] = $img['src'];
            $filtered[] = $img;
        }
    }

    // --- ⚙️ Limite à 10 images max ---
    return array_slice($filtered, 0, 10);

}, 10, 2);


/**
 * 🧩 Enrichit aussi les sitemaps Yoast pour les taxonomies (catégories de tests)
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


/**
 * LMAL – Neutraliser WP Rocket sur la lightbox (thème)
 * Objectif: empêcher Delay/Defer/Minify/Combine de casser PhotoSwipe + payload.
 */

add_action('init', function () {

  // 1) Exclure du "Delay JS"
  add_filter('rocket_delay_js_exclusions', function ($excluded) {
    $excluded = is_array($excluded) ? $excluded : [];
    return array_merge($excluded, [
      'labomaison-article-lightbox',
      'lmal-lightbox.js',
      'lmal-bootstrap.js',
      'photoswipe',
      'photoswipe-lightbox',
      'PhotoSwipe',
      'PhotoSwipeLightbox',
      'labomaisonArticleLightbox',
      'window.labomaisonArticleLightbox',
    ]);
  });

  // 2) Exclure de l’optimisation JS (minify/combine/defer selon config Rocket)
  add_filter('rocket_exclude_js', function ($excluded) {
    $excluded = is_array($excluded) ? $excluded : [];
    return array_merge($excluded, [
      'labomaison-article-lightbox/assets/js/lmal-lightbox.js',
      'labomaison-article-lightbox/assets/js/lmal-bootstrap.js',
      'labomaison-article-lightbox/assets/vendor/photoswipe/photoswipe.umd.min.js',
      'labomaison-article-lightbox/assets/vendor/photoswipe/photoswipe-lightbox.umd.min.js',
      'photoswipe.umd.min.js',
      'photoswipe-lightbox.umd.min.js',
      'labomaison-article-lightbox/assets/',
    ]);
  });

  // 3) Exclure le CSS du plugin des optimisations agressives (RUC/unused CSS)
  add_filter('rocket_exclude_css', function ($excluded) {
    $excluded = is_array($excluded) ? $excluded : [];
    $excluded[] = 'labomaison-article-lightbox/assets/css/lmal-lightbox.css';
    return $excluded;
  });

  // 4) Si "Remove Unused CSS" est actif, on force l’inclusion de notre CSS
  // (WP Rocket a plusieurs filtres selon versions, on couvre le plus courant)
  add_filter('rocket_rucss_inline_content_exclusions', function ($excluded) {
    $excluded = is_array($excluded) ? $excluded : [];
    return array_merge($excluded, [
      '.pswp.lmal-pswp',
      '.lmal-thumbs',
      '.lmal-thumb',
    ]);
  });

}, 1);

/**
 * LM Guard-Fou (Child Theme) — FIXED
 * - 410: ?w=, _load_more/_page/load_more (même encodés), /contents/, /shop/
 * - 301: /page/N/ au-delà du max -> page 1 de l’archive
 * - 410: marques "vides" (CPT marque) -> 410 (fallback même si plugin cassé)
 *
 * FIX CRITIQUE:
 * - SUPPRIME parse_request (trop tôt => casse admin/editor/metabox catégories)
 * - Renforce le garde WP_ADMIN
 */

/* =========================
   Helpers de réponse
========================= */
if (!function_exists('lm_guard_send_410')) {
    function lm_guard_send_410(string $reason): void {
        header('X-LM-Guard: 410; reason=' . $reason);
        header('X-Robots-Tag: noindex, nofollow', true);
        if (function_exists('nocache_headers')) {
            nocache_headers();
        } else {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true);
            header('Pragma: no-cache', true);
        }
        if (function_exists('status_header')) {
            status_header(410);
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 410 Gone', true, 410);
        }
        header('Content-Type: text/plain; charset=utf-8', true);
        echo "410 Gone";
        exit;
    }
}

if (!function_exists('lm_guard_send_301')) {
    function lm_guard_send_301(string $to, string $reason): void {
        header('X-LM-Guard: 301; reason=' . $reason);
        header('X-Robots-Tag: noindex, nofollow', true);
        if (function_exists('nocache_headers')) {
            nocache_headers();
        }
        wp_safe_redirect($to, 301);
        exit;
    }
}

/* =========================
   1) Garde-fou FRONT only : coupe ASAP
   - utile pour ?w= / _load_more / _page même si encodé
   - IMPORTANT: NE PAS accrocher sur parse_request (casse admin)
========================= */
if (!function_exists('lm_guard_early_410')) {
    function lm_guard_early_410(): void {
        // HARD BLOCK admin/editor
        if (defined('WP_ADMIN') && WP_ADMIN) return;
        if (is_admin()) return;

        if (defined('WP_CLI') && WP_CLI) return;
        if (defined('DOING_CRON') && DOING_CRON) return;
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (defined('REST_REQUEST') && REST_REQUEST) return;

        $raw_uri     = $_SERVER['REQUEST_URI'] ?? '/';
        $decoded_uri = rawurldecode($raw_uri);

        $parsed = @parse_url($decoded_uri);
        $path   = isset($parsed['path']) ? (string) $parsed['path'] : $decoded_uri;
        $query  = isset($parsed['query']) ? (string) $parsed['query'] : ($_SERVER['QUERY_STRING'] ?? '');

        // 410: ?w= (normal / encodé)
        if (
            (is_string($query) && preg_match('/(^|[&])w=([^&]+)/i', $query)) ||
            preg_match('/[?&]w=([^&]+)/i', $raw_uri) ||
            preg_match('/[?&]w=([^&]+)/i', $decoded_uri)
        ) {
            lm_guard_send_410('w_param');
        }

        // 410: _load_more / load_more / _page (normal / encodé)
        if (
            (is_string($query) && preg_match('/(^|[&])(_load_more|load_more|_page)=/i', $query)) ||
            preg_match('/(_load_more|load_more|_page)=/i', $raw_uri) ||
            preg_match('/(_load_more|load_more|_page)=/i', $decoded_uri)
        ) {
            lm_guard_send_410('load_more_param_410');
        }

        // 410: /contents/*
        if (strpos($path, '/contents/') === 0) {
            lm_guard_send_410('contents_prefix');
        }

        // 410: /shop/*
        if (strpos($path, '/shop/') === 0) {
            lm_guard_send_410('shop_prefix');
        }
    }
}
// FIX: plus de parse_request
add_action('template_redirect', 'lm_guard_early_410', 0);

/* =========================
   2) 301: /page/N/ au-delà du max pages -> page 1 de l’archive
========================= */
if (!function_exists('lm_guard_redirect_over_max_pagination')) {
    function lm_guard_redirect_over_max_pagination(): void {
        if (defined('WP_ADMIN') && WP_ADMIN) return;
        if (is_admin()) return;
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (defined('REST_REQUEST') && REST_REQUEST) return;

        if (!(is_archive() || is_home() || is_search())) {
            return;
        }

        $paged = (int) get_query_var('paged');
        if ($paged <= 1) {
            return;
        }

        global $wp_query;
        $max = isset($wp_query->max_num_pages) ? (int) $wp_query->max_num_pages : 0;

        if ($max <= 0) {
            return;
        }

        if ($paged > $max) {
            $target = get_pagenum_link(1);
            if (!$target) {
                $target = home_url('/');
            }
            lm_guard_send_301($target, 'paged_over_max');
        }
    }
}
add_action('template_redirect', 'lm_guard_redirect_over_max_pagination', 1);

/* =========================
   3) 410: Marques "vides" (CPT marque)
========================= */
if (!function_exists('lm_guard_brand_unused_410')) {
    function lm_guard_brand_unused_410(): void {
        if (defined('WP_ADMIN') && WP_ADMIN) return;
        if (is_admin()) return;
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (defined('REST_REQUEST') && REST_REQUEST) return;

        $marque_post = null;

        if (is_singular('marque')) {
            $marque_post = get_queried_object();
        } else {
            $raw_uri     = $_SERVER['REQUEST_URI'] ?? '/';
            $decoded_uri = rawurldecode($raw_uri);
            $path        = trim((string) parse_url($decoded_uri, PHP_URL_PATH), '/');

            if ($path !== 'marques' && preg_match('#^marques/([^/]+)/?$#i', $path, $m)) {
                $slug = sanitize_title($m[1]);
                $candidate = get_page_by_path($slug, OBJECT, 'marque');
                if ($candidate && !empty($candidate->ID)) {
                    $marque_post = $candidate;
                }
            }
        }

        if (!$marque_post || empty($marque_post->ID)) {
            return;
        }

        $marque_id = (int) $marque_post->ID;
        $meta_like = '"' . $marque_id . '"';

        $q_tests = new WP_Query([
            'post_type'      => 'test',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
            'fields'         => 'ids',
            'meta_query'     => [[
                'key'     => 'marque',
                'value'   => $meta_like,
                'compare' => 'LIKE',
            ]],
        ]);

        $q_posts = new WP_Query([
            'post_type'      => 'post',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
            'fields'         => 'ids',
            'meta_query'     => [[
                'key'     => 'marque',
                'value'   => $meta_like,
                'compare' => 'LIKE',
            ]],
        ]);

        $has_any = ($q_tests->have_posts() || $q_posts->have_posts());
        wp_reset_postdata();

        if (!$has_any) {
            lm_guard_send_410('brand_unused_410');
        }
    }
}
add_action('template_redirect', 'lm_guard_brand_unused_410', 2);

/**
 * C2S Widget Shortcode
 * Usage:
 *  - [c2s_widget wid="ox9Vv6+Ab9o="]                      -> Méthode 2 (div + wmain.js) (recommandée)
 *  - [c2s_widget wid="ox9Vv6+Ab9o=" method="script"]       -> Méthode 1 (script affichage.js?wid=...)
 */

add_action('init', function () {

    // Ajoute async+defer aux scripts de ce widget (optionnel mais demandé)
    add_filter('script_loader_tag', function ($tag, $handle, $src) {
        if (strpos($handle, 'c2s-') !== 0) {
            return $tag;
        }

        // Si déjà async/defer, ne pas dupliquer
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
            'method' => 'div',   // 'div' (méthode 2) ou 'script' (méthode 1)
            'class'  => 'c2s-widget',
        ], $atts, 'c2s_widget');

        $wid = sanitize_text_field($atts['wid']);
        if (empty($wid)) {
            return '<!-- c2s_widget: wid manquant -->';
        }

        $method = strtolower(sanitize_text_field($atts['method']));

        // Méthode 2 : DIV + wmain.js (recommandée)
        if ($method !== 'script') {
            $handle = 'c2s-wmain';
            $src    = 'https://lbm.clic2shop.com/widget/wmain.js';

            // Charge le script une seule fois, même si plusieurs shortcodes sur la page
            if (!wp_script_is($handle, 'enqueued')) {
                wp_enqueue_script($handle, $src, [], null, true);
            }

            return sprintf(
                '<div class="%s" data-c2s-wid="%s"></div>',
                esc_attr($atts['class']),
                esc_attr($wid)
            );
        }

        // Méthode 1 : Script direct affichage.js?wid=...
        // On encode le wid comme dans leur exemple ( + => %2B, = => %3D )
        $wid_enc = rawurlencode($wid);
        $src     = 'https://lbm.clic2shop.com/widget/affichage.js?wid=' . $wid_enc;

        // Handle unique par wid, pour éviter collisions si plusieurs wids différents
        $handle = 'c2s-affichage-' . substr(md5($src), 0, 10);

        if (!wp_script_is($handle, 'enqueued')) {
            wp_enqueue_script($handle, $src, [], null, true);
        }

        // Pas de div requise pour la méthode 1 (selon leur doc)
        return '<!-- c2s_widget: affichage.js chargé -->';
    });
});


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



/**
 * Plugin Name: LaboMaison - Rank Math Schema (ACF + Auto-clean)
 * Description: Variables Rank Math pour ACF + CollectionPage categorie_test + nettoyage Product sur CPT test.
 * Author: LaboMaison
 */

if (!defined('ABSPATH')) exit;

/**
 * ================================
 * 1) Variables Rank Math (%lm_*%)
 * ================================
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

    // Note STRICTE 1..5 (sinon vide)
    $get_note_val = function(int $post_id): string {
        $note_globale = get_post_meta($post_id, 'note_globale', true);
        if ($note_globale === '' || !is_numeric($note_globale)) return '';
        $note = (float) $note_globale;
        if ($note < 1.0 || $note > 5.0) return '';
        // format propre: 4 ou 4.5
        $out = rtrim(rtrim(number_format($note, 1, '.', ''), '0'), '.');
        return $out;
    };

    // Prix STRICT >0 (sinon vide)
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

    // ===== Variables “brutes” =====
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

    // ===== Variables “dérivées” =====
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


/**
 * ==============================================================
 * 2) CollectionPage sur archives categorie_test (LISTE PROPRE)
 * - Pas de Review, pas d’Offer, pas de rating/price dans la liste
 * ==============================================================
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
 * ===================================================================
 * 3) Nettoyage automatique Product sur singles "test"
 * - Supprime review + aggregateRating si note invalide
 * - Supprime offers si prix invalide
 * ===================================================================
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
        // Ajout reviewCount propre
        $product['aggregateRating']['reviewCount'] = '1';
    }
    if (!$has_valid_price) {
        unset($product['offers']);
    }
};

    foreach ($data as $key => $node) {
        if (!is_array($node)) continue;

        // Node direct
        if (isset($node['@type']) && $is_type($node['@type'], 'Product')) {
            $clean_product($data[$key]);
            continue;
        }

        // Graph imbriqué
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

/**
 * Labomaison — Rank Math Breadcrumbs (FULL)
 *
 * Règles :
 * - Univers = terme racine (parent=0) de la taxonomie concernée
 * - ACTU (post) :
 *   - Accueil → Univers → Catégorie → TEST vedette (Marque + Nom) [CLICABLE vers le TEST]
 *   - Fallback si pas de test vedette : Accueil → Univers → Catégorie → Titre (non cliquable)
 * - TESTS :
 *   - UNIQUEMENT si is_singular('test') ou is_post_type_archive('test')
 *   - Accueil → Univers → Catégorie → Tests (cliquable /dernier-test/)
 *   - PAS de titre de test
 * - Catégories :
 *   - Accueil → Univers → Catégorie (courante non cliquable)
 * - Tags / autres taxos :
 *   - Accueil → Terme (courant non cliquable)
 * - Jamais de lien vers la page courante
 *
 * Note importante :
 * Rank Math rend souvent le dernier crumb non cliquable. On force le lien uniquement
 * pour le cas ACTU → TEST vedette.
 */

/* =========================
 * HELPERS
 * ========================= */

function lm_breadcrumb_get_univers(\WP_Term $term): ?\WP_Term {
    while (!empty($term->parent)) {
        $parent = get_term((int) $term->parent, $term->taxonomy);
        if (!$parent || is_wp_error($parent)) return null;
        $term = $parent;
    }
    return $term;
}

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

    // Rank Math primary (fonction)
    if (function_exists('rank_math_get_primary_term')) {
        $primary = rank_math_get_primary_term('category', $post_id);
        if ($primary && !is_wp_error($primary)) return $primary;
    }

    // Fallback : catégorie la plus profonde
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
 * ACTU : produit_vedette pointe vers un CPT test
 * Retourne array:
 * [
 *   'test_id' => int,
 *   'label'   => string, // "Marque Nom"
 *   'url'     => string
 * ]
 * ou null si non applicable.
 *
 * Champs ACF attendus sur le TEST :
 * - marque (peut être WP_Post relationship ou string)
 * - nom    (le "nom modèle" chez vous)
 */
function lm_breadcrumb_get_featured_test_data(int $post_id): ?array {
    if (!function_exists('get_field')) return null;

    $ref = get_field('produit_vedette', $post_id);
    if (empty($ref)) return null;

    // Normalisation ACF
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

    // marque peut être relationship vers CPT "marque" (WP_Post) ou array de WP_Post
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
        // fallback ultra-sûr
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
 * Retourne le terme categorie_test correspondant à une WP category
 * en se basant sur le slug (mapping déjà en place chez vous).
 */
function lm_map_post_category_to_test_term(\WP_Term $cat): ?\WP_Term {
    $t = get_term_by('slug', $cat->slug, 'categorie_test');
    if ($t && !is_wp_error($t) && $t instanceof \WP_Term) {
        return $t;
    }
    return null;
}

/* =========================
 * 1) ITEMS — construit la liste des crumbs
 * ========================= */

add_filter('rank_math/frontend/breadcrumb/items', function (array $crumbs, $class = null): array {
    global $post;

    $out = [];

    // Accueil : on garde Rank Math si présent
    if (!empty($crumbs[0])) {
        $out[] = $crumbs[0];
    } else {
        $out[] = ['Accueil', home_url('/')];
    }

    /* =========================
     * ARCHIVE CATÉGORIE
     * Accueil → Univers → Catégorie
     * ========================= */
    if (is_category()) {
        $cat = get_queried_object();
        if ($cat instanceof \WP_Term) {
            $univers = lm_breadcrumb_get_univers($cat);

            if ($univers instanceof \WP_Term) {
                $out[] = [$univers->name, get_term_link($univers)];
            }

            // Courant non cliquable UNIQUEMENT si différent de l'univers
            if (!$univers || (int) $univers->term_id !== (int) $cat->term_id) {
                $out[] = [$cat->name, ''];
            }
        }
        return $out;
    }

    /* =========================
     * ACTU (post)
     * Accueil → Univers → Catégorie → TEST vedette (Marque + Nom) [cliquable]
     * ========================= */
    if (is_singular('post') && $post instanceof \WP_Post) {

        // Univers + Cat (anti-doublon)
        $cat = lm_breadcrumb_pick_primary_category((int) $post->ID);
       if ($cat instanceof \WP_Term) {

        // 2) Mapping strict vers categorie_test (par slug)
        $test_term = lm_map_post_category_to_test_term($cat);

        // Pas de correspondance ? => on n’ajoute aucun crumb "cat"
        if ($test_term instanceof \WP_Term) {

            // 3) Univers = racine de categorie_test
            $univers = lm_breadcrumb_get_univers($test_term);

            if ($univers instanceof \WP_Term) {
                $out[] = [$univers->name, get_term_link($univers)];
            }

            // 4) Catégorie = terme mappé (categorie_test)
            if (!$univers || (int) $univers->term_id !== (int) $test_term->term_id) {
                $out[] = [$test_term->name, get_term_link($test_term)];
            }
        }
    }

    // TEST vedette
    $featured = lm_breadcrumb_get_featured_test_data((int) $post->ID);
    if (is_array($featured)) {
        $out[] = [$featured['label'], $featured['url']];
        return $out;
    }

        // Si pas de test vedette: on NE RAJOUTE RIEN (on s'arrête à Accueil → Univers → Catégorie)
return $out;
    }

    /* =========================
     * TESTS — UNIQUEMENT ICI
     * Accueil → Univers → Catégorie → Tests (cliquable)
     * PAS de titre de test
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

            // Catégorie cliquable UNIQUEMENT si différente de l'univers
            if (!$univers || (int) $univers->term_id !== (int) $term->term_id) {
                $out[] = [$term->name, get_term_link($term)];
            }
        }


        return $out;
    }

	/* =========================
 * TAXONOMIE categorie_test (pages hub dédiées)
 * Accueil → Niveau 1 → Niveau 2 → ... (dernier non cliquable)
 * ========================= */
if (is_tax('categorie_test')) {
    $term = get_queried_object();

    if ($term instanceof \WP_Term) {

        $chain = [];
        $current = $term;

        // Remonte toute la hiérarchie (racine → ... → courant)
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

        // Injecte la chaîne : tous cliquables sauf le dernier
        $last_index = count($chain) - 1;

        foreach ($chain as $i => $t) {
            if ($i === $last_index) {
                $out[] = [$t->name, '']; // page courante
            } else {
                $link = get_term_link($t);
                $out[] = [$t->name, is_wp_error($link) ? '' : $link];
            }
        }
    }

    return $out;
}

    /* =========================
     * TAGS / AUTRES TAXOS
     * Accueil → Terme (courant non cliquable)
     * ========================= */
    if (is_tag() || is_tax()) {
        $term = get_queried_object();
        if ($term instanceof \WP_Term) {
            $out[] = [$term->name, ''];
        }
        return $out;
    }

    // Default : Rank Math gère
    return $crumbs;

}, 10, 2);

/* =========================
 * 2) HTML — force le dernier crumb cliquable UNIQUEMENT pour ACTU→TEST vedette
 * ========================= */

add_filter('rank_math/frontend/breadcrumb/html', function ($html, $crumbs, $class) {

    if (!(is_singular('post') || is_singular('test')) || !is_array($crumbs) || count($crumbs) < 2) {
    return $html;
}

    $last = end($crumbs);
    if (!is_array($last) || empty($last[0]) || empty($last[1])) {
        return $html; // dernier crumb sans URL => on ne force pas
    }

    $label = (string) $last[0];
    $url   = (string) $last[1];

    // Ne jamais lier vers la page courante
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

    // Remplace <span class="last">LABEL</span> par un lien (1 fois)
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

    // Fallback : remplace la dernière occurrence du label par un lien
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

/**
 * LM Popularity — Anti-cache tracker (WP Rocket safe) + weekly window (7 jours glissants)
 * Metas:
 * - lm_views_daily : array('YYYYMMDD' => int)  (stockage journalier)
 * - post_views_7d  : int                      (somme glissante 7 jours)
 */

add_action('wp_ajax_nopriv_lm_track_view', 'lm_track_view');
add_action('wp_ajax_lm_track_view', 'lm_track_view');

function lm_track_view() {
    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    if (!$post_id) {
        wp_send_json_error(['message' => 'missing_post_id']);
    }

    $pt = get_post_type($post_id);
    if (!in_array($pt, ['post', 'test'], true)) {
        wp_send_json_error(['message' => 'invalid_post_type']);
    }

    // Ne pas compter admin/loggés (optionnel)
    if (is_user_logged_in()) {
        wp_send_json_success(['skipped' => true]);
    }

    $day_key = wp_date('Ymd'); // timezone WP

    $daily = get_post_meta($post_id, 'lm_views_daily', true);
    if (!is_array($daily)) $daily = [];

    $daily[$day_key] = isset($daily[$day_key]) ? ((int)$daily[$day_key] + 1) : 1;

    // Garde 35 jours d’historique
    $cutoff = (int) wp_date('Ymd', strtotime('-35 days'));
    foreach ($daily as $k => $v) {
        if ((int)$k < $cutoff) unset($daily[$k]);
    }

    update_post_meta($post_id, 'lm_views_daily', $daily);

    // 7 jours glissants
    $sum7 = 0;
    for ($i = 0; $i < 7; $i++) {
        $k = wp_date('Ymd', strtotime("-{$i} days"));
        $sum7 += isset($daily[$k]) ? (int)$daily[$k] : 0;
    }
    update_post_meta($post_id, 'post_views_7d', $sum7);

    wp_send_json_success(['post_id' => $post_id, 'post_views_7d' => $sum7]);
}

add_action('wp_enqueue_scripts', function () {
    if (!is_singular(['post', 'test'])) return;
    if (is_admin()) return;
    if (is_user_logged_in()) return;

    wp_register_script('lm-views-tracker', '', [], null, true);

    wp_add_inline_script('lm-views-tracker', 'window.LM_VIEWS=' . wp_json_encode([
        'ajaxurl' => admin_url('admin-ajax.php'),
        'post_id' => (int) get_queried_object_id(),
    ]) . ';', 'before');

    wp_add_inline_script('lm-views-tracker', <<<JS
document.addEventListener('DOMContentLoaded', function () {
  try {
    if (!window.LM_VIEWS || !LM_VIEWS.post_id) return;

    // 1 hit max / jour / session
    var day = (new Date()).toISOString().slice(0,10);
    var key = 'lm_viewed_' + LM_VIEWS.post_id + '_' + day;
    if (sessionStorage.getItem(key)) return;
    sessionStorage.setItem(key, '1');

    var form = new FormData();
    form.append('action', 'lm_track_view');
    form.append('post_id', LM_VIEWS.post_id);

    if (navigator.sendBeacon) {
      navigator.sendBeacon(LM_VIEWS.ajaxurl, form);
    } else {
      fetch(LM_VIEWS.ajaxurl, { method: 'POST', body: form, credentials: 'same-origin' });
    }
  } catch(e) {}
});
JS, 'after');

    wp_enqueue_script('lm-views-tracker');
}, 20);

/**
 * WPGB Grid 30 — tri sur post_views_7d
 * IMPORTANT: pas de meta_query "EXISTS" (sinon grid vide au début)
 * Fallback: si pas de post_views_7d encore, tri par modified.
 */
add_filter('wp_grid_builder/grid/query_args', function ($query_args, $grid_id) {
    if (30 === (int) $grid_id) {
        $query_args['meta_key'] = 'post_views_7d';
        $query_args['orderby']  = [
            'meta_value_num' => 'DESC',
            'modified'       => 'DESC',
        ];
        $query_args['order'] = 'DESC';

        // On ne filtre pas par existence du meta => jamais vide
        unset($query_args['date_query']);
    }
    return $query_args;
}, 50, 2);


/**
 * Anti-CLS — Clic2Shop widget
 * Injecte un JS léger qui verrouille la hauteur pendant le rendu,
 * puis libère quand les offres sont présentes.
 */
add_action('wp_enqueue_scripts', function () {

    // Charge uniquement sur le front
    if (is_admin()) return;

    // Optionnel : restreindre à certaines pages / taxonomies si vous voulez.
    // Exemple : uniquement sur la taxonomie "forfait-box-internet"
    // if (!is_tax('category', 'forfait-box-internet')) return;

    $js = <<<JS
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.c2s-widget').forEach(function (widget) {
    widget.classList.add('is-loading');

    var target = widget.querySelector('#c2s_result_container') || widget;

    var markReady = function () {
      widget.classList.remove('is-loading');
      widget.classList.add('is-ready');
    };

    var obs = new MutationObserver(function () {
      var offers = widget.querySelectorAll('.m-offer');
      if (offers.length >= 3) {
        markReady();
        obs.disconnect();
      }
    });

    obs.observe(target, { childList: true, subtree: true });

    setTimeout(function () {
      try { obs.disconnect(); } catch(e) {}
      markReady();
    }, 7000);
  });
});
JS;

    // Injecte sans dépendances (jQuery inutile)
    wp_register_script('lm-c2s-anti-cls', '', [], null, true);
    wp_enqueue_script('lm-c2s-anti-cls');
    wp_add_inline_script('lm-c2s-anti-cls', $js);

}, 20);

/**
 * Force le trailing slash (/) sur les URLs "propres" si la version sans slash renvoie 200.
 * Objectif SEO: éviter le duplicate content (/slug et /slug/ en 200).
 *
 * IMPORTANT:
 * - Après ajout: purge cache (Kinsta + Cloudflare) sinon tu verras encore des 200.
 */
add_action('template_redirect', function () {

    // Front only + sécurité
    if (is_admin()) return;
    if (defined('WP_CLI') && WP_CLI) return;
    if (defined('DOING_AJAX') && DOING_AJAX) return;
    if (defined('DOING_CRON') && DOING_CRON) return;
    if (defined('REST_REQUEST') && REST_REQUEST) return;

    // Ne gérer que GET/HEAD (évite d'impacter POST)
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, ['GET', 'HEAD'], true)) return;

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if ($uri === '/' || $uri === '') return;

    $parts = parse_url($uri);
    $path  = $parts['path'] ?? $uri;
    $query = $parts['query'] ?? '';

    // Ignore endpoints techniques
    $skip_prefixes = [
        '/wp-json',
        '/wp-admin',
        '/wp-login.php',
        '/xmlrpc.php',
    ];
    foreach ($skip_prefixes as $pfx) {
        if (strpos($path, $pfx) === 0) return;
    }

    // Ignore fichiers (extensions) + sitemaps/robots
    if (preg_match('#\.[a-z0-9]{1,6}$#i', $path)) return; // ex: .png .js .xml ...
    if (preg_match('#/(robots\.txt|sitemap\.xml|sitemap_index\.xml)$#i', $path)) return;

    // Si déjà avec slash => ok
    if (substr($path, -1) === '/') return;

    // Cible: ajoute slash + conserve query string
    $target = $path . '/';
    if ($query !== '') {
        $target .= '?' . $query;
    }

    // 301
    wp_safe_redirect($target, 301);
    exit;

}, 0);

/**
 * Rank Math — Breadcrumb Author:
 * Accueil > La rédaction > {Auteur} > Page X
 */
add_filter('rank_math/frontend/breadcrumb/items', function($crumbs, $class) {

    if (!is_author()) {
        return $crumbs;
    }

    $author    = get_queried_object();
    $author_id = isset($author->ID) ? (int) $author->ID : 0;
    $name      = $author ? $author->display_name : '';
    $paged     = max(1, (int) get_query_var('paged'));

    // Construire des crumbs AU FORMAT RANK MATH: [label, url]
    $new = array();

    $new[] = array('Accueil', home_url('/'));
    $new[] = array('La rédaction', home_url('/la-redaction/'));

    // Auteur
    if ($author_id && $name) {
        $new[] = array($name, get_author_posts_url($author_id));
    }

    // Pagination (dernier crumb sans URL)
    if ($paged > 1) {
        $new[] = array('Page ' . $paged, '');
    }

    return $new;

}, 10, 2);

/**
 * Rank Math — Nettoyage sitemap (règle unique) — LM-HTTP-002 (+ LM-PARAM guardrails)
 *
 * Objectif : ne publier dans les sitemaps que des URLs “propres” et stables
 * (sans paramètres, sans pagination en path, sans URL externe).
 *
 * NOTE PERF : pas de HEAD ici (évite self-DDOS / TTFB sitemap).
 * Les non-200 se corrigent via LM-HTTP (301/410) + hygiène sitemap, pas via runtime checks.
 *
 * Placement : MU-plugin recommandé.
 */
add_filter('rank_math/sitemap/entry', function ($entry, $type, $object) {

    if (empty($entry['loc'])) {
        return $entry;
    }

    $url = (string) $entry['loc'];

    // 0) Retire les fragments (#...) (inutile dans un sitemap)
    $url = preg_replace('/#.*/', '', $url);

    // 0bis) URL valide (http/https) + host autorisé
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

    // Autoriser home_url() et site_url() (utile si divergence WPML / settings)
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

    // 1) Query strings OUT (zéro "?" dans les sitemaps)
    if (strpos($url, '?') !== false) {
        return false;
    }

    // 2) Pagination en path OUT
    if (preg_match('#/page/\d+/?$#', $url)) {
        return false;
    }

    // OK
    $entry['loc'] = $url;
    return $entry;

}, 99, 3);

/**
 * LM-CAN-001 — Rank Math Canonical Normalizer (Phase 1: Pagination /page/N/)
 *
 * Règle (validée) :
 * - Les pages paginées (/page/N/) doivent être auto-référentes (canonical = elles-mêmes),
 *   pas canonisées vers la page 1.
 *
 * Scope : correction ciblée sur la famille #1 uniquement.
 * Rollback : supprimer ce fichier MU-plugin.
 */

add_filter('rank_math/frontend/canonical', function ($canonical) {

    // Sécurité: uniquement front, canonical string
    if (!is_string($canonical) || $canonical === '') {
        return $canonical;
    }

    // Si la requête actuelle est une pagination WordPress (page 2+)
    // is_paged() = true pour archives, home, catégories, tags, auteurs, etc.
    if (!is_paged()) {
        return $canonical;
    }

    /**
     * Canonical auto-référent = URL actuelle (sans #, sans query),
     * normalisée via home_url() + trailingslashit.
     */
    $current = (function_exists('wp_get_canonical_url') ? wp_get_canonical_url() : '');

    // Fallback robuste si wp_get_canonical_url() ne renvoie rien
    if (!$current) {
        $scheme = is_ssl() ? 'https' : 'http';
        $host   = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $path   = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $current = $scheme . '://' . $host . $path;
    }

    // Nettoyage minimal
    $current = preg_replace('/#.*/', '', $current);
    $current = preg_replace('/\?.*/', '', $current);

    // Normalisation trailing slash
    $current = trailingslashit($current);

    // Si c’est bien une pagination en path (/page/N/), on force le canonical
    if (preg_match('#/page/\d+/?$#', $current)) {
        return $current;
    }

    // Sinon on ne touche pas (scope strict)
    return $canonical;

}, 99);

/**
 * LM-CAN-001 — Rank Math Canonical Normalizer (Phase 2: Tracking params)
 *
 * Règles :
 * - Si l’URL courante contient des paramètres de tracking, le canonical doit pointer vers
 *   l’URL normalisée SANS query string.
 * - Scope strict : on ne touche pas aux paramètres fonctionnels inconnus (tri/filtres).
 *
 * Rollback : supprimer ce fichier MU-plugin.
 */

add_filter('rank_math/frontend/canonical', function ($canonical) {

    if (!is_string($canonical) || $canonical === '') {
        return $canonical;
    }

    // Canonical courant (RankMath). On préfère le recalcul sur l’URL réelle.
    $current = (function_exists('wp_get_canonical_url') ? wp_get_canonical_url() : '');

    if (!$current) {
        $scheme = is_ssl() ? 'https' : 'http';
        $host   = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $path   = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $current = $scheme . '://' . $host . $path;
    }

    // Nettoyage minimal
    $current = preg_replace('/#.*/', '', $current);

    $parts = wp_parse_url($current);
    if (empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])) {
        return $canonical;
    }

    // Guardrail : uniquement host du site (évite injections / host weird)
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

    // Pas de query => rien à faire
    if (empty($parts['query'])) {
        return $canonical;
    }

    // Parse query params
    parse_str($parts['query'], $query);

    if (empty($query) || !is_array($query)) {
        return $canonical;
    }

    // Détecteurs tracking : préfixes + clés exactes
    $tracking_prefixes = ['utm_'];
    $tracking_keys     = [
        'gclid', 'fbclid', 'msclkid',
        'gbraid', 'wbraid', // Google variants
        'yclid', 'dclid',   // autres click IDs fréquents
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

    // Si pas de tracking détecté : on ne touche pas (scope strict)
    if (!$has_tracking) {
        return $canonical;
    }

    // Canonical cible = URL sans query, normalisée slash final
    $target = $parts['scheme'] . '://' . $parts['host'] . $parts['path'];
    $target = preg_replace('#/+$#', '/', $target);
    $target = trailingslashit($target);

    return $target;

}, 99);

/**
 * LM-CAN-001 — Rank Math Canonical Normalizer (Phase 3: Archives Marques)
 *
 * Règle :
 * - Toutes les URLs sous /marques/ (y compris noindex) doivent être auto-référentes.
 *
 * Scope : uniquement le répertoire /marques/ (pattern/template), pas d'autres archives.
 * Rollback : supprimer ce fichier MU-plugin.
 */

add_filter('rank_math/frontend/canonical', function ($canonical) {

    if (!is_string($canonical) || $canonical === '') {
        return $canonical;
    }

    // Détection pattern "/marques/" (robuste même si la taxonomie s'appelle autrement)
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    if ($uri === '' || stripos($uri, '/marques/') !== 0) {
        return $canonical;
    }

    // Canonical auto-référent = URL actuelle sans query/fragment, normalisée slash final
    $current = (function_exists('wp_get_canonical_url') ? wp_get_canonical_url() : '');

    if (!$current) {
        $scheme = is_ssl() ? 'https' : 'http';
        $host   = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $current = $scheme . '://' . $host . $uri;
    }

    $current = preg_replace('/#.*/', '', $current);
    $current = preg_replace('/\?.*/', '', $current);

    // Guardrail host
    $allowed_hosts = array_filter(array_unique([
        wp_parse_url(home_url('/'), PHP_URL_HOST),
        wp_parse_url(site_url('/'), PHP_URL_HOST),
    ]));
    $parts = wp_parse_url($current);
    if (empty($parts['host']) || !in_array($parts['host'], $allowed_hosts, true)) {
        return $canonical;
    }

    $current = trailingslashit($current);

    // Protection : on ne force que si on reste bien dans /marques/
    if (!preg_match('#^https?://[^/]+/marques/#i', $current)) {
        return $canonical;
    }

    return $current;

}, 99);

/**
 * LM-CAN-001 — Canonical Hardening for /redacteur/ (author-like archives)
 *
 * Problème visé : aucune balise <link rel="canonical"> rendue sur /redacteur/
 * Solution : injection server-side uniquement si ABSENTE (anti-doublon).
 *
 * Scope : uniquement les URLs dont le path commence par /redacteur/
 * Rollback : supprimer ce fichier MU-plugin.
 */

if (!defined('ABSPATH')) { exit; }

function lm_can_is_redacteur_scope(): bool {
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    if ($uri === '') return false;

    // Retire query pour matcher strictement le path
    $path = preg_replace('/\?.*/', '', $uri);
    return (stripos($path, '/redacteur/') === 0);
}

function lm_can_current_url_clean(): string {
    $scheme = is_ssl() ? 'https' : 'http';
    $host   = wp_parse_url(home_url('/'), PHP_URL_HOST);
    $uri    = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';

    $url = $scheme . '://' . $host . $uri;
    $url = preg_replace('/#.*/', '', $url);
    $url = preg_replace('/\?.*/', '', $url);

    // Normalisation trailing slash
    return trailingslashit($url);
}

/**
 * 1) Si RankMath fournit un canonical, on le normalise (au cas où)
 *    (utile si parfois il s’affiche, parfois non).
 */
add_filter('rank_math/frontend/canonical', function ($canonical) {
    if (!lm_can_is_redacteur_scope()) return $canonical;
    return lm_can_current_url_clean();
}, 99);

/**
 * 2) Hardening : si aucun canonical n’est présent dans <head>, on l’injecte.
 *    On fait ça via buffer HTML, scope strict /redacteur/ uniquement.
 */
add_action('template_redirect', function () {
    if (!lm_can_is_redacteur_scope()) return;

    ob_start(function ($html) {

        // Si déjà un canonical, on ne touche pas (anti-doublon)
        if (stripos($html, 'rel="canonical"') !== false || stripos($html, "rel='canonical'") !== false) {
            return $html;
        }

        $canonical = esc_url(lm_can_current_url_clean());
        $tag = "<link rel=\"canonical\" href=\"{$canonical}\" />\n";

        // Injection juste avant </head> si possible
        if (stripos($html, '</head>') !== false) {
            return preg_replace('~</head>~i', $tag . '</head>', $html, 1);
        }

        // Fallback : si </head> introuvable, on renvoie tel quel
        return $html;
    });
}, 0);


/**
 * LM-PAG-001 — Rank Math Title/Description Pagination Normalizer
 *
 * Objectif :
 * - Standardiser les titres paginés au format strict " – Page X"
 * - Éviter le format "Page X à Y" (ou "of Y") sur tags/cats
 * - Gérer correctement la page des articles (is_home / page_for_posts)
 *
 * Scope :
 * - Blog index (is_home) : ajoute " – Page X"
 * - Tag archives (is_tag) : remplace tout "Page X à Y" => " – Page X"
 * - Category archives (is_category) : idem (optionnel mais recommandé)
 *
 * Rollback : supprimer ce bloc.
 */

/* ==========================================================
   A) TITLE
   ========================================================== */
add_filter('rank_math/frontend/title', function ($title) {

    $p = (int) get_query_var('paged');
    if ($p <= 1) {
        return $title;
    }

    /**
     * 1) Blog index (page des articles)
     * URL type: /actualites/page/2/
     */
    if (is_home()) {
        $posts_page_id = (int) get_option('page_for_posts');
        if ($posts_page_id > 0) {
            // Anti-doublon si déjà suffixé
            if (stripos($title, 'Page ' . $p) === false) {
                $title .= ' – Page ' . $p;
            }
            return $title;
        }
    }

    /**
     * 2) Tag / Category archives
     * URL type: /actualites/bons-plans/page/38/ (tag) ou /category/.../page/2/
     *
     * But : virer "Page X à Y" ou "page X of Y", puis ajouter " – Page X".
     */
    if (is_tag() || is_category()) {

        // Nettoyage pagination existante (RM/thème)
        $title = preg_replace('~\s*[-–]\s*Page\s+\d+\s*(?:à|of)\s*\d+~iu', '', $title);
        $title = preg_replace('~\s*[-–]\s*Page\s+\d+~iu', '', $title);

        // Anti-doublon final
        if (stripos($title, 'Page ' . $p) === false) {
            $title = rtrim($title) . ' – Page ' . $p;
        }

        return $title;
    }

    // Autres contextes : ne pas toucher
    return $title;

}, 99);

/**
 * LM-PAG — Tag archives : suffixe pagination sur meta description
 * Objectif : éviter description dupliquée sur /tag/.../page/N/
 */
add_filter('rank_math/frontend/description', function ($description) {

    // Archives d'étiquette uniquement
    if (!is_tag()) {
        return $description;
    }

    $p = (int) get_query_var('paged');
    if ($p <= 1) {
        return $description;
    }

    // Anti-doublon si déjà suffixé
    if (stripos($description, 'Page ' . $p) !== false) {
        return $description;
    }

    $description = trim((string) $description);

    // Si Rank Math renvoie vide, fallback minimal
    if ($description === '') {
        $term = get_queried_object();
        $name = !empty($term->name) ? $term->name : 'Actualités';
        $description = $name;
    }

    return $description . ' – Page ' . $p;

}, 99);


/**
 * LM-CSS — Toggle optionnel pour charger la feuille refactorisée.
 *
 * Par défaut, seul style.css (chargé par le thème) reste actif.
 * Pour activer la version modulaire, définir USE_REFACTORED_CSS à true
 * (par ex. dans wp-config.php ou via un mu-plugin) :
 *
 *   define('USE_REFACTORED_CSS', true);
 *
 * Aucun style n’est supprimé ici, on ajoute simplement une feuille
 * supplémentaire à des fins de tests.
 */
add_action('wp_enqueue_scripts', function () {
    if (defined('USE_REFACTORED_CSS') && USE_REFACTORED_CSS) {
        wp_enqueue_style(
            'labomaison-refactored',
            get_stylesheet_directory_uri() . '/css/style-refactored.css',
            [],
            '0.0.1'
        );
    }
}, 20);

