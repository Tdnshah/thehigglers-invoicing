<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_admin_can_record_payment_for_their_invoice(): void
    {
        // Create a company and its admin user
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        // Create a client and invoice belonging to this user
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'total' => 1000.00,
            'status' => 'sent',
        ]);

        $paymentData = [
            'amount' => 500.00,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'transaction_reference' => 'TXN123456',
            'notes' => 'Partial payment',
        ];

        $response = $this->actingAs($user)
            ->post(route('payments.store', $invoice), $paymentData);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 500.00,
            'transaction_reference' => 'TXN123456',
        ]);
        
        // Verify invoice status is NOT paid yet (partial payment)
        $this->assertEquals('sent', $invoice->fresh()->status);
    }

    public function test_invoice_status_updates_to_paid_when_full_payment_is_recorded(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'total' => 1000.00,
            'status' => 'sent',
        ]);

        $paymentData = [
            'amount' => 1000.00,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Cash',
            'transaction_reference' => 'TXN999999', // Added required field
        ];

        // Ensure the factory created total is exactly 1000.00 and no floating point weirdness from factory randoms
        // Although we hardcoded total=1000.00 above, let's just proceed.

        $this->actingAs($user)
            ->post(route('payments.store', $invoice), $paymentData);

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_cannot_record_payment_exceeding_remaining_balance(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'total' => 1000.00,
        ]);

        // Record first partial payment
        $invoice->payments()->create([
            'client_id' => $client->id,
            'amount' => 600.00,
            'payment_date' => now(),
            'transaction_reference' => 'TXN_OLD',
        ]);

        // Try to record another payment greater than remaining 400
        $paymentData = [
            'amount' => 500.00,
            'payment_date' => now()->format('Y-m-d'),
            'transaction_reference' => 'TXN_FAIL',
        ];

        $response = $this->actingAs($user)
            ->post(route('payments.store', $invoice), $paymentData);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('payments', 1); // Only the initial payment exists
    }

    public function test_unauthorized_user_cannot_record_payment(): void
    {
        $company = Company::factory()->create();
        $owner = User::factory()->create(['company_id' => $company->id]);
        $otherUser = User::factory()->create(); // Not associated with the company

        $client = Client::factory()->create(['user_id' => $owner->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $owner->id,
            'client_id' => $client->id,
        ]);

        $paymentData = [
            'amount' => 100.00,
            'payment_date' => now()->format('Y-m-d'),
            'transaction_reference' => 'TXN_AUTH',
        ];

        $response = $this->actingAs($otherUser)
            ->post(route('payments.store', $invoice), $paymentData);

        $response->assertForbidden();
        $this->assertDatabaseCount('payments', 0);
    }
}
