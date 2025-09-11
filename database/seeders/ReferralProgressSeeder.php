<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Referral;
use App\Models\WorkflowStep;
use App\Models\ReferralProgress;
use App\Models\User; // Ensure this model exists and is correctly configured

class ReferralProgressSeeder extends Seeder
{
    public function run()
    {
        // Fetch the first two referrals from the database
        $referralOne = Referral::first(); // Alternatively, use ->find(1) if you prefer
        $referralTwo = Referral::skip(1)->first(); // Fetch the second referral

        // Fetch the first user from the users table
        $user = User::first(); // Ensure at least one user exists or handle accordingly

        // Retrieve specific workflow steps by their names
        $collectInfoStep = WorkflowStep::where('name', 'Collect Patient Info')->first();
        $confirmHistoryStep = WorkflowStep::where('name', 'Confirm Medical History')->first();

        // Check if the required workflow steps exist
        if (!$collectInfoStep || !$confirmHistoryStep) {
            $this->command->error('Required Workflow Steps not found. Seeder aborted.');
            return;
        }

        // Seed progress for the first referral
        ReferralProgress::create([
            'referral_id' => $referralOne->id,
            'workflow_step_id' => $collectInfoStep->id,
            'completed_by' => $user->id ?? null,
            'completed_at' => now(),
            'status' => 'completed',
            'notes' => 'Patient info collected successfully.',
        ]);

        ReferralProgress::create([
            'referral_id' => $referralOne->id,
            'workflow_step_id' => $confirmHistoryStep->id,
            'completed_by' => $user->id ?? null,
            'completed_at' => now(),
            'status' => 'completed',
            'notes' => 'Medical history confirmed with PCP.',
        ]);

        // Seed progress for the second referral (partial or initial step)
        ReferralProgress::create([
            'referral_id' => $referralTwo->id,
            'workflow_step_id' => $collectInfoStep->id,
            'completed_by' => $user->id ?? null,
            'completed_at' => now(),
            'status' => 'completed',
            'notes' => 'Started the second referral’s initial data.',
        ]);

        // Optionally, you can add more entries or handle additional logic here
    }
}
