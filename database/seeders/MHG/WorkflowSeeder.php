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

        // You can create more workflows if needed:
        // Workflow::create([
        //     'name' => 'Another Workflow',
        //     'description' => 'Some description...',
        // ]);
    }
}
