<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            // Each step is tied to a stage
            $table->foreignId('workflow_stage_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('type')->default('action'); // e.g., 'action', 'decision', 'fork', etc.

            // Optionally link to another step (for linear workflows or branching)
            $table->foreignId('next_step_id')->nullable()
                  ->constrained('workflow_steps')
                  ->nullOnDelete();

            $table->json('metadata')->nullable(); // Additional config or data
            $table->integer('order')->default(0);


            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
