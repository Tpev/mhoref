<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowStage;

class WorkflowStageSeeder extends Seeder
{
    public function run()
    {
        // Get workflows
        $dischargeWorkflow = Workflow::where('name', 'Patient Discharge Workflow')->first();
        $exclusionWorkflow = Workflow::where('name', 'Outpatient Exclusion Screening')->first();

        // Create stages for Patient Discharge Workflow
        WorkflowStage::create([
            'workflow_id' => $dischargeWorkflow->id,
            'name' => 'Prior to discharge',
            'order' => 1,
            'description' => 'Provide basic information for patient.',
        ]);

        WorkflowStage::create([
            'workflow_id' => $dischargeWorkflow->id,
            'name' => 'Day before discharge',
            'order' => 2,
            'description' => 'Check patient insurance authorization.',
        ]);

        // Create stages for Exclusion Screening Workflow
        WorkflowStage::create([
            'workflow_id' => $exclusionWorkflow->id,
            'name' => 'Initial Screening',
            'order' => 1,
            'description' => 'Review initial comorbidity exclusion criteria.',
        ]);

        WorkflowStage::create([
            'workflow_id' => $exclusionWorkflow->id,
            'name' => 'Specialist Notes & Functional Assessment',
            'order' => 2,
            'description' => 'Gather additional documentation and evaluate functional capacity.',
        ]);
    }
}
