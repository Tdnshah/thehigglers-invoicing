<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Export quotations are quoted under a LUT, and the invoice cloned from an
 * approved quotation has to carry that same LUT. The form already collected it
 * but there was nowhere to store it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('lut_number')->nullable()->after('place_of_supply');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('lut_number');
        });
    }
};
