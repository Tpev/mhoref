<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id();

            // Link to the referral
            $table->foreignId('referral_id')->constrained()->cascadeOnDelete();

            // Link to referral_progresses (plural)
            $table->foreignId('referral_progress_id')->nullable()->constrained('referral_progresses')->cascadeOnDelete();

            // File details
            $table->string('original_name');
            $table->string('path');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};
