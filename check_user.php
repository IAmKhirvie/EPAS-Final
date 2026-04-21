<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'Juswa@gmail.com')->first();
if ($user) {
    echo "User found: " . $user->email . PHP_EOL;
    echo "Role: " . $user->role . PHP_EOL;
    echo "Stat: " . $user->stat . PHP_EOL;
} else {
    echo "User not found" . PHP_EOL;
}
