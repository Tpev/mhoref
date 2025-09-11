<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkflowStage;
use App\Models\WorkflowStep;

class WorkflowStepSeeder extends Seeder
{
    public function run()
    {
        // Retrieve existing workflow stage by name
        $initialReviewStage = WorkflowStage::where('name', 'Prior to discharge')->first();

        if (!$initialReviewStage) {
            $this->command->error('The workflow stage \"Prior to discharge\" was not found.');
            return;
        }

        // Step 1: Collect patient discharge information
        WorkflowStep::create([
            'workflow_stage_id' => $initialReviewStage->id,
            'name'              => 'Patient Discharge Information',
            'type'              => 'form',
            'order'             => 1,
            'metadata'          => [
                'fields' => [
                    ['name' => 'first_name', 'type' => 'text', 'label' => 'Patient First Name', 'required' => true],
                    ['name' => 'last_name', 'type' => 'text', 'label' => 'Patient Last Name', 'required' => true],
                    ['name' => 'date_of_discharge', 'type' => 'date', 'label' => 'Date of Discharge', 'required' => true],
                    ['name' => 'destination', 'type' => 'text', 'label' => 'Destination', 'required' => true],
                    ['name' => 'home_health_agency', 'type' => 'text', 'label' => 'Home Health Agency', 'required' => true],
                    ['name' => 'additional_notes', 'type' => 'textarea', 'label' => 'Additional Notes', 'required' => false],
                ],
            ],
        ]);

        // Step 2: Decision step to determine if patient needs DME
        $decisionStep = WorkflowStep::create([
            'workflow_stage_id' => $initialReviewStage->id,
            'name'              => 'Does this patient need DME?',
            'type'              => 'decision',
            'order'             => 2,
            'metadata'          => [
                'question' => 'Does this patient need DME?',
                'options'  => ['Yes', 'No'],
                'on_true'  => 'Proceed with DME workflow',
                'on_false' => 'No further action required',
            ],
        ]);

        // Step 3: Checkbox step for patient visit (conditional)
        WorkflowStep::create([
            'workflow_stage_id' => $initialReviewStage->id,
            'name'              => 'Patient Visit Completed',
            'type'              => 'checkbox',
            'order'             => 3,
            'metadata'          => [
                'label' => 'Confirm that patient visit has been completed',
                'depends_on' => [
                    'step_id' => $decisionStep->id,
                    'value'   => 'Yes',
                ],
            ],
        ]);

        // Step 4: Upload step for documentation for DME (conditional)
        WorkflowStep::create([
            'workflow_stage_id' => $initialReviewStage->id,
            'name'              => 'Upload Documentation for DME',
            'type'              => 'upload',
            'order'             => 4,
            'metadata'          => [
                'upload_label'  => 'Upload DME documentation',
                'allowed_mimes' => ['pdf', 'jpg', 'png'],
                'max_files'     => 5,
                'max_size'      => 2048,
                'depends_on' => [
                    'step_id' => $decisionStep->id,
                    'value'   => 'Yes',
                ],
            ],
        ]);

        // Step 5: Upload step for DME docs Signature (conditional)
        WorkflowStep::create([
            'workflow_stage_id' => $initialReviewStage->id,
            'name'              => 'Upload DME Signature Documents',
            'type'              => 'upload',
            'order'             => 5,
            'metadata'          => [
                'upload_label'  => 'Upload signed DME documents',
                'allowed_mimes' => ['pdf', 'jpg', 'png'],
                'max_files'     => 5,
                'max_size'      => 2048,
                'depends_on' => [
                    'step_id' => $decisionStep->id,
                    'value'   => 'Yes',
                ],
            ],
        ]);
    }
}
