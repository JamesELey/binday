# BinDay Laravel Application - Deployment Package Creator
# Optimized for Fasthost File Manager upload to avoid FTP timeouts

$ErrorActionPreference = 'Stop'

Write-Host "🚀 Creating optimized deployment package for Fasthost..." -ForegroundColor Green

# 1. Clear all caches to reduce file count
Write-Host "📦 Clearing Laravel caches..." -ForegroundColor Yellow
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Install production dependencies only (reduces vendor size by ~50%)
Write-Host "🔧 Installing production dependencies..." -ForegroundColor Yellow
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Get project root
$projectRoot = Get-Location

# 4. Create temporary directory for deployment files
$tempDir = Join-Path $env:TEMP "binday-deploy-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null

Write-Host "📁 Copying files to temporary directory: $tempDir" -ForegroundColor Yellow

# 5. Copy files excluding problematic directories
$excludePatterns = @(
    '\.git',
    'node_modules',
    'storage\\framework\\cache\\data',
    'storage\\logs\\.*\.log',
    'deploy_ftp\.ps1',
    'root_index\.php',
    'DEPLOYMENT_GUIDE\.md',
    'DEVELOPMENT_NOTES\.md',
    'production\.env\.example',
    'create-deployment-package\.ps1',
    '\.env$'  # Don't include local .env
)

Get-ChildItem -Path $projectRoot -Recurse -File -Force | 
    Where-Object { 
        $file = $_.FullName
        $shouldExclude = $false
        foreach ($pattern in $excludePatterns) {
            if ($file -match $pattern) {
                $shouldExclude = $true
                break
            }
        }
        -not $shouldExclude
    } | 
    ForEach-Object {
        $sourcePath = $_.FullName
        $relativePath = $sourcePath.Substring($projectRoot.Path.Length + 1)
        $destPath = Join-Path $tempDir $relativePath
        
        # Create directory if it doesn't exist
        $destDir = Split-Path $destPath -Parent
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        Copy-Item $sourcePath $destPath
    }

# 6. Copy the production environment template as .env
$prodEnvTemplate = Join-Path $projectRoot "production.env.example"
if (Test-Path $prodEnvTemplate) {
    Write-Host "📝 Adding production .env template..." -ForegroundColor Yellow
    Copy-Item $prodEnvTemplate (Join-Path $tempDir ".env")
} else {
    Write-Host "⚠️  Warning: production.env.example not found. You'll need to create .env manually." -ForegroundColor Red
}

# 7. Create the deployment zip
$zipPath = Join-Path $projectRoot "binday-deploy.zip"
Write-Host "🗜️  Creating deployment package: binday-deploy.zip" -ForegroundColor Yellow

if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

# Use .NET compression for better compatibility
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($tempDir, $zipPath)

# 8. Clean up temporary directory
Remove-Item $tempDir -Recurse -Force

# 9. Get file size
$zipSize = (Get-Item $zipPath).Length
$zipSizeMB = [math]::Round($zipSize / 1MB, 2)

Write-Host ""
Write-Host "✅ Deployment package created successfully!" -ForegroundColor Green
Write-Host "📦 Package: binday-deploy.zip ($zipSizeMB MB)" -ForegroundColor Cyan
Write-Host ""
Write-Host "📋 Next steps:" -ForegroundColor Yellow
Write-Host "1. Login to your Fasthost control panel" -ForegroundColor White
Write-Host "2. Go to File Manager" -ForegroundColor White
Write-Host "3. Navigate to /htdocs/" -ForegroundColor White
Write-Host "4. Upload binday-deploy.zip" -ForegroundColor White
Write-Host "5. Extract the zip file" -ForegroundColor White
Write-Host "6. Rename extracted folder to 'binday'" -ForegroundColor White
Write-Host "7. Upload root_index.php as /htdocs/index.php" -ForegroundColor White
Write-Host "8. Update .env file with your database credentials" -ForegroundColor White
Write-Host ""
Write-Host "🔗 Your site will be available at: https://thebinday.co.uk" -ForegroundColor Green

# 10. Restore development dependencies
Write-Host ""
Write-Host "🔄 Restoring development dependencies..." -ForegroundColor Yellow
composer install

Write-Host "🎉 Ready for deployment!" -ForegroundColor Green
