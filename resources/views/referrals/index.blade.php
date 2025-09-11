<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Referral Intake Overview') }}
            </h2>



            <!-- Create Referral Button -->
            <livewire:create-referral />
        </div>
    </x-slot>

    <div class="py-8 px-4 max-w-7xl mx-auto space-y-6">
        <!-- Referrals Table -->
        <livewire:referrals-table />
    </div>
</x-app-layout>
