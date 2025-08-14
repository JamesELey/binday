#!/usr/bin/env node

/**
 * 🧪 BinDay Deployment Health Check
 * 
 * Comprehensive tests to verify successful deployment and site functionality
 */

const https = require('https');
const http = require('http');

class HealthChecker {
    constructor(baseUrl) {
        this.baseUrl = baseUrl.replace(/\/$/, ''); // Remove trailing slash
        this.results = [];
        this.totalTests = 0;
        this.passedTests = 0;
    }

    async runAllTests() {
        console.log('🧪 Starting BinDay Deployment Health Checks...\n');
        console.log(`🎯 Target URL: ${this.baseUrl}\n`);

        // Core functionality tests
        await this.testSiteAccessibility();
        await this.testHomePage();
        await this.testBinMapPage();
        await this.testCollectionsPage();
        await this.testAreasPage();
        await this.testAdminSeedPage();
        
        // Technical health tests
        await this.testLaravelFramework();
        await this.testDatabaseConnection();
        await this.testAssetLoading();
        await this.testResponseTimes();

        this.printResults();
        
        // Exit with appropriate code
        process.exit(this.passedTests === this.totalTests ? 0 : 1);
    }

    async makeRequest(path, expectedStatus = 200, timeout = 10000) {
        return new Promise((resolve) => {
            const url = `${this.baseUrl}${path}`;
            const client = this.baseUrl.startsWith('https') ? https : http;
            
            const startTime = Date.now();
            
            const req = client.get(url, { timeout }, (res) => {
                const responseTime = Date.now() - startTime;
                let data = '';
                
                res.on('data', chunk => data += chunk);
                res.on('end', () => {
                    resolve({
                        status: res.statusCode,
                        data,
                        responseTime,
                        headers: res.headers
                    });
                });
            });

            req.on('timeout', () => {
                req.destroy();
                resolve({ status: 'timeout', responseTime: timeout });
            });

            req.on('error', (err) => {
                resolve({ status: 'error', error: err.message, responseTime: Date.now() - startTime });
            });
        });
    }

    test(name, passed, details = '') {
        this.totalTests++;
        if (passed) {
            this.passedTests++;
            console.log(`✅ ${name}`);
        } else {
            console.log(`❌ ${name}`);
        }
        if (details) {
            console.log(`   ${details}`);
        }
        console.log('');
        
        this.results.push({ name, passed, details });
    }

    async testSiteAccessibility() {
        console.log('🌐 Testing Site Accessibility...');
        const response = await this.makeRequest('/');
        
        this.test(
            'Site is accessible',
            response.status === 200,
            response.status === 200 ? 
                `Response time: ${response.responseTime}ms` : 
                `Status: ${response.status}, Error: ${response.error || 'Unknown'}`
        );
    }

    async testHomePage() {
        console.log('🏠 Testing Home Page...');
        const response = await this.makeRequest('/');
        
        if (response.status === 200) {
            const hasTitle = response.data.includes('<title>') || response.data.includes('BinDay');
            const hasHtml = response.data.includes('<html') || response.data.includes('<!DOCTYPE');
            
            this.test(
                'Home page loads with valid HTML',
                hasHtml,
                hasHtml ? 'Valid HTML structure detected' : 'No valid HTML structure found'
            );
            
            this.test(
                'Home page has proper title/content',
                hasTitle,
                hasTitle ? 'Page title/content found' : 'No title or BinDay content found'
            );
        } else {
            this.test('Home page loads with valid HTML', false, 'Home page not accessible');
            this.test('Home page has proper title/content', false, 'Home page not accessible');
        }
    }

    async testBinMapPage() {
        console.log('🗺️ Testing Bin Map Page...');
        const response = await this.makeRequest('/bins/map');
        
        this.test(
            'Bin Map page accessible',
            response.status === 200,
            response.status === 200 ? 
                `Loaded in ${response.responseTime}ms` : 
                `Status: ${response.status}`
        );
    }

    async testCollectionsPage() {
        console.log('📅 Testing Collections Page...');
        const response = await this.makeRequest('/collections');
        
        this.test(
            'Collections page accessible',
            response.status === 200,
            response.status === 200 ? 
                `Loaded in ${response.responseTime}ms` : 
                `Status: ${response.status}`
        );
    }

    async testAreasPage() {
        console.log('🏘️ Testing Areas Page...');
        const response = await this.makeRequest('/areas');
        
        this.test(
            'Areas page accessible',
            response.status === 200,
            response.status === 200 ? 
                `Loaded in ${response.responseTime}ms` : 
                `Status: ${response.status}`
        );
    }

    async testAdminSeedPage() {
        console.log('⚙️ Testing Admin Seed Page...');
        const response = await this.makeRequest('/admin/seed');
        
        this.test(
            'Admin seed page accessible',
            response.status === 200,
            response.status === 200 ? 
                `Loaded in ${response.responseTime}ms` : 
                `Status: ${response.status}`
        );
    }

    async testLaravelFramework() {
        console.log('🚀 Testing Laravel Framework...');
        const response = await this.makeRequest('/');
        
        if (response.status === 200) {
            const hasLaravel = response.data.includes('Laravel') || 
                             response.headers['x-powered-by']?.includes('Laravel') ||
                             response.data.includes('csrf-token');
            
            this.test(
                'Laravel framework detected',
                hasLaravel,
                hasLaravel ? 'Laravel framework signatures found' : 'No Laravel signatures detected'
            );
        } else {
            this.test('Laravel framework detected', false, 'Cannot test - site not accessible');
        }
    }

    async testDatabaseConnection() {
        console.log('🗄️ Testing Database Connection...');
        
        // Test if pages that likely use database load
        const response = await this.makeRequest('/collections');
        
        // If collections page loads without 500 error, DB is likely working
        const dbWorking = response.status === 200;
        
        this.test(
            'Database connection working',
            dbWorking,
            dbWorking ? 
                'Database-dependent pages load successfully' : 
                'Database-dependent pages failing (possible DB issue)'
        );
    }

    async testAssetLoading() {
        console.log('📦 Testing Asset Loading...');
        
        // Test common Laravel asset paths
        const cssResponse = await this.makeRequest('/css/app.css');
        const jsResponse = await this.makeRequest('/js/app.js');
        
        const assetsOk = cssResponse.status === 200 || jsResponse.status === 200;
        
        this.test(
            'Static assets loading',
            assetsOk,
            assetsOk ? 
                'At least one asset type loading successfully' : 
                'No standard assets found (CSS/JS may be inline or CDN-based)'
        );
    }

    async testResponseTimes() {
        console.log('⚡ Testing Response Times...');
        
        const response = await this.makeRequest('/');
        const responseTime = response.responseTime;
        
        const fastResponse = responseTime < 2000; // Under 2 seconds
        const acceptableResponse = responseTime < 5000; // Under 5 seconds
        
        this.test(
            'Response time acceptable',
            acceptableResponse,
            `Response time: ${responseTime}ms ${fastResponse ? '(Fast!)' : acceptableResponse ? '(Acceptable)' : '(Slow)'}`
        );
    }

    printResults() {
        console.log('=' .repeat(60));
        console.log('🧪 DEPLOYMENT HEALTH CHECK RESULTS');
        console.log('=' .repeat(60));
        console.log(`✅ Passed: ${this.passedTests}/${this.totalTests} tests`);
        console.log(`❌ Failed: ${this.totalTests - this.passedTests}/${this.totalTests} tests`);
        console.log('');
        
        if (this.passedTests === this.totalTests) {
            console.log('🎉 ALL TESTS PASSED! Deployment is healthy! 🚀');
        } else {
            console.log('⚠️  Some tests failed. Please investigate the issues above.');
            console.log('');
            console.log('Failed tests:');
            this.results.filter(r => !r.passed).forEach(r => {
                console.log(`   ❌ ${r.name}: ${r.details}`);
            });
        }
        
        console.log('');
        console.log(`🌐 Application URL: ${this.baseUrl}`);
        console.log('=' .repeat(60));
    }
}

// Main execution
async function main() {
    const siteUrl = process.env.SITE_URL || process.argv[2];
    
    if (!siteUrl) {
        console.error('❌ Error: Please provide site URL');
        console.error('Usage: node deployment-health-check.js <site-url>');
        console.error('   or: SITE_URL=<site-url> node deployment-health-check.js');
        process.exit(1);
    }
    
    const checker = new HealthChecker(siteUrl);
    await checker.runAllTests();
}

if (require.main === module) {
    main().catch(console.error);
}

module.exports = HealthChecker;
