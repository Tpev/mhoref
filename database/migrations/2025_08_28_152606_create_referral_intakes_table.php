<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referral_intakes', function (Blueprint $table) {
            $table->id();

            // Optional linking context (keep nullable if not always available)
            $table->foreignId('referral_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workflow_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workflow_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();

            // Who submitted
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();

            // Patient
            $table->string('patient_first_name', 100);
            $table->string('patient_last_name', 100);
            $table->date('patient_dob');
            $table->string('patient_phone', 30);

            // Referring PCP
            $table->string('pcp_first_name', 100);
            $table->string('pcp_last_name', 100);
            $table->string('pcp_npi', 20);

            // Clinical
            $table->text('last_visit_note')->nullable();
            $table->text('diag_for_referral'); // diagnosis text or code
            $table->string('smoking_status', 20); // never|former|current
            $table->decimal('bmi', 5, 2)->nullable();
            $table->text('medication_list')->nullable(); // free text, one per line

            // Files
            $table->json('xray_files')->nullable(); // array of file objects {path, original, mime, size}
            $table->string('surgery_report_path')->nullable(); // single file path
            $table->text('implant_info')->nullable();

            // Surgery flag
            $table->boolean('prior_joint_surgery')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_intakes');
    }
};
