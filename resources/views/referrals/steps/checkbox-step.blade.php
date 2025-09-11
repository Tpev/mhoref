@php
    $label = $step->metadata['label'] ?? 'Task Check';

    // Groups logic
    $userGroups  = auth()->user()->group ?? [];
    $writeGroups = $step->group_can_write ?? [];
    $seeGroups   = $step->group_can_see   ?? [];

    $canWrite = !empty(array_intersect($userGroups, $writeGroups));
    $canSee   = $canWrite ? true : !empty(array_intersect($userGroups, $seeGroups));

    $isEditing = $editingFormStep === $step->id;
@endphp

<div id="step-{{ $step->id }}" class="checkbox-step-container {{ $bgClasses }}">
    <!-- Step Header -->
    <div class="checkbox-step-header">
        <h5 class="checkbox-step-title">
            Task: {{ $label }}
        </h5>
        <svg class="checkbox-step-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M13 16h-1v-4h1m0-4h-1M12 2a10 10 0 100 20 10 10 0 000-20z"/>
        </svg>
    </div>

    @if(!$canSee)
        <p class="text-sm text-gray-500 italic">You do not have permission to view details for this step.</p>
    @else
        @if(!$isStepCompleted || $isEditing)
            @if($canWrite)
                <div class="checkbox-step-action">
                    <input
                        type="checkbox"
                        wire:model="checkboxAnswers.{{ $step->id }}"
                        id="checkbox_step_{{ $step->id }}"
                        class="checkbox-input"
                    />
                    <label for="checkbox_step_{{ $step->id }}" class="checkbox-label">
                        Mark as done
                    </label>
                </div>

                <div class="flex gap-2 mt-3">
                    <button wire:click="saveCheckbox({{ $step->id }})" class="btn-green">
                        Confirm
                    </button>

                    @if($isStepCompleted)
                        <button wire:click="$set('editingFormStep', null)" class="btn-gray">
                            Cancel
                        </button>
                    @endif
                </div>
            @else
                <p class="text-xs text-gray-400 italic mt-3">
                    You do not have permission to complete this task.
                </p>
            @endif
        @else
            @if($stepProgresses->isNotEmpty())
                <ul class="completed-steps-list mt-2">
                    @foreach($stepProgresses as $progress)
                        <li class="completed-step-item">
                            <strong>Status:</strong> {{ ucfirst($progress->status) }}<br>
                            <small>
                                Completed by: {{ optional($progress->user)->name ?? 'N/A' }}
                                @if($progress->completed_at)
                                    on {{ $progress->completed_at->format('M d, Y H:i') }}
                                @endif
                            </small>
                            @if($progress->notes)
                                <div class="progress-notes">Notes: {{ $progress->notes }}</div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if($canWrite)
                <button wire:click="editForm({{ $step->id }})" class="btn-edit-link mt-2">
                    Modify
                </button>
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
                              rows="2"
                              class="comment-textarea"
                              placeholder="Add a comment..."></textarea>

                    <button wire:click="addComment({{ $step->id }})"
                            class="btn-green-small mt-2">
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


<style>
/* === Main Container === */
.checkbox-step-container {
    background-color: #f9fafb;
    border: 1px solid #d1fae5;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
    margin-bottom: 1.5rem;
    transition: all 0.3s ease-in-out;
}
.checkbox-step-container:hover {
    transform: scale(1.01);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
}

/* === Header === */
.checkbox-step-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.checkbox-step-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #14532d;
}
.checkbox-step-icon {
    width: 1.5rem;
    height: 1.5rem;
    color: #16a34a;
}

/* === Checkbox === */
.checkbox-step-action {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.checkbox-input {
    width: 1.25rem;
    height: 1.25rem;
    accent-color: #16a34a;
}
.checkbox-label {
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
}

/* === Completed Steps === */
.completed-steps-list {
    list-style: none;
    padding-left: 0;
}
.completed-step-item {
    background-color: #ecfdf5;
    color: #065f46;
    padding: 0.75rem;
    border: 1px solid #34d399;
    border-radius: 0.375rem;
    margin-bottom: 0.75rem;
}
.progress-notes {
    margin-top: 0.5rem;
    font-size: 0.85rem;
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
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
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
    margin-bottom: 0.25rem;
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
    font-size: 0.875rem;
    background-color: #f9fafb;
    margin-top: 0.5rem;
    color: #111827;
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
</style>
