<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

// Create a test PDF-like file
$testFile = storage_path('app/test.txt');
file_put_contents($testFile, 'Test content for Cloudinary');

$result = Cloudinary::uploadFile($testFile, [
    'folder' => 'test_raw',
    'resource_type' => 'raw',
]);

echo "getSecurePath: " . $result->getSecurePath() . PHP_EOL;
echo "getPublicId: " . $result->getPublicId() . PHP_EOL;

// Check the response object
$response = $result->getResponse();
if ($response) {
    echo "Response secure_url: " . ($response['secure_url'] ?? 'N/A') . PHP_EOL;
    echo "Response url: " . ($response['url'] ?? 'N/A') . PHP_EOL;
    echo "Response resource_type: " . ($response['resource_type'] ?? 'N/A') . PHP_EOL;
}

unlink($testFile);
