<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$profiles = \App\Models\PsychologistProfile::all();

foreach ($profiles as $p) {
    $updated = false;
    
    // Fix diploma URL: /image/upload/ -> /raw/upload/ for PDFs
    if ($p->diploma && str_contains($p->diploma, '/image/upload/') && str_ends_with($p->diploma, '.pdf')) {
        $p->diploma = str_replace('/image/upload/', '/raw/upload/', $p->diploma);
        $updated = true;
        echo "Fixed diploma URL for profile {$p->id}\n";
    }
    
    // Fix CIN URL: /image/upload/ -> /raw/upload/ for PDFs
    if ($p->cin && str_contains($p->cin, '/image/upload/') && str_ends_with($p->cin, '.pdf')) {
        $p->cin = str_replace('/image/upload/', '/raw/upload/', $p->cin);
        $updated = true;
        echo "Fixed CIN URL for profile {$p->id}\n";
    }
    
    if ($updated) {
        $p->save();
    }
    
    echo "Profile ID: {$p->id}\n";
    echo "  Diploma: {$p->diploma}\n";
    echo "  CIN: {$p->cin}\n\n";
}
