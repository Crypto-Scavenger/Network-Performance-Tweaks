/**
 * Network & Performance Tweaks - Browser Console Test Script
 * 
 * Instructions:
 * 1. Open your WordPress site in browser
 * 2. Open Developer Console (F12)
 * 3. Paste this entire script and press Enter
 * 4. Review the detailed test results
 * 
 * Note: Some tests require specific plugin settings to be enabled
 */

(function() {
    'use strict';
    
    // Test results storage
    const results = {
        passed: [],
        failed: [],
        warnings: [],
        info: []
    };
    
    // Styling for console output
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
    
    // =====================================================================
    // TEST 1: DNS Prefetch Disabled
    // =====================================================================
    console.log('%c\n📡 TEST 1: DNS Prefetch Settings', styles.section);
    
    // Check for dns-prefetch-control meta tag
    const dnsPrefetchMeta = document.querySelector('meta[http-equiv="x-dns-prefetch-control"]');
    if (dnsPrefetchMeta && dnsPrefetchMeta.getAttribute('content') === 'off') {
        results.passed.push('✓ DNS prefetch control meta tag present and set to "off"');
        console.log('%c  ✓ DNS prefetch control meta tag found', styles.pass);
    } else {
        results.failed.push('✗ DNS prefetch control meta tag missing or incorrect');
        console.log('%c  ✗ DNS prefetch control meta tag not found', styles.fail);
    }
    
    // Check for any dns-prefetch link tags
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
    
    // =====================================================================
    // TEST 2: Google Fonts Disabled
    // =====================================================================
    console.log('%c\n🔤 TEST 2: Google Fonts', styles.section);
    
    // Check stylesheets
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
    
    // Check link tags
    const googleFontsLinks = document.querySelectorAll('link[href*="fonts.googleapis.com"]');
    if (googleFontsLinks.length > 0) {
        results.warnings.push(`⚠ Found ${googleFontsLinks.length} Google Fonts link tag(s)`);
        console.log('%c  ⚠ Google Fonts link tags found:', styles.warn);
        googleFontsLinks.forEach(link => {
            console.log('    - ' + link.href);
        });
    }
    
    // =====================================================================
    // TEST 3: Google Maps Disabled
    // =====================================================================
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
    
    // =====================================================================
    // TEST 4: Script/Style Version Parameters
    // =====================================================================
    console.log('%c\n🔢 TEST 4: Script/Style Version Parameters', styles.section);
    
    // Check scripts
    const scriptsWithVer = Array.from(document.scripts).filter(script => {
        return script.src && script.src.includes('?ver=');
    });
    
    // Check stylesheets
    const stylesWithVer = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).filter(link => {
        return link.href && link.href.includes('?ver=');
    });
    
    const totalWithVer = scriptsWithVer.length + stylesWithVer.length;
    
    if (totalWithVer === 0) {
        results.passed.push('✓ All version parameters removed from scripts/styles');
        console.log('%c  ✓ No version parameters found', styles.pass);
    } else {
        results.info.push(`ℹ Found ${scriptsWithVer.length} scripts and ${stylesWithVer.length} styles with version parameters`);
        console.log('%c  ℹ Version parameters found (expected if setting disabled):', styles.info);
        console.log(`    - ${scriptsWithVer.length} scripts with ?ver=`);
        console.log(`    - ${stylesWithVer.length} styles with ?ver=`);
    }
    
    // =====================================================================
    // TEST 5: WordPress Version Hidden
    // =====================================================================
    console.log('%c\n🔒 TEST 5: WordPress Version Disclosure', styles.section);
    
    const generatorMeta = document.querySelector('meta[name="generator"]');
    if (!generatorMeta || !generatorMeta.content.includes('WordPress')) {
        results.passed.push('✓ WordPress version not disclosed in meta tags');
        console.log('%c  ✓ Generator meta tag removed/hidden', styles.pass);
    } else {
        results.failed.push('✗ WordPress version exposed in generator meta tag');
        console.log('%c  ✗ Generator meta tag still present:', styles.fail);
        console.log('    - ' + generatorMeta.content);
    }
    
    // =====================================================================
    // TEST 6: RSS Feed Links
    // =====================================================================
    console.log('%c\n📰 TEST 6: RSS Feed Links', styles.section);
    
    const feedLinks = document.querySelectorAll('link[type="application/rss+xml"], link[type="application/atom+xml"]');
    
    if (feedLinks.length === 0) {
        results.passed.push('✓ No RSS feed links found');
        console.log('%c  ✓ RSS feed links removed', styles.pass);
    } else {
        results.info.push(`ℹ Found ${feedLinks.length} RSS feed link(s)`);
        console.log('%c  ℹ RSS feed links present (expected if setting disabled):', styles.info);
        feedLinks.forEach(link => {
            console.log('    - ' + link.title + ' (' + link.href + ')');
        });
    }
    
    // =====================================================================
    // TEST 7: Additional Meta Tags (RSD, WLW)
    // =====================================================================
    console.log('%c\n🏷️ TEST 7: Discovery Meta Tags', styles.section);
    
    const rsdLink = document.querySelector('link[rel="EditURI"]');
    const wlwLink = document.querySelector('link[rel="wlwmanifest"]');
    
    let discoveryTagsFound = 0;
    
    if (!rsdLink) {
        results.passed.push('✓ RSD link removed');
        console.log('%c  ✓ RSD (EditURI) link not found', styles.pass);
    } else {
        results.info.push('ℹ RSD link present');
        console.log('%c  ℹ RSD link found:', styles.info);
        console.log('    - ' + rsdLink.href);
        discoveryTagsFound++;
    }
    
    if (!wlwLink) {
        results.passed.push('✓ Windows Live Writer manifest removed');
        console.log('%c  ✓ WLW manifest not found', styles.pass);
    } else {
        results.info.push('ℹ Windows Live Writer manifest present');
        console.log('%c  ℹ WLW manifest found:', styles.info);
        console.log('    - ' + wlwLink.href);
        discoveryTagsFound++;
    }
    
    // =====================================================================
    // TEST 8: REST API Links
    // =====================================================================
    console.log('%c\n🔌 TEST 8: REST API Discovery', styles.section);
    
    const restApiLink = document.querySelector('link[rel="https://api.w.org/"]');
    
    if (!restApiLink) {
        results.info.push('ℹ REST API link not found in head');
        console.log('%c  ℹ REST API link removed from head', styles.info);
    } else {
        results.info.push('ℹ REST API link present in head');
        console.log('%c  ℹ REST API link found:', styles.info);
        console.log('    - ' + restApiLink.href);
    }
    
    // =====================================================================
    // TEST 9: Shortlink
    // =====================================================================
    console.log('%c\n🔗 TEST 9: Shortlink Tag', styles.section);
    
    const shortlink = document.querySelector('link[rel="shortlink"]');
    
    if (!shortlink) {
        results.info.push('ℹ Shortlink tag not found');
        console.log('%c  ℹ Shortlink tag not present', styles.info);
    } else {
        results.info.push('ℹ Shortlink tag present');
        console.log('%c  ℹ Shortlink found:', styles.info);
        console.log('    - ' + shortlink.href);
    }
    
    // =====================================================================
    // TEST 10: Check for External Resources
    // =====================================================================
    console.log('%c\n🌐 TEST 10: External Resources Summary', styles.section);
    
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
        externalScripts.forEach(script => {
            try {
                const url = new URL(script.src);
                console.log(`    - ${url.hostname}`);
            } catch(e) {}
        });
    }
    
    if (externalStyles.length > 0) {
        console.log(`\n  External Stylesheets:`);
        externalStyles.forEach(link => {
            try {
                const url = new URL(link.href);
                console.log(`    - ${url.hostname}`);
            } catch(e) {}
        });
    }
    
    // =====================================================================
    // FINAL SUMMARY
    // =====================================================================
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
    
    // =====================================================================
    // MANUAL TESTING INSTRUCTIONS
    // =====================================================================
    console.log('%c\n═══════════════════════════════════════════════════════', styles.section);
    console.log('%c🔍 MANUAL TESTS REQUIRED', styles.title);
    console.log('%c═══════════════════════════════════════════════════════', styles.section);
    
    console.log(`%c
The following settings require manual verification:

1. POST REVISIONS LIMIT
   - Go to Posts → Edit any post
   - Make 10+ changes and save each time
   - Check: Tools → Revisions (should show only your configured limit)

2. EMPTY TRASH DAYS
   - Trash a post
   - Check database or wait for configured days
   - Verify: Post is permanently deleted after X days

3. AUTOSAVE FREQUENCY
   - Edit a post and stop typing
   - Watch for autosave notifications
   - Check: Should autosave at your configured interval (default 60s)

4. SELF PINGBACKS
   - Create a post with a link to another post on your site
   - Check: No pingback notification should appear

5. SHORTCODE CLEANUP
   - Add content with a fake shortcode like [nonexistent]
   - View the post on frontend
   - Check: Shortcode should be removed if cleanup is enabled

6. HEARTBEAT FREQUENCY
   - Open browser DevTools → Network tab
   - Stay on WordPress admin for 2+ minutes
   - Check: /admin-ajax.php?action=heartbeat calls at your interval

7. DATABASE TABLE
   - Check phpMyAdmin or similar
   - Look for: wp_npt_settings table
   - Should contain: All your plugin settings
`, styles.info);
    
    console.log('%c\n═══════════════════════════════════════════════════════', styles.section);
    console.log('%c✅ TESTING COMPLETE', styles.title);
    console.log('%c═══════════════════════════════════════════════════════\n', styles.section);
    
    // Return summary object for programmatic access
    return {
        passed: results.passed.length,
        failed: results.failed.length,
        warnings: results.warnings.length,
        info: results.info.length,
        details: results
    };
})();
