<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A dated snapshot of where an invoice stood at each event in its life:
 * approval, and every payment after it. Each row keeps the three numbers the
 * client and the accountant care about (total, received to date, balance) so
 * the position at any past point can be read without replaying the ledger.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('revision_number');
            $table->string('event', 20);
            $table->decimal('total', 15, 2);
            $table->decimal('amount_received', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['invoice_id', 'revision_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_revisions');
    }
};
