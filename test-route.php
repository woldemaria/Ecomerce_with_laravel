<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::first();
if (!$user) {
    $user = App\Models\User::factory()->create();
}
auth()->login($user);

$request = Illuminate\Http\Request::create('/products/bulk-manage', 'GET');
$response = $kernel->handle($request);
echo "Status: " . $response->status() . "\n";
if ($response->exception) {
    echo $response->exception->getMessage() . "\n";
    echo $response->exception->getTraceAsString() . "\n";
}
