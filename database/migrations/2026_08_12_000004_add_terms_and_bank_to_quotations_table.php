<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quotations already had a terms_conditions body; they gain the same
 * global / custom / both switch and per document bank details as invoices.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('terms_mode', 10)->default('global')->after('terms_conditions');
            $table->string('bank_mode', 10)->default('global')->after('terms_mode');
            $table->json('bank_details')->nullable()->after('bank_mode');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['terms_mode', 'bank_mode', 'bank_details']);
        });
    }
};
