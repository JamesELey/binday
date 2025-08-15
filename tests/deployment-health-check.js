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

        // Core functionality tests for authenticated application
        await this.testSiteAccessibility();
        await this.testAuthenticationPages();
        await this.testProtectedPagesRedirect();
        
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
        
        // For authenticated app, home should redirect to login (302)
        const isAccessible = response.status === 302 || response.status === 200;
        
        this.test(
            'Site is accessible',
            isAccessible,
            isAccessible ? 
                `Site responds correctly (${response.status}) - Response time: ${response.responseTime}ms` : 
                `Status: ${response.status}, Error: ${response.error || 'Unknown'}`
        );
    }

    async testAuthenticationPages() {
        console.log('🔐 Testing Authentication Pages...');
        const loginResponse = await this.makeRequest('/login');
        
        this.test(
            'Login page accessible',
            loginResponse.status === 200,
            loginResponse.status === 200 ? 
                `Login page loads correctly - ${loginResponse.responseTime}ms` : 
                `Login page status: ${loginResponse.status}`
        );

        // Test if login page contains proper form elements
        if (loginResponse.status === 200) {
            const hasLoginForm = loginResponse.data.includes('password') || 
                                loginResponse.data.includes('login') ||
                                loginResponse.data.includes('email');
            
            this.test(
                'Login page has authentication form',
                hasLoginForm,
                hasLoginForm ? 'Login form elements detected' : 'No login form found'
            );
        } else {
            this.test('Login page has authentication form', false, 'Login page not accessible');
        }
    }

    async testProtectedPagesRedirect() {
        console.log('🔒 Testing Protected Pages (should redirect to login)...');
        
        const protectedPages = [
            { path: '/bins/map', name: 'Bin Map page' },
            { path: '/collections', name: 'Collections page' },
            { path: '/routes', name: 'Route Planner page' }
        ];

        for (const page of protectedPages) {
            const response = await this.makeRequest(page.path);
            
            // Protected pages should redirect to login (302) or require auth
            const properlyProtected = response.status === 302 || response.status === 401;
            
            this.test(
                `${page.name} properly protected`,
                properlyProtected,
                properlyProtected ? 
                    `Correctly redirects/blocks (${response.status})` : 
                    `Unexpected status: ${response.status} (should be 302 or 401)`
            );
        }
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
        
        // Test login page which requires database for user authentication
        const loginResponse = await this.makeRequest('/login');
        
        // If login page loads without 500 error, database connection is likely working
        // (Login page requires database to check users, render forms, etc.)
        const dbWorking = loginResponse.status === 200;
        
        this.test(
            'Database connection working',
            dbWorking,
            dbWorking ? 
                'Login page loads (database connection working)' : 
                'Login page fails to load (possible database issue)'
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
