<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReferralsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public ?string $dischargeFilter = null;

    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()         { $this->resetPage(); }
    public function updatingStatusFilter()   { $this->resetPage(); }
    public function updatingDischargeFilter(){ $this->resetPage(); }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        // 1) Base query + eager loads (include "intake")
        $referrals = Referral::query()
            ->with(['workflow.stages.steps', 'progress.step', 'intake'])
            ->when($this->search, function ($query) {
                $term = "%{$this->search}%";
                $query->where(function ($q) use ($term) {
                    $q->where('id', 'like', $term)
                      ->orWhereHas('intake', function ($q2) use ($term) {
                          $q2->where('patient_first_name', 'like', $term)
                             ->orWhere('patient_last_name', 'like', $term)
                             ->orWhere('pcp_first_name', 'like', $term)
                             ->orWhere('pcp_last_name', 'like', $term);
                      })
                      ->orWhereHas('progress', function ($q3) use ($term) {
                          $q3->where('notes', 'like', $term);
                      });
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->get();

        // 2) (Optional legacy) Discharge filter from progress->notes if you still use it
        if ($this->dischargeFilter) {
            $referrals = $referrals->filter(function ($referral) {
                $firstStepProgress = $referral->progress->firstWhere('step.type', 'form');
                if (!$firstStepProgress || !$firstStepProgress->notes) return false;

                $notes = json_decode($firstStepProgress->notes, true);
                $date  = $notes['date_of_discharge'] ?? null;
                if (!$date) return false;

                $parsed = Carbon::parse($date);
                return match ($this->dischargeFilter) {
                    'today'    => $parsed->isToday(),
                    'tomorrow' => $parsed->isTomorrow(),
                    default    => false,
                };
            });
        }

        // 3) Map computed fields (use intake for patient + pcp)
        $referrals = $referrals->map(function ($referral) {
            $intake = $referral->intake;

            // Patient / PCP from ReferralIntake
            $patientFirst = $intake?->patient_first_name ?? '—';
            $patientLast  = $intake?->patient_last_name ?? '—';
            $patientDob   = $intake?->patient_dob?->format('Y-m-d') ?? ($intake?->patient_dob ?? '—');
            $pcpFirst     = $intake?->pcp_first_name ?? '—';
            $pcpLast      = $intake?->pcp_last_name ?? '—';

            // Progress from workflow/progress relations (unchanged)
            $totalSteps      = $referral->workflow->stages->flatMap->steps->count();
            $completedSteps  = $referral->progress->where('status', 'completed')->count();
            $progressPercent = $totalSteps ? round(($completedSteps / $totalSteps) * 100) : 0;

            $remainingSteps = $referral->workflow->stages->flatMap->steps->filter(function ($step) use ($referral) {
                return !$referral->progress
                    ->where('workflow_step_id', $step->id)
                    ->where('status', 'completed')
                    ->count();
            });
            $currentStep = $remainingSteps->first()?->name ?? 'All steps completed';

            // Attach for table binding
            $referral->patient_first_name = $patientFirst;
            $referral->patient_last_name  = $patientLast;
            $referral->patient_dob        = $patientDob;
            $referral->pcp_first_name     = $pcpFirst;
            $referral->pcp_last_name      = $pcpLast;

            $referral->progress_percent = $progressPercent;
            $referral->completed_steps  = $completedSteps;
            $referral->total_steps      = $totalSteps;
            $referral->current_step     = $currentStep;

            return $referral;
        });

        // 4) Sort In Memory
        $referrals = $this->sortCollection($referrals);

        // 5) Paginate
        $paginated = $this->paginateCollection($referrals, 10);

        return view('livewire.referrals-table', ['referrals' => $paginated]);
    }

    protected function sortCollection(Collection $referrals): Collection
    {
        return $this->sortDirection === 'asc'
            ? $referrals->sortBy($this->sortField, SORT_REGULAR, false)
            : $referrals->sortByDesc($this->sortField);
    }

    protected function paginateCollection(Collection $collection, int $perPage)
    {
        $page  = request()->get('page', 1);
        $items = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
