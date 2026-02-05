# Module Assignment Plan - Labomaison Theme

**Generated**: 2026-02-04
**Purpose**: Assign every function to its target module file

---

## Target Directory Structure

```
/inc
├── setup/
│   ├── theme-support.php       [~80 lines]
│   ├── enqueue-assets.php      [~120 lines]
│   ├── image-sizes.php         [~30 lines]
│   └── admin-config.php        [~100 lines]
├── admin/
│   ├── admin-columns.php       [~100 lines]
│   └── editor-config.php       [~80 lines]
├── hooks/
│   ├── content-filters.php     [~150 lines]
│   ├── query-modifications.php [~100 lines]
│   └── template-redirects.php  [~250 lines]
├── integrations/
│   ├── wpgridbuilder.php       [~300 lines]
│   ├── rankmath.php            [~600 lines]
│   ├── generatepress.php       [~80 lines]
│   ├── wprocket.php            [~80 lines]
│   ├── litespeed.php           [~30 lines]
│   └── affilizz.php            [~100 lines]
├── shortcodes/
│   ├── index.php               [Loader]
│   ├── product-display.php     [~400 lines]
│   ├── ratings.php             [~200 lines]
│   ├── taxonomy-display.php    [~400 lines]
│   ├── related-content.php     [~800 lines]
│   ├── archive-grids.php       [~600 lines]
│   ├── comparison.php          [~150 lines]
│   ├── navigation.php          [~400 lines]
│   └── misc.php                [~200 lines]
├── ajax/
│   ├── ajax-views.php          [~150 lines]
│   └── ajax-filters.php        [~200 lines]
├── rss/
│   └── feed-customization.php  [~200 lines]
├── analytics/
│   └── ga4-tracking.php        [~300 lines]
└── utilities/
    ├── helpers.php             [~200 lines]
    └── security.php            [~100 lines]
```

**Total Estimated**: ~5,200 lines in /inc (vs ~8,000 original)
**Reduction**: ~35% through organization and removal of comments/whitespace

---

## Detailed Module Assignments

### /inc/utilities/helpers.php

**Purpose**: General helper functions used across the theme
**Load Priority**: 1 (FIRST)
**Dependencies**: None

| Function | Original Location | Lines | Risk |
|----------|-------------------|-------|------|
| `generate_star_rating()` | shortcode_list.php:L1948 | ~30 | LOW |
| `generate_card_html()` | shortcode_list.php:L2150 | ~40 | LOW |
| `generate_content_card()` | shortcode_list.php:L2251 | ~30 | LOW |
| `generate_product_card()` | shortcode_list.php:L2283 | ~20 | LOW |
| `generate_content_card_custom()` | shortcode_list.php:L2305 | ~10 | LOW |
| `render_post_item_for_test()` | shortcode_list.php:L1446 | ~50 | LOW |
| `render_post_item_for_marque()` | shortcode_list.php:L1740 | ~30 | LOW |
| `lm_pagination_markup_compat()` | shortcode_list.php:L3374 | ~100 | LOW |
| `lm_get_test_card_title()` | shortcode_list.php:L4061 | ~50 | LOW |
| `lm_render_related_test_card()` | shortcode_list.php:L4439 | ~60 | LOW |
| `initialize_displayed_news_ids()` | shortcode_list.php:L2242 | ~10 | LOW |
| `add_custom_post_type_class()` | shortcode_list.php:L1788 | ~15 | LOW |
| `modify_headline_block_for_tests()` | shortcode_list.php:L1800 | ~35 | LOW |

**Testing Checklist**:
- [ ] Star rating generates correct SVG
- [ ] Card HTML renders properly
- [ ] Pagination markup valid
- [ ] Helper functions accessible from shortcodes

---

### /inc/utilities/security.php

**Purpose**: Security response functions
**Load Priority**: 1 (FIRST)
**Dependencies**: None

| Function | Original Location | Lines | Risk |
|----------|-------------------|-------|------|
| `lm_guard_send_410()` | functions.php:L1669 | ~20 | CRITICAL |
| `lm_guard_send_301()` | functions.php:L1690 | ~15 | CRITICAL |
| `lm_can_is_redacteur_scope()` | functions.php:L3243 | ~10 | LOW |
| `lm_can_current_url_clean()` | functions.php:L3252 | ~15 | LOW |

**Testing Checklist**:
- [ ] 410 response sends correct headers
- [ ] 301 redirect works correctly
- [ ] URL scope detection accurate
- [ ] No PHP errors on invalid URLs

---

### /inc/setup/theme-support.php

**Purpose**: Core theme setup and configuration
**Load Priority**: 2
**Dependencies**: None

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `wpse325327_add_excerpts_to_pages()` | functions.php:L63 | ~5 | LOW |
| Comments disable (init) | functions.php:L871-879 | ~10 | LOW |
| Comments menu removal | functions.php:L882-884 | ~5 | LOW |
| Dashboard widget removal | functions.php:L887-889 | ~5 | LOW |
| Comments redirect | functions.php:L892-898 | ~10 | LOW |
| Discussion metabox removal | functions.php:L913-916 | ~5 | LOW |
| Feed links removal | functions.php:L908-910 | ~5 | LOW |
| `__return_false` on comments_open | functions.php:L901 | ~2 | LOW |
| `__return_false` on pings_open | functions.php:L902 | ~2 | LOW |
| `__return_empty_array` on comments_array | functions.php:L905 | ~2 | LOW |
| `__return_true` on wp_lazy_loading_enabled | functions.php:L822 | ~2 | LOW |

**Testing Checklist**:
- [ ] Excerpts work on pages
- [ ] Comments disabled sitewide
- [ ] Comments menu hidden
- [ ] No PHP notices

---

### /inc/setup/enqueue-assets.php

**Purpose**: All CSS and JavaScript enqueuing
**Load Priority**: 2
**Dependencies**: None

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `enqueue_instagram_embed_script()` | functions.php:L27-35 | ~10 | LOW |
| `enqueue_font_awesome()` | functions.php:L37-40 | ~5 | LOW |
| `my_custom_fonts_enqueue()` | functions.php:L68-74 | ~10 | LOW |
| `load_acf_scripts()` | functions.php:L55-58 | ~5 | MEDIUM |
| ACF styles dequeue | functions.php:L1142-1150 | ~10 | LOW |
| Refactored CSS toggle | functions.php:L3415-3424 | ~15 | LOW |
| USE_REFACTORED_CSS constant | functions.php:L21 | ~2 | LOW |

**Testing Checklist**:
- [ ] Font Awesome icons display
- [ ] Custom.js loads
- [ ] Instagram/TikTok embeds work
- [ ] ACF styles removed from frontend
- [ ] No console errors

---

### /inc/setup/image-sizes.php

**Purpose**: Custom image size definitions
**Load Priority**: 2
**Dependencies**: None

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `add_image_size('author-thumbnail')` | functions.php:L865 | ~2 | LOW |
| `add_image_size('grid_size')` | functions.php:L866 | ~2 | LOW |

**Testing Checklist**:
- [ ] Image sizes appear in media library
- [ ] Regenerate thumbnails works

---

### /inc/setup/admin-config.php

**Purpose**: Admin-specific configurations
**Load Priority**: 2 (Admin only)
**Dependencies**: None

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| Plugins init delay | functions.php:L845-861 | ~20 | CRITICAL |
| Disable comments on marque (init) | functions.php:L814-819 | ~8 | LOW |

**Testing Checklist**:
- [ ] WPSP Pro initializes correctly
- [ ] Affilizz initializes correctly
- [ ] Marque posts have no comment option

---

### /inc/admin/admin-columns.php

**Purpose**: Custom admin list columns
**Load Priority**: 3 (Admin only)
**Dependencies**: ACF

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| manage_test_posts_columns | functions.php:L130-133 | ~5 | LOW |
| manage_test_posts_custom_column | functions.php:L136-161 | ~30 | MEDIUM |
| restrict_manage_posts | functions.php:L163-185 | ~25 | MEDIUM |
| parse_query | functions.php:L186-209 | ~25 | MEDIUM |

**Testing Checklist**:
- [ ] Marque column appears in test list
- [ ] Marque links work
- [ ] Taxonomy dropdowns filter correctly

---

### /inc/admin/editor-config.php

**Purpose**: Block editor and publishing validations
**Load Priority**: 3 (Admin only)
**Dependencies**: None

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `check_category_before_publishing()` | functions.php:L649-683 | ~40 | MEDIUM |

**Testing Checklist**:
- [ ] Cannot publish without category
- [ ] Cannot publish with "Blog" category
- [ ] Error message displays correctly
- [ ] Gutenberg editor works normally

---

### /inc/hooks/content-filters.php

**Purpose**: Filters on post content
**Load Priority**: 4
**Dependencies**: utilities/helpers.php

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `add_image_dimensions()` | functions.php:L760-788 | ~30 | MEDIUM |
| `add_dimensions_to_affilizz_images()` | functions.php:L791-811 | ~25 | MEDIUM |
| `force_lazy_load_images()` | functions.php:L825-830 | ~8 | LOW |
| render_block (query_loop) | functions.php:L432-451 | ~25 | MEDIUM |
| gettext (search results) | functions.php:L837-842 | ~8 | LOW |

**Testing Checklist**:
- [ ] Images have width/height attributes
- [ ] Affilizz images sized correctly
- [ ] Lazy loading attribute added
- [ ] Query loop headlines show shortcodes
- [ ] Search results title translated

---

### /inc/hooks/query-modifications.php

**Purpose**: Modify WordPress queries
**Load Priority**: 4
**Dependencies**: None

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `adjust_main_query_based_on_ratings()` | functions.php:L548-603 | ~60 | HIGH |
| `add_custom_post_types_to_rss_feed()` | functions.php:L715-721 | ~10 | LOW |

**Testing Checklist**:
- [ ] Test archives sort by rating
- [ ] Category archives sort correctly
- [ ] RSS includes test posts
- [ ] No performance degradation

---

### /inc/hooks/template-redirects.php

**Purpose**: HTTP redirects and responses
**Load Priority**: 4
**Dependencies**: utilities/security.php

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `lm_guard_early_410()` (priority 0) | functions.php:L1707-1755 | ~50 | HIGH |
| `lm_guard_redirect_over_max_pagination()` (priority 1) | functions.php:L1760-1792 | ~35 | HIGH |
| `lm_guard_brand_unused_410()` (priority 2) | functions.php:L1797-1861 | ~70 | HIGH |
| Trailing slash redirect (priority 0) | functions.php:L2859-2907 | ~50 | HIGH |
| Marque feed redirect | functions.php:L513-545 | ~35 | MEDIUM |
| `custom_pre_handle_404()` | functions.php:L995-1017 | ~25 | MEDIUM |
| Canonical hardening (redacteur) | functions.php:L3278-3299 | ~25 | MEDIUM |

**Testing Checklist**:
- [ ] ?w= URLs return 410
- [ ] Over-max pagination redirects
- [ ] Empty brands return 410
- [ ] URLs have trailing slash
- [ ] Marque feeds redirect to home
- [ ] Redacteur pages have canonical

---

### /inc/integrations/wpgridbuilder.php

**Purpose**: WP Grid Builder hooks
**Load Priority**: 5
**Dependencies**: WP Grid Builder plugin, ACF
**Condition**: `class_exists('WP_Grid_Builder')`

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| generateblocks_query_loop_args | functions.php:L76-86 | ~15 | MEDIUM |
| wp_grid_builder/blocks (affilizz) | functions.php:L89-110 | ~25 | MEDIUM |
| wp_grid_builder/grid/the_object (author, Grid 19) | functions.php:L114-127 | ~15 | MEDIUM |
| wp_grid_builder/grid/the_object (category, Grid 10) | functions.php:L213-234 | ~25 | MEDIUM |
| `grid_query_related_products_fill()` (Grid 23) | functions.php:L262-343 | ~85 | HIGH |
| Grid 6 sort by note_globale | functions.php:L346-356 | ~15 | MEDIUM |
| `grid_query_related_products_test_fill()` (Grid 24) | functions.php:L367-426 | ~65 | HIGH |
| `exclude_empty_content_or_no_associations()` (Grid 29) | functions.php:L723-757 | ~40 | MEDIUM |
| Author filter (Grid 26) | functions.php:L928-937 | ~15 | LOW |
| wpgb_lazy_load disable | functions.php:L939 | ~2 | LOW |
| Views sort (Grid 30) | functions.php:L2785-2798 | ~18 | MEDIUM |

**Testing Checklist**:
- [ ] All grids display correctly
- [ ] Related products show 6 items
- [ ] Author grid shows correct posts
- [ ] Category featured images display
- [ ] Affilizz blocks render

---

### /inc/integrations/rankmath.php

**Purpose**: Rank Math SEO integration
**Load Priority**: 5
**Dependencies**: Rank Math plugin, ACF
**Condition**: `class_exists('RankMath')`

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| **Variables** |
| rank_math/vars/register_extra_replacements | functions.php:L1970-2117 | ~150 | MEDIUM |
| **Breadcrumb Helpers** |
| `lm_breadcrumb_get_univers()` | functions.php:L2321-2328 | ~10 | LOW |
| `lm_breadcrumb_pick_primary_category()` | functions.php:L2330-2371 | ~45 | MEDIUM |
| `lm_breadcrumb_get_featured_test_data()` | functions.php:L2388-2444 | ~60 | MEDIUM |
| `lm_map_post_category_to_test_term()` | functions.php:L2451-2457 | ~10 | LOW |
| **Breadcrumb Filters** |
| rank_math/frontend/breadcrumb/items | functions.php:L2463-2628 | ~170 | HIGH |
| rank_math/frontend/breadcrumb/html | functions.php:L2635-2687 | ~55 | MEDIUM |
| rank_math/frontend/breadcrumb/items (author) | functions.php:L2913-2942 | ~35 | MEDIUM |
| **Schema** |
| rank_math/json_ld (CollectionPage) | functions.php:L2126-2222 | ~100 | MEDIUM |
| rank_math/json_ld (Product cleanup) | functions.php:L2232-2292 | ~65 | MEDIUM |
| **Sitemap** |
| rank_math/sitemap/entry | functions.php:L2955-3016 | ~65 | MEDIUM |
| wpseo_sitemap_urlimages | functions.php:L1471-1580 | ~115 | MEDIUM |
| wpseo_sitemap_term_image | functions.php:L1586-1595 | ~15 | LOW |
| **Canonical** |
| Pagination canonical | functions.php:L3029-3071 | ~45 | MEDIUM |
| Tracking params canonical | functions.php:L3084-3174 | ~95 | MEDIUM |
| Marques canonical | functions.php:L3186-3229 | ~50 | MEDIUM |
| Redacteur canonical | functions.php:L3269-3272 | ~5 | LOW |
| **Title/Description** |
| rank_math/frontend/title | functions.php:L3321-3366 | ~50 | LOW |
| rank_math/frontend/description | functions.php:L3372-3400 | ~35 | LOW |

**Testing Checklist**:
- [ ] Custom variables work in templates
- [ ] Breadcrumbs show correct hierarchy
- [ ] Featured test appears in breadcrumb
- [ ] CollectionPage schema validates
- [ ] Product schema has valid data
- [ ] Sitemap excludes paginated URLs
- [ ] Canonicals are self-referential on pagination
- [ ] Titles have proper pagination suffix

---

### /inc/integrations/generatepress.php

**Purpose**: GeneratePress parent theme hooks
**Load Priority**: 5
**Dependencies**: GeneratePress theme

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| generate_archive_title | functions.php:L458-476 | ~20 | LOW |
| generate_after_loop | functions.php:L478-504 | ~30 | LOW |

**Testing Checklist**:
- [ ] Category pages show ACF chapeau
- [ ] After loop shows ACF content
- [ ] FAQ displays on archives

---

### /inc/integrations/wprocket.php

**Purpose**: WP Rocket exclusions
**Load Priority**: 5
**Dependencies**: WP Rocket plugin
**Condition**: `class_exists('WP_Rocket_Core')` or similar

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| rocket_delay_js_exclusions | functions.php:L1606-1619 | ~15 | LOW |
| rocket_exclude_js | functions.php:L1622-1633 | ~15 | LOW |
| rocket_exclude_css | functions.php:L1636-1640 | ~8 | LOW |
| rocket_rucss_inline_content_exclusions | functions.php:L1644-1652 | ~12 | LOW |

**Testing Checklist**:
- [ ] Lightbox JS not delayed
- [ ] PhotoSwipe loads correctly
- [ ] CSS exclusions work

---

### /inc/integrations/litespeed.php

**Purpose**: LiteSpeed Cache hooks
**Load Priority**: 5
**Dependencies**: LiteSpeed Cache plugin
**Condition**: `defined('LSCWP_V')`

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| litespeed_media_ignore_remote_missing_sizes | functions.php:L835 | ~2 | LOW |
| litespeed_optm_img_attr | functions.php:L918-924 | ~10 | LOW |

**Testing Checklist**:
- [ ] Remote images don't error
- [ ] fetchpriority removed

---

### /inc/integrations/affilizz.php

**Purpose**: Affilizz integration
**Load Priority**: 5
**Dependencies**: Affilizz plugin

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| Affilizz preconnect | functions.php:L946-954 | ~12 | LOW |
| Affilizz footer render | functions.php:L960-986 | ~30 | MEDIUM |

**Testing Checklist**:
- [ ] Affilizz blocks load quickly
- [ ] Price blocks render
- [ ] Manual render triggers work

---

### /inc/shortcodes/index.php

**Purpose**: Load all shortcode files
**Load Priority**: 6

```php
<?php
// Shortcode loader
require_once __DIR__ . '/product-display.php';
require_once __DIR__ . '/ratings.php';
require_once __DIR__ . '/taxonomy-display.php';
require_once __DIR__ . '/related-content.php';
require_once __DIR__ . '/archive-grids.php';
require_once __DIR__ . '/comparison.php';
require_once __DIR__ . '/navigation.php';
require_once __DIR__ . '/misc.php';
```

---

### /inc/shortcodes/product-display.php

**Purpose**: Product-related shortcodes
**Dependencies**: ACF, utilities/helpers.php

| Shortcode | Function | Original Line | Risk |
|-----------|----------|---------------|------|
| `afficher_caracteristiques` | `afficher_caracteristiques_shortcode` | L3 | LOW |
| `ou_acheter` | `afficher_ou_acheter_shortcode` | L169 | LOW |
| `afficher_galerie` | `afficher_galerie_shortcode` | L196 | LOW |
| `afficher_pros_cons` | `afficher_pros_cons_shortcode` | L228 | LOW |
| `display_faq` | `display_faq_section` | L1002 | LOW |
| `display_conclusion` | `display_conclusion_section` | L1027 | LOW |
| `display_contenu` | `display_contenu_section` | L1054 | LOW |
| `display_presentation` | `display_presentation_section` | L1079 | LOW |
| `display_chapeau` | `display_chapeau_shortcode` | L613 | LOW |
| `afficher_custom_data` | `afficher_custom_data_shortcode` | L966 | LOW |

---

### /inc/shortcodes/ratings.php

**Purpose**: Rating display shortcodes
**Dependencies**: ACF, utilities/helpers.php

| Shortcode | Function | Original Line | Risk |
|-----------|----------|---------------|------|
| `afficher_notes` | `afficher_notes_shortcode` | L88 | LOW |
| `afficher_note_globale` | `afficher_note_globale_shortcode` | L138 | LOW |
| `afficher_note_globale_card` | `afficher_note_globale_shortcode_card` | L933 | LOW |

---

### /inc/shortcodes/taxonomy-display.php

**Purpose**: Taxonomy and category shortcodes
**Dependencies**: ACF

| Shortcode | Function | Original Line | Risk |
|-----------|----------|---------------|------|
| `show_acf_titre` | `display_acf_titre_shortcode` | L458 | LOW |
| `show_acf_contenu` | `display_acf_contenu_shortcode` | L494 | LOW |
| `show_acf_faq` | `display_acf_faq_shortcode` | L526 | LOW |
| `show_acf_chapeau` | `display_acf_chapeau_shortcode` | L631 | LOW |
| `display_category` | `display_category_info_shortcode` | L667 | MEDIUM |
| `display_last_updated_category` | `display_last_updated_category_info_shortcode` | L703 | LOW |
| `test_archive_field` | `test_archive_acf_field_shortcode` | L559 | LOW |
| `category_link` | `category_link_shortcode` | L1616 | LOW |
| `post_category_link` | `post_category_link_shortcode` | L1642 | LOW |

---

### /inc/shortcodes/related-content.php

**Purpose**: Related posts and content shortcodes
**Dependencies**: ACF, WP_Query, utilities/helpers.php

| Shortcode | Function | Original Line | Risk |
|-----------|----------|---------------|------|
| `linked_test_category` | `display_linked_test_category` | L823 | MEDIUM |
| `linked_test_category_post_thumbnail` | `display_linked_test_category_post_thumbnail` | L866 | MEDIUM |
| `linked_test_category_complete` | `display_linked_test_category_complete` | L903 | MEDIUM |
| `display_related_news` | `display_related_news_posts` | L1172 | MEDIUM |
| `display_related_test_categories` | `display_related_test_categories` | L1258 | MEDIUM |
| `display_related_news_for_test` | `display_related_news_posts_for_test` | L1363 | MEDIUM |
| `display_associated_tests` | `display_associated_tests_shortcode` | L1501 | MEDIUM |
| `display_related_buying_guides` | `display_universal_buying_guides` | L1835 | MEDIUM |
| `related_articles` | `display_related_articles` | L1977 | MEDIUM |
| `associated_news` | `display_associated_news` | L2311 | MEDIUM |
| `associated_products` | `display_associated_products` | L2518 | MEDIUM |
| `buying_guides_and_comparisons` | `display_buying_guides_and_comparisons` | L2577 | MEDIUM |

---

### /inc/shortcodes/archive-grids.php

**Purpose**: Archive/listing shortcodes
**Dependencies**: WP_Query, ACF, utilities/helpers.php

| Shortcode | Function | Original Line | Risk |
|-----------|----------|---------------|------|
| `latest_articles` | `display_latest_articles` | L2189 | MEDIUM |
| `latest_news` | `display_latest_news` | L2467 | MEDIUM |
| `marques_list` | `marques_pagination_shortcode` | L3177 | MEDIUM |
| `marque_content_list` | `marque_content_list_shortcode` | L3472 | MEDIUM |
| `author_content_list` | `author_content_list_shortcode` | L3704 | MEDIUM |
| `lm_terms_grid` | `lm_terms_grid_shortcode` | L3931 | MEDIUM |
| `lm_tests_grid` | `lm_tests_grid_shortcode` | L4107 | MEDIUM |
| `produits_populaires` | `lm_produits_populaires_shortcode` | L4500 | MEDIUM |
| `afficher_articles_pour_marque` | `afficher_articles_lies_a_marque_courante` | L1679 | HIGH |

**Associated Functions**:
- `clear_marques_cache()` - L3293
- `marques_display_pagination()` - L3300
- `lm_brand_listing_item_compat()` - L3560
- `lm_redirect_legacy_page_param()` - L3870
- `lm_register_*_pagination_rewrite()` - Multiple

---

### /inc/shortcodes/comparison.php

**Purpose**: Product comparison shortcodes
**Dependencies**: ACF, other shortcodes

| Shortcode | Function | Original Line | Risk |
|-----------|----------|---------------|------|
| `comparaison_form` | `wpb_comparaison_form_shortcode` | L326 | MEDIUM |
| `show_comparison` | `display_comparison_shortcode` | L350 | MEDIUM |
| `show_acf_relationship_data` | `display_acf_relationship_data` | L419 | LOW |

---

### /inc/shortcodes/navigation.php

**Purpose**: Navigation and menu shortcodes
**Dependencies**: ACF

| Shortcode | Function | Original Line | Risk |
|-----------|----------|---------------|------|
| `scrollable_menu` | `generate_scrollable_menu_shortcode` | L2801 | MEDIUM |
| `dynamic_toc` | `insert_static_toc` | L1550 | LOW |
| `acf_publications` | `acf_publications_shortcode` | L2726 | MEDIUM |

---

### /inc/shortcodes/misc.php

**Purpose**: Miscellaneous shortcodes
**Dependencies**: Varies

| Shortcode | Function | Original Line | Risk |
|-----------|----------|---------------|------|
| `archive_filtre` | `afficher_archive_filtre_shortcode` | L270 | MEDIUM |
| `display_post_dates` | `display_post_dates_shortcode` | L773 | LOW |
| `promotion_link` | `display_promotion_link` | L1326 | LOW |
| `show_acf_promotion_data` | `show_acf_promotion_data_shortcode` | L1579 | LOW |
| `social_share` | `custom_social_share_buttons` | L3133 | LOW |
| `qd_video` | Anonymous | L3673 | LOW |
| `lm_seo_only_first` | `lm_seo_only_first_shortcode` | L3912 | LOW |
| `search_title` | `get_search_title` | L1132 | LOW |
| `author_featured_image` | `display_author_featured_image` | L1142 | LOW |
| `c2s_widget` | Anonymous (functions.php) | L1888 | LOW |
| `show_term_c2s_widget` | Anonymous (functions.php) | L1937 | LOW |

---

### /inc/ajax/ajax-views.php

**Purpose**: View tracking AJAX handler
**Load Priority**: 7 (AJAX only)
**Dependencies**: None

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `lm_track_view()` | functions.php:L2699-2738 | ~45 | HIGH |
| wp_ajax_lm_track_view | functions.php:L2696-2697 | ~2 | HIGH |
| wp_ajax_nopriv_lm_track_view | functions.php:L2696-2697 | ~2 | HIGH |
| Views tracker script | functions.php:L2741-2778 | ~40 | MEDIUM |

**Testing Checklist**:
- [ ] View count increments
- [ ] Session storage prevents duplicates
- [ ] 7-day window calculates correctly
- [ ] No errors for non-singular pages

---

### /inc/ajax/ajax-filters.php

**Purpose**: AJAX filter/load more handlers
**Load Priority**: 7 (AJAX only)
**Dependencies**: utilities/helpers.php

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `load_more_articles()` | shortcode_list.php:L1766 | ~25 | HIGH |
| `load_more_articles_by_marque()` | shortcode_list.php:L1701 | ~45 | HIGH |

**Testing Checklist**:
- [ ] Load more button works
- [ ] Pagination returns correct posts
- [ ] No duplicate posts loaded
- [ ] AJAX responses valid JSON

---

### /inc/rss/feed-customization.php

**Purpose**: RSS feed customizations
**Load Priority**: 8 (Feed only)
**Dependencies**: ACF

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `labomaison_custom_rss_description()` | functions.php:L1041-1058 | ~20 | LOW |
| `labomaison_add_content_encoded()` | functions.php:L1062-1100 | ~45 | MEDIUM |
| `labomaison_add_enclosure()` | functions.php:L1105-1128 | ~30 | LOW |
| `labomaison_clean_guid()` | functions.php:L1135-1137 | ~5 | LOW |
| `lm_force_custom_rss_template()` | functions.php:L1154-1160 | ~10 | LOW |
| bloginfo_rss (home URL) | functions.php:L1029-1034 | ~8 | LOW |
| the_excerpt_rss hook | functions.php:L1037 | ~2 | LOW |
| the_content_feed hook | functions.php:L1038 | ~2 | LOW |
| rss2_item hooks | functions.php:L1102, L1130 | ~4 | LOW |
| get_the_guid hook | functions.php:L1134 | ~2 | LOW |
| feed_template hook | functions.php:L1161 | ~2 | LOW |
| Custom RSS include | functions.php:L1165-1170 | ~8 | LOW |

**Testing Checklist**:
- [ ] RSS feed validates
- [ ] Images have enclosures
- [ ] Descriptions clean
- [ ] GUIDs are permalinks
- [ ] Custom template loads

---

### /inc/analytics/ga4-tracking.php

**Purpose**: Google Analytics 4 with CookieYes
**Load Priority**: 9
**Dependencies**: None

| Function/Hook | Original Location | Lines | Risk |
|---------------|-------------------|-------|------|
| `lm_should_load_ga4()` | functions.php:L1178-1196 | ~20 | LOW |
| `lm_enqueue_ga4_head()` | functions.php:L1210-1454 | ~250 | HIGH |
| AdSense verification | functions.php:L1459-1464 | ~8 | LOW |

**Testing Checklist**:
- [ ] GA4 loads on public pages
- [ ] GA4 disabled for logged-in users
- [ ] CookieYes consent respected
- [ ] Page views tracked after consent
- [ ] AdSense verification present

---

## Migration Sequence

### Week 1: Foundation + LOW RISK

| Day | Module | Lines | Test Focus |
|-----|--------|-------|------------|
| 1 | utilities/helpers.php | ~200 | Function calls |
| 1 | utilities/security.php | ~100 | Response codes |
| 2 | setup/theme-support.php | ~80 | Comments, excerpts |
| 2 | setup/enqueue-assets.php | ~120 | Asset loading |
| 3 | setup/image-sizes.php | ~30 | Media library |
| 3 | integrations/generatepress.php | ~80 | Archive pages |
| 4 | integrations/wprocket.php | ~80 | Cache behavior |
| 4 | integrations/litespeed.php | ~30 | LiteSpeed |
| 5 | shortcodes/product-display.php | ~400 | Product pages |
| 5 | shortcodes/ratings.php | ~200 | Star ratings |

### Week 2: MEDIUM RISK Core

| Day | Module | Lines | Test Focus |
|-----|--------|-------|------------|
| 1 | admin/admin-columns.php | ~100 | Admin list |
| 1 | admin/editor-config.php | ~80 | Publishing |
| 2 | setup/admin-config.php | ~100 | Plugin init |
| 2 | hooks/content-filters.php | ~150 | Content rendering |
| 3 | rss/feed-customization.php | ~200 | RSS feed |
| 3 | integrations/affilizz.php | ~100 | Affilizz blocks |
| 4 | shortcodes/taxonomy-display.php | ~400 | Category pages |
| 5 | shortcodes/comparison.php | ~150 | Comparison |
| 5 | shortcodes/misc.php | ~200 | Various |

### Week 3: HIGH RISK

| Day | Module | Lines | Test Focus |
|-----|--------|-------|------------|
| 1 | hooks/query-modifications.php | ~100 | Archive sorting |
| 2 | hooks/template-redirects.php | ~250 | Redirects |
| 3 | integrations/wpgridbuilder.php | ~300 | All grids |
| 4 | integrations/rankmath.php | ~600 | SEO + breadcrumbs |
| 5 | shortcodes/related-content.php | ~800 | Related posts |

### Week 4: Final + Cleanup

| Day | Module | Lines | Test Focus |
|-----|--------|-------|------------|
| 1 | ajax/ajax-views.php | ~150 | View tracking |
| 1 | ajax/ajax-filters.php | ~200 | Load more |
| 2 | shortcodes/archive-grids.php | ~600 | Archive pages |
| 2 | shortcodes/navigation.php | ~400 | Menus |
| 3 | analytics/ga4-tracking.php | ~300 | Analytics |
| 4 | Full regression testing | - | All pages |
| 5 | Cleanup legacy files | - | Remove backups |

---

## New functions.php (Post-Migration)

```php
<?php
/**
 * Labomaison Theme Functions
 *
 * @package Labomaison
 * @version 2.0.0
 */

// Constants
define('LABOMAISON_VERSION', '2.0.0');
define('LABOMAISON_DIR', get_stylesheet_directory());
define('LABOMAISON_URI', get_stylesheet_directory_uri());
define('LABOMAISON_INC', LABOMAISON_DIR . '/inc');

// CSS Toggle (testing)
define('USE_REFACTORED_CSS', true);

// =========================================
// PRIORITY 1: Foundation (ALWAYS FIRST)
// =========================================
require_once LABOMAISON_INC . '/utilities/helpers.php';
require_once LABOMAISON_INC . '/utilities/security.php';

// =========================================
// PRIORITY 2: Theme Setup
// =========================================
require_once LABOMAISON_INC . '/setup/theme-support.php';
require_once LABOMAISON_INC . '/setup/enqueue-assets.php';
require_once LABOMAISON_INC . '/setup/image-sizes.php';

// =========================================
// PRIORITY 3: Admin Only
// =========================================
if (is_admin()) {
    require_once LABOMAISON_INC . '/admin/admin-columns.php';
    require_once LABOMAISON_INC . '/admin/editor-config.php';
    require_once LABOMAISON_INC . '/setup/admin-config.php';
}

// =========================================
// PRIORITY 4: Hooks
// =========================================
require_once LABOMAISON_INC . '/hooks/query-modifications.php';
require_once LABOMAISON_INC . '/hooks/template-redirects.php';
require_once LABOMAISON_INC . '/hooks/content-filters.php';

// =========================================
// PRIORITY 5: Plugin Integrations
// =========================================
if (class_exists('WP_Grid_Builder')) {
    require_once LABOMAISON_INC . '/integrations/wpgridbuilder.php';
}

if (class_exists('RankMath')) {
    require_once LABOMAISON_INC . '/integrations/rankmath.php';
}

// GeneratePress is parent theme
require_once LABOMAISON_INC . '/integrations/generatepress.php';

if (defined('WP_ROCKET_VERSION')) {
    require_once LABOMAISON_INC . '/integrations/wprocket.php';
}

if (defined('LSCWP_V')) {
    require_once LABOMAISON_INC . '/integrations/litespeed.php';
}

if (function_exists('affilizz_init') || class_exists('Affilizz\Core')) {
    require_once LABOMAISON_INC . '/integrations/affilizz.php';
}

// =========================================
// PRIORITY 6: Shortcodes
// =========================================
require_once LABOMAISON_INC . '/shortcodes/index.php';

// =========================================
// PRIORITY 7: AJAX Handlers
// =========================================
if (defined('DOING_AJAX') && DOING_AJAX) {
    require_once LABOMAISON_INC . '/ajax/ajax-views.php';
    require_once LABOMAISON_INC . '/ajax/ajax-filters.php';
}

// =========================================
// PRIORITY 8: RSS Customization
// =========================================
require_once LABOMAISON_INC . '/rss/feed-customization.php';

// =========================================
// PRIORITY 9: Analytics
// =========================================
require_once LABOMAISON_INC . '/analytics/ga4-tracking.php';
```

**Estimated new functions.php**: ~80 lines

---

## Summary

| Metric | Before | After |
|--------|--------|-------|
| Total Lines | ~8,000 | ~5,200 |
| Files | 2 | 25+ |
| functions.php | 3,426 lines | ~80 lines |
| shortcode_list.php | 4,600 lines | Split into 8 files |
| Load Conditions | None | Admin, AJAX, Plugin checks |

**Reduction**: ~35% code reduction through organization
**Maintainability**: Dramatically improved with clear module boundaries
