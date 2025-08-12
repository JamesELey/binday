<?php
// Simple deploy extractor for shared hosting
// WARNING: Protect with a strong token via query string

$token = $_GET['token'] ?? '';
$expected = getenv('DEPLOY_TOKEN') ?: 'use-secrets';
// Allow passing token via script content if env not set (for Actions only)
if ($expected === 'use-secrets') {
    // Fallback to GitHub Actions secret passed as query if no env
    $expected = $_GET['expected'] ?? '';
}

if (!hash_equals((string)$expected, (string)$token)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Base dir is project root (one level above public)
$baseDir = dirname(__DIR__);
$zipPath = $baseDir . '/deploy.zip';

if (!file_exists($zipPath)) {
    http_response_code(404);
    echo 'deploy.zip not found';
    exit;
}

$zip = new ZipArchive();
if ($zip->open($zipPath) !== true) {
    http_response_code(500);
    echo 'Failed to open zip';
    exit;
}
if (!$zip->extractTo($baseDir)) {
    $zip->close();
    http_response_code(500);
    echo 'Failed to extract zip';
    exit;
}
$zip->close();

@unlink($zipPath);
// Remove this script after deploy
@unlink(__FILE__);

echo 'OK';


