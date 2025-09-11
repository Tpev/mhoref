<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\SignatureRequest;

class PendingSignaturesWidget extends Component
{
    /**
     * Fetch all pending signature requests for the current user.
     */
    public function getRequestsProperty()
    {
        return SignatureRequest::with(['referral', 'documents'])
            ->where('assigned_user_id', auth()->id())
            ->where('status', 'pending')  // or however you mark “not yet signed”
            ->latest('created_at')
            ->get();
    }

    /**
     * Listen for the child's dispatch('signature-saved') call.
     * The method can be empty—Livewire will automatically re-render this component.
     */
    #[On('signature-saved')]
    public function handleSignatureSaved(): void
    {
        // no body needed
    }

    public function render()
    {
        return view('livewire.dashboard.pending-signatures-widget');
    }
}
