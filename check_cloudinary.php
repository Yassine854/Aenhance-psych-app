<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PsychologistProfile;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

echo "=== Checking Database Records ===\n";

$profiles = PsychologistProfile::whereNotNull('diploma')
    ->orWhereNotNull('cin')
    ->get(['id', 'diploma', 'cin']);

foreach ($profiles as $profile) {
    echo "\nProfile ID: {$profile->id}\n";
    echo "Diploma URL: {$profile->diploma}\n";
    echo "CIN URL: {$profile->cin}\n";
}

echo "\n=== Checking Cloudinary Resources ===\n";

try {
    // List resources in the psychologist_profiles folder
    $result = Cloudinary::admin()->assets([
        'type' => 'upload',
        'prefix' => 'psychologist_profiles',
        'max_results' => 50,
        'resource_type' => 'raw'
    ]);
    
    echo "Raw files in psychologist_profiles:\n";
    if (isset($result['resources'])) {
        foreach ($result['resources'] as $resource) {
            echo "  - {$resource['public_id']} ({$resource['secure_url']})\n";
        }
    } else {
        echo "  No raw resources found\n";
    }
    
    // Also check image resources
    $imageResult = Cloudinary::admin()->assets([
        'type' => 'upload',
        'prefix' => 'psychologist_profiles',
        'max_results' => 50,
        'resource_type' => 'image'
    ]);
    
    echo "\nImage files in psychologist_profiles:\n";
    if (isset($imageResult['resources'])) {
        foreach ($imageResult['resources'] as $resource) {
            echo "  - {$resource['public_id']} ({$resource['secure_url']})\n";
        }
    } else {
        echo "  No image resources found\n";
    }
    
} catch (\Exception $e) {
    echo "Error listing Cloudinary resources: " . $e->getMessage() . "\n";
}

echo "\n=== Cloudinary Config ===\n";
echo "Cloud Name: " . config('cloudinary.cloud_name') . "\n";
