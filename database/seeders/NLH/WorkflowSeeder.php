<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workflow;

class WorkflowSeeder extends Seeder
{
    public function run()
    {
        Workflow::create([
            'name' => 'Patient Discharge Workflow',
            'description' => 'A workflow for handling patient discharge.',
        ]);

        Workflow::create([
            'name' => 'Outpatient Exclusion Screening',
            'description' => 'Screening process to determine if a patient meets exclusion criteria for outpatient specialty surgery.',
        ]);
    }
}
