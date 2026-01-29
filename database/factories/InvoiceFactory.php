<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 10000);
        $tax = $subtotal * 0.18;
        $total = $subtotal + $tax;

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'invoice_number' => 'INV-' . fake()->unique()->numerify('####'),
            'invoice_date' => fake()->date(),
            'due_date' => fake()->date(),
            'invoice_type' => 'regular',
            'currency' => 'INR',
            'subtotal' => $subtotal,
            'igst' => $tax, // Assuming IGST for simplicity in factory
            'cgst' => 0,
            'sgst' => 0,
            'total' => $total,
            'status' => fake()->randomElement(['draft', 'sent', 'paid', 'overdue']),
            'notes' => fake()->sentence(),
        ];
    }
}
