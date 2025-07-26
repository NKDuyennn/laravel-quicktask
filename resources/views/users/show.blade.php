<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">{{ __('User Information') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Full Name') }}
                                </label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $user->fullname ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Username') }}
                                </label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $user->username ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Email') }}
                                </label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $user->email ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Status') }}
                                </label>
                                <p class="mt-1 text-sm">
                                    @if($user->is_active)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Role') }}
                                </label>
                                <p class="mt-1 text-sm">
                                    @if($user->is_admin)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            {{ __('Admin') }}
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                            {{ __('User') }}
                                        </span>
                                    @endif
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Created At') }}
                                </label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Section -->
                    @if($user->tasks && $user->tasks->count() > 0)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">{{ __('User Tasks') }}</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($user->tasks as $task)
                                    <div class="bg-white dark:bg-gray-600 rounded px-3 py-2 text-sm">
                                        {{ $task->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">{{ __('User Tasks') }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 italic">
                            {{ __('No tasks assigned to this user.') }}
                        </p>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('users.edit', $user) }}">
                            <x-primary-button>
                                {{ __('Edit User') }}
                            </x-primary-button>
                        </a>
                        
                        <a href="{{ route('users.index') }}">
                            <x-secondary-button>
                                {{ __('Back to List') }}
                            </x-secondary-button>
                        </a>
                        
                        <form method="POST" action="{{ route('users.destroy', $user) }}" 
                              id="delete-user-form" 
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <x-danger-button type="button" onclick="confirmDeleteUser()">
                                {{ __('Delete User') }}
                            </x-danger-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmDeleteUser() {
        if (confirm('Are you sure you want to delete this user?')) {
            document.getElementById('delete-user-form').submit();
        }
    }
    </script>
</x-app-layout>