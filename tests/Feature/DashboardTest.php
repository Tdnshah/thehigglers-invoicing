<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_screen_can_be_rendered(): void
    {
        // Need to create a company to pass CheckInstallation middleware
        Company::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_dashboard_displays_correct_stats(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $client = Client::factory()->create(['user_id' => $user->id]);

        // Create Invoices
        Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'draft',
            'total' => 100,
            'currency' => 'USD'
        ]);

        Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'sent',
            'total' => 200,
            'currency' => 'EUR'
        ]);

         Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'paid',
            'total' => 300,
            'currency' => 'INR'
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        
        // Check for specific stats in the view (using regex or text search)
        $response->assertSee('Draft Invoices');
        $response->assertSee('Sent Invoices');
        $response->assertSee('Paid Invoices');
        
        // Assert view data contains the correct counts
        $response->assertViewHas('statusCounts', function($counts) {
            return ($counts['draft'] ?? 0) === 1 && 
                   ($counts['sent'] ?? 0) === 1 && 
                   ($counts['paid'] ?? 0) === 1;
        });

        // Assert view data contains correct earnings (Paid INR)
        $response->assertViewHas('earnings', function($earnings) {
            $inr = $earnings->where('currency', 'INR')->first();
            return $inr && $inr->total_amount == 300;
        });
    }
}
