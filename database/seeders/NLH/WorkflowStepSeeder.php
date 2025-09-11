<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkflowStage;
use App\Models\WorkflowStep;
use App\Models\Workflow;


class WorkflowStepSeeder extends Seeder
{
    public function run()
    {
		$workflow = Workflow::where('name', 'Patient Discharge Workflow')->first(); // or create a new workflow
if (!$workflow) {
    $this->command->error('Workflow "Patient Discharge Workflow" not found.');
    return;
}

$exclusionStage = WorkflowStage::firstOrCreate(
    [
        'name' => 'Patient Exclusion Criteria',
        'workflow_id' => $workflow->id,
    ],
    [
        'description' => 'Evaluation of patient eligibility for outpatient procedures'
    ]
);


        // Define the exclusion criteria steps
        $steps = [
		
            [
                'name' => 'Central Nervous System/Neurologic Disorders',
                'type' => 'decision',
                'order' => 1,
                'metadata' => [
                    'question' => 'Does the patient have any of the following CNS/Neurologic conditions?',
                    'options' => [
                        'Moderate to severe dementia',
                        'Stroke or TIA within the last 3 months',
                        'Other disorders with significant functional limitations (e.g., ALS, MS, Parkinson’s)'
                    ],
                    'on_true' => 'Exclude from outpatient procedure',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['anesthesiologist', 'admin'],
                'group_can_see' => ['anesthesiologist', 'surgeon'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'Cardiovascular Conditions',
                'type' => 'decision',
                'order' => 2,
                'metadata' => [
                    'question' => 'Does the patient have any of the following cardiovascular conditions?',
                    'options' => [
                        'Drug-eluting stent placement <6 months',
                        'Bare metal stent placement <30 days',
                        'Unstable angina',
                        'Severe hypertrophic cardiomyopathy',
                        'Pulmonary hypertension (severe)',
                        'Congestive heart failure with ejection fraction <30%'
                    ],
                    'on_true' => 'Exclude from outpatient procedure',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['cardiologist', 'admin'],
                'group_can_see' => ['cardiologist', 'anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'Pulmonary Conditions',
                'type' => 'decision',
                'order' => 3,
                'metadata' => [
                    'question' => 'Does the patient have any of the following pulmonary conditions?',
                    'options' => [
                        'Oxygen dependency (except nighttime only)',
                        'Room air saturations <92%',
                        'Recent asthma or COPD exacerbation requiring steroids or hospitalization',
                        'Non-compliant with BiPAP/CPAP',
                        'Tracheostomy'
                    ],
                    'on_true' => 'Exclude from outpatient procedure',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['pulmonologist', 'admin'],
                'group_can_see' => ['pulmonologist', 'anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'Endocrine Conditions',
                'type' => 'decision',
                'order' => 4,
                'metadata' => [
                    'question' => 'Does the patient have uncontrolled diabetes (Hemoglobin A1c >8.5)?',
                    'options' => ['Yes', 'No'],
                    'on_true' => 'Consult anesthesia and surgeon regarding infection risk',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['endocrinologist', 'admin'],
                'group_can_see' => ['endocrinologist', 'anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'Pregnancy Status',
                'type' => 'decision',
                'order' => 5,
                'metadata' => [
                    'question' => 'Is the patient pregnant?',
                    'options' => ['Yes', 'No'],
                    'on_true' => 'Exclude from outpatient procedure',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['obstetrician', 'admin'],
                'group_can_see' => ['obstetrician', 'anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'Renal Conditions',
                'type' => 'decision',
                'order' => 6,
                'metadata' => [
                    'question' => 'Does the patient have renal failure requiring dialysis?',
                    'options' => ['Yes', 'No'],
                    'on_true' => 'Exclude from outpatient procedure',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['nephrologist', 'admin'],
                'group_can_see' => ['nephrologist', 'anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'Liver Conditions',
                'type' => 'decision',
                'order' => 7,
                'metadata' => [
                    'question' => 'Does the patient have end-stage cirrhosis?',
                    'options' => ['Yes', 'No'],
                    'on_true' => 'Exclude from outpatient procedure',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['hepatologist', 'admin'],
                'group_can_see' => ['hepatologist', 'anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'Coagulopathy',
                'type' => 'decision',
                'order' => 8,
                'metadata' => [
                    'question' => 'Does the patient have any of the following coagulopathies?',
                    'options' => [
                        'Thrombocytopenia (platelets <75K)',
                        'Inability to discontinue anticoagulation therapy preoperatively',
                        'Other coagulation disorders (e.g., von Willebrand, Hemophilia)',
                        'Current DVT/PE on anticoagulation'
                    ],
                    'on_true' => 'Exclude from outpatient procedure',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['hematologist', 'admin'],
                'group_can_see' => ['hematologist', 'anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'Anesthesia Considerations',
                'type' => 'decision',
                'order' => 9,
                'metadata' => [
                    'question' => 'Does the patient have any of the following anesthesia-related conditions?',
                    'options' => [
                        'History of post-op delirium',
                        'History or family history of malignant hyperthermia',
                        'Known difficult airway',
                        'Musculoskeletal condition with severe limitation of neck movement'
                    ],
                    'on_true' => 'Consult anesthesia for evaluation',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['anesthesiologist', 'admin'],
                'group_can_see' => ['anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'Pediatric Considerations',
                'type' => 'decision',
                'order' => 10,
                'metadata' => [
                    'question' => 'Is the patient a pediatric case with any of the following?',
                    'options' => [
                        'Age <12 months',
                        'Tonsillectomy in children <3 years old',
                        'Family history of malignant hyperthermia or pseudocholinesterase deficiency',
                        'Congenital or co-morbid medical conditions (e.g., craniofacial, cardiac congenital conditions, morbid obesity)'
                    ],
                    'on_true' => 'Exclude from outpatient procedure',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['pediatrician', 'admin'],
                'group_can_see' => ['pediatrician', 'anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'BMI Considerations',
                'type' => 'decision',
                'order' => 11,
                'metadata' => [
                    'question' => 'Does the patient have a BMI that meets any of the following criteria?',
                    'options' => [
                        'BMI >50',
                        'BMI >45 for intra-abdominal cases',
                        'BMI 40-50 (requires anesthesia assessment)'
                    ],
                    'on_true' => 'Consult anesthesia for evaluation',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['anesthesiologist', 'admin'],
                'group_can_see' => ['anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
            [
                'name' => 'Chronic Opioid/Suboxone Use',
                'type' => 'decision',
                'order' => 12,
                'metadata' => [
                    'question' => 'Is the patient on Suboxone or chronic opioids?',
                    'options' => ['Yes', 'No'],
                    'on_true' => 'Evaluate if surgery can be performed under MAC anesthesia or with peripheral nerve block',
                    'on_false' => 'Proceed with evaluation'
                ],
                'group_can_write' => ['pain_management_specialist', 'admin'],
                'group_can_see' => ['pain_management_specialist', 'anesthesiologist'],
                'group_get_notif' => ['surgical_team']
            ],
			[
				'name' => 'Mobility Considerations',
				'type' => 'decision',
				'order' => 13,
				'metadata' => [
					'question' => 'Is the patient non-ambulatory and/or requires a Hoyer lift for transfer?',
					'options' => ['Yes', 'No'],
					'on_true' => 'Exclude from outpatient procedure',
					'on_false' => 'Proceed with evaluation'
				],
				'group_can_write' => ['physical_therapist', 'admin'],
				'group_can_see' => ['physical_therapist', 'anesthesiologist'],
				'group_get_notif' => ['surgical_team']
			],
        ];

        // Create each step from the array
        foreach ($steps as $stepData) {
            $stepData['workflow_stage_id'] = $exclusionStage->id;
            WorkflowStep::create($stepData);
        }

    }
}
 
