/**
 * css-compare.js
 *
 * Objectif :
 * Comparer le contenu CSS de `style.css` (source historique)
 * avec la somme des fichiers modulaires dans `css/` afin de
 * vérifier qu'aucune règle n’a été perdue.
 *
 * Utilisation :
 *   1. Dans le dossier du thème, installer PostCSS :
 *        npm init -y
 *        npm install postcss
 *
 *   2. Lancer le script :
 *        node css-compare.js
 *
 *   3. Le script affichera :
 *        - les règles présentes dans style.css mais absentes des modules
 *        - les règles présentes dans les modules mais pas dans style.css
 *
 * Note :
 * - La comparaison est basée sur couples (selectors, declarations normalisées).
 * - Les commentaires et espaces sont ignorés.
 */

const fs = require('fs');
const path = require('path');
const postcss = require('postcss');

const THEME_ROOT = __dirname;

const ORIGINAL_FILE = path.join(THEME_ROOT, 'style.css');
const MODULE_FILES = [
  path.join(THEME_ROOT, 'css', '_base.css'),
  path.join(THEME_ROOT, 'css', '_layout.css'),
  path.join(THEME_ROOT, 'css', '_navigation.css'),
  path.join(THEME_ROOT, 'css', '_cards.css'),
  path.join(THEME_ROOT, 'css', '_badges.css'),
  path.join(THEME_ROOT, 'css', '_grids.css'),
  path.join(THEME_ROOT, 'css', '_test-sections.css'),
  path.join(THEME_ROOT, 'css', '_plugins.css'),
  path.join(THEME_ROOT, 'css', '_media-queries.css'),
  path.join(THEME_ROOT, 'css', '_legacy-temp.css'),
];

function readFileSafe(filePath) {
  try {
    return fs.readFileSync(filePath, 'utf8');
  } catch (e) {
    console.error('Impossible de lire le fichier :', filePath);
    throw e;
  }
}

/**
 * Normalise une règle PostCSS en clé de comparaison simple.
 * - concatène les sélecteurs
 * - trie et normalise les déclarations (prop: value)
 */
function ruleToKey(rule) {
  const selectors = (rule.selectors || [])
    .map((s) => s.trim())
    .filter(Boolean)
    .join(', ');

  const decls = [];
  rule.walkDecls((decl) => {
    const prop = decl.prop && decl.prop.trim();
    const value = decl.value && decl.value.trim();
    if (!prop || !value) return;
    decls.push(`${prop}: ${value}`);
  });

  decls.sort();

  return `${selectors} { ${decls.join('; ')} }`;
}

function collectRulesFromCss(cssText, contextLabel) {
  const root = postcss.parse(cssText, { from: undefined });
  const rules = new Set();

  root.walkRules((rule) => {
    // Ignorer les règles vides
    let hasDecl = false;
    rule.walkDecls(() => {
      hasDecl = true;
    });
    if (!hasDecl) return;

    const key = ruleToKey(rule);
    rules.add(key);
  });

  console.log(
    `[${contextLabel}] Règles collectées :`,
    rules.size
  );

  return rules;
}

function main() {
  console.log('=== COMPARAISON CSS style.css vs modules css/ ===');

  const originalCss = readFileSafe(ORIGINAL_FILE);
  const originalRules = collectRulesFromCss(originalCss, 'style.css');

  const modulesCss = MODULE_FILES
    .map((file) => {
      if (!fs.existsSync(file)) {
        console.warn('Fichier module manquant (ignoré) :', file);
        return '';
      }
      return readFileSafe(file);
    })
    .join('\n\n');

  const moduleRules = collectRulesFromCss(modulesCss, 'modules css/*');

  // Diff : règles présentes dans style.css mais pas dans les modules
  const missingInModules = [];
  for (const key of originalRules) {
    if (!moduleRules.has(key)) {
      missingInModules.push(key);
    }
  }

  // Diff inverse : règles présentes dans les modules mais pas dans style.css
  const missingInOriginal = [];
  for (const key of moduleRules) {
    if (!originalRules.has(key)) {
      missingInOriginal.push(key);
    }
  }

  console.log('\n=== Résultats ===');

  if (missingInModules.length === 0) {
    console.log(
      '- OK : toutes les règles de style.css ont un équivalent dans les modules.'
    );
  } else {
    console.log(
      `- ATTENTION : ${missingInModules.length} règle(s) présente(s) dans style.css ` +
        'n’ont pas été retrouvées dans les modules :'
    );
    missingInModules.slice(0, 50).forEach((rule, idx) => {
      console.log(`  [style-only ${idx + 1}] ${rule}`);
    });
    if (missingInModules.length > 50) {
      console.log(
        `  ... (${missingInModules.length - 50} règle(s) supplémentaire(s) non affichées)`
      );
    }
  }

  if (missingInOriginal.length === 0) {
    console.log(
      '- OK : les modules ne contiennent pas de règles supplémentaires inconnues de style.css.'
    );
  } else {
    console.log(
      `- INFO : ${missingInOriginal.length} règle(s) présente(s) uniquement dans les modules :`
    );
    missingInOriginal.slice(0, 50).forEach((rule, idx) => {
      console.log(`  [modules-only ${idx + 1}] ${rule}`);
    });
    if (missingInOriginal.length > 50) {
      console.log(
        `  ... (${missingInOriginal.length - 50} règle(s) supplémentaire(s) non affichées)`
      );
    }
  }

  console.log('\n=== Fin de la comparaison ===');
}

main();

