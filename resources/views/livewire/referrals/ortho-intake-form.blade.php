{{-- Multi-step wizard using TallStack UI (ts-*) --}}
<div class="max-w-5xl mx-auto px-4 py-10 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Orthopedics — Referral Intake</h1>
            <p class="text-sm text-slate-500 mt-1">MaineHealth · Demo </p>
        </div>
        <a href="{{ route('referrals.ortho.intake') }}" class="text-sm underline">Refresh</a>
    </div>

    {{-- Progress --}}
    <div class="space-y-2">
        <div class="flex items-center justify-between text-sm">
            <span class="font-medium">Step {{ $step }} of {{ $totalSteps }}</span>
            <span class="text-slate-500">{{ $this->progress }}%</span>
        </div>
        <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
            <div class="h-full bg-indigo-500 transition-all" style="width: {{ $this->progress }}%"></div>
        </div>
        <div class="flex gap-2 text-xs text-slate-500">
            <span class="{{ $step>=1 ? 'text-indigo-600 font-medium' : '' }}">Patient</span>
            <span>•</span>
            <span class="{{ $step>=2 ? 'text-indigo-600 font-medium' : '' }}">Referring PCP</span>
            <span>•</span>
            <span class="{{ $step>=3 ? 'text-indigo-600 font-medium' : '' }}">Clinical</span>
            <span>•</span>
            <span class="{{ $step>=4 ? 'text-indigo-600 font-medium' : '' }}">X-Rays & Surgery</span>
            <span>•</span>
            <span class="{{ $step>=5 ? 'text-indigo-600 font-medium' : '' }}">Review</span>
        </div>
    </div>

    {{-- Success alert --}}
    @if($submitted)
        <x-ts-alert icon="check-circle" class="bg-emerald-50 border-emerald-200">
            <span class="font-medium">Submitted.</span> Validated for demo. No data saved.
        </x-ts-alert>
    @endif

    <x-ts-card class="space-y-8">

        {{-- ================== STEP 1: PATIENT ================== --}}
        @if($step === 1)
            <section class="space-y-4">
                <h2 class="text-lg font-semibold">Patient Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ts-input label="First Name" wire:model.defer="first_name" placeholder="Jane" />
                        @error('first_name') <x-ts-error :message="$message" /> @enderror
                    </div>
                    <div>
                        <x-ts-input label="Last Name" wire:model.defer="last_name" placeholder="Doe" />
                        @error('last_name') <x-ts-error :message="$message" /> @enderror
                    </div>
                    <div>
                        <x-ts-input type="date" label="Date of Birth" wire:model.defer="dob" />
                        @error('dob') <x-ts-error :message="$message" /> @enderror
                    </div>
                    <div>
                        <x-ts-input label="Phone Number" wire:model.defer="phone" placeholder="(555) 555-1234" />
                        @error('phone') <x-ts-error :message="$message" /> @enderror
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== STEP 2: PCP ================== --}}
        @if($step === 2)
            <section class="space-y-4">
                <h2 class="text-lg font-semibold">Referring PCP</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-ts-input label="PCP First Name" wire:model.defer="pcp_first_name" placeholder="John" />
                        @error('pcp_first_name') <x-ts-error :message="$message" /> @enderror
                    </div>
                    <div>
                        <x-ts-input label="PCP Last Name" wire:model.defer="pcp_last_name" placeholder="Smith" />
                        @error('pcp_last_name') <x-ts-error :message="$message" /> @enderror
                    </div>
                    <div>
                        <x-ts-input label="PCP NPI" wire:model.defer="pcp_npi" placeholder="1234567890" />
                        @error('pcp_npi') <x-ts-error :message="$message" /> @enderror
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== STEP 3: CLINICAL ================== --}}
        @if($step === 3)
            <section class="space-y-4">
                <h2 class="text-lg font-semibold">Clinical Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-ts-textarea
                            label="Note from Last Visit"
                            wire:model.defer="last_visit_note"
                            placeholder="Summary of last encounter..."
                            rows="3"
                        />
                        @error('last_visit_note') <x-ts-error :message="$message" /> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <x-ts-textarea
                            label="Diagnosis for Referral"
                            wire:model.defer="diag_for_referral"
                            placeholder="e.g., Osteoarthritis of knee, M17.10"
                            rows="3"
                        />
                        @error('diag_for_referral') <x-ts-error :message="$message" /> @enderror
                    </div>
                    <div>
                        {{-- TallStack UI v2 select — options emitted as JSON to avoid null/undefined --}}

					<x-ts-select.styled
						label="Smoking Status"
						placeholder="Select status"
						wire:model.defer="smoking_status"
						:options="$smokingOptions"
					/>

                        @error('smoking_status') <x-ts-error :message="$message" /> @enderror
                    </div>
                    <div>
                        <x-ts-input
                            type="number" step="0.1" min="5" max="80"
                            label="Body Mass Index (BMI)"
                            wire:model.defer="bmi"
                            placeholder="24.6"
                        />
                        @error('bmi') <x-ts-error :message="$message" /> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <x-ts-textarea
                            label="Medication List"
                            wire:model.defer="medication_list"
                            placeholder="Name • Dose • Frequency (one per line)…"
                            rows="4"
                        />
                        @error('medication_list') <x-ts-error :message="$message" /> @enderror
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== STEP 4: X-RAYS (UPLOAD) & SURGERY ================== --}}
        @if($step === 4)
            <section class="space-y-6">

                {{-- X-Rays upload --}}
                <div class="space-y-3">
                    <h2 class="text-lg font-semibold">X-Ray(s) — Upload Documents</h2>
                    <x-ts-upload
                        label="Attach X-Ray files (PDF/JPG/PNG — up to 10)"
                        wire:model="xrays"
                        multiple
                        hint="You can drag & drop multiple files."
                    />
                    @error('xrays') <x-ts-error :message="$message" /> @enderror
                    @foreach($errors->get('xrays.*') as $msg)
                        <x-ts-error :message="$msg[0]" />
                    @endforeach

                    @if(!empty($xrays))
                        <div class="text-sm text-slate-600">
                            <span class="font-medium">Selected:</span>
                            <ul class="list-disc ml-5">
                                @foreach($xrays as $file)
                                    <li>{{ $file->getClientOriginalName() }} ({{ number_format($file->getSize()/1024, 0) }} KB)</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- Prior Surgery --}}
                <div class="space-y-3">
                    <h3 class="text-base font-semibold">Prior Joint Surgery</h3>
                    <div class="flex flex-wrap gap-4">
                        <x-ts-radio label="No" value="no" wire:model="prior_joint_surgery" />
                        <x-ts-radio label="Yes" value="yes" wire:model="prior_joint_surgery" />
                    </div>
                    @error('prior_joint_surgery') <x-ts-error :message="$message" /> @enderror

                    @if($prior_joint_surgery === 'yes')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-1">
                                <x-ts-upload
                                    label="Surgery Report (PDF/JPG/PNG)"
                                    wire:model="surgery_report"
                                    hint="Upload the operative note or report."
                                />
                                @error('surgery_report') <x-ts-error :message="$message" /> @enderror
                                @if($surgery_report)
                                    <p class="text-sm text-slate-600 mt-1">
                                        Selected: {{ $surgery_report->getClientOriginalName() }}
                                        ({{ number_format($surgery_report->getSize()/1024, 0) }} KB)
                                    </p>
                                @endif
                            </div>

                            <div class="md:col-span-1">
                                <x-ts-textarea
                                    label="Implant Information"
                                    wire:model.defer="implant_info"
                                    placeholder="Manufacturer • Model • Size • Side • Notes…"
                                    rows="4"
                                />
                                @error('implant_info') <x-ts-error :message="$message" /> @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- ================== STEP 5: REVIEW ================== --}}
        @if($step === 5)
            <section class="space-y-6">
                <h2 class="text-lg font-semibold">Review & Confirm</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ts-card class="p-4">
                        <h3 class="font-semibold mb-2">Patient</h3>
                        <dl class="text-sm space-y-1">
                            <div><dt class="font-medium inline">First:</dt> <dd class="inline">{{ $first_name ?: '—' }}</dd></div>
                            <div><dt class="font-medium inline">Last:</dt> <dd class="inline">{{ $last_name ?: '—' }}</dd></div>
                            <div><dt class="font-medium inline">DOB:</dt> <dd class="inline">{{ $dob ?: '—' }}</dd></div>
                            <div><dt class="font-medium inline">Phone:</dt> <dd class="inline">{{ $phone ?: '—' }}</dd></div>
                        </dl>
                    </x-ts-card>

                    <x-ts-card class="p-4">
                        <h3 class="font-semibold mb-2">Referring PCP</h3>
                        <dl class="text-sm space-y-1">
                            <div><dt class="font-medium inline">First:</dt> <dd class="inline">{{ $pcp_first_name ?: '—' }}</dd></div>
                            <div><dt class="font-medium inline">Last:</dt> <dd class="inline">{{ $pcp_last_name ?: '—' }}</dd></div>
                            <div><dt class="font-medium inline">NPI:</dt> <dd class="inline">{{ $pcp_npi ?: '—' }}</dd></div>
                        </dl>
                    </x-ts-card>

                    <x-ts-card class="p-4 md:col-span-2">
                        <h3 class="font-semibold mb-2">Clinical</h3>
                        <dl class="text-sm space-y-1">
                            <div><dt class="font-medium inline">Diagnosis:</dt> <dd class="inline">{{ $diag_for_referral ?: '—' }}</dd></div>
                            <div><dt class="font-medium inline">Smoking:</dt> <dd class="inline">{{ ucfirst($smoking_status ?: '—') }}</dd></div>
                            <div><dt class="font-medium inline">BMI:</dt> <dd class="inline">{{ $bmi ?: '—' }}</dd></div>
                            <div><dt class="font-medium inline">Med List:</dt> <dd class="inline whitespace-pre-line">{{ $medication_list ?: '—' }}</dd></div>
                            <div><dt class="font-medium block">Last Visit Note:</dt>
                                <dd class="mt-1 text-slate-700 whitespace-pre-line">{{ $last_visit_note ?: '—' }}</dd>
                            </div>
                        </dl>
                    </x-ts-card>

                    <x-ts-card class="p-4 md:col-span-2">
                        <h3 class="font-semibold mb-2">X-Rays & Surgery</h3>
                        <div class="text-sm space-y-2">
                            <div>
                                <span class="font-medium">X-Ray files:</span>
                                @if(!empty($xrays))
                                    <ul class="list-disc ml-5">
                                        @foreach($xrays as $file)
                                            <li>{{ $file->getClientOriginalName() }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span>—</span>
                                @endif
                            </div>

                            <div>
                                <span class="font-medium">Prior Joint Surgery:</span>
                                <span>{{ strtoupper($prior_joint_surgery ?: '—') }}</span>
                            </div>

                            @if($prior_joint_surgery === 'yes')
                                <div>
                                    <span class="font-medium">Surgery Report:</span>
                                    <span>{{ $surgery_report ? $surgery_report->getClientOriginalName() : '—' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Implant Info:</span>
                                    <div class="text-slate-700 whitespace-pre-line">{{ $implant_info ?: '—' }}</div>
                                </div>
                            @endif
                        </div>
                    </x-ts-card>
                </div>
            </section>
        @endif

        {{-- ================== NAVIGATION ================== --}}
        <div class="flex items-center justify-between pt-2 sticky bottom-0 bg-white/80 backdrop-blur rounded-b-xl">
            <div>
                @if($step > 1)
                    <x-ts-button color="secondary" icon="arrow-left" wire:click="prevStep">Back</x-ts-button>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($step < $totalSteps)
                    <x-ts-button icon="arrow-right" wire:click="nextStep">Next</x-ts-button>
                @else
                    <x-ts-button icon="paper-airplane" wire:click="submit">Submit (Demo)</x-ts-button>
                @endif
            </div>
        </div>

    </x-ts-card>
</div>
