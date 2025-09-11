<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Workflow Details') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto grid grid-cols-12 gap-6">
        <!-- Sidebar / Step Index -->
        <aside class="col-span-3 sticky top-6 h-fit bg-white dark:bg-gray-800 border rounded p-4 shadow-sm">

            <h3 class="text-lg font-bold mb-3 text-gray-700 dark:text-gray-100">Steps Index</h3>
            <ul class="space-y-2 text-sm text-green-700 dark:text-green-300">
                @php
                    $referral = \App\Models\Referral::with(['workflow.stages.steps', 'progress'])->find($id);
                    $workflow = $referral?->workflow;
                    $stages   = $workflow?->stages->sortBy('order');
                    $userGroups = auth()->user()->group ?? [];
                    $stepProgressMap = $referral->progress->keyBy('workflow_step_id');

                    function extractActualValue($notes) {
                        if (!$notes || !is_string($notes)) return null;
                        $decoded = json_decode($notes, true);
                        if (!is_array($decoded)) return $notes;
                        return $decoded[array_key_first($decoded)] ?? null;
                    }
                @endphp

                @foreach($stages as $stage)
                    @php
                        $visibleSteps = $stage->steps->sortBy('order')->filter(function ($step) use ($userGroups, $stepProgressMap) {
                            $writeGroups = $step->group_can_write ?? [];
                            $seeGroups   = $step->group_can_see ?? [];

                            $canWrite = !empty(array_intersect($userGroups, $writeGroups));
                            $canSee   = $canWrite || !empty(array_intersect($userGroups, $seeGroups));

                            $dependency = $step->metadata['depends_on'] ?? null;
                            if ($dependency) {
                                $dependentStepId = $dependency['step_id'] ?? null;
                                $expectedValue   = $dependency['value'] ?? null;
                                $actualProgress  = $stepProgressMap[$dependentStepId] ?? null;
                                $actualValue     = extractActualValue($actualProgress?->notes);

                                if ($actualValue !== $expectedValue) {
                                    return false;
                                }
                            }

                            return $canSee;
                        });
                    @endphp

                    @if($visibleSteps->isNotEmpty())
                        <li class="font-semibold mt-3 text-gray-600 dark:text-gray-300">{{ $stage->name }}</li>
                        <ul class="ml-3 space-y-1">
                            @foreach($visibleSteps as $step)
                                <li>
                                    <a href="#step-{{ $step->id }}" class="hover:underline">
                                        {{ $step->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endforeach
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="col-span-9">
            <livewire:referral-workflow-show :referralId="$id" />
        </div>
    </div>
</x-app-layout>
