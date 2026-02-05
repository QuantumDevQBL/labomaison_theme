# Dependency Graph - Labomaison Theme

**Generated**: 2026-02-04
**Purpose**: Map all function interdependencies for safe migration

---

## Load Priority Hierarchy

```
┌─────────────────────────────────────────────────────────────────────┐
│ PRIORITY 0: WordPress Core (Already Loaded)                         │
├─────────────────────────────────────────────────────────────────────┤
│ PRIORITY 1: Foundation Utilities (Must load first)                  │
│   └── utilities/helpers.php                                         │
│   └── utilities/security.php                                        │
│   └── utilities/formatting.php                                      │
├─────────────────────────────────────────────────────────────────────┤
│ PRIORITY 2: Theme Setup (Core configuration)                        │
│   └── setup/theme-support.php                                       │
│   └── setup/enqueue-assets.php                                      │
│   └── setup/image-sizes.php                                         │
├─────────────────────────────────────────────────────────────────────┤
│ PRIORITY 3: Admin (Backend only)                                    │
│   └── admin/admin-columns.php                                       │
│   └── admin/editor-config.php                                       │
│   └── setup/admin-config.php                                        │
├─────────────────────────────────────────────────────────────────────┤
│ PRIORITY 4: Hooks & Filters (Before content)                        │
│   └── hooks/query-modifications.php                                 │
│   └── hooks/template-redirects.php                                  │
│   └── hooks/content-filters.php                                     │
├─────────────────────────────────────────────────────────────────────┤
│ PRIORITY 5: Plugin Integrations (Conditional)                       │
│   └── integrations/wpgridbuilder.php (if WP Grid Builder active)    │
│   └── integrations/rankmath.php (if Rank Math active)               │
│   └── integrations/affilizz.php (if Affilizz active)                │
│   └── integrations/generatepress.php (parent theme)                 │
│   └── integrations/wprocket.php (if WP Rocket active)               │
│   └── integrations/litespeed.php (if LiteSpeed active)              │
├─────────────────────────────────────────────────────────────────────┤
│ PRIORITY 6: Shortcodes (Content rendering)                          │
│   └── shortcodes/*.php (all shortcode files)                        │
├─────────────────────────────────────────────────────────────────────┤
│ PRIORITY 7: AJAX Handlers (After dependencies)                      │
│   └── ajax/ajax-views.php                                           │
│   └── ajax/ajax-filters.php                                         │
├─────────────────────────────────────────────────────────────────────┤
│ PRIORITY 8: RSS/Feed (Specialized)                                  │
│   └── rss/feed-customization.php                                    │
├─────────────────────────────────────────────────────────────────────┤
│ PRIORITY 9: Analytics (Non-blocking)                                │
│   └── analytics/ga4-tracking.php                                    │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Detailed Dependency Chains

### 1. Utility Functions (Foundation Layer)

```
utilities/helpers.php (LOAD FIRST - No dependencies)
├── generate_star_rating()
│   └── Used by: afficher_notes_shortcode, afficher_note_globale_shortcode
├── generate_card_html()
│   └── Used by: display_related_articles, display_latest_articles
├── generate_content_card()
│   └── Used by: display_associated_news, display_latest_news
├── generate_product_card()
│   └── Used by: display_associated_products
│   └── Depends on: ACF get_field()
└── generate_content_card_custom()
    └── Used by: various shortcodes

utilities/security.php (LOAD FIRST - No dependencies)
├── lm_guard_send_410()
│   └── Used by: lm_guard_early_410, lm_guard_brand_unused_410
└── lm_guard_send_301()
    └── Used by: lm_guard_redirect_over_max_pagination
```

### 2. Theme Setup Dependencies

```
setup/theme-support.php (No dependencies)
├── wpse325327_add_excerpts_to_pages()
│   └── add_post_type_support('page', 'excerpt')
└── Comments disable hooks
    └── Multiple init/admin hooks

setup/enqueue-assets.php (No dependencies)
├── enqueue_instagram_embed_script()
│   └── Conditional: is_singular(['post', 'marque', 'test'])
├── enqueue_font_awesome()
│   └── wp_enqueue_style() only
├── my_custom_fonts_enqueue()
│   └── Requires: /inc/js/custom.js file
└── load_acf_scripts()
    └── Requires: ACF Plugin active
```

### 3. Admin Dependencies

```
admin/admin-columns.php
├── manage_test_posts_columns filter
│   └── No dependencies
└── manage_test_posts_custom_column action
    └── Depends on: ACF get_field('marque')

admin/editor-config.php
└── check_category_before_publishing()
    └── Hook: rest_pre_insert_post
    └── No function dependencies
```

### 4. Query Modifications

```
hooks/query-modifications.php
├── adjust_main_query_based_on_ratings()
│   └── Hook: pre_get_posts
│   └── Creates: new WP_Query (performance concern)
│   └── Uses: meta_query on note_globale, post_views_count
│   └── Scope: is_tax('categorie_test') || is_tax('etiquette_test')
│
└── add_custom_post_types_to_rss_feed()
    └── Hook: pre_get_posts
    └── Scope: is_feed()
    └── No dependencies
```

### 5. Template Redirects (Order Critical!)

```
hooks/template-redirects.php (PRIORITY ORDER MATTERS!)

PRIORITY 0: lm_guard_early_410()
├── Checks: ?w=, _load_more, /contents/, /shop/
├── Calls: lm_guard_send_410()
└── MUST RUN FIRST

PRIORITY 1: lm_guard_redirect_over_max_pagination()
├── Checks: paged > max_num_pages
├── Calls: lm_guard_send_301()
└── Uses: global $wp_query

PRIORITY 2: lm_guard_brand_unused_410()
├── Checks: is_singular('marque') with no content
├── Calls: lm_guard_send_410()
├── Creates: 2x WP_Query
└── Uses: ACF meta_query on 'marque' field

PRIORITY default: trailing_slash_redirect() [Anonymous L2859]
├── Forces trailing slash on all URLs
└── wp_safe_redirect() 301

PRIORITY default: custom RSS template redirect [Anonymous L1165]
├── Scope: is_feed()
└── Includes custom feed-rss2.php
```

### 6. Content Filters Chain

```
hooks/content-filters.php (Order may matter for content manipulation)

the_content filter:
├── add_image_dimensions() [Priority 10]
│   └── Regex manipulation of <img> tags
│   └── Uses: getimagesize(), wp_upload_dir()
│
├── add_dimensions_to_affilizz_images() [Priority 10]
│   └── Specific to .affilizz-icon images
│   └── Uses: getimagesize()
│
└── force_lazy_load_images() [Priority 10]
    └── Adds loading="lazy" attribute
    └── Regex manipulation

render_block filter:
└── Anonymous (L432)
    └── Replaces specific className blocks with shortcodes
    └── Classes: query_loop_headline, query_loop_headline_post_thumbnail, etc.
    └── Calls: do_shortcode() implicitly
```

### 7. WP Grid Builder Integration

```
integrations/wpgridbuilder.php
├── REQUIRES: class_exists('WP_Grid_Builder')
│
├── wp_grid_builder/blocks filter
│   └── Adds affilizz_button block
│   └── Uses: ACF get_field('bouton_affiliz')
│
├── wp_grid_builder/grid/the_object filter
│   ├── Grid 19: Author featured image
│   │   └── Uses: ACF get_field('featured_image', 'user_')
│   └── Grid 10: Category featured image
│       └── Uses: ACF get_field('featured', 'categorie_test_')
│
└── wp_grid_builder/grid/query_args filter
    ├── Grid 6: Sort by note_globale
    ├── Grid 23: Related products (grid_query_related_products_fill)
    │   └── Uses: ACF get_field('produit_associe')
    │   └── Creates: WP_Query
    ├── Grid 24: Related test (grid_query_related_products_test_fill)
    │   └── Uses: ACF, WP_Query
    ├── Grid 26: Author filter on is_author()
    ├── Grid 29: Exclude empty content
    └── Grid 30: Sort by post_views_7d
```

### 8. Rank Math Integration

```
integrations/rankmath.php
├── REQUIRES: class_exists('RankMath')
│
├── rank_math/vars/register_extra_replacements
│   └── Registers: lm_nom, lm_chapeau, lm_conclusion, lm_note, etc.
│   └── Uses: ACF get_field(), get_post_meta()
│
├── rank_math/json_ld filter (Priority 99)
│   └── CollectionPage schema for is_tax('categorie_test')
│   └── Creates: WP_Query for ItemList
│
├── rank_math/json_ld filter (Priority 999)
│   └── Cleans Product schema on is_singular('test')
│   └── Removes invalid review/offers
│
├── rank_math/frontend/breadcrumb/items filter
│   ├── Uses: lm_breadcrumb_get_univers()
│   ├── Uses: lm_breadcrumb_pick_primary_category()
│   ├── Uses: lm_breadcrumb_get_featured_test_data()
│   └── Uses: lm_map_post_category_to_test_term()
│
├── rank_math/frontend/breadcrumb/html filter
│   └── Modifies last crumb to be clickable
│
├── rank_math/sitemap/entry filter (Priority 99)
│   └── Cleans sitemap URLs
│
├── rank_math/frontend/canonical filter (Multiple)
│   ├── Pagination auto-referent (Priority 99)
│   ├── Tracking params removal (Priority 99)
│   ├── Marques archive (Priority 99)
│   └── Redacteur hardening (Priority 99)
│
├── rank_math/frontend/title filter (Priority 99)
│   └── Pagination title normalization
│
└── rank_math/frontend/description filter (Priority 99)
    └── Tag description pagination suffix

BREADCRUMB HELPER DEPENDENCIES:
lm_breadcrumb_get_univers($term)
└── Pure function, no dependencies

lm_breadcrumb_pick_primary_category($post_id)
├── Checks: Yoast _yoast_wpseo_primary_category
├── Checks: Rank Math rank_math_primary_category
└── Fallback: get_the_terms with depth calculation

lm_breadcrumb_get_featured_test_data($post_id)
├── Uses: ACF get_field('produit_vedette')
├── Uses: ACF get_field('marque')
└── Uses: ACF get_field('nom')

lm_map_post_category_to_test_term($cat)
└── Pure function: get_term_by('slug')
```

### 9. Shortcode Dependencies

```
shortcodes/shortcode-*.php

HIGH DEPENDENCY SHORTCODES:
├── display_comparison_shortcode()
│   └── Calls: [afficher_caracteristiques], [afficher_notes], [afficher_pros_cons]
│   └── All three must be registered BEFORE
│
├── afficher_articles_pour_marque() / afficher_articles_lies_a_marque_courante()
│   └── Uses: load_more_articles_by_marque()
│   └── Uses: render_post_item_for_marque()
│   └── Registers AJAX handler
│
├── display_related_news_for_test()
│   └── Uses: render_post_item_for_test()
│
└── marques_pagination_shortcode()
    └── Uses: marques_display_pagination()
    └── Uses: clear_marques_cache()
    └── Uses: Transients API

HELPER FUNCTION DEPENDENCIES (Must be in same file or loaded before):
├── generate_star_rating() - Used by multiple rating shortcodes
├── generate_card_html() - Used by article shortcodes
├── render_post_item_for_test() - Used by test-related shortcodes
├── render_post_item_for_marque() - Used by brand shortcodes
└── lm_pagination_markup_compat() - Used by pagination shortcodes
```

### 10. AJAX Handlers

```
ajax/ajax-views.php
├── lm_track_view()
│   └── Hooks: wp_ajax_lm_track_view, wp_ajax_nopriv_lm_track_view
│   └── Uses: update_post_meta('lm_views_daily'), update_post_meta('post_views_7d')
│   └── No function dependencies
│
└── Views tracker script enqueue
    └── Inline JS with LM_VIEWS object
    └── Uses: admin_url('admin-ajax.php')

ajax/ajax-filters.php
├── load_more_articles()
│   └── Needs: wp_ajax_load_more_articles registration
│   └── Uses: WP_Query
│   └── Uses: render_post_item_for_marque() [from shortcodes]
│
└── load_more_articles_by_marque()
    └── Called by afficher_articles_pour_marque shortcode
    └── Uses: WP_Query
```

### 11. RSS/Feed Customization

```
rss/feed-customization.php
├── STANDALONE MODULE - Few dependencies
│
├── labomaison_custom_rss_description()
│   └── Uses: ACF get_field('chapeau')
│   └── Fallback: get_the_excerpt()
│
├── labomaison_add_content_encoded()
│   └── Uses: global $post
│   └── Uses: wp_get_attachment_image_url()
│   └── Uses: apply_filters('the_content')
│
├── labomaison_add_enclosure()
│   └── Uses: get_the_post_thumbnail_url()
│   └── Uses: filesize() for length
│
├── labomaison_clean_guid()
│   └── Pure: get_permalink()
│
└── lm_force_custom_rss_template()
    └── Requires: /feed-rss2.php file exists
```

### 12. Analytics (GA4)

```
analytics/ga4-tracking.php
├── STANDALONE MODULE - No theme dependencies
│
├── lm_should_load_ga4()
│   └── Checks: is_user_logged_in(), is_preview(), post status, preprod
│   └── No dependencies
│
└── lm_enqueue_ga4_head()
    └── Enqueues GTM script
    └── Inline JS for Consent Mode
    └── CookieYes bridge code
    └── Depends on: lm_should_load_ga4()
```

---

## Circular Dependencies (NONE FOUND)

After analysis, no circular dependencies were identified. All dependency chains are linear.

---

## Critical Path Analysis

### Functions That MUST Load First

1. **Security helpers** (lm_guard_send_410, lm_guard_send_301)
   - Used immediately on template_redirect priority 0

2. **Helper utilities** (generate_star_rating, generate_card_html, etc.)
   - Used by shortcodes which may be called during content rendering

3. **Breadcrumb helpers** (lm_breadcrumb_get_univers, etc.)
   - Used by Rank Math integration immediately

### Functions That CAN Load Later

1. **AJAX handlers** - Only needed when DOING_AJAX
2. **Admin functions** - Only needed when is_admin()
3. **RSS functions** - Only needed when is_feed()
4. **Analytics** - Non-blocking, can be last

---

## Plugin Dependency Matrix

| Function Group | ACF | WPGB | RankMath | GeneratePress |
|----------------|-----|------|----------|---------------|
| utilities/helpers.php | Some | - | - | - |
| setup/*.php | Yes | - | - | - |
| admin/*.php | Yes | - | - | - |
| integrations/wpgridbuilder.php | Yes | **Required** | - | - |
| integrations/rankmath.php | Yes | - | **Required** | - |
| integrations/generatepress.php | - | - | - | **Required** |
| shortcodes/*.php | **Required** | - | - | - |
| ajax/*.php | Some | - | - | - |
| hooks/*.php | Some | - | - | - |

---

## Recommended Loading Order

```php
// functions.php

// PRIORITY 0: Constants
define('LABOMAISON_VERSION', '2.0.0');
define('LABOMAISON_DIR', get_stylesheet_directory());
define('LABOMAISON_INC', LABOMAISON_DIR . '/inc');

// PRIORITY 1: Foundation (ALWAYS FIRST)
require_once LABOMAISON_INC . '/utilities/helpers.php';
require_once LABOMAISON_INC . '/utilities/security.php';

// PRIORITY 2: Theme Setup
require_once LABOMAISON_INC . '/setup/theme-support.php';
require_once LABOMAISON_INC . '/setup/enqueue-assets.php';
require_once LABOMAISON_INC . '/setup/image-sizes.php';

// PRIORITY 3: Admin Only
if (is_admin()) {
    require_once LABOMAISON_INC . '/admin/admin-columns.php';
    require_once LABOMAISON_INC . '/admin/editor-config.php';
    require_once LABOMAISON_INC . '/setup/admin-config.php';
}

// PRIORITY 4: Hooks (Before content rendering)
require_once LABOMAISON_INC . '/hooks/query-modifications.php';
require_once LABOMAISON_INC . '/hooks/template-redirects.php';
require_once LABOMAISON_INC . '/hooks/content-filters.php';

// PRIORITY 5: Plugin Integrations (Conditional)
if (class_exists('WP_Grid_Builder')) {
    require_once LABOMAISON_INC . '/integrations/wpgridbuilder.php';
}

if (class_exists('RankMath')) {
    require_once LABOMAISON_INC . '/integrations/rankmath.php';
}

// GeneratePress is parent, always load
require_once LABOMAISON_INC . '/integrations/generatepress.php';

if (class_exists('WP_Rocket_Core')) {
    require_once LABOMAISON_INC . '/integrations/wprocket.php';
}

if (defined('LSCWP_V')) {
    require_once LABOMAISON_INC . '/integrations/litespeed.php';
}

// PRIORITY 6: Shortcodes
require_once LABOMAISON_INC . '/shortcodes/index.php'; // Loads all shortcodes

// PRIORITY 7: AJAX Only
if (defined('DOING_AJAX') && DOING_AJAX) {
    require_once LABOMAISON_INC . '/ajax/ajax-views.php';
    require_once LABOMAISON_INC . '/ajax/ajax-filters.php';
}

// PRIORITY 8: RSS Only
if (is_feed()) {
    require_once LABOMAISON_INC . '/rss/feed-customization.php';
}

// PRIORITY 9: Analytics (Non-blocking)
require_once LABOMAISON_INC . '/analytics/ga4-tracking.php';
```

---

## Next Steps

1. Complete Risk Assessment Matrix (Task 1.3)
2. Create Module Assignment Plan (Task 1.4)
3. Begin Phase 2: Foundation Setup
