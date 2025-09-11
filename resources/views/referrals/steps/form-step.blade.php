<div> {{-- SINGLE ROOT WRAPPER FOR LIVEWIRE --}}

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js" defer></script>


    <div id="step-{{ $step->id }}" class="form-step-container {{ $bgClasses }}">

        {{-- Example definitions (adjust as needed): --}}
        {{-- $isStepCompleted => whether this step is completed for the current referral --}}
        {{-- $editingFormStep => which step is being edited (null or step->id) --}}
        {{-- $canWrite => whether user can edit the step --}}
        {{-- $step->metadata['fields'] => array of form fields definitions --}}

        @if(!$isStepCompleted || ($editingFormStep === $step->id))
            <!-- SHOW FORM (either not completed or user has clicked "Modify") -->

            <form wire:submit.prevent="saveForm({{ $step->id }})" class="space-y-4">
                @php
                    $fields = $step->metadata['fields'] ?? [];
                @endphp

                @foreach($fields as $field)
    <div>
        @php
            $fieldName  = $field['name'] ?? '';
            $fieldValue = $formAnswers[$step->id][$fieldName] ?? '';
            $readOnly   = !$canWrite ? 'readonly' : '';
            $disabled   = !$canWrite ? 'disabled' : '';
        @endphp

        @if($field['type'] !== 'checkbox')
            <label class="form-label">
                {{ $field['label'] }}
                @if(!empty($field['required']))
                    <span class="required-asterisk">*</span>
                @endif
            </label>
        @endif
		
        @switch($field['type'])
            @case('text')
			    @case('number')
        <input
            type="{{ $field['type'] === 'number' ? 'number' : $field['type'] }}"
            {{--  Livewire’s .number modifier casts to int/float  --}}
            wire:model{{ $field['type'] === 'number' ? '.number' : '' }}="formAnswers.{{ $step->id }}.{{ $fieldName }}"
            value="{{ $fieldValue }}"
            class="form-input-field"
            {{ $readOnly }} {{ $disabled }}>
        @break
		
@case('signature')
@php
    $sigId   = "sig_{$step->id}_{$fieldName}";
    $initial = $formAnswers[$step->id][$fieldName] ?? '';   // "signatures/…png" when editing
@endphp

<div
    x-data="sigPad(
        '{{ $sigId }}',
        '{{ route('signature.upload') }}',
        '{{ csrf_token() }}',
        '{{ $initial }}',
        @this,                                              // Livewire component
        'formAnswers.{{ $step->id }}.{{ $fieldName }}'      // property path
    )"
    class="space-y-2"
>
    <div class="border rounded-md shadow-inner bg-white">
        <canvas id="{{ $sigId }}" class="w-full h-40"></canvas>
    </div>

    <template x-if="path">
        <img :src="'/storage/' + path" class="h-24 object-contain border">
    </template>

    <div class="flex gap-2">
        <button type="button" x-show="!busy" @click="upload" class="btn-green">
            Save&nbsp;Signature
        </button>
        <button type="button" @click="clear" class="btn-gray">Clear</button>
        <span   x-show="busy" class="text-sm text-gray-500">Uploading…</span>
    </div>
</div>
@break








            @case('date')
                <input type="{{ $field['type'] }}"
                       wire:model="formAnswers.{{ $step->id }}.{{ $fieldName }}"
                       value="{{ $fieldValue }}"
                       class="form-input-field"
                       {{ $readOnly }} {{ $disabled }}>
                @break

@case('checkbox')
    @php
        $mlData = $mlSuggestions[$fieldName] ?? null;
        $mlValue = $mlData['value'] ?? null;
        $mlReason = $mlData['reason'] ?? null;

        $isFlaggedYes = $mlValue === 'yes';
        $isFlaggedNo  = $mlValue === 'no';
    @endphp

    <div class="flex items-center gap-2
                @if($isFlaggedYes) border border-yellow-400 bg-yellow-50
                @elseif($isFlaggedNo) border border-blue-300 bg-blue-50
                @endif
                rounded px-2 py-1">
        <input type="checkbox"
               wire:model="formAnswers.{{ $step->id }}.{{ $fieldName }}"
               id="{{ $fieldName }}_{{ $step->id }}"
               {{ $disabled }}
               class="h-4 w-4 text-green-600 border-gray-300 rounded">

        <label for="{{ $fieldName }}_{{ $step->id }}" class="text-sm text-gray-700">
            {{ $field['label'] ?? $fieldName }}
            @if($isFlaggedYes)
                <span class="text-yellow-600 text-xs ml-2 font-semibold"
                      @if($mlReason) title="{{ $mlReason }}" @endif>
                    (Suggested: Yes)
                </span>
            @elseif($isFlaggedNo)
                <span class="text-blue-600 text-xs ml-2 font-semibold"
                      @if($mlReason) title="{{ $mlReason }}" @endif>
                    (Suggested: No)
                </span>
            @endif
        </label>

        @if($isFlaggedYes)
            <svg class="w-4 h-4 text-yellow-500 ml-1"
                 title="{{ $mlReason ?? 'Suggested: likely positive' }}"
                 xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
            </svg>
        @elseif($isFlaggedNo)
            <svg class="w-4 h-4 text-blue-500 ml-1"
                 title="{{ $mlReason ?? 'Suggested: likely negative' }}"
                 xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
            </svg>
        @endif
    </div>
    @break






                            @case('textarea')
                                <textarea wire:model="formAnswers.{{ $step->id }}.{{ $fieldName }}"
                                          class="form-textarea-field"
                                          {{ $readOnly }} {{ $disabled }}>{{ $fieldValue }}</textarea>
                                @break
@case('select')
<div x-data="{
    open: false,
    search: '{{ $fieldValue }}',
    selected: '{{ $fieldValue }}',
    options: {{ json_encode($field['options'] ?? []) }},
    filtered() {
        return this.options.filter(o => o.toLowerCase().includes(this.search.toLowerCase()));
    },
    setSelected(val) {
        this.selected = val;
        this.search = val;
    }
}" class="relative">
    <!-- INPUT FIELD -->
    <input type="text"
           x-model="search"
           @focus="open = true"
           @input="setSelected(search)"
           @keydown.escape="open = false"
           @click.away="open = false"
           placeholder="Select or type to search"
           class="form-input-field"
           {{ $readOnly ? 'readonly' : '' }}>

    <!-- DROPDOWN -->
    <ul x-show="open"
        class="absolute z-50 bg-white border border-gray-300 rounded shadow max-h-60 overflow-y-auto w-full mt-1"
        x-transition>
        <template x-for="option in filtered()" :key="option">
            <li @click="setSelected(option); open = false"
                class="px-3 py-2 hover:bg-green-100 cursor-pointer text-sm"
                x-text="option"></li>
        </template>
    </ul>

    <!-- LIVEWIRE SYNC -->
    <input type="hidden"
       x-model="selected"
       x-init="$watch('selected', value => $wire.set('formAnswers.{{ $step->id }}.{{ $fieldName }}', value))">

</div>
@break
@case('multiselect')
<div
    x-data="{
        open: false,
        search: '',
        selected: {{ json_encode($formAnswers[$step->id][$fieldName] ?? []) }},
        options: {{ json_encode($field['options'] ?? []) }},
        toggleOption(option) {
            if (this.selected.includes(option)) {
                this.selected = this.selected.filter(o => o !== option);
            } else {
                this.selected.push(option);
            }
        },
        filteredOptions() {
            return this.options.filter(o =>
                o.toLowerCase().includes(this.search.toLowerCase())
            );
        }
    }"
    class="relative"
>
    <div class="form-input-field cursor-pointer" @click="open = !open">
        <template x-if="selected.length === 0">
            <span class="text-gray-400">Select options...</span>
        </template>
        <template x-for="item in selected" :key="item">
            <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold mr-1 px-2 py-0.5 rounded">
                <span x-text="item"></span>
            </span>
        </template>
    </div>

    <div x-show="open"
         @click.outside="open = false"
         class="absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded shadow max-h-60 overflow-y-auto"
    >
        <input type="text"
               x-model="search"
               class="w-full px-3 py-2 border-b text-sm"
               placeholder="Search...">

        <template x-for="option in filteredOptions()" :key="option">
            <div @click="toggleOption(option)"
                 class="px-3 py-2 text-sm hover:bg-green-100 cursor-pointer flex items-center">
                <input type="checkbox" class="mr-2" :checked="selected.includes(option)" readonly>
                <span x-text="option"></span>
            </div>
        </template>
    </div>

    <!-- Sync with Livewire -->
    <input type="hidden"
           x-init="$watch('selected', value => $wire.set('formAnswers.{{ $step->id }}.{{ $fieldName }}', value))"
           :value="selected">
</div>
@break



                        @endswitch
                    </div>
                @endforeach

                <div class="flex gap-2">
                    @if($canWrite)
                        <button type="submit" class="btn-green">Submit Form</button>
                    @endif

                    @if($isStepCompleted)
                        <!-- If the step is completed but user is currently editing it, allow cancel -->
                        <button type="button"
                                wire:click="$set('editingFormStep', null)"
                                class="btn-gray">
                            Cancel
                        </button>
						
                    @endif
                </div>
            </form>

        @else
            <!-- COMPLETED VIEW (not in edit mode) -->
            @php
                // Load fields only to check missing required
                $fields = $step->metadata['fields'] ?? [];
                $missingFields = [];

                // Get the latest progress record (adjust if you handle multiple differently)
                $latestProgress = $stepProgresses->sortByDesc('id')->first();

                // Decode JSON 'notes' => array
                $savedResponses = $latestProgress && $latestProgress->notes
                    ? json_decode($latestProgress->notes, true)
                    : [];

                // For each required field, check if it's missing or empty in $savedResponses
                foreach($fields as $field) {
                    $fname      = $field['name'] ?? null;
                    $isRequired = !empty($field['required']);
                    if ($isRequired && $fname) {
                        $value = $savedResponses[$fname] ?? null;
                        if (empty($value)) {
                            $missingFields[] = $field['label'] ?? $fname;
                        }
                    }
                }
            @endphp

            <p class="form-complete-msg">This form has been completed.</p>

            <!-- Show final responses -->
            <ul class="form-responses">
                @foreach($stepProgresses as $progress)
                    @if($progress->notes)
                        @php 
                            $responses = json_decode($progress->notes, true); 
                        @endphp
@foreach($responses as $label => $answer)
    <li>
        <strong>{{ ucfirst(str_replace('_', ' ', $label)) }}:</strong>
        @if(is_string($answer) && \Carbon\Carbon::hasFormat($answer, 'Y-m-d'))
            {{ \Carbon\Carbon::parse($answer)->format('m/d/Y') }}
        @elseif(is_array($answer))
            {{ implode(', ', $answer) }}
        @else
            {{ $answer }}
        @endif
    </li>
@endforeach

                    @endif
                @endforeach
            </ul>

            <!-- Missing required fields -->
            @if(!empty($missingFields))
                <div class="missing-fields-container">
                    <strong>Missing Required Fields:</strong>
                    <ul>
                        @foreach($missingFields as $mf)
                            <li class="missing-field-item">{{ $mf }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Show status & completion details -->
            @php
                $progress = $latestProgress;
            @endphp
            @if($progress)
                <div class="progress-details">
                    <strong>Status:</strong> {{ ucfirst($progress->status) }}<br>
                    <small>
                        Completed by: {{ optional($progress->user)->name ?? 'N/A' }}
                        @if($progress->completed_at)
                            on {{ $progress->completed_at->format('M d, Y H:i') }}
                        @endif
                    </small>
                </div>
            @endif
@php
    /**
     * $step->metadata can be:
     *   • an array   – when the Step model has  `protected $casts = ['metadata'=>'array']`
     *   • a JSON string
     *   • an empty string / null
     *
     * Make sure we always treat it as an array _before_ looking for a key,
     * otherwise PHP will throw “Cannot access offset of type string on string”.
     */
    $meta     = is_array($step->metadata)
                ? $step->metadata
                : (is_string($step->metadata) && $step->metadata !== ''
                     ? json_decode($step->metadata, true) ?: []
                     : []);

    $pdfView = $meta['pdf_template'] ?? null;   // ← set in your seeder when the step owns a PDF
@endphp

@if($canWrite)
    <button wire:click="editForm({{ $step->id }})" class="btn-edit-link">
        Modify
    </button>

    {{-- show button only if the step is done _and_ declares a pdf_template --}}
    @if($isStepCompleted && $pdfView)
        <a  href="{{ route('referral.step.pdf', [$referral->id, $step->id]) }}"
            target="_blank"
            class="btn-green">
            Download&nbsp;PDF
        </a>
    @endif
@endif

@endif



        <!-- Comments Section -->
        <div class="comments-section">
            <button wire:click="toggleComments({{ $step->id }})" class="btn-comments-toggle">
                {{ ($showCommentsForStep === $step->id) ? 'Hide' : 'View' }} Comments
                ({{ $step->comments->where('referral_id', $referral->id)->count() }})
            </button>

            @if($showCommentsForStep === $step->id)
                <div class="comments-box">
                    @php
                        $comments = $step->comments
                            ->where('referral_id', $referral->id)
                            ->sortByDesc('created_at');
                    @endphp

                    @forelse($comments as $comment)
                        <div class="comment-item">
                            <div class="comment-header">
                                <strong>{{ $comment->user->name }}</strong>
                                <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="comment-content">{{ $comment->comment }}</p>
                        </div>
                    @empty
                        <p class="comment-empty">No comments yet.</p>
                    @endforelse

                    @if($canWrite)
                        <textarea wire:model.defer="newComment.{{ $step->id }}"
                                  rows="2" class="comment-textarea"
                                  placeholder="Add a comment..."></textarea>

                        <button wire:click="addComment({{ $step->id }})" class="btn-green-small">
                            Post Comment
                        </button>
                    @else
                        <p class="text-xs text-gray-400 italic mt-2">
                            You do not have permission to post comments here.
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Inline Styles (optional to keep here) -->
    <style>
    /* === Container === */
    .form-step-container {
        background-color: #f9fafb;
        border: 1px solid #d1fae5;
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        margin-bottom: 1.5rem;
        transition: all 0.3s ease-in-out;
    }
    .form-step-container:hover {
        transform: scale(1.01);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
    }

    /* === Titles & Labels === */
    .form-step-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #065f46;
        margin-bottom: 1rem;
    }
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    .required-asterisk {
        color: #dc2626;
    }

    /* === Inputs & Textareas === */
    .form-input-field,
    .form-textarea-field {
        width: 100%;
        border-radius: 0.375rem;
        border: 1px solid #d1fae5;
        background-color: #ffffff;
        color: #111827;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: border-color 0.2s ease;
    }
    .form-input-field:focus,
    .form-textarea-field:focus {
        outline: none;
        border-color: #16a34a;
        box-shadow: 0 0 0 1px #16a34a;
    }

    /* === Completed Text === */
    .form-complete-msg {
        color: #15803d;
        font-style: italic;
        margin-bottom: 1rem;
    }
    .form-responses {
        list-style: none;
        padding: 0;
        color: #374151;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    /* Missing fields */
    .missing-fields-container {
        border: 1px solid #fca5a5;
        background-color: #fef2f2;
        padding: 0.75rem;
        border-radius: 0.375rem;
        margin-bottom: 1rem;
        color: #b91c1c;
    }
    .missing-field-item {
        list-style: disc;
        margin-left: 1.5rem;
        margin-bottom: 0.25rem;
    }

    /* === Buttons === */
    .btn-green {
        background-color: #16a34a;
        color: #ffffff;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 0.375rem;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .btn-green:hover {
        background-color: #15803d;
    }
    .btn-green-small {
        background-color: #16a34a;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 0.375rem;
        border: none;
        margin-top: 0.5rem;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .btn-green-small:hover {
        background-color: #15803d;
    }
    .btn-gray {
        background-color: #e5e7eb;
        color: #374151;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 0.375rem;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .btn-gray:hover {
        background-color: #d1d5db;
    }
    .btn-edit-link {
        font-size: 0.875rem;
        color: #15803d;
        background: none;
        border: none;
        cursor: pointer;
        text-decoration: underline;
    }

    /* === Comments Section === */
    .comments-section {
        margin-top: 1.5rem;
        border-top: 1px solid #e5e7eb;
        padding-top: 1rem;
    }
    .btn-comments-toggle {
        font-size: 0.875rem;
        color: #15803d;
        background: none;
        border: none;
        cursor: pointer;
        text-decoration: underline;
    }
    .comments-box {
        margin-top: 1rem;
        background-color: #ffffff;
        border: 1px solid #d1fae5;
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        max-height: 16rem;
        overflow-y: auto;
    }
    .comment-item {
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
    }
    .comment-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.875rem;
        color: #065f46;
    }
    .comment-time {
        font-size: 0.75rem;
        color: #6b7280;
    }
    .comment-content {
        font-size: 0.875rem;
        color: #374151;
    }
    .comment-empty {
        font-style: italic;
        color: #6b7280;
    }
    .comment-textarea {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #d1fae5;
        border-radius: 0.375rem;
        background-color: #f9fafb;
        margin-top: 0.5rem;
        color: #111827;
    }
    </style>
<script>
function sigPad(canvasId, uploadUrl, csrf, existingPath, wire, propPath) {
    return {
        pad:  null,
        path: existingPath,   // bound thumbnail + server value
        busy: false,

        init() {
            const canvas = document.getElementById(canvasId);
            this.resize(canvas);

            this.pad = new SignaturePad(canvas, { penColor: '#0f172a' });
            window.addEventListener('resize', () => this.resize(canvas));

            // if editing, draw the saved signature on canvas (optional)
            if (this.path) {
                const img = new Image();
                img.onload = () => canvas.getContext('2d')
                            .drawImage(img, 0, 0, canvas.width, canvas.height);
                img.src = '/storage/' + this.path;
            }
        },

        resize(c) {
            const r = Math.max(window.devicePixelRatio || 1, 1);
            c.width  = c.offsetWidth  * r;
            c.height = c.offsetHeight * r;
            c.getContext('2d').scale(r, r);
        },

        clear() {
            this.pad.clear();
            this.path = '';
            wire.set(propPath, '');        // tell Livewire immediately
        },

        async upload() {
            if (this.pad.isEmpty()) { alert('Please sign first'); return; }

            this.busy = true;
            try {
                const res = await fetch(uploadUrl, {
                    method : 'POST',
                    headers : {
                        'Content-Type' : 'application/json',
                        'X-CSRF-TOKEN' : csrf,
                    },
                    body : JSON.stringify({
                        data_url : this.pad.toDataURL('image/png')
                    })
                });

                const { path } = await res.json();
                this.path = path;           // for thumbnail
                wire.set(propPath, path);   // 🔑 push straight into Livewire
            } catch {
                alert('Upload failed');
            } finally {
                this.busy = false;
            }
        }
    }
}
</script>







</div>
