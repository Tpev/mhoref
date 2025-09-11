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
        $dischargeStage = WorkflowStage::where('name', 'Prior to discharge')->first();
        $dischargeStage1 = WorkflowStage::where('name', 'Day before discharge')->first();

        if (!$dischargeStage) {
            $this->command->error('The workflow stage "Prior to discharge" was not found.');
            return;
        }

        /**
         * ------------------------------------------------
         *  1) Initiate discharge
         * ------------------------------------------------
         */
$step1 = WorkflowStep::create([
    'workflow_stage_id' => $dischargeStage->id,
    'name'              => 'Initiate discharge for a patient',
    'type'              => 'form',
    'order'             => 1,
    'metadata'          => [
        'fields' => [
            ['name' => 'first_name',         'type' => 'text',     'label' => 'Patient First Name',     'required' => true],
            ['name' => 'last_name',          'type' => 'text',     'label' => 'Patient Last Name',      'required' => true],
            ['name' => 'date_of_discharge',  'type' => 'date',     'label' => 'Date of Discharge',      'required' => true],
            ['name' => 'destination',        'type' => 'text',     'label' => 'Destination',            'required' => true],
            [
                'name' => 'facility',
                'type' => 'select',
                'label' => 'Facility',
                'required' => true,
                'options' => ['Facility A', 'Facility B', 'Facility C']
            ],
            ['name' => 'home_health_agency', 'type' => 'text',     'label' => 'Home Health Agency',     'required' => false],
            [
                'name' => 'prefered_pharmacy',
                'type' => 'select',
                'label' => 'Preferred Pharmacy',
                'required' => false,
                'options' => ['Pharmacy One', 'Pharmacy Two', 'Pharmacy Three']
            ],
            ['name' => 'additional_notes',   'type' => 'textarea', 'label' => 'Additional Notes',        'required' => false],
        ],
    ],
    'group_can_write'   => ['discharge_coordinator','social_worker'],
    'group_can_see'     => ['discharge_coordinator','social_worker'],
    'group_get_notif'   => ['pt_ot','nurse','provider','social_worker','family'],
]);

        /**
         * ------------------------------------------------
         *  2) Patient initiated discharge?
         *     (decision = "Question")
         * ------------------------------------------------
         */
        $step2 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Patient Initiated discharge?',
            'type'              => 'decision',
            'order'             => 2,
            'metadata'          => [
                'question' => 'Has the patient initiated discharge?',
                'options'  => ['Yes', 'No'],
                // You can define on_true/on_false or leave as-is
                'on_true'  => 'Continue discharge workflow',
                'on_false' => 'No discharge needed',
            ],
            'group_can_write'   => ['discharge_coordinator','social_worker'],
            'group_can_see'     => ['discharge_coordinator','social_worker'],
            'group_get_notif'   => ['pt_ot','nurse','provider','social_worker','family'],
        ]);

        /**
         * ------------------------------------------------
         *  3) Patient capacity to make decision?
         *     (depends on #2 being "Yes", if that’s your logic)
         * ------------------------------------------------
         */
        $step3 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Patient has the capacity to make the decision?',
            'type'              => 'decision',
            'order'             => 3,
            'metadata'          => [
                'question' => 'Does the patient have capacity to make discharge decisions?',
                'options'  => ['Yes', 'No'],
                'on_true'  => 'Proceed with discharge if safe',
                'on_false' => 'Notify provider / family / guardians',
                'depends_on' => [
                    'step_id' => $step2->id,
                    'value'   => 'Yes',
                ],
            ],
            'group_can_write'   => ['pt_ot','social_worker','provider'],
            'group_can_see'     => ['pt_ot','social_worker','provider'],
            'group_get_notif'   => ['provider'], // e.g. if capacity is in question
        ]);

        /**
         * ------------------------------------------------
         *  4) The patient need DME ? PT/OT
         *     (decision = "Question")
         * ------------------------------------------------
         */
        $step4 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Does the patient need DME? (PT/OT)',
            'type'              => 'decision',
            'order'             => 4,
            'metadata'          => [
                'question' => 'Does this patient require PT/OT DME?',
                'options'  => ['Yes', 'No'],
                'on_true'  => 'Trigger PT/OT DME steps',
                'on_false' => 'Skip PT/OT DME steps',
            ],
            'group_can_write'   => ['pt_ot','discharge_coordinator','provider'],
            'group_can_see'     => ['pt_ot','discharge_coordinator','provider'],
            'group_get_notif'   => ['pt_ot','discharge_coordinator','provider'],
        ]);

$step51 = WorkflowStep::create([
    'workflow_stage_id' => $dischargeStage->id,
    'name'              => 'List of DME Equipment Needed',
    'type'              => 'form',
    'order'             => 5,
    'metadata'          => [
        'fields' => [
            [
                'name'     => 'dme_equipment',
                'type'     => 'multiselect',
                'label'    => 'DME Equipment',
                'required' => false,
                'options'  => [
                    'Walker',
                    'Wheelchair',
                    'Hospital Bed',
                    'Oxygen Tank',
                    'Crutches',
                    'Shower Chair',
                    'Bedside Commode',
                    'Nebulizer',
                    'CPAP Machine',
                    'Hoyer Lift',
                    'Other',
                ],
            ],
            [
                'name'     => 'dme_equipment_other_note',
                'type'     => 'text',
                'label'    => 'Other Equipment (if any)',
                'required' => false,
            ],
        ],
        'depends_on' => [
            'step_id' => $step4->id,
            'value'   => 'Yes',
        ],
    ],
    'group_can_write' => ['discharge_coordinator', 'nurse'],
    'group_can_see'   => ['discharge_coordinator', 'nurse', 'provider'],
    'group_get_notif' => ['provider'],
]);

        /**
         * ------------------------------------------------
         *  5) Patient visit for DME PT/OT (Checkbox)
         * ------------------------------------------------
         */
        $step5 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Patient visit for DME PT/OT',
            'type'              => 'checkbox',
            'order'             => 6,
            'metadata'          => [
                'label' => 'Confirm that the provider has completed the PT/OT DME visit',
                'depends_on' => [
                    'step_id' => $step4->id,
                    'value'   => 'Yes',
                ],
            ],
            'group_can_write'   => ['provider'],
            'group_can_see'     => ['provider'],
            'group_get_notif'   => ['provider'],
        ]);






        /**
         * ------------------------------------------------
         *  6) Documentation for DME PT/OT (Upload)
         * ------------------------------------------------
         */
        $step6 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Documentation for DME PT/OT',
            'type'              => 'upload',
            'order'             => 7,
            'metadata'          => [
                'upload_label'  => 'Upload PT/OT DME documentation',
                'allowed_mimes' => ['pdf','jpg','png'],
                'max_files'     => 5,
                'max_size'      => 2048,
                'depends_on'    => [
                    'step_id' => $step4->id,
                    'value'   => 'Yes',
                ],
            ],
            'group_can_write'   => ['pt_ot'],
            'group_can_see'     => ['pt_ot','provider'],
            'group_get_notif'   => ['provider'],
        ]);



        /**
         * ------------------------------------------------
         *  8) The patient need DME ? Nursing
         *     (decision = "Question")
         * ------------------------------------------------
         */
        $step8 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Does the patient need DME? (Nursing)',
            'type'              => 'decision',
            'order'             => 8,
            'metadata'          => [
                'question' => 'Does this patient require Nursing DME?',
                'options'  => ['Yes', 'No'],
                'on_true'  => 'Trigger Nursing DME steps',
                'on_false' => 'Skip Nursing DME steps',
            ],
            'group_can_write'   => ['nurse_manager','provider'],
            'group_can_see'     => ['nurse_manager','provider','social_worker'],
            'group_get_notif'   => ['provider','nurse_manager','social_worker'],
        ]);

$step51 = WorkflowStep::create([
    'workflow_stage_id' => $dischargeStage->id,
    'name'              => 'List of DME Equipment Needed',
    'type'              => 'form',
    'order'             => 9,
    'metadata'          => [
        'fields' => [
            [
                'name'     => 'dme_equipment',
                'type'     => 'multiselect',
                'label'    => 'DME Equipment',
                'required' => false,
                'options'  => [
                    'Walker',
                    'Wheelchair',
                    'Hospital Bed',
                    'Oxygen Tank',
                    'Crutches',
                    'Shower Chair',
                    'Bedside Commode',
                    'Nebulizer',
                    'CPAP Machine',
                    'Hoyer Lift',
                    'Other',
                ],
            ],
            [
                'name'     => 'dme_equipment_other_note',
                'type'     => 'text',
                'label'    => 'Other Equipment (if any)',
                'required' => false,
            ],
        ],
        'depends_on' => [
            'step_id' => $step8->id,
            'value'   => 'Yes',
        ],
    ],
    'group_can_write' => ['discharge_coordinator', 'nurse'],
    'group_can_see'   => ['discharge_coordinator', 'nurse', 'provider'],
    'group_get_notif' => ['provider'],
]);
        /**
         * ------------------------------------------------
         *  9) Patient visit for DME Nursing
         * ------------------------------------------------
         */
        $step9 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Patient visit for DME Nursing',
            'type'              => 'checkbox',
            'order'             => 10,
            'metadata'          => [
                'label' => 'Confirm that the provider has completed the Nursing DME visit',
                'depends_on' => [
                    'step_id' => $step8->id,
                    'value'   => 'Yes',
                ],
            ],
            'group_can_write'   => ['provider'],
            'group_can_see'     => ['provider'],
            'group_get_notif'   => ['provider'],
        ]);

        /**
         * ------------------------------------------------
         * 10) Documentation for DME Nursing (Upload)
         * ------------------------------------------------
         */
        $step10 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Documentation for DME Nursing',
            'type'              => 'upload',
            'order'             => 11,
            'metadata'          => [
                'upload_label'  => 'Upload Nursing DME documentation',
                'allowed_mimes' => ['pdf','jpg','png'],
                'max_files'     => 5,
                'max_size'      => 2048,
                'depends_on'    => [
                    'step_id' => $step8->id,
                    'value'   => 'Yes',
                ],
            ],
            'group_can_write'   => ['unit_coordinator','ma'],
            'group_can_see'     => ['provider','unit_coordinator','ma'],
            'group_get_notif'   => ['provider'],
        ]);



        /**
         * ------------------------------------------------
         * 12) Patient have community resource needs?
         *     (decision = "Question")
         * ------------------------------------------------
         */
        $step12 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Does the patient have community resource needs?',
            'type'              => 'decision',
            'order'             => 12,
            'metadata'          => [
                'question' => 'Does this patient need community resources?',
                'options'  => ['Yes', 'No'],
                'on_true'  => 'Proceed with community resource listing',
                'on_false' => 'No resources needed',
            ],
            'group_can_write'   => ['social_worker'],
            'group_can_see'     => ['social_worker','provider','nurse_manager'],
            'group_get_notif'   => ['provider','nurse_manager'],
        ]);

        /**
         * ------------------------------------------------
         * 13) Community resource needs listing. (Form)
         * ------------------------------------------------
         */
        $step13 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Community resource needs listing.',
            'type'              => 'form',
            'order'             => 13,
            'metadata'          => [
                'fields' => [
                    ['name' => 'resources', 'type' => 'textarea', 'label' => 'List the resources needed', 'required' => true],
                ],
                'depends_on' => [
                    'step_id' => $step12->id,
                    'value'   => 'Yes',
                ],
            ],
            'group_can_write'   => ['social_worker'],
            'group_can_see'     => ['social_worker','provider','nurse_manager'],
            'group_get_notif'   => ['provider','nurse_manager'],
        ]);

        /**
         * ------------------------------------------------
         * 14) Need for Patient education?
         *     (decision = "Question")
         * ------------------------------------------------
         */
        $step14 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Need for Patient education?',
            'type'              => 'decision',
            'order'             => 14,
            'metadata'          => [
                'question' => 'Does the patient need further education?',
                'options'  => ['Yes', 'No'],
                'on_true'  => 'Notify nursing to complete education',
                'on_false' => 'No additional education needed',
            ],
            'group_can_write'   => ['nurse'],
            'group_can_see'     => ['nurse','provider'],
            'group_get_notif'   => ['provider'], // or whoever tracks the education
        ]);

        /**
         * ------------------------------------------------
         * 15) Patient education completed (Checkbox)
         * ------------------------------------------------
         */
        $step15 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Patient education completed',
            'type'              => 'checkbox',
            'order'             => 15,
            'metadata'          => [
                'label' => 'Mark once patient education is completed',
                'depends_on' => [
                    'step_id' => $step14->id,
                    'value'   => 'Yes',
                ],
            ],
            'group_can_write'   => ['nurse'],
            'group_can_see'     => ['nurse','provider'],
            'group_get_notif'   => ['provider'], // notify provider once done
        ]);

        /**
         * ------------------------------------------------
         * 16) Need for patient family notification?
         *     (decision = "Question")
         * ------------------------------------------------
         */
        $step16 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Need for patient family notification?',
            'type'              => 'decision',
            'order'             => 16,
            'metadata'          => [
                'question' => 'Do we need to notify family about discharge?',
                'options'  => ['Yes','No'],
                'on_true'  => 'Proceed with family notification',
                'on_false' => 'No family notification required',
            ],
            'group_can_write'   => ['social_worker'],
            'group_can_see'     => ['social_worker'],
            'group_get_notif'   => ['social_worker'],
        ]);
		$stepNotify = WorkflowStep::create([
			'workflow_stage_id' => $dischargeStage->id,
			'name'              => 'Notify Family Member',
			'type'              => 'notify', // The new step type
			'order'             => 17,       // wherever it belongs
			'metadata'          => [
				'label' => 'Notify Family about discharge',
				// Optionally: 'depends_on' => [...], if you want it visible only after some decision
			],
			'group_can_write'   => ['social_worker','nurse'], // whoever can trigger the notification
			'group_can_see'     => ['social_worker','nurse'],
			'group_get_notif'   => [], // e.g., no notifications or define as needed
		]);
        /**
         * ------------------------------------------------
         * 17) Notification sent (Checkbox)
         * ------------------------------------------------
         */
        $step17 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage->id,
            'name'              => 'Notification sent to family',
            'type'              => 'checkbox',
            'order'             => 18,
            'metadata'          => [
                'label' => 'Mark once the family is contacted',
                'depends_on' => [
                    'step_id' => $step16->id,
                    'value'   => 'Yes',
                ],
            ],
            'group_can_write'   => ['social_worker'],
            'group_can_see'     => ['social_worker'],
            'group_get_notif'   => ['social_worker'],
        ]);

        /**
         * ================================================
         *   Second batch of steps from CSV
         * ================================================
         */

        /**
         * 18) Discharge note in Epic (Checkbox)
         */
        $step18 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Discharge note in Epic',
            'type'              => 'checkbox',
            'order'             => 19,
            'metadata'          => [
                'label' => 'Confirm that a discharge note is in Epic',
            ],
            'group_can_write'   => ['app','provider'], // "APP / Provider"
            'group_can_see'     => ['app','provider'],
            'group_get_notif'   => [],
        ]);

        /**
         * 19) Med Rec (we might introduce a custom type "med_rec"
         *     or treat it like a "form" or "checkbox"
         */
        $step19 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Med Rec',
            'type'              => 'med_rec', // or 'form', depends on your app
            'order'             => 20,
            'metadata'          => [
                'label' => 'Med Rec',
            ],
            'group_can_write'   => ['medical_assistant','provider'],
            'group_can_see'     => ['medical_assistant','provider'],
            'group_get_notif'   => ['provider'],
        ]);

        /**
         * 20) Drugs subject to PMP ? (decision)
         */
        $step20 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Drugs subject to PMP?',
            'type'              => 'decision',
            'order'             => 21,
            'metadata'          => [
                'question' => 'Are any medications subject to PMP requirements?',
                'options'  => ['Yes','No'],
                'on_true'  => 'PMP must be completed',
                'on_false' => 'No PMP check needed',
            ],
            'group_can_write'   => ['medical_assistant','provider'],
            'group_can_see'     => ['medical_assistant','provider'],
            'group_get_notif'   => ['provider'],
        ]);

        /**
         * 21) PMP Done (Checkbox), depends on #20 "Yes"
         */
        $step21 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'PMP Done',
            'type'              => 'checkbox',
            'order'             => 22,
            'metadata'          => [
                'label' => 'Check once PMP check is done',
                'depends_on' => [
                    'step_id' => $step20->id,
                    'value'   => 'Yes',
                ],
            ],
            'group_can_write'   => ['medical_assistant','provider'],
            'group_can_see'     => ['medical_assistant','provider'],
            'group_get_notif'   => ['provider'],
        ]);

        /**
         * 22) Patient visit (Checkbox)
         */
        $step22 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Patient visit',
            'type'              => 'checkbox',
            'order'             => 23,
            'metadata'          => [
                'label' => 'Confirm patient visit completed',
            ],
            'group_can_write'   => ['provider'],
            'group_can_see'     => ['provider','nurse','social_worker'],
            'group_get_notif'   => ['nurse','social_worker'],
        ]);

        /**
         * 23) Epic documentation signed (Checkbox)
         */
        $step23 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Epic documentation signed',
            'type'              => 'checkbox',
            'order'             => 24,
            'metadata'          => [
                'label' => 'Confirm Epic documentation is signed',
            ],
            'group_can_write'   => ['provider'],
            'group_can_see'     => ['provider','nurse','social_worker'],
            'group_get_notif'   => ['nurse','social_worker'],
        ]);

        /**
         * 24) Homebound certification (Checkbox)
         */
        $step24 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Homebound certification',
            'type'              => 'checkbox',
            'order'             => 25,
            'metadata'          => [
                'label' => 'Confirm homebound certification has been completed',
            ],
            'group_can_write'   => ['physician'],
            'group_can_see'     => ['physician','social_worker'],
            'group_get_notif'   => ['social_worker'],
        ]);

        /**
         * 25) Notification sent to family (Checkbox)
         */
        $step25 = WorkflowStep::create([
            'workflow_stage_id' => $dischargeStage1->id,
            'name'              => 'Notification sent to family',
            'type'              => 'checkbox',
            'order'             => 26,
            'metadata'          => [
                'label' => 'Confirm that family has been notified',
            ],
            'group_can_write'   => ['social_worker'],
            'group_can_see'     => ['social_worker'],
            'group_get_notif'   => ['social_worker'],
        ]);

        $this->command->info('Workflow steps successfully seeded based on new CSV data!');
    }
}
