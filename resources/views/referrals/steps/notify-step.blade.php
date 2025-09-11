@php
    $question = 'Notify Family about Discharge';
    $latestProgress = $stepProgresses->sortByDesc('id')->first();
    $savedData = $latestProgress && $latestProgress->notes
        ? json_decode($latestProgress->notes, true)
        : [];

    $familyName = $savedData['family_name'] ?? '';
    $familyEmail = $savedData['family_email'] ?? '';
    $customNote = $savedData['custom_note'] ?? '';

    $userGroups = auth()->user()->group ?? [];
    $writeGroups = $step->group_can_write ?? [];
    $seeGroups   = $step->group_can_see ?? [];

    $canWrite = !empty(array_intersect($userGroups, $writeGroups));
    $canSee = $canWrite || !empty(array_intersect($userGroups, $seeGroups));
@endphp

<div id="step-{{ $step->id }}" class="step-container {{ $bgClasses }}">
    <h5 class="step-question">{{ $question }}</h5>

    @if(!$canSee)
        <p class="text-gray-500 text-sm italic">
            You do not have permission to view details for this step.
        </p>
    @else
        @if(!$isStepCompleted)
            @if($canWrite)
                <div class="space-y-2">
                    <input type="text"
                           wire:model.defer="notifyData.{{ $step->id }}.family_name"
                           placeholder="Family Member Name"
                           class="input-field w-full">

                    <input type="email"
                           wire:model.defer="notifyData.{{ $step->id }}.family_email"
                           placeholder="Family Member Email"
                           class="input-field w-full">

                    <textarea wire:model.defer="notifyData.{{ $step->id }}.custom_note"
                              placeholder="Add a custom message (optional)"
                              class="input-field w-full"
                              rows="3"></textarea>

                    <button wire:click="sendFamilyNotification({{ $step->id }})"
                            class="btn-green">
                        Send Notification
                    </button>
                </div>
            @else
                <p class="text-sm italic text-gray-500">
                    You do not have permission to notify the family.
                </p>
            @endif
        @else
            @foreach($stepProgresses as $progress)
                <div class="step-completed-block">
                    <div class="progress-details">
                        <strong>Status:</strong> {{ ucfirst($progress->status) }}<br>
                        <small>
                            Completed by: {{ optional($progress->user)->name ?? 'N/A' }}
                            @if($progress->completed_at)
                                on {{ $progress->completed_at->format('M d, Y H:i') }}
                            @endif
                        </small>

                        @if($progress->notes)
                            @php
                                $notes = json_decode($progress->notes, true);
                                $name = $notes['family_name'] ?? '—';
                                $email = $notes['family_email'] ?? '—';
                                $customNote = $notes['custom_note'] ?? null;
                            @endphp

                            <div class="progress-notes mt-2">
                                <strong>Notes:</strong>
                                Notification sent to {{ $name }} ({{ $email }})
                                @if($customNote)
                                    <div class="mt-1 italic text-gray-700">
                                        Custom Message: "{{ $customNote }}"
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    @endif

    <!-- Comments -->
    <div class="comments-section">
        <button wire:click="toggleComments({{ $step->id }})" class="btn-comments-toggle">
            {{ ($showCommentsForStep === $step->id) ? 'Hide' : 'View' }} Comments
            ({{ $step->comments->where('referral_id', $referral->id)->count() }})
        </button>

        @if($showCommentsForStep === $step->id)
            <div class="comments-box">
                @forelse($step->comments->where('referral_id', $referral->id)->sortByDesc('created_at') as $comment)
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
                    <textarea wire:model.defer="newComment.{{ $step->id }}" rows="2"
                              class="comment-textarea"
                              placeholder="Add a comment...">
                    </textarea>

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
