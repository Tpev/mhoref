<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_progresses', function (Blueprint $table) {
            $table->id();

            // Link progress to a specific referral
            $table->foreignId('referral_id')->constrained()->cascadeOnDelete();

            // Which step is this referring to?
            $table->foreignId('workflow_step_id')->constrained()->cascadeOnDelete();

            // Who completed it, if relevant
            $table->foreignId('completed_by')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Timestamps for step completion
            $table->timestamp('completed_at')->nullable();

            // Could be 'completed', 'in_progress', 'failed', etc.
            $table->string('status')->default('completed');

            // Optional notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_progresses');
    }
};
