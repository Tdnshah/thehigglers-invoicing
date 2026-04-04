<?php
/**
 * Migration created manually since artisan command was unavailable in the current shell environment.
 */
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
        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('status');
        });

        // Set the original (V0) quotations as active by default for existing data
        DB::table('quotations')->where('revision_number', 0)->update(['is_active' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
