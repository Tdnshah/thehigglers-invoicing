<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One running counter per numbering scope.
 *
 * The scope is the rendered number with the sequence token blanked out, so the
 * format itself decides what the series resets on: a format carrying {FY}
 * restarts every financial year, one carrying {CLIENT} runs per client, one
 * carrying neither runs forever. Counters are incremented under a row lock so
 * two invoices created at the same moment cannot take the same serial.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 20);
            $table->string('scope_key');
            $table->unsignedInteger('next_number')->default(1);
            $table->timestamps();

            $table->unique(['company_id', 'document_type', 'scope_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
