<div class="space-y-8 p-6 max-w-7xl mx-auto">
    @if(session()->has('success'))
        <div class="p-3 mb-4 text-green-800 bg-green-100 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-3 mb-4 text-red-800 bg-red-100 rounded">
            {{ session('error') }}
        </div>
    @endif

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>

    
	
<div class="flex items-center space-x-3">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" >
    <i class="fa-solid fa-notes-medical text-green-600 dark:text-green-400 text-4xl"></i>
    <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
        Patient Referral #{{ $referral->id }} – Details
    </h2>
</div>
@php
    /** @var \App\Models\Referral $referral */
    $intake = $referral->intake;

    // ---- Normalizers / helpers (same safety as before) ----
    $normalizeFiles = function ($raw) {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } elseif ($raw !== '') {
                $raw = [$raw];
            } else {
                $raw = [];
            }
        }
        return is_array($raw) ? $raw : [];
    };
    $buildHref = function ($urlLike) {
        if (!is_string($urlLike) || $urlLike === '') return null;
        return \Illuminate\Support\Str::startsWith($urlLike, ['http://','https://'])
            ? $urlLike
            : asset('storage/'.$urlLike);
    };

    $dash = '—';

    // BMI badge palette
    $bmiVal = is_numeric($intake->bmi ?? null) ? (float) $intake->bmi : null;
    $bmiTone = fn($bmi) => $bmi === null ? ['bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200','N/A']
        : ($bmi < 18.5 ? ['bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200', number_format($bmi,1)]
        : ($bmi < 25 ? ['bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200', number_format($bmi,1)]
        : ($bmi < 30 ? ['bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200', number_format($bmi,1)]
        : ['bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200', number_format($bmi,1)])));
    [$bmiBadgeClass, $bmiText] = $bmiTone($bmiVal);

    // Smoking status badge class
    $smoke = trim((string)($intake->smoking_status ?? ''));
    $smokeLower = strtolower($smoke);
    $smokeClass = match(true) {
        $smokeLower === ''                      => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
        str_contains($smokeLower,'never')      => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
        str_contains($smokeLower,'former')     => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
        str_contains($smokeLower,'current'),
        str_contains($smokeLower,'smoker')     => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200',
        default                                => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
    };

    // Shorthand badge (yes/no)
    $boolBadge = function ($truthy) {
        return $truthy
            ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200"><svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 0 1 0 1.414l-7.25 7.25a1 1 0 0 1-1.414 0l-3.25-3.25a1 1 0 1 1 1.414-1.414l2.543 2.543 6.543-6.543a1 1 0 0 1 1.414 0z"/></svg>Yes</span>'
            : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>No</span>';
    };

    // File “chip”
    $fileChip = function ($href, $label = null) {
        $label ??= $href ? basename(parse_url($href, PHP_URL_PATH) ?? $href) : 'Attachment';
        return <<<HTML
        <a href="{$href}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-full text-xs font-medium
                  bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 hover:bg-indigo-100
                  dark:bg-indigo-900/40 dark:text-indigo-200 dark:ring-indigo-800">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M8 17a5 5 0 0 1 0-7l5-5a3.5 3.5 0 1 1 5 5l-6.5 6.5a2 2 0 1 1-3-3l5.5-5.5a.75.75 0 1 1 1.06 1.06L9.06 14.56a.5.5 0 1 0 .71.71L17 8.06A2 2 0 1 0 14.17 5.2l-5 5A3.5 3.5 0 0 0 14.12 15l6.5-6.5a5 5 0 1 0-7.07-7.07l-5 5A7 7 0 1 0 18.9 16.78l4.6-4.6a.75.75 0 0 1 1.06 1.06l-4.6 4.6A8.5 8.5 0 1 1 8 17Z"/>
            </svg>
            <span class="truncate max-w-[12rem]">{$label}</span>
        </a>
        HTML;
    };
@endphp

@if ($intake)
    {{-- Fancy card with TS toolbar --}}
    <x-ts-card class="p-0 overflow-hidden">
        {{-- Card header with action bar --}}
        <div class="flex items-center justify-between px-5 py-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border-b border-slate-200/70 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white grid place-content-center shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 0 0-2 2v12l4-4h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z"/></svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-slate-100">Referral Intake Summary</h2>
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        Submitted {{ $intake->submitted_at?->format('M d, Y H:i') ?? $dash }}
                    </p>
                </div>
            </div>

            {{-- TallStackUI buttons (no accordion) --}}
            <div class="flex items-center gap-2">

            </div>
        </div>

        {{-- Body sections --}}
        <div class="divide-y divide-slate-200 dark:divide-slate-700">
            {{-- Patient --}}
            <section class="px-5 py-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold tracking-widest uppercase text-slate-500">Patient</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">#{{ $referral->id }}</span>
                </div>
                <dl class="grid grid-cols-12 gap-x-6 gap-y-3 text-sm">
                    <div class="col-span-12 sm:col-span-6">
                        <dt class="text-slate-500">First Name</dt>
                        <dd class="mt-0.5 font-medium">{{ $intake->patient_first_name ?: $dash }}</dd>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <dt class="text-slate-500">Last Name</dt>
                        <dd class="mt-0.5 font-medium">{{ $intake->patient_last_name ?: $dash }}</dd>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <dt class="text-slate-500">Date of Birth</dt>
                        <dd class="mt-0.5 font-medium">{{ $intake->patient_dob?->format('M d, Y') ?: $dash }}</dd>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="mt-0.5 font-medium">{{ $intake->patient_phone ?: $dash }}</dd>
                    </div>
                </dl>
            </section>

            {{-- PCP --}}
            <section class="px-5 py-4">
                <h3 class="mb-3 text-xs font-semibold tracking-widest uppercase text-slate-500">Primary Care Provider</h3>
                <dl class="grid grid-cols-12 gap-x-6 gap-y-3 text-sm">
                    <div class="col-span-12 sm:col-span-4">
                        <dt class="text-slate-500">First Name</dt>
                        <dd class="mt-0.5 font-medium">{{ $intake->pcp_first_name ?: $dash }}</dd>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <dt class="text-slate-500">Last Name</dt>
                        <dd class="mt-0.5 font-medium">{{ $intake->pcp_last_name ?: $dash }}</dd>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <dt class="text-slate-500">NPI</dt>
                        <dd class="mt-0.5 font-medium">{{ $intake->pcp_npi ?: $dash }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Clinical --}}
            <section class="px-5 py-4">
                <h3 class="mb-3 text-xs font-semibold tracking-widest uppercase text-slate-500">Clinical</h3>
                <dl class="grid grid-cols-12 gap-x-6 gap-y-3 text-sm">
                    <div class="col-span-12 sm:col-span-6">
                        <dt class="text-slate-500">Diagnosis</dt>
                        <dd class="mt-0.5 font-medium">{{ $intake->diag_for_referral ?: $dash }}</dd>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <dt class="text-slate-500">BMI</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $bmiBadgeClass }}">
                                {{ $bmiText }}
                            </span>
                        </dd>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <dt class="text-slate-500">Smoking Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $smokeClass }}">
                                {{ $smoke !== '' ? $smoke : 'Unknown' }}
                            </span>
                        </dd>
                    </div>
                    <div class="col-span-12">
                        <dt class="text-slate-500">Last Visit Note</dt>
                        <dd class="mt-1 font-medium whitespace-pre-line text-slate-900/90 dark:text-slate-100">
                            {{ $intake->last_visit_note ?: $dash }}
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Meds & Files --}}
            <section class="px-5 py-4">
                <h3 class="mb-3 text-xs font-semibold tracking-widest uppercase text-slate-500">Medications & Files</h3>

                {{-- Medication list with a tiny TS toolbar --}}
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <dt class="text-slate-500 text-sm">Medication List</dt>
                        <dd class="mt-1 font-medium whitespace-pre-line text-sm">
                            {{ $intake->medication_list ?: $dash }}
                        </dd>
                    </div>
                    <div class="shrink-0 flex items-center gap-2">

                    </div>
                </div>

                <div class="grid grid-cols-12 gap-4 mt-4">
                    {{-- X-Ray Files as chips --}}
                    <div class="col-span-12 sm:col-span-6">
                        <dt class="text-slate-500 text-sm">X-Ray Files</dt>
                        <dd class="mt-2">
                            @php $files = $normalizeFiles($intake->xray_files); @endphp
                            @if (count($files))
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($files as $f)
                                        @php
                                            $urlLike = is_string($f) ? $f : ($f['url'] ?? $f['path'] ?? $f['storage_path'] ?? $f['file'] ?? null);
                                            $name    = is_string($f) ? basename($f) : ($f['name'] ?? ($urlLike ? basename($urlLike) : 'Attachment'));
                                            $href    = $buildHref($urlLike);
                                        @endphp
                                        @if ($href)
                                            {!! $fileChip($href, $name) !!}
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">{{ $name }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400">{{ $dash }}</span>
                            @endif
                        </dd>
                    </div>

                    {{-- Surgery Report --}}
                    <div class="col-span-12 sm:col-span-6">
                        <dt class="text-slate-500 text-sm">Surgery Report</dt>
                        <dd class="mt-2">
                            @php
                                $srRaw  = $intake->surgery_report_path;
                                $srPath = is_string($srRaw) ? $srRaw : ($srRaw['url'] ?? $srRaw['path'] ?? $srRaw['storage_path'] ?? $srRaw['file'] ?? null);
                                $srHref = $buildHref($srPath);
                            @endphp
                            @if ($srHref)
                                {!! $fileChip($srHref, 'View Report') !!}
                            @else
                                <span class="text-slate-400">{{ $dash }}</span>
                            @endif
                        </dd>
                    </div>

                    {{-- Prior Joint Surgery --}}
                    <div class="col-span-12 sm:col-span-6">
                        <dt class="text-slate-500 text-sm">Prior Joint Surgery</dt>
                        <dd class="mt-1">{!! $boolBadge($intake->prior_joint_surgery) !!}</dd>
                    </div>

                    {{-- Implant Info --}}
                    <div class="col-span-12 sm:col-span-6">
                        <dt class="text-slate-500 text-sm">Implant Info</dt>
                        <dd class="mt-1 font-medium text-sm">{{ $intake->implant_info ?: $dash }}</dd>
                    </div>
                </div>
            </section>
        </div>
    </x-ts-card>
@else
    <x-ts-alert color="gray" title="No Referral Intake found">
        No intake form has been submitted for this referral.
    </x-ts-alert>
@endif



    <section class="space-y-6">
        @if($referral->workflow && $referral->workflow->stages->isNotEmpty())
            @php
                $currentStageId = $referral->current_stage_id ?? null;

                // Gather all steps in ascending order
                $allStepsOrdered = collect();
                $stagesOrdered = $referral->workflow->stages->sortBy('order');
                foreach ($stagesOrdered as $st) {
                    $allStepsOrdered = $allStepsOrdered->concat($st->steps->sortBy('order'));
                }

                // Completed steps
                $completedStepIds = $referral->progress
                    ->where('status', 'completed')
                    ->pluck('workflow_step_id')
                    ->unique();

                // Identify the next step (first incomplete)
                $nextStepId = null;
                foreach ($allStepsOrdered as $oneStep) {
                    if (! $completedStepIds->contains($oneStep->id)) {
                        $nextStepId = $oneStep->id;
                        break;
                    }
                }

/* -----------------------------------------------------------------------
 |  isStepVisible ­– decide whether each workflow card is rendered
 *---------------------------------------------------------------------*/
$isStepVisible = function ($step) use ($referral)
{
    /* ── signature_response: only the assigned signer sees it ───────── */
    if ($step->type === 'signature_response') {

        // ➊ find the matching request row
 $request = isset($step->metadata['request_step_id'])
     ? \App\Models\SignatureRequest::where('referral_id',   $referral->id)
           ->where('workflow_step_id', $step->metadata['request_step_id'])
           ->latest()
           ->first()
     : \App\Models\SignatureRequest::where('referral_id', $referral->id)
           ->latest()
           ->first();           // fallback: newest request
        if (!$request) {
            return false;              // no request yet → hide card
        }

        // ➋ show the card only to the signer
        return auth()->id() === $request->assigned_user_id;
    }

    /* ── all other step types keep the old depends_on rules ─────────── */
    $meta = $step->metadata ?? [];
    if (!isset($meta['depends_on'])) {
        return true;
    }

    $dep   = $meta['depends_on'];
    $done  = $referral->progress
               ->where('workflow_step_id', $dep['step_id'] ?? 0)
               ->where('status', 'completed')
               ->first();

    return $done
        && trim(str_replace('Chosen: ', '', $done->notes)) === $dep['value'];
};
            @endphp

            <div class="grid grid-cols-1 gap-6">
                @foreach($stagesOrdered as $stage)
                    @php
                        $isCurrentStage = ($currentStageId === $stage->id);
                    @endphp

                    <div class="relative rounded-lg shadow-lg bg-white dark:bg-gray-800 overflow-hidden
                                transition-transform duration-300 hover:scale-[1.02] hover:shadow-2xl border-l-4
                                {{ $isCurrentStage ? 'border-green-500' : 'border-transparent' }}">
                        @if($isCurrentStage)
                            <span class="absolute top-2 right-2 inline-block bg-gradient-to-r from-green-400 to-green-600
                                         text-white text-xs font-bold px-2 py-1 rounded shadow-lg animate-pulse">
                                Current Stage
                            </span>
                        @endif

                        <div class="p-4 space-y-2">
                            <!-- Stage Header -->
                            <div class="flex items-center space-x-2">
								<i class="fa-solid fa-laptop-medical text-green-600 dark:text-green-400 text-4xl"></i>
                                <h4 class="font-bold text-lg text-gray-800 dark:text-gray-100">
                                    {{ $stage->name }} 
                                </h4>
                            </div>

                            <!-- Steps -->
                            <div class="mt-3 space-y-3">
                                @foreach($stage->steps->sortBy('order') as $step)
                                    @if($isStepVisible($step))
                                        @php
                                            // Step progress data
											
                                               /* --------------------------------------------------------------
											 |  Pick the right progress set for this step card
											 |  -------------------------------------------------------------
											 |  • signature_response needs the *request* row so it can show
											 |    the documents immediately.
											 |  • every other type keeps the usual per-step filtering.
											 *------------------------------------------------------------- */
											$stepProgresses = $step->type === 'signature_response'
												? $referral->progress
													  ->filter(fn($p) => $p->step?->type === 'signature_request')
												: $referral->progress
													  ->where('workflow_step_id', $step->id);
                                            $isStepCompleted = $stepProgresses->where('status', 'completed')->isNotEmpty();
                                            $isStepNext = (!$isStepCompleted && $step->id === $nextStepId);

                                            // Decide background/border
                                            if ($isStepCompleted) {
                                                $bgClasses = 'bg-green-50 dark:bg-green-900 border-l-4 border-green-400';
                                            } elseif ($isStepNext) {
                                                $bgClasses = 'bg-orange-50 dark:bg-orange-900 border-l-4 border-orange-400';
                                            } else {
                                                $bgClasses = 'bg-gray-50 dark:bg-gray-700';
                                            }

                                            // Retrieve group arrays (if any)
                                            $canWrite = $step->group_can_write ?? [];
                                            $canSee   = $step->group_can_see ?? [];
                                            $getNotif = $step->group_get_notif ?? [];
                                        @endphp

                                        <!-- Step Container -->
                                        <div class="relative group-step {{ $bgClasses }} p-3 rounded shadow-sm transition mb-2">
                                            <!-- Step Title + Group Info Icon -->
                                            <div class="flex items-center justify-end mb-2">
                                                <div class="flex items-center space-x-1">
                                                    <h5 class="font-bold mb-0.5 text-gray-800 dark:text-gray-100">
                                                        
                                                    </h5>
												<!-- Container to align icon completely to the right -->
												<div class="relative flex justify-end">
													<!-- Group Info Icon + Tooltip -->
													<div class="group hover-trigger cursor-pointer">
														<svg class="w-4 h-4 text-gray-400"
															 fill="none" stroke="currentColor" stroke-width="1.5"
															 viewBox="0 0 24 24">
															<path stroke-linecap="round" stroke-linejoin="round"
																  d="M11.049 2.927c.3-.09.633-.09.933 0l7.19 2.073a1
																  1 0 01.7.954v6.088c0 3.374-2.56 7.292-7.682
																  10.272a1 1 0 01-1.028 0C6.56 19.314 4 15.396 4
																  12.032V5.954a1 1 0 01.7-.954l7.19-2.073z"/>
														</svg>
														<!-- Tooltip on hover -->
														<div class="hover-target hidden group-hover:block
																	absolute top-6 right-0
																	bg-white border border-gray-200 rounded p-3 text-xs
																	text-gray-700 shadow z-20 w-52 pointer-events-none">
															<div class="mb-1">
																<strong>Can Write:</strong>
																{{ $canWrite ? implode(', ', $canWrite) : 'None' }}
															</div>
															<div class="mb-1">
																<strong>Can See:</strong>
																{{ $canSee ? implode(', ', $canSee) : 'None' }}
															</div>
															<div>
																<strong>Notify:</strong>
																{{ $getNotif ? implode(', ', $getNotif) : 'None' }}
															</div>
														</div>
													</div>
												</div>

                                                </div>
                                            </div>

                                            @include("referrals.steps.{$step->type}-step", [
                                                'step' => $step,
                                                'stepProgresses' => $stepProgresses,
                                                'bgClasses' => '', /* We already used $bgClasses above */
                                                'isStepCompleted' => $isStepCompleted,
                                            ])
                                        </div>
                                        <!-- End Step Container -->
                                    @endif
                                @endforeach
                            </div>
                            <!-- End Steps -->
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="italic text-gray-500 dark:text-gray-400">
                No stages found for this workflow.
            </p>
        @endif
		
    </section>
<!-- Some additional CSS for the group info icon tooltip -->
<style>
.group-step:hover {
    transform: scale(1.01);
    box-shadow: 0 4px 8px rgba(0,0,0,0.06);
}

/* The container for our step group icon is .hover-trigger
   and the tooltip is .hover-target. We reveal on group-hover. */
.hover-trigger {
    position: relative;
    display: inline-block;
}

.hover-target {
    /* Hidden by default, made visible on hover with group-hover:block */
    position: absolute;
    top: 1.5rem; /* adjust vertical offset */
    left: 50%;
    transform: translateX(-50%);
    min-width: 10rem;
    background-color: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    padding: 0.5rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    white-space: normal;
}

/* Trigger show/hide using Tailwind or manual approach:
   We'll do a simpler approach using hidden / group-hover:block
   which we used above:
     .hidden.group-hover:block
   */
   .editable:focus { outline: 2px dashed #60a5fa; border-radius: 2px; }

</style>




@push('scripts')
<script>
/*───────────────────────────────────────────────*
 |  1. Controlled-substance list (PMP flag)      |
 *───────────────────────────────────────────────*/
const pmpList = [

    "hydrocodone",
    "hydrocodone/acetaminophen",
    "vicodin",
    "lortab",
    "norco",
    "oxycodone",
    "oxycodone/acetaminophen",
    "oxycontin",
    "percocet",
    "roxicodone",
    "fentanyl",
    "duragesic",
    "sublimaze",
    "actiq",
    "morphine",
    "ms contin",
    "avinza",
    "kadian",
    "methadone",
    "dolophine",
    "methadose",
    "hydromorphone",
    "dilaudid",
    "exalgo",
    "meperidine",
    "demerol",
    "codeine",
    "amphetamine",
    "adderall",
    "adderall xr",
    "dexedrine",
    "evekeo",
    "dextroamphetamine",
    "methylphenidate",
    "ritalin",
    "ritalin la",
    "ritalin sr",
    "concerta",
    "daytrana",
    "quillivant xr",
    "quillichew",
    "metadate",
    "focalin",
    "focalin xr",
    "lisdexamfetamine",
    "vyvanse",
    "cocaine hydrochloride",
    "tapentadol",
    "nucynta",


    "buprenorphine",
    "suboxone",
    "subutex",
    "sublocade",
    "buprenex",
    "probuphine",
    "butrans",
    "codeine with acetaminophen",
    "tylenol with codeine",
    "tylenol with codeine #3",
    "tylenol with codeine #4",
    "capital with codeine",
    "phendimetrazine",
    "benzphetamine",
    "ketamine",
    "testosterone",
    "androgel",
    "depo-testosterone",
    "oxandrolone",
    "anavar",
    "nandrolone",
    "deca-durabolin",

    // Schedule IV
    "alprazolam",
    "xanax",
    "xanax xr",
    "niravam",
    "alprazolam intensol",
    "clonazepam",
    "klonopin",
    "diazepam",
    "valium",
    "lorazepam",
    "ativan",
    "temazepam",
    "restoril",
    "triazolam",
    "halcion",
    "midazolam",
    "versed",
    "eszopiclone",
    "lunesta",
    "zolpidem",
    "ambien",
    "ambien cr",
    "zaleplon",
    "sonata",
    "phenobarbital",
    "carisoprodol",
    "soma",
    "modafinil",
    "provigil",
    "armodafinil",
    "nuvigil",
    "butorphanol",
    "stadol",
    "chlordiazepoxide",
    "librium",


    "diphenoxylate/atropine",
    "lomotil",
    "pregabalin",
    "lyrica",
    "lacosamide",
    "vimpat",
    "brivaracetam",
    "briviact",
    "cough syrup with codeine",
    "promethazine with codeine",
    "robitussin ac",
    "cheratussin ac",
    "guaifenesin/codeine"
];

/*───────────────────────────────────────────────*
 |  2. Normalisation helpers                     |
 *───────────────────────────────────────────────*/
const reClean  = /[^A-Z0-9 ]+/g;           // strip punctuation
const doseLike = /\d/;                    // any digit → dose token
const normaliseDrug = s => s.toUpperCase().replace(reClean, '').trim();

/*───────────────────────────────────────────────*
 |  3. Brand ⇆ Generic dictionary bootstrap      |
 *───────────────────────────────────────────────*/
window.brandSet       = new Set();
window.genericSet     = new Set();
window.brandToGeneric = {};               // BRAND → first generic

(async () => {
  try {
    const res = await fetch('{{ asset('data/brand_generic.json') }}');
    if (res.ok) {
      const src = await res.json();
      for (const [brand, gens] of Object.entries(src)) {
        const nBrand = normaliseDrug(brand);
        const nGen   = normaliseDrug(gens[0]);
        brandSet.add(nBrand);
        genericSet.add(nGen);
        brandToGeneric[nBrand] = nGen;
      }
    } else {
      console.warn('brand_generic.json not found');
    }
  } catch (err) {
    console.error('Unable to load brand_generic.json', err);
  }
})();

/*───────────────────────────────────────────────*
 |  4. Generic helpers                           |
 *───────────────────────────────────────────────*/
function drugType(str){
  const n = normaliseDrug(str);
  if (brandSet.has(n))   return 'brand';
  if (genericSet.has(n)) return 'generic';
  return null;
}
function badgeHtml(type){
    /* brand pills are now hidden, generic pills remain */
    if (type !== 'generic') return '';                // ← single new condition
    return `<span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded
                     bg-purple-100 text-purple-800 text-xs font-semibold">
              Generic
            </span>`;
}

const isPMPDrug     = txt => pmpList.some(d => txt.toLowerCase().includes(d));
const canonicalDrug = txt => brandToGeneric[ normaliseDrug(txt) ] ?? normaliseDrug(txt);
const brandHint = (orig, canon) =>
  normaliseDrug(orig) !== canon
    ? ` <span class="text-xs italic text-gray-400">(brand: ${orig})</span>` : '';

/*───────────────────────────────────────────────*
 |  5. Parse textarea lines  (robust splitter)   |
 *───────────────────────────────────────────────*/
function parseMedList(text){
  return text.split('\n').map(line=>{
    const t = line.trim();
    if (!t) return null;

    const tokens = t.split(/\s+/);
    const idx    = tokens.findIndex(tok => doseLike.test(tok));   // first token with digit

    if (idx > 0) {  // found a dose chunk
      return {
        name:   tokens.slice(0, idx).join(' '),
        dosage: tokens.slice(idx).join(' '),
        original: t
      };
    }
    return { name: t, dosage: '', original: t };  // whole line is the name
  }).filter(Boolean);
}

/*───────────────────────────────────────────────*
 |  6. Compare Facility vs Epic lists            |
 *───────────────────────────────────────────────*/
function compareLists(){
  const fac = parseMedList(document.getElementById('facilityMedList').value);
  const epi = parseMedList(document.getElementById('epicMedList').value);

  /* group by canonical generic */
  const map = {};
  const add = (arr,key)=>arr.forEach(r=>{
    const canon = canonicalDrug(r.name);
    map[canon] ??= { facility:[], epic:[] };
    map[canon][key].push({...r, canon});
  });
  add(fac,'facility'); add(epi,'epic');

  let html='';
  for (const [canon,obj] of Object.entries(map)){
    const badge = badgeHtml(drugType(canon));
    const pmp   = isPMPDrug(canon) ? ' <span class="text-red-500 text-xs font-semibold">(PMP)</span>' : '';
    let status='', color='', details='';

    if (obj.facility.length && obj.epic.length){
      const f = obj.facility[0], e = obj.epic[0];
      if (f.dosage === e.dosage){
        status='Match'; color='bg-green-50 border-green-400';
        details=`
          <span class="editable med-line" contenteditable="true" spellcheck="false" data-original="${f.name}">
            ${canon} ${f.dosage}${badge}${pmp}
            ${brandHint(f.name,canon)||brandHint(e.name,canon)}
          </span>`;
      } else {
        status='Dosage Mismatch'; color='bg-orange-50 border-orange-400';
        details=`
          <div>
            <span class="block font-semibold editable" contenteditable="true" spellcheck="false" data-original="${f.name}">
              ${canon} ${badge}${brandHint(f.name,canon)||brandHint(e.name,canon)}
            </span>
            <span>Facility Dosage: <strong>${f.dosage}</strong></span><br>
            <span>Epic&nbsp;&nbsp;&nbsp;&nbsp;Dosage: <strong>${e.dosage}</strong></span>
            <select class="mt-2 w-full border-gray-300 rounded reconcile-select">
              <option value="${f.original}">Keep Facility (${f.dosage})</option>
              <option value="${e.original}">Keep Epic (${e.dosage})</option>
            </select>
          </div>`;
      }

    } else if (obj.facility.length){
      const f=obj.facility[0];
      status='Only in Facility'; color='bg-red-50 border-red-400';
      details=`
        <span class="editable med-line" contenteditable="true" spellcheck="false" data-original="${f.name}">
          ${canon} ${badge}${pmp} ${brandHint(f.name,canon)}
        </span>`;
    } else {
      const e=obj.epic[0];
      status='Only in Epic'; color='bg-yellow-50 border-yellow-400';
      details=`
        <span class="editable med-line" contenteditable="true" spellcheck="false" data-original="${e.name}">
          ${canon} ${badge}${pmp} ${brandHint(e.name,canon)}
        </span>`;
    }

    html += `
      <div class="border-l-4 ${color} p-3 mb-2 rounded shadow-sm">
        ${details}
        <span class="text-xs text-gray-500 italic ml-2">(${status})</span>
        <button onclick="removeMed(this)" class="float-right text-xs text-red-500 hover:underline">Remove</button>
      </div>`;
  }
  document.getElementById('comparisonResults').innerHTML = html;
}

/*───────────────────────────────────────────────*
 |  7. Remove card / Save & Complete             |
 *───────────────────────────────────────────────*/
function removeMed(btn){ btn.parentElement.remove(); }

function saveAndMarkComplete(stepId) {
    const rows  = document.querySelectorAll('#comparisonResults div.border-l-4');
    const meds  = [];

    rows.forEach(div => {

        /* 1️⃣  if a dropdown exists, that’s authoritative */
        const sel = div.querySelector('.reconcile-select');
        if (sel && sel.value.trim()) {
            const txt = sel.value.trim();
            meds.push({ text: txt, pmp: isPMPDrug(txt) });
            return;   // done with this div
        }

        /* 2️⃣  otherwise look for an inline-edited span */
        const ed  = div.querySelector('.editable');
        if (ed) {
            const txt = ed.innerText
                          .replace(/\(brand:.*\)$/i, '')  // strip hint
                          .trim();
            if (txt) {
                meds.push({ text: txt, pmp: isPMPDrug(txt) });
                return;
            }
        }

        /* 3️⃣  fallback to raw text (only-in lists) */
        const raw = div.textContent
                       .replace('Remove', '')
                       .replace(/\(.*?\)/, '')
                       .trim();
        if (raw) meds.push({ text: raw, pmp: isPMPDrug(raw) });
    });

    /* ── de-dupe and preview (unchanged) ───────────────────────── */
    const unique = Array.from(new Map(meds.map(m => [m.text, m])).values());

    let html = '<h5 class="medrec-subtitle">Final Medication List</h5><ul class="form-responses">';
    unique.forEach(m => {
        html += `<li>${m.text}${badgeHtml(drugType(m.text))}${m.pmp
            ? ' <span class="text-red-500 text-sm font-semibold">(PMP)</span>'
            : ''}</li>`;
    });
    html += '</ul>';
    document.getElementById('comparisonResults').innerHTML = html;

    /* ── push to Livewire (unchanged) ──────────────────────────── */
    @this.set('facilityList', document.getElementById('facilityMedList').value);
    @this.set('epicList',     document.getElementById('epicMedList').value);
    @this.set('finalMeds',    unique);
    @this.call('saveMedRec',  stepId);
}


/*───────────────────────────────────────────────*
 |  8. Auto-rebuild grid after Livewire patches  |
 *───────────────────────────────────────────────*/
Livewire.hook('message.processed', () => {
  const f=document.getElementById('facilityMedList');
  const e=document.getElementById('epicMedList');
  const r=document.getElementById('comparisonResults');
  if(f && e && r && !r.querySelector('.border-l-4') && (f.value.trim()||e.value.trim())){
    setTimeout(compareLists,0);
  }
});
</script>
@endpush




</div>

