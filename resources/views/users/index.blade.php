<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __('User list') }}
                </div>
            </div>
            <a href="{{ route('users.create') }}">
                <x-primary-button class="mt-4">
                    {{ __('Create new user') }}
                </x-primary-button>
            </a>
            <!-- @dump($users) -->
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-gray-900 dark:text-gray-100" scope="col">#</th>
                        <th class="text-gray-900 dark:text-gray-100" scope="col">Name</th>
                        <th class="text-gray-900 dark:text-gray-100" scope="col">Username</th>
                        <th class="text-gray-900 dark:text-gray-100" scope="col">Tasks</th>
                        <th class="text-gray-900 dark:text-gray-100" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                        <tr>
                            <th class="text-gray-900 dark:text-gray-100 text-center" scope="row">{{ ++$index }}</th>
                            <td class="text-gray-900 dark:text-gray-100 text-center">{{ $user->fullname }}</td>
                            <td class="text-gray-900 dark:text-gray-100 text-center">{{ $user->username }}</td>
                            <td class="text-gray-900 dark:text-gray-100 text-center">
                                @foreach ($user->tasks as $task)
                                    {{ $task->name }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                                <!-- @dump($user) -->
                            </td>
                            <td class="text-gray-900 dark:text-gray-100 text-center">
                                <a href="{{ route('users.show', $user) }}">
                                    <x-primary-button class="mt-4">
                                        {{ __('View') }}
                                    </x-primary-button>
                                </a>
                                <a href="{{ route('users.edit', $user) }}">
                                    <x-primary-button class="mt-4">
                                        {{ __('Edit') }}
                                    </x-primary-button>
                                </a>
                                <form method="POST" action="{{ route('users.destroy', $user) }}" 
                                      id="delete-form-{{ $user->id }}" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-primary-button type="button" 
                                                    onclick="confirmDelete('{{ $user->id }}')" 
                                                    class="mt-4">
                                        {{ __('Delete') }}
                                    </x-primary-button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function confirmDelete(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            document.getElementById('delete-form-' + userId).submit();
        }
    }
    </script>
</x-app-layout>