<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="text-gray-600 hover:text-gray-800 focus:outline-none px-3 py-2 rounded-md border">
        Notifications ({{ $notifications->count() }})
    </button>

    <div x-show="open" @click.away="open = false"
         class="absolute right-0 mt-2 w-[600px] bg-white shadow-2xl rounded-lg overflow-hidden z-50">
        <div class="max-h-[600px] overflow-y-auto">
            <ul class="divide-y">
                @forelse($notifications as $notif)
                    <li class="px-6 py-4 hover:bg-gray-100 flex justify-between items-start">
                        <a href="{{ route('referrals.workflow.show', $notif->data['referral_id']) }}" class="block flex-1">
                            <p class="text-lg font-semibold text-gray-800">{{ $notif->data['message'] }}</p>
                            <p class="text-md text-gray-600">Referral ID: #{{ $notif->data['referral_id'] }}</p>
                            <span class="text-sm text-gray-400">{{ $notif->created_at->format('F j, Y, g:i a') }} ({{ $notif->created_at->diffForHumans() }})</span>
                        </a>
                        <button wire:click="markAsRead('{{ $notif->id }}')"
                                class="ml-6 text-sm text-blue-500 hover:text-blue-700 whitespace-nowrap">
                            Mark as read
                        </button>
                    </li>
                @empty
                    <li class="px-6 py-6 text-md text-gray-500">No new notifications.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>