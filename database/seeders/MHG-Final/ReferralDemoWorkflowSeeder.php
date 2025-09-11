<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\WorkflowStep;
use Illuminate\Support\Str;

class ReferralDemoWorkflowSeeder extends Seeder
{
    public function run()
    {
        // Create Workflow
        $workflow = Workflow::create([
            'name' => 'Referral Intake and Processing',
            'description' => 'Handles referrals from intake to scheduling, including anesthesia review.',
        
        ]);

        /**
         * Stage 1: Intake and Triage
         */
        $stage1 = WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Intake and Triage',
            'order' => 1,
        ]);

        // Step 1.1: Patient Information Form
        WorkflowStep::create([
            'workflow_stage_id' => $stage1->id,
            'name' => 'Patient Intake Form',
            'type' => 'form',
            'order' => 1,
            'metadata' => [
                'fields' => [
                    ['name' => 'first_name', 'type' => 'text', 'label' => 'First Name', 'required' => true],
                    ['name' => 'last_name', 'type' => 'text', 'label' => 'Last Name', 'required' => true],
                    ['name' => 'date_of_birth', 'type' => 'date', 'label' => 'Date of Birth', 'required' => true],
                    ['name' => 'referral_reason', 'type' => 'textarea', 'label' => 'Reason for Referral', 'required' => true],
                ],
            ]
        ]);

        // Step 1.2: Triage Decision
        WorkflowStep::create([
            'workflow_stage_id' => $stage1->id,
            'name' => 'Triage Priority Decision',
            'type' => 'decision',
            'order' => 2,
            'metadata' => [
                'question' => 'Is this referral urgent?',
                'options' => ['Yes', 'No'],
                'on_true' => 'Proceed to Urgent Workflow',
                'on_false' => 'Proceed with Standard Workflow',
            ]
        ]);

        /**
         * Stage 2: Anesthesia and Review
         */
        $stage2 = WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Anesthesia Review',
            'order' => 2,
        ]);

        // Step 2.1: Anesthesia Requirement Checkbox
        WorkflowStep::create([
            'workflow_stage_id' => $stage2->id,
            'name' => 'Requires Anesthesia',
            'type' => 'checkbox',
            'order' => 1,
            'metadata' => [
                'label' => 'Mark if anesthesia review is required',
            ]
        ]);

        // Step 2.2: Upload Medical Clearance
        WorkflowStep::create([
            'workflow_stage_id' => $stage2->id,
            'name' => 'Upload Medical Clearance Documents',
            'type' => 'upload',
            'order' => 2,
            'metadata' => [
                'upload_label' => 'Medical Clearance',
                'max_files' => 3,
                'allowed_mimes' => ['pdf', 'jpg', 'png']
            ]
        ]);

        /**
         * Stage 3: Scheduling and Follow-up
         */
        $stage3 = WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Scheduling and Follow-up',
            'order' => 3,
        ]);

        // Step 3.1: Schedule Procedure
        WorkflowStep::create([
            'workflow_stage_id' => $stage3->id,
            'name' => 'Schedule Procedure',
            'type' => 'form',
            'order' => 1,
            'metadata' => [
                'fields' => [
                    ['name' => 'scheduled_date', 'type' => 'date', 'label' => 'Scheduled Date', 'required' => true],
                    ['name' => 'scheduled_time', 'type' => 'text', 'label' => 'Scheduled Time', 'required' => true],
                    ['name' => 'location', 'type' => 'text', 'label' => 'Procedure Location', 'required' => true],
                ],
            ]
        ]);

        // Step 3.2: Final Confirmation
        WorkflowStep::create([
            'workflow_stage_id' => $stage3->id,
            'name' => 'Final Confirmation Checkbox',
            'type' => 'checkbox',
            'order' => 2,
            'metadata' => [
                'label' => 'Mark as Confirmed',
            ]
        ]);
    }
}
