<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PsychologistProfile;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

echo "=== Listing Cloudinary assets under psychologist_profiles ===\n";

try {
    foreach (['raw', 'image'] as $rtype) {
        $res = Cloudinary::admin()->assets([
            'type' => 'upload',
            'resource_type' => $rtype,
            'prefix' => 'psychologist_profiles',
            'max_results' => 500,
        ]);

        echo "\nResource type: $rtype\n";
        if (isset($res['resources']) && is_array($res['resources']) && count($res['resources']) > 0) {
            foreach ($res['resources'] as $r) {
                $pub = $r['public_id'] ?? '(no public_id)';
                $url = $r['secure_url'] ?? ($r['url'] ?? '(no url)');
                echo " - $pub -> $url\n";
            }
        } else {
            echo " - no resources found\n";
        }
    }
} catch (\Exception $e) {
    echo "Error listing Cloudinary resources: " . $e->getMessage() . "\n";
}
