# Chapter 15: Gate & Policy - Hướng dẫn thực hiện

## Mục đích và So sánh

### Tác dụng của Gate & Policy
- **Gate:** Dùng cho phân quyền đơn giản, logic tập trung
- **Policy:** Dùng cho phân quyền phức tạp theo từng Model
- **Middleware:** Dùng cho kiểm tra quyền ở route level

### So sánh tình huống sử dụng

| Tình huống | Gate | Policy | Middleware |
|------------|------|--------|------------|
| Kiểm tra admin toàn cục | ✅ | ❌ | ✅ |
| Phân quyền theo Model cụ thể | ❌ | ✅ | ❌ |
| Logic phức tạp nhiều điều kiện | ❌ | ✅ | ❌ |
| Kiểm tra trước khi vào Controller | ✅ | ❌ | ✅ |
| Kiểm tra trong View/Controller | ✅ | ✅ | ❌ |

---

## Thứ tự thực hiện

### Bước 1: Tạo UserPolicy

```bash
php artisan make:policy UserPolicy --model=User
```

Tạo file `app/Policies/UserPolicy.php` với nội dung:

```php
<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Chỉ admin mới có thể xem danh sách tất cả user
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Admin hoặc chính user đó mới có thể xem thông tin
        return $user->is_admin || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Chỉ admin mới có thể tạo user mới
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Chỉ chính user đó mới có thể update thông tin của mình
        // Hoặc admin có thể update thông tin của user khác
        return $user->id === $model->id || $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Admin có thể xóa user khác (không phải chính mình)
        // User thường không thể tự xóa mình
        return $user->is_admin && $user->id !== $model->id;
    }

    /**
     * Custom policy: User can only edit their own profile
     */
    public function editOwnProfile(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Custom policy: User can view their own tasks
     */
    public function viewOwnTasks(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->is_admin;
    }
}
```

### Bước 2: Cập nhật AuthServiceProvider

Chỉnh sửa file `app/Providers/AuthServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [
        User::class => \App\Policies\UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Đăng ký Gates
        $this->registerGates();
    }

    /**
     * Register Gates for authorization
     */
    private function registerGates(): void
    {
        // Gate kiểm tra admin (thay thế CheckAdmin middleware)
        Gate::define('admin-access', function (User $user) {
            return $user->is_admin === true;
        });

        // Gate kiểm tra user active
        Gate::define('active-user', function (User $user) {
            return $user->is_active === true;
        });

        // Gate kiểm tra admin hoặc chính user đó
        Gate::define('admin-or-self', function (User $user, User $targetUser) {
            return $user->is_admin || $user->id === $targetUser->id;
        });

        // Gate quản lý user (chỉ admin)
        Gate::define('manage-users', function (User $user) {
            return $user->is_admin === true;
        });

        // Gate xem danh sách user
        Gate::define('view-users', function (User $user) {
            return $user->is_admin === true;
        });

        // Gate tạo user mới
        Gate::define('create-user', function (User $user) {
            return $user->is_admin === true;
        });

        // Gate xóa user
        Gate::define('delete-user', function (User $user, User $targetUser) {
            // Admin có thể xóa user khác (không phải chính mình)
            return $user->is_admin && $user->id !== $targetUser->id;
        });
    }
}
```

### Bước 3: Cập nhật UserController

Chỉnh sửa `app/Http/Controllers/UserController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Sử dụng Gate thay vì middleware
        Gate::authorize('view-users');
        
        $users = User::with('tasks')->paginate(10);
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Kiểm tra quyền tạo user bằng Gate
        Gate::authorize('create-user');

        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        Gate::authorize('create-user');

        $user = User::create([
            'fullname' => $request->fullname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->boolean('is_admin'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Sử dụng Policy để kiểm tra quyền xem
        $this->authorize('view', $user);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Kiểm tra quyền edit: chỉ chính user hoặc admin
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        // Policy: Chỉ chính user mới có quyền update thông tin của mình
        $this->authorize('update', $user);

        $data = [
            'fullname' => $request->fullname,
            'username' => $request->username,
            'email' => $request->email,
        ];

        // Chỉ cập nhật password nếu có nhập
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Chỉ admin mới có thể thay đổi is_admin và is_active
        if (Gate::allows('admin-access')) {
            $data['is_admin'] = $request->boolean('is_admin');
            $data['is_active'] = $request->boolean('is_active');
        }

        $user->update($data);

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Sử dụng Gate để kiểm tra quyền xóa
        Gate::authorize('delete-user', $user);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show user profile (chỉ user đó mới xem được)
     */
    public function profile()
    {
        $user = auth()->user();
        
        return view('users.profile', compact('user'));
    }

    /**
     * Update user profile (chỉ user đó mới update được)
     */
    public function updateProfile(UpdateUserRequest $request)
    {
        $user = auth()->user();
        
        // Kiểm tra user chỉ có thể edit profile của chính mình
        $this->authorize('editOwnProfile', $user);

        $data = [
            'fullname' => $request->fullname,
            'username' => $request->username,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
```

### Bước 4: Cập nhật Routes

Chỉnh sửa `routes/web.php` (bỏ CheckAdmin middleware):

```php
<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // User profile routes
    Route::get('/my-profile', [UserController::class, 'profile'])->name('users.profile');
    Route::patch('/my-profile', [UserController::class, 'updateProfile'])->name('users.profile.update');
});

// User management routes - Không cần CheckAdmin middleware
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
});

require __DIR__.'/auth.php';
```

### Bước 5: Cập nhật View show.blade.php

Chỉnh sửa `resources/views/users/show.blade.php`:

```php
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
                    <!-- User Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">{{ __('User Information') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Các field thông tin user giữ nguyên -->
                            <!-- ... -->
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Created At') }}
                                </label>
                                <div class="mt-1 space-y-1">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="font-medium">D/M/Y:</span> {{ formatDateDMYWithTime($user->created_at) }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Y/M/D:</span> {{ formatDateYMDWithTime($user->created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks Section với phân quyền -->
                    @can('viewOwnTasks', $user)
                    @if($user->tasks && $user->tasks->count() > 0)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">{{ __('User Tasks') }}</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($user->tasks as $task)
                                    <div class="bg-white dark:bg-gray-600 rounded px-3 py-2 text-sm">
                                        <div class="font-medium">{{ $task->name }}</div>
                                        @if($task->created_at)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Tạo: {{ formatDateDMY($task->created_at) }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    @endcan

                    <!-- Action Buttons với phân quyền -->
                    <div class="flex flex-wrap gap-3">
                        <!-- Edit Button -->
                        @can('update', $user)
                        <a href="{{ route('users.edit', $user) }}">
                            <x-primary-button>
                                {{ auth()->user()->id === $user->id ? __('Edit My Profile') : __('Edit User') }}
                            </x-primary-button>
                        </a>
                        @endcan
                        
                        <!-- Back to List - Chỉ admin -->
                        @can('admin-access')
                        <a href="{{ route('users.index') }}">
                            <x-secondary-button>
                                {{ __('Back to List') }}
                            </x-secondary-button>
                        </a>
                        @endcan
                        
                        <!-- Delete Button -->
                        @can('delete', $user)
                        <form method="POST" action="{{ route('users.destroy', $user) }}" 
                              id="delete-user-form" class="inline">
                            @csrf
                            @method('DELETE')
                            <x-danger-button type="button" onclick="confirmDeleteUser()">
                                {{ __('Delete User') }}
                            </x-danger-button>
                        </form>
                        @endcan

                        <!-- Thông báo cho user thường -->
                        @cannot('admin-access')
                        @if(auth()->user()->id !== $user->id)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                            <p class="text-sm text-yellow-600">
                                {{ __('You can only view this profile. Contact admin for modifications.') }}
                            </p>
                        </div>
                        @endif
                        @endcannot
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
```

---

## Cách sử dụng Gate & Policy trong Blade

### 1. Gate đơn giản
```php
@can('admin-access')
    <p>Bạn là admin!</p>
@endcan

@cannot('admin-access')
    <p>Bạn không phải admin</p>
@endcannot
```

### 2. Gate với tham số
```php
@can('admin-or-self', $user)
    <a href="{{ route('users.edit', $user) }}">Edit</a>
@endcan
```

### 3. Policy với Model
```php
@can('update', $user)
    <button>Edit User</button>
@endcan

@can('delete', $user)
    <button>Delete User</button>
@endcan
```

### 4. Kiểm tra multiple conditions
```php
@canany(['update', 'delete'], $user)
    <div class="admin-actions">
        <!-- Admin actions -->
    </div>
@endcanany
```

---

## Cách sử dụng trong Controller

### 1. Gate
```php
// Kiểm tra và ném exception nếu không có quyền
Gate::authorize('admin-access');

// Kiểm tra và trả về boolean
if (Gate::allows('admin-access')) {
    // Logic khi có quyền
}

if (Gate::denies('admin-access')) {
    // Logic khi không có quyền
}
```

### 2. Policy
```php
// Kiểm tra và ném exception
$this->authorize('update', $user);

// Kiểm tra với class
$this->authorize('create', User::class);
```

---

## Tóm tắt các thay đổi

1. ✅ **Đã tạo UserPolicy** với các quyền cụ thể theo Model
2. ✅ **Đã tạo Gates** thay thế CheckAdmin middleware  
3. ✅ **Cập nhật Controller** sử dụng Gate/Policy thay vì middleware
4. ✅ **Cập nhật Routes** bỏ CheckAdmin middleware
5. ✅ **Cập nhật View** với các directive @can/@cannot
6. ✅ **Áp dụng phân quyền**: User chỉ edit được profile của mình

### Kết quả đạt được:
- **Gate:** Thay thế CheckAdmin middleware
- **Policy:** User chỉ có quyền update thông tin của chính mình
- **Phân quyền linh hoạt:** Kiểm tra quyền ở cả Controller và View level