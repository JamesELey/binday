# BinDay Local Server Starter
# This script starts the local development server using PHP's built-in server
# instead of Laravel's serve command to avoid the "Undefined array key 1" error

Write-Host "🚀 Starting BinDay Local Development Server..." -ForegroundColor Green
Write-Host ""

# Kill any existing PHP processes
Write-Host "🔄 Stopping any existing PHP processes..." -ForegroundColor Yellow
Get-Process -Name "php" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue

# Clear Laravel caches
Write-Host "🧹 Clearing Laravel caches..." -ForegroundColor Yellow
php artisan config:clear | Out-Null
php artisan route:clear | Out-Null
php artisan view:clear | Out-Null

# Start the server using PHP's built-in server
Write-Host "🌐 Starting server at http://127.0.0.1:8000..." -ForegroundColor Green
Write-Host ""
Write-Host "📋 Available URLs:" -ForegroundColor Cyan
Write-Host "   🏠 Home:        http://127.0.0.1:8000/" -ForegroundColor White
Write-Host "   🗺️  Map:         http://127.0.0.1:8000/bins/map" -ForegroundColor White
Write-Host "   ⚙️  Admin Seed:  http://127.0.0.1:8000/admin/seed" -ForegroundColor White
Write-Host "   📅 Collections: http://127.0.0.1:8000/collections" -ForegroundColor White
Write-Host ""
Write-Host "⚠️  Press Ctrl+C to stop the server" -ForegroundColor Red
Write-Host ""

# Start server (this will block until Ctrl+C)
php -S 127.0.0.1:8000 -t public
