@php
    $uploadLabel  = $step->metadata['upload_label'] ?? 'Upload Files';
    $maxFiles     = $step->metadata['max_files'] ?? 5;
    $allowedMimes = $step->metadata['allowed_mimes'] ?? ['pdf', 'jpg', 'png'];

    // Check user groups for read/write access
    $userGroups   = auth()->user()->group ?? [];
    $writeGroups  = $step->group_can_write ?? [];
    $seeGroups    = $step->group_can_see   ?? [];

    $canWrite = !empty(array_intersect($userGroups, $writeGroups));
    $canSee   = $canWrite ? true : !empty(array_intersect($userGroups, $seeGroups));
@endphp

<div  id="step-{{ $step->id }}" class="upload-step-container {{ $bgClasses }}">
    <!-- Step Header -->
    <div class="upload-step-header">
        <svg class="upload-step-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        <span>{{ $uploadLabel }}</span>
        <span class="upload-step-id">(ID: {{ $step->id }})</span>
    </div>

    @if(!$canSee)
        <!-- No permission to view details -->
        <p class="text-sm text-gray-500 italic mt-3">
            You do not have permission to view details for this step.
        </p>
    @else
        <!-- The user can see the step details -->
        @if(!$isStepCompleted)
            <!-- Show upload UI only if user can write. Otherwise read-only. -->
            @if($canWrite)
                <!-- File Upload -->
                <div class="mt-3">
                    <input
                        type="file"
                        wire:model="uploadFiles.{{ $step->id }}"
                        id="upload_step_{{ $step->id }}"
                        multiple
                        class="upload-input"
                    />
                    @error("uploadFiles.{$step->id}.*") 
                        <span class="upload-error">{{ $message }}</span> 
                    @enderror
                    @error("uploadFiles.{$step->id}") 
                        <span class="upload-error">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Upload button -->
                <button
                    wire:click="saveUpload({{ $step->id }})"
                    class="btn-green mt-4"
                >
                    Upload Files
                </button>

                <!-- Upload progress preview -->
                @if (isset($uploadFiles[$step->id]) && count($uploadFiles[$step->id]) > 0)
                    <div class="upload-progress-container">
                        @foreach ($uploadFiles[$step->id] as $file)
                            <div class="upload-progress-item">
                                <svg class="spinner-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span class="upload-file-name">{{ $file->getClientOriginalName() }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <!-- Not completed yet, but user cannot write -->
                <p class="text-xs text-gray-400 italic mt-3">
                    You do not have permission to upload files on this step.
                </p>
            @endif

        @else
            <!-- Completed Step -->
            @if($stepProgresses->isNotEmpty())
                <ul class="completed-steps-list mt-2">
                    @foreach($stepProgresses as $progress)
                        <li class="completed-step-item">
                            <strong>Status:</strong> {{ $progress->status }}
                            <br>
                            <small>
                               Completed by: {{ optional($progress->user)->name ?? 'N/A' }}
                                @if($progress->completed_at)
                                    on {{ $progress->completed_at->format('M d, Y H:i') }}
                                @else
                                    (No date)
                                @endif
                            </small>

                            @if($progress->notes)
                                <div class="progress-notes">
                                    Notes: {{ $progress->notes }}
                                </div>
                            @endif

                            @if($progress->uploadedFiles && $progress->uploadedFiles->isNotEmpty())
                                <ul class="uploaded-files-list">
                                    @foreach($progress->uploadedFiles as $file)
                                        <li>
                                            <a href="{{ Storage::url($file->path) }}" target="_blank" class="uploaded-file-link">
                                                {{ $file->original_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="no-progress-text">No progress recorded for this step yet.</p>
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

                <!-- If user can write => can post comments, else read-only -->
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

<style>
/* === Upload Step === */
.upload-step-container {
    background-color: #f9fafb;
    border: 1px solid #d1fae5;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
    margin-bottom: 1.5rem;
    transition: all 0.3s ease-in-out;
}
.upload-step-container:hover {
    transform: scale(1.01);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
}
.upload-step-header {
    display: flex;
    align-items: center;
    font-size: 1rem;
    font-weight: 600;
    color: #065f46;
}
.upload-step-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: #16a34a;
    margin-right: 0.5rem;
}
.upload-step-id {
    margin-left: auto;
    font-size: 0.75rem;
    color: #6b7280;
}

/* === Upload Field === */
.upload-input {
    display: block;
    width: 100%;
    padding: 0.5rem;
    font-size: 0.875rem;
    color: #065f46;
    border-radius: 0.375rem;
    border: 1px solid #d1fae5;
    background-color: #ffffff;
}
.upload-input::file-selector-button {
    background-color: #dcfce7;
    color: #15803d;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    font-weight: 500;
}
.upload-error {
    color: #dc2626;
    font-size: 0.75rem;
}

/* === Upload Progress === */
.upload-progress-container {
    margin-top: 1rem;
}
.upload-progress-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.upload-file-name {
    font-size: 0.875rem;
    color: #374151;
}
.spinner-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: #15803d;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* === Completed Step Display === */
.completed-steps-list {
    list-style: none;
    padding: 0;
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
    font-style: italic;
    margin-top: 0.5rem;
}
.uploaded-files-list {
    list-style: disc;
    margin-top: 0.5rem;
    padding-left: 1rem;
}
.uploaded-file-link {
    color: #065f46;
    text-decoration: underline;
    font-weight: 500;
}
.no-progress-text {
    font-style: italic;
    color: #6b7280;
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
