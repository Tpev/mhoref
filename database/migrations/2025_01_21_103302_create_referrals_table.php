<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            // Link to a workflow
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();

            // Optionally store a patient ID or other info:
            // $table->foreignId('patient_id')->nullable()->constrained();

            $table->string('status')->default('in_progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
