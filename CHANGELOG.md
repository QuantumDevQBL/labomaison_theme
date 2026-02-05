## Changelog – Thème Labomaison

---

## PHP/Functions.php Refactoring (v2.0.0)

### [2026-02-04] – Phase 2 COMPLETE (Foundation Structure)

#### Directory Structure Created
```
/inc
├── setup/           (4 files: theme-support, enqueue-assets, image-sizes, admin-config)
├── admin/           (2 files: admin-columns, editor-config)
├── hooks/           (3 files: content-filters, query-modifications, template-redirects)
├── integrations/    (6 files: wpgridbuilder, rankmath, generatepress, wprocket, litespeed, affilizz)
├── shortcodes/      (9 files: index, product-display, ratings, taxonomy-display, related-content, archive-grids, comparison, navigation, misc)
├── ajax/            (2 files: ajax-views, ajax-filters)
├── rss/             (1 file: feed-customization)
├── analytics/       (1 file: ga4-tracking)
└── utilities/       (2 files: helpers, security)
```

#### Files Created
- 30 module files with proper headers and TODO markers
- `functions.php.new` - New autoloader (~150 lines)
- `functions.php.backup` - Original backup
- `inc/shortcodes/shortcode_list.php.backup` - Original backup

### [2026-02-04] – Phase 1 COMPLETE (Analysis & Planning)

#### Documentation Created
- `functions-inventory.md` - Complete catalog of ~175 functions
- `dependency-graph.md` - 9-tier priority loading system
- `risk-assessment.md` - Risk classification (50 LOW, 60 MEDIUM, 25 HIGH, 5 CRITICAL)
- `module-assignment.md` - Detailed file-by-file mapping

#### Key Findings
- functions.php: 3,426 lines → ~80 lines (target)
- shortcode_list.php: 4,600 lines → Split into 8 files
- Total: ~8,000 lines to migrate
- Reduction target: ~35% through organization

### Phase 3 (PENDING) – Module Migration

See `module-assignment.md` for detailed migration plan.

---

## CSS Refactoring (COMPLETE)

### [2026-02-04] – Phase 3 COMPLETE (Nettoyage)

#### Nettoyage style.css
- Sauvegarde créée : `style.css.backup-20260204-161125` (69 KB)
- `style.css` réduit à l'en-tête WordPress uniquement (1.2 KB)
- Tous les styles proviennent maintenant de `css/style-refactored.css`

#### Résultat final
- **Avant** : style.css monolithique de 3,850 lignes
- **Après** : 9 modules CSS totalisant 4,880 lignes (documentées)
- Gain : Architecture maintenable, CSS variables, responsive consolidé

#### Rollback si nécessaire
```bash
cp style.css.backup-20260204-161125 style.css
```
Et mettre `USE_REFACTORED_CSS` à `false` dans functions.php.

---

### [2026-02-04] – Phase 2 COMPLETE (Module-by-Module Migration)

#### Phase 2.1: _layout.css Complete
- Migrated GeneratePress Site CSS blocks (L954-1072 + L1877-1991)
- Resolved `.inside-article` padding conflicts (4 occurrences → hierarchy)
- Added footer, pagination, sidebar styles
- CSS custom properties integrated throughout

#### Phase 2.2: _navigation.css Complete
- Migrated navigation links with animated underline (L1753-1783)
- Desktop/mobile nav toggle (L2631-2677)
- Search button styles (L909-918 + L1793-1798 merged)
- Social icons and menu bar items

#### Phase 2.3: _badges.css Complete
- Consolidated `.post-term-item` from 3 locations (L1081, L1361, L2001)
- Added BEM modifiers (`--absolute`, `--relative`)
- Color variations (electromenager, etc.)
- Contextual positioning rules
- `.post-type-badge`, `.test-badge` variants

#### Phase 2.4: _cards.css Complete (900+ lines)
- Marque item cards (archive)
- Marque post list items (brand page) with hover effects
- Article cards (linked post)
- Query loop components
- Buying guides overlay cards
- Mixed list homepage cards
- Complete mobile responsive styles (480px, 600px, 768px, 992px, 1024px)

#### Phase 2.5: _grids.css Complete
- GB Grid columns and post loop (L937-952 + L1860-1875 merged)
- Archive ratio-third grid (L1044-1052 + L1961-1968 merged)
- Related news grid with flex-basis variations
- Brand listing BEM grid (1/2/3 column responsive)
- Query loop components
- Pagination styles (GP + custom)

#### Phase 2.6: _test-sections.css Complete
- Section containers base (TOC, pros/cons, FAQ, notes, etc.)
- Pros/cons component with border colors
- Sous-notes & fiche technique
- Notes-produit star ratings
- TOC grid layout
- GB headline shortcode
- Mobile responsive overrides

#### Phase 2.7: _plugins.css Complete
- WP Grid Builder facets & cards
- Filters panel (desktop/mobile layouts)
- Affilizz/Rndzz CLS fixes
- Glightbox lightbox styles
- Rank Math breadcrumb

#### Phase 2.8: _media-queries.css Consolidation Complete
- Normalized breakpoints:
  - 767px/769px → 768px (off-by-one fixes)
  - 992px/996px/1025px → 1024px (desktop consolidation)
- Organized into 5 breakpoint sections:
  - 480px (small mobile)
  - 481px, 600px (intermediate)
  - 768px (tablet - max/min)
  - 1024px (desktop - max/min)
- Consolidated 52 media query blocks into structured sections

#### Metrics (Phase 2 Complete)
- Rules in style.css: 524
- Rules in modules: 472 (90%)
- Remaining (style-only due to CSS vars): ~52

#### Notes
- CSS custom properties used throughout modules
- Original style.css unchanged for rollback capability
- Comparison tool shows "style-only" for rules using CSS variables (expected)

---

### [2026-02-04] – Phase 1 COMPLETE (Deep Analysis & Foundation)

#### Phase 1.1: Deep Audit Synthesis
- Created comprehensive `migration-tracking.md` (500+ lines)
- Classified all 3,850 lines of style.css by target module
- Identified 47+ duplicate rule instances
- Documented 6 exact duplicate blocks (145 lines recoverable)
- Mapped 6 override conflict patterns requiring resolution
- Created dependency graph for migration order
- Established risk assessment per selector

#### Phase 1.2: Custom Properties Extraction
- Expanded `_base.css` from 31 to 250+ lines
- Extracted comprehensive color palette (50+ tokens)
  - Brand colors: --color-primary, --color-accent
  - Neutral scale: --color-gray-50 through --color-gray-900
  - Semantic aliases: --bg-section, --bg-card, --border-card
- Documented spacing system (--space-xs through --space-xl)
- Added typography tokens (--text-xs through --text-6xl)
- Defined shadow system (--shadow-sm through --shadow-card)
- Added transition presets and z-index scale
- Documented GeneratePress variable mapping

#### Phase 1.3: Breakpoint Normalization Strategy
- Analyzed 53 media queries across 11 different breakpoints
- Defined target system: 480px, 768px, 1024px
- Created normalization rules:
  - 767px/769px → 768px (off-by-one fixes)
  - 992px/996px/1025px → 1024px (desktop consolidation)
- Documented special cases (600px for Affilizz)
- Projected 72% reduction in media query blocks (53 → 15)

#### Metrics
- Rules in style.css: 524
- Rules migrated to modules: 161 (31%)
- Rules remaining: 378 (72%)
- Estimated effort remaining: 25-30 hours

#### Files Created/Modified
- `migration-tracking.md` (NEW - comprehensive tracking document)
- `css/_base.css` (EXPANDED - 250+ lines of custom properties)

---

### [Unreleased] – Phase 1 (Préparation)

#### Ajout
- Création de l’architecture CSS modulaire dans `Labomaison/css/` :
  - `_base.css` (variables globales : couleurs, espacements, rayons, breakpoints documentaires).
  - `_layout.css` (squelette pour layout global).
  - `_navigation.css` (squelette pour navigation / header).
  - `_cards.css` (squelette pour cartes de contenu).
  - `_badges.css` (squelette pour badges / labels).
  - `_grids.css` (squelette pour grilles et listings).
  - `_test-sections.css` (squelette pour sections tests / fiches produits).
  - `_media-queries.css` (structure mobile-first pour consolidation future).
  - `_plugins.css` (squelette pour styles plugins : WP Grid Builder, Affilizz, etc.).
  - `_legacy-temp.css` (parking pour règles legacy à trier).
  - `style-refactored.css` (scaffold de feuille d’import, non encore utilisé par WordPress).

#### Notes
- Aucun changement n’a été apporté à `style.css` ni aux fichiers PHP à ce stade.
- Les prochaines phases consisteront à migrer progressivement des blocs depuis `style.css` vers ces fichiers, en commentant chaque déplacement et en conservant les règles legacy pour rollback facile.

### [Unreleased] – Phase 2 (Quick wins – doublons exacts)

#### Ajout
- Consolidation du bloc « GeneratePress Site CSS » dans `css/_layout.css` à partir des sections dupliquées de `style.css` (L954–1072 et L1877–1991), avec commentaires vers les lignes d’origine.
- Ajout des styles de la page de comparaison (`.comparaison-container` et `.comparaison-container .test`) dans `css/_layout.css` (fusion L922–932 et L1845–1855).
- Ajout des règles de grille GenerateBlocks pour le post loop (`.post_loop_container .gb-grid-wrapper`, `.gb-grid-wrapper`, `.post_loop_container .gb-grid-column`, `.gb-grid-wrapper > .gb-grid-column-fff0051c`) dans `css/_grids.css` (fusion L937–952 et L1860–1875).
- Ajout du badge spécifique `.post-term-item.term-electromenager` dans `css/_badges.css` (fusion L1077–1079 et L1996–1998).
- Ajout des containers de vignettes home (`.gb-container-c3d101a4`, `.gb-container-38f7095b`, `.gb-container-d9fb1e26`) dans `css/_cards.css` (fusion L1111–1123 et L2052–2064).
- Ajout des styles du bouton de recherche Gutenberg (`.wp-block-search__button.has-small-font-size.has-icon.wp-element-button` et `svg`) dans `css/_navigation.css` (fusion L909–918 et L1793–1798).

#### Notes
- `style.css` contient toujours les blocs d’origine, aucun style fonctionnel n’a été supprimé.
- Les nouveaux fichiers CSS modulaires préparent l’activation future de `style-refactored.css` avec une possibilité de rollback simple.

### [Unreleased] – Phase 3 (Variations modérées)

#### Ajout
- Création d’un socle consolidé pour les badges dans `css/_badges.css` :
  - `.post-term-item` (styles de base : couleurs, padding, radius, typographie) dérivé des multiples définitions de `style.css` (L1081–1086, L1361–1376, L2001–2009).
  - Classes cibles `.post-term-item--absolute` et `.post-term-item--relative` pour préparer la migration des positionnements contextuels (`.term_container_query_loop .post-term-item`, `.sidebar_post_container .post-term-item`, `.terms_container .post-term-item`).
  - Badge spécifique `.post-term-item.term-electromenager` marqué comme consolidation de L1077–1079 et L1996–1998.
- Création d’une section « ARTICLE CONTAINERS » dans `css/_layout.css` :
  - `.inside-article` par défaut avec padding horizontal 20px (référence aux blocs L875–878, L1092–1095, L2015–2019).
  - Variantes contextuelles ciblées pour `.home .inside-article`, `.archive .inside-article` (padding 10px) et `.single-marque.separate-containers .inside-article` (padding 0), préparant la résolution des paddings contradictoires.

#### Notes
- Aucun retrait dans `style.css` : toutes les anciennes définitions de `.post-term-item` et `.inside-article` restent actives tant que `style-refactored.css` n’est pas branché.
- Les nouvelles règles agissent comme référence structurée pour la future migration vers un CSS modulaire, en limitant le risque de régression.

### [Unreleased] – Phase 6 (Badges avancés / pills)

#### Ajout
- Enrichissement de `css/_badges.css` pour couvrir les pills de tests et de related news :
  - Création d’un style générique `.badge-pill` basé sur les règles communes de `style.css` (L1360–1378) : couleur texte blanche, fond sombre, padding, radius, uppercase, etc.
  - Documentation des alias à migrer (`.post-term-item`, `.related-news__term-link`, `.test-category-link`) afin de les rattacher progressivement à `.badge-pill`.
  - Reprise des positionnements contextuels pour `.test-category-link` (global, `.home`, `.post_banner_container`) conformément aux définitions existantes (L1190–1192, L1380–1396, L1438–1443).
  - Centralisation de `.term_absolute` et de ses variantes (`.publication_container .term_absolute`, `.post_archive_container .term_absolute`) d’après L1431–1436, L2230–2233, L2993–2996.

#### Notes
- Aucun sélecteur n’a été retiré de `style.css` : les blocs sont simplement recopiés et structurés dans `css/_badges.css` pour préparer une migration ultérieure plus sûre.
- Ces nouveaux styles n’affecteront le rendu qu’une fois `style-refactored.css` (ou des imports équivalents) branché dans la chaîne de chargement CSS.

### [Unreleased] – Phase 7 (Navigation & Cards)

#### Ajout
- **Navigation** (`css/_navigation.css`) :
  - Recopie et structuration des styles de liens de navigation avec soulignement animé : `.wp-block-navigation-item__content` et son `::after` (réf. `style.css` L1753–1783).
  - Centralisation des styles de la barre de navigation : `.navigation_block`, `.main-navigation .sticky-navigation-logo`, `.inside-navigation.grid-container`, `.menu-bar-items`, `.navigation-branding`, y compris le `max-width` et le padding horizontal desktop (réf. L1786–1841).
- **Cards** (`css/_cards.css`) :
  - Recopie du bloc principal de cartes : `.card`, `.related-articles__card`, `.related-content__card` (layout flex, gaps, padding, radius, box-shadow) – réf. L2893–2903.
  - Ajout des styles associés aux images de cartes (`.related-content__image img`, `.card__image img`, `.related-articles__image img`) et au contenu (`.card__content`, `.related-articles__content`, `.related-content__content`), titres, extraits, dates, avec les valeurs d’origine (L2906–2973, L2975–2986).

#### Notes
- Comme pour les étapes précédentes, `style.css` reste inchangé : tous les blocs d’origine sont toujours présents et actifs.
- Les modules `_navigation.css` et `_cards.css` servent désormais de références complètes pour ces familles de composants en vue d’une activation progressive via `style-refactored.css`.

### [Unreleased] – Phase 8 (Grids & Sections de test)

#### Ajout
- **Grids / listings** (`css/_grids.css`) :
  - Recopie de la grille related news (`.related-news-posts`, `.related-news__items`, `.related-news__item`, `.related-news__content`, etc.) incluant les règles de flex, de `flex-basis` par nombre d’items et les adaptations responsive (L1208–1358).
  - Intégration de la grille BEM de listing marque (`.lm-brandListing__grid`, `.lm-brandListing__pagination`, `.lm-card__inner`) avec les colonnes adaptatives 1/2/3 colonnes selon les breakpoints (L3607–3689).
- **Sections de test / produit** (`css/_test-sections.css`) :
  - Centralisation des containers de sections (`.toc-desktop`, `.pros-cons-container_shortcode`, `.faq-section`, `.presentation-section`, `.contenu-section`, `.chapeau-content`, `.notes-produit`, `.caracteristiques_container`, `.conclusion-section`) avec leurs dimensions, padding, radius et fond gris (L1555–1563).
  - Recopie des styles communs de texte (`.pros-cons-container_shortcode p`, `.faq-section p`, `.notes-produit p`, `.caracteristiques_container p`, `.conclusion-section p`, `.chapeau-content p`) et des titres (`.title_container`, `.title_container h2`, etc.) – réf. L1566–1595.

#### Notes
- `style.css` continue de contenir tous les blocs d’origine pour related news, brand listing et sections de test : aucune règle n’a été supprimée.
- Les fichiers `_grids.css` et `_test-sections.css` jouent désormais le rôle de référentiels structurés pour ces familles, en vue d’un basculement contrôlé vers `style-refactored.css`.

### [Unreleased] – Phase 9 (Plugins : WP Grid Builder, Affilizz, Glightbox)

#### Ajout
- **WP Grid Builder** (`css/_plugins.css`) :
  - Recopie des styles de facets (`.wpgb-facet`, boutons apply/reset, variantes `wpgb-facet-14`, `wpgb-facet-25`, `hide-facette`, sliders, etc.) et des cartes (`.wpgb-grid-19 .wpgb-card-inner`, `.wp-grid-builder .wpgb-card-30`, `.wpgb-card-inner` CLS fix).
  - Intégration de l’UI de filtre avancé (`.filters-panel`, `.facet-block`, boutons toggle/close/reset, responsive mobile/desktop) en regroupant les règles dispersées (L3193–3484).
- **Affilizz / Rndzz** :
  - Centralisation des hauteurs minimales et états skeleton (`.wp-block-affilizz-publication`, `.affilizz-rendering-container`, `.affilizz-skeleton`) ainsi que des wrappers d’offres (`.affilizz-offer-list-wrapper`, `.rndzz-offer-list-wrapper`, `.affilizz-box-layout`, `.rndzz-box-layout`).
  - Reprise des styles de container Affilizz dans les cartes WPGB (`.wpgb-card-44 .affilizz-container`, `.affilizz-container`).
- **Glightbox** :
  - Recopie des styles de la lightbox marque (`.glightbox-container`, `.glightbox-container .ginner-container`, `.glightbox-mobile .goverlay`) pour desktop et mobile.

#### Notes
- Comme pour les autres phases, `style.css` contient toujours toutes les règles originales des plugins.
- `_plugins.css` sert désormais de hub unique pour les styles liés aux extensions (WP Grid Builder, Affilizz, Glightbox), ce qui facilitera l’analyse et le debug lors du basculement vers le CSS modulaire.

### [Unreleased] – Phase 10 (Toggle CSS refactorisé)

#### Ajout
- Dans `functions.php` :
  - Ajout d’un hook `wp_enqueue_scripts` qui, si la constante `USE_REFACTORED_CSS` est définie à `true`, enfile la feuille `css/style-refactored.css` sous le handle `labomaison-refactored`.
  - Comportement par défaut inchangé : tant que `USE_REFACTORED_CSS` n’est pas activée, seule la chaîne actuelle (dont `style.css`) est utilisée.

#### Notes
- Ce toggle permet de tester la version modulaire du CSS sur un environnement de dev/staging sans retirer ni modifier `style.css`.
- En mode test (constante à true), les deux feuilles coexistent (originale + refactorisée), ce qui facilite la comparaison visuelle et le debug progressif avant toute suppression de blocs legacy.






### [Unreleased] – Phase 4–5 (Media queries & fichier d’import)

#### Ajout
- Remplissage de `css/_media-queries.css` avec une première consolidation mobile-first :
  - Bloc `@media (max-width: 768px)` pour :
    - la navigation mobile (`.inside-navigation.grid-container`, `.menu-toggle`), basé sur les règles existantes de `style.css` (L1811–1815, L2663–2665),
    - les images de cartes (`.card__image img`, `.related-articles__image img`, `.related-content__image img`) – réf. L3007–3014,
    - les sections pros/cons (`.pros-cons-container_shortcode .square-box`, `.pros-cons-container_shortcode`) – réf. L776–791,
    - les cartes related news (`.related-news__item`, `.related-news-posts`) – réf. L1347–1351, L1972–1987.
  - Bloc `@media (min-width: 768px)` pour la grille archive `.ratio-third` (réf. L1044–1052, L1961–1968).
  - Bloc `@media (min-width: 1024px)` pour la sidebar sticky (`.sidebar.is-right-sidebar > .inside-right-sidebar`), en s’alignant sur les définitions existantes (L980–985, L1903–1907).
- Complétion de `css/style-refactored.css` avec les `@import` vers tous les modules :
  - Base : `_base.css`.
  - Layout : `_layout.css`, `_navigation.css`.
  - Composants : `_cards.css`, `_badges.css`, `_grids.css`, `_test-sections.css`.
  - Plugins : `_plugins.css`.
  - Media queries : `_media-queries.css` (chargé en dernier).
  - Legacy : `_legacy-temp.css`.

#### Notes
- `style-refactored.css` n’est toujours pas chargé par WordPress : il s’agit uniquement d’un point d’entrée prêt pour une activation contrôlée plus tard.
- Les media queries restent également présentes dans `style.css` : la consolidation est préparée dans un fichier dédié mais aucun comportement en production n’a été modifié à ce stade.




