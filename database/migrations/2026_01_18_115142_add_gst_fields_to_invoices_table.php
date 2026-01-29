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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_type')->default('regular')->after('status'); // regular, export, interstate
            $table->string('place_of_supply')->nullable()->after('invoice_type'); // State Code e.g., 27
            $table->string('lut_number')->nullable()->after('place_of_supply'); // For export invoices
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_type', 'place_of_supply', 'lut_number']);
        });
    }
};
