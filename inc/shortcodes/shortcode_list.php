<?php

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
          break; // Quitte la boucle dès qu'une caractéristique valide est trouvée
        }
      }
    }
  }

  // Si aucune caractéristique valide n'est trouvée, retourne une chaîne vide ou un message.
  if (!$caracteristiques_existent) {
    return ""; // Ou return "Aucune caractéristique définie pour ce produit.";
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
          // Traitement des tableaux pour les afficher en pills
          if (is_array($sub_field_value)) {
            $i = 0; // Compteur pour alterner les couleurs de fond
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


// Fonction pour afficher les notes détaillées
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

// Fonction pour afficher la note globale
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


function afficher_ou_acheter_shortcode()
{
  global $post;

  // Récupération du nom de la marque à partir du champ relationnel 'marque'

  $ou_acheter_titre = get_field('ou_acheter_titre', $post->ID);
  $contenu_acheter = get_field('ou_acheter_contenu');

  if (!$ou_acheter_titre || !$contenu_acheter) {
    return '';
  }

  $contenu = "<div class='ou_acheter_container'>";
  $contenu .= "<h2 id='ou_acheter_title' class='ou_acheter_title'>$ou_acheter_titre</h2>"; // Commencez par le titre avec le nom du produit
  if ($contenu_acheter) {
    $contenu .= "<div class='ou-acheter-contenu'>" . $contenu_acheter . "</div>"; // Ajoutez le contenu
  } else {
    $contenu .= "<p>Contenu à venir...</p>"; // Gestion du cas où le contenu n'est pas encore disponible
  }

  $contenu .= "</div>";

  return $contenu;
}
add_shortcode('ou_acheter', 'afficher_ou_acheter_shortcode');

function afficher_galerie_shortcode($atts)
{
  $atts = shortcode_atts(array(
    'position' => 'debut', // 'debut' pour les premières images, 'fin' pour les dernières
  ), $atts);

  $images = get_field('gallerie_produit', false, false); // Obtient les IDs des images
  $image_feature_id = get_post_thumbnail_id(); // Obtient l'ID de l'image à la une
  $contenu = '<div class="galerie-images" id="gallerie_produit_title">';

  if ($images) {
    $selected_images = ($atts['position'] === 'fin') ? array_slice($images, -2, 2) : array_slice($images, 0, 2);

    // Apply different size based on the position
    $size_class_first = ($atts['position'] === 'fin') ? 'small' : 'large';
    $size_class_second = ($atts['position'] === 'fin') ? 'large' : 'small';

    foreach ($selected_images as $index => $image_id) {
      $image_url = wp_get_attachment_url($image_id);
      // Apply the size class conditionally
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

function afficher_pros_cons_shortcode($atts)
{
  // Récupère l'ID de post passé en paramètre ou utilise l'ID du post global
  $atts = shortcode_atts(array(
    'post_id' => get_the_ID(),
  ), $atts);

  $post_id = $atts['post_id'];

  $points_existent = false;

  // Récupérer les valeurs des champs ACF en utilisant l'ID du post spécifié
  $points_forts = get_field('points_forts', $post_id);
  $points_faibles = get_field('points_faibles', $post_id);

  // Si aucune caractéristique valide n'est trouvée, retourne une chaîne vide ou un message.
  if (!$points_forts || !$points_faibles) {
    return ""; // Ou return "Aucune caractéristique définie pour ce produit.";
  }

  // Construire le HTML
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

  $html .= '</div>'; // Fin de .pros-cons-container

  return $html;
}
add_shortcode('afficher_pros_cons', 'afficher_pros_cons_shortcode');

/*archive filter shortcode*/
function afficher_archive_filtre_shortcode($atts)
{
  // Attributs du shortcode, permettant par exemple de spécifier un CPT
  $atts = shortcode_atts(
    array(
      'post_type' => 'post', // Type de post par défaut
      'posts_per_page' => 2, // Nombre de posts par page par défaut
    ),
    $atts,
    'archive_filtre'
  );

  // Obtenir l'ID de la catégorie "coin"
  $coin_term = get_term_by('slug', 'coin', 'category');
  $coin_term_id = $coin_term ? $coin_term->term_id : 0;

  $html = '<div id="filtres">';

  // Exclure la catégorie "coin" et obtenir les catégories restantes
  $categories = get_terms(['taxonomy' => 'category', 'hide_empty' => false, 'exclude' => [$coin_term_id]]);
  foreach ($categories as $category) {
    if (strpos($category->slug, 'coin') !== false) {
      $html .= '<button class="filtre-categorie-btn" data-slug="' . esc_attr($category->slug) . '"><div class="color-container color-' . $category->slug . '"></div>' . esc_html($category->name) . '</button>';
    }
  }

  $html .= '<select id="filtre-auteur">';
  $html .= '<option value="">Rédacteurs</option>';

  // Ajouter les auteurs au dropdown
  $users = get_users(['who' => 'authors']);
  foreach ($users as $user) {
    $html .= '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
  }

  $html .= '</select>';
  $html .= '<input type="text" id="filtre-recherche" placeholder="Recherche...">';
  $html .= '<button style="display:none;" id="filtre-submit">Filtrer</button>'; // Bouton pour soumettre les filtres
  $html .= '</div>'; // Fin des filtres

  // Le markup de base pour le conteneur des posts et les filtres
  $html .= '<div id="archive-filtre-container" data-post-type="' . esc_attr($atts['post_type']) . '">';
  $html .= '<div id="filtres"><!-- Les filtres seront chargés ici --></div>';
  $html .= '<div id="posts-container"><!-- Les posts seront chargés ici --></div>';
  $html .= '</div>';
  $html .= '<div id="pagination-container"></div>';

  // Ajouter le script et le style
  wp_enqueue_script('archive-filtre-js');
  wp_enqueue_style('archive-filtre-css');

  return $html;
}
add_shortcode('archive_filtre', 'afficher_archive_filtre_shortcode');

// comparison feature
function wpb_comparaison_form_shortcode()
{
  // Dans votre fonction wpb_comparaison_form_shortcode()
  $form = '<form action="/comparatif" method="get" id="comparaison-form">';
  $form .= '<input type="hidden" id="selectedTest1" name="test1">';
  $form .= '<input type="hidden" id="selectedTest2" name="test2">';
  $form .= '<button type="submit" id="btn-comparer">Comparer</button>';
  $form .= '</form>';

  // Un peu de JavaScript pour s'assurer que l'utilisateur sélectionne exactement deux checkboxes avant de soumettre le formulaire.
  $form .= '<script>
        document.getElementById("comparaison-form").addEventListener("submit", function(event) {
            var checkedBoxes = document.querySelectorAll(".custom-checkbox:checked");
            if (checkedBoxes.length !== 2) {
                alert("Veuillez sélectionner exactement deux produits pour comparer.");
                event.preventDefault(); // Empêche la soumission du formulaire
            }
        });
    </script>';

  return $form;
}
add_shortcode('comparaison_form', 'wpb_comparaison_form_shortcode');

function display_comparison_shortcode()
{
  // Initialisation de la variable de sortie
  $output = '';

  // Vérifiez si les paramètres test1 et test2 sont présents
  if (isset($_GET['test1']) && isset($_GET['test2'])) {
    $test1_id = intval($_GET['test1']);
    $test2_id = intval($_GET['test2']);

    // Assurez-vous que les ID sont valides et non nuls
    if ($test1_id > 0 && $test2_id > 0) {
      $output .= '<div class="comparaison-container">';

      // Récupérez et affichez les informations pour chaque test
      $tests = [$test1_id, $test2_id];
      foreach ($tests as $test_id) {
        $post = get_post($test_id);
        if ($post) { // Assurez-vous que le post existe

          $image_url = get_the_post_thumbnail_url($post, 'full');

          // Récupérer les champs ACF
          $nom = get_field('nom', $test_id);
          $auteur_id = get_field('auteur', $test_id); // ou $post->post_author si le champ ACF 'auteur' stocke l'ID de l'utilisateur
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

          $output .=          '<h2>' . esc_html(get_the_title($post)) . '</h2>';
          $output .= '<p>Auteur: <a href="' . esc_url($auteur_url) . '">' . esc_html($auteur_name) . '</a></p>';
          if ($image_url) {
            $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($nom) . '">';
          }

          $output .= '<p>Nom: ' . esc_html($nom) . '</p>';
          $output .= '<p>Points Forts: ' . esc_html($points_forts) . '</p>';
          $output .= '<p>Points Faibles: ' . esc_html($points_faibles) . '</p>';

          $output .=         '<div>' . apply_filters('the_content', $post->post_content) . '</div>';
          $output .=        '</div>';
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

function display_acf_relationship_data()
{
  // Ensure the ACF function exists
  if (!function_exists('get_field')) return;

  $output = ''; // Initialize output string

  // Get current post ID (adjust if you're using this outside the loop)
  $post_id = get_the_ID();



  // Fetch 'type_de_produit' relationship data
  $types = get_field('type_de_produit', $post_id);


  if ($types) {
    $output .= 'Type de produit: ';
    foreach ($types as $type) {

      $output .= get_the_title($type) . ', ';
    }
    $output = rtrim($output, ', ') . '<br>'; // Remove trailing comma and add line break
  }

  // Fetch 'marque' relationship data
  $brands = get_field('marque', $post_id);
  if ($brands) {
    $output .= 'Marque: ';
    foreach ($brands as $brand) {
      $output .= get_the_title($brand->ID) . ', ';
    }
    $output = rtrim($output, ', '); // Remove trailing comma
  }

  return $output; // Return the formatted data
}
add_shortcode('show_acf_relationship_data', 'display_acf_relationship_data');

function display_acf_titre_shortcode()
{
  // Initializing the return value
  $output = '';

  // Check if we are on a taxonomy term page
  if (is_tag() || is_tax() || is_category()) {
    // Get the current term ID
    $term_id = get_queried_object_id();
    $taxonomy = get_queried_object()->taxonomy;

    // Fetch the 'titre' field for the current term
    $titre = get_field('titre', $taxonomy . '_' . $term_id);


    // Check if 'titre' field has a value
    if ($titre) {
      // Encapsulate the 'titre' field value in an h1 tag
      $output = '<h1>' . esc_html($titre) . '</h1>';
    } else {
      // Fallback to the term name, also within an h1 tag
      $term = get_queried_object();
      $output = '<h1>' . esc_html($term->name) . '</h1>';
    }
  } else {
    // Not a taxonomy term page
    $output = 'Ce n\'est pas une taxonomie.';
  }

  // Return the output
  return $output;
}

// Adding the shortcode
add_shortcode('show_acf_titre', 'display_acf_titre_shortcode');

function display_acf_contenu_shortcode()
{
  // Initializing the return value
  $output = '';

  // Check if we are on a taxonomy term page
  if (is_tag() || is_tax() || is_category()) {
    // Get the current term ID
    $term_id = get_queried_object_id();
    $taxonomy = get_queried_object()->taxonomy;

    // Fetch the 'titre' field for the current term
    $contenu = get_field('contenu', $taxonomy . '_' . $term_id);


    // Check if 'titre' field has a value
    if ($contenu) {
      // Encapsulate the 'titre' field value in an h1 tag
      $output = '<div class="tax_content">' . wp_kses_post($contenu) . '</div>';
    }
  } else {
    // Not a taxonomy term page
    return;
  }

  // Return the output
  return $output;
}

// Adding the shortcode
add_shortcode('show_acf_contenu', 'display_acf_contenu_shortcode');

function display_acf_faq_shortcode()
{
  // Initializing the return value
  $output = '';

  // Check if we are on a taxonomy term page
  if (is_tag() || is_tax() || is_category()) {
    // Get the current term ID
    $term_id = get_queried_object_id();
    $taxonomy = get_queried_object()->taxonomy;

    // Fetch the 'titre' field for the current term
    $faq = get_field('faq', $taxonomy . '_' . $term_id);


    // Check if 'titre' field has a value
    if ($faq) {
      // Encapsulate the 'titre' field value in an h1 tag
      $output = '<div id="faq_title" class="tax_faq">' . wp_kses_post($faq) . '</div>';
    }
  } else {
    // Not a taxonomy term page
    return;
  }

  // Return the output
  return $output;
}

// Adding the shortcode
add_shortcode('show_acf_faq', 'display_acf_faq_shortcode');


function test_archive_acf_field_shortcode($atts)
{
  // Extract attributes with defaults
  $atts = shortcode_atts(array(
    'post_id' => get_the_ID(), // Default to current post ID
    'field_name' => '',        // No default field name
  ), $atts);

  // Check if a valid field name is provided
  if (empty($atts['field_name'])) {
    return 'ACF field name not specified.';
  }

  $post_id = $atts['post_id'];
  $field_name = $atts['field_name'];
  $field_value = get_field($field_name, $post_id);

  // Determine the output based on the field type
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
      $image_url = $field_value['url']; // Assumes return format is array
      $content .= "<span class='label'>{$field_object['label']}</span>: <img src='{$image_url}' alt='{$field_value['alt']}' />";
      break;

    case 'wysiwyg':
      $content .= "<span class='label'>{$field_object['label']}</span>: <div class='value'>{$field_value}</div>";
      break;
  }

  $content .= "</div>";

  return $content;
}

// Register the shortcode with WordPress
add_shortcode('test_archive_field', 'test_archive_acf_field_shortcode');


function display_chapeau_shortcode()
{
  // Use ACF's get_field() to retrieve the 'chapeau' field content for the current post
  $chapeau_content = get_field('chapeau');

  // If there's nothing to summon, return an empty whisper
  if (!$chapeau_content) {
    return '';
  }

  // Enchant the content within a div, making it visible to all who gaze upon it
  $output = '<div class="chapeau-content">' . wp_kses_post($chapeau_content) . '</div>';

  return $output;
}
add_shortcode('display_chapeau', 'display_chapeau_shortcode');


function display_acf_chapeau_shortcode()
{
  // Initializing the return value
  $output = '';


  // Check if we are on a taxonomy term page
  if (is_tag() || is_tax() || is_category()) {
    // Get the current term ID and taxonomy information
    $term_id = get_queried_object_id();
    $taxonomy = get_queried_object()->taxonomy;

    // Fetch the 'chapeau' field for the current term
    $chapeau = get_field('chapeau', $taxonomy . '_' . $term_id);

    // Check if 'chapeau' field has a value
    if ($chapeau) {
      // Output 'chapeau' field value allowing HTML
      $output = $chapeau; // Removed esc_html to allow HTML content
    } else {
      // Fallback to the term description (content) allowing HTML when 'chapeau' is empty
      $term = get_queried_object();
      $output = wp_kses_post($term->description); // Use wp_kses_post to allow safe HTML
    }
  } else {
    // Not a taxonomy term page, output a default message
    $output = 'Not a taxonomy term context.';
  }

  // Return the output
  return $output;
}

// Adding the shortcode
add_shortcode('show_acf_chapeau', 'display_acf_chapeau_shortcode');

function display_category_info_shortcode($atts)
{
    // Extraire l'ID du terme à partir des attributs
    $atts = shortcode_atts(['term_id' => 0], $atts);
    $term_id = intval($atts['term_id']);

    if (!$term_id) {
        return 'Veuillez spécifier un ID de terme valide.';
    }

    // Récupérer le terme à partir de l'ID
    $term = get_term($term_id);

    if (is_wp_error($term) || !$term) {
        return 'Le terme spécifié est introuvable.';
    }

    // Récupérer les champs personnalisés
    $image_id = get_field('featured', $term);
    $titre = get_field('titre', $term) ?: $term->name;
    $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
    $term_url = get_term_link($term);

    // Générer le HTML
    $output = '<div class="category_container publication_template_container small_image mobile_image">';
    $output .= '<a href="' . esc_url($term_url) . '" class="category_featured_link" style="background-image: url(' . esc_url($image_url) . ');">';
    $output .= '<div class="header_thumbnail_container">';
    $output .= '<h2 class="gb-headline gb-headline-text">' . esc_html($titre) . '</h2>';
    $output .= '</div>'; // .header_thumbnail_container
    $output .= '</a>'; // .category_featured_link
    $output .= '</div>'; // .category_container

    return $output;
}
add_shortcode('display_category', 'display_category_info_shortcode');

function display_last_updated_category_info_shortcode()
{
  // Essayer d'obtenir l'ID de la catégorie mise à jour en dernier
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

  // Si après la tentative précédente, nous n'avons pas d'ID, sélectionner une catégorie par défaut
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

  // Continuer avec l'ID de catégorie déterminé
  $category = get_term($category_id, 'categorie_test');
  if (is_wp_error($category) || !$category) {
    // Si tout échoue, sélectionnez la première catégorie disponible comme fallback
    $fallback_category = get_terms([
      'taxonomy' => 'categorie_test',
      'number' => 1,
    ]);

    if (!is_wp_error($fallback_category) && !empty($fallback_category)) {
      $category = array_shift($fallback_category);
      $category_id = $category->term_id;
    } else {
      // Si aucune catégorie n'est disponible, sortir de la fonction
      return 'Catégories non trouvées.';
    }
  }

  // Construire et retourner le HTML avec les informations de la catégorie
  $image_id = get_field('featured', 'categorie_test_' . $category_id);
  $categorie_titre = get_field('titre', 'categorie_test_' . $category_id) ?: $category->name;
  $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
  $category_url = get_term_link($category);

  $output = '<div class="category_container publication_template_container small_image mobile_image">';
  $output .= '<a href="' . esc_url($category_url) . '" class="category_featured_link" style="background-image: url(' . esc_url($image_url) . ');">';
  $output .= '<div class="header_thumbnail_container">';
  $output .= '<h2 class="gb-headline gb-headline-text">' . esc_html($categorie_titre) . '</h2>';
  $output .= '</div>'; // .header_thumbnail_container
  $output .= '</a>'; // .category_featured_link
  $output .= '</div>'; // .category_container

  return $output;
}
add_shortcode('display_last_updated_category', 'display_last_updated_category_info_shortcode');


function display_post_dates_shortcode()
{
  global $post;

  // Initialize output variable
  $output = '';

  // Get post publish and modified dates
  $publish_date = get_the_date('d/m/Y \à H:i', $post->ID);
  $modified_date = get_the_modified_date('d/m/Y \à H:i', $post->ID);

	

  // Check if the post has been updated (modified date is different from publish date)
  if ($publish_date == $modified_date) {
    // Post has not been updated
    $output .= "<span class='datetime'>Publié le " . $publish_date . "</span>";
  } else {
    // Post has been updated
    $output .= "<span class='datetime'>Mis à jour le " . $modified_date . "</span>";
  }

  return $output;
}

add_filter('render_block', function($block_content, $block) {
  // Vérifie si le bloc contient la classe spécifiée pour modifier les dates
  if (strpos($block['attrs']['className'] ?? '', 'show-post-date') !== false) {
      // Récupère l'ID du post courant dans le contexte du query loop
      $post_id = get_the_ID();
      $publish_date = get_the_date('d/m/Y \à H:i', $post_id);
      $modified_date = get_the_modified_date('d/m/Y \à H:i', $post_id);

      // Construit le contenu en fonction de si le post a été modifié après sa publication
      if ($publish_date == $modified_date) {
          $date_content = "<span class='datetime'>Publié le " . $publish_date . "</span>";
      } else {
          $date_content = "<span class='datetime'>Mis à jour le " . $modified_date . "</span>";
      }

      // Insère les dates avant ou après le contenu original du bloc
      $block_content = $date_content . $block_content;
  }

  return $block_content;
}, 10, 2);


// Register the shortcode with WordPress
add_shortcode('display_post_dates', 'display_post_dates_shortcode');
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
            $selected_category = $categories[0]; // Prend la première catégorie

            // Cherche la catégorie de test correspondante
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
        // Code existant pour les tests
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

function display_linked_test_category_complete($atts)
{
  // Extract the post ID from attributes, defaulting to the current post if not specified
  $atts = shortcode_atts(['post_id' => get_the_ID()], $atts);
  $post_id = $atts['post_id'];

  if (!$post_id) return ''; // Abandon the spell if no post is found

  $output = '';
  $categories = get_the_terms($post_id, 'categorie_test'); // Directly retrieve terms from 'categorie_test'

  if (!empty($categories) && !is_wp_error($categories)) {
    foreach ($categories as $category) {
      $term_link = get_term_link($category, 'categorie_test');
      if (!is_wp_error($term_link)) {
        // Assemble the linked categories
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

function afficher_note_globale_shortcode_card($atts)
{
  global $post;
  $atts = shortcode_atts(['post_id' => $post->ID], $atts);
  $post_id = $atts['post_id'];



  if (property_exists($post, 'note_globale') && is_numeric($post->note_globale)) {
    $note_globale = (float)$post->note_globale;
  } else {
    // Ensure the value is numeric; otherwise, default to 0
    $note_globale = is_numeric(get_field('note_globale', $post_id)) ? (float)get_field('note_globale', $post_id) : 0.0;
  }

  // Si la note globale n'est pas renseignée, retourner une chaîne vide.
  if (empty($note_globale)) {
    return ''; // Vous pouvez aussi retourner un message spécifique si vous préférez.
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

function afficher_custom_data_shortcode($atts)
{
  global $post;
  // Retrieve the provided post ID or use the current post ID by default
  $atts = shortcode_atts(['post_id' => $post->ID], $atts);
  $post_id = $atts['post_id'];

  // Retrieve the 'nom' field value
  $nom = get_field('nom', $post_id);

  // Retrieve the 'marque' field, assuming it returns an array of post objects
  $marque_posts = get_field('marque', $post_id);
  $marque_title = '';

  // If 'marque' posts are available and is an array, get the title of the first related 'marque' post
  if (!empty($marque_posts) && is_array($marque_posts)) {
    $marque_post = reset($marque_posts); // Get the first item from the array
    $marque_title = get_the_title($marque_post->ID);
  }

  // Combine 'marque' title and 'nom' to create 'test_card_title', trimming to avoid leading/trailing spaces
  $test_card_title = trim($marque_title . ' ' . $nom);

  // If both 'marque' and 'nom' are empty, fallback to the post title
  if (empty($test_card_title)) {
    $test_card_title = get_the_title($post_id);
  }

  // Return the title or combined information, properly escaped for HTML
  return esc_html($test_card_title);
}

// Register the shortcode to make it available for use
add_shortcode('afficher_custom_data', 'afficher_custom_data_shortcode');

/*Faqu section shortcode*/
function display_faq_section()
{
  // Get the FAQ title and content if available
  $faq_title = get_field('faq_titre');
  $faq_content = get_field('faq_contenu');

  if (!$faq_title || !$faq_content) {
    return '';
  }

  $output = '';

  // Only display if both title and content are not empty
  if (!empty($faq_title) && !empty($faq_content)) {
    $output .= '<div class="faq-section">';
    $output .= '<h2 id="faq_title">' . esc_html($faq_title) . '</h2>';
    $output .= '<div>' . wp_kses_post($faq_content) . '</div>';
    $output .= '</div>';
  }

  return $output;
}
add_shortcode('display_faq', 'display_faq_section');

/*Conclusion section shortcode*/
function display_conclusion_section()
{
  // Get the FAQ title and content if available
  $conclusion = get_field('conclusion');

  if (!$conclusion) {
    return '';
  }

  $output = '';

  // Only display if both title and content are not empty
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

/*Avis contenu section shortcode*/
function display_contenu_section()
{
  // Get the FAQ title and content if available
  $avis_de_labomaison = get_field('avis_de_labomaison');
  $contenu_du_test = get_field('contenu_du_test');

  if (!$avis_de_labomaison || !$contenu_du_test) {
    return '';
  }

  $output = '';

  // Only display if both title and content are not empty
  if ($avis_de_labomaison && $contenu_du_test) {
    $output .= '<div class="contenu-section">';
    $output .= '<h2 id="contenu_title">' . esc_html($avis_de_labomaison) . '</h2>';
    $output .= '<div>' . wp_kses_post($contenu_du_test) . '</div>';
    $output .= '</div>';
  }

  return $output;
}
add_shortcode('display_contenu', 'display_contenu_section');

/*Presentation section shortcode*/
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
      return ''; // on supprime les iframes non autorisées
    },
    $presentation_content
  );

  // Appliquer wp_kses au reste du contenu (hors iframe déjà filtré)
  $filtered_content = wp_kses($filtered_content, $allowed_tags);

  $output .= '<div class="presentation-section">';
  $output .= '<h2 id="presentation_title">' . esc_html($presentation_title) . '</h2>';
  $output .= '<div>' . $filtered_content . '</div>';
  $output .= '</div>';

  return $output;
}
add_shortcode('display_presentation', 'display_presentation_section');


add_shortcode('search_title', 'get_search_title');
function get_search_title()
{
  if (is_search()) {
    return '<div class="search_title_container"><h3 class="search-for">Résultats de recherche pour</h3><h1 class="search-title">' . get_search_query() . '</h1></div>';
  } elseif (is_archive()) {
    return '<h1 class="search-title">' . get_the_archive_title() . '</h1>';
  }
}

function display_author_featured_image()
{
  global $post; // Summon the global post object to access the current post's data

  // Retrieve the ID of the post's author
  $author_id = $post->post_author;

	// Récupérer le nom de l'auteur
	$author_name = get_the_author_meta('display_name', $author_id);

  // Fetch the URL to the author's page
  $author_url = get_author_posts_url($author_id);

  // Assuming the featured image is stored as user meta. Replace 'your_meta_key' with the actual meta key.
  $author_image_id = get_user_meta($author_id, 'featured_image', true);

  // If an image ID exists, conjure the image HTML
  if (!empty($author_image_id)) {
    $image_html = wp_get_attachment_image($author_image_id, 'author-thumbnail'); // 'full' can be changed to any registered image size

    // Return the image HTML, encased in a banner div
            return '<div style="width: 30px; height: 30px;" class="author-featured-image-banner"><a href="' . esc_url($author_url) . '" aria-label="Visitez la page de l\'auteur ' . esc_attr($author_name) . '">' . $image_html . '</a></div>';
  }

  // Should no image be bound to the spell, return an empty string
  return '';
}
add_shortcode('author_featured_image', 'display_author_featured_image');

/*modify request in query loop*/
function display_related_news_posts($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10
    ), $atts);
    $limit = intval($atts['limit']);

    if (is_tax('categorie_test')) {
        // Récupérer l'objet de la catégorie de test
        $term = get_queried_object();
        $test_category_slug = $term->slug;

        // Utiliser le slug de la catégorie de test pour trouver la catégorie de post
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

				// Catégorie "categorie_test" correspondante au slug courant
				$test_term = get_term_by('slug', $test_category_slug, 'categorie_test');

				// Lien du terme (si trouvé)
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

						// Badge catégorie (uniquement si terme + lien OK)
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


            $output .= '</div>'; // Fermeture de .related-content__grid
            $output .= '</div>'; // Fermeture de .related-content

            wp_reset_postdata();
            return $output;
        }
    }
    return '';
}
add_shortcode('display_related_news', 'display_related_news_posts');

function display_related_test_categories($atts) {
    $atts = shortcode_atts(array(
        'limit' => 4
    ), $atts);
    $limit = intval($atts['limit']);

    if (is_tax('categorie_test')) {
        $current_term = get_queried_object();
        $parent_id = $current_term->parent;

        // Si c'est une catégorie enfant, on prend le parent, sinon on garde la catégorie actuelle
        $parent_term_id = $parent_id ? $parent_id : $current_term->term_id;

        // Récupérer toutes les catégories enfants du même parent (ou les enfants si on est sur le parent)
        $args = array(
            'taxonomy' => 'categorie_test',
            'child_of' => $parent_term_id,
            'hide_empty' => false,
            'exclude' => $current_term->term_id, // Exclure la catégorie actuelle
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

                $featured_image_id = get_field('featured', $category); // Assurez-vous que 'featured_image' est le bon nom du champ ACF

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

            $output .= '</div>'; // Fermeture de .related-content__grid
            $output .= '</div>'; // Fermeture de .related-content

            return $output;
        }
    }
    return '';
}
add_shortcode('display_related_test_categories', 'display_related_test_categories');

function display_promotion_link() {
    if (!is_single()) {
        return '';
    }

    global $post;

    // Récupérer les termes parents de la taxonomie 'promotion' pour cet article
    $promotion_terms = wp_get_post_terms($post->ID, 'promotion', array('parent' => 0));

    if (empty($promotion_terms) || is_wp_error($promotion_terms)) {
        return '';
    }

    // Utiliser le premier terme parent trouvé
    $promotion_term = $promotion_terms[0];

    // Construire le lien vers la page d'archive de la promotion
    $promotion_link = get_term_link($promotion_term);

    if (is_wp_error($promotion_link)) {
        return '';
    }

    $current_year = date('Y'); // Obtenir l'année courante

    // Construire le HTML pour le lien avec le même style que le shortcode de catégorie
    $markup = '<blockquote style="margin-bottom: 0px;">';
    $markup .= '&gt;&gt; <a style="font-size: inherit;" href="' . esc_url($promotion_link) . '">';
    $markup .= '<span style="font-size: inherit;">' . esc_html($promotion_term->name) . ' : Retrouvez tous nos articles en promotion dans notre sélection ' . $current_year .'</span>';
    $markup .= '</a>';
    $markup .= '</blockquote>';

    return $markup;
}
add_shortcode('promotion_link', 'display_promotion_link');

function display_related_news_posts_for_test()
{
  global $post;

  // Initialisation
  $output = '';
  $displayed_posts_count = 0;
  $needed_posts = 6; // Nombre minimal d'articles à afficher
  $directly_linked_articles = get_field('articles_associes', $post->ID); // Assumer que cela retourne un tableau d'objets post
  $excluded_article_ids = []; // Pour exclure les articles déjà affichés

  // Affichage des articles directement liés
  if ($directly_linked_articles) {
    // Trier les articles directement liés par date de mise à jour décroissante
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

    // Requête pour compléter les articles
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
      'orderby' => 'date', // Order by date
      'order' => 'DESC' // Most recent first
    ];

    $query = new WP_Query($args);
    while ($query->have_posts()) {
      $query->the_post();
      $output .= render_post_item_for_test(get_the_ID());
      $displayed_posts_count++;
      $excluded_article_ids[] = get_the_ID(); // Add to excluded to prevent duplication in the next query
    }
    wp_reset_postdata();
  }

  // Si moins de 3 articles, chercher des articles sans restriction de catégorie
  if ($displayed_posts_count < $needed_posts) {
    $args = [
      'post_type' => 'post',
      'posts_per_page' => $needed_posts - $displayed_posts_count,
      'post__not_in' => $excluded_article_ids, // Exclure les articles déjà affichés
      'post_status' => 'publish',
      'orderby' => 'date', // Order by date
      'order' => 'DESC' // Most recent first
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

// Fonction utilitaire pour générer le HTML d'un article pour le test
function render_post_item_for_test($post_id)
{
  // Obtenir les données de l'article
  $permalink = get_permalink($post_id);
  $title = get_the_title($post_id);
  $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
  $last_updated = get_the_modified_time('j F Y à H:i', $post_id);
  $chapeau = get_field('chapeau', $post_id);

  // Récupérer la catégorie de l'article
  $categories = get_the_category($post_id);
  $category_html = '';
  if (!empty($categories) && !is_wp_error($categories)) {
    $selected_category = null;
    // Sélectionner la première catégorie non-parente, ou la première catégorie si toutes sont parentes
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

  // Construire le HTML
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

add_shortcode('display_related_news_for_test', 'display_related_news_posts_for_test');

function display_associated_tests_shortcode()
{
  global $post;

  // Initialize output variable
  $output = '';

  // Retrieve the related posts from the ACF 'produit_associe' relationship field
  $associated_tests = get_field('produit_associe', $post->ID);

  if ($associated_tests) {
    $output .= '<div class="related-news-posts related-news__items--count-' . min(count($associated_tests), 4) . '">';

    foreach ($associated_tests as $test_post) {
      $test_thumbnail = get_the_post_thumbnail_url($test_post->ID, 'large');
      $test_title = get_the_title($test_post->ID);
      $test_permalink = get_permalink($test_post->ID);
      $terms = get_the_terms($test_post->ID, 'your_taxonomy_name'); // Adjust 'your_taxonomy_name' as needed

      $output .= '<div class="related-news__item" style="background-image: url(' . esc_url($test_thumbnail) . ');">';
      $output .= '<a href="' . esc_url($test_permalink) . '" class="related-news__full-link"></a>';
      $output .= '<div class="related-news__overlay">';
      $output .= '<div class="related-news__content">';
      $output .= '<h4 class="related-news__headline">' . esc_html($test_title) . '</h4>';

      // Display terms if available
      if (!is_wp_error($terms) && !empty($terms)) {
        $output .= '<div class="related-news__terms">';
        foreach ($terms as $term) {
          $term_link = get_term_link($term);
          if (!is_wp_error($term_link)) {
            $output .= '<a href="' . esc_url($term_link) . '" class="related-news__term-link">' . esc_html($term->name) . '</a> ';
          }
        }
        $output .= '</div>'; // Close .related-news__terms
      }

      $output .= '</div>'; // Close .related-news__content
      $output .= '</div>'; // Close .related-news__overlay
      $output .= '</div>'; // Close .related-news__item
    }

    $output .= '</div>'; // Close .related-news-posts
  }

  return $output;
}
add_shortcode('display_associated_tests', 'display_associated_tests_shortcode');

function insert_static_toc() {
    if (get_post_type() !== 'test') {
        return '<style>#block-3{display:none;}</style>';
    }

    $sections = [
        'Fiche Technique / Caractéristiques' => 'caracteristiques_title',
        'Avis Labo Maison' => 'contenu_title',
        'Sous notes' => 'notes_du_produit_title',
        'Conclusion' => 'conclusion_title',
        'Où trouver … au meilleur prix ?' => 'ou_acheter_title',
        'Présentation' => 'presentation_title'
    ];

    $toc_html = '<nav class="toc-desktop toc-columns" aria-label="Table des matières">';
    $toc_html .= '<h2 class="toc_headline">Table des matières</h2>';
    $toc_html .= '<ul role="list">';

    foreach ($sections as $title => $id) {
        $toc_html .= '<li role="listitem"><a href="#' . esc_attr($id) . '" class="toc-link" aria-label="Aller à la section ' . esc_attr($title) . '">' . esc_html($title) . '</a></li>';
    }

    $toc_html .= '</ul>';
    $toc_html .= '</nav>';

    return $toc_html;
}
add_shortcode('dynamic_toc', 'insert_static_toc');

function show_acf_promotion_data_shortcode() {
    $output = ''; // Initialiser la variable de sortie

    // Vérifier si nous sommes sur une page de terme de taxonomie
    if (is_tax('promotion')) { // Assurez-vous de remplacer 'promotion' par le nom réel de votre taxonomie si nécessaire
        // Obtenir l'ID du terme courant et des informations sur la taxonomie
        $term_id = get_queried_object_id();
        $taxonomy = get_queried_object()->taxonomy;

        // Récupérer les champs 'contenu' et 'faq' pour le terme courant
        $contenu = get_field('contenu', $taxonomy . '_' . $term_id);
        $faq = get_field('faq', $taxonomy . '_' . $term_id);

        // Construire la sortie HTML
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
        // Pas un contexte de terme de taxonomie, message par défaut
        $output = 'Cette information n\'est pas disponible dans ce contexte.';
    }

    return $output; // Retourner le contenu construit
}

// Ajouter le shortcode à WordPress
add_shortcode('show_acf_promotion_data', 'show_acf_promotion_data_shortcode');

function category_link_shortcode() {
  global $post;

  // Get the current test categories
  $test_categories = get_the_terms($post->ID, 'categorie_test');
  if ($test_categories && !is_wp_error($test_categories)) {
      // Assuming you want to link to the first category found
      $category = $test_categories[0];
      $category_link = get_term_link($category);

      if (!is_wp_error($category_link)) {
          $current_year = date('Y'); // Get the current year

          $markup = '<blockquote style="margin-bottom: 0px;">';
		  $markup .= '&gt;&gt; <a style="font-size: inherit;" href="' . esc_url($category_link) . '">';
          $markup .= '<span style="font-size: inherit;">' . esc_html($category->name) . ' : Retrouvez tous nos tests et fiches produits dans notre comparatif ' . $current_year .'</span>';
          $markup .= '</a>';
          $markup .= '</blockquote>';
          return $markup;
      }
  }

  return ''; // Return empty string if no category found or there's an error
}
add_shortcode('category_link', 'category_link_shortcode');

function post_category_link_shortcode() {
  global $post;

  // Get the categories of the current post
  $post_categories = get_the_terms($post->ID, 'category');
  if ($post_categories && !is_wp_error($post_categories)) {
      // Assuming you want to link to the first category found
      $category = $post_categories[0];
      $category_slug = $category->slug;

      // Get the corresponding test category
      $test_category = get_terms([
          'taxonomy' => 'categorie_test',
          'slug' => $category_slug,
          'hide_empty' => false,
      ]);

      if ($test_category && !is_wp_error($test_category) && !empty($test_category)) {
          $test_category = $test_category[0];
          $category_link = get_term_link($test_category);

          if (!is_wp_error($category_link)) {
            $current_year = date('Y'); // Get the current year
            $markup = '<blockquote style="margin-bottom: 0px;">';
		  $markup .= '&gt;&gt; <a style="font-size: inherit;" href="' . esc_url($category_link) . '">';
          $markup .= '<span style="font-size: inherit;">' . esc_html($category->name) . ' : Retrouvez tous nos tests et fiches produits dans notre comparatif ' . $current_year .'</span>';
          $markup .= '</a>';
          $markup .= '</blockquote>';
          return $markup;
        }
      }
  }

  return ''; // Return empty string if no corresponding test category found or there's an error
}
add_shortcode('post_category_link', 'post_category_link_shortcode');

function afficher_articles_lies_a_marque_courante() {
    if (!is_singular('marque')) {
        return ''; // Assurez-vous que nous sommes sur une page de type marque
    }

    // Récupérer l'ID de la marque courante
    $marque_id = get_the_ID();

    // Initialiser la variable de sortie
    $output = '<div id="related-news-container" data-marque-id="' . $marque_id . '">';

    // Charger les premiers articles
    $output .= load_more_articles_by_marque($marque_id, 1, []);

    $output .= '</div>';
    //$output .= '<div id="load-more-container"><button id="load-more-button" data-paged="2" data-exclude="[]">Charger plus</button></div>'; // Bouton pour charger plus

    return $output;
}
add_shortcode('afficher_articles_pour_marque', 'afficher_articles_lies_a_marque_courante');


function load_more_articles_by_marque($marque_id, $paged, $exclude_ids) {
    // Créer une requête pour récupérer les articles associés à cette marque
    $args = [
        'post_type' => 'post',
        'meta_query' => [
            [
                'key' => 'marque', // Champ de relation
                'value' => '"' . $marque_id . '"', // ID de la marque courante
                'compare' => 'LIKE',
            ]
        ],
        'posts_per_page' => 9, // Nombre d'articles par page
        'paged' => $paged,
        'post_status' => 'publish',
        'post__not_in' => $exclude_ids // Exclure les articles déjà affichés
    ];

    $query = new WP_Query($args);

    // Initialiser la variable de sortie
    $output = '';

    if ($query->have_posts()) {
        // Boucle à travers les articles et les afficher
        while ($query->have_posts()) {
            $query->the_post();
            $exclude_ids[] = get_the_ID(); // Ajouter l'ID de l'article à la liste des exclus
            $output .= render_post_item_for_marque(get_the_ID());
        }

		$output .= '<div class="no-more-posts" style="margin-top: 35px;"><a href="' . get_post_type_archive_link('test') . '">Voir tous les tests</a></div>';
    }

    // Réinitialiser les données de post
    wp_reset_postdata();

    return $output;
}

function render_post_item_for_marque($post_id) {
    // Obtenir les données de l'article
    $permalink = get_permalink($post_id);
    $title = get_the_title($post_id);
    $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
    $last_updated = get_the_modified_time('j F Y à H:i', $post_id);
    $linked_test_category_html = do_shortcode('[linked_test_category post_id="' . $post_id . '"]');
    $chapeau = get_field('chapeau', $post_id);

    // Construire le HTML
    $html = "
        <div class='article-card' data-post-id='$post_id'>
            <div class='article-thumbnail' style='background-image: url(\"$thumbnail_url\");'>
                <a href='$permalink' class='overlay-link'></a>
                $linked_test_category_html
            </div>
            <div class='article-content'>
                <span class='article-title'><a href='$permalink' class='article-title'>$title</a></span>
                <p class='article-excerpt chapeau_post_card'>" . $chapeau . "</p>
                <span class='datetime'>Mis à jour le $last_updated</span>
            </div>
        </div>";

    return $html;
}

function load_more_articles() {
    $marque_id = intval($_POST['marque_id']);
    $paged = intval($_POST['paged']);
    $exclude = isset($_POST['exclude']) ? array_map('intval', explode(',', $_POST['exclude'])) : [];

    echo load_more_articles_by_marque($marque_id, $paged, $exclude);
    wp_die();
}
add_action('wp_ajax_load_more_articles', 'load_more_articles');
add_action('wp_ajax_nopriv_load_more_articles', 'load_more_articles');



add_filter( 'generateblocks_query_loop_args', function( $query_args, $attributes ) {
    if ( ! empty( $attributes['className'] ) && strpos( $attributes['className'], 'multi-post-type' ) !== false ) {
        return array_merge( $query_args, array(
          'post_type' => array('post', 'test'),
        ) );
    }
    return $query_args;
}, 10, 2 );

function add_custom_post_type_class( $classes ) {
    if ( 'test' === get_post_type() ) {
        $classes[] = 'post-type-test';
    } elseif ( 'post' === get_post_type() ) {
        $classes[] = 'post-type-post';
    }
    return $classes;
}
add_filter( 'post_class', 'add_custom_post_type_class' );



function modify_headline_block_for_tests( $block_content, $block ) {
  // Vérifier si on est sur une publication de type 'test'
  if ( 'test' === get_post_type() ) {
      // Vérifier si la classe 'home_tests_fields_stars' est présente dans le bloc
      if ( isset( $block['attrs']['className'] ) && strpos( $block['attrs']['className'], 'home_tests_fields_stars' ) !== false ) {
          // Ajouter le shortcode pour afficher les étoiles
          $shortcode_stars = do_shortcode('[afficher_note_globale_card post_id="' . get_the_ID() . '"]');
          $block_content = $shortcode_stars . $block_content;
      }

      // Vérifier si la classe 'home_tests_fields_affiliz' est présente dans le bloc
      if ( isset( $block['attrs']['className'] ) && strpos( $block['attrs']['className'], 'home_tests_fields_affiliz' ) !== false ) {
          // Récupérer le champ personnalisé 'bouton_affiliz'
          $bouton_affiliz = get_post_meta( get_the_ID(), 'bouton_affiliz', true );

          // Vérifier que la valeur n'est pas vide
          if ( !empty($bouton_affiliz) ) {
             

              // Ajouter directement le contenu HTML récupéré sans le modifier
              $block_content = $bouton_affiliz . $block_content;
          } 
      }
  } else {
      // Si ce n'est pas un 'test', masquer ces blocs
      if ( isset( $block['attrs']['className'] ) &&
           (strpos( $block['attrs']['className'], 'home_tests_fields_stars' ) !== false || strpos( $block['attrs']['className'], 'home_tests_fields_affiliz' ) !== false) ) {
          return ''; // Ne rien afficher
      }
  }

  return $block_content;
}
add_filter( 'render_block', 'modify_headline_block_for_tests', 10, 2 );

function display_universal_buying_guides($atts) {
    $atts = shortcode_atts(array(
        'limit' => 4
    ), $atts);

    $limit = intval($atts['limit']);
    $category = null;
    $current_object_id = null;

    // Toujours initialiser
    $queried_test_term = null;        // terme courant si on est sur is_tax('categorie_test')
    $associated_test_term = null;     // terme categorie_test associé à la catégorie WP

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
        // Important: post__not_in attend des IDs de posts, pas des term_id
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

    // Si aucun guide et pas de terme categorie_test pertinent à afficher -> rien
    // Ici on compare uniquement contre le terme categorie_test associé, pas le terme query.
    if (!$buying_guides->have_posts() && (!$associated_test_term || $associated_test_term->term_id === $current_object_id)) {
        wp_reset_postdata();
        return '';
    }

    // Titre conditionnel basé sur l’existence du terme associé
    $title_text = ($associated_test_term) ? 'Guide(s) d\'achat' : 'Comparatif et guide(s) d\'achat';

    // HTML
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

    // Ajouter la catégorie de test associée si elle existe et n'est pas l’objet courant
    if ($associated_test_term && $associated_test_term->term_id !== $current_object_id && $count < $limit) {
        $output .= generate_card_html($associated_test_term, true);
    }

    $output .= '</div>';
    wp_reset_postdata();

    return $output;
}
add_shortcode('display_related_buying_guides', 'display_universal_buying_guides');


// Shortcode pour les articles associés
function generate_star_rating($note_globale) {
    if (empty($note_globale)) return '';

    $note_globale = is_numeric($note_globale) ? floatval($note_globale) : 0.0;
    $unique_id = uniqid('star_');

    $output = "<div class='note-globale'><div class='etoiles-container'>";
    for ($i = 1; $i <= 5; $i++) {
        $fill = $note_globale >= $i ? 100 : ($note_globale > $i - 1 ? ($note_globale - floor($note_globale)) * 100 : 0);
        // ajoute l'unique au gradient ID
        $grad_id = "grad-{$unique_id}-{$i}";
        $output .= "<svg width='20' height='20' viewBox='0 0 50 50' class='etoile'>
            <defs>
                <linearGradient id='{$grad_id}'>
                    <stop offset='{$fill}%' stop-color='gold'/>
                    <stop offset='{$fill}%' stop-color='gray'/>
                </linearGradient>
            </defs>
            <polygon points='25,1 32,19 50,19 35,30 40,48 25,37 10,48 15,30 0,19 18,19' fill='url(#{$grad_id})'/>
        </svg>";
    }
    $output .= "</div></div>";
    return $output;
}




// [related_articles]
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

    // Helper badge categorie_test correspondant au slug de la catégorie WP (pour les articles)
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

        // Badge (catégorie) au-dessus du titre
        if ($is_test) {
            $output .= $render_test_badge($pid);
        } else {
            $output .= $render_article_badge($current_category->slug, $current_category->name);
        }

        // Titre
        $output .= '<span class="related-content__card-title"><a href="' . esc_url($permalink) . '">'
                . esc_html($title) . '</a></span>';

        // Test : étoiles + CTA Affiliz (pas de prix)
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
            // Article : date (homogène)
            $output .= '<span class="related-content__date">Publié le ' . esc_html(get_the_date('', $pid)) . '</span>';
        }

        $output .= '</div></div>';
    }

    $output .= '</div></div>';

    return $output;
}
add_shortcode('related_articles', 'display_related_articles');



// Fonction commune pour générer le HTML d'une carte
function generate_card_html($post_or_term, $is_category = false) {
      if ($is_category) {
        // $post_or_term est un WP_Term
        $term_id = isset($post_or_term->term_id) ? (int) $post_or_term->term_id : 0;

        $permalink = get_term_link($post_or_term, 'categorie_test');

        // IMPORTANT : pour ACF sur taxonomie, il faut passer "taxonomy_termId"
        $acf_ref = $term_id ? ('categorie_test_' . $term_id) : $post_or_term;

        $title = get_field('titre', $acf_ref);
        $featured_image_id = get_field('featured', $acf_ref);
        $thumbnail_url = $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'medium') : '';
        $content = isset($post_or_term->description) ? wp_trim_words($post_or_term->description, 10) : '';
        $date = ''; // pas de date fiable pour un terme
    } else {
        $permalink = get_permalink($post_or_term->ID);
        $title = get_the_title($post_or_term->ID);
        $thumbnail_url = get_the_post_thumbnail_url($post_or_term->ID, 'medium');
        $content = wp_trim_words(get_the_content(null, false, $post_or_term->ID), 10);
        $date = get_the_date('d/m/Y \à H:i', $post_or_term->ID);
    }

    $output = '<div class="related-content__card">';
    $output .= '<div class="related-content__image">';
    $output .= '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($title) . '">';
    $output .= '</div>';
    $output .= '<div class="related-content__content">';
    $output .= '<span class="related-content__card-title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></span>';
    //$output .= '<p class="related-content__excerpt">' . esc_html($content) . '</p>';
    if ($date) {
        $output .= '<span class="related-content__date">Publié le ' . esc_html($date) . '</span>';
    }
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

function display_latest_articles($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10
    ), $atts);
    $limit = intval($atts['limit']);

    // Requête pour récupérer les derniers articles
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $latest_posts = new WP_Query($args);

    if (!$latest_posts->have_posts()) {
        return '';
    }

    // Générer le HTML
    $output = '<div class="related-content latest-articles">';
		$output .= '
<span class="gb-headline-shortcode">
    <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">.st1{display:none}<path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
    <span>derniers articles</span>
</span>
';
    $output .= '<div class="related-content__grid">';

    while ($latest_posts->have_posts()) {
        $latest_posts->the_post();
        $output .= '<div class="related-content__card">';
        $output .= '<div class="related-content__image">';
        $output .= '<a href="' . get_permalink() . '">' . get_the_post_thumbnail(null, 'medium') . '</a>';
        $output .= '</div>';
        $output .= '<div class="related-content__content">';
        $output .= '<span class="related-content__card-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></span>';
        //$output .= '<p class="related-content__excerpt">' . wp_trim_words(get_the_excerpt(), 20) . '</p>';
        $output .= '<span class="related-content__date">Publié le ' . get_the_date() . '</span>';
        $output .= '</div>';
        $output .= '</div>';
    }

    $output .= '</div>';
    $output .= '</div>';

    wp_reset_postdata();
    return $output;
}
add_shortcode('latest_articles', 'display_latest_articles');


// 1. Initialiser la variable globale pour stocker les IDs des articles affichés
function initialize_displayed_news_ids() {
    global $displayed_news_ids;
    if (!isset($displayed_news_ids)) {
        $displayed_news_ids = array();
    }
}
add_action('wp', 'initialize_displayed_news_ids');

// 2. Fonction utilitaire pour générer une carte de contenu (pour les articles et catégories)
function generate_content_card($image_html, $title, $link, $date = '', $category_name = '', $category_link = '', $category_class = '') {
    $output = '<div class="related-content__card">';

    if ($image_html) {
        $output .= '<div class="related-content__image">';
        $output .= '<a href="' . esc_url($link) . '">' . $image_html . '</a>';
        $output .= '</div>';
    }

    $output .= '<div class="related-content__content">';

    // Afficher la catégorie au-dessus du titre
    if ($category_name && $category_link && $category_class) {
        $output .= '<span class="post-term-item ' . esc_attr($category_class) . '">';
        $output .= '<a href="' . esc_url($category_link) . '" data-original-text="' . esc_attr($category_name) . '" style="display: -webkit-box; -webkit-line-clamp: 1; -moz-box-orient: vertical; overflow: hidden; line-height: 1.5; max-height: 1.5em; word-break: break-word; text-overflow: ellipsis;">';
        $output .= esc_html($category_name);
        $output .= '</a></span>';
    }

    $output .= '<span class="related-content__card-title"><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></span>';

    if ($date) {
        $output .= '<span class="related-content__date">Publié le ' . esc_html($date) . '</span>';
    }

    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

// 3. Fonction utilitaire pour générer une carte de produit
function generate_product_card() {
    $product = wc_get_product(get_the_ID());
    if (!$product) return '';

    $image = $product->get_image('medium');
    $name = $product->get_name();
    $price = $product->get_price_html();
    $link = get_permalink();

    $output = '<div class="related-content__card">';
    $output .= '<div class="related-content__image">';
    $output .= '<a href="' . esc_url($link) . '">' . $image . '</a>';
    $output .= '</div>';
    $output .= '<div class="related-content__content">';
    $output .= '<span class="related-content__card-title"><a href="' . esc_url($link) . '">' . esc_html($name) . '</a></span>';
    $output .= '<p class="related-content__price">' . $price . '</p>';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

// 4. Fonction utilitaire pour générer une carte personnalisée (par exemple, pour la catégorie)
function generate_content_card_custom($image_html, $title, $link) {
    return generate_content_card($image_html, $title, $link);
}

// 5. Shortcode pour les Actualités Associées
// 5. Shortcode pour les Actualités Associées
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

    // IMPORTANT : on normalise produit_associe (ça peut être int, array, WP_Post)
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

    // 4. Deux autres tests dans la même catégorie (exclure produit_associe + post courant)
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

    // Si aucun test n'a été trouvé, fallback: afficher uniquement les articles
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

        // --- TEST : étoiles + CTA Affiliz, JAMAIS le prix ---
        if ($is_test) {

            // Note globale
            $note = floatval(get_post_meta($p->ID, 'note_globale', true));
            if ($note > 0) {
                $output .= '<div class="related-content__stars">' . generate_star_rating($note) . '</div>';
            }

            // CTA Affiliz (HTML stocké en meta)
            $bouton_affiliz = get_post_meta($p->ID, 'bouton_affiliz', true);

            if (!empty($bouton_affiliz)) {
                // On l'injecte tel quel (si tu veux durcir: wp_kses avec whitelist)
                $output .= '<div class="related-content__cta related-content__cta--affiliz">' . $bouton_affiliz . '</div>';
            }

            // IMPORTANT : aucun prix n'est affiché ici
        }

        $output .= '</div></div>';
    }

    $output .= '</div></div>';
    return $output;
}
add_shortcode('associated_news', 'display_associated_news');


// 6. Shortcode pour les Dernières Actualités
function display_latest_news($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10
    ), $atts);
    $limit = intval($atts['limit']);

    // Initialiser la variable globale
    initialize_displayed_news_ids();
    global $displayed_news_ids;

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'post__not_in' => $displayed_news_ids
    );
    $latest_news = new WP_Query($args);

    $output = '<div class="related-content latest-news">';
	$output .= '
 <span class="gb-headline-shortcode">
        <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">.st1{display:none}<path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
        <span>Dernières actualités</span>
    </span>
';
    $output .= '<div class="related-content__grid">';

    if ($latest_news->have_posts()) {
        while ($latest_news->have_posts()) {
            $latest_news->the_post();
            $thumbnail = get_the_post_thumbnail(null, 'medium');
            $title = get_the_title();
            $permalink = get_permalink();
            $date = get_the_date();
            $output .= generate_content_card($thumbnail, $title, $permalink, $date);
            $displayed_news_ids[] = get_the_ID(); // Ajouter à la liste des articles affichés
        }
        wp_reset_postdata();
    }

    $output .= '</div></div>';

    // Mettre à jour la variable globale avec les nouveaux IDs affichés
    $GLOBALS['displayed_news_ids'] = $displayed_news_ids;

    return $output;
}
add_shortcode('latest_news', 'display_latest_news');

// 7. Shortcode pour les Produits Associés
function display_associated_products($atts) {
    $atts = shortcode_atts(array(
        'limit' => 6
    ), $atts);
    $limit = intval($atts['limit']);
    $current_product = wc_get_product(get_the_ID());

    if (!$current_product) {
        return ''; // Si ce n'est pas un produit, ne rien afficher
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

// 8. Shortcode pour Comparatif et Guide(s) d'achat
function display_buying_guides_and_comparisons($atts) {
    $atts = shortcode_atts(array(
        'limit' => 6 // Limiter le nombre total d'articles affichés
    ), $atts);
    $limit = intval($atts['limit']);
    $current_post_id = get_the_ID();

    // Récupérer les catégories du CPT "test"
    $test_categories = get_the_terms($current_post_id, 'categorie_test'); // Assurez-vous que 'categorie_test' est la bonne taxonomie
    if (empty($test_categories) || is_wp_error($test_categories)) {
        return ''; // Aucun contenu à afficher si pas de catégorie associée
    }

    // On récupère le premier terme de la catégorie du test
    $category = $test_categories[0];
    $category_slug = $category->slug;

    // Récupérer l'URL de l'archive de la catégorie
    $category_link = get_term_link($category, 'categorie_test');
    if (is_wp_error($category_link)) {
        $category_link = '';
    }

    // Récupérer le champ ACF 'featured' de la catégorie (qui renvoie un ID d'image)
    $featured_id = get_field('featured', 'categorie_test_' . $category->term_id);
    $featured_image = $featured_id ? wp_get_attachment_image($featured_id, 'medium') : '';

    // Limiter le nombre de guides et de comparatifs affichés
    $guides_limit = floor($limit / 2); // La moitié pour les guides d'achat
    $comparisons_limit = $limit - $guides_limit; // Le reste pour les comparatifs

    // Requête pour récupérer les guides d’achat ayant le même slug de catégorie
    $guides_args = array(
        'post_type' => 'post',
        'posts_per_page' => $guides_limit, // Limiter le nombre de guides
        'tax_query' => array(
            array(
                'taxonomy' => 'category',
                'field' => 'slug',
                'terms' => $category_slug
            ),
        ),
        'tag' => 'guide-dachat', // S'assurer que les articles sont tagués comme guide d'achat
        'post__not_in' => array($current_post_id), // Exclure l'article actuel
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $guides = new WP_Query($guides_args);

    // Requête pour récupérer les comparatifs ayant le même slug de catégorie
    $comparisons_args = array(
        'post_type' => 'test',
        'posts_per_page' => $comparisons_limit, // Limiter les comparatifs
        'tax_query' => array(
            array(
                'taxonomy' => 'categorie_test',
                'field' => 'slug',
                'terms' => $category_slug // Utilisation du même slug de catégorie
            ),
        ),
        'tag' => 'comparatif', // S'assurer que les articles sont tagués comme comparatifs
        'post__not_in' => array($current_post_id), // Exclure l'article actuel
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $comparisons = new WP_Query($comparisons_args);

	$category_title = get_field('titre', 'categorie_test_' . $category->term_id);

// Si le champ ACF 'titre' est vide, utiliser le nom de la catégorie comme fallback
$category_title = $category_title ? $category_title : $category->name;

	// Après les deux WP_Query, ajoutez une vérification
$has_content = ($guides->have_posts() || $comparisons->have_posts());

// Si aucun contenu n'est trouvé, retourner une chaîne vide
if (!$has_content && empty($featured_image)) {
    wp_reset_postdata();
    return '';
}

    // Générer la sortie HTML
    $output = '<div class="related-content guides-and-comparisons">';

	$output .= '
<span class="gb-headline-shortcode">
    <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">.st1{display:none}<path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
    <span>Comparatif et guide(s) d\'achat</span>
</span>
';

    // Ajouter la carte de l'archive de la catégorie
    if ($category_link && $featured_image) {
        $output .= generate_content_card_custom(
            $featured_image,
            $category_title,
            $category_link
        );
    }

    $output .= '<div class="related-content__grid">';

    // Ajouter les guides d'achat à la sortie
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

    // Ajouter les comparatifs à la sortie
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

    $output .= '</div></div>'; // Fin du wrapper

    wp_reset_postdata();
    return $output;
}
add_shortcode('buying_guides_and_comparisons', 'display_buying_guides_and_comparisons');


/*home carousel*/

add_filter( 'generateblocks_query_loop_args', function( $query_args, $attributes ) {
	if (
        ! is_admin() &&
        ! empty( $attributes['className'] ) &&
        strpos( $attributes['className'], 'advanced-query' ) !== false
	) {
        // pass meta_query parameter
        $query_args[ 'post_type' ] = array('post', 'test');
	}

	return $query_args;
}, 10, 2 );

function acf_publications_shortcode() {
    // Récupérer les publications du champ ACF (changer 'publications_a_afficher' par le nom de votre champ)
    $publications = get_field('publications_a_afficher', 'option');

    if (!$publications) {
        return '<p>Aucune publication sélectionnée</p>';
    }

    $output = '<div class="publications-wrapper">';

    foreach ($publications as $publication) {
        if (isset($publication['publication'])) {
            $post_id = $publication['publication']->ID;
            $post_type = get_post_type($post_id);
            $category_name = '';
            $category_link = '';

            // Si le post est de type 'post', chercher la catégorie test associée
            if ($post_type == 'post') {
                // Récupérer les catégories du post
                $post_categories = get_the_category($post_id);
                if (!empty($post_categories)) {
                    // Prendre la première catégorie du post et récupérer son slug
                    $post_category_slug = $post_categories[0]->slug;

                    // Récupérer le terme de la taxonomie 'categorie_test' qui correspond au même slug
                    $terms = get_terms(array(
                        'taxonomy' => 'categorie_test',
                        'slug' => $post_category_slug,
                        'hide_empty' => false,
                    ));

                    if (!empty($terms)) {
                        $category_name = esc_html($terms[0]->name);
                        $category_link = get_term_link($terms[0]->term_id);
                    }
                }
            }

            // Si le post est de type 'test', simplement récupérer la catégorie 'categorie_test' associée
            if ($post_type == 'test') {
                $terms = wp_get_post_terms($post_id, 'categorie_test');
                if (!empty($terms)) {
                    $category_name = esc_html($terms[0]->name);
                    $category_link = get_term_link($terms[0]->term_id);
                }
            }

            // Générer le HTML harmonisé
            $output .= '
            <div class="publication" style="background-image: url(' . get_the_post_thumbnail_url($post_id, 'medium_large') . ');">';

            // Si une catégorie existe, on l'affiche avec un lien cliquable
            if (!empty($category_name)) {
                $output .= '<a href="' . esc_url($category_link) . '" class="category-label post-term-item">' . $category_name . '</a>';
            }

            // Ajouter le lien de la publication et l'overlay avec le titre
            $output .= '
                <a href="' . get_permalink($post_id) . '" class="publication-link">
                    <div class="publication-overlay header_thumbanil_container">
                        <span class="publication-title">' . get_the_title($post_id) . '</span>
                    </div>
                </a>
            </div>';
        }
    }

    $output .= '</div>';

    return $output;
}

add_shortcode('acf_publications', 'acf_publications_shortcode');

function generate_scrollable_menu_shortcode( $atts ) {
  $atts = shortcode_atts( array(
      'menu_id' => '',
  ), $atts, 'scrollable_menu' );

  if ( empty( $atts['menu_id'] ) ) {
      return 'Veuillez spécifier un ID de menu.';
  }

  // Récupération du menu (version desktop)
  $menu = wp_nav_menu( array(
      'menu'       => $atts['menu_id'],
      'container'  => false,
      'menu_class' => 'scrollable-menu',
      'echo'       => false,
  ) );

  if ( empty( $menu ) ) {
      return 'Menu introuvable.';
  }

  ob_start();
  ?>
  <!-- Version Desktop (affichage classique avec bordure animée, etc.) -->
  <div class="desktop-menu">
    <nav class="scrollable-menu-container">
      <?php echo $menu; ?>
    </nav>
  </div>

  <?php
  // Préparation du slider pour Mobile :
  // Extraction des <li> du menu pour reconstituer des "slides" à 2 items
  preg_match_all( '/<li[^>]*>.*?<\/li>/s', $menu, $matches );
  $items = $matches[0];
  if ( empty( $items ) ) {
      return 'Aucun élément trouvé dans le menu.';
  }

  $slides = array_chunk( $items, 2 );
  $totalSlides = count( $slides );

  // Détection de la slide active côté serveur
  $activeSlide = 0;
  foreach ( $slides as $index => $slide ) {
    $slideHTML = implode( '', $slide );
    if ( strpos( $slideHTML, 'current-menu-item' ) !== false || strpos( $slideHTML, 'current_page_item' ) !== false ) {
      $activeSlide = $index;
      break;
    }
  }

  // Calcul de la translation initiale (en pourcentage) pour la version non infinie
  // Puisque nous allons insérer un clone en début, la position initiale sera décalée d'une slide
  $initialTransform = -($activeSlide * 100);
  ?>

  <!-- Version Mobile (slider tactile & infinite) -->
  <div class="mobile-menu">
    <div class="scrollable-menu-wrapper">
      <button class="scroll-arrow prev" aria-label="Faire défiler vers la gauche"><i class="fas fa-chevron-left"></i></button>
      <div class="scrollable-menu-container">
        <!-- On injecte le style inline pour positionner le slider dès le chargement -->
        <div class="slides" style="transform: translateX(<?php echo $initialTransform; ?>%);">
          <?php foreach( $slides as $slide ): ?>
            <div class="slide">
              <ul>
                <?php foreach( $slide as $li ): ?>
                  <?php echo $li; ?>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="scroll-arrow next" aria-label="Faire défiler vers la droite"><i class="fas fa-chevron-right"></i></button>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const slidesContainer = document.querySelector(".mobile-menu .slides");
      if (!slidesContainer) return;
      const prevBtn = document.querySelector(".mobile-menu .scroll-arrow.prev");
      const nextBtn = document.querySelector(".mobile-menu .scroll-arrow.next");

      // Récupération des slides originales (avant clonage)
      const originalSlides = document.querySelectorAll(".mobile-menu .slide");
      const originalTotal = <?php echo $totalSlides; ?>;

      // Clonage pour l'effet infini : clone de la première et de la dernière slide
      const firstSlide = slidesContainer.firstElementChild;
      const lastSlide = slidesContainer.lastElementChild;
      const firstClone = firstSlide.cloneNode(true);
      const lastClone = lastSlide.cloneNode(true);
      slidesContainer.insertBefore(lastClone, firstSlide);
      slidesContainer.appendChild(firstClone);

      // Nouveau total de slides (original + 2 clones)
      const totalSlides = originalTotal + 2;

      // On initialise currentSlide en tenant compte du clone ajouté en début
      let currentSlide = <?php echo $activeSlide; ?> + 1;

      // Fonction de mise à jour
      function updateSlider(withTransition = true) {
        slidesContainer.style.transition = withTransition ? "transform 0.3s ease" : "none";
        slidesContainer.style.transform = "translateX(-" + (currentSlide * 100) + "%)";
      }

      prevBtn.addEventListener("click", function() {
        currentSlide--;
        updateSlider();
      });

      nextBtn.addEventListener("click", function() {
        currentSlide++;
        updateSlider();
      });

      // Gestion du swipe tactile
      let touchStartX = 0;
      let touchEndX = 0;
      slidesContainer.addEventListener("touchstart", function(e) {
          touchStartX = e.touches[0].clientX;
      });
      slidesContainer.addEventListener("touchend", function(e) {
          touchEndX = e.changedTouches[0].clientX;
          if (touchEndX < touchStartX - 50) {
              currentSlide++;
          } else if (touchEndX > touchStartX + 50) {
              currentSlide--;
          }
          updateSlider();
      });

      // Gestion de la boucle infinie après transition
      slidesContainer.addEventListener("transitionend", function() {
        if (currentSlide === 0) {
          currentSlide = originalTotal;
          updateSlider(false);
        }
        if (currentSlide === totalSlides - 1) {
          currentSlide = 1;
          updateSlider(false);
        }
      });

      // Mise à jour initiale sans transition pour éviter le flicker
      updateSlider(false);
      // Réactivation de la transition après un court délai
      setTimeout(() => {
        slidesContainer.style.transition = "transform 0.3s ease";
      }, 50);
    });
  </script>

  <style>
  /* ================= Desktop Styles (version initiale) ================= */
  .desktop-menu .scrollable-menu-container {
    display: flex;
    overflow-x: auto;
    white-space: nowrap;
    scroll-behavior: smooth;
    padding: 0px;
    scrollbar-width: none;
  }
  .desktop-menu .scrollable-menu-container::-webkit-scrollbar {
    display: none;
  }
  .desktop-menu .scrollable-menu {
    display: flex;
    padding: 0;
    margin: 0;
    list-style: none;
    gap: 30px;
    margin: auto;
  }
  .desktop-menu .scrollable-menu li {
    flex: 0 0 auto;
    margin: 0;
  }
  .scrollable-menu a {
    text-decoration: none;
    color: #333;
    padding: 0;
    display: inline-block;
    transition: 0.3s;
  }
  .scrollable-menu a:hover {
    color: #000;
    border-radius: 5px;
  }
  /* Couleur spécifique sur l'item actif (exemple avec la classe promo_element) */
  .promo_element a,
  .desktop-menu .current-menu-item a,
  .desktop-menu .current_page_item a {
    color: #b0121c !important;
    font-weight: 700;
  }
  /* Animation de bordure au hover et affichage permanent pour l'item actif */
  .slide li a,
  .scrollable-menu li a {
    position: relative;
    display: inline-block;
    text-decoration: none;
    color: #333;
    transition: color 0.3s ease;
    padding-bottom: 5px;
  }
  .slide li a::after,
  .scrollable-menu li a::after {
    content: "";
    display: block;
    width: 0;
    height: 3px;
    background: currentColor;
    transition: width 0.3s ease;
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
  }
  .slide li a:hover::after,
  .scrollable-menu li a:hover::after,
  .slide li.current-menu-item a::after,
  .scrollable-menu li.current-menu-item a::after {
    width: 100%;
  }
  /* ================= Mobile Styles (slider tactile & infinite) ================= */
  @media (min-width: 992px) {
    .desktop-menu {
      display: block;
    }
    .mobile-menu {
      display: none;
    }
    /* Masquer les flèches sur desktop */
    .scroll-arrow {
      display: none !important;
    }
  }

  @media (max-width: 991px) {
    .desktop-menu {
      display: none;
    }
    .mobile-menu {
      display: block;
    }
    .scrollable-menu-wrapper {
      display: grid;
      grid-template-columns: 30px 1fr 30px;
      align-items: center;
      gap: 10px;
      width: 100%;
    }
    .scroll-arrow.prev {
      margin-left: 10px;
    }
    .scroll-arrow.next {
      margin-left: -6px;
    }
    .scroll-arrow {
      background: rgba(176, 18, 28, 0.7);
      border: none;
      border-radius: 50%;
      width: 28px;
      height: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 16px;
      opacity: 1;
      transition: opacity 0.3s ease;
      padding: 0;
    }
    .scroll-arrow.disabled {
      pointer-events: none;
    }
    .scrollable-menu-wrapper:hover .scroll-arrow:not(.disabled) {
      opacity: 1;
    }
    .mobile-menu .scrollable-menu-container {
      overflow: hidden;
      width: 100%;
    }
    .mobile-menu .slides {
      display: flex;
      transition: transform 0.3s ease;
    }
    .mobile-menu .slide {
      flex: 0 0 100%;
    }
    .mobile-menu .slide ul {
      display: flex;
      justify-content: space-around;
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .mobile-menu .slide ul li {
      flex: 1;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .mobile-menu .slide ul li a {
      text-decoration: none;
      color: #333;
      display: block;
      transition: color 0.3s ease;
      padding-bottom: 5px;
      width: fit-content;
    }
    .mobile-menu .slide ul li a:hover {
      color: #000;
    }
    /* Pour l'item actif sur mobile, la bordure reste affichée */
    .mobile-menu .slide ul li.current-menu-item a::after,
    .mobile-menu .slide ul li.current_page_item a::after {
      width: 100%;
    }
  }
  </style>
  <?php
  return ob_get_clean();
}
add_shortcode( 'scrollable_menu', 'generate_scrollable_menu_shortcode' );

function custom_social_share_buttons($atts)
{
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



// Deveoo

/* Page marque */
// Shortcode pour afficher les marques avec pagination
function marques_pagination_shortcode() {
    ob_start();

    // Récupérer le numéro de page depuis l'URL
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    // Utiliser le cache
    $cache_key = 'marques_valid_ids';
    $valid_marques_ids = get_transient($cache_key);

    if (false === $valid_marques_ids) {
        $valid_marques_ids = array();

        // Récupérer tous les posts et tests qui ont une marque associée
        $post_types = array('post', 'test');

        foreach ($post_types as $post_type) {
            $posts_with_marque = get_posts(array(
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => 'marque',
                        'compare' => 'EXISTS'
                    )
                )
            ));

            foreach ($posts_with_marque as $post_id) {
                $marques_associees = get_field('marque', $post_id, false);
                if ($marques_associees && is_array($marques_associees)) {
                    $valid_marques_ids = array_merge($valid_marques_ids, $marques_associees);
                }
            }
        }

        // Nettoyer et dédupliquer
        $valid_marques_ids = array_unique(array_filter($valid_marques_ids));

        if (empty($valid_marques_ids)) {
            $valid_marques_ids = array(0); // Éviter une requête vide
        }

        set_transient($cache_key, $valid_marques_ids, 12 * HOUR_IN_SECONDS);
    }

    // Arguments de la requête paginée
    $args_paged = array(
        'post_type'      => 'marque',
        'posts_per_page' => 20,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'Desc',
        'post__in'       => $valid_marques_ids
    );

    $marques_query = new WP_Query($args_paged);

    echo '<div class="marques-container">';

    if ($marques_query->have_posts()) :
        echo '<div class="marques-grid">';
        while ($marques_query->have_posts()) : $marques_query->the_post();
            $marque_id = get_the_ID();
            $marque_title = get_the_title();
            $marque_excerpt = get_the_excerpt();
            $marque_link = get_permalink();
            $marque_thumbnail = get_the_post_thumbnail($marque_id, 'medium', array(
                'class' => 'marque-image',
                'alt' => $marque_title,
                'loading' => 'lazy'
            ));

            echo '<div class="marque-item">';
            echo '<div class="item">';

            echo '<div class="marque-image">';
            echo '<a href="' . esc_url($marque_link) . '" class="marque-link">';
            if ($marque_thumbnail) {
                echo $marque_thumbnail;
            } else {
                echo '<div class="marque-placeholder">' . substr($marque_title, 0, 2) . '</div>';
            }
            echo '</a>';
            echo '</div>';

            echo '<div class="marque-content">';
            echo '<h3 class="marque-title"><a href="' . esc_url($marque_link) . '" class="marque-link">' . esc_html($marque_title) . '</a></h3>';
            if ($marque_excerpt) {
                echo '<div class="marque-excerpt">' . wp_kses_post(wp_trim_words($marque_excerpt, 18, '...')) . '</div>';
            }
            echo '</div>';

            echo '</div>';
            echo '</div>';

        endwhile;
        echo '</div>';

        // Afficher la pagination
        marques_display_pagination($marques_query);

    else :
        echo '<p>Aucune marque associée à des articles ou tests trouvée.</p>';
    endif;

    echo '</div>';

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('marques_list', 'marques_pagination_shortcode');

// Fonction pour vider le cache quand nécessaire
function clear_marques_cache() {
    delete_transient('marques_valid_ids');
}
add_action('save_post', 'clear_marques_cache');
add_action('acf/save_post', 'clear_marques_cache', 20);

// Pagination corrigée avec nombre limité de pages affichées
function marques_display_pagination($query) {
    $total_pages = $query->max_num_pages;
    $current_page = max(1, get_query_var('paged'));

    if ($total_pages <= 1) return;

    // Nombre maximum de pages à afficher dans la pagination
    $pages_to_show = 3;
    $half_pages = floor($pages_to_show / 2);

    echo '<div class="marques-pagination">';

    // Page précédente
    if ($current_page > 1) {
        echo '<a href="' . get_pagenum_link($current_page - 1) . '" class="pagination-prev">«</a>';
    }

    // Première page
    if ($current_page > $half_pages + 1) {
        echo '<a href="' . get_pagenum_link(1) . '" class="page-link">1</a>';
        if ($current_page > $half_pages + 2) {
            echo '<span class="pagination-dots">...</span>';
        }
    }

    // Pages numérotées autour de la page courante
    $start_page = max(1, $current_page - $half_pages);
    $end_page = min($total_pages, $current_page + $half_pages);

    // Ajuster si on est près du début
    if ($start_page <= 1) {
        $end_page = min($total_pages, $pages_to_show);
    }

    // Ajuster si on est près de la fin
    if ($end_page >= $total_pages) {
        $start_page = max(1, $total_pages - $pages_to_show + 1);
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            echo '<a class="current-page">' . $i . '</a>';
        } else {
            echo '<a href="' . get_pagenum_link($i) . '" class="page-link">' . $i . '</a>';
        }
    }

    // Dernière page
    if ($current_page < $total_pages - $half_pages) {
        if ($current_page < $total_pages - $half_pages - 1) {
            echo '<span class="pagination-dots">...</span>';
        }
        echo '<a href="' . get_pagenum_link($total_pages) . '" class="page-link">' . $total_pages . '</a>';
    }

    // Page suivante
    if ($current_page < $total_pages) {
        echo '<a href="' . get_pagenum_link($current_page + 1) . '" class="pagination-next">»</a>';
    }

    echo '</div>';
}
/**
 * =========================================================
 * SINGLE MARQUE — LISTING CONTENU (POST + TEST) AVEC PAGINATION
 * - Pas d'AJAX, pas d'infinite scroll
 * - Markup BEM + classes legacy (GenerateBlocks / existantes)
 * - Pagination via /marques/mova/?paged=2
 * - Pagination UL: .page-numbers
 * - Pagination LI: .page-number (+ .current sur le LI actif)
 * =========================================================
 */

/* ---------- Pagination helper: add classes to paginate_links() output ---------- */
function lm_pagination_markup_compat($links_html) {
    if (empty($links_html) || !is_string($links_html)) {
        return $links_html;
    }

    // Add class to UL
    // paginate_links(type=list) outputs: <ul class='page-numbers'> ... OR sometimes <ul>...
    if (strpos($links_html, '<ul') !== false) {
        // If UL already has class, append page-numbers if missing
        if (preg_match('#<ul[^>]*class=[\'"][^\'"]*[\'"][^>]*>#', $links_html)) {
            $links_html = preg_replace_callback(
                '#<ul([^>]*?)class=[\'"]([^\'"]*)[\'"]([^>]*?)>#',
                function ($m) {
                    $classes = trim($m[2]);
                    $class_list = preg_split('/\s+/', $classes, -1, PREG_SPLIT_NO_EMPTY);

                    if (!in_array('page-numbers', $class_list, true)) {
                        $class_list[] = 'page-numbers';
                    }

                    return '<ul' . $m[1] . 'class="' . esc_attr(implode(' ', $class_list)) . '"' . $m[3] . '>';
                },
                $links_html,
                1
            );
        } else {
            // No class attribute on ul
            $links_html = preg_replace('#<ul(\s*?)>#', '<ul class="page-numbers">', $links_html, 1);
        }
    }

    // Add class="page-number" to every LI
    if (strpos($links_html, '<li') !== false) {
        $links_html = preg_replace_callback(
            '#<li([^>]*)>#',
            function ($m) {
                $attrs = $m[1];

                if (preg_match('#class=[\'"]([^\'"]*)[\'"]#', $attrs, $cm)) {
                    $classes = trim($cm[1]);
                    $class_list = preg_split('/\s+/', $classes, -1, PREG_SPLIT_NO_EMPTY);

                    if (!in_array('page-number', $class_list, true)) {
                        $class_list[] = 'page-number';
                    }

                    // Replace existing class attr
                    $attrs = preg_replace(
                        '#class=[\'"][^\'"]*[\'"]#',
                        'class="' . esc_attr(implode(' ', $class_list)) . '"',
                        $attrs,
                        1
                    );
                } else {
                    $attrs .= ' class="page-number"';
                }

                return '<li' . $attrs . '>';
            },
            $links_html
        );
    }

    // Add .current to the LI that contains the current span
    // paginate_links renders current as: <span aria-current='page' class='page-numbers current'>2</span>
    // We add class current on the parent LI for easier styling.
    $links_html = preg_replace_callback(
        '#<li([^>]*)>(\s*)<span([^>]*?)aria-current=[\'"]page[\'"]([^>]*?)>(.*?)</span>(\s*)</li>#',
        function ($m) {
            $li_attrs = $m[1];

            if (preg_match('#class=[\'"]([^\'"]*)[\'"]#', $li_attrs, $cm)) {
                $classes = trim($cm[1]);
                $class_list = preg_split('/\s+/', $classes, -1, PREG_SPLIT_NO_EMPTY);

                if (!in_array('current', $class_list, true)) {
                    $class_list[] = 'current';
                }

                $li_attrs = preg_replace(
                    '#class=[\'"][^\'"]*[\'"]#',
                    'class="' . esc_attr(implode(' ', $class_list)) . '"',
                    $li_attrs,
                    1
                );
            } else {
                $li_attrs .= ' class="current"';
            }

            return '<li' . $li_attrs . '>' . $m[2] . '<span' . $m[3] . 'aria-current="page"' . $m[4] . '>' . $m[5] . '</span>' . $m[6] . '</li>';
        },
        $links_html
    );

    return $links_html;
}

/* ---------- SHORTCODE: [marque_content_list] ---------- */
function marque_content_list_shortcode($atts) {
    if (!is_singular('marque')) {
        return '<p>Ce shortcode doit être utilisé sur une page de marque.</p>';
    }

    $current_marque_id = get_queried_object_id();
    if (!$current_marque_id) {
        return '<p>Marque introuvable.</p>';
    }

    // Pagination robuste sur single : /marques/mova/?paged=2
    $paged = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;

    $atts = shortcode_atts(array(
        'posts_per_page' => 12,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ), $atts);

    $args = array(
        'post_type'           => array('post', 'test'),
        'posts_per_page'      => (int) $atts['posts_per_page'],
        'paged'               => $paged,
        'orderby'             => $atts['orderby'],
        'order'               => $atts['order'],
        'ignore_sticky_posts' => true,
        'meta_query'          => array(
            array(
                'key'     => 'marque',
                'value'   => '"' . $current_marque_id . '"',
                'compare' => 'LIKE',
            )
        ),
    );

    $q = new WP_Query($args);

    ob_start();

    echo '<section class="marque-content-list lm-brandListing" id="marques-list">';

    if ($q->have_posts()) {

        echo '<div class="marque-posts-list lm-brandListing__grid" id="marque-list">';

        while ($q->have_posts()) {
            $q->the_post();
            lm_brand_listing_item_compat();
        }

        echo '</div>';

        // Pagination
        if ($q->max_num_pages > 1) {
            $base_url = remove_query_arg('paged');

            $links = paginate_links(array(
                'base'      => esc_url_raw(add_query_arg('paged', '%#%', $base_url)),
                'format'    => '',
                'current'   => $paged,
                'total'     => (int) $q->max_num_pages,
                'mid_size'  => 2,
                'prev_text' => '«',
                'next_text' => '»',
                'type'      => 'list',
            ));

            $links = lm_pagination_markup_compat($links);

            echo '<nav class="marque-posts-pagination lm-brandListing__pagination" aria-label="Pagination">';
            echo $links; // safe: generated by WP + minimal string edits
            echo '</nav>';
        }

    } else {
        echo '<p class="no-content lm-brandListing__empty">Aucun contenu trouvé pour cette marque.</p>';
    }

    echo '</section>';

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('marque_content_list', 'marque_content_list_shortcode');


/* ---------- ITEM: renderer BEM + legacy (compat immédiate) ---------- */
function lm_brand_listing_item_compat() {
    $post_id   = get_the_ID();
    $post_type = get_post_type($post_id);
    $url       = get_permalink($post_id);
    $title     = get_the_title($post_id);

    // --- Term (cat/tax) ---
    $term_name = '';
    $term_link = '';
    $term_slug = '';

    if ($post_type === 'post') {
        $cats = get_the_category($post_id);
        if (!empty($cats) && !is_wp_error($cats)) {
            $term_name = $cats[0]->name;
            $term_slug = $cats[0]->slug;
            $term_link = get_category_link($cats[0]->term_id);
        }
    } else {
        $possible_taxonomies = array(
            'test_category',
            'test_cat',
            'category_test',
            'tests_category',
            'categorie_test',
            'categories_test',
        );

        foreach ($possible_taxonomies as $tax) {
            if (taxonomy_exists($tax)) {
                $terms = get_the_terms($post_id, $tax);
                if (!empty($terms) && !is_wp_error($terms)) {
                    $term_name = $terms[0]->name;
                    $term_slug = $terms[0]->slug;
                    $term_link = get_term_link($terms[0]);
                    break;
                }
            }
        }
    }

    // --- Media ---
    if (has_post_thumbnail($post_id)) {
        $thumb_id = get_post_thumbnail_id($post_id);
        $media_html = wp_get_attachment_image(
            $thumb_id,
            'medium',
            false,
            array(
                'class'    => 'query_loop_image lm-card__img',
                'loading'  => 'lazy',
                'decoding' => 'async',
            )
        );
    } else {
        $media_html = '<div class="post-list-placeholder lm-card__placeholder" aria-hidden="true">' . esc_html(mb_substr($title, 0, 2)) . '</div>';
    }

    // --- Datetime ---
    $date_str = get_the_date('d/m/Y', $post_id);
    $time_str = get_the_time('H:i', $post_id);

    $article_classes = array_merge(
        array(
            'dynamic-content-template',
            'resize-featured-image',
            'post-type-' . $post_type,
            'lm-card',
            'lm-card--' . $post_type,
        ),
        get_post_class('', $post_id)
    );

    echo '<article id="post-' . esc_attr($post_id) . '" class="' . esc_attr(implode(' ', $article_classes)) . '">';
        echo '<div class="gb-grid-column gb-grid-column-a223c2a2 gb-query-loop-item lm-card__inner">';
            echo '<div class="gb-container gb-container-a223c2a2 post_archive_container">';
                echo '<div class="gb-container gb-container-3a861d0a lm-card__media">';
                    echo '<figure class="gb-block-image gb-block-image-f0f7190a">';
                        echo '<a class="lm-card__mediaLink" href="' . esc_url($url) . '" aria-label="' . esc_attr($title) . '">';
                            echo $media_html;
                        echo '</a>';
                    echo '</figure>';
                echo '</div>';

                echo '<div class="gb-container gb-container-a84b6e14 content_container_query_loop lm-card__body">';

                    if (!empty($term_name) && !empty($term_link)) {
                        $term_class = !empty($term_slug) ? ' term-' . sanitize_html_class($term_slug) : '';
                        echo '<span class="test-category-link term_absolute post-term-item' . esc_attr($term_class) . ' lm-card__term">';
                            echo '<a class="lm-card__termLink" href="' . esc_url($term_link) . '">' . esc_html($term_name) . '</a>';
                        echo '</span> ';
                    }

                    echo '<p class="gb-headline gb-headline-659923e8 related-content__card-title related-content__card-title_bold gb-headline-text lm-card__title">';
                        echo '<a class="lm-card__titleLink" href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
                    echo '</p>';

                    echo '<span class="datetime lm-card__datetime">Publié le ' . esc_html($date_str) . ' à ' . esc_html($time_str) . '</span>';

                echo '</div>';
            echo '</div>';
        echo '</div>';
    echo '</article>';
}

/**
 * Shortcode: [qd_video]
 * ACF field: video_url (URL)
 * Usage:
 *  - With ACF: [qd_video]
 *  - Override: [qd_video url="https://www.youtube.com/embed/mzCABJVRtdI"]
 */

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

/**
 * =========================================================
 * AUTHOR — CONTENT LIST (POST + TEST) + DEBUG
 * - Pagination native archive auteur (/page/X/)
 * - Debug: injecte un objet JS + data-* sur le <nav> pour console.log()
 * =========================================================
 */
function author_content_list_shortcode($atts) {

    $atts = shortcode_atts(array(
        'author_id'      => 0,
        'posts_per_page' => 12,
        'orderby'        => 'date',
        'order'          => 'DESC',
        // mettre à true temporairement pour afficher les logs
        'debug'          => 0,
    ), $atts);

    $is_author_archive = is_author();

    // Auteur cible
    if ((int) $atts['author_id'] > 0) {
        $author_id = (int) $atts['author_id'];
    } elseif ($is_author_archive) {
        $author_id = (int) get_queried_object_id();
    } else {
        $author_id = (int) get_the_author_meta('ID');
    }
    if (!$author_id) return '';

    // Pagination : archive auteur = /page/X/ (query_var 'paged'), sinon pas de pagination
    $paged = $is_author_archive ? max(1, (int) get_query_var('paged')) : 1;

    // Query
    $q_args = array(
        'post_type'           => array('post', 'test'),
		'post_status'         => array('publish'),
        'posts_per_page'      => (int) $atts['posts_per_page'],
        'paged'               => $paged,
        'orderby'             => $atts['orderby'],
        'order'               => $atts['order'],
        'ignore_sticky_posts' => true,
        'author'              => $author_id,
        // Important en archive: évite des interférences de plugins "optimisation requêtes"
        'suppress_filters'    => false,
    );

    $q = new WP_Query($q_args);

    ob_start();

    // Wrappers identiques à marque (style safe)
    echo '<section class="marque-content-list lm-brandListing" id="marques-list">';

    if ($q->have_posts()) {

        echo '<div class="marque-posts-list lm-brandListing__grid" id="marque-list">';

        while ($q->have_posts()) {
            $q->the_post();
            lm_brand_listing_item_compat(); // NE PAS TOUCHER
        }

        echo '</div>';

        // Pagination UNIQUEMENT sur archive auteur
        if ($is_author_archive && $q->max_num_pages > 1) {

            $base = trailingslashit(get_author_posts_url($author_id)) . '%_%';

            $links = paginate_links(array(
                'base'      => $base,
                'format'    => 'page/%#%/',
                'current'   => $paged,
                'total'     => (int) $q->max_num_pages,
                'mid_size'  => 2,
                'prev_text' => '«',
                'next_text' => '»',
                'type'      => 'list',
            ));

            if (function_exists('lm_pagination_markup_compat')) {
                $links = lm_pagination_markup_compat($links);
            }

            // --- DEBUG DATA (sur <nav>) ---
            $debug_payload = array(
                'ctx'                => 'author_content_list',
                'is_author_archive'   => (bool) $is_author_archive,
                'author_id'           => (int) $author_id,
                'paged'               => (int) $paged,
                'posts_per_page'      => (int) $q->get('posts_per_page'),
                'post_type'           => $q->get('post_type'),
                'orderby'             => $q->get('orderby'),
                'order'               => $q->get('order'),
                'found_posts'         => (int) $q->found_posts,
                'post_count'          => (int) $q->post_count,
                'max_num_pages'       => (int) $q->max_num_pages,
                'request'             => (string) $q->request,
                // utile pour détecter si quelque chose injecte une contrainte / filtre
                'query_vars_diff'     => array(
                    'paged_qv'   => (int) get_query_var('paged'),
                    'paged_get'  => isset($_GET['paged']) ? (int) $_GET['paged'] : null,
                    'page_qv'    => (int) get_query_var('page'),
                    'pagename'   => (string) get_query_var('pagename'),
                ),
            );

            $debug_json = wp_json_encode($debug_payload);

            echo '<nav class="marque-posts-pagination lm-brandListing__pagination" aria-label="Pagination"'
                . ' data-lm-debug=\'' . esc_attr($debug_json) . '\''
                . '>';

            echo $links;
            echo '</nav>';

            // --- DEBUG JS (console.log) ---
            if (!empty($atts['debug'])) {
                echo "<script>
document.addEventListener('DOMContentLoaded', function () {
  var nav = document.querySelector('nav.marque-posts-pagination[data-lm-debug]');
  if (!nav) return;
  try {
    var data = JSON.parse(nav.getAttribute('data-lm-debug'));
    console.group('[LM DEBUG] author_content_list pagination');
    console.log('DATA:', data);
    console.log('TOTAL PAGES (max_num_pages):', data.max_num_pages);
    console.log('FOUND POSTS:', data.found_posts, 'POST COUNT on this page:', data.post_count);
    console.log('REQUEST SQL:', data.request);
    console.log('TIP: si max_num_pages est trop haut, cherche un pre_get_posts global ou un filtre qui change posts_per_page / post_type.');
    console.groupEnd();
  } catch (e) {
    console.error('[LM DEBUG] JSON parse failed', e);
  }
});
</script>";
            }
        }

    } else {
        echo '<p class="no-content lm-brandListing__empty">Aucun contenu trouvé pour cet auteur.</p>';
    }

    echo '</section>';

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('author_content_list', 'author_content_list_shortcode');

/**
 * Fix 404 pagination sur archives auteur :
 * aligne le main query (celui qui décide du 404) sur le listing attendu.
 */
add_action('pre_get_posts', function($q) {
    if (is_admin() || !$q->is_main_query() || !$q->is_author()) return;

    $q->set('post_type', array('post', 'test'));
    $q->set('posts_per_page', 12);
    $q->set('orderby', 'date');
    $q->set('order', 'DESC');

    // CRITIQUE : sinon main query et shortcode ne comptent pas les mêmes posts
    $q->set('post_status', array('publish')); // on EXCLUT private + acf-disabled
    $q->set('ignore_sticky_posts', true);
}, 9);



/**
 * LM — Redirect ?_page=N vers /comparatifs/page/N/
 */
function lm_redirect_legacy_page_param() {

    if (!is_page('comparatifs')) {
        return;
    }

    if (!isset($_GET['_page'])) {
        return;
    }

    $page = (int) $_GET['_page'];

    if ($page < 2) {
        wp_safe_redirect(home_url('/comparatifs/'), 301);
        exit;
    }

    wp_safe_redirect(
        home_url("/comparatifs/page/{$page}/"),
        301
    );
    exit;
}
add_action('template_redirect', 'lm_redirect_legacy_page_param');

/**
 * LM — Rewrite pagination page /comparatifs/page/N/
 */
function lm_register_comparatifs_pagination_rewrite() {
    add_rewrite_rule(
        '^comparatifs/page/([0-9]+)/?$',
        'index.php?pagename=comparatifs&paged=$matches[1]',
        'top'
    );
}
add_action('init', 'lm_register_comparatifs_pagination_rewrite');



/**
 * LM — Affiche le contenu uniquement sur la page 1 d’une page paginée
 */
function lm_seo_only_first_shortcode($atts, $content = null) {

    if (is_admin()) {
        return '';
    }

    // Page courante de pagination (0 ou 1 = page 1)
    $paged = max(1, get_query_var('paged'));

    if ($paged > 1) {
        return '';
    }

    return do_shortcode($content);
}
add_shortcode('lm_seo_only_first', 'lm_seo_only_first_shortcode');
/**
 * LM — Grille de terms (ACF) avec pagination SEO propre
 */
function lm_terms_grid_shortcode($atts) {

    $atts = shortcode_atts([
        'taxonomy'  => 'categorie_test',
        'per_page'  => 12,
        'base_slug' => 'comparatifs',
    ], $atts);

    $taxonomy  = $atts['taxonomy'];
    $per_page  = (int) $atts['per_page'];
    $base_slug = trim($atts['base_slug'], '/');

    // Pagination
    $paged  = max(1, get_query_var('paged'));
    $offset = ($paged - 1) * $per_page;

    // Total des terms
    $total_terms = wp_count_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ]);

    if (is_wp_error($total_terms) || $total_terms === 0) {
        return '';
    }

    $total_pages = (int) ceil($total_terms / $per_page);

    // Récupération des terms
    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'number'     => $per_page,
        'offset'     => $offset,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        return '';
    }

    ob_start();
    ?>

    <section class="lm-terms">
        <div class="lm-terms-grid">

            <?php foreach ($terms as $term) :

                // ===== ACF TERM FIELDS =====
                $acf_content = get_field('contenu', $term);
                $acf_image   = get_field('featured', $term); // ID attendu

                $image_url = $acf_image
                    ? wp_get_attachment_image_url($acf_image, 'medium_large')
                    : '';

                $image_alt = $acf_image
                    ? get_post_meta($acf_image, '_wp_attachment_image_alt', true)
                    : '';

                $term_link = get_term_link($term);
            ?>

                <article class="lm-card">
                    <a class="lm-card-link" href="<?php echo esc_url($term_link); ?>">

                        <?php if ($image_url) : ?>
                            <div class="lm-card-media">
                                <img
                                    src="<?php echo esc_url($image_url); ?>"
                                    alt="<?php echo esc_attr($image_alt ?: $term->name); ?>"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>
                        <?php endif; ?>

                        <div class="lm-card-content">
                            
                                <h3 class="lm-card-title">
                                    <?php echo esc_html($term->name); ?>
                                </h3>
                           

                            <?php if ($acf_content) : ?>
                                <p class="lm-card-excerpt">
                                    <?php echo esc_html(wp_trim_words(strip_tags($acf_content), 32)); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                    </a>
                </article>

            <?php endforeach; ?>

        </div>

        <?php if ($total_pages > 1) : ?>
            <nav class="lm-pagination" aria-label="Pagination comparatifs">
                <ul class="lm-pagination-list">

                    <?php for ($i = 1; $i <= $total_pages; $i++) :
                        $url = ($i === 1)
                            ? home_url("/{$base_slug}/")
                            : home_url("/{$base_slug}/page/{$i}/");
                    ?>
                        <li class="lm-pagination-item <?php echo ($i === $paged) ? 'is-active' : ''; ?>">
                            <a href="<?php echo esc_url($url); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                </ul>
            </nav>
        <?php endif; ?>

    </section>

    <?php
    return ob_get_clean();
}
add_shortcode('lm_terms_grid', 'lm_terms_grid_shortcode');

/**
 * Titre carte test = "{marque} {nom}" (ACF) sinon fallback = titre WP
 */
function lm_get_test_card_title(int $post_id): string
{
    // Fallback WP
    $fallback = (string) get_the_title($post_id);

    if (!function_exists('get_field')) {
        return $fallback;
    }

    $nom = trim((string) get_field('nom', $post_id));

    // ACF relationship : peut être array de posts, ou un post, ou un ID selon réglages
    $marque = get_field('marque', $post_id);
    $marque_title = '';

    if (!empty($marque)) {
        // Cas 1: array (relationship multiple)
        if (is_array($marque)) {
            $first = reset($marque);

            if (is_object($first) && isset($first->ID)) {
                $marque_title = (string) get_the_title((int) $first->ID);
            } elseif (is_numeric($first)) {
                $marque_title = (string) get_the_title((int) $first);
            }
        }
        // Cas 2: objet post (relationship single)
        elseif (is_object($marque) && isset($marque->ID)) {
            $marque_title = (string) get_the_title((int) $marque->ID);
        }
        // Cas 3: ID (relationship return = ID)
        elseif (is_numeric($marque)) {
            $marque_title = (string) get_the_title((int) $marque);
        }
    }

    $title = trim(trim($marque_title) . ' ' . $nom);

    // Si tout est vide, fallback WP
    return $title !== '' ? $title : $fallback;
}


/**
 * Shortcode: [lm_tests_grid]
 */
function lm_tests_grid_shortcode($atts) {

    $atts = shortcode_atts([
        'post_type' => 'test',
        'per_page'  => 12,
        'mode'      => 'auto', // auto | latest
        'taxonomy'  => '',
        'term_id'   => '',
        'term_slug' => '',
        'auto'      => '1',    // 1 = auto contexte archive, 0 = désactiver
        'base_slug' => '',
    ], $atts);

    $post_type = sanitize_key($atts['post_type']);
    $per_page  = max(1, (int) $atts['per_page']);
    $mode      = sanitize_key($atts['mode']);
    $auto      = ($atts['auto'] === '1' || $atts['auto'] === 1 || $atts['auto'] === true);
    $paged     = max(1, (int) get_query_var('paged'));

    $has_acf = function_exists('get_field');

    // Term filter (si pas latest)
    $tax_query = [];

    if ($mode !== 'latest') {

        $manual_taxonomy = (!empty($atts['taxonomy']) && (!empty($atts['term_id']) || !empty($atts['term_slug'])));

        if ($manual_taxonomy) {

            $taxonomy = sanitize_key($atts['taxonomy']);
            $term = null;

            if (!empty($atts['term_id'])) {
                $term = get_term((int) $atts['term_id'], $taxonomy);
            } else {
                $term = get_term_by('slug', sanitize_title($atts['term_slug']), $taxonomy);
            }

            if ($term && !is_wp_error($term)) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => [(int) $term->term_id],
                ];
            }

        } elseif ($auto && (is_category() || is_tag() || is_tax())) {

            $queried = get_queried_object();
            if ($queried && !empty($queried->taxonomy) && !empty($queried->term_id)) {
                $tax_query[] = [
                    'taxonomy' => $queried->taxonomy,
                    'field'    => 'term_id',
                    'terms'    => [(int) $queried->term_id],
                ];
            }
        }
    }

    $args = [
    'post_type'           => $post_type,
    'posts_per_page'      => $per_page,
    'paged'               => $paged,
    'ignore_sticky_posts' => true,
    'post_status'         => 'publish',

    // Tri par note_globale (ACF)
    'meta_key'            => 'note_globale',
    'orderby'             => [
        'meta_value_num' => 'DESC',
        'date'           => 'DESC', // tie-breaker propre
    ],

    // Exclure les posts sans note + forcer NUMERIC
    'meta_query'          => [
        [
            'key'     => 'note_globale',
            'compare' => 'EXISTS',
        ],
        [
            'key'     => 'note_globale',
            'value'   => '',
            'compare' => '!=',
        ],
        [
            'key'     => 'note_globale',
            'type'    => 'NUMERIC',
        ],
    ],
];



    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    $q = new WP_Query($args);

    if (!$q->have_posts()) {
        wp_reset_postdata();
        return '';
    }

    $base_slug = trim((string) $atts['base_slug'], '/');
    $is_page_pagination = (!empty($base_slug) && is_page());

    ob_start();
    ?>
    <section class="lm-tests">
        <div class="lm-tests-grid">

            <?php while ($q->have_posts()) : $q->the_post(); ?>
                <?php
                    $post_id   = get_the_ID();
                    $permalink = get_permalink($post_id);

                    // ACF
                    $nom            = $has_acf ? (string) get_field('nom', $post_id) : '';
                    $note_globale    = $has_acf ? get_field('note_globale', $post_id) : '';
                    $bouton_affiliz  = $has_acf ? (string) get_field('bouton_affiliz', $post_id) : '';

                    $title = lm_get_test_card_title($post_id);

                    // Image
                    $img_url = get_the_post_thumbnail_url($post_id, 'medium_large');

                    // Excerpt
                    $excerpt = get_the_excerpt($post_id);
                    if (!$excerpt) {
                        $excerpt = wp_trim_words(wp_strip_all_tags(get_the_content(null, false, $post_id)), 24);
                    }

                    // Stars HTML (ne pas appeler une fonction inexistante)
                    $stars_html = '';
                    if (!empty($note_globale)) {
                        if (function_exists('lm_generate_star_rating')) {
                            $stars_html = lm_generate_star_rating($note_globale);
                        } elseif (function_exists('generate_star_rating')) {
                            $stars_html = generate_star_rating($note_globale);
                        }
                    }
                ?>

                <article class="lm-test-card">
                    <div class="lm-test-card-inner">

                        <!-- Overlay global : toute la carte mène au test -->
                        <a class="lm-test-card-overlay"
                           href="<?php echo esc_url($permalink); ?>"
                           aria-label="<?php echo esc_attr($title); ?>"></a>

                        <?php if (!empty($stars_html)) : ?>
                            <div class="lm-test-card-stars">
                                <?php echo $stars_html; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($img_url) : ?>
                            <a class="lm-test-card-media-link" href="<?php echo esc_url($permalink); ?>">
                                <div class="lm-test-card-media">
                                    <img
                                        src="<?php echo esc_url($img_url); ?>"
                                        alt="<?php echo esc_attr($title); ?>"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </div>
                            </a>
                        <?php endif; ?>

                        <div class="lm-test-card-content">

                            <h3 class="lm-test-card-title">
                                <a class="lm-test-card-title-link" href="<?php echo esc_url($permalink); ?>">
                                    <?php echo esc_html($title); ?>
                                </a>
                            </h3>

                            <?php if ($excerpt) : ?>
                                <p class="lm-test-card-excerpt"><?php echo esc_html($excerpt); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($bouton_affiliz)) : ?>
                                <div class="lm-test-card-cta">
                                    <!-- IMPORTANT: si Affilizz renvoie du HTML (a/button), on le laisse tel quel -->
                                    <div class="affilizz-container"><?php echo $bouton_affiliz; ?></div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </article>

            <?php endwhile; ?>

        </div>

        <?php
        // Pagination inchangée
        $total_pages = (int) $q->max_num_pages;

        if ($total_pages > 1) {

            if ($is_page_pagination) {
                $base   = home_url("/{$base_slug}/%_%");
                $format = 'page/%#%/';

                $links = paginate_links([
                    'base'      => $base,
                    'format'    => $format,
                    'current'   => $paged,
                    'total'     => $total_pages,
                    'mid_size'  => 2,
                    'end_size'  => 1,
                    'prev_text' => '← précédent',
                    'next_text' => '<span aria-hidden="true">→</span> suivant',
                    'type'      => 'array',
                ]);
            } else {
                $big = 999999;
                $links = paginate_links([
                    'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                    'format'    => '',
                    'current'   => $paged,
                    'total'     => $total_pages,
                    'mid_size'  => 2,
                    'end_size'  => 1,
                    'prev_text' => '← précédent',
                    'next_text' => '<span aria-hidden="true">→</span> suivant',
                    'type'      => 'array',
                ]);
            }

            if (!empty($links) && is_array($links)) {

                echo '<nav class="lm-pagination" aria-label="Pagination">';
                echo '<ul class="lm-pagination-list">';

                foreach ($links as $link_html) {

                    $is_current = (strpos($link_html, 'current') !== false);
                    $li_class = 'lm-pagination-item' . ($is_current ? ' is-active' : '');

                    $link_html = preg_replace('/>(\d+)</', '><span class="screen-reader-text">Page</span>$1<', $link_html);
                    $link_html = str_replace('class="', 'class="lm-page-numbers ', $link_html);

                    echo '<li class="' . esc_attr($li_class) . '">';
                    echo $link_html;
                    echo '</li>';
                }

                echo '</ul>';
                echo '</nav>';
            }
        }
        ?>

    </section>
    <?php

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('lm_tests_grid', 'lm_tests_grid_shortcode');


/**
 * LM — Rewrite pagination page /dernier-test/page/N/
 */
function lm_register_dernier_test_pagination_rewrite() {
    add_rewrite_rule(
        '^dernier-test/page/([0-9]+)/?$',
        'index.php?pagename=dernier-test&paged=$matches[1]',
        'top'
    );
}
add_action('init', 'lm_register_dernier_test_pagination_rewrite');
/**
 * LM — Pagination taxonomie custom: /cuisine/{term-path}/page/N/
 * Taxonomie: categorie_test
 */
function lm_register_categorie_test_pagination_rewrite() {

    add_rewrite_rule(
        '^cuisine/(.+?)/page/([0-9]+)/?$',
        'index.php?categorie_test=$matches[1]&paged=$matches[2]',
        'top'
    );
}
add_action('init', 'lm_register_categorie_test_pagination_rewrite');

/**
 * LM — Fix 404 on last paged archive pages without breaking page 1.
 * Only adjust main query when paged > 1 (URLs /page/N/).
 */
add_action('pre_get_posts', function (WP_Query $q) {

    if (is_admin() || !$q->is_main_query()) {
        return;
    }

    // On ne touche PAS la page 1, uniquement les /page/N/
    $paged = (int) get_query_var('paged');
    if ($paged < 2) {
        return;
    }

    // Archives concernées : catégories + taxos custom
    $is_target_archive =
        is_category()
        || is_tax('categorie_test')
        || is_tax('etiquette-test');

    if (!$is_target_archive) {
        return;
    }

    // Doit matcher ton shortcode (per_page=12)
    $q->set('posts_per_page', 12);

    // Sécurité
    $q->set('post_status', 'publish');
    $q->set('ignore_sticky_posts', true);

}, 20);

/**
 * Rend UNE carte test avec le markup IDENTIQUE à celui de [related_articles]
 * (image + badge categorie_test + titre + étoiles + CTA Affiliz)
 */
function lm_render_related_test_card(int $test_id): string
{
    if ($test_id <= 0) return '';

    $permalink = get_permalink($test_id);
    if (!$permalink) return '';

    $thumb = get_the_post_thumbnail($test_id, 'thumbnail');
    $title = get_the_title($test_id);

    // Helper badge categorie_test (copié de ton shortcode)
    $terms = get_the_terms($test_id, 'categorie_test');
    $badge = '';
    if (!empty($terms) && !is_wp_error($terms)) {
        $t = $terms[0];
        $link = get_term_link($t, 'categorie_test');
        if (!is_wp_error($link)) {
            $badge = '<span class="post-term-item term-' . esc_attr($t->slug) . '">'
                . '<a href="' . esc_url($link) . '">' . esc_html($t->name) . '</a>'
                . '</span>';
        }
    }

    $out  = '<div class="related-content__card test type-test">';

    $out .= '<div class="related-content__image">';
    $out .= '<a href="' . esc_url($permalink) . '">' . $thumb . '</a>';
    $out .= '</div>';

    $out .= '<div class="related-content__content">';

    // Badge au-dessus du titre
    if ($badge !== '') {
        $out .= $badge;
    }

    // Titre
    $out .= '<span class="related-content__card-title"><a href="' . esc_url($permalink) . '">'
        . esc_html($title) . '</a></span>';

    // Étoiles
    $note = function_exists('get_field') ? get_field('note_globale', $test_id) : '';
    if (!empty($note) && function_exists('generate_star_rating')) {
        $out .= '<div class="related-content__stars">' . generate_star_rating($note) . '</div>';
    }

    // CTA Affiliz (HTML brut)
    $bouton_affiliz = get_post_meta($test_id, 'bouton_affiliz', true);
    if (!empty($bouton_affiliz)) {
        $out .= '<div class="related-content__cta related-content__cta--affiliz">' . $bouton_affiliz . '</div>';
    }

    $out .= '</div></div>';

    return $out;
}

/**
 * Shortcode: [produits_populaires]
 * Liste 6 posts "test" triés par date de publication (DESC)
 */
function lm_produits_populaires_shortcode($atts): string
{
    $atts = shortcode_atts([
        'post_type' => 'test',
        'limit'     => 6,
        'orderby'   => 'date',
        'order'     => 'DESC',
        'title'     => 'Produits populaires',
    ], $atts);

    $post_type = sanitize_key($atts['post_type']);
    $limit     = max(1, (int) $atts['limit']);
    $orderby   = sanitize_key($atts['orderby']);
    $order     = (strtoupper((string) $atts['order']) === 'ASC') ? 'ASC' : 'DESC';
    $title     = (string) $atts['title'];

    $q = new WP_Query([
    'post_type'           => $post_type,
    'posts_per_page'      => $limit,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,

    // Tri principal: post_views_7d (numérique)
    'meta_key'            => 'post_views_7d',
    'orderby'             => [
        'meta_value_num' => 'DESC',
        'modified'       => 'DESC',
    ],
    'order'               => 'DESC',

    // IMPORTANT: inclure aussi les posts sans meta => jamais vide
    'meta_query'          => [
        'relation' => 'OR',
        [
            'key'     => 'post_views_7d',
            'compare' => 'EXISTS',
        ],
        [
            'key'     => 'post_views_7d',
            'compare' => 'NOT EXISTS',
        ],
    ],
]);


    if (!$q->have_posts()) {
        wp_reset_postdata();
        return '';
    }

    $out  = '<div class="related-content related-articles">';

    // Headline : tu peux garder EXACTEMENT le même markup si tu veux
    $out .= '<span class="gb-headline-shortcode">
        <span class="gb-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path d="M0 0h48v48H0V0z"></path><path d="M7.981 40.019h32.038V7.981H7.981v32.038z"></path></svg></span>
        <span>' . esc_html($title) . '</span>
    </span>';

    // Grid : même classes pour réutiliser le CSS existant
    $out .= '<div class="related-content__grid mixed_list_homepage">';

    while ($q->have_posts()) {
        $q->the_post();
        $pid = (int) get_the_ID();

        $out .= lm_render_related_test_card($pid);
    }

    $out .= '</div></div>';

    wp_reset_postdata();
    return $out;
}
add_shortcode('produits_populaires', 'lm_produits_populaires_shortcode');



/**
 * LM — Kill legacy WP Grid Builder pagination (?_page=N)
 * Returns 410 Gone (legacy URLs removed).
 */


add_action('template_redirect', function () {
    // WP Grid Builder legacy param
    if (!isset($_GET['_page'])) {
        return;
    }

    $p = wp_unslash($_GET['_page']);

    // Si ce n'est pas un entier positif, on ignore (évite de casser d'autres usages)
    if (!is_scalar($p) || !preg_match('/^\d+$/', (string) $p)) {
        return;
    }

    // Optionnel : ne tuer que si > 1 (page 1 est inutile et souvent équivalente à l'URL propre)
    if ((int) $p <= 1) {
        // soit 410 aussi (strict), soit redirect vers l'URL sans query (plus "soft")
        // Je recommande: 410 aussi pour neutraliser définitivement l'ancien format.
    }

    status_header(410);
    nocache_headers();

    // Optionnel: petit body (pas obligatoire)
    echo '410 Gone';
    exit;
}, 0);