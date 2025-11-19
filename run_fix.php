<?php
// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Making borrow_date nullable...\n";
    DB::statement('ALTER TABLE borrowings MODIFY borrow_date DATE NULL');
    echo "✓ borrow_date is now nullable\n";
    
    echo "Making due_date nullable...\n";
    DB::statement('ALTER TABLE borrowings MODIFY due_date DATE NULL');
    echo "✓ due_date is now nullable\n";
    
    echo "\n✓ All done! You can now create bookings.\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
