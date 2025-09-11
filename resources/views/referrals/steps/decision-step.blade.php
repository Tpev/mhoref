<div> {{-- SINGLE ROOT WRAPPER FOR LIVEWIRE --}}

    <div id="step-{{ $step->id }}" class="step-container {{ $bgClasses }}">
        @php
            $options  = $step->metadata['options'] ?? [];
            $question = $step->metadata['question'] ?? 'Decision';
            $comments = $step->comments->where('referral_id', $referral->id)->sortByDesc('created_at');
            $userGroups = auth()->user()->group ?? [];
            $writeGroups = $step->group_can_write ?? [];
            $seeGroups   = $step->group_can_see ?? [];

            $canWrite = !empty(array_intersect($userGroups, $writeGroups));
            $canSee   = $canWrite || !empty(array_intersect($userGroups, $seeGroups));
        @endphp

        <h5 class="step-question">{{ $question }}</h5>

        @if(!$canSee)
            <p class="text-gray-500 text-sm italic">You do not have permission to view details for this step.</p>
        @else
            @if(!$isStepCompleted || ($editingFormStep === $step->id))
                @if(!empty($options))
                    <div class="step-options">
                        @foreach($options as $option)
                            <label class="step-option">
                                <input type="radio"
                                       wire:model="decisionAnswers.{{ $step->id }}"
                                       value="{{ $option }}"
                                       class="step-radio"
                                       @if(!$canWrite) disabled @endif>
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex gap-2 mt-2">
                        @if($canWrite)
                            <button wire:click="saveDecision({{ $step->id }})" class="btn-green">
                                Save Decision
                            </button>
                        @endif

                        @if($isStepCompleted)
                            <button type="button"
                                    wire:click="$set('editingFormStep', null)"
                                    class="btn-gray">
                                Cancel
                            </button>
                        @endif
                    </div>
                @else
                    <p class="step-no-options">No options available.</p>
                @endif
            @else
                {{-- COMPLETED VIEW --}}
                @php
                    $latestProgress = $stepProgresses->sortByDesc('id')->first();
                @endphp

                @if($latestProgress && !empty($latestProgress->notes))
                    <div class="decision-final-answer">
                        <span class="answer-value">{{ $latestProgress->notes }}</span>
                    </div>
                @endif

                <div class="progress-details">
                    <strong>Status:</strong> {{ ucfirst($latestProgress->status ?? '-') }}<br>
                    <small>
                        Completed by: {{ optional($latestProgress->user)->name ?? 'N/A' }}
                        @if($latestProgress->completed_at)
                            on {{ $latestProgress->completed_at->format('M d, Y H:i') }}
                        @endif
                    </small>
                </div>

                @if($canWrite)
                    <button wire:click="editForm({{ $step->id }})" class="btn-edit-link mt-2">
                        Modify
                    </button>
                @endif
            @endif
        @endif

        {{-- Comments --}}
        <div class="comments-section">
            <button wire:click="toggleComments({{ $step->id }})" class="btn-comments-toggle">
                {{ ($showCommentsForStep === $step->id) ? 'Hide' : 'View' }} Comments
                ({{ $step->comments->where('referral_id', $referral->id)->count() }})
            </button>

            @if($showCommentsForStep === $step->id)
                <div class="comments-box">
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
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>


<style>
/* === Container === */
.step-container {
    background-color: #f9fafb;
    border: 1px solid #e2e8f0;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
    margin-bottom: 1.5rem;
    transition: all 0.3s ease-in-out;
}
.step-container:hover {
    transform: scale(1.01);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
}

/* === Question === */
.step-question {
    font-size: 1.25rem;
    font-weight: 600;
    color: #14532d;
    margin-bottom: 1rem;
}

/* === Options === */
.step-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.step-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background-color: #ffffff;
    border: 1px solid #d1fae5;
    border-radius: 0.375rem;
    transition: background-color 0.2s ease, border-color 0.2s ease;
    cursor: pointer;
}
.step-option:hover {
    background-color: #f0fdf4;
    border-color: #16a34a;
}
.step-radio {
    width: 1.25rem;
    height: 1.25rem;
    accent-color: #16a34a;
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
.step-no-options {
    font-style: italic;
    color: #6b7280;
    font-size: 0.875rem;
}

.step-completed-block {
    margin-bottom: 1rem;
}

/* Large, prominent display for the chosen answer */
.decision-final-answer {
    font-size: 1.125rem;  /* slightly larger than normal text */
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #065f46;       /* teal-ish color */
    display: flex;
    align-items: center;
}
.answer-label {
    margin-right: 0.5rem;
}
.answer-value {
    background-color: #d1fae5; /* light green background */
    padding: 0.3rem 0.6rem;
    border-radius: 0.375rem;
    font-weight: 700;
}

/* Smaller details for status, user, notes */
.progress-details {
    background-color: #ecfdf5;
    color: #065f46;
    padding: 0.75rem;
    border: 1px solid #34d399;
    border-radius: 0.375rem;
}
.progress-notes {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    font-style: italic;
    color: #374151;
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
