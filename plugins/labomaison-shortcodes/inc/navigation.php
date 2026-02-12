<?php
/**
 * Navigation Shortcodes
 *
 * Shortcodes for menus, TOC, and navigation elements.
 *
 * @package Labomaison
 * @subpackage Shortcodes
 * @since 2.0.0
 *
 * Shortcodes:
 * - [scrollable_menu]    - Desktop/mobile scrollable navigation menu with slider
 * - [dynamic_toc]        - Static table of contents for test post type
 * - [acf_publications]   - ACF-powered publications display
 *
 * Dependencies: ACF (for acf_publications), WordPress menus
 * Load Priority: 6
 * Risk Level: MEDIUM
 *
 * Migrated from: shortcode_list.php
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* =========================================================================
 * 1. Scrollable Menu Shortcode [scrollable_menu]
 *    Migrated from: shortcode_list.php L2801-3131
 * ========================================================================= */

if ( ! function_exists( 'generate_scrollable_menu_shortcode' ) ) {
function generate_scrollable_menu_shortcode( $atts ) {
  wp_enqueue_style('font-awesome');
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
}
add_shortcode( 'scrollable_menu', 'generate_scrollable_menu_shortcode' );

/* =========================================================================
 * 2. Static Table of Contents [dynamic_toc]
 *    Migrated from: shortcode_list.php L1550-1577
 * ========================================================================= */

if ( ! function_exists( 'insert_static_toc' ) ) {
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
}
add_shortcode('dynamic_toc', 'insert_static_toc');

/* =========================================================================
 * 3. ACF Publications Shortcode [acf_publications]
 *    Migrated from: shortcode_list.php L2726-2799
 * ========================================================================= */

if ( ! function_exists( 'acf_publications_shortcode' ) ) {
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
}
add_shortcode('acf_publications', 'acf_publications_shortcode');
