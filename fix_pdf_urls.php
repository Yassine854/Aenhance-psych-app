<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$profiles = \App\Models\PsychologistProfile::all();

foreach ($profiles as $p) {
    echo "Profile ID: {$p->id}\n";
    echo "  Diploma: {$p->diploma}\n";
    echo "  CIN: {$p->cin}\n\n";
}
