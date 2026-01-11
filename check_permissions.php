<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::find(1);
if ($user) {
    echo 'User: ' . $user->email . PHP_EOL;
    echo 'Roles: ' . $user->roles->pluck('name')->join(', ') . PHP_EOL;
    echo 'Permissions:' . PHP_EOL;
    foreach(['property.list', 'property-type.list', 'units.list', 'rooms.list', 'beds.list', 'amenities.list'] as $perm) {
        echo $perm . ': ' . ($user->can($perm) ? 'YES' : 'NO') . PHP_EOL;
    }
} else {
    echo 'User not found' . PHP_EOL;
}

