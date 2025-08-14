# Fix PHP 8.1 Compatibility Issues
Write-Host "🔧 Fixing PHP 8.1 Compatibility Issues..." -ForegroundColor Green

# Remove vendor directory
if (Test-Path "vendor") {
    Remove-Item -Recurse -Force "vendor"
    Write-Host "✅ Vendor directory removed" -ForegroundColor Green
}

# Remove composer.lock
if (Test-Path "composer.lock") {
    Remove-Item -Force "composer.lock"
    Write-Host "✅ Composer.lock removed" -ForegroundColor Green
}

# Install dependencies
Write-Host "📥 Installing fresh dependencies..." -ForegroundColor Yellow
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

Write-Host "✅ Done! You can now run: php artisan serve" -ForegroundColor Green
