<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * An invoice now has to be approved before any payment can be recorded against
 * it, and it can carry its own terms and bank details on top of the company
 * level ones.
 *
 * The status column is a MariaDB enum, so the new value goes in with a raw
 * MODIFY; there is no portable Schema call for it.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft','approved','sent','paid','overdue') NOT NULL DEFAULT 'draft'");

        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->string('terms_mode', 10)->default('global')->after('notes');
            $table->text('terms_conditions')->nullable()->after('terms_mode');
            $table->string('bank_mode', 10)->default('global')->after('terms_conditions');
            $table->json('bank_details')->nullable()->after('bank_mode');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approved_at', 'terms_mode', 'terms_conditions', 'bank_mode', 'bank_details']);
        });

        DB::statement("UPDATE invoices SET status = 'draft' WHERE status = 'approved'");
        DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft','sent','paid','overdue') NOT NULL DEFAULT 'draft'");
    }
};
