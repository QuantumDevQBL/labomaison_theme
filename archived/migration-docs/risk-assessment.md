# Risk Assessment Matrix - Labomaison Theme Migration

**Generated**: 2026-02-04
**Purpose**: Classify every function by migration risk level

---

## Risk Level Definitions

| Level | Icon | Criteria | Testing Required |
|-------|------|----------|------------------|
| **LOW** | :green_circle: | Pure functions, no side effects, no dependencies, simple filters | Basic verification |
| **MEDIUM** | :yellow_circle: | 1-2 dependencies, CPT/taxonomy registrations, ACF-dependent | Functional testing |
| **HIGH** | :red_circle: | 3+ dependencies, AJAX handlers, query modifications, global state | Extensive testing + monitoring |
| **CRITICAL** | :no_entry: | Security functions, core WP modifications, data structure changes | Full regression testing |

---

## Risk Assessment by Module

### :green_circle: LOW RISK Functions (Safe to Migrate First)

#### Theme Setup & Assets

| Function | File | Line | Rationale |
|----------|------|------|-----------|
| `enqueue_instagram_embed_script()` | functions.php | L27 | Simple echo, no dependencies |
| `enqueue_font_awesome()` | functions.php | L37 | wp_enqueue_style only |
| `wpse325327_add_excerpts_to_pages()` | functions.php | L63 | add_post_type_support only |
| `my_custom_fonts_enqueue()` | functions.php | L68 | wp_enqueue_script only |
| Image sizes (add_image_size) | functions.php | L865 | Static definitions |
| Comments disable hooks | functions.php | L871+ | Multiple simple hooks |

#### RSS/Feed Functions

| Function | File | Line | Rationale |
|----------|------|------|-----------|
| `adjust_rss_pubdate_timezone()` | functions.php | L687 | Pure date formatting |
| `custom_rss_pubdate()` | functions.php | L706 | Simple echo |
| `labomaison_clean_guid()` | functions.php | L1135 | Pure: get_permalink only |
| `lm_force_custom_rss_template()` | functions.php | L1154 | File existence check |

#### Helper Functions

| Function | File | Line | Rationale |
|----------|------|------|-----------|
| `generate_star_rating()` | shortcode_list.php | L1948 | Pure HTML generation |
| `generate_card_html()` | shortcode_list.php | L2150 | Pure HTML generation |
| `generate_content_card()` | shortcode_list.php | L2251 | Pure HTML generation |
| `generate_content_card_custom()` | shortcode_list.php | L2305 | Pure HTML generation |
| `lm_can_is_redacteur_scope()` | functions.php | L3243 | Pure URL check |
| `lm_can_current_url_clean()` | functions.php | L3252 | Pure URL formatting |
| `lm_breadcrumb_get_univers()` | functions.php | L2321 | Pure term traversal |
| `lm_map_post_category_to_test_term()` | functions.php | L2451 | Pure get_term_by |

#### Simple Shortcodes (ACF-only, no complex logic)

| Shortcode | Function | Line | Rationale |
|-----------|----------|------|-----------|
| `afficher_caracteristiques` | shortcode_list.php | L3 | ACF repeater display |
| `afficher_notes` | shortcode_list.php | L88 | ACF field + star SVG |
| `afficher_note_globale` | shortcode_list.php | L138 | Single ACF field |
| `ou_acheter` | shortcode_list.php | L169 | 2 ACF fields |
| `afficher_galerie` | shortcode_list.php | L196 | ACF gallery field |
| `afficher_pros_cons` | shortcode_list.php | L228 | 2 ACF fields |
| `display_chapeau` | shortcode_list.php | L613 | Single ACF field |
| `display_faq` | shortcode_list.php | L1002 | Single ACF field |
| `display_conclusion` | shortcode_list.php | L1027 | Single ACF field |
| `display_contenu` | shortcode_list.php | L1054 | Single ACF field |
| `display_presentation` | shortcode_list.php | L1079 | Single ACF field |
| `search_title` | shortcode_list.php | L1132 | No dependencies |
| `category_link` | shortcode_list.php | L1616 | Pure taxonomy link |
| `post_category_link` | shortcode_list.php | L1642 | Pure category link |
| `social_share` | shortcode_list.php | L3133 | Static HTML |
| `dynamic_toc` | shortcode_list.php | L1550 | Static HTML output |
| `lm_seo_only_first` | shortcode_list.php | L3912 | Simple conditional |

#### Filter Hooks (Simple transformations)

| Hook | Callback | Rationale |
|------|----------|-----------|
| `gettext` | Anonymous L837 | Simple string replace |
| `wp_lazy_loading_enabled` | `__return_true` | Constant return |
| `comments_open/pings_open` | `__return_false` | Constant return |
| `comments_array` | `__return_empty_array` | Constant return |
| `wpgb_lazy_load` | `__return_false` | Constant return |
| `litespeed_media_ignore_remote_missing_sizes` | `__return_true` | Constant return |

**Total LOW RISK: ~50 items**

---

### :yellow_circle: MEDIUM RISK Functions (Migrate with Caution)

#### ACF-Dependent Admin Functions

| Function | File | Line | Rationale |
|----------|------|------|-----------|
| `load_acf_scripts()` | functions.php | L55 | ACF dependency |
| Admin columns (test CPT) | functions.php | L130-161 | ACF get_field, HTML output |
| Taxonomy filters | functions.php | L163-209 | parse_query modification |
| `check_category_before_publishing()` | functions.php | L649 | REST API filter, validation |

#### Content Filters

| Function | File | Line | Rationale |
|----------|------|------|-----------|
| `add_image_dimensions()` | functions.php | L760 | Regex on content, file ops |
| `add_dimensions_to_affilizz_images()` | functions.php | L791 | Regex, file operations |
| `force_lazy_load_images()` | functions.php | L825 | Regex on content |
| render_block filter | functions.php | L432 | Shortcode injection |
| generate_archive_title filter | functions.php | L458 | ACF + HTML output |
| generate_after_loop filter | functions.php | L478 | ACF + HTML output |

#### RSS Functions (ACF-dependent)

| Function | File | Line | Rationale |
|----------|------|------|-----------|
| `labomaison_custom_rss_description()` | functions.php | L1041 | ACF chapeau field |
| `labomaison_add_content_encoded()` | functions.php | L1062 | Global $post, apply_filters |
| `labomaison_add_enclosure()` | functions.php | L1105 | File system operations |

#### WP Grid Builder Integration

| Hook/Function | File | Line | Rationale |
|---------------|------|------|-----------|
| generateblocks_query_loop_args | functions.php | L76 | Query modification |
| wp_grid_builder/blocks (affilizz) | functions.php | L89 | Block registration, ACF |
| wp_grid_builder/grid/the_object (author) | functions.php | L114 | ACF get_field |
| wp_grid_builder/grid/the_object (category) | functions.php | L213 | ACF get_field |
| Grid 6 sort | functions.php | L346 | Query args modification |
| Grid 29 filter | functions.php | L723 | Complex meta_query |

#### Rank Math Integration

| Hook/Function | File | Line | Rationale |
|---------------|------|------|-----------|
| rank_math/vars/register_extra_replacements | functions.php | L1970 | Multiple ACF fields |
| rank_math/json_ld (CollectionPage) | functions.php | L2126 | WP_Query, schema building |
| rank_math/json_ld (Product cleanup) | functions.php | L2232 | Schema modification |
| rank_math/sitemap/entry | functions.php | L2955 | URL validation |
| rank_math/frontend/title | functions.php | L3321 | String manipulation |
| rank_math/frontend/description | functions.php | L3372 | String manipulation |

#### Breadcrumb Helpers

| Function | File | Line | Rationale |
|----------|------|------|-----------|
| `lm_breadcrumb_pick_primary_category()` | functions.php | L2330 | Multi-plugin support |
| `lm_breadcrumb_get_featured_test_data()` | functions.php | L2388 | Multiple ACF fields |

#### Shortcodes with WP_Query

| Shortcode | Function | Line | Rationale |
|-----------|----------|------|-----------|
| `show_comparison` | shortcode_list.php | L350 | Calls other shortcodes |
| `display_category` | shortcode_list.php | L667 | Taxonomy operations |
| `display_last_updated_category` | shortcode_list.php | L703 | Options API |
| `linked_test_category` | shortcode_list.php | L823 | ACF relationship |
| `linked_test_category_post_thumbnail` | shortcode_list.php | L866 | ACF + images |
| `linked_test_category_complete` | shortcode_list.php | L903 | ACF + multiple fields |
| `display_related_news` | shortcode_list.php | L1172 | WP_Query |
| `display_related_test_categories` | shortcode_list.php | L1258 | WP_Query |
| `display_related_news_for_test` | shortcode_list.php | L1363 | WP_Query + ACF |
| `display_associated_tests` | shortcode_list.php | L1501 | ACF relationship |
| `display_related_buying_guides` | shortcode_list.php | L1835 | WP_Query |
| `related_articles` | shortcode_list.php | L1977 | WP_Query |
| `latest_articles` | shortcode_list.php | L2189 | WP_Query |
| `associated_news` | shortcode_list.php | L2311 | ACF + WP_Query |
| `latest_news` | shortcode_list.php | L2467 | WP_Query |
| `associated_products` | shortcode_list.php | L2518 | ACF relationship |
| `buying_guides_and_comparisons` | shortcode_list.php | L2577 | WP_Query |
| `marques_list` | shortcode_list.php | L3177 | WP_Query + Transients |
| `marque_content_list` | shortcode_list.php | L3472 | WP_Query |
| `author_content_list` | shortcode_list.php | L3704 | WP_Query |
| `lm_terms_grid` | shortcode_list.php | L3931 | WP_Query |
| `lm_tests_grid` | shortcode_list.php | L4107 | WP_Query + ACF |
| `produits_populaires` | shortcode_list.php | L4500 | WP_Query |

#### Rewrite Rules

| Function | File | Line | Rationale |
|----------|------|------|-----------|
| `pm_change_author_base()` | functions.php | L607 | Global $wp_rewrite |
| `lm_register_comparatifs_pagination_rewrite()` | shortcode_list.php | L3898 | Rewrite rules |
| `lm_register_dernier_test_pagination_rewrite()` | shortcode_list.php | L4378 | Rewrite rules |
| `lm_register_categorie_test_pagination_rewrite()` | shortcode_list.php | L4390 | Rewrite rules |

**Total MEDIUM RISK: ~60 items**

---

### :red_circle: HIGH RISK Functions (Extensive Testing Required)

#### Query Modifications

| Function | File | Line | Risk Factor |
|----------|------|------|-------------|
| `adjust_main_query_based_on_ratings()` | functions.php | L548 | pre_get_posts on archives, creates nested WP_Query |
| `add_custom_post_types_to_rss_feed()` | functions.php | L715 | pre_get_posts on feed |

#### Template Redirects

| Function | File | Line | Risk Factor |
|----------|------|------|-------------|
| `lm_guard_early_410()` | functions.php | L1707 | Priority 0, exits early |
| `lm_guard_redirect_over_max_pagination()` | functions.php | L1760 | Priority 1, 301 redirects |
| `lm_guard_brand_unused_410()` | functions.php | L1797 | Priority 2, 410 responses |
| Trailing slash redirect | functions.php | L2859 | 301 on all URLs |
| Marque feed redirect | functions.php | L513 | 301 redirects |
| custom_pre_handle_404 | functions.php | L995 | 404 handling modification |

#### Breadcrumbs (Complex logic)

| Function | File | Line | Risk Factor |
|----------|------|------|-------------|
| rank_math/frontend/breadcrumb/items | functions.php | L2463 | Complex conditional logic |
| rank_math/frontend/breadcrumb/html | functions.php | L2635 | HTML manipulation |
| Author breadcrumbs | functions.php | L2913 | Separate logic path |

#### Canonical Handlers

| Function | File | Line | Risk Factor |
|----------|------|------|-------------|
| Pagination canonical | functions.php | L3029 | URL modification |
| Tracking params canonical | functions.php | L3084 | URL modification |
| Marques canonical | functions.php | L3186 | URL modification |
| Redacteur canonical hardening | functions.php | L3269 | Output buffering |

#### AJAX Handlers

| Function | File | Line | Risk Factor |
|----------|------|------|-------------|
| `lm_track_view()` | functions.php | L2699 | Writes to database |
| `load_more_articles()` | shortcode_list.php | L1766 | AJAX + WP_Query |
| `load_more_articles_by_marque()` | shortcode_list.php | L1701 | AJAX + WP_Query |

#### WP Grid Builder (Complex queries)

| Function | File | Line | Risk Factor |
|----------|------|------|-------------|
| `grid_query_related_products_fill()` | functions.php | L262 | Complex query logic, 6-product fill |
| `grid_query_related_products_test_fill()` | functions.php | L367 | Similar complexity |
| Grid 30 views sort | functions.php | L2785 | Meta query on views |

#### Analytics

| Function | File | Line | Risk Factor |
|----------|------|------|-------------|
| `lm_enqueue_ga4_head()` | functions.php | L1210 | Complex JS injection, CookieYes bridge |
| `lm_should_load_ga4()` | functions.php | L1178 | Multiple condition checks |

#### Shortcodes with AJAX

| Shortcode | Function | Line | Risk Factor |
|-----------|----------|------|-------------|
| `afficher_articles_pour_marque` | shortcode_list.php | L1679 | AJAX load more |
| `archive_filtre` | shortcode_list.php | L270 | Filter UI + AJAX |
| `scrollable_menu` | shortcode_list.php | L2801 | Complex ACF + JS |
| `acf_publications` | shortcode_list.php | L2726 | Complex ACF repeater |

**Total HIGH RISK: ~25 items**

---

### :no_entry: CRITICAL RISK Functions (Full Regression Required)

#### Security/Response Functions

| Function | File | Line | Critical Factor |
|----------|------|------|-----------------|
| `lm_guard_send_410()` | functions.php | L1669 | HTTP response, exits |
| `lm_guard_send_301()` | functions.php | L1690 | HTTP redirect, exits |

#### Data Structure Impact

| Function | File | Line | Critical Factor |
|----------|------|------|-----------------|
| `update_last_updated_test_category()` | functions.php | L238 | Writes to options table |
| `initialize_post_views_for_existing_posts()` | functions.php | L615 | Bulk meta update (disabled) |

#### Plugin Init Delay

| Hook | File | Line | Critical Factor |
|------|------|------|-----------------|
| plugins_loaded (WPSP/Affilizz delay) | functions.php | L845 | Modifies plugin load order |

**Total CRITICAL RISK: ~5 items**

---

## Migration Order Recommendation

### Phase 1: LOW RISK (Week 1)
Migrate in this order:
1. `utilities/helpers.php` - All helper functions
2. `utilities/security.php` - Guard response functions
3. `setup/theme-support.php` - Theme setup basics
4. `setup/enqueue-assets.php` - Asset enqueueing
5. `setup/image-sizes.php` - Image sizes
6. Simple shortcodes (no WP_Query)

### Phase 2: MEDIUM RISK (Week 2)
1. `admin/admin-columns.php` - Admin customizations
2. `admin/editor-config.php` - Editor config
3. `integrations/generatepress.php` - Parent theme hooks
4. `integrations/wprocket.php` - Cache exclusions
5. `integrations/litespeed.php` - LiteSpeed hooks
6. `hooks/content-filters.php` - Content filters
7. `rss/feed-customization.php` - RSS functions
8. Medium-complexity shortcodes

### Phase 3: HIGH RISK (Week 3)
1. `hooks/query-modifications.php` - Query filters
2. `hooks/template-redirects.php` - Redirect logic
3. `integrations/wpgridbuilder.php` - Grid Builder
4. `integrations/rankmath.php` - SEO integration
5. `ajax/ajax-views.php` - View tracking
6. `ajax/ajax-filters.php` - AJAX filters
7. Complex shortcodes with AJAX

### Phase 4: CRITICAL + FINAL (Week 4)
1. Security functions (already in utilities/security.php)
2. Final shortcodes
3. Full regression testing
4. Cleanup legacy file

---

## Testing Strategy by Risk Level

### LOW RISK Testing
- Visual inspection
- Single function call verification
- No full page testing required

### MEDIUM RISK Testing
- Frontend page load verification
- Admin interface check
- ACF field display verification
- Basic WP_Query result check

### HIGH RISK Testing
- Full page functionality test
- AJAX endpoint testing
- Browser console monitoring
- PHP error log monitoring
- Multiple scenario testing

### CRITICAL RISK Testing
- Full site regression
- SEO impact verification
- Security audit
- Performance benchmarking
- 24-hour monitoring after deploy

---

## Risk Mitigation Strategies

### For HIGH/CRITICAL Functions

1. **Feature Flags**
```php
if (defined('LM_USE_NEW_GUARD') && LM_USE_NEW_GUARD) {
    require_once LABOMAISON_INC . '/hooks/template-redirects.php';
} else {
    // Original code inline
}
```

2. **Gradual Rollout**
- Test on staging first
- Deploy to 10% of traffic
- Monitor for 24 hours
- Full rollout

3. **Instant Rollback**
```php
// Emergency: Revert to original
define('LM_USE_LEGACY_FUNCTIONS', true);
```

4. **Logging**
```php
if (WP_DEBUG) {
    error_log('[LM Migration] Function X called with: ' . print_r($args, true));
}
```

---

## Summary Statistics

| Risk Level | Count | Percentage |
|------------|-------|------------|
| LOW | ~50 | 29% |
| MEDIUM | ~60 | 34% |
| HIGH | ~25 | 14% |
| CRITICAL | ~5 | 3% |
| **TOTAL** | **~175** | **100%** |

---

## Next Steps

1. Create Module Assignment Plan (Task 1.4) - Detailed file-by-file mapping
2. Begin Phase 2: Foundation Setup
3. Start LOW RISK migrations
