<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_user_cannot_view_invoices_of_other_clients()
    {
        // 1. Create a Company and a Client belonging to that Company
        $company = Company::factory()->create();
        $adminUser = User::factory()->create(['company_id' => $company->id]);
        
        $clientA = Client::factory()->create(['user_id' => $adminUser->id]);
        $clientB = Client::factory()->create(['user_id' => $adminUser->id]);

        // 2. Create invoices for both clients
        $invoiceA = Invoice::factory()->create([
            'user_id' => $adminUser->id,
            'client_id' => $clientA->id
        ]);
        
        $invoiceB = Invoice::factory()->create([
            'user_id' => $adminUser->id,
            'client_id' => $clientB->id
        ]);

        // 3. Create a User for Client A
        $clientUserA = User::factory()->create([
            'client_id' => $clientA->id,
            'company_id' => null, // Explicitly not a company admin
        ]);

        // 4. Authenticate as Client A
        $response = $this->actingAs($clientUserA)->get(route('invoices.index'));

        // 5. Assert: See Invoice A, Do NOT see Invoice B
        $response->assertStatus(200);
        $response->assertSee($invoiceA->invoice_number);
        $response->assertDontSee($invoiceB->invoice_number);
    }

    public function test_client_user_cannot_access_create_edit_destroy_routes()
    {
        $company = Company::factory()->create();
        $adminUser = User::factory()->create(['company_id' => $company->id]);
        $client = Client::factory()->create(['user_id' => $adminUser->id]);
        
        $invoice = Invoice::factory()->create([
            'user_id' => $adminUser->id,
            'client_id' => $client->id
        ]);

        $clientUser = User::factory()->create([
            'client_id' => $client->id,
            'company_id' => null,
        ]);

        // Attempt Create
        $response = $this->actingAs($clientUser)->get(route('invoices.create'));
        $response->assertStatus(403); // Forbidden

        // Attempt Store
        $response = $this->actingAs($clientUser)->post(route('invoices.store'), []);
        $response->assertStatus(403);

        // Attempt Edit
        $response = $this->actingAs($clientUser)->get(route('invoices.edit', $invoice));
        $response->assertStatus(403);

        // Attempt Update
        $response = $this->actingAs($clientUser)->put(route('invoices.update', $invoice), []);
        $response->assertStatus(403);

        // Attempt Destroy
        $response = $this->actingAs($clientUser)->delete(route('invoices.destroy', $invoice));
        $response->assertStatus(403);
    }

    public function test_client_user_cannot_view_invoice_not_belonging_to_them()
    {
        $company = Company::factory()->create();
        $adminUser = User::factory()->create(['company_id' => $company->id]);
        
        $clientA = Client::factory()->create(['user_id' => $adminUser->id]);
        $clientB = Client::factory()->create(['user_id' => $adminUser->id]);

        $invoiceB = Invoice::factory()->create([
            'user_id' => $adminUser->id,
            'client_id' => $clientB->id
        ]);

        $clientUserA = User::factory()->create([
            'client_id' => $clientA->id,
        ]);

        // Client A tries to view Client B's invoice
        $response = $this->actingAs($clientUserA)->get(route('invoices.show', $invoiceB));
        $response->assertStatus(403);
        
        // Client A tries to print/pdf Client B's invoice
        $response = $this->actingAs($clientUserA)->get(route('invoices.print', $invoiceB));
        $response->assertStatus(403);
    }
}
