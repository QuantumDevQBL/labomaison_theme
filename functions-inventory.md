# Functions Inventory - Labomaison Theme

**Generated**: 2026-02-04
**Source Files**: `functions.php` (3,426 lines), `inc/shortcodes/shortcode_list.php` (~4,600 lines)
**Total Lines**: ~8,000 lines

---

## Table of Contents

1. [functions.php Analysis](#functionsphp-analysis)
   - [Named Functions](#named-functions)
   - [Actions & Filters](#actions--filters)
   - [Inline Shortcodes](#inline-shortcodes)
2. [shortcode_list.php Analysis](#shortcode_listphp-analysis)
   - [Shortcode Functions](#shortcode-functions)
   - [Helper Functions](#helper-functions)
3. [Categorized Inventory](#categorized-inventory)
4. [Plugin Dependencies](#plugin-dependencies)
5. [Global Variables](#global-variables)

---

## functions.php Analysis

### Named Functions

| Function | Lines | Purpose | Dependencies | Risk |
|----------|-------|---------|--------------|------|
| `enqueue_instagram_embed_script()` | L27-34 | Add Instagram/TikTok embed scripts to head | None | LOW |
| `enqueue_font_awesome()` | L37-39 | Enqueue Font Awesome CSS | None | LOW |
| `my_track_post_views()` | L42-52 | Track post views (commented out) | `$post` global | LOW |
| `load_acf_scripts()` | L55-57 | Load ACF form head scripts | ACF Plugin | MEDIUM |
| `wpse325327_add_excerpts_to_pages()` | L63-66 | Add excerpt support to pages | None | LOW |
| `my_custom_fonts_enqueue()` | L68-73 | Enqueue custom.js | None | LOW |
| `update_last_updated_test_category()` | L238-256 | Update last modified category option | None | LOW |
| `grid_query_related_products_fill()` | L262-342 | Fill related products grid (ID 23) | ACF, WP_Query | MEDIUM |
| `grid_query_related_products_test_fill()` | L367-425 | Fill related products test grid (ID 24) | ACF, WP_Query | MEDIUM |
| `adjust_main_query_based_on_ratings()` | L548-602 | Modify archive query for ratings sort | WP_Query | HIGH |
| `pm_change_author_base()` | L607-611 | Change author URL base to /redacteur/ | `$wp_rewrite` global | MEDIUM |
| `initialize_post_views_for_existing_posts()` | L615-646 | Initialize post views meta (one-time) | None | LOW |
| `check_category_before_publishing()` | L649-681 | Validate category on publish | None | MEDIUM |
| `adjust_rss_pubdate_timezone()` | L687-704 | Adjust RSS date timezone | None | LOW |
| `custom_rss_pubdate()` | L706-710 | Custom RSS pubDate output | `$post` global | LOW |
| `add_custom_post_types_to_rss_feed()` | L715-720 | Add CPTs to RSS feed | WP_Query | LOW |
| `exclude_empty_content_or_no_associations()` | L723-756 | Grid 29 query filter | WP Grid Builder | MEDIUM |
| `add_image_dimensions()` | L760-787 | Auto-add image dimensions | None | MEDIUM |
| `add_dimensions_to_affilizz_images()` | L791-810 | Add dimensions to Affilizz images | None | MEDIUM |
| `force_lazy_load_images()` | L825-829 | Force lazy loading on images | None | LOW |
| `labomaison_custom_rss_description()` | L1041-1058 | Custom RSS description from ACF | ACF | LOW |
| `labomaison_add_content_encoded()` | L1062-1100 | Add content:encoded to RSS | `$post` global | MEDIUM |
| `labomaison_add_enclosure()` | L1105-1128 | Add enclosure to RSS | `$post` global | LOW |
| `labomaison_clean_guid()` | L1135-1137 | Clean RSS GUID | None | LOW |
| `lm_force_custom_rss_template()` | L1154-1160 | Force custom RSS template | None | LOW |
| `lm_should_load_ga4()` | L1178-1196 | Check if GA4 should load | None | LOW |
| `lm_enqueue_ga4_head()` | L1210-1454 | Enqueue GA4 + CookieYes bridge | None | HIGH |
| `lm_guard_send_410()` | L1669-1687 | Send 410 response | None | CRITICAL |
| `lm_guard_send_301()` | L1690-1700 | Send 301 redirect | None | CRITICAL |
| `lm_guard_early_410()` | L1707-1752 | Early 410 for spam URLs | None | HIGH |
| `lm_guard_redirect_over_max_pagination()` | L1760-1791 | Redirect over-max pagination | WP_Query | HIGH |
| `lm_guard_brand_unused_410()` | L1797-1860 | 410 for empty brand pages | WP_Query | HIGH |
| `lm_breadcrumb_get_univers()` | L2321-2328 | Get root term for breadcrumbs | None | LOW |
| `lm_breadcrumb_pick_primary_category()` | L2330-2371 | Pick primary category | Yoast/RankMath | MEDIUM |
| `lm_breadcrumb_get_featured_test_data()` | L2388-2444 | Get featured test for breadcrumb | ACF | MEDIUM |
| `lm_map_post_category_to_test_term()` | L2451-2457 | Map category to categorie_test | None | LOW |
| `lm_track_view()` | L2699-2738 | AJAX view tracking handler | None | HIGH |
| `lm_can_is_redacteur_scope()` | L3243-3249 | Check if /redacteur/ scope | None | LOW |
| `lm_can_current_url_clean()` | L3252-3262 | Get clean current URL | None | LOW |

### Actions & Filters

#### Theme Setup & Assets

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `wp_head` | `enqueue_instagram_embed_script` | 10 | Instagram/TikTok embeds | LOW |
| `wp_enqueue_scripts` | `enqueue_font_awesome` | 10 | Font Awesome CSS | LOW |
| `wp_head` | `load_acf_scripts` | 10 | ACF form head | MEDIUM |
| `init` | `wpse325327_add_excerpts_to_pages` | 10 | Page excerpts | LOW |
| `wp_enqueue_scripts` | `my_custom_fonts_enqueue` | 10 | Custom JS | LOW |
| `wp_enqueue_scripts` | `lm_enqueue_ga4_head` | 20 | GA4 tracking | HIGH |
| `wp_enqueue_scripts` | Anonymous (L1142) | 100 | Dequeue ACF styles | LOW |
| `wp_enqueue_scripts` | Anonymous (L2741) | 20 | Views tracker script | MEDIUM |
| `wp_enqueue_scripts` | Anonymous (L2806) | 20 | C2S anti-CLS script | LOW |
| `wp_enqueue_scripts` | Anonymous (L3415) | 20 | Refactored CSS toggle | LOW |

#### WP Grid Builder Integration

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `generateblocks_query_loop_args` | Anonymous (L76) | 10 | Grid pagination fix | MEDIUM |
| `wp_grid_builder/blocks` | Anonymous (L89) | 10 | Affilizz button block | MEDIUM |
| `wp_grid_builder/grid/the_object` | Anonymous (L114) | 10 | Author featured image (Grid 19) | MEDIUM |
| `wp_grid_builder/grid/the_object` | Anonymous (L213) | 10 | Category featured (Grid 10) | MEDIUM |
| `wp_grid_builder/grid/query_args` | `grid_query_related_products_fill` | 10 | Related products (Grid 23) | MEDIUM |
| `wp_grid_builder/grid/query_args` | Anonymous (L346) | 10 | Sort by note_globale (Grid 6) | MEDIUM |
| `wp_grid_builder/grid/query_args` | `grid_query_related_products_test_fill` | 10 | Related test (Grid 24) | MEDIUM |
| `wp_grid_builder/grid/query_args` | `exclude_empty_content_or_no_associations` | 10 | Filter Grid 29 | MEDIUM |
| `wp_grid_builder/grid/query_args` | Anonymous (L928) | 10 | Author filter (Grid 26) | LOW |
| `wp_grid_builder/grid/query_args` | Anonymous (L2785) | 50 | Sort by views 7d (Grid 30) | MEDIUM |
| `wpgb_lazy_load` | `__return_false` | 10 | Disable WPGB lazy load | LOW |

#### Admin Customizations

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `manage_test_posts_columns` | Anonymous (L130) | 10 | Add Marque column | LOW |
| `manage_test_posts_custom_column` | Anonymous (L136) | 10 | Populate Marque column | LOW |
| `restrict_manage_posts` | Anonymous (L163) | 10 | Taxonomy dropdowns | LOW |
| `parse_query` | Anonymous (L186) | 10 | Filter by taxonomy | MEDIUM |
| `admin_menu` | Anonymous (L882) | 10 | Remove comments menu | LOW |
| `wp_dashboard_setup` | Anonymous (L887) | 10 | Remove comments widget | LOW |
| `admin_init` | Anonymous (L892) | 10 | Redirect comments page | LOW |
| `admin_init` | Anonymous (L913) | 10 | Remove discussion metabox | LOW |

#### Content Filters

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `the_content` | `add_image_dimensions` | 10 | Add image dimensions | MEDIUM |
| `the_content` | `add_dimensions_to_affilizz_images` | 10 | Affilizz image dimensions | MEDIUM |
| `the_content` | `force_lazy_load_images` | 10 | Force lazy loading | LOW |
| `generate_archive_title` | Anonymous (L458) | 10 | ACF chapeau on archives | LOW |
| `generate_after_loop` | Anonymous (L478) | 10 | ACF content/FAQ after loop | LOW |
| `render_block` | Anonymous (L432) | 10 | Query loop headline shortcodes | MEDIUM |
| `gettext` | Anonymous (L837) | 10 | Translate search results | LOW |

#### Query Modifications

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `pre_get_posts` | `adjust_main_query_based_on_ratings` | 10 | Sort archives by rating | HIGH |
| `pre_get_posts` | `add_custom_post_types_to_rss_feed` | 10 | Add CPTs to RSS | LOW |

#### RSS Feed

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `bloginfo_rss` | Anonymous (L1029) | 10 | Force RSS link to home | LOW |
| `the_excerpt_rss` | `labomaison_custom_rss_description` | 10 | Custom RSS description | LOW |
| `the_content_feed` | `labomaison_custom_rss_description` | 10 | Custom feed content | LOW |
| `rss2_item` | `labomaison_add_content_encoded` | 10 | Add content:encoded | MEDIUM |
| `rss2_item` | `labomaison_add_enclosure` | 10 | Add enclosure | LOW |
| `get_the_guid` | `labomaison_clean_guid` | 10 | Clean GUID | LOW |
| `feed_template` | `lm_force_custom_rss_template` | 10 | Custom RSS template | LOW |

#### Template Redirects & SEO

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `template_redirect` | Anonymous (L513) | 10 | Marque feed redirect | MEDIUM |
| `template_redirect` | `lm_guard_early_410` | 0 | Early 410 guard | HIGH |
| `template_redirect` | `lm_guard_redirect_over_max_pagination` | 1 | Over-max redirect | HIGH |
| `template_redirect` | `lm_guard_brand_unused_410` | 2 | Empty brand 410 | HIGH |
| `template_redirect` | Anonymous (L1165) | 0 | Custom RSS include | LOW |
| `template_redirect` | Anonymous (L2859) | 0 | Force trailing slash | HIGH |
| `template_redirect` | Anonymous (L3278) | 0 | Canonical hardening | MEDIUM |
| `pre_handle_404` | `custom_pre_handle_404` | 10 | Pagination 404 handling | MEDIUM |

#### Rank Math SEO Integration

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `rank_math/vars/register_extra_replacements` | Anonymous (L1970) | 10 | Custom variables | MEDIUM |
| `rank_math/json_ld` | Anonymous (L2126) | 99 | CollectionPage schema | MEDIUM |
| `rank_math/json_ld` | Anonymous (L2232) | 999 | Product schema cleanup | MEDIUM |
| `rank_math/frontend/breadcrumb/items` | Anonymous (L2463) | 10 | Custom breadcrumbs | HIGH |
| `rank_math/frontend/breadcrumb/html` | Anonymous (L2635) | 10 | Breadcrumb HTML mod | MEDIUM |
| `rank_math/frontend/breadcrumb/items` | Anonymous (L2913) | 10 | Author breadcrumbs | MEDIUM |
| `rank_math/sitemap/entry` | Anonymous (L2955) | 99 | Sitemap URL cleanup | MEDIUM |
| `rank_math/frontend/canonical` | Anonymous (L3029) | 99 | Pagination canonical | MEDIUM |
| `rank_math/frontend/canonical` | Anonymous (L3084) | 99 | Tracking params | MEDIUM |
| `rank_math/frontend/canonical` | Anonymous (L3186) | 99 | Marques canonical | MEDIUM |
| `rank_math/frontend/canonical` | Anonymous (L3269) | 99 | Redacteur canonical | MEDIUM |
| `rank_math/frontend/title` | Anonymous (L3321) | 99 | Pagination title | LOW |
| `rank_math/frontend/description` | Anonymous (L3372) | 99 | Tag description | LOW |

#### Yoast SEO / Sitemap

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `wpseo_sitemap_urlimages` | Anonymous (L1471) | 10 | Enrich sitemap images | MEDIUM |
| `wpseo_sitemap_term_image` | Anonymous (L1586) | 10 | Term sitemap images | LOW |

#### WP Rocket Integration

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `rocket_delay_js_exclusions` | Anonymous (L1606) | 10 | Exclude lightbox JS | LOW |
| `rocket_exclude_js` | Anonymous (L1622) | 10 | Exclude JS from optimize | LOW |
| `rocket_exclude_css` | Anonymous (L1636) | 10 | Exclude CSS | LOW |
| `rocket_rucss_inline_content_exclusions` | Anonymous (L1644) | 10 | RUCSS exclusions | LOW |

#### AJAX Handlers

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `wp_ajax_nopriv_lm_track_view` | `lm_track_view` | 10 | Track views (guest) | HIGH |
| `wp_ajax_lm_track_view` | `lm_track_view` | 10 | Track views (logged) | HIGH |

#### Init Hooks

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `init` | `pm_change_author_base` | 10 | Author URL base | MEDIUM |
| `init` | Anonymous (L814) | 10 | Disable comments on marque | LOW |
| `init` | Anonymous (L871) | 10 | Disable comments globally | LOW |
| `init` | Anonymous (L908) | 10 | Remove feed links | LOW |
| `init` | Anonymous (L1603) | 1 | WP Rocket exclusions | LOW |
| `init` | Anonymous (L1870) | 10 | C2S widget shortcode | LOW |
| `plugins_loaded` | Anonymous (L845) | 10 | Delay plugin init | MEDIUM |

#### Miscellaneous

| Hook | Callback | Priority | Purpose | Risk |
|------|----------|----------|---------|------|
| `save_post` | `update_last_updated_test_category` | 10 | Track last category | LOW |
| `rest_pre_insert_post` | `check_category_before_publishing` | 10 | Category validation | MEDIUM |
| `wp_lazy_loading_enabled` | `__return_true` | 10 | Enable lazy load | LOW |
| `litespeed_media_ignore_remote_missing_sizes` | `__return_true` | 10 | LiteSpeed fix | LOW |
| `litespeed_optm_img_attr` | Anonymous (L918) | 10 | Remove fetchpriority | LOW |
| `comments_open` | `__return_false` | 20 | Disable comments | LOW |
| `pings_open` | `__return_false` | 20 | Disable pings | LOW |
| `comments_array` | `__return_empty_array` | 10 | Empty comments | LOW |
| `script_loader_tag` | Anonymous (L1873) | 10 | C2S async/defer | LOW |
| `wp_head` | Anonymous (L946) | 1 | Affilizz preconnect | LOW |
| `wp_footer` | Anonymous (L960) | 10 | Affilizz render trigger | MEDIUM |
| `wp_head` | Anonymous (L1459) | 10 | AdSense verification | LOW |

### Inline Shortcodes (in functions.php)

| Shortcode | Handler | Line | Purpose | Risk |
|-----------|---------|------|---------|------|
| `c2s_widget` | Anonymous | L1888 | Clic2Shop widget | LOW |
| `show_term_c2s_widget` | Anonymous | L1937 | Term C2S widget | LOW |

---

## shortcode_list.php Analysis

### Shortcode Functions

| Shortcode | Function | Line | Purpose | Dependencies | Risk |
|-----------|----------|------|---------|--------------|------|
| `afficher_caracteristiques` | `afficher_caracteristiques_shortcode` | L3-84 | Display product specs table | ACF | LOW |
| `afficher_notes` | `afficher_notes_shortcode` | L88-135 | Display sub-ratings with stars | ACF | LOW |
| `afficher_note_globale` | `afficher_note_globale_shortcode` | L138-166 | Display global rating stars | ACF | LOW |
| `ou_acheter` | `afficher_ou_acheter_shortcode` | L169-194 | Display where to buy section | ACF | LOW |
| `afficher_galerie` | `afficher_galerie_shortcode` | L196-226 | Display product gallery | ACF | LOW |
| `afficher_pros_cons` | `afficher_pros_cons_shortcode` | L228-267 | Display pros/cons | ACF | LOW |
| `archive_filtre` | `afficher_archive_filtre_shortcode` | L270-323 | Archive filter UI | JS dependency | MEDIUM |
| `comparaison_form` | `wpb_comparaison_form_shortcode` | L326-348 | Comparison form | JS dependency | MEDIUM |
| `show_comparison` | `display_comparison_shortcode` | L350-417 | Display comparison results | ACF, other shortcodes | MEDIUM |
| `show_acf_relationship_data` | `display_acf_relationship_data` | L419-456 | Display ACF relationships | ACF | LOW |
| `show_acf_titre` | `display_acf_titre_shortcode` | L458-492 | Display ACF title | ACF | LOW |
| `show_acf_contenu` | `display_acf_contenu_shortcode` | L494-524 | Display ACF content | ACF | LOW |
| `show_acf_faq` | `display_acf_faq_shortcode` | L526-556 | Display ACF FAQ | ACF | LOW |
| `test_archive_field` | `test_archive_acf_field_shortcode` | L559-610 | Test archive ACF field | ACF | LOW |
| `display_chapeau` | `display_chapeau_shortcode` | L613-628 | Display chapeau excerpt | ACF | LOW |
| `show_acf_chapeau` | `display_acf_chapeau_shortcode` | L631-665 | Display ACF chapeau on terms | ACF | LOW |
| `display_category` | `display_category_info_shortcode` | L667-701 | Display category info | None | LOW |
| `display_last_updated_category` | `display_last_updated_category_info_shortcode` | L703-770 | Last updated category | Option API | LOW |
| `display_post_dates` | `display_post_dates_shortcode` | L773-822 | Display post dates | None | LOW |
| `linked_test_category` | `display_linked_test_category` | L823-865 | Linked test category | ACF | MEDIUM |
| `linked_test_category_post_thumbnail` | `display_linked_test_category_post_thumbnail` | L866-901 | Linked test thumbnail | ACF | MEDIUM |
| `linked_test_category_complete` | `display_linked_test_category_complete` | L903-931 | Complete linked test info | ACF | MEDIUM |
| `afficher_note_globale_card` | `afficher_note_globale_shortcode_card` | L933-964 | Card-style global rating | ACF | LOW |
| `afficher_custom_data` | `afficher_custom_data_shortcode` | L966-999 | Custom data display | ACF | LOW |
| `display_faq` | `display_faq_section` | L1002-1024 | FAQ section | ACF | LOW |
| `display_conclusion` | `display_conclusion_section` | L1027-1051 | Conclusion section | ACF | LOW |
| `display_contenu` | `display_contenu_section` | L1054-1076 | Content section | ACF | LOW |
| `display_presentation` | `display_presentation_section` | L1079-1129 | Presentation section | ACF | LOW |
| `search_title` | `get_search_title` | L1132-1169 | Search page title | None | LOW |
| `author_featured_image` | `display_author_featured_image` | L1142-1169 | Author featured image | ACF | LOW |
| `display_related_news` | `display_related_news_posts` | L1172-1256 | Related news posts | WP_Query | MEDIUM |
| `display_related_test_categories` | `display_related_test_categories` | L1258-1324 | Related test categories | WP_Query | MEDIUM |
| `promotion_link` | `display_promotion_link` | L1326-1361 | Promotion link | None | LOW |
| `display_related_news_for_test` | `display_related_news_posts_for_test` | L1363-1499 | Related news for test | WP_Query, ACF | MEDIUM |
| `display_associated_tests` | `display_associated_tests_shortcode` | L1501-1548 | Associated tests | ACF | MEDIUM |
| `dynamic_toc` | `insert_static_toc` | L1550-1577 | Table of contents | JS | LOW |
| `show_acf_promotion_data` | `show_acf_promotion_data_shortcode` | L1579-1614 | ACF promotion data | ACF | LOW |
| `category_link` | `category_link_shortcode` | L1616-1640 | Category link | None | LOW |
| `post_category_link` | `post_category_link_shortcode` | L1642-1677 | Post category link | None | LOW |
| `afficher_articles_pour_marque` | `afficher_articles_lies_a_marque_courante` | L1679-1698 | Articles for brand | ACF, AJAX | HIGH |
| `display_related_buying_guides` | `display_universal_buying_guides` | L1835-1944 | Buying guides | WP_Query | MEDIUM |
| `related_articles` | `display_related_articles` | L1977-2145 | Related articles | WP_Query | MEDIUM |
| `latest_articles` | `display_latest_articles` | L2189-2238 | Latest articles | WP_Query | MEDIUM |
| `associated_news` | `display_associated_news` | L2311-2463 | Associated news | ACF, WP_Query | MEDIUM |
| `latest_news` | `display_latest_news` | L2467-2515 | Latest news | WP_Query | MEDIUM |
| `associated_products` | `display_associated_products` | L2518-2574 | Associated products | ACF | MEDIUM |
| `buying_guides_and_comparisons` | `display_buying_guides_and_comparisons` | L2577-2708 | Guides & comparisons | WP_Query | MEDIUM |
| `acf_publications` | `acf_publications_shortcode` | L2726-2799 | ACF publications | ACF | MEDIUM |
| `scrollable_menu` | `generate_scrollable_menu_shortcode` | L2801-3131 | Scrollable menu | ACF | MEDIUM |
| `social_share` | `custom_social_share_buttons` | L3133-3169 | Social share buttons | None | LOW |
| `marques_list` | `marques_pagination_shortcode` | L3177-3290 | Paginated brands list | WP_Query, Transients | MEDIUM |
| `marque_content_list` | `marque_content_list_shortcode` | L3472-3556 | Brand content list | WP_Query | MEDIUM |
| `qd_video` | Anonymous | L3673 | Video embed | None | LOW |
| `author_content_list` | `author_content_list_shortcode` | L3704-3846 | Author content list | WP_Query | MEDIUM |
| `lm_seo_only_first` | `lm_seo_only_first_shortcode` | L3912-3927 | First page SEO content | None | LOW |
| `lm_terms_grid` | `lm_terms_grid_shortcode` | L3931-4056 | Terms grid | WP_Query | MEDIUM |
| `lm_tests_grid` | `lm_tests_grid_shortcode` | L4107-4372 | Tests grid | WP_Query, ACF | MEDIUM |
| `produits_populaires` | `lm_produits_populaires_shortcode` | L4500-4574 | Popular products | WP_Query | MEDIUM |

### Helper Functions (in shortcode_list.php)

| Function | Line | Purpose | Dependencies | Risk |
|----------|------|---------|--------------|------|
| `load_more_articles_by_marque()` | L1701 | Load more brand articles | WP_Query | MEDIUM |
| `render_post_item_for_marque()` | L1740 | Render post item for brand | None | LOW |
| `load_more_articles()` | L1766 | AJAX load more handler | WP_Query | HIGH |
| `add_custom_post_type_class()` | L1788 | Add CPT body class | None | LOW |
| `modify_headline_block_for_tests()` | L1800 | Modify headline for tests | None | LOW |
| `generate_star_rating()` | L1948 | Generate star SVG | None | LOW |
| `generate_card_html()` | L2150 | Generate card HTML | None | LOW |
| `initialize_displayed_news_ids()` | L2242 | Initialize news ID tracking | Global var | LOW |
| `generate_content_card()` | L2251 | Generate content card | None | LOW |
| `generate_product_card()` | L2283 | Generate product card | ACF | LOW |
| `generate_content_card_custom()` | L2305 | Custom content card | None | LOW |
| `render_post_item_for_test()` | L1446 | Render test post item | ACF | LOW |
| `clear_marques_cache()` | L3293 | Clear brands transient | Transients | LOW |
| `marques_display_pagination()` | L3300 | Brands pagination HTML | None | LOW |
| `lm_pagination_markup_compat()` | L3374 | Pagination markup helper | None | LOW |
| `lm_brand_listing_item_compat()` | L3560 | Brand listing item | ACF | LOW |
| `lm_redirect_legacy_page_param()` | L3870 | Legacy page param redirect | None | MEDIUM |
| `lm_register_comparatifs_pagination_rewrite()` | L3898 | Comparatifs rewrite rules | None | MEDIUM |
| `lm_get_test_card_title()` | L4061 | Get test card title | ACF | LOW |
| `lm_register_dernier_test_pagination_rewrite()` | L4378 | Test pagination rewrite | None | MEDIUM |
| `lm_register_categorie_test_pagination_rewrite()` | L4390 | Category test pagination | None | MEDIUM |
| `lm_render_related_test_card()` | L4439 | Render related test card | ACF | LOW |

---

## Categorized Inventory

### By Target Module

#### setup/theme-support.php
- `wpse325327_add_excerpts_to_pages()` - Page excerpt support
- Comments disable hooks (L871-916)

#### setup/enqueue-assets.php
- `enqueue_instagram_embed_script()` - Social embeds
- `enqueue_font_awesome()` - Icon fonts
- `my_custom_fonts_enqueue()` - Custom JS
- `load_acf_scripts()` - ACF scripts
- Refactored CSS toggle (L3415)
- ACF styles dequeue (L1142)

#### setup/image-sizes.php
- `add_image_size()` calls (L865-866)

#### admin/admin-columns.php
- Test CPT columns (L130-161)
- Taxonomy filters (L163-209)

#### admin/editor-config.php
- Category validation (L649-683)

#### integrations/wpgridbuilder.php
- All `wp_grid_builder/*` filters (~12 hooks)
- Grid query modifications

#### integrations/affilizz.php
- Affilizz button block (L89)
- Affilizz image dimensions (L791)
- Affilizz preconnect/render (L946, L960)

#### integrations/rankmath.php
- All `rank_math/*` filters (~15 hooks)
- Breadcrumb functions (L2321-2687)
- Schema modifications

#### integrations/generatepress.php
- `generate_archive_title` filter
- `generate_after_loop` filter
- `generateblocks_query_loop_args` filter

#### integrations/wprocket.php
- All `rocket_*` filters (L1603-1652)

#### hooks/content-filters.php
- `add_image_dimensions()` - Image dimensions
- `force_lazy_load_images()` - Lazy loading
- `add_dimensions_to_affilizz_images()`
- `render_block` filter (L432)

#### hooks/query-modifications.php
- `adjust_main_query_based_on_ratings()` - Archive sorting
- `add_custom_post_types_to_rss_feed()` - RSS CPTs

#### hooks/template-redirects.php
- All `template_redirect` hooks
- 410/301 guard functions
- Trailing slash enforcement
- Pagination redirects

#### utilities/helpers.php
- `generate_star_rating()` - Star SVGs
- `generate_card_html()` - Card templates
- `generate_content_card()` - Content cards
- `generate_product_card()` - Product cards
- Pagination markup helpers

#### utilities/security.php
- `lm_guard_send_410()` - 410 response
- `lm_guard_send_301()` - 301 redirect
- Guard-related URL checks

#### ajax/ajax-views.php
- `lm_track_view()` - View tracking
- Views tracker script enqueue

#### ajax/ajax-filters.php
- `load_more_articles()` - Load more handler
- `load_more_articles_by_marque()` - Brand load more

#### rss/feed-customization.php
- All RSS-related functions
- Custom RSS template

#### analytics/ga4-tracking.php
- `lm_should_load_ga4()` - GA4 check
- `lm_enqueue_ga4_head()` - GA4 + CookieYes

#### shortcodes/* (60+ shortcodes)
- To be organized per shortcode or by category

---

## Plugin Dependencies

### Required Plugins

| Plugin | Functions Using It | Risk If Missing |
|--------|-------------------|-----------------|
| **ACF Pro** | ~80% of shortcodes, all ACF field calls | CRITICAL |
| **WP Grid Builder** | 12+ grid filters, block registration | HIGH |
| **Rank Math** | 15+ SEO hooks, breadcrumbs, schema | HIGH |
| **GeneratePress** | Theme hooks, block filters | MEDIUM |
| **LiteSpeed Cache** | 2 filters | LOW |
| **WP Rocket** | 4 exclusion filters | LOW |

### Optional Plugins

| Plugin | Functions | Fallback |
|--------|-----------|----------|
| Yoast SEO | Sitemap image filters | Works without |
| Affilizz | Button block, image dimensions | Silent fail |
| WPSP PRO | Plugin init delay | Works without |

---

## Global Variables

| Variable | Used In | Type | Purpose |
|----------|---------|------|---------|
| `$post` | Multiple functions | WP_Post | Current post object |
| `$wp_rewrite` | `pm_change_author_base()` | WP_Rewrite | Rewrite rules |
| `$pagenow` | Admin filters | string | Current admin page |
| `$content_width` | Theme setup | int | Content width |

---

## Statistics Summary

| Category | Count |
|----------|-------|
| Named Functions (functions.php) | 35 |
| add_action hooks | 45+ |
| add_filter hooks | 55+ |
| Shortcodes (shortcode_list.php) | 60 |
| Helper Functions (shortcode_list.php) | 25+ |
| **Total Functions** | **~175** |
| **Total Lines** | **~8,000** |

---

## Next Steps

1. Create Dependency Graph (Task 1.2)
2. Create Risk Assessment Matrix (Task 1.3)
3. Create Module Assignment Plan (Task 1.4)
4. Begin Phase 2: Foundation Setup
