<!-- resources/views/referrals/steps/action-step.blade.php -->

@php
    // We assume the parent passes these variables:
    // $step, $stepProgresses, $bgClasses, $isStepCompleted, $isStepNext, etc.
@endphp

<div id="step-{{ $step->id }}" class="p-3 rounded shadow-sm transition-all {{ $bgClasses }} mb-2">
    <div class="flex items-center text-sm font-semibold text-indigo-600 dark:text-indigo-300 mb-2">
        <!-- Step Icon -->
        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h8M4 18h4" />
        </svg>
        {{ $step->name }}
        <span class="ml-2 text-xs text-gray-400">(ID: {{ $step->id }})</span>
    </div>

    @if($stepProgresses->isNotEmpty())
        <ul class="space-y-2">
            @foreach($stepProgresses as $progress)
                <li class="text-sm text-gray-700 dark:text-gray-200">
                    <strong>Status:</strong> {{ $progress->status }}
                    <br>
                    <small class="text-gray-500 dark:text-gray-400">
                        Completed by User #{{ $progress->completed_by ?? 'N/A' }}
                        @if($progress->completed_at)
                            on {{ $progress->completed_at->format('M d, Y H:i') }}
                        @else
                            (No date)
                        @endif
                    </small>
                    @if($progress->notes)
                        <div class="mt-1 text-xs italic text-gray-600 dark:text-gray-300">
                            Notes: {{ $progress->notes }}
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <p class="italic text-gray-500 text-sm mt-1">
            No progress recorded for this step yet.
        </p>
    @endif
	
	    <!-- Comments Section -->
    <div class="mt-4 border-t pt-3">
        <button wire:click="toggleComments({{ $step->id }})"
                class="text-sm text-indigo-600 hover:underline">
            {{ ($showCommentsForStep === $step->id) ? 'Hide' : 'View' }} Comments ({{ $commentsCount = $step->comments->where('referral_id', $referral->id)->count() }})
        </button>

        @if($showCommentsForStep === $step->id)
            <div class="mt-3 bg-gray-100 dark:bg-gray-700 p-3 rounded shadow-inner space-y-2 max-h-64 overflow-y-auto">
                @forelse($step->comments->where('referral_id', $referral->id)->sortByDesc('created_at') as $comment)
                    <div class="text-sm border-b border-gray-200 dark:border-gray-600 pb-2">
                        <strong>{{ $comment->user->name }}</strong>
                        <span class="text-xs text-gray-500 dark:text-gray-300">{{ $comment->created_at->diffForHumans() }}</span>
                        <p>{{ $comment->comment }}</p>
                    </div>
                @empty
                    <p class="text-xs italic text-gray-500">No comments yet.</p>
                @endforelse

                <textarea wire:model.defer="newComment.{{ $step->id }}" rows="2"
                          class="mt-2 w-full border rounded dark:bg-gray-800"
                          placeholder="Add a comment..."></textarea>

                <button wire:click="addComment({{ $step->id }})"
                        class="mt-2 bg-indigo-600 text-white text-sm px-3 py-1 rounded hover:bg-indigo-700">
                    Post Comment
                </button>
            </div>
        @endif
    </div>
</div>
