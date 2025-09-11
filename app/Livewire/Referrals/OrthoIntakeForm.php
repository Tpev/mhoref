<?php

namespace App\Livewire\Referrals;

use Illuminate\Support\Facades\Auth;
use App\Models\ReferralIntake;
use Illuminate\Support\Str;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrthoIntakeForm extends Component
{
    use WithFileUploads;

    public ?int $workflow_id = null;
    public ?int $workflow_step_id = null;

    // Wizard
    public int $step = 1;
    public int $totalSteps = 5;
    public bool $submitted = false;

    // Patient
    public string $first_name = '';
    public string $last_name  = '';
    public ?string $dob       = null; // Y-m-d
    public string $phone      = '';

    // Referring PCP
    public string $pcp_first_name = '';
    public string $pcp_last_name  = '';
    public string $pcp_npi        = '';

    // Clinical
    public string $last_visit_note = '';
    public string $diag_for_referral = '';
    public string $smoking_status = ''; // never|former|current
    public ?float $bmi = null;
    public string $medication_list = '';

    // Step 4 — Uploads
    /** @var array<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $xrays = []; // multiple documents
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $surgery_report = null; // single document upload
    public string $implant_info = ''; // free text details

    public string $prior_joint_surgery = ''; // yes|no

    /** @var array<array{label:string,value:string}> */
    public array $smokingOptions = [];

    public function mount(): void
    {
        // Initialize select options to avoid null/undefined in TallStackUI
        $this->smokingOptions = [
            ['label' => 'Never',  'value' => 'never'],
            ['label' => 'Former', 'value' => 'former'],
            ['label' => 'Current','value' => 'current'],
        ];
    }

    protected function rules(): array
    {
        return [
            // step 1
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'dob'                 => 'required|date',
            'phone'               => 'required|string|max:30',

            // step 2
            'pcp_first_name'      => 'required|string|max:100',
            'pcp_last_name'       => 'required|string|max:100',
            'pcp_npi'             => 'required|string|max:20',

            // step 3
            'last_visit_note'     => 'nullable|string|max:5000',
            'diag_for_referral'   => 'required|string|max:2000',
            'smoking_status'      => 'required|in:never,former,current',
            'bmi'                 => 'nullable|numeric|min:5|max:80',
            'medication_list'     => 'nullable|string|max:5000',

            // step 4 — uploads & surgery
            'xrays'               => 'nullable|array|max:10',
            'xrays.*'             => 'file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:10240', // 10 MB each
            'prior_joint_surgery' => 'required|in:yes,no',
            'surgery_report'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:20480', // 20 MB
            'implant_info'        => 'nullable|string|max:5000',
        ];
    }

    protected function stepFields(int $step): array
    {
        return match ($step) {
            1 => ['first_name','last_name','dob','phone'],
            2 => ['pcp_first_name','pcp_last_name','pcp_npi'],
            3 => ['last_visit_note','diag_for_referral','smoking_status','bmi','medication_list'],
            4 => ['xrays','prior_joint_surgery','surgery_report','implant_info'],
            5 => [], // review
            default => [],
        };
    }

    public function getProgressProperty(): int
    {
        return (int) round(($this->step / $this->totalSteps) * 100);
    }

    protected function validateOnlyStep(): void
    {
        $fields = $this->stepFields($this->step);
        if (empty($fields)) return;

        $rules = array_intersect_key($this->rules(), array_flip($fields));

        // Conditional requirements on this step only
        if ($this->step === 4 && $this->prior_joint_surgery === 'yes') {
            $rules['surgery_report'] = 'required|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:20480';
        }

        $this->validate($rules);
    }

    public function nextStep(): void
    {
        $this->validateOnlyStep();
        if ($this->step < $this->totalSteps) $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function submit(): void
    {
        $rules = $this->rules();
        if ($this->prior_joint_surgery === 'yes') {
            $rules['surgery_report'] = 'required|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:20480';
        }
        $this->validate($rules);

        DB::transaction(function () {
            // Always attach to workflow_id = 1
            $referral = Referral::create([
                'workflow_id' => 1,
                'status'      => 'new',
            ]);

            $intake = ReferralIntake::create([
                'referral_id'         => $referral->id,
                'workflow_id'         => 1,
                'workflow_step_id'    => null,
                'submitted_by'        => Auth::id(),
                'submitted_at'        => now(),

                'patient_first_name'  => $this->first_name,
                'patient_last_name'   => $this->last_name,
                'patient_dob'         => $this->dob,
                'patient_phone'       => $this->phone,

                'pcp_first_name'      => $this->pcp_first_name,
                'pcp_last_name'       => $this->pcp_last_name,
                'pcp_npi'             => $this->pcp_npi,

                'last_visit_note'     => $this->last_visit_note,
                'diag_for_referral'   => $this->diag_for_referral,
                'smoking_status'      => $this->smoking_status,
                'bmi'                 => $this->bmi,
                'medication_list'     => $this->medication_list,

                'prior_joint_surgery' => $this->prior_joint_surgery === 'yes',
                'implant_info'        => $this->implant_info ?: null,
            ]);

            // === file storage ===
            $base = "referrals/intakes/{$intake->id}";
            $xrayMeta = [];
            foreach ($this->xrays ?? [] as $file) {
                $ext  = $file->getClientOriginalExtension();
                $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $slug = Str::slug(substr($name, 0, 100));
                $storedPath = $file->storeAs("$base/xrays", "{$slug}-".Str::random(8).".$ext", 'public');

                $xrayMeta[] = [
                    'path'       => $storedPath,
                    'url'        => asset("storage/$storedPath"),
                    'original'   => $file->getClientOriginalName(),
                    'mime'       => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                ];
            }

            $surgeryPath = null;
            if ($this->prior_joint_surgery === 'yes' && $this->surgery_report) {
                $ext  = $this->surgery_report->getClientOriginalExtension();
                $name = pathinfo($this->surgery_report->getClientOriginalName(), PATHINFO_FILENAME);
                $slug = Str::slug(substr($name, 0, 100));
                $surgeryPath = $this->surgery_report->storeAs("$base/surgery", "{$slug}-".Str::random(8).".$ext", 'public');
            }

            $intake->update([
                'xray_files'          => $xrayMeta,
                'surgery_report_path' => $surgeryPath,
            ]);
        });

        $this->submitted = true;
        $this->step = $this->totalSteps;
    }

    public function render()
    {
        return view('livewire.referrals.ortho-intake-form')
            ->title('Ortho Referral Intake • MaineHealth')
            ->layout('layouts.guest'); // uses your <x-guest-layout>
    }
}
