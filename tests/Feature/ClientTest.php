<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_admin_can_view_their_clients(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('clients.index'));

        $response->assertStatus(200);
        $response->assertSee($client->name);
    }

    public function test_company_admin_can_create_client(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $clientData = [
            'name' => 'Test Client',
            'email' => 'testclient@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'gst_number' => '22AAAAA0000A1Z5',
        ];

        $response = $this->actingAs($user)->post(route('clients.store'), $clientData);

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client',
            'email' => 'testclient@example.com',
            'user_id' => $user->id,
        ]);
    }

    public function test_company_admin_can_update_client(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'name' => 'Updated Client Name',
            'email' => 'updated@example.com',
            'phone' => '9876543210',
        ];

        $response = $this->actingAs($user)->put(route('clients.update', $client), $updateData);

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Updated Client Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_company_admin_can_delete_client(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('clients.destroy', $client));

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_user_cannot_view_clients_of_another_user(): void
    {
        $company1 = Company::factory()->create();
        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $client1 = Client::factory()->create(['user_id' => $user1->id]);

        $company2 = Company::factory()->create();
        $user2 = User::factory()->create(['company_id' => $company2->id]);

        $response = $this->actingAs($user2)->get(route('clients.index'));

        $response->assertStatus(200);
        $response->assertDontSee($client1->name);
    }

    public function test_user_cannot_update_client_of_another_user(): void
    {
        $company1 = Company::factory()->create();
        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $client1 = Client::factory()->create(['user_id' => $user1->id]);

        $company2 = Company::factory()->create();
        $user2 = User::factory()->create(['company_id' => $company2->id]);

        $updateData = ['name' => 'Hacked Name'];

        $response = $this->actingAs($user2)->put(route('clients.update', $client1), $updateData);

        $response->assertForbidden();
    }
}
