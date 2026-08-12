<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Company level document settings: the numbering formats that replace the old
 * random suffixes, and the global terms every document can inherit.
 *
 * Clients gain a short code so a numbering format can address them, e.g.
 * INV-{CLIENT}-{YYYY}-{MM}-{SEQ:2}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('invoice_number_format')->nullable()->after('custom_fields');
            $table->string('quotation_number_format')->nullable()->after('invoice_number_format');
            $table->text('terms_conditions')->nullable()->after('quotation_number_format');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('code', 12)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['invoice_number_format', 'quotation_number_format', 'terms_conditions']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
