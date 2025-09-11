<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class Dashboard extends Component
{
    public $referralsInProgress = 0;
    public $dischargeToday      = 0;
    public $dischargeTomorrow   = 0;

    public array $upcomingDischarges = [];
    public array $tasks              = [];
    public $unreadNotifications      = [];

    public function mount()
    {
        // 1) Referrals In Progress
        $this->referralsInProgress = Referral::where('status', 'in_progress')->count();

        // 2) Load All Referrals w/ Steps+Progress
        $allReferrals = Referral::with(['progress.step', 'workflow.stages.steps'])->get();

        // Discharge counters + upcoming
        $this->computeDischargeData($allReferrals);

        // My Tasks
        $this->buildMyTasks($allReferrals);

        // Unread Notifications
        $this->fetchUnreadNotifications();
    }

    /**
     * Build metrics for dischargeToday / dischargeTomorrow
     * and an array of upcoming discharges in the next 7 days.
     */
    protected function computeDischargeData($allReferrals)
    {
        $today  = Carbon::today();
        $cutoff = $today->clone()->addDays(7);

        $collection = collect();

        foreach ($allReferrals as $referral) {
            // Attempt to parse "form" step's date_of_discharge
            $formProgress = $referral->progress->firstWhere('step.type', 'form');
            if (!$formProgress || !$formProgress->notes) {
                continue;
            }

            $notes = json_decode($formProgress->notes, true);
            $dischargeDateRaw = $notes['date_of_discharge'] ?? null;

            if (!$dischargeDateRaw) {
                continue;
            }

            try {
                $parsedDate = Carbon::parse($dischargeDateRaw);

                // Today / Tomorrow counters
                if ($parsedDate->isToday()) {
                    $this->dischargeToday++;
                } elseif ($parsedDate->isTomorrow()) {
                    $this->dischargeTomorrow++;
                }

                // If within next 7 days => push to upcoming
                if ($parsedDate->between($today, $cutoff)) {
                    $patientName = trim(($notes['first_name'] ?? '') . ' ' . ($notes['last_name'] ?? ''));
                    $patientName = $patientName ?: '—';
                    $status  = $referral->status ?? '—';
                    $dateStr = $parsedDate->format('m/d/Y');

                    $collection->push([
                        'id'             => $referral->id,
                        'patient_name'   => $patientName,
                        'discharge_date' => $dateStr,
                        'status'         => ucfirst($status),
                    ]);
                }
            } catch (\Exception $e) {
                // skip invalid date
            }
        }

        // Sort by date
        $this->upcomingDischarges = $collection
            ->sortBy(fn($item) => Carbon::parse($item['discharge_date']))
            ->values()
            ->all();
    }

    /**
     * Check if a step is visible based on 'depends_on'.
     * e.g. if step #3 depends on step #2 being "Yes", we skip if #2 is not completed or not "Yes".
     */
    protected function isStepVisible($referral, $step): bool
    {
        $metadata = $step->metadata ?? [];
        if (!isset($metadata['depends_on'])) {
            return true; // no condition => always visible
        }

        $depends   = $metadata['depends_on'];
        $depStepId = $depends['step_id'];
        $depValue  = $depends['value'] ?? null;

        $depProgress = $referral->progress
            ->where('workflow_step_id', $depStepId)
            ->where('status', 'completed')
            ->first();

        if (!$depProgress) {
            // The step we depend on isn't completed => not visible
            return false;
        }

        // If the stored notes (usually "Yes"/"No") != required value => skip
        if ($depProgress->notes !== $depValue) {
            return false;
        }

        return true;
    }

    /**
     * Build My Tasks:
     *  - Incomplete steps
     *  - user can write (group_can_write)
     *  - step is "visible" (depends_on satisfied)
     */
    protected function buildMyTasks($allReferrals)
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        // user's groups
        $userGroups = is_array($user->group) ? $user->group : Arr::wrap($user->group);

        $tasksCollection = collect();

        foreach ($allReferrals as $referral) {
            // All steps
            $steps = $referral->workflow->stages->flatMap->steps;

            // parse "form" notes for patient name & discharge date
            $formProgress  = $referral->progress->firstWhere('step.type', 'form');
            $patientName   = '—';
            $dischargeDate = '—';

            if ($formProgress && $formProgress->notes) {
                $notes = json_decode($formProgress->notes, true);
                $fname = $notes['first_name'] ?? '';
                $lname = $notes['last_name'] ?? '';
                $full  = trim("$fname $lname");
                if (!empty($full)) {
                    $patientName = $full;
                }

                if (!empty($notes['date_of_discharge'])) {
                    try {
                        $dischargeDate = Carbon::parse($notes['date_of_discharge'])->format('m/d/Y');
                    } catch (\Exception $e) {
                        $dischargeDate = 'Invalid';
                    }
                }
            }

            foreach ($steps as $step) {
                // skip if depends_on not satisfied
                if (!$this->isStepVisible($referral, $step)) {
                    continue;
                }

                // skip if user not in group_can_write
                $canWriteGroups = is_array($step->group_can_write) ? $step->group_can_write : [];
                $intersection   = array_intersect($userGroups, $canWriteGroups);
                if (empty($intersection)) {
                    continue;
                }

                // skip if step completed
                $isCompleted = $referral->progress
                    ->where('workflow_step_id', $step->id)
                    ->where('status', 'completed')
                    ->isNotEmpty();
                if ($isCompleted) {
                    continue;
                }

                // push to tasks with step ID
                $tasksCollection->push([
                    'referral_id'    => $referral->id,
                    'step_id'        => $step->id,  // so we can link to the exact step
                    'patient_name'   => $patientName,
                    'step_name'      => $step->name,
                    'discharge_date' => $dischargeDate,
                ]);
            }
        }

        // sort by patient_name
        $this->tasks = $tasksCollection->sortBy('patient_name')->values()->all();
    }

    /**
     * Fetch all unread notifications from the current user
     */
    public function fetchUnreadNotifications()
    {
        $user = auth()->user();
        if (!$user) {
            $this->unreadNotifications = [];
            return;
        }
        // Provided by Notifiable trait on User model
        $this->unreadNotifications = $user->unreadNotifications;
    }

    /**
     * Mark a single notification as read (user clicked “Dismiss”).
     */
    public function markAsRead($notificationId)
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $notif = $user->unreadNotifications->where('id', $notificationId)->first();
        if ($notif) {
            $notif->markAsRead();
        }

        // Refresh
        $this->fetchUnreadNotifications();
    }

    /**
     * Example quick action: “View All Discharges”
     */
    public function viewAllDischarges()
    {
        return redirect()->route('referrals.index');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
