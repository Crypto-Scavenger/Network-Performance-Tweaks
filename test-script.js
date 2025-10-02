/**
 * Network & Performance Tweaks - Browser Console Test Script
 * 
 * Instructions:
 * 1. Open your WordPress site in browser
 * 2. Open Developer Console (F12)
 * 3. Paste this entire script and press Enter
 * 4. Review the detailed test results
 */

(function() {
    'use strict';
    
    const results = {
        passed: [],
        failed: [],
        warnings: [],
        info: []
    };
    
    const styles = {
        title: 'font-size: 18px; font-weight: bold; color: #2271b1; padding: 10px 0;',
        pass: 'color: #00a32a; font-weight: bold;',
        fail: 'color: #d63638; font-weight: bold;',
        warn: 'color: #dba617; font-weight: bold;',
        info: 'color: #72aee6;',
        section: 'font-size: 14px; font-weight: bold; color: #1d2327; margin-top: 10px;'
    };
    
    console.log('%c🔧 Network & Performance Tweaks - Plugin Test', styles.title);
    console.log('%cTesting plugin functionality...', styles.info);
    console.log('─────────────────────────────────────────────────────────');
    
    // TEST 1: DNS Prefetch
    console.log('%c\n📡 TEST 1: DNS Prefetch Settings', styles.section);
    
    const dnsPrefetchMeta = document.querySelector('meta[http-equiv="x-dns-prefetch-control"]');
    if (dnsPrefetchMeta && dnsPrefetchMeta.getAttribute('content') === 'off') {
        results.passed.push('✓ DNS prefetch control meta tag present and set to "off"');
        console.log('%c  ✓ DNS prefetch control meta tag found', styles.pass);
    } else {
        results.failed.push('✗ DNS prefetch control meta tag missing or incorrect');
        console.log('%c  ✗ DNS prefetch control meta tag not found', styles.fail);
    }
    
    const dnsPrefetchLinks = document.querySelectorAll('link[rel="dns-prefetch"]');
    if (dnsPrefetchLinks.length === 0) {
        results.passed.push('✓ No DNS prefetch link tags found');
        console.log('%c  ✓ No DNS prefetch links present', styles.pass);
    } else {
        results.warnings.push(`⚠ Found ${dnsPrefetchLinks.length} DNS prefetch link(s)`);
        console.log('%c  ⚠ Found DNS prefetch links (may be added by theme/plugins):', styles.warn);
        dnsPrefetchLinks.forEach(link => {
            console.log('    - ' + link.href);
        });
    }
    
    // TEST 2: Google Fonts
    console.log('%c\n🔤 TEST 2: Google Fonts', styles.section);
    
    const googleFontsStyles = Array.from(document.styleSheets).filter(sheet => {
        try {
            return sheet.href && sheet.href.includes('fonts.googleapis.com');
        } catch(e) {
            return false;
        }
    });
    
    if (googleFontsStyles.length === 0) {
        results.passed.push('✓ No Google Fonts stylesheets loaded');
        console.log('%c  ✓ No Google Fonts detected', styles.pass);
    } else {
        results.failed.push(`✗ Found ${googleFontsStyles.length} Google Fonts stylesheet(s)`);
        console.log('%c  ✗ Google Fonts still loaded:', styles.fail);
        googleFontsStyles.forEach(sheet => {
            console.log('    - ' + sheet.href);
        });
    }
    
    const googleFontsLinks = document.querySelectorAll('link[href*="fonts.googleapis.com"]');
    if (googleFontsLinks.length > 0) {
        results.warnings.push(`⚠ Found ${googleFontsLinks.length} Google Fonts link tag(s)`);
        console.log('%c  ⚠ Google Fonts link tags found:', styles.warn);
        googleFontsLinks.forEach(link => {
            console.log('    - ' + link.href);
        });
    }
    
    // TEST 3: Google Maps
    console.log('%c\n🗺️ TEST 3: Google Maps API', styles.section);
    
    const googleMapsScripts = Array.from(document.scripts).filter(script => {
        return script.src && (
            script.src.includes('maps.googleapis.com') || 
            script.src.includes('maps.google.com')
        );
    });
    
    if (googleMapsScripts.length === 0) {
        results.passed.push('✓ No Google Maps scripts loaded');
        console.log('%c  ✓ No Google Maps scripts detected', styles.pass);
    } else {
        results.failed.push(`✗ Found ${googleMapsScripts.length} Google Maps script(s)`);
        console.log('%c  ✗ Google Maps scripts still loaded:', styles.fail);
        googleMapsScripts.forEach(script => {
            console.log('    - ' + script.src);
        });
    }
    
    // TEST 4: External Resources Summary
    console.log('%c\n🌐 TEST 4: External Resources Summary', styles.section);
    
    const allScripts = Array.from(document.scripts);
    const allStyles = Array.from(document.querySelectorAll('link[rel="stylesheet"]'));
    
    const currentDomain = window.location.hostname;
    
    const externalScripts = allScripts.filter(script => {
        if (!script.src) return false;
        try {
            const url = new URL(script.src);
            return url.hostname !== currentDomain;
        } catch(e) {
            return false;
        }
    });
    
    const externalStyles = allStyles.filter(link => {
        if (!link.href) return false;
        try {
            const url = new URL(link.href);
            return url.hostname !== currentDomain;
        } catch(e) {
            return false;
        }
    });
    
    console.log(`  📊 Resource Summary:`);
    console.log(`    - Total Scripts: ${allScripts.filter(s => s.src).length}`);
    console.log(`    - External Scripts: ${externalScripts.length}`);
    console.log(`    - Total Styles: ${allStyles.length}`);
    console.log(`    - External Styles: ${externalStyles.length}`);
    
    if (externalScripts.length > 0) {
        console.log(`\n  External Scripts:`);
        const externalDomains = {};
        externalScripts.forEach(script => {
            try {
                const url = new URL(script.src);
                externalDomains[url.hostname] = (externalDomains[url.hostname] || 0) + 1;
            } catch(e) {}
        });
        Object.keys(externalDomains).forEach(domain => {
            console.log(`    - ${domain} (${externalDomains[domain]} script${externalDomains[domain] > 1 ? 's' : ''})`);
        });
    }
    
    if (externalStyles.length > 0) {
        console.log(`\n  External Stylesheets:`);
        const externalStyleDomains = {};
        externalStyles.forEach(link => {
            try {
                const url = new URL(link.href);
                externalStyleDomains[url.hostname] = (externalStyleDomains[url.hostname] || 0) + 1;
            } catch(e) {}
        });
        Object.keys(externalStyleDomains).forEach(domain => {
            console.log(`    - ${domain} (${externalStyleDomains[domain]} stylesheet${externalStyleDomains[domain] > 1 ? 's' : ''})`);
        });
    }
    
    // TEST 5: Shortcode Cleanup (Frontend)
    console.log('%c\n📝 TEST 5: Shortcode Cleanup', styles.section);
    
    const postContent = document.querySelector('.entry-content, .post-content, article .content, main article');
    if (postContent) {
        const shortcodePattern = /\[([^\]]+)\]/g;
        const foundShortcodes = postContent.innerText.match(shortcodePattern);
        
        if (foundShortcodes && foundShortcodes.length > 0) {
            results.info.push(`ℹ Found ${foundShortcodes.length} potential shortcode(s) in content`);
            console.log('%c  ℹ Shortcodes found in content:', styles.info);
            foundShortcodes.slice(0, 5).forEach(sc => {
                console.log('    - ' + sc);
            });
            if (foundShortcodes.length > 5) {
                console.log(`    ... and ${foundShortcodes.length - 5} more`);
            }
            console.log('  ⚠ If these are from deactivated plugins, shortcode cleanup may not be enabled');
        } else {
            results.passed.push('✓ No leftover shortcodes detected in content');
            console.log('%c  ✓ No shortcodes found in content', styles.pass);
        }
    } else {
        results.info.push('ℹ Could not find post content area to check for shortcodes');
        console.log('%c  ℹ Could not locate post content area', styles.info);
    }
    
    // FINAL SUMMARY
    console.log('%c\n═══════════════════════════════════════════════════════', styles.section);
    console.log('%c📊 TEST SUMMARY', styles.title);
    console.log('%c═══════════════════════════════════════════════════════', styles.section);
    
    console.log(`%c\n✓ PASSED: ${results.passed.length}`, styles.pass);
    results.passed.forEach(msg => console.log(`  ${msg}`));
    
    if (results.failed.length > 0) {
        console.log(`%c\n✗ FAILED: ${results.failed.length}`, styles.fail);
        results.failed.forEach(msg => console.log(`  ${msg}`));
    }
    
    if (results.warnings.length > 0) {
        console.log(`%c\n⚠ WARNINGS: ${results.warnings.length}`, styles.warn);
        results.warnings.forEach(msg => console.log(`  ${msg}`));
    }
    
    if (results.info.length > 0) {
        console.log(`%c\nℹ INFO: ${results.info.length}`, styles.info);
        results.info.forEach(msg => console.log(`  ${msg}`));
    }
    
    // MANUAL TESTING INSTRUCTIONS
    console.log('%c\n═══════════════════════════════════════════════════════', styles.section);
    console.log('%c🔍 MANUAL TESTS REQUIRED', styles.title);
    console.log('%c═══════════════════════════════════════════════════════', styles.section);
    
    console.log(`%c
The following settings require manual verification in WordPress admin:

1. POST REVISIONS LIMIT
   - Go to Posts → Edit any post
   - Make 10+ changes and save each time
   - Go to post editor → Click "Revisions" in right sidebar
   - Count: Should show only your configured limit (default: 5)

2. EMPTY TRASH DAYS
   - Trash a post
   - Check database table wp_posts for _wp_trash_meta_time
   - Or wait for configured days and verify permanent deletion

3. AUTOSAVE FREQUENCY
   - Edit a post and stop typing
   - Watch bottom left for "Draft saved at [time]"
   - Time between autosaves should match your setting (default: 60s)

4. SELF PINGBACKS
   - Create a new post with a link to another post on your site
   - Publish and check Comments section
   - Should NOT see a pingback notification

5. HEARTBEAT FREQUENCY (Admin Area Test)
   - Open admin area and open DevTools → Network tab
   - Filter by "heartbeat" or look for admin-ajax.php
   - Watch the timing between heartbeat requests
   - Should match your configured interval (default: 60s)

6. DATABASE TABLE
   - Check phpMyAdmin or similar
   - Look for: wp_npt_settings table
   - Should contain: All your plugin settings as rows

7. WORDPRESS CONSTANTS
   - Install "Query Monitor" plugin
   - Check Constants section
   - Look for: WP_POST_REVISIONS, EMPTY_TRASH_DAYS, AUTOSAVE_INTERVAL
   - Values should match your plugin settings
`, styles.info);
    
    console.log('%c\n═══════════════════════════════════════════════════════', styles.section);
    console.log('%c✅ FRONTEND TESTING COMPLETE', styles.title);
    console.log('%c═══════════════════════════════════════════════════════\n', styles.section);
    
    return {
        passed: results.passed.length,
        failed: results.failed.length,
        warnings: results.warnings.length,
        info: results.info.length,
        details: results
    };
})();
