<?php

// database/migrations/xxxx_create_signature_requests_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /* ───── the request “header” ───── */
        Schema::create('signature_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_step_id');        // the *request* step
            $table->foreignId('assigned_user_id')         // signer
                  ->constrained('users')->cascadeOnDelete();
            $table->foreignId('requested_by')             // creator
                  ->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending/completed
            $table->timestamps();
        });

        /* ───── each doc inside that request ───── */
        Schema::create('signature_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signature_request_id')
                  ->constrained()->cascadeOnDelete();
            $table->string('orig_name');
            $table->string('orig_path');
            $table->string('signature_png_path')->nullable();
            $table->string('signed_pdf_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_documents');
        Schema::dropIfExists('signature_requests');
    }
};
