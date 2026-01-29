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
        Schema::table('users', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('email');
            $table->string('gst_number')->nullable()->after('company_name');
            $table->text('address')->nullable()->after('gst_number');
            $table->string('phone')->nullable()->after('address');
            $table->string('bank_name')->nullable()->after('phone');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_ifsc')->nullable()->after('bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'gst_number',
                'address',
                'phone',
                'bank_name',
                'bank_account_number',
                'bank_ifsc',
            ]);
        });
    }
};
