<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\WorkflowStage;

class WorkflowStageSeeder extends Seeder
{
    public function run()
    {
        // Get the workflow we created in WorkflowSeeder
        $workflow = Workflow::where('name', 'Patient Discharge Workflow')->first();

        // Create some stages for this workflow
        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Prior to discharge',
            'order' => 1,
            'description' => 'Provide basic information for patient.',
        ]);

        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Day before discharge',
            'order' => 2,
            'description' => 'Check patient insurance authorization.',
        ]);

    }
}
