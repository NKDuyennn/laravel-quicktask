<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __('You are logged in!') }}
                </div>
            </div>
        
            <div class="mt-4">
                <x-input-label for="task" :value="__('Task')" />
                <x-text-input id="task" name="task" type="text" class="mt-1 block w-full" required autocomplete="task" />
                <x-input-error :messages="$errors->get('task')" class="mt-2" />
            </div>
            <x-primary-button class="mt-4">
                {{ __('Add Task') }}
            </x-primary-button>
        </div>
    </div>
</x-app-layout>