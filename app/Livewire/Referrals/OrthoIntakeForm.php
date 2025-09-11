<?php

namespace App\Livewire\Referrals;

use Illuminate\Support\Facades\Auth;
use App\Models\ReferralIntake;
use Illuminate\Support\Str;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;

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

    // === OCR Prefill (Step 1) ===
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $ocr_pdf = null; // PDF to send to OCR API
    public ?string $ocr_error = null; // display API error if any
    public array $ocr_last_response = []; // optional debug/preview

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
            // OCR PDF (optional)
            'ocr_pdf'             => 'nullable|file|mimes:pdf|max:20480', // 20 MB

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

        // Conditional requirement on step 4
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

    /**
     * OCR prefill using local API (defaults to http://127.0.0.1:8009/extract).
     * Populates $first_name, $last_name, $dob if good candidates found.
     */
    public function ocrPrefill(): void
    {
        $this->reset('ocr_error');
        // Validate only the uploaded OCR file; it's optional overall
        $this->validateOnly('ocr_pdf');

        if (!$this->ocr_pdf) {
            $this->ocr_error = 'Please choose a PDF first.';
            $this->dispatch('ts-toast', title: 'OCR', description: $this->ocr_error, icon: 'warning');
            return;
        }

        try {
            $base = config('services.ocr.base_url') ?? env('OCR_BASE_URL', 'http://127.0.0.1:8009');
            $url  = rtrim($base, '/').'/extract';

            $response = Http::timeout(90)
                ->acceptJson()
                ->attach('file', fopen($this->ocr_pdf->getRealPath(), 'r'), $this->ocr_pdf->getClientOriginalName())
                ->post($url, [
                    // include a template if your API expects it; otherwise omit
                    // 'template' => 'Athena',
                ]);

            if ($response->failed()) {
                $msg = $response->json('detail') ?? $response->body();
                throw new \RuntimeException('API error: ' . $msg);
            }

            $json = $response->json() ?? [];
            $this->ocr_last_response = $json;

            // Pick the best person & DOB
            $bestPerson = $this->pickBestPerson($json['people'] ?? []);
            $bestDob    = $this->pickBestDob($json['dobs'] ?? []);

            if ($bestPerson) {
                $this->first_name = trim((string)($bestPerson['first_name'] ?? $this->first_name));
                $this->last_name  = trim((string)($bestPerson['last_name']  ?? $this->last_name));
            }
            if ($bestDob && !empty($bestDob['date'])) {
                $norm = $this->normalizeDob((string)$bestDob['date']);
                if ($norm) {
                    $this->dob = $norm; // Y-m-d
                }
            }

            $this->dispatch('ts-toast', title: 'OCR Prefill', description: 'Fields updated from PDF.', icon: 'success');

        } catch (\Throwable $e) {
            $this->ocr_error = $e->getMessage();
            $this->dispatch('ts-toast', title: 'OCR Prefill', description: $this->ocr_error, icon: 'danger');
        }
    }

    /** @param array<int, array<string,mixed>> $people */
    protected function pickBestPerson(array $people): ?array
    {
        if (empty($people)) return null;
        // Sort by confidence desc, take first with both first/last present
        usort($people, fn($a,$b) => ($b['confidence'] ?? 0) <=> ($a['confidence'] ?? 0));
        foreach ($people as $p) {
            if (!empty($p['first_name']) && !empty($p['last_name'])) return $p;
        }
        return $people[0] ?? null;
    }

    /** @param array<int, array<string,mixed>> $dobs */
    protected function pickBestDob(array $dobs): ?array
    {
        if (empty($dobs)) return null;
        usort($dobs, fn($a,$b) => ($b['confidence'] ?? 0) <=> ($a['confidence'] ?? 0));
        return $dobs[0] ?? null;
    }

    /** Normalize API DOB strings to Y-m-d if possible. */
    protected function normalizeDob(?string $raw): ?string
    {
        if (!$raw) return null;
        $raw = trim($raw);

        // Try common formats
        $formats = ['Y-m-d', 'm/d/Y', 'm-d-Y', 'd/m/Y', 'd-m-Y', 'M d, Y', 'F d, Y'];
        foreach ($formats as $fmt) {
            try {
                $c = Carbon::createFromFormat($fmt, $raw);
                if ($c) return $c->format('Y-m-d');
            } catch (\Throwable) {
                // keep trying
            }
        }

        // Last resort: strtotime
        $ts = strtotime($raw);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }
        return null;
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
