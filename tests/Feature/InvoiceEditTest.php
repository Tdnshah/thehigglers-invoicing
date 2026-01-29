<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_edit_page_loads_correctly()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $client = Client::factory()->create(['user_id' => $user->id]);
        
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);
        
        // Create an item with specific tax rate to verify handling
        $invoice->items()->create([
            'description' => 'Test Item',
            'quantity' => 1,
            'unit_price' => 100,
            'tax_rate' => 18.00, // Decimal in DB
            'amount' => 100
        ]);

        $response = $this->actingAs($user)->get(route('invoices.edit', $invoice));

        $response->assertStatus(200);
        $response->assertViewHas('invoiceItems');
        
        // Verify the data passed to the view has the integer casted tax_rate
        $viewData = $response->viewData('invoiceItems');
        $this->assertEquals(18, $viewData[0]['tax_rate']); // Should be int 18, not "18.00"
    }
}
