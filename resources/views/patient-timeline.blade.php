<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Patient Timeline') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        <livewire:patient-timeline />
    </div>
</x-app-layout>
