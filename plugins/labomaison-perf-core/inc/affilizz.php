<?php
/**
 * Affilizz Integration
 *
 * Hooks for Affilizz affiliate plugin.
 *
 * @package Labomaison
 * @subpackage Integrations
 * @since 2.0.0
 *
 * Functions in this file:
 * - Affilizz preconnect header
 * - Affilizz footer render trigger
 *
 * Dependencies: Affilizz plugin
 * Load Priority: 5
 * Risk Level: MEDIUM
 *
 * Migrated from: functions.php L946-986
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// AFFILIZZ SCRIPT LOADING
// =============================================================================

/**
 * Charge le script Affilizz avant CookieYes pour éviter tout blocage
 * et optimise le chargement pour un affichage rapide du bloc prix.
 * S'applique uniquement sur les articles (post) et les tests (CPT "test").
 *
 * @since 2.0.0
 */
add_action('wp_head', function() {
    if (!is_admin() && (is_singular('post') || is_singular('test'))) {
        // Préconnect pour accélérer la connexion TLS
        echo '<link rel="preconnect" href="https://sc.affilizz.com" crossorigin>' . "\n";

        // Charge directement le script Affilizz avec defer (non bloquant mais prioritaire)
        echo '<script type="text/javascript" src="https://sc.affilizz.com/affilizz.js" defer crossorigin="anonymous"></script>' . "\n";
    }
}, 1);

// =============================================================================
// AFFILIZZ RENDER TRIGGER
// =============================================================================

/**
 * Force l'exécution immédiate du bloc Affilizz
 * (utile si WP Rocket ou CookieYes retardent les scripts tiers)
 *
 * @since 2.0.0
 */
add_action('wp_footer', function() {
    if (!is_admin() && (is_singular('post') || is_singular('test'))) : ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            function triggerAffilizzRender() {
                if (typeof window.affilizz !== 'undefined' && typeof window.affilizz.renderAll === 'function') {
                    try {
                        window.affilizz.renderAll();
                        console.log('Bloc Affilizz déclenché manuellement');
                        return true;
                    } catch (e) {
                        console.warn('Erreur Affilizz:', e);
                    }
                }
                return false;
            }

            if (!triggerAffilizzRender()) {
                // Retente après un court délai
                setTimeout(triggerAffilizzRender, 300);
                // Et au scroll si tout est encore bloqué
                window.addEventListener('scroll', triggerAffilizzRender, { once: true });
            }
        });
        </script>
    <?php endif;
});
