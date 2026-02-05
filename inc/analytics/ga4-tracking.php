<?php
/**
 * Google Analytics 4 Tracking
 *
 * GA4 implementation with CookieYes consent management.
 *
 * @package Labomaison
 * @subpackage Analytics
 * @since 2.0.0
 *
 * Functions in this file:
 * - lm_should_load_ga4()
 * - lm_enqueue_ga4_head()
 * - AdSense verification
 *
 * Dependencies: None
 * Load Priority: 9
 * Risk Level: HIGH
 *
 * Migrated from: functions.php L1178-1464
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// GA4 LOADING CONDITION
// =============================================================================

/**
 * Determine if GA4 should be loaded
 *
 * Excludes: logged-in users, previews, unpublished content, preprod
 *
 * @since 2.0.0
 * @return bool True if GA4 should load
 */
if ( ! function_exists('lm_should_load_ga4') ) {
    function lm_should_load_ga4() {
        // Préprod ?
        $is_preprod = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'preprod.') !== false;

        // Brouillon / non publié (uniquement sur les contenus singuliers)
        $not_published = false;
        if ( function_exists('is_singular') && is_singular() && function_exists('get_post_status') ) {
            $status = get_post_status( get_queried_object_id() );
            if ( $status && $status !== 'publish' ) {
                $not_published = true;
            }
        }

        return ! is_user_logged_in()
            && ! is_preview()
            && ! $not_published
            && ! $is_preprod;
    }
}

// =============================================================================
// GA4 + CONSENT MODE V2 + COOKIEYES BRIDGE
// =============================================================================

/**
 * Google Tag (GT-NMCXXG3R) - Consent Mode v2 + CookieYes Bridge
 * Version 2.1 - Compatible GA4 via Google Tag
 *
 * @since 2.0.0
 * @return void
 */
function lm_enqueue_ga4_head() {
    // Vérifier si GA4 doit être chargé
    if ( ! lm_should_load_ga4() ) {
        add_action('wp_head', function () {
            echo "<!-- GA4 disabled (admin/preview/preprod) -->\n";
        }, 1);
        return;
    }

    // 1) Charger le script Google Tag Manager
    wp_enqueue_script(
        'lm-gtag',
        'https://www.googletagmanager.com/gtag/js?id=GT-NMCXXG3R',
        array(),
        null,
        false // Dans le <head>
    );

    // 2) Configuration principale avec gestion intelligente du consentement
    $inline_head = "
window.dataLayer = window.dataLayer || [];
function gtag(){ dataLayer.push(arguments); }

// Pre-consent (before CookieYes processing)
gtag('consent', 'default', {
  'ad_storage': 'denied',
  'analytics_storage': 'denied',
  'ad_user_data': 'denied',
  'ad_personalization': 'denied',
  'functionality_storage': 'granted',
  'security_storage': 'granted',
  'wait_for_update': 500
});

// Control variables
window.lm_ga4 = {
    initialized: false,
    pageview_sent: false,
    consent_granted: false,
    measurement_id: 'GT-NMCXXG3R'
};


/**
 * Detect existing CookieYes consent
 * Compatible with v3.x storing in 'cookieyes-consent' cookie
 */
function lm_get_cookieyes_consent() {
    try {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const trimmed = cookie.trim();
            if (trimmed.startsWith('cookieyes-consent=')) {
                const value = decodeURIComponent(trimmed.substring(18));
                const data = JSON.parse(value);
                return {
                    analytics: data.categories?.analytics === 'yes',
                    ads: data.categories?.advertisement === 'yes'
                };
            }
        }
    } catch(e) {
        // No existing CookieYes consent
    }
    return null;
}

// Check existing consent
const existingConsent = lm_get_cookieyes_consent();

// Configure Consent Mode based on current state
if (existingConsent && existingConsent.analytics) {
    // Returning visitor with consent = immediate granted
    gtag('consent', 'default', {
        'ad_storage': existingConsent.ads ? 'granted' : 'denied',
        'analytics_storage': 'granted',
        'ad_user_data': existingConsent.ads ? 'granted' : 'denied',
        'ad_personalization': existingConsent.ads ? 'granted' : 'denied',
        'functionality_storage': 'granted',
        'security_storage': 'granted'
    });
    window.lm_ga4.consent_granted = true;
} else {
    // New visitor or rejection = restricted mode
    gtag('consent', 'default', {
        'ad_storage': 'denied',
        'analytics_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'functionality_storage': 'granted',
        'security_storage': 'granted',
        'wait_for_update': 2500  // Wait 2.5s for banner
    });
}

// Initialize GA4
gtag('js', new Date());

// Configuration with optimized parameters
gtag('config', window.lm_ga4.measurement_id, {
    'anonymize_ip': true,
    'send_page_view': true,  // Sent only if consent granted
    'page_location': window.location.href,
    'page_title': document.title,
    'page_referrer': document.referrer,
    'cookie_flags': 'SameSite=None;Secure'  // Cross-domain compatibility
});

window.lm_ga4.initialized = true;

// Mark page_view as sent if consent was already granted
if (window.lm_ga4.consent_granted) {
    window.lm_ga4.pageview_sent = true;
}
";

    wp_add_inline_script('lm-gtag', $inline_head, 'after');

    // 3) Bridge CookieYes -> GA4 with smart recovery
    wp_register_script('lm-ga4-bridge', '', array(), null, true);

    $inline_footer = "
/**
 * Consent update and page_view recovery
 */
function lm_handle_consent_update(consent) {
    if (typeof gtag !== 'function' || !consent) {
        return;
    }

    // Parse CookieYes v3.x format
    const accepted = Array.isArray(consent.accepted) ? consent.accepted : [];
    const rejected = Array.isArray(consent.rejected) ? consent.rejected : [];

    const analyticsGranted = accepted.includes('analytics');
    const adsGranted = accepted.includes('advertisement');

    // Save previous state
    const wasGrantedBefore = window.lm_ga4.consent_granted;
    const pageviewAlreadySent = window.lm_ga4.pageview_sent;

    // Update Consent Mode
    gtag('consent', 'update', {
        'analytics_storage': analyticsGranted ? 'granted' : 'denied',
        'ad_storage': adsGranted ? 'granted' : 'denied',
        'ad_user_data': adsGranted ? 'granted' : 'denied',
        'ad_personalization': adsGranted ? 'granted' : 'denied'
    });

    // CRITICAL: Recover lost page_view
    if (analyticsGranted && !wasGrantedBefore && !pageviewAlreadySent) {
        // Send the page_view that was blocked
        gtag('event', 'page_view', {
            page_location: window.location.href,
            page_title: document.title,
            page_referrer: document.referrer,
            engagement_time_msec: Math.round(performance.now()),
            consent_timing: 'delayed'
        });

        window.lm_ga4.pageview_sent = true;

        // Bonus: send consent event for tracking
        gtag('event', 'consent_granted', {
            event_category: 'engagement',
            event_label: 'cookieyes',
            value: Math.round(performance.now() / 1000)
        });
    }

    // Update state
    window.lm_ga4.consent_granted = analyticsGranted;
}

// Listen to ALL possible CookieYes events
const cookieyesEvents = [
    'cookieyes_consent_update',     // Main v3.x event
    'cli_consent_update',           // Legacy compatibility
    'cookieyes_event_updated',      // Alternative event
    'cookieYes_consent_update'      // Case variant
];

cookieyesEvents.forEach(eventName => {
    document.addEventListener(eventName, function(e) {
        lm_handle_consent_update(e.detail);
    }, { once: false, passive: true });
});

// Safety mechanism: check after timeout
setTimeout(function() {
    if (!window.lm_ga4.pageview_sent && window.lm_ga4.initialized) {
        const currentConsent = lm_get_cookieyes_consent();
        if (currentConsent && currentConsent.analytics) {
            gtag('event', 'page_view', {
                page_location: window.location.href,
                page_title: document.title,
                consent_timing: 'timeout_recovery'
            });
            window.lm_ga4.pageview_sent = true;
        }
    }
}, 5000);

// Debug logging in development
if (window.location.hostname === 'localhost' || window.location.search.includes('debug=ga4')) {
    window.lm_ga4_debug = function() {
        console.table({
            'GA4 Initialized': window.lm_ga4.initialized,
            'Page View Sent': window.lm_ga4.pageview_sent,
            'Consent Granted': window.lm_ga4.consent_granted,
            'Measurement ID': window.lm_ga4.measurement_id
        });
    };
}
";

    wp_add_inline_script('lm-ga4-bridge', $inline_footer, 'after');
    wp_enqueue_script('lm-ga4-bridge');
}

add_action('wp_enqueue_scripts', 'lm_enqueue_ga4_head', 20);

// =============================================================================
// GOOGLE ADSENSE
// =============================================================================

/**
 * Google AdSense domain verification script
 *
 * @since 2.0.0
 */
add_action('wp_head', function() {
    ?>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7727728964402200"
        crossorigin="anonymous"></script>
    <?php
});
