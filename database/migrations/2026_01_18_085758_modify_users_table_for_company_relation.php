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
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            
            // Remove the individual company fields we added earlier as they now belong to Company model
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');

            // Add back the columns if rolling back
            $table->string('company_name')->nullable();
            $table->string('gst_number')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();
        });
    }
};
