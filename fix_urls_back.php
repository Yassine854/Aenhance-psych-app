<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PsychologistProfile;

$profile = PsychologistProfile::find(10);

if ($profile) {
    // Fix URLs back to image/upload (that's what Cloudinary actually has)
    if ($profile->diploma) {
        $profile->diploma = str_replace('/raw/upload/', '/image/upload/', $profile->diploma);
    }
    if ($profile->cin) {
        $profile->cin = str_replace('/raw/upload/', '/image/upload/', $profile->cin);
    }
    $profile->save();
    
    echo "Fixed URLs back to image/upload\n";
    echo "Diploma: {$profile->diploma}\n";
    echo "CIN: {$profile->cin}\n";
} else {
    echo "Profile not found\n";
}
