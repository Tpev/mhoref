<div class="bg-white shadow rounded-lg p-6 space-y-4">
    <h3 class="text-xl font-semibold mb-2 flex items-center">
        My Pending Signatures
        <span class="ml-2 inline-flex items-center justify-center
                     w-6 h-6 text-xs font-bold rounded-full
                     bg-emerald-600 text-white">
            {{ $this->requests->count() }}
        </span>
    </h3>

    @forelse($this->requests as $req)
        {{-- reusable card component --}}
        <livewire:dashboard.pending-signature-card :request="$req" :key="$req->id" />
    @empty
        <p class="italic text-sm text-gray-500">
            Nothing waiting for your signature 🎉
        </p>
    @endforelse
</div>
