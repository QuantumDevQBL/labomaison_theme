## Audit CSS – `Labomaison/style.css`

### Contexte général

Le fichier [`Labomaison/style.css`](./style.css) contient ~3 850 lignes mêlant :
- styles GeneratePress copiés/collés,
- surcouches spécifiques (tests, fiches produits, marques, filtres, Affilizz, WP Grid Builder),
- nombreux doublons de sélecteurs et de media queries,
- une migration progressive vers une convention BEM (`.lm-*`).

L’objectif de ce document est de lister les principaux doublons/redondances, de proposer des regroupements par fonction, et d’identifier les media queries à consolider.

---

## 1. Sélecteurs en doublon

### 1.1. Doublons exacts ou quasi-exacts

| Sélecteur / bloc | Occurrences (lignes approx. dans `style.css`) | Observations | Recommandation |
| --- | --- | --- | --- |
| `/* GeneratePress Site CSS */` (bloc complet) | ~954–1072 et ~1877–1991 | Deux blocs très similaires (mêmes sélecteurs : `.site-description`, `.site, .box-shadow`, `.button, .submit`, `.sidebar.is-right-sidebar`, `body:not(.single) .post-term-item`, etc.), avec de légères variations sur la partie archive. | Garder une seule version de ce bloc (idéalement la plus récente) et supprimer l’autre. Centraliser ces styles en début ou fin de fichier. |
| `.comparaison-container` et `.comparaison-container .test` | ~922–932 et ~1845–1855 | Styles de la page de comparaison répétés à l’identique (flex, `justify-content: space-evenly`, `.test` en colonne centrée). | Fusionner les deux définitions dans un seul bloc placé dans une section « comparison page ». |
| `.post_loop_container .gb-grid-wrapper`, `.gb-grid-wrapper`, `.post_loop_container .gb-grid-column`, `.gb-grid-wrapper > .gb-grid-column-fff0051c` | ~937–952 et ~1860–1875 | Bloc de configuration de grille GenerateBlocks dupliqué (même largeur 49%, mêmes `margin-left: 0`, `justify-content: space-between`). | Conserver un seul bloc et supprimer le doublon. |
| `.post-term-item.term-electromenager` | ~1077–1079 et ~1996–1998 | Même couleur de fond spécifique (#ff5b33) définie deux fois. | Garder une seule déclaration. |
| `.gb-container-c3d101a4, .gb-container-38f7095b, .gb-container-d9fb1e26` | ~1111–1125 et ~2052–2065 | Même groupe de conteneurs (flex, `border-radius: 8px`, `background-size: cover`, `justify-content: end`, `align-items: flex-end`) répété. | Mutualiser dans un seul bloc. |
| `.wp-block-search__button.has-small-font-size.has-icon.wp-element-button` | ~909–918 et ~1793–1798 | Styles identiques (flex, `justify-content: center`, `border-radius: 8px`, `background-color: #f3f3f3`). | Garder une seule définition, proche d’une section « navigation/recherche ». |
| `.related-news__item` (base + variantes responsive) | ~1289–1306, 1347–1356, 1972–1983 | Règles similaires pour largeur/hauteur de cartes related news répétées dans plusieurs blocs media (50%, 100%, flex-basis, etc.). | Centraliser les règles communes (layout, radius, positionnement) puis ne laisser dans les media queries que les surcharges strictement nécessaires. |
| `.header_thumbanil_container` | ~1099–1109 et combiné avec `.header_thumbnail_container` ~2027–2037 | Bloc très proche (même layout flex, même `background-color`, mêmes border-radius) défini deux fois, avec une variante incluant `.header_thumbnail_container`. | Uniformiser : un seul bloc pour `.header_thumbanil_container, .header_thumbnail_container` avec propriétés communes. |

### 1.2. Sélecteurs identiques avec variations (overrides implicites)

| Sélecteur | Occurrences (lignes approx.) | Différences | Recommandation |
| --- | --- | --- | --- |
| `.post-term-item` | ~1081–1086, ~1361–1369, ~2001–2009 | Plusieurs définitions successives ajoutent/écrasent `padding`, `border-radius`, `width`, `position` et `background-color` (avec `!important`). | Extraire un style de base unique (`.post-term-item`), puis décliner des variantes `.post-term-item--archive`, `.post-term-item--pill`, etc. pour les cas spécifiques (position absolue, size). |
| `.inside-article` | ~875–878, ~1092–1096, ~2015–2019, ~2309–2312 | Plusieurs blocs modifient à répétition les `padding-left/right` (0, puis 20px, puis 10px). | Décider du padding par contexte (global vs `.single-marque`, `.separate-containers`) et limiter à 1–2 définitions clairement scopées (par ex. `.single .inside-article`). |
| `.site-main` | ~2256–2259 et ~2287–2290, plus overrides par page (`.page-id-99 .site-main`, `.blog .site-main`, `.home .site-main`) | Valeurs de padding différentes, parfois doublées (`padding: 30px` puis `padding: 40px 20px`) et overrides par template. | Définir une hiérarchie claire : 1 style global `.site-main`, puis variantes par template (`.home .site-main`, `.single-test .site-main`) dans une section dédiée « layout ». |
| `.content_container_query_loop` | ~1409–1412, ~3000–3003, ~3710–3719 | Même composant de contenu pour Query Loop défini avec des variantes d’alignement (column vs row, `justify-content` différents). | Définir un style de base (direction, gaps) puis gérer les variations via modifier classes (`.content_container_query_loop--brand`, `--archive`, `--home`). |
| `.gb-icon svg` | ~2400–2403 et ~2470–2472 | Deux définitions consécutives pour le même sélecteur : une met `background: currentColor`, l’autre `background: transparent`. | Choisir un comportement par défaut clair (probablement `background: transparent`) et ne définir l’autre qu’avec une classe utilitaire séparée. |
| `.related-news__content` | ~1261–1265 et ~1308–1316 | D’abord simple bloc de contenu (padding, align-items), puis redéfini en overlay (`position: absolute`, `background: rgba(0,0,0,.5)`, `height: 50%`). | Scinder en deux composants explicites (`.related-news__content` vs `.related-news__overlay`) pour éviter une double signification du même sélecteur. |

---

## 2. Propriétés redondantes / contradictoires

### 2.1. Redondances au sein d’un même bloc

- **`.pros-cons-container_shortcode .square-box` (~714–729)**
  - `background-color` est défini deux fois (`#f3f3f3` puis `#fff`), la seconde valeur écrase systématiquement la première.
  - **Action** : supprimer la valeur intermédiaire inutile ou la déplacer vers une variante `.square-box--muted`.

- **`.sous-notes` (~573–579)**
  - `flex-direction: column;` puis immédiatement `flex-direction: row;` dans le même bloc.
  - **Action** : garder une seule valeur ici (probablement `row`) et utiliser une media query ou une classe modificateur pour la variante colonne.

- **`.article-card .article-thumbnail` (~1618–1627)**
  - `flex-basis: 40%` et `background-size: cover` apparaissent deux fois dans le même bloc.
  - **Action** : supprimer les duplications directes pour alléger le CSS.

- **`.gb-headline-shortcode` (~3047–3067)**
  - De nombreuses propriétés sont répétées : `display`, `font-size`, `text-transform`, `color` (plusieurs lignes redondantes).
  - **Action** : regrouper les déclarations pour chaque propriété en une seule ligne, clarifier l’ordre et éliminer les doublons.

- **`.toggle-filters-button, .close-filters-button, .facet-block .wpgb-reset button` (~3319–3332, ~3416–3421, ~3463–3468)**
  - Trois blocs répètent quasiment les mêmes propriétés (background, padding, font-size, font-weight) avec des valeurs proches.
  - **Action** : définir un style unique pour les boutons de filtre et n’exposer que les vraies différences (taille sur mobile, largeur, etc.).

### 2.2. Redondances / conflits entre blocs proches

- **Badges et pills (`.post-term-item`, `.related-news__term-link`, `.test-category-link`, `.term_absolute`, etc.)**
  - Beaucoup de styles répètent `color: #fff !important`, `background-color: #343a40 !important`, `padding: 5px 10px !important`, `border-radius: 3px`, `font-size: 10px !important`.
  - **Impact** : difficile de savoir quel style s’applique vraiment dans chaque contexte (archive, sidebar, carte, listing marque).
  - **Action** : créer des composants réutilisables (`.badge`, `.badge--dark`, `.badge--small`) et remplacer les redéfinitions éparses.

- **Cartes d’articles et de related content (`.card`, `.related-articles__card`, `.related-content__card`, `.article-card`, `.post_archive_container`)**
  - Tous définissent des layouts très similaires (flex, gaps, radius, box-shadow, min-height).
  - **Action** : définir une base commune `.lm-card` (déjà amorcée) avec variants (`--compact`, `--overlay`) et faire en sorte que les anciens sélecteurs pointent vers ces styles (ou soient progressivement remplacés).

- **`img` et images mises en carte**
  - `.related-content__image img` est défini plusieurs fois (taille, `border-radius`), puis un bloc générique `img[class*="wp-image"]` gère aussi dimension et ratio.
  - **Action** : définir des règles globales pour les images (ratio, block, max-width) puis sur-spécifier seulement ce qui est propre aux cartes.

- **Navigation / header (`.inside-navigation.grid-container`, `.menu-bar-item a`, `.wp-block-navigation-item__content`, etc.)**
  - Le même composant de navigation est ajusté à plusieurs endroits (padding, display, align-items) pour desktop et mobile.
  - **Action** : centraliser ces réglages dans une section « navigation » avec une logique mobile-first, en limitant les overrides.

---

## 3. Regroupements possibles par fonction

### 3.1. Layout global

- **Candidats au regroupement** :
  - `.site-main`, `.archive .site-main`, `.page-id-99 .site-main`, `.blog .site-main`, `.home .site-main`, `.single-test .site-main`, `.archive .site-content`, `.sidebar.is-right-sidebar`, `.inside-right-sidebar`.
- **Idée de structure** :
  - Regrouper ces règles dans une section ou un fichier dédié (par ex. `layout.css`) avec une hiérarchie claire : global → templates → cas particuliers.
  - Limiter les redéfinitions contradictoires de `padding` et de `background-color`.

### 3.2. Navigation / header

- **Sélecteurs concernés** :
  - `.inside-navigation.grid-container`, `.navigation-branding`, `.menu-bar-items`, `.menu-bar-item a`, `.wp-block-navigation-item__content` (+ `::after`), `.navigation_block`, `.menu-toggle`, `.sticky-navigation-logo`, `.site-logo.mobile-header-logo img`, `.wp-block-search__button...`.
- **Proposition** :
  - Regrouper tout ce qui touche à la barre de navigation (desktop + mobile) dans un bloc unique avec sous-sections `/* desktop */` et `/* mobile */`.
  - Documenter les états sticky/mobile pour éviter les overrides dispersés (`@media (max-width: 1024px)` vs `@media (max-width: 768px)`).

### 3.3. Cartes de contenu (articles, related, listing marque/tests)

- **Sélecteurs concernés** :
  - `.card`, `.related-articles__card`, `.related-content__card`, `.article-card`, `.post_archive_container`, `.lm-card__*`, `.related-news__item`, `.related-news__content`, `.related-news__headline`, `.post-list-*`, `.marque-post-list-item`.
- **Proposition** :
  - S’appuyer sur la nomenclature BEM déjà introduite (`.lm-card`, `.lm-card__media`, `.lm-card__body`, etc.) comme base commune.
  - Réduire les anciens sélecteurs à de simples « skins » qui réutilisent les mêmes primitives (rayons, ombres, marges, layout flex/grid).

### 3.4. Badges / labels / catégories

- **Sélecteurs concernés** :
  - `.post-term-item`, `.post-term-item.term-*`, `.related-news__term-link`, `.test-category-link`, `.term_absolute`, `.term_container_query_loop .post-term-item`, `.home_thumbnail_container .term-*`.
- **Proposition** :
  - Créer un module commun `badges.css` (ou une section dédiée) avec :
    - `.badge` (form factor de base),
    - variantes `.badge--category`, `.badge--test`, `.badge--overlay`,
    - mapping des anciennes classes vers ces variantes (ou refactor progressif du markup).

### 3.5. Grilles & listings

- **Sélecteurs concernés** :
  - `.wpgb-grid-*`, `.wpgb-card-*`, `.lm-brandListing__*`, `.publication_container*`, `.home_thumbnail_container`, `.display_last_updated_category_shortcode .category_container`, `.related-news-posts`.
- **Proposition** :
  - Mutualiser les patterns de grille (grid vs flex) : définir un petit set d’utilitaires de grille (gap, columns) et les appliquer à ces composants.
  - Clarifier la différence entre les listes « classiques » (WP Grid Builder) et celles liées aux marques/tests (`.lm-brandListing__grid`).

### 3.6. Sections test / fiche produit

- **Sélecteurs concernés** :
  - `.pros-cons-container_shortcode`, `.pro_cons_container`, `.sous-notes`, `.fiche-technique`, `.ou_acheter_container`, `.notes-produit`, `.caracteristiques_container`, `.conclusion-section`, `.chapeau-content`, `.toc-desktop`, `.faq-section`.
- **Proposition** :
  - Regrouper dans un module « test/fiche produit » pour rendre explicite l’ensemble du funnel contenu (chapeau → sommaire → caractéristiques → notes → conclusion).
  - Factoriser les containers gris à bord arrondi (background `#f3f3f3`, `max-width: 1000px`, `padding`, `margin auto`) en un composant unique `.lm-section-box`.

---

## 4. Media queries à consolider

### 4.1. Breakpoints actuellement utilisés

Breakpoints repérés (lignes approximatives) :

- `@media screen and (max-width: 996px)` (~548, ~1159)
- `@media screen and (max-width: 992px)` (aucun direct, mais `@media (min-width: 992px)` existe)
- `@media (max-width: 1024px)` (~2181, ~2631)
- `@media (max-width: 992px)` (via `.post-list-title` en `@media (min-width: 992px)` au lieu de max)
- `@media (max-width: 768px)` (très nombreux blocs : galerie, pros/cons, cartes, related news, site-main, glightbox, filtres, breadcrumbs, etc.)
- `@media (max-width: 767px)` (~3233, ~2579)
- `@media (max-width: 600px)` (~2448)
- `@media (max-width: 480px)` (~1354, ~2526)
- `@media (min-width: 768px)` (~1043, ~1062, ~1235, ~168?, ~3721)
- `@media (min-width: 769px)` (~980, ~1903, ~1958)
- `@media (min-width: 992px)` (~2605)
- `@media (min-width: 1024px)` (~3685)
- `@media (min-width: 1025px)` (~2616)

On observe :
- des doublons `max-width: 768px` / `max-width: 767px`,
- des paliers très proches (`992`, `1024`, `1025`),
- des règles dispersées pour les mêmes composants (ex. `.related-news__item`, `.article-card`, `.home_thumbnail_container`, `.glightbox-container`, `.filters-panel`).

### 4.2. Proposition de grille cible

Proposition de set réduit et cohérent :

- **480px** : très petits mobiles.
- **768px** : mobile / petit tablet (seuil principal).
- **1024px** : tablets/petits laptops.
- **1200px** (optionnel) : grands écrans.

Recommandations :

- Normaliser tous les `max-width: 767px` en `max-width: 768px` (ou l’inverse) pour éviter les off-by-one implicites.
- Remplacer les combinaisons 992/1024/1025 par un seul seuil (1024 ou 1200) en regroupant les comportements attendus desktop.
- Grouper les règles par fonction dans chaque media query :
  - bloc `/* mobile ≤ 768px */` contenant les ajustements pour cartes, navigation, filtres, fiches produit, etc.
  - bloc `/* ≥ 768px */` pour la mise en grille plus dense (ex. `.lm-brandListing__grid`, `.ratio-third`).
  - bloc `/* ≥ 1024px */` pour les layout 3 colonnes, sidebars sticky, etc.

---

## 5. Checklist de refactorisation (priorisée)

### Quick wins (faible risque)

- Fusionner les blocs strictement dupliqués :
  - un seul bloc « GeneratePress Site CSS »,
  - un seul bloc pour `.comparaison-container`, `.post_loop_container .gb-grid-wrapper`, `.post-term-item.term-electromenager`, `.gb-container-c3d101a4, .gb-container-38f7095b, .gb-container-d9fb1e26`, `.wp-block-search__button...`.
- Nettoyer les redondances évidentes dans les blocs (`flex-direction` doublé, `background-color` répété, propriétés dupliquées dans `.article-card .article-thumbnail`, `.gb-headline-shortcode`, etc.).
- Centraliser les styles de boutons de filtres (`.toggle-filters-button`, `.close-filters-button`, `.wpgb-reset button`).

### Refactors structurants

- Définir un module commun pour les **badges/labels** et remplacer progressivement les multiples variantes `.post-term-item`, `.related-news__term-link`, `.test-category-link`.
- S’appuyer sur les classes **BEM `.lm-*`** pour rationaliser les cartes de contenu, en réduisant la dépendance aux anciens sélecteurs composites.
- Regrouper la logique **layout global** (`.site-main`, `.archive`, `.home`, `.single-test`, sidebars) dans une section ou un fichier dédié.

### Media queries

- Cartographier précisément chaque composant affecté par `@media (max-width: 768px)` et fusionner les blocs par fonctionnalité (navigation, cartes, fiches produits, filtres).
- Normaliser les breakpoints autour de 480 / 768 / 1024(/1200) et supprimer les variantes très proches (767, 992, 1025).
- S’orienter vers une approche mobile-first : déclarer les styles par défaut pour mobile, puis surcharger progressivement aux breakpoints supérieurs.

