<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Referral;
use Illuminate\Support\Facades\Auth;

class CreateReferral extends Component
{
    public function create()
    {
        // Adjust workflow_id as needed (e.g., default workflow, selected from form, etc.)
        $referral = Referral::create([
            'workflow_id' => 1, // adjust logic as necessary
            'status'      => 'in_progress',
        ]);

        session()->flash('success', 'Referral created successfully.');

        // Redirect to the referral's workflow page
        return redirect()->route('referrals.workflow.show', ['id' => $referral->id]);
    }

    public function render()
    {
        return view('livewire.create-referral');
    }
}
