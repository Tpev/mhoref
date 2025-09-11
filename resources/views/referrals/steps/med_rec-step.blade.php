<div> <!-- SINGLE ROOT DIV -->

    <div id="step-{{ $step->id }}" class="medrec-step-container {{ $bgClasses }}">
        @php
            $isStepCompleted = $stepProgresses->where('status','completed')->isNotEmpty();
            $canWrite        = $canWrite ?? true;
        @endphp

        {{-- ─────────────────────────────────── EDIT (or first-run) ────────────────────────────── --}}
        @if(!$isStepCompleted || ($editingFormStep === $step->id))
            <h4 class="step-title">Medication Reconciliation</h4>

            {{-- OCR Upload --}}
            <form onsubmit="handleOcrUpload(event)" enctype="multipart/form-data" class="mb-4">
                @csrf
                <label for="ocrImage" class="block text-sm font-medium text-gray-700">Scan prescription image (OCR)</label>
                <input type="file" name="image" id="ocrImage" accept="image/*" required class="block w-full border border-gray-300 rounded mt-1 mb-2">
                <button type="submit" class="btn-green">Extract Meds from Image</button>
            </form>

            {{-- Medication input sections --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Facility list -->
                <div>
                    <h5 class="medrec-subtitle">Facility Medication List</h5>
                    <textarea id="facilityMedList"
                              wire:model.defer="facilityList"
                              class="medrec-textarea" rows="10"></textarea>
                </div>

                <!-- Epic list -->
                <div>
                    <h5 class="medrec-subtitle">Epic Medication List</h5>
                    <textarea id="epicMedList"
                              wire:model.defer="epicList"
                              class="medrec-textarea" rows="10"></textarea>
                </div>

                <!-- Reconciliation panel -->
                <div>
                    <h5 class="medrec-subtitle">Reconciliation</h5>
                    <div id="comparisonResults" class="medrec-results-area">
                        <p class="text-gray-500">
                            Differences appear here after “Compare Lists”
                        </p>
                    </div>

                    @if($canWrite)
                        <button type="button"
                                onclick="compareLists()"
                                class="medrec-button bg-green-600 hover:bg-green-700">
                            Compare Lists
                        </button>
                    @endif
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-4 mt-4">
                @if($canWrite)
                    <button type="button"
                            onclick="saveAndMarkComplete({{ $step->id }})"
                            class="btn-green">
                        Save&nbsp;&amp;&nbsp;Mark&nbsp;Completed
                    </button>
                @endif

                <button wire:click="$set('editingFormStep', null)"
                        class="btn-gray">
                    Cancel
                </button>
            </div>

        {{-- ─────────────── COMPLETED VIEW (read-only) ─────────────── --}}
        @else
            <p class="form-complete-msg">Medication Reconciliation has been completed.</p>

            @php
                $latestProgress = $stepProgresses->sortByDesc('id')->first();
                $savedData = $latestProgress && $latestProgress->notes
                           ? json_decode($latestProgress->notes, true)
                           : [];
            @endphp

            @if(!empty($savedData['final_meds']))
                <div class="final-meds-list mt-4">
                    <h5 class="medrec-subtitle mb-2">Final Medication List</h5>

                    <ul class="divide-y divide-gray-300 rounded-md border border-gray-200 overflow-hidden">
                        @foreach($savedData['final_meds'] as $medItem)
                            @php
                                $text  = is_array($medItem) ? ($medItem['text'] ?? '') : $medItem;
                                $isPmp = is_array($medItem) ? !empty($medItem['pmp'])   : false;
                            @endphp

                            <li class="p-3 flex items-center justify-between bg-white hover:bg-gray-50 transition-colors">
                                <span class="text-sm font-medium text-gray-700">{{ $text }}</span>
                                @if($isPmp)
                                    <span class="ml-2 inline-flex items-center space-x-1 text-red-600 text-xs font-semibold">
                                        <i class="fa-solid fa-triangle-exclamation"></i><span>PMP</span>
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($latestProgress)
                <div class="progress-details mt-2">
                    <strong>Status:</strong> {{ ucfirst($latestProgress->status) }}<br>
                    <small>
                        Completed by: {{ optional($latestProgress->user)->name ?? 'N/A' }}
                        @if($latestProgress->completed_at)
                            on {{ $latestProgress->completed_at->format('M d, Y H:i') }}
                        @endif
                    </small>
                </div>
            @endif

            @if($canWrite)
                <button wire:click="editMedRec({{ $step->id }})"
                        class="btn-edit-link">
                    Modify
                </button>
            @endif
        @endif

        {{-- ──────────────────────── Comments ─────────────────────── --}}
        <div class="comments-section">
            <button wire:click="toggleComments({{ $step->id }})"
                    class="btn-comments-toggle">
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
                                  placeholder="Add a comment…"></textarea>
                        <button wire:click="addComment({{ $step->id }})"
                                class="btn-green-small">
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

    <!-- Styles scoped to this partial -->
    <style>
        .medrec-step-container {
            background-color: #f9fafb;
            border: 1px solid #d1fae5;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .step-title      { font-size: 1.125rem; font-weight: 600; color: #065f46; margin-bottom: 1rem; }
        .medrec-subtitle { font-size: 1rem;    font-weight: 600; color: #374151; margin-bottom: 0.75rem; }
        .medrec-textarea {
            width: 100%; border: 1px solid #ccc; border-radius: 0.375rem; padding: 0.5rem;
        }
        .medrec-results-area {
            border: 1px solid #ccc; background-color: #ffffff; border-radius: 0.375rem;
            min-height: 15rem; max-height: 24rem; overflow-y: auto; padding: 0.5rem;
        }
        .medrec-button {
            display: inline-block; color: #ffffff; font-weight: 600;
            padding: 0.5rem 0.75rem; border-radius: 0.375rem; margin-right: 0.5rem; margin-top: 0.5rem;
        }
    </style>

    <script>
    function handleOcrUpload(event) {
        event.preventDefault();

        const form = event.target;
        const input = form.querySelector('input[type="file"]');
        const file = input.files[0];

        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);

        fetch("{{ route('ocr.extract') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.extracted_meds && data.extracted_meds.length > 0) {
                const list = data.extracted_meds.join('\n');
                document.getElementById('facilityMedList').value = list;
            } else {
                alert('No medications detected.');
            }
        })
        .catch(error => {
            console.error('OCR extraction failed:', error);
            alert('OCR extraction failed.');
        });
    }
    </script>
</div> <!-- SINGLE ROOT DIV ends -->
