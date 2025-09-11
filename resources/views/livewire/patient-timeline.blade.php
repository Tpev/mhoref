<!-- resources/views/livewire/patient-timeline.blade.php -->
<div class="py-10 px-4 min-h-screen bg-gradient-to-br from-blue-50 to-blue-100">
    <!-- Main Container -->
    <div class="max-w-4xl mx-auto bg-white shadow-2xl rounded-xl p-8 relative overflow-hidden">
        <!-- Subtle Texture (optional) -->
        <div
            class="absolute inset-0 opacity-5 pointer-events-none bg-no-repeat bg-center bg-cover"
            style="background-image: url('https://www.transparenttextures.com/patterns/white-carbn.png');"
        ></div>

        <!-- Title -->
        <h2 class="text-3xl font-extrabold mb-8 text-gray-800 text-center uppercase tracking-wider">
            Patient Timeline
        </h2>

        <!-- Timeline Container -->
        <div class="relative pl-8">
            <!-- Main vertical line (faint) -->
            <div class="absolute left-4 top-0 w-1 bg-gradient-to-b from-blue-300 to-green-300 h-full rounded-full opacity-40"></div>

            <ul class="space-y-8">
                @foreach($events as $index => $event)
                    <li class="relative group">
                        <!-- Event Card -->
                        <div
                            class="
                                flex items-start transition-all transform
                                group-hover:scale-[1.01] group-hover:-translate-y-1
                                hover:shadow-lg bg-gray-50 border border-gray-200
                                rounded-lg p-5
                            "
                        >
                            <!-- Icon & Connector -->
                            <div class="flex flex-col items-center mr-6">
                                <!-- Icon -->
                                <div
                                    class="
                                        w-10 h-10 flex items-center justify-center
                                        rounded-full shadow-md
                                        transition-transform duration-300
                                        group-hover:scale-110
                                        {{ $event['status'] === 'completed' ? 'bg-green-500' : ($event['status'] === 'in-progress' ? 'bg-blue-500' : 'bg-gray-400') }}
                                        text-white
                                    "
                                >
                                    @if($event['icon'] === 'user-circle')
                                        <!-- User Circle Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0 0H9m3 0h3" />
                                        </svg>
                                    @elseif($event['icon'] === 'clipboard-check')
                                        <!-- Clipboard Check Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12l2 2 4-4m5 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    @elseif($event['icon'] === 'heart')
                                        <!-- Heart Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor"
                                             viewBox="0 0 20 20">
                                            <path
                                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                            />
                                        </svg>
                                    @elseif($event['icon'] === 'calendar')
                                        <!-- Calendar Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    @endif
                                </div>

                                <!-- Connector line (only if not the last item) -->
                                @if(!$loop->last)
                                    <div
                                        class="w-1 flex-1
                                            {{ $event['status'] === 'completed' ? 'bg-green-500' : ($event['status'] === 'in-progress' ? 'bg-blue-500' : 'bg-gray-300') }}
                                        "
                                        style="
                                            margin-top: 0.5rem;
                                            border-left: 2px
                                                {{ $event['status'] === 'pending' ? 'dashed' : 'solid' }}
                                                {{ $event['status'] === 'completed' ? '#10B981' : ($event['status'] === 'in-progress' ? '#3B82F6' : '#D1D5DB') }};
                                        "
                                    ></div>
                                @endif
                            </div>

                            <!-- Event Details -->
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <span class="text-sm font-bold text-gray-800">{{ $event['date'] }}</span>
                                    @if($event['status'] === 'completed')
                                        <span
                                            class="ml-3 text-xs font-semibold uppercase text-green-700 bg-green-100 px-2 py-1 rounded"
                                        >
                                            Completed
                                        </span>
                                    @elseif($event['status'] === 'in-progress')
                                        <span
                                            class="ml-3 text-xs font-semibold uppercase text-blue-700 bg-blue-100 px-2 py-1 rounded"
                                        >
                                            In Progress
                                        </span>
                                    @elseif($event['status'] === 'pending')
                                        <span
                                            class="ml-3 text-xs font-semibold uppercase text-gray-700 bg-gray-200 px-2 py-1 rounded"
                                        >
                                            Pending
                                        </span>
                                    @endif
                                </div>
                                <p class="text-gray-700">
                                    {{ $event['description'] }}
                                </p>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
