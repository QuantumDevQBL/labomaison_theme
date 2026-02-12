# Plugin Migration Guide

**Ordre de migration recommandé :** perf → shortcodes → SEO

---

## 1. labomaison-perf-core (RISQUE: Faible)

### Fichiers à copier du thème vers le plugin

| Source (thème) | Destination (plugin) | Action thème |
|----------------|---------------------|--------------|
| `inc/integrations/wprocket.php` (tout le fichier) | `inc/wprocket.php` | Supprimer du thème |
| `inc/integrations/litespeed.php` (tout) | `inc/litespeed.php` | Supprimer du thème |
| `inc/integrations/affilizz.php` (tout) | `inc/affilizz.php` | Supprimer du thème |
| `inc/hooks/content-filters.php` L44-103 | `inc/image-dimensions.php` | Retirer les 2 fonctions du thème |
| `inc/setup/theme-support.php` L63-68 (`force_lazy_load_images`) | `inc/lazy-loading.php` | Retirer du thème |
| `inc/setup/theme-support.php` L164+ (WPSP delay) | `inc/plugin-load-order.php` | Retirer du thème |

### Modifications thème après migration

Dans `functions.php`, modifier les chargements conditionnels :
```php
// WP Rocket — skip if perf plugin handles it
if ( defined( 'WP_ROCKET_VERSION' ) && ! function_exists( 'lm_perf_core_active' ) ) {
    require_once LABOMAISON_INC . '/integrations/wprocket.php';
}
// idem pour litespeed, affilizz
```

### Validation
```bash
wp plugin activate labomaison-perf-core
curl -sI https://staging.labomaison.com/ | grep -i "x-lm"
# Vérifier que les images ont width/height
# Vérifier que WP Rocket exclusions sont actives
```

---

## 2. labomaison-shortcodes (RISQUE: Moyen)

### Fichiers à copier du thème vers le plugin

| Source (thème) | Destination (plugin) |
|----------------|---------------------|
| `inc/shortcodes/product-display.php` | `inc/product-display.php` |
| `inc/shortcodes/ratings.php` | `inc/ratings.php` |
| `inc/shortcodes/taxonomy-display.php` | `inc/taxonomy-display.php` |
| `inc/shortcodes/related-content.php` | `inc/related-content.php` |
| `inc/shortcodes/archive-grids.php` | `inc/archive-grids.php` |
| `inc/shortcodes/comparison.php` | `inc/comparison.php` |
| `inc/shortcodes/navigation.php` | `inc/navigation.php` |
| `inc/shortcodes/misc.php` | `inc/misc.php` |

### Helpers à extraire de `inc/utilities/helpers.php`

Ces fonctions sont utilisées par les shortcodes et doivent être copiées dans `inc/helpers.php` du plugin :
- `generate_star_rating()`
- `generate_card_html()`
- `generate_content_card()`
- `generate_product_card()`
- `generate_content_card_custom()`
- `render_post_item_for_test()`
- `render_post_item_for_marque()`
- `lm_get_test_card_title()`
- `lm_render_related_test_card()`
- `lm_pagination_markup_compat()`

**IMPORTANT:** Wrapper chaque fonction avec `if ( ! function_exists( '...' ) )` pour la compat.

### Modification thème après migration

Remplacer `inc/shortcodes/index.php` par :
```php
<?php
// Skip shortcode loading if plugin handles them
if ( function_exists( 'lm_shortcodes_plugin_active' ) ) {
    return;
}
// ... ancien code de chargement ...
```

### Validation
```bash
wp plugin activate labomaison-shortcodes
wp eval 'global $shortcode_tags; $lm = array_filter(array_keys($shortcode_tags), function($k) { return strpos($k, "afficher_") === 0 || strpos($k, "display_") === 0 || strpos($k, "linked_") === 0 || strpos($k, "lm_") === 0; }); echo implode("\n", $lm);'
# Doit lister 60+ shortcodes
```

---

## 3. labomaison-seo-core (RISQUE: Élevé — SEO impact)

### Fichiers à copier du thème vers le plugin

| Source (thème rankmath.php) | Lignes | Destination (plugin) |
|-----------------------------|--------|---------------------|
| SITEMAP IMAGE ENRICHMENT | L38-167 | `inc/sitemap.php` |
| RANK MATH VARIABLE REPLACEMENTS | L169-329 | `inc/variables.php` |
| JSON-LD SCHEMA MODIFICATIONS | L330-507 | `inc/schema.php` |
| BREADCRUMB (helpers + filters + HTML + author) | L508-941 | `inc/breadcrumbs.php` |
| SITEMAP ENTRY CLEANUP + CANONICAL HANDLERS | L942-1211 | `inc/canonical.php` |
| TITLE & DESCRIPTION PAGINATION | L1212-end | `inc/title-description.php` |

### Depuis content-filters.php

| Source | Destination |
|--------|------------|
| `generate_archive_title` filter (L140-158) | `inc/archive-content.php` |
| `generate_after_loop` filter (L167-193) | `inc/archive-content.php` |

### Dependencies qui restent dans le thème

Le plugin a besoin de ces fonctions du thème (garder dans `security.php`) :
- `lm_can_is_redacteur_scope()` — utilisé par canonical
- `lm_can_current_url_clean()` — utilisé par canonical

### Modification thème après migration

Dans `functions.php` :
```php
// Rank Math SEO — skip if SEO plugin handles it
if ( class_exists( 'RankMath' ) && ! function_exists( 'lm_seo_core_active' ) ) {
    require_once LABOMAISON_INC . '/integrations/rankmath.php';
}
```

### Validation (CRITIQUE — tester en staging pendant 48h)
```bash
wp plugin activate labomaison-seo-core

# 1. Vérifier les breadcrumbs
curl -s https://staging.labomaison.com/categorie-test/aspirateurs/ | grep "breadcrumb"

# 2. Vérifier le schema JSON-LD
curl -s https://staging.labomaison.com/categorie-test/aspirateurs/ | grep "CollectionPage"

# 3. Vérifier les canonical URLs
curl -sI https://staging.labomaison.com/categorie-test/aspirateurs/page/2/ | grep -i "canonical"

# 4. Vérifier le sitemap
curl -s https://staging.labomaison.com/sitemap_index.xml | head -20

# 5. Vérifier Rank Math variables
wp eval 'echo RankMath\replace_vars("%lm_cat_name%", get_post(1));'

# 6. Monitorer PHP error log
tail -f wp-content/debug.log | grep -i "lm_seo\|rankmath\|breadcrumb"
```

---

## Rollback par plugin

Chaque plugin peut être désactivé indépendamment :
```bash
wp plugin deactivate labomaison-perf-core    # Le thème reprend automatiquement
wp plugin deactivate labomaison-shortcodes   # Le thème reprend automatiquement
wp plugin deactivate labomaison-seo-core     # Le thème reprend automatiquement
```

Ceci fonctionne grâce aux guards `function_exists()` / `lm_seo_core_active()` dans le thème.
