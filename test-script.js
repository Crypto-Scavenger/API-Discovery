/**
 * API & Discovery Plugin Feature Test Script
 * Paste this in browser console to test if plugin features work
 * 
 * Usage: Just paste and press Enter. Results will show in console.
 */

(async function testAPIDiscovery() {
    console.log('%c🔍 API & Discovery Plugin Test', 'font-size: 20px; font-weight: bold; color: #0073aa;');
    console.log('%cTesting plugin features...', 'color: #666; font-style: italic;');
    console.log('─'.repeat(60));

    const results = {
        passed: 0,
        failed: 0,
        tests: []
    };

    // Helper function to add test result
    function addResult(name, status, message, details = '') {
        results.tests.push({ name, status, message, details });
        if (status === 'PASS') results.passed++;
        if (status === 'FAIL') results.failed++;
    }

    // Helper function to check if element exists in HTML
    function checkHeadElement(selector, shouldExist = false) {
        const element = document.querySelector(selector);
        return shouldExist ? element !== null : element === null;
    }

    // Test 1: REST API Frontend Links
    console.log('\n%c📡 Test 1: REST API Frontend Links', 'font-weight: bold; color: #2271b1;');
    const restLink = document.querySelector('link[rel="https://api.w.org/"]');
    const restLinkAlternate = document.querySelector('link[href*="/wp-json/"]');
    
    if (!restLink && !restLinkAlternate) {
        addResult('REST API Links', 'PASS', 'REST API links removed from frontend', 
            'If "Disable REST API (Frontend)" is enabled, this should pass');
        console.log('✅ No REST API links found in HTML head');
    } else {
        addResult('REST API Links', 'FAIL', 'REST API links still present', 
            'Feature may be disabled or not working');
        console.log('❌ REST API links found:', restLink || restLinkAlternate);
    }

    // Test 2: XML-RPC Header
    console.log('\n%c📡 Test 2: XML-RPC Availability', 'font-weight: bold; color: #2271b1;');
    try {
        const xmlrpcResponse = await fetch('/xmlrpc.php', { 
            method: 'HEAD',
            cache: 'no-cache'
        });
        const hasPingbackHeader = xmlrpcResponse.headers.get('X-Pingback');
        
        if (hasPingbackHeader) {
            addResult('XML-RPC', 'FAIL', 'X-Pingback header present', 
                `Header value: ${hasPingbackHeader}`);
            console.log('❌ XML-RPC is active (X-Pingback header found)');
        } else {
            addResult('XML-RPC', 'PASS', 'X-Pingback header removed', 
                'If "Disable XML-RPC" is enabled, this should pass');
            console.log('✅ No X-Pingback header found');
        }
    } catch (error) {
        addResult('XML-RPC', 'INFO', 'Could not test XML-RPC', error.message);
        console.log('⚠️ Could not test XML-RPC:', error.message);
    }

    // Test 3: RSD Link
    console.log('\n%c📡 Test 3: Really Simple Discovery (RSD)', 'font-weight: bold; color: #2271b1;');
    const rsdLink = document.querySelector('link[rel="EditURI"]');
    
    if (!rsdLink) {
        addResult('RSD Link', 'PASS', 'RSD link removed from head', 
            'If "Disable RSD" is enabled, this should pass');
        console.log('✅ No RSD link found');
    } else {
        addResult('RSD Link', 'FAIL', 'RSD link still present', 
            `Found: ${rsdLink.href}`);
        console.log('❌ RSD link found:', rsdLink.href);
    }

    // Test 4: Windows Live Writer Manifest
    console.log('\n%c📡 Test 4: Windows Live Writer Manifest', 'font-weight: bold; color: #2271b1;');
    const wlwLink = document.querySelector('link[rel="wlwmanifest"]');
    
    if (!wlwLink) {
        addResult('WLW Manifest', 'PASS', 'Windows Live Writer link removed', 
            'If "Disable WLW" is enabled, this should pass');
        console.log('✅ No Windows Live Writer link found');
    } else {
        addResult('WLW Manifest', 'FAIL', 'Windows Live Writer link still present', 
            `Found: ${wlwLink.href}`);
        console.log('❌ Windows Live Writer link found:', wlwLink.href);
    }

    // Test 5: Feed Links
    console.log('\n%c📡 Test 5: RSS Feed Links', 'font-weight: bold; color: #2271b1;');
    const feedLinks = document.querySelectorAll('link[type="application/rss+xml"], link[type="application/atom+xml"]');
    
    if (feedLinks.length === 0) {
        addResult('Feed Links', 'PASS', 'Feed links removed from head', 
            'If "Disable RSS Feed Links" is enabled, this should pass');
        console.log('✅ No feed links found in HTML head');
    } else {
        addResult('Feed Links', 'FAIL', `${feedLinks.length} feed link(s) still present`, 
            'Feature may be disabled or not working');
        console.log(`❌ Found ${feedLinks.length} feed link(s):`, feedLinks);
    }

    // Test 6: Feed Accessibility
    console.log('\n%c📡 Test 6: RSS Feed Accessibility', 'font-weight: bold; color: #2271b1;');
    try {
        const feedResponse = await fetch('/feed/', { 
            method: 'GET',
            cache: 'no-cache'
        });
        
        if (feedResponse.status === 410) {
            addResult('Feeds Disabled', 'PASS', 'Feeds return 410 Gone status', 
                'If "Disable RSS Feeds Completely" is enabled, this should pass');
            console.log('✅ Feeds are completely disabled (410 Gone)');
        } else if (feedResponse.ok) {
            addResult('Feeds Disabled', 'FAIL', 'Feeds are still accessible', 
                `Status: ${feedResponse.status}`);
            console.log('❌ Feeds are still accessible');
            
            // Check feed generator
            const feedText = await feedResponse.text();
            if (feedText.includes('<generator>')) {
                console.log('  ⚠️ Feed contains generator tag (WordPress version visible)');
                addResult('Feed Generator', 'FAIL', 'Generator tag found in feed', 
                    'WordPress version is exposed');
            } else {
                console.log('  ✅ No generator tag in feed');
                addResult('Feed Generator', 'PASS', 'Generator tag removed from feed', 
                    'If "Disable Feed Generator Tags" is enabled, this should pass');
            }
        }
    } catch (error) {
        addResult('Feeds Disabled', 'INFO', 'Could not test feed access', error.message);
        console.log('⚠️ Could not test feed accessibility:', error.message);
    }

    // Test 7: Database Table Exists
    console.log('\n%c📡 Test 7: Plugin Database Table', 'font-weight: bold; color: #2271b1;');
    console.log('⚠️ Cannot test database from frontend - check in phpMyAdmin');
    console.log('   Table should be: wp_api_discovery_settings');
    addResult('Database Table', 'INFO', 'Cannot verify from frontend', 
        'Check for wp_api_discovery_settings table in database');

    // Print Summary
    console.log('\n' + '─'.repeat(60));
    console.log('%c📊 Test Summary', 'font-size: 16px; font-weight: bold; color: #0073aa;');
    console.log('─'.repeat(60));
    
    results.tests.forEach(test => {
        const icon = test.status === 'PASS' ? '✅' : 
                     test.status === 'FAIL' ? '❌' : 
                     '⚠️';
        const color = test.status === 'PASS' ? '#46b450' : 
                      test.status === 'FAIL' ? '#dc3232' : 
                      '#ffb900';
        
        console.log(`${icon} %c${test.name}%c: ${test.message}`, 
            `color: ${color}; font-weight: bold;`, 'color: inherit;');
        
        if (test.details) {
            console.log(`   └─ ${test.details}`);
        }
    });

    console.log('\n' + '─'.repeat(60));
    console.log(`%c✅ Passed: ${results.passed}  |  ❌ Failed: ${results.failed}  |  ⚠️ Info: ${results.tests.length - results.passed - results.failed}`, 
        'font-weight: bold; font-size: 14px;');
    console.log('─'.repeat(60));

    // Final recommendations
    console.log('\n%c💡 How to Interpret Results:', 'font-weight: bold; color: #2271b1;');
    console.log('');
    console.log('• PASS (✅) = Feature is working as expected (when enabled in settings)');
    console.log('• FAIL (❌) = Feature is NOT working (check plugin settings)');
    console.log('• INFO (⚠️) = Cannot verify this feature from browser console');
    console.log('');
    console.log('%cNote: Results depend on which features you enabled in plugin settings!', 'font-style: italic; color: #666;');
    console.log('Go to: WordPress Admin → Tools → API & Discovery to enable/disable features');
    console.log('');
    console.log('%c✅ Test Complete!', 'font-size: 16px; font-weight: bold; color: #46b450;');

    return results;
})();
