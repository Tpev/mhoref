<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkflowStage;
use App\Models\WorkflowStep;

class WorkflowStepSeeder extends Seeder
{
    public function run()
    {
        // Retrieve (or verify) the workflow stage by name

        $dischargeStage1 = WorkflowStage::where('name', 'Referral Intake')->first();



        /**
         * ------------------------------------------------
         *  1) Initiate discharge
         * ------------------------------------------------
         */
        /**
         * 24) Homebound certification (Checkbox)
         */
        $step24 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Do we have all required information?',
            'type'              => 'checkbox',
            'order'             => 1,
            'metadata'          => [
                'label' => 'Do we have all required information?',
            ],
            'group_can_write'   => ['physician'],
            'group_can_see'     => ['physician','social_worker'],
            'group_get_notif'   => ['social_worker'],
        ]);
		        /**
         * 24) Homebound certification (Checkbox)
         */
        $step24 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Clinical documentation has been reviewed',
            'type'              => 'checkbox',
            'order'             => 2,
            'metadata'          => [
                'label' => 'Clinical documentation has been reviewed',
            ],
            'group_can_write'   => ['physician'],
            'group_can_see'     => ['physician','social_worker'],
            'group_get_notif'   => ['social_worker'],
        ]);
		        /**
         * 24) Homebound certification (Checkbox)
         */
        $step24 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Surgeon has reviewed referral intake',
            'type'              => 'checkbox',
            'order'             => 3,
            'metadata'          => [
                'label' => 'Surgeon has reviewed referral intake',
            ],
            'group_can_write'   => ['physician'],
            'group_can_see'     => ['physician','social_worker'],
            'group_get_notif'   => ['social_worker'],
        ]);
		        /**
         * 24) Homebound certification (Checkbox)
         */
        $step24 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Referral handed off to scheduling',
            'type'              => 'checkbox',
            'order'             => 4,
            'metadata'          => [
                'label' => 'Referral handed off to scheduling',
            ],
            'group_can_write'   => ['physician'],
            'group_can_see'     => ['physician','social_worker'],
            'group_get_notif'   => ['social_worker'],
        ]);


        $this->command->info('Workflow steps successfully seeded based on new CSV data!');
    }
}
