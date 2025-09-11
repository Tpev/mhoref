<div class="flex justify-end mb-4">
    <button wire:click="create" class="btn-create-referral">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M12 4.5a.75.75 0 01.75.75v6h6a.75.75 0 010 1.5h-6v6a.75.75 0 01-1.5 0v-6h-6a.75.75 0 010-1h6v-6A.75.75 0 0112 4.5z"
                  clip-rule="evenodd" />
        </svg>
        Create Referral
    </button>
<style>
    .btn-create-referral {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem; /* text-sm */
        font-weight: 600; /* font-semibold */
        color: white;
        background-color: #16a34a; /* green-600 */
        border-radius: 0.375rem; /* rounded-md */
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: background-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: none;
    }

    .btn-create-referral:hover {
        background-color: #15803d; /* green-700 */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-create-referral:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.5); /* ring-green-500 */
    }

    .btn-create-referral svg {
        width: 1rem;
        height: 1rem;
        fill: currentColor;
    }
</style>	
</div>


