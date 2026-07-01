<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_pages_load(): void
    {
        $user = User::factory()->create();

        $routes = [
            '/dashboard',
            '/products',
            '/products/create',
            '/products/bulk-manage',
            '/inventory',
            '/inventory/adjust',
            '/inventory/low-stock',
            '/reports/performance',
            '/reports/search',
            '/settings',
            '/profile',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            if ($response->status() !== 200) {
                echo "\nRoute $route failed with status: " . $response->status() . "\n";
                if ($response->status() === 500) {
                    echo $response->exception->getMessage() . "\n" . $response->exception->getTraceAsString() . "\n";
                }
            }
            $this->assertEquals(200, $response->status(), "Route $route failed");
        }
    }
}
