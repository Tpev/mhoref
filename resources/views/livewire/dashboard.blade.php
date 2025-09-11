<div class="py-8 px-4 max-w-7xl mx-auto space-y-6">
    <!-- Metrics Card -->
    <section
        class="bg-gradient-to-r from-green-400 to-blue-500 text-white p-6 rounded-xl shadow-md hover:shadow-xl transition transform hover:-translate-y-1"
    >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-4xl font-extrabold">
                    {{ $referralsInProgress }}
                </div>
                <div class="uppercase text-xs tracking-wide">Referrals in Progress</div>
            </div>
            <div>
                <div class="text-4xl font-extrabold">
                    {{ $dischargeToday }}
                </div>
                <div class="uppercase text-xs tracking-wide">Discharge Today</div>
            </div>
            <div>
                <div class="text-4xl font-extrabold">
                    {{ $dischargeTomorrow }}
                </div>
                <div class="uppercase text-xs tracking-wide">Discharge Tomorrow</div>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-6">
		<livewire:dashboard.pending-signatures-widget />
            <!-- My Tasks Card -->
            <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition">
                <h3 class="text-xl font-semibold border-b pb-3 mb-4">My Tasks</h3>
                <ul class="space-y-3">
                    @forelse($tasks as $task)
                        <li
                            class="flex justify-between items-center bg-gray-50 p-3 rounded hover:bg-green-50 transition"
                        >
                            <div>
                                <div class="font-medium">
                                    {{ $task['step_name'] }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Patient: {{ $task['patient_name'] }}
                                    @if($task['discharge_date'] !== '—')
                                        • Discharge: {{ $task['discharge_date'] }}
                                    @endif
                                </div>
                            </div>
                            <a
                                href="{{ route('referrals.workflow.show', ['id' => $task['referral_id']]) }}?step={{ $task['step_id'] }}"
                                class="text-green-600 hover:underline"
                            >
                                Go to Step
                            </a>
                        </li>
                    @empty
                        <li class="text-gray-500 italic">No tasks found.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Upcoming Discharges -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <h3 class="p-5 text-xl font-semibold border-b">Upcoming Discharges</h3>
                <table class="min-w-full">
                    <thead class="bg-green-100 text-left text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-2">Patient</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($upcomingDischarges as $dis)
                            <tr class="hover:bg-green-50 transition">
                                <td class="px-4 py-2">{{ $dis['patient_name'] }}</td>
                                <td class="px-4 py-2">{{ $dis['discharge_date'] }}</td>
                                <td class="px-4 py-2 text-yellow-700">{{ $dis['status'] }}</td>
                                <td class="px-4 py-2">
                                    <a href="#"
                                       class="text-green-600 hover:underline"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-center text-gray-500 italic">
                                    No upcoming discharges.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Alerts & Notifications -->
<div class="bg-white shadow rounded-lg p-6" x-data="{ open: true }">
    <h3 class="text-xl font-semibold mb-3">
        Alerts & Notifications ({{ $unreadNotifications->count() }})
    </h3>

    <!-- You can keep the toggle button if you still want a way to manually close/open -->
    <button
        @click="open = !open"
        class="bg-gray-100 text-gray-700 px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-200 transition"
    >
        Toggle Notifications
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        class="mt-4 w-full bg-white border border-gray-200 shadow-2xl rounded-lg overflow-hidden relative z-50"
        style="display: none;"
    >
        <div class="max-h-96 overflow-y-auto">
            <ul class="divide-y">
                @forelse($unreadNotifications as $notif)
                    <li class="px-6 py-4 hover:bg-gray-100 flex justify-between items-start">
                        <a href="{{ route('referrals.workflow.show', $notif->data['referral_id'] ?? 0) }}"
                           class="block flex-1">
                            <p class="text-lg font-semibold text-gray-800">
                                {{ $notif->data['message'] ?? 'Notification' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Referral ID: #{{ $notif->data['referral_id'] ?? 'N/A' }}
                            </p>
                            <span class="text-xs text-gray-400">
                                {{ $notif->created_at->format('F j, Y, g:i a') }}
                                ({{ $notif->created_at->diffForHumans() }})
                            </span>
                        </a>
                        <button
                            wire:click="markAsRead('{{ $notif->id }}')"
                            class="ml-6 text-sm text-blue-500 hover:text-blue-700 whitespace-nowrap"
                        >
                            Mark as read
                        </button>
                    </li>
                @empty
                    <li class="px-6 py-6 text-md text-gray-500">
                        No new notifications.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>


<!-- Quick Actions -->
<div class="bg-white shadow rounded-lg p-6">
    <h3 class="text-xl font-semibold mb-3">Quick Actions</h3>
    <div class="space-y-2">
        <!-- View All Discharges -->
        <button
            wire:click="viewAllDischarges"
            class="block w-full bg-gray-100 rounded py-2 px-4 text-center hover:bg-gray-200 transition"
        >
            View All Discharges
        </button>
    </div>
</div>

        </div>
    </section>
</div>
