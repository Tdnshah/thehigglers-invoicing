<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A company banks in more than one place: a domestic current account for INR
 * invoices and a separate account, usually with SWIFT and IBAN, for exports.
 * Each document picks the account its client should pay into.
 *
 * The single set of bank columns on companies becomes the first account, so
 * nothing in flight loses its payment details.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc', 20)->nullable();
            $table->string('swift', 20)->nullable();
            $table->string('iban', 40)->nullable();
            $table->string('branch')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('bank_mode')->constrained('bank_accounts')->nullOnDelete();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('bank_mode')->constrained('bank_accounts')->nullOnDelete();
        });

        // Carry the existing company block over as the default account.
        DB::table('companies')
            ->whereNotNull('bank_account_number')
            ->where('bank_account_number', '!=', '')
            ->orderBy('id')
            ->each(function ($company) {
                DB::table('bank_accounts')->insert([
                    'company_id' => $company->id,
                    'label' => trim(($company->bank_name ?: 'Primary') . ' account'),
                    'bank_name' => $company->bank_name,
                    'account_number' => $company->bank_account_number,
                    'ifsc' => $company->bank_ifsc,
                    'is_default' => true,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        // "global" meant the company block; it now means the selected account.
        DB::table('invoices')->where('bank_mode', 'global')->update(['bank_mode' => 'account']);
        DB::table('quotations')->where('bank_mode', 'global')->update(['bank_mode' => 'account']);
    }

    public function down(): void
    {
        DB::table('invoices')->where('bank_mode', 'account')->update(['bank_mode' => 'global']);
        DB::table('quotations')->where('bank_mode', 'account')->update(['bank_mode' => 'global']);

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
        });

        Schema::dropIfExists('bank_accounts');
    }
};
