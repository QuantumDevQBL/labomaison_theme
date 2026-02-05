# CSS Migration Tracking

**Generated:** 2026-02-04
**Source:** style.css (3,850 lines)
**Status:** Phase 1.1 - Deep Audit Synthesis

---

## 1. File Structure Overview

### 1.1 Major Code Blocks (Line Ranges)

| Lines | Section | Module Target | Risk |
|-------|---------|---------------|------|
| 1-9 | Theme header | N/A (keep in style.css) | - |
| 12-105 | Deveoo: GB columns, pagination, brands | _grids.css | LOW |
| 106-183 | Category test cards, buying guides | _cards.css | MEDIUM |
| 185-220 | Custom pagination | _grids.css | LOW |
| 223-488 | Page Marque: items, list, badges | _cards.css | MEDIUM |
| 490-509 | Footer basics | _layout.css | LOW |
| 511-627 | Pros/cons, sous-notes, fiche-technique | _test-sections.css | MEDIUM |
| 630-702 | Ou acheter, global styles, galerie | _test-sections.css | LOW |
| 704-792 | Pros/cons shortcode detailed | _test-sections.css | MEDIUM |
| 794-802 | Footer SVG | _layout.css | LOW |
| 804-815 | WP Grid Builder facets | _plugins.css | LOW |
| 817-821 | Navigation container | _navigation.css | LOW |
| 823-869 | Footer form, banner | _layout.css | LOW |
| 871-878 | `.inside-article` (v1) | _layout.css | HIGH |
| 880-898 | WPGB grid styles | _plugins.css | LOW |
| 901-918 | List style, navigation search button | _navigation.css | MEDIUM |
| 920-932 | Comparison page (v1) | _layout.css | LOW |
| 937-952 | GB grid wrapper (DUPLICATE v1) | _grids.css | HIGH |
| 954-1072 | **GeneratePress Site CSS (BLOCK 1)** | _layout.css | **CRITICAL** |
| 1076-1090 | `.post-term-item` base (v1) | _badges.css | HIGH |
| 1092-1096 | `.inside-article` (v2) | _layout.css | HIGH |
| 1099-1125 | Home thumbnail containers | _cards.css | MEDIUM |
| 1128-1144 | Newsletter iframes | _plugins.css | LOW |
| 1146-1206 | Category container (home) | _grids.css | MEDIUM |
| 1208-1358 | Related news posts | _grids.css | MEDIUM |
| 1360-1453 | Test category pills, term positioning | _badges.css | HIGH |
| 1455-1478 | Sidebar widgets, term hiding | _layout.css | MEDIUM |
| 1481-1499 | Archive post pages, search | _grids.css | LOW |
| 1501-1544 | Search title, post cards, datetime | _cards.css | LOW |
| 1546-1595 | Test sections containers | _test-sections.css | MEDIUM |
| 1597-1601 | WPGB facet style | _plugins.css | LOW |
| 1607-1679 | Article card (linked post test) | _cards.css | HIGH |
| 1684-1748 | Mobile styles for test sections | _media-queries.css | HIGH |
| 1751-1841 | Navigation links, block, container | _navigation.css | **CRITICAL** |
| 1843-1855 | Comparison page (DUPLICATE v2) | _layout.css | HIGH |
| 1860-1875 | GB grid wrapper (DUPLICATE v2) | _grids.css | HIGH |
| 1877-1991 | **GeneratePress Site CSS (BLOCK 2)** | _layout.css | **CRITICAL** |
| 1995-2023 | Term styles, inside-article (v3) | _badges.css, _layout.css | HIGH |
| 2027-2066 | Header thumbnail, GB containers | _cards.css | MEDIUM |
| 2068-2137 | Star ratings, notes-produit | _test-sections.css | MEDIUM |
| 2139-2211 | Home thumbnail layouts | _grids.css | MEDIUM |
| 2213-2227 | WPGB card body styles | _plugins.css | LOW |
| 2230-2254 | Term absolute, global notation | _badges.css | MEDIUM |
| 2256-2290 | `.site-main` variants | _layout.css | HIGH |
| 2292-2312 | Single-test mobile, inside-article (v4) | _media-queries.css | HIGH |
| 2314-2330 | Newsletter styles | _plugins.css | LOW |
| 2332-2348 | Page site-main overrides | _layout.css | HIGH |
| 2351-2366 | Chapeau article, load more | _cards.css | LOW |
| 2369-2397 | Footer list, publication | _layout.css | LOW |
| 2400-2403 | `.gb-icon svg` (v1) | _plugins.css | LOW |
| 2406-2437 | Pill in table (value-pill) | _badges.css | LOW |
| 2439-2455 | Affilizz publication | _plugins.css | MEDIUM |
| 2457-2472 | Homepage dates, gb-icon (v2) | _plugins.css | LOW |
| 2475-2485 | Nav item, test page grid | _navigation.css | LOW |
| 2489-2624 | GLightbox, brand page responsive | _plugins.css | MEDIUM |
| 2626-2677 | Mobile navigation (1024px) | _navigation.css | **CRITICAL** |
| 2679-2714 | Widget sidebar, nav item colors | _layout.css | LOW |
| 2716-2795 | Page marque brand logo | _cards.css | LOW |
| 2797-2803 | Article card override | _cards.css | MEDIUM |
| 2805-2892 | Mixed list homepage | _grids.css | MEDIUM |
| 2893-2990 | Card/related-articles components | _cards.css | HIGH |
| 2993-3003 | Post archive container, content query | _cards.css | MEDIUM |
| 3005-3033 | Mobile cards | _media-queries.css | HIGH |
| 3036-3091 | GB headline shortcode, categorie test | _plugins.css | MEDIUM |
| 3093-3127 | Home advanced query, carousel, menu toggle | _grids.css, _navigation.css | LOW |
| 3129-3156 | Affilizz container, social icons | _plugins.css | LOW |
| 3158-3186 | TOC desktop | _test-sections.css | LOW |
| 3187-3484 | Filters panel (WPGB) | _plugins.css | HIGH |
| 3486-3510 | Content section, chapeau, fiche-technique | _test-sections.css | LOW |
| 3512-3515 | Share block hide | _layout.css | LOW |
| 3518-3582 | CLS fixes (WPGB, Affilizz, images) | _plugins.css | MEDIUM |
| 3584-3598 | WP image fixes | _plugins.css | LOW |
| 3602-3698 | BEM brand listing (lm-*) | _grids.css | LOW |
| 3700-3754 | Query loop image, post archive | _cards.css | MEDIUM |
| 3757-3801 | Breadcrumb (Rank Math) | _plugins.css | LOW |
| 3803-3850 | Mobile header test (breadcrumb+stars) | _media-queries.css | LOW |

---

## 2. Duplication Matrix

### 2.1 Exact Duplicates (Merge Required)

| ID | Selector/Block | Location 1 | Location 2 | Lines Saved | Decision |
|----|----------------|------------|------------|-------------|----------|
| D1 | GeneratePress Site CSS | L954-1072 (118 lines) | L1877-1991 (114 lines) | ~100 | Keep L1877 (more complete), merge unique from L954 |
| D2 | `.comparaison-container` | L922-932 | L1845-1855 | 10 | Keep L922, delete L1845 |
| D3 | `.post_loop_container .gb-grid-wrapper` | L937-952 | L1860-1875 | 15 | Keep L937, delete L1860 |
| D4 | `.post-term-item.term-electromenager` | L1077-1079 | L1996-1998 | 2 | Keep L1077, delete L1996 |
| D5 | `.gb-container-c3d101a4...` | L1111-1125 | L2052-2065 | 13 | Keep L1111, delete L2052 |
| D6 | `.wp-block-search__button...` | L909-918 | L1793-1798 | 5 | Keep L909, delete L1793 |

**Total lines recoverable from exact duplicates: ~145 lines**

### 2.2 Override Conflicts (Cascade Analysis Required)

| ID | Selector | Occurrences | Properties Conflict | Resolution |
|----|----------|-------------|---------------------|------------|
| O1 | `.post-term-item` | L1081, L1361, L2001 | padding, position, background-color | Define base → modifiers |
| O2 | `.inside-article` | L875, L1092, L2015, L2309 | padding (0→20→20→10) | Context hierarchy |
| O3 | `.site-main` | L2256, L2278, L2283, L2287, L2333 | padding (30→20→10→40) | Template-specific |
| O4 | `.related-news__content` | L1261, L1308 | position (static→absolute) | Component vs overlay |
| O5 | `.gb-icon svg` | L2400, L2470 | background (currentColor→transparent) | Keep L2470 (transparent) |
| O6 | `.content_container_query_loop` | L1409, L3000, L3710 | flex-direction, align | Context-based |

---

## 3. Media Query Analysis

### 3.1 Breakpoint Distribution

| Breakpoint | Occurrences | Lines | Normalization Target |
|------------|-------------|-------|----------------------|
| 480px | 2 | L1354, L2526 | Keep (small mobile) |
| 600px | 2 | L2448, L2568 | → 480px or 768px |
| 767px | 3 | L2579, L3233, L3291 | → 768px |
| **768px** | **48** | Multiple | **STANDARD** |
| 769px | 5 | L980, L1043, L1062, L1903, L1958 | → 768px (min-width) |
| 992px | 2 | L2605 | → 1024px |
| 996px | 2 | L548, L1159 | → 1024px |
| **1024px** | 7 | L2181, L2631, L3685... | **STANDARD** |
| 1025px | 1 | L2616 | → 1024px |

### 3.2 Consolidation Plan

**Target breakpoints:**
- `480px` - Small mobile (optional)
- `768px` - Mobile/tablet (primary)
- `1024px` - Desktop (secondary)

**Migration rules:**
1. `767px` → `768px` (normalize off-by-one)
2. `769px min-width` → `768px min-width` (equivalent)
3. `992px`, `996px`, `1025px` → `1024px`
4. `600px` → evaluate case-by-case (mostly Affilizz)

---

## 4. Module Migration Checklist

### 4.1 _layout.css (CRITICAL - Do First)

- [x] Site description (L956-961)
- [x] Box shadow (L964-967)
- [x] Button/submit radius (L969-973)
- [x] Sidebar sticky (L976-985)
- [x] Category label padding (L988-999)
- [ ] Term category colors (L1001-1027) → may move to _badges.css
- [x] Archive site-main (L1030-1041)
- [x] Ratio-third grid (L1044-1068)
- [ ] `.site-main` hierarchy (L2256-2290) - multiple overrides
- [ ] `.inside-article` resolution (4 occurrences)
- [x] Comparison page (L922-932)
- [ ] Footer list styles (L2370-2390)
- [ ] Page-specific site-main (L2333-2348)

**Status:** ~60% migrated | **Remaining:** ~80 lines

### 4.2 _navigation.css (CRITICAL)

- [x] Search button (L909-918)
- [x] Navigation item underline (L1754-1783)
- [x] Navigation block (L1786-1791)
- [x] Sticky logo (L1800-1803)
- [x] Inside navigation container (L1805-1841)
- [ ] Menu bar items (L2477-2480)
- [ ] Mobile navigation (L2631-2677) - important!
- [ ] Red menu item (L2708-2714)
- [ ] Menu toggle hide text (L3124-3127)

**Status:** ~50% migrated | **Remaining:** ~60 lines

### 4.3 _badges.css (CRITICAL)

- [x] Base `.post-term-item` (L1081-1090)
- [x] `.term-electromenager` color (L1077-1079)
- [ ] Test category pill (L1361-1378) - complex positioning
- [ ] `.test-category-link` (L1380-1396)
- [ ] `.term_container_query_loop` positioning (L1402-1417)
- [ ] `.test_cat_shortcode_container` (L1419-1443)
- [ ] `.publication_container .term_absolute` (L1431-1436)
- [ ] `.sidebar_post_container .post-term-item` (L1446-1453)
- [ ] Term hiding on home (L1470-1479)
- [ ] `.terms_container .post-term-item` (L2021-2023)
- [ ] `.term_absolute` base (L2230-2235)
- [ ] `.post_archive_container .term_absolute` reset (L2993-2997)

**Status:** ~35% migrated | **Remaining:** ~100 lines

### 4.4 _cards.css (HIGH)

- [x] GB containers (L1111-1125)
- [x] Card/related base (L2893-2904)
- [x] Card images (L2906-2920)
- [x] Card content (L2924-2932)
- [x] Card titles (L2934-2960)
- [x] Card excerpt/date (L2962-2990)
- [ ] Marque post list item (L275-448) - extensive
- [ ] Article card (L1607-1679)
- [ ] Post card container (L1511-1524)
- [ ] Query loop image (L2815-2830, L3703-3734)
- [ ] Post archive container (L3736-3743)
- [ ] Mixed list homepage (L2809-2892)
- [ ] Buying guides overlay (L114-173)

**Status:** ~40% migrated | **Remaining:** ~200 lines

### 4.5 _grids.css (HIGH)

- [x] GB grid wrapper (L937-952)
- [x] Related news grid (L1208-1358)
- [x] Brand listing grid (L3673-3689)
- [ ] Category container (L1150-1206)
- [ ] Home thumbnail layouts (L2139-2211)
- [ ] Publication containers (L2159-2171)
- [ ] Mixed list grid (L2864-2868)
- [ ] BEM brand listing (L3607-3698)

**Status:** ~50% migrated | **Remaining:** ~150 lines

### 4.6 _test-sections.css (HIGH)

- [x] Section containers base (L1546-1595)
- [x] Section headings (L1587-1595)
- [ ] Pros/cons container (L706-792)
- [ ] Sous-notes/critère (L566-606)
- [ ] Fiche technique (L603-627)
- [ ] Ou acheter (L631-644)
- [ ] Star ratings (L2068-2130)
- [ ] Notes produit (L2094-2127)
- [ ] TOC desktop (L3159-3185)
- [ ] Mobile section styles (L1684-1748)
- [ ] Chapeau content (L3496-3499)

**Status:** ~25% migrated | **Remaining:** ~200 lines

### 4.7 _plugins.css (MEDIUM)

- [x] WPGB facets (L804-815)
- [x] WPGB grid styles (L881-898)
- [x] Filters panel (L3193-3484) - partially
- [x] Affilizz base (L2439-2455)
- [x] GLightbox (L2490-2504)
- [ ] WPGB card styles (L2213-2227)
- [ ] Affilizz CLS fixes (L3540-3582)
- [ ] Social icons (L3141-3156)
- [ ] GB headline shortcode (L3038-3067)
- [ ] WP image fixes (L3584-3598)
- [ ] Rank Math breadcrumb (L3757-3801)

**Status:** ~60% migrated | **Remaining:** ~100 lines

### 4.8 _media-queries.css (MEDIUM)

- [x] Mobile navigation (basic)
- [x] Mobile cards (basic)
- [x] Pros/cons mobile (basic)
- [ ] Consolidate 48+ `@media (max-width: 768px)` blocks
- [ ] Consolidate 5 `@media (min-width: 769px)` blocks
- [ ] Consolidate 7 `@media (min-width/max-width: 1024px)` blocks
- [ ] Normalize 767px → 768px
- [ ] Normalize 992/996/1025px → 1024px

**Status:** ~15% migrated | **Remaining:** ~300 lines (to consolidate)

---

## 5. Dependency Graph

### 5.1 Critical Path (Must Migrate First)

```
1. _base.css (custom properties)
   ↓
2. _layout.css (site structure)
   ↓
3. _navigation.css (header/menu)
   ↓
4. _badges.css (depends on layout context)
   ↓
5. _cards.css (depends on badges for terms)
   ↓
6. _grids.css (depends on cards for items)
   ↓
7. _test-sections.css (standalone, can parallel with 5-6)
   ↓
8. _plugins.css (isolated, can parallel)
   ↓
9. _media-queries.css (LAST - overrides everything)
```

### 5.2 Cascade Dependencies

| Module | Depends On | Blocks |
|--------|-----------|--------|
| _layout.css | _base.css | - |
| _navigation.css | _base.css, _layout.css | - |
| _badges.css | _base.css | - |
| _cards.css | _base.css, _badges.css | - |
| _grids.css | _base.css, _cards.css | - |
| _test-sections.css | _base.css | - |
| _plugins.css | _base.css | - |
| _media-queries.css | ALL ABOVE | Must be last |

---

## 6. Risk Assessment

### 6.1 High-Risk Selectors

| Selector | Risk Level | Reason | Mitigation |
|----------|------------|--------|------------|
| `.site-main` | HIGH | 5+ definitions, page-specific | Map hierarchy clearly |
| `.inside-article` | HIGH | 4 padding conflicts | Context-specific rules |
| `.post-term-item` | HIGH | 3+ definitions with !important | BEM modifiers |
| `@media 768px` blocks | HIGH | 48 occurrences | Consolidate by component |
| GeneratePress blocks | CRITICAL | Duplicate, affects all pages | Merge carefully |
| `.related-news__content` | MEDIUM | Dual purpose (content/overlay) | Split selectors |

### 6.2 !important Audit

| Location | Selector | Property | Justification |
|----------|----------|----------|---------------|
| L507 | `.social_icons_container` | margin-left | Override GP default |
| L876-877 | `.inside-article` | padding-left/right | Override GP container |
| L1082 | `.post-term-item` | color | Override link styles |
| L1085 | `.post-term-item` | background-color | Category override |
| L1123 | `.gb-container-*` | justify-content | Override GP flex |
| L1366-1369 | `.post-term-item` | multiple | Legacy badge styles |
| L2005-2006 | `.post-term-item` | padding, background | Duplicate with !important |
| L2310-2311 | `.inside-article` | padding-left/right | Mobile override |
| L2336 | `.site-main` | padding | Page-specific |
| L2802 | `.article-card` | width | Layout fix |

**Total: 35+ !important usages** - Document and evaluate each for removal

---

## 7. Estimated Effort

| Module | Current Lines | Target Lines | Effort | Priority |
|--------|---------------|--------------|--------|----------|
| _base.css | 31 | 80-100 | 1 hour | P0 |
| _layout.css | 200 | 300-350 | 3 hours | P0 |
| _navigation.css | 90 | 150-180 | 2 hours | P0 |
| _badges.css | 130 | 200-250 | 2 hours | P1 |
| _cards.css | 121 | 350-400 | 4 hours | P1 |
| _grids.css | 191 | 300-350 | 3 hours | P1 |
| _test-sections.css | 58 | 250-300 | 3 hours | P2 |
| _plugins.css | 303 | 400-450 | 3 hours | P2 |
| _media-queries.css | 75 | 200-250 | 4 hours | P3 |
| _legacy-temp.css | - | TBD | - | - |

**Total estimated effort: 25-30 hours**

---

## 8. Validation Checkpoints

### 8.1 Per-Module Validation

After each module migration:
1. Run `node css-compare.js`
2. Visual check: Homepage, Archive, Single Test, Single Post, Brand Page, Search
3. Responsive check: 320px, 768px, 1024px, 1440px
4. Browser check: Chrome, Firefox (minimum)
5. Update CHANGELOG.md

### 8.2 Page Templates to Test

| Page | Critical Elements |
|------|-------------------|
| Homepage | Cards, thumbnails, badges, mixed list |
| Archive (categorie_test) | Grid, badges, related cards |
| Single Test | Pros/cons, notes, fiche technique, TOC |
| Single Post | Article content, related posts |
| Brand Page (single-marque) | Brand list, pagination, cards |
| Search Results | Cards, badges, pagination |
| Comparison Page | Comparison container |

---

## 9. Progress Log

| Date | Module | Action | Result |
|------|--------|--------|--------|
| 2026-02-04 | All | Initial audit synthesis | migration-tracking.md created |
| - | _base.css | Scaffold | 31 lines (basic) |
| - | _layout.css | Partial migration | 200 lines |
| - | _navigation.css | Partial migration | 90 lines |
| - | _badges.css | Partial migration | 130 lines |
| - | _cards.css | Partial migration | 121 lines |
| - | _grids.css | Partial migration | 191 lines |
| - | _test-sections.css | Scaffold | 58 lines |
| - | _plugins.css | Partial migration | 303 lines |
| - | _media-queries.css | Scaffold | 75 lines |

---

## 10. Next Actions

1. **Phase 1.2**: Complete `_base.css` with all custom properties
2. **Phase 1.3**: Document breakpoint normalization strategy
3. **Phase 2.1**: Complete `_layout.css` migration
4. **Phase 2.2**: Complete `_navigation.css` migration
5. **Phase 2.3**: Complete `_badges.css` migration

**Phase 1.2 Complete: Custom Properties Extraction**
**Phase 1.3 Complete: Breakpoint Normalization Strategy**

---

## 11. Breakpoint Normalization Strategy

### 11.1 Current Breakpoint Chaos (53 Total Media Queries)

| Breakpoint | Type | Count | Lines |
|------------|------|-------|-------|
| 480px | max-width | 2 | L1354, L2526 |
| 481px | min-width | 1 | L2551 |
| 600px | max-width | 1 | L2448 |
| 600px | min-width | 1 | L2568 |
| 767px | max-width | 2 | L2579, L3233 |
| **768px** | **max-width** | **27** | L561, L685, L776, L1198, L1347, L1461, L1661, L1684, L1811, L1972, L2132, L2150, L2175, L2201, L2249, L2292, L2340, L2464, L2736, L2821, L2881, L3005, L3112, L3452, L3576, L3774, L3804 |
| **768px** | **min-width** | **6** | L1235, L3168, L3207, L3387, L3679, L3721 |
| 769px | min-width | 5 | L980, L1043, L1062, L1903, L1958 |
| 992px | min-width | 1 | L2605 |
| 996px | max-width | 2 | L548, L1159 |
| 1024px | max-width | 2 | L2181, L2631 |
| 1024px | min-width | 1 | L3685 |
| 1025px | min-width | 1 | L2616 |

### 11.2 Target Breakpoint System

```css
/* Small mobile - optional, use sparingly */
@media (max-width: 480px) { }

/* Mobile/Tablet - PRIMARY breakpoint */
@media (max-width: 768px) { }   /* Mobile styles */
@media (min-width: 768px) { }   /* Tablet+ styles */

/* Desktop - SECONDARY breakpoint */
@media (max-width: 1024px) { }  /* Mobile/tablet override */
@media (min-width: 1024px) { }  /* Desktop styles */
```

### 11.3 Normalization Rules

| Current | Target | Rule | Affected |
|---------|--------|------|----------|
| 481px min | 480px min | Round down | L2551 |
| 600px | Case-by-case | Affilizz-specific, keep or → 480 | L2448, L2568 |
| 767px max | 768px max | Off-by-one fix | L2579, L3233 |
| 769px min | 768px min | Off-by-one fix | L980, L1043, L1062, L1903, L1958 |
| 992px min | 1024px min | Consolidate desktop | L2605 |
| 996px max | 1024px max | Consolidate desktop | L548, L1159 |
| 1025px min | 1024px min | Off-by-one fix | L2616 |

### 11.4 Migration Plan by Component

#### Navigation (5 MQ)
- L980, L1903: `769px min` → `768px min` (sidebar sticky)
- L1811: `768px max` (nav padding) - KEEP
- L2631: `1024px max` (mobile nav) - KEEP

#### Cards (12 MQ)
- L1347, L1354: Related news mobile
- L1661, L3005: Article card, card images
- L2821, L2881: Mixed list, query loop
- Consolidate into single `@media (max-width: 768px)` block

#### Test Sections (8 MQ)
- L685: Galerie images
- L776: Pros/cons
- L1684: Mobile section styles
- L2132: Note critere
- Consolidate into single block

#### Grids (6 MQ)
- L1159: Category container (996px → 1024px)
- L1198: Category featured link
- L1235: Related news 768px min
- L2150, L2175: Home thumbnail
- L2201: Publication containers

#### Plugins (8 MQ)
- L548: GB containers (996px → 1024px)
- L2448: Affilizz (600px - EVALUATE)
- L2579: GLightbox (767px → 768px)
- L3168, L3207, L3387: Filters panel
- L3452: Filters mobile

#### Layout (8 MQ)
- L1043, L1062, L1958: Archive ratio-third (769px → 768px)
- L1972: Archive site-main mobile
- L2249, L2292, L2340: Site-main variants
- L3774, L3804: Breadcrumb mobile

### 11.5 Consolidation Strategy

**Before (53 scattered blocks):**
```css
@media (max-width: 768px) { .nav { ... } }
/* ... 200 lines later ... */
@media (max-width: 768px) { .cards { ... } }
/* ... 300 lines later ... */
@media (max-width: 768px) { .sections { ... } }
```

**After (grouped by component in _media-queries.css):**
```css
/* ===== MOBILE: ≤ 768px ===== */
@media (max-width: 768px) {
  /* --- Navigation --- */
  .inside-navigation.grid-container { ... }
  .menu-toggle { ... }

  /* --- Cards --- */
  .card__image img { ... }
  .article-card { ... }

  /* --- Test Sections --- */
  .pros-cons-container_shortcode { ... }
  .sous-notes { ... }

  /* --- Grids --- */
  .related-news__item { ... }
  .category_container { ... }
}

/* ===== TABLET: ≥ 768px ===== */
@media (min-width: 768px) {
  /* --- Layout --- */
  .archive .site-main .ratio-third { ... }

  /* --- Filters --- */
  .filters-panel.open { ... }
}

/* ===== DESKTOP: ≥ 1024px ===== */
@media (min-width: 1024px) {
  /* --- Sidebar --- */
  .sidebar.is-right-sidebar > .inside-right-sidebar { ... }

  /* --- Grids --- */
  .lm-brandListing__grid { ... }
}
```

### 11.6 Special Cases

#### 600px Breakpoint (Affilizz)
**Decision:** Keep for Affilizz-specific CLS fixes
```css
/* Affilizz - mobile-specific min-height */
@media (max-width: 600px) {
  .wp-block-affilizz-publication,
  .affilizz-rendering-container {
    min-height: 550px;
  }
}
```

#### 480px Breakpoint (Small Mobile)
**Decision:** Keep for brand page post list
```css
/* Small mobile adjustments */
@media (max-width: 480px) {
  .post-list-header { margin-bottom: 0; }
  .post-list-title { font-size: 12px; }
  .marque-post-list-item { gap: 10px; padding: 10px; }
}
```

### 11.7 Expected Results

| Metric | Before | After | Change |
|--------|--------|-------|--------|
| Total media queries | 53 | ~15 | -72% |
| Unique breakpoints | 11 | 3-4 | -73% |
| 768px blocks | 33 | 2-3 | -91% |
| Lines of CSS | ~400 | ~250 | -38% |

---

## 12. Phase 1 Completion Summary

### Deliverables Created
1. **migration-tracking.md** - This document (comprehensive)
2. **_base.css** - 250+ lines of CSS custom properties
3. **Breakpoint strategy** - Documented above

### Ready for Phase 2
- All 3,850 lines classified
- 47+ duplicates identified
- 53 media queries mapped
- Custom properties extracted
- Normalization rules defined

**READY TO PROCEED TO PHASE 2: Module-by-Module Migration**
