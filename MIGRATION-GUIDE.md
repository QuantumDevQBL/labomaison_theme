## Migration CSS – Labomaison

Ce guide explique comment passer progressivement du `style.css` monolithique aux fichiers CSS modulaires du dossier `css/`, sans casser la prod.

### 1. Architecture actuelle

- `style.css`
  - Fichier historique, toujours chargé par GeneratePress (thème enfant).
  - Contient encore **100%** des règles d’origine.

- Dossier `css/`
  - `_base.css` : variables globales (`:root`, couleurs, espacements, radius, breakpoints documentaires).
  - `_layout.css` : layout global (GeneratePress Site CSS, `.site-main`, `.inside-article`, `.comparaison-container`, etc.).
  - `_navigation.css` : navigation / header (WP Navigation links, barre principale, bouton de recherche).
  - `_cards.css` : cartes de contenu (cartes articles, related, home thumbnails GB).
  - `_badges.css` : badges & pills (`.post-term-item`, related terms, `test-category-link`, `term_absolute`, etc.).
  - `_grids.css` : grilles & listings (post loop GB, related news, brand listing BEM).
  - `_test-sections.css` : sections test/produit (toc, pros/cons, caractéristiques, conclusion, etc.).
  - `_media-queries.css` : media queries consolidées (mobile/tablette/desktop pour navigation, cartes, related news, etc.).
  - `_plugins.css` : plugins (WP Grid Builder, Affilizz/Rndzz, Glightbox…).
  - `_legacy-temp.css` : parking pour règles à trier / conserver provisoirement.
  - `style-refactored.css` : point d’entrée qui `@import` tous les `_*.css`.

- `functions.php`
  - Toggle optionnel :

    ```php
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
    ```

### 2. Mapping : de `style.css` vers les modules

#### 2.1. Layout global

- **Blocs concernés dans `style.css`** :
  - `/* GeneratePress Site CSS */` (L954–1072 et L1877–1991).
  - `.site-main`, `.archive .site-main`, `.archive .site-content`, `.page-id-99 .site-main`, `.blog .site-main`, `.home .site-main`, `.single-test .site-main`, etc.
  - `.inside-article` (plusieurs occurrences, y compris variantes mobile).
  - `.comparaison-container`, `.comparaison-container .test`.

- **Cible** : `css/_layout.css`
  - Section `LAYOUT GLOBAL – GeneratePress Base`.
  - Section `COMPARISON PAGE`.
  - Section `ARTICLE CONTAINERS`.

#### 2.2. Navigation / header

- **Blocs `style.css`** :
  - Bloc `/*navigation*/` autour de L1751–1841.
  - `.wp-block-search__button.has-small-font-size.has-icon.wp-element-button` + `svg` (L909–918, L1793–1798).
  - Media queries de navigation dans `@media (max-width: 768px)` et `@media (max-width: 1024px)` (L1811–1815, L2631–2665).

- **Cible** :
  - Règles de base : `css/_navigation.css`.
  - Ajustements responsive : `css/_media-queries.css`.

#### 2.3. Cards (articles, related, marque/tests)

- **Blocs `style.css`** :
  - Cartes : `.card`, `.related-articles__card`, `.related-content__card` et sous-éléments (L2893–2986).
  - Cartes « Linked post test » : `.article-card`, `.article-thumbnail`, `.article-content`, `.article-title`, etc. (L1607–1674).
  - Home thumbnails : `.header_thumbanil_container`, `.header_thumbnail_container`, `.gb-container-c3d101a4`, `.gb-container-38f7095b`, `.gb-container-d9fb1e26`.

- **Cible** :
  - Structures cartes : `css/_cards.css`.
  - Aspects purement layout (grid/listing) : parfois en complément dans `css/_grids.css`.

#### 2.4. Badges / labels / pills

- **Blocs `style.css`** :
  - `.post-term-item`, `.post-term-item.term-*`, `.post-term-item.term-electromenager` (plusieurs blocs vers L1077–1086 et L1996–2009).
  - `.related-news__term-link` (L1283–1286, L1326–1333, L1360–1376).
  - `.test-category-link` (L1380–1396, L1190–1192, L1438–1443).
  - `.term_absolute` et contextes (`.publication_container .term_absolute`, `.post_archive_container .term_absolute`).
  - Badges dans sidebar, query loop, etc.

- **Cible** : `css/_badges.css`
  - Base `.post-term-item` + variations (`term-electromenager`).
  - Style générique `.badge-pill`.
  - Positionnements contextuels regroupés.

#### 2.5. Grids & listings

- **Blocs `style.css`** :
  - Post loop GB : `.post_loop_container .gb-grid-wrapper`, `.gb-grid-wrapper`, `.gb-grid-column-fff0051c`.
  - Related news : `.related-news-posts`, `.related-news__item`, `.related-news__content`, etc. (L1208–1358).
  - Brand listing BEM : `.lm-brandListing__grid`, `.lm-brandListing__pagination`, `.lm-card__inner` (L3607–3698).

- **Cible** : `css/_grids.css`.

#### 2.6. Sections test / produit

- **Blocs `style.css`** :
  - `.pros-cons-container_shortcode` et variantes.
  - `.sous-notes`, `.fiche-technique`, `.ou_acheter_container`.
  - `.toc-desktop`, `.faq-section`, `.presentation-section`, `.contenu-section`, `.chapeau-content`, `.notes-produit`, `.caracteristiques_container`, `.conclusion-section`, `.title_container`, etc. (L1555–1595, + autres).

- **Cible** : `css/_test-sections.css` (+ `_layout.css` pour certains layouts globaux).

#### 2.7. Plugins

- **Blocs `style.css`** :
  - WP Grid Builder : `.wpgb-*`, `.wp-grid-builder .wpgb-card-*`, `.filters-panel`, `.facet-block`, etc. (L803–808, 879–882, 1596–1601, 2213–2227, 2362–2367, 3187–3484, 3518–3532).
  - Affilizz/Rndzz : `.wp-block-affilizz-publication`, `.affilizz-rendering-container`, `.affilizz-container`, `.affilizz-offer-list-wrapper`, `.rndzz-offer-list-wrapper`, `.affilizz-box-layout`, `.rndzz-box-layout`, etc. (L2440–2454, 3129–3139, 3535–3581).
  - Glightbox : `.glightbox-container`, `.ginner-container`, `.glightbox-mobile .goverlay` (L2489–2504, 2579–2586, 2622–2624).

- **Cible** : `css/_plugins.css`.

#### 2.8. Media queries

- **Blocs `style.css`** :
  - `@media (max-width: 768px)`, `@media (max-width: 767px)`, `@media (max-width: 600px)`, `@media (max-width: 480px)`, `@media (min-width: 768px)`, `@media (min-width: 769px)`, `@media (min-width: 1024px)`, etc.

- **Cible** : `css/_media-queries.css`
  - Centralisation progressive par fonctionnalité (navigation, cards, related, sections test, plugins).

### 3. Stratégie de migration en prod (sécurisée)

#### 3.1. Pré-requis

1. **Environnement de test/staging** disponible (ne pas activer d’emblée en prod).
2. Accès à `wp-config.php` (ou à un mu-plugin) pour définir des constantes.
3. Accès aux outils de debug front (DevTools, screenshots avant/après).

#### 3.2. Activer la feuille refactorisée en test

Sur l’environnement de test uniquement :

```php
// wp-config.php
define('USE_REFACTORED_CSS', true);
```

Effet :
- `style.css` **reste chargé** comme avant.
- `css/style-refactored.css` est ajouté par-dessus (via `functions.php`).
- Les doublons entre les deux feuilles peuvent être inspectés visuellement.

#### 3.3. Plan de test minimal

Pages à vérifier après activation de `USE_REFACTORED_CSS` :

- **Home** :
  - Navigation (desktop & mobile), hero, home thumbnails, listing « mixed_list_homepage », WPGB grids, Affilizz éventuel.

- **Archive tests / catégories** (`/categorie_test/...`) :
  - Grille archive (`ratio-third`), cartes tests, badges `.post-term-item`, related news.

- **Single test** (`single-test`) :
  - Sommaire `toc-desktop`, sections pros/cons, fiches techniques, notes, conclusion, blocs Affilizz/Rndzz, glightbox.

- **Page marque** (`single-marque`) :
  - Listing de contenus marque (`.lm-brandListing__grid`), cartes marque, sidebar, glightbox.

- **Pages avec filtres WPGB** :
  - Panneau `.filters-panel`, facets `.wpgb-facet`, boutons apply/reset, cartes liées.

Pour chaque page :
- Comparer avant/après (captures d’écran si possible).
- Noter tout décalage de layout, de couleurs, de hover, de tailles de police.

### 4. Phase de nettoyage (commenter / retirer dans `style.css`)

Une fois que `USE_REFACTORED_CSS` est activé en test **et que les pages clés sont stables**, on peut commencer à alléger `style.css`.

#### 4.1. Principe général

1. **Famille par famille** (ex : badges, cards, navigation).
2. Pour chaque bloc clairement recopié dans un `_*.css` :
   - Le **commenter** dans `style.css` avec un tag de migration.
   - Exemple :

     ```css
     /* MIGRÉ vers css/_navigation.css – voir section "CONTAINER NAVIGATION" */
     /*
     .navigation_block {
       ...
     }
     */
     ```

3. Recharger la page sur l’environnement de test :
   - Si aucun changement visuel → le bloc est réellement redondant, on peut envisager sa **suppression définitive** plus tard.
   - Si un bug apparaît → dé-commenter, ajuster la version dans le module (`_*.css`), retester.

#### 4.2. Ordre recommandé

1. **Blocs strictement dupliqués** (déjà identifiés dans `css-audit.md`) :
   - GeneratePress Site CSS (bloc ancien vs nouveau).
   - `.post_loop_container .gb-grid-wrapper`, `.comparaison-container`, `.post-term-item.term-electromenager`, etc.

2. **Familles bien encapsulées** :
   - Badges (`_badges.css`).
   - Cards (`_cards.css`).
   - Grids (`_grids.css`).
   - Plugins (`_plugins.css`).

3. **Layout global & media queries** :
   - `archive .site-main`, `.site-main`, `.inside-article`.
   - Breakpoints consolidés dans `_media-queries.css`.

Toujours :
- Procéder par **petits lots**,
- Noter chaque action dans `CHANGELOG.md`,
- Tester immédiatement après chaque lot.

### 5. Rollback

Si un problème majeur est détecté :

1. Mettre `USE_REFACTORED_CSS` à `false` ou commenter la constante → on revient à l’état initial (seul `style.css` actif).
2. Si des blocs ont été supprimés dans `style.css` :
   - S’appuyer sur le contrôle de version (Git) ou sur un backup (`style.css.backup`) pour restaurer.

Tant que vous ne **supprimez pas définitivement** les blocs commentés dans `style.css`, le rollback consiste essentiellement à :
- réactiver ces blocs (enlever les commentaires),
- désactiver `USE_REFACTORED_CSS`.

---

En résumé :
- Vous avez maintenant une **architecture CSS modulaire** prête.
- Le toggle `USE_REFACTORED_CSS` permet de tester sans risque.
- La phase suivante est un travail incrémental de **commentaire puis suppression** dans `style.css`, guidé par ce mapping et le `CHANGELOG.md`, avec tests manuels à chaque étape.

