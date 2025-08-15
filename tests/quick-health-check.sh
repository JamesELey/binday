#!/bin/bash

# 🧪 Quick Health Check for BinDay Deployment
# Simple shell script version for environments without Node.js

set -e

SITE_URL="${1:-$SITE_URL}"
if [ -z "$SITE_URL" ]; then
    echo "❌ Error: Please provide site URL"
    echo "Usage: ./quick-health-check.sh <site-url>"
    echo "   or: SITE_URL=<site-url> ./quick-health-check.sh"
    exit 1
fi

echo "🧪 BinDay Quick Health Check"
echo "🎯 Target: $SITE_URL"
echo ""

# Remove trailing slash
SITE_URL="${SITE_URL%/}"

TESTS_PASSED=0
TOTAL_TESTS=0

# Function to test URL
test_url() {
    local url="$1"
    local name="$2"
    local expected_status="${3:-200}"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    echo "🔍 Testing: $name"
    
    # Use curl to test the URL
    if command -v curl >/dev/null 2>&1; then
        response=$(curl -s -o /dev/null -w "%{http_code}|%{time_total}" --max-time 10 "$url" 2>/dev/null || echo "000|0")
        status_code=$(echo "$response" | cut -d'|' -f1)
        response_time=$(echo "$response" | cut -d'|' -f2)
        
        if [ "$status_code" = "$expected_status" ]; then
            echo "   ✅ $name (${status_code}, ${response_time}s)"
            TESTS_PASSED=$((TESTS_PASSED + 1))
        else
            echo "   ❌ $name (Status: $status_code)"
        fi
    else
        # Fallback to wget if curl not available
        if wget --spider --quiet --timeout=10 "$url" 2>/dev/null; then
            echo "   ✅ $name (accessible)"
            TESTS_PASSED=$((TESTS_PASSED + 1))
        else
            echo "   ❌ $name (not accessible)"
        fi
    fi
    echo ""
}

# Run tests for authenticated application
echo "🌐 Testing Site Accessibility..."
test_url "$SITE_URL/" "Home page" "302"  # Should redirect to login

echo "🔐 Testing Authentication Pages..."
test_url "$SITE_URL/login" "Login page" "200"  # Login should be accessible

echo "🗺️ Testing Protected Pages (should redirect to login)..."
test_url "$SITE_URL/bins/map" "Bin Map page" "302"  # Should redirect to login
test_url "$SITE_URL/collections" "Collections page" "302"  # Should redirect to login
test_url "$SITE_URL/routes" "Route Planner page" "302"  # Should redirect to login

# Test for common Laravel assets (might 404, that's ok)
echo "📦 Testing Assets..."
if command -v curl >/dev/null 2>&1; then
    asset_response=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "$SITE_URL/css/app.css" 2>/dev/null || echo "000")
    if [ "$asset_response" = "200" ]; then
        echo "   ✅ CSS assets found"
    else
        echo "   ℹ️  CSS assets (may be inline or CDN-based)"
    fi
else
    echo "   ℹ️  Asset testing skipped (curl not available)"
fi
echo ""

# Results
echo "=" | tr '\n' ' ' | xargs printf '%.0s' {1..50}; echo ""
echo "🧪 HEALTH CHECK RESULTS"
echo "=" | tr '\n' ' ' | xargs printf '%.0s' {1..50}; echo ""
echo "✅ Passed: $TESTS_PASSED/$TOTAL_TESTS tests"
echo "❌ Failed: $((TOTAL_TESTS - TESTS_PASSED))/$TOTAL_TESTS tests"
echo ""

if [ "$TESTS_PASSED" = "$TOTAL_TESTS" ]; then
    echo "🎉 ALL TESTS PASSED! Deployment is healthy! 🚀"
    echo "🌐 Application URL: $SITE_URL"
    exit 0
else
    echo "⚠️  Some tests failed. Please check the deployment."
    echo "🌐 Application URL: $SITE_URL"
    exit 1
fi
