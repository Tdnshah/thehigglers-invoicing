<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Internal notes on an invoice, mirroring quotation_notes, so the team can keep a
 * running record through the payment cycle instead of one overwritten text field.
 * The single legacy invoices.notes value becomes the first note in the thread.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note');
            $table->timestamps();
        });

        DB::table('invoices')
            ->whereNotNull('notes')
            ->where('notes', '!=', '')
            ->orderBy('id')
            ->each(function ($invoice) {
                DB::table('invoice_notes')->insert([
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                    'note' => $invoice->notes,
                    'created_at' => $invoice->created_at,
                    'updated_at' => $invoice->created_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_notes');
    }
};
