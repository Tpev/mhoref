@php
use App\Models\SignatureRequest;
use App\Models\User;

$stepId = $step->id;

$request = SignatureRequest::with(['documents','signer'])
            ->where('referral_id',   $referral->id)
            ->where('workflow_step_id', $stepId)
            ->latest()
            ->first();

$status = [
    'label' => $request
                ? ($request->status === 'completed'
                      ? 'Completed'
                      : 'Pending signature')
                : 'Draft',
    'class' => $request
                ? ($request->status === 'completed'
                      ? 'bg-emerald-600'
                      : 'bg-amber-400 text-gray-900')
                : 'bg-gray-400',
];

$signer = $request?->signer?->name ?? '—';
@endphp

<div class="space-y-6">

    <!-- header -->
    <div class="flex items-center justify-between">
        <h5 class="font-semibold text-gray-800 dark:text-gray-100">
            {{ $step->name }}
        </h5>

        <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold
                     rounded-full shadow text-white {{ $status['class'] }}">
            {{ $status['label'] }}
        </span>
    </div>

    <!-- when request already exists ------------------------------------------------ -->
    @if($request)
        <div class="space-y-2 text-sm">
            <p>
                <span class="font-medium">Signer:</span>
                <em>{{ $signer }}</em>
            </p>

            <ul class="pl-5 list-disc space-y-1">
                @foreach($request->documents as $doc)
                    <li>
                        <a  class="text-emerald-700 hover:underline"
                            href="{{ Storage::url($doc->orig_path) }}"
                            target="_blank">
                            {{ $doc->orig_name }}
                        </a>

                        @if($doc->signed_at)
                            <span class="ml-1 text-emerald-600">✓ signed</span>
                            <a  class="ml-2 underline text-xs"
                                href="{{ Storage::url($doc->signed_pdf_path) }}"
                                target="_blank">
                                download
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

    <!-- upload UI (only if not yet sent) ------------------------------------------ -->
    @elseif($this->userCanWriteStep($stepId))
        <div class="space-y-5">

            <!-- signer select -->
            <div>
                <label class="block mb-1 text-sm font-medium">Signer</label>
                <select
                    wire:model="sigRequestSigner.{{ $stepId }}"
                    class="w-full rounded border-gray-300 text-sm
                           focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">— choose user —</option>
                    @foreach(User::orderBy('name')->get() as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
                @error("sigRequestSigner.$stepId")
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- drop zone -->
            <div
                x-data
                @dragover.prevent
                @drop.prevent="
                    $wire.set('sigRequestFiles.{{ $stepId }}',
                              [...$event.dataTransfer.files])"
                class="p-6 rounded-lg border-2 border-dashed border-emerald-300/60
                       bg-emerald-50/30 text-center cursor-pointer
                       hover:bg-emerald-50/60 transition">
                <input type="file" multiple class="hidden"
                       wire:model="sigRequestFiles.{{ $stepId }}" x-ref="file" />
                <p class="text-sm text-gray-600">
                    Drag PDF(s) here or
                    <span class="underline text-emerald-700" x-on:click="$refs.file.click()">browse</span>.
                </p>
                @error("sigRequestFiles.$stepId.*")
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- preview -->
            @if(!empty($sigRequestFiles[$stepId]))
                <ul class="pl-5 list-disc text-sm space-y-1">
                    @foreach($sigRequestFiles[$stepId] as $f)
                        <li>{{ $f->getClientOriginalName() }}</li>
                    @endforeach
                </ul>
            @endif

            <!-- send -->
            <button
                type="button"
                wire:click="saveSignatureRequest({{ $stepId }})"
                class="inline-flex items-center px-4 py-2 rounded shadow
                       bg-emerald-600 text-white text-sm font-semibold
                       hover:bg-emerald-700 focus:outline-none
                       focus:ring-2 focus:ring-emerald-500">
                Send request
            </button>
        </div>

    @else
        <p class="italic text-sm text-gray-500">
            Only authorised users can create a signature request.
        </p>
    @endif
</div>
