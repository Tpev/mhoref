<div class="max-w-5xl mx-auto px-4 py-8 space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">OCR Test</h1>
            <p class="text-sm text-gray-500">Upload a PDF, choose a template, we’ll hit the Python API and show the extracted names & DOBs.</p>
        </div>
        <x-ts-badge>
            API: {{ config('services.ocr.url') ?? env('OCR_API_URL') }}
        </x-ts-badge>
    </div>

    {{-- Upload Card --}}
    <x-ts-card class="space-y-4">
        <form wire:submit.prevent="submit" class="space-y-4" enctype="multipart/form-data">
            {{-- Template select --}}
            <div>
                <label for="template" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    Template
                </label>
                <select
                    id="template"
                    wire:model="template"
                    class="w-full rounded border-gray-300 dark:bg-slate-800 dark:text-slate-100"
                >
                    <option value="Athena">Athena</option>
                    <option value="Intermed">Intermed</option>
                    <option value="Northern Light">Northern Light</option>
                    <option value="Maine General">Maine General</option>
                </select>
                @error('template')
                    <x-ts-alert color="danger" class="mt-2">{{ $message }}</x-ts-alert>
                @enderror
            </div>

            <div>
                <label for="pdf" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    PDF file
                </label>
                <input
                    id="pdf"
                    type="file"
                    accept="application/pdf"
                    wire:model="pdf"
                    class="block w-full text-sm file:mr-4 file:py-2 file:px-4
                           file:rounded file:border-0 file:text-sm
                           file:bg-slate-100 file:text-slate-700
                           hover:file:bg-slate-200 dark:file:bg-slate-700 dark:file:text-slate-200
                           border rounded p-2 dark:bg-slate-800 dark:text-slate-100"
                />
                @error('pdf')
                    <x-ts-alert color="danger" class="mt-2">{{ $message }}</x-ts-alert>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <x-ts-button type="submit" icon="cloud-arrow-up" class="ts-button-primary" wire:loading.attr="disabled">
                    Send to OCR API
                </x-ts-button>
                <span wire:loading class="text-sm text-gray-500">
                    Uploading & processing…
                </span>
            </div>
        </form>
    </x-ts-card>

    {{-- Error --}}
    @if($error)
        <x-ts-alert color="danger" title="API error">
            {{ $error }}
        </x-ts-alert>
    @endif

    {{-- Results --}}
    @if(!empty($apiResponse))
        @php
            // Sort by confidence and pick top + others
            $people = collect($apiResponse['people'] ?? [])
                ->sortByDesc(fn($p) => (float)($p['confidence'] ?? 0))
                ->values();
            $dobs = collect($apiResponse['dobs'] ?? [])
                ->sortByDesc(fn($d) => (float)($d['confidence'] ?? 0))
                ->values();

            $bestPerson   = $people->first();
            $bestDob      = $dobs->first();
            $othersPeople = $people->slice(1);
            $othersDobs   = $dobs->slice(1);
        @endphp

        <x-ts-card class="space-y-6">
            <div class="flex items-center gap-3">
                <x-ts-badge color="primary">{{ $apiResponse['template'] ?? 'Template' }}</x-ts-badge>
                <x-ts-badge color="success">People: {{ count($apiResponse['people'] ?? []) }}</x-ts-badge>
                <x-ts-badge color="success">DOBs: {{ count($apiResponse['dobs'] ?? []) }}</x-ts-badge>
            </div>

            {{-- TOP RESULTS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Top Person --}}
                <div class="rounded-lg border p-4">
                    <h3 class="font-semibold mb-3">Top Person</h3>
                    @if ($bestPerson)
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="text-xl font-bold">
                                    {{ trim(($bestPerson['first_name'] ?? '—') . ' ' . ($bestPerson['last_name'] ?? '')) }}
                                </div>
                                <x-ts-badge color="{{ (float)($bestPerson['confidence'] ?? 0) >= 0.9 ? 'success' : 'warning' }}">
                                    {{ number_format((float)($bestPerson['confidence'] ?? 0), 3) }}
                                </x-ts-badge>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                <x-ts-badge color="gray">Source: {{ $bestPerson['source'] ?? '—' }}</x-ts-badge>
                                <span class="ml-2">Page: {{ $bestPerson['page'] ?? '?' }}</span>
                                <span class="ml-2">Line: {{ $bestPerson['line'] ?? '?' }}</span>
                            </div>
                            @if(!empty($bestPerson['context']))
                                <div class="text-xs text-gray-500 bg-slate-50 dark:bg-slate-800 p-2 rounded">
                                    {{ $bestPerson['context'] }}
                                </div>
                            @endif
                        </div>
                    @else
                        <x-ts-alert color="gray">No person detected.</x-ts-alert>
                    @endif
                </div>

                {{-- Top DOB --}}
                <div class="rounded-lg border p-4">
                    <h3 class="font-semibold mb-3">Top DOB</h3>
                    @if ($bestDob)
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="text-xl font-bold">
                                    {{ $bestDob['date'] ?? '—' }}
                                </div>
                                <x-ts-badge color="{{ (float)($bestDob['confidence'] ?? 0) >= 0.9 ? 'success' : 'warning' }}">
                                    {{ number_format((float)($bestDob['confidence'] ?? 0), 3) }}
                                </x-ts-badge>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                <span class="mr-3">Page: {{ $bestDob['page'] ?? '?' }}</span>
                                <span>Line: {{ $bestDob['line'] ?? '?' }}</span>
                            </div>
                            @if(!empty($bestDob['context']))
                                <div class="text-xs text-gray-500 bg-slate-50 dark:bg-slate-800 p-2 rounded">
                                    {{ $bestDob['context'] }}
                                </div>
                            @endif
                        </div>
                    @else
                        <x-ts-alert color="gray">No DOB detected.</x-ts-alert>
                    @endif
                </div>
            </div>

            {{-- Other People --}}
            <div>
                <h3 class="font-semibold mb-2">Other People (candidates)</h3>
                @if ($othersPeople->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-600 dark:text-gray-300 border-b">
                                    <th class="py-2 pr-4">First</th>
                                    <th class="py-2 pr-4">Last</th>
                                    <th class="py-2 pr-4">Confidence</th>
                                    <th class="py-2 pr-4">Page:Line</th>
                                    <th class="py-2 pr-4">Source</th>
                                    <th class="py-2">Context</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($othersPeople as $p)
                                    <tr>
                                        <td class="py-2 pr-4">{{ $p['first_name'] ?? '—' }}</td>
                                        <td class="py-2 pr-4">{{ $p['last_name'] ?? '—' }}</td>
                                        <td class="py-2 pr-4">
                                            <x-ts-badge color="{{ (float)($p['confidence'] ?? 0) >= 0.9 ? 'success' : 'warning' }}">
                                                {{ number_format((float)($p['confidence'] ?? 0), 3) }}
                                            </x-ts-badge>
                                        </td>
                                        <td class="py-2 pr-4">{{ $p['page'] ?? '?' }}:{{ $p['line'] ?? '?' }}</td>
                                        <td class="py-2 pr-4"><x-ts-badge color="gray">{{ $p['source'] ?? '—' }}</x-ts-badge></td>
                                        <td class="py-2">{{ $p['context'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ts-alert color="gray">No other person candidates.</x-ts-alert>
                @endif
            </div>

            {{-- Other DOBs --}}
            <div>
                <h3 class="font-semibold mb-2">Other DOBs (candidates)</h3>
                @if ($othersDobs->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-600 dark:text-gray-300 border-b">
                                    <th class="py-2 pr-4">Date</th>
                                    <th class="py-2 pr-4">Confidence</th>
                                    <th class="py-2 pr-4">Page:Line</th>
                                    <th class="py-2">Context</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($othersDobs as $d)
                                    <tr>
                                        <td class="py-2 pr-4">{{ $d['date'] ?? '—' }}</td>
                                        <td class="py-2 pr-4">
                                            <x-ts-badge color="{{ (float)($d['confidence'] ?? 0) >= 0.9 ? 'success' : 'warning' }}">
                                                {{ number_format((float)($d['confidence'] ?? 0), 3) }}
                                            </x-ts-badge>
                                        </td>
                                        <td class="py-2 pr-4">{{ $d['page'] ?? '?' }}:{{ $d['line'] ?? '?' }}</td>
                                        <td class="py-2">{{ $d['context'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ts-alert color="gray">No other DOB candidates.</x-ts-alert>
                @endif
            </div>

            {{-- Raw JSON (debug) --}}
            <div>
                <h3 class="font-semibold mb-2">Raw JSON</h3>
                <pre class="text-xs bg-slate-100 dark:bg-slate-800 p-3 rounded overflow-auto">
{{ json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                </pre>
            </div>
        </x-ts-card>
    @endif
</div>
