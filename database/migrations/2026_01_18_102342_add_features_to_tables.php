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
        // 1. Add Custom Fields to Companies and Clients
        Schema::table('companies', function (Blueprint $table) {
            $table->json('custom_fields')->nullable(); // For LUT, PAN, etc.
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('currency')->default('INR'); // Currency preference
            $table->json('custom_fields')->nullable(); // For specific client details
        });

        // 2. Add Currency to Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('currency')->default('INR');
            $table->decimal('exchange_rate', 15, 6)->default(1.000000); // For converting to base currency if needed
        });

        // 3. Add HSN and Tax Rate to Invoice Items
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('hsn_code')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(18.00); // Specific tax rate per item (0, 5, 12, 18, 28)
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['currency', 'custom_fields']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['hsn_code', 'tax_rate']);
        });
    }
};
