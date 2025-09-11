<?php
// app/Http/Livewire/PatientTimeline.php

namespace App\Livewire;

use Livewire\Component;

class PatientTimeline extends Component
{
    public $events = [];

    public function mount()
    {
        $this->events = [
            [
                'date' => 'January 1, 2023',
                'description' => 'Patient admitted to the hospital.',
                'status' => 'completed', // 'completed', 'in-progress', 'pending'
                'icon' => 'user-circle', // Heroicon name
            ],
            [
                'date' => 'January 5, 2023',
                'description' => 'Initial diagnosis made.',
                'status' => 'completed',
                'icon' => 'clipboard-check',
            ],
            [
                'date' => 'Estimated January 10, 2023',
                'description' => 'Treatment started.',
                'status' => 'in-progress',
                'icon' => 'heart',
            ],
            [
                'date' => 'Estimated January 20, 2023',
                'description' => 'Follow-up appointment scheduled.',
                'status' => 'pending',
                'icon' => 'calendar',
            ],
            // Add more events as needed
        ];
    }

    public function render()
    {
        return view('livewire.patient-timeline');
    }
}
