<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Company Admin
            $table->foreignId('client_id')->constrained()->onDelete('cascade'); // Assigned Client
            
            // Revisions implementation
            $table->foreignId('parent_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->integer('revision_number')->default(0);
            
            // Quotation details
            $table->string('quotation_number')->unique();
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            
            // Specific types matching Invoices
            $table->string('quotation_type')->default('regular'); // regular, export, interstate
            $table->string('place_of_supply', 2)->nullable(); // e.g., 27 for Maharashtra
            
            // Financial details
            $table->string('currency', 3)->default('INR');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('cgst', 15, 2)->default(0);
            $table->decimal('sgst', 15, 2)->default(0);
            $table->decimal('igst', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            // Status: draft, sent, approved, rejected
            $table->string('status')->default('draft');
            
            // Additional Info
            $table->text('client_notes')->nullable(); // Customer-facing notes
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete(); // Set when converted

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
