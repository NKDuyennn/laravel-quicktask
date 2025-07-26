# Laravel Blade Template - Hướng dẫn tạo CRUD interface

## Mục tiêu
- Tạo giao diện Blade Template để hiển thị danh sách User với các chức năng CRUD
- Hiển thị các đối tượng liên quan khi click vào một User
- Tích hợp styling với SCSS

## Phần 1: Thiết lập cấu trúc View

### 1.1 Tạo folder và file view

```bash
# Tạo folder users trong resources/views/
mkdir resources/views/users

# Tạo các file view cần thiết
touch resources/views/users/index.blade.php
touch resources/views/users/edit.blade.php
touch resources/views/users/create.blade.php
touch resources/views/users/show.blade.php
```

### 1.2 Cấu trúc thư mục
```
resources/views/
├── layouts/
│   └── app.blade.php
├── users/
│   ├── index.blade.php      # Danh sách users
│   ├── create.blade.php     # Form tạo user mới
│   ├── edit.blade.php       # Form chỉnh sửa user
│   └── show.blade.php       # Chi tiết user
└── welcome.blade.php
```

## Phần 2: Cấu hình Controller

### 2.1 Sửa UserController.php

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách users
     */
    public function index()
    {
        return view('users.index', [
            'users' => User::all(),
        ]);
    }

    /**
     * Hiển thị form tạo user mới
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Lưu user mới vào database
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')
                        ->with('success', __('User created successfully'));
    }

    /**
     * Hiển thị chi tiết user
     */
    public function show(User $user)
    {
        // Load các relationships nếu có
        $user->load(['posts', 'comments']); // Ví dụ relationships
        
        return view('users.show', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa user
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Cập nhật thông tin user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('users.index')
                        ->with('success', __('User updated successfully'));
    }

    /**
     * Xóa user
     */
    public function destroy(User $user)
    {
        $user->delete();
        
        return redirect()->route('users.index')
                        ->with('success', __('User deleted successfully'));
    }
}
```

## Phần 7: Cài đặt và chạy ứng dụng

### 7.1 Cài đặt dependencies

```bash
# Cài đặt PHP dependencies
composer install

# Cài đặt Node.js dependencies  
npm install

# Cài đặt SASS compiler
npm install sass --save-dev

# Cài đặt Bootstrap và icons
npm install bootstrap @popperjs/core bootstrap-icons --save
```

### 7.2 Build assets

```bash
# Biên dịch assets cho development
npm run dev

# Hoặc build cho production
npm run build

# Watch mode (tự động build khi có thay đổi)
npm run dev -- --watch
```

### 7.3 Chạy ứng dụng

```bash
# Chạy Laravel development server
php artisan serve

# Truy cập ứng dụng
# http://localhost:8000/users
```

## Phần 8: Tính năng nâng cao

### 8.1 Pagination

**Cập nhật UserController.php:**
```php
public function index()
{
    return view('users.index', [
        'users' => User::paginate(10), // Thay vì User::all()
    ]);
}
```

**Thêm pagination vào index.blade.php:**
```html
<!-- Thêm vào cuối card-body -->
@if($users->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $users->links() }}
    </div>
@endif
```

### 8.2 Search và Filter

**Cập nhật UserController.php:**
```php
public function index(Request $request)
{
    $query = User::query();
    
    // Search by name or email
    if ($request->filled('search')) {
        $search = $request->get('search');
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
    
    // Filter by creation date
    if ($request->filled('created_from')) {
        $query->whereDate('created_at', '>=', $request->get('created_from'));
    }
    
    if ($request->filled('created_to')) {
        $query->whereDate('created_at', '<=', $request->get('created_to'));
    }
    
    return view('users.index', [
        'users' => $query->latest()->paginate(10),
        'filters' => $request->only(['search', 'created_from', 'created_to'])
    ]);
}
```

**Thêm form search vào index.blade.php:**
```html
<!-- Thêm trước card chứa table -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="{{ __('Search by name or email...') }}"
                           value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <input type="date" 
                           class="form-control" 
                           name="created_from" 
                           placeholder="{{ __('From date') }}"
                           value="{{ $filters['created_from'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <input type="date" 
                           class="form-control" 
                           name="created_to" 
                           placeholder="{{ __('To date') }}"
                           value="{{ $filters['created_to'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                        {{ __('Search') }}
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['search', 'created_from', 'created_to']))
                <div class="mt-2">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>
                        {{ __('Clear Filters') }}
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>
```

### 8.3 Bulk Actions

**Thêm JavaScript cho bulk actions:**

**File resources/js/users.js:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActions = document.getElementById('bulk-actions');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkActions();
        });
    }
    
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });
    
    function toggleBulkActions() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        if (checkedBoxes.length > 0) {
            bulkActions.style.display = 'block';
        } else {
            bulkActions.style.display = 'none';
            selectAllCheckbox.checked = false;
        }
        
        // Update select all checkbox state
        selectAllCheckbox.checked = checkedBoxes.length === userCheckboxes.length;
    }
    
    // Bulk delete
    const bulkDeleteBtn = document.getElementById('bulk-delete');
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select at least one user');
                return;
            }
            
            if (confirm(`Are you sure you want to delete ${checkedBoxes.length} users?`)) {
                const form = document.getElementById('bulk-form');
                form.submit();
            }
        });
    }
});
```

**Import vào resources/js/app.js:**
```javascript
import './bootstrap';
import './users'; // Add this line
```

**Cập nhật index.blade.php để hỗ trợ bulk actions:**
```html
<!-- Thêm form bulk actions -->
<form id="bulk-form" method="POST" action="{{ route('users.bulk-destroy') }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Bulk actions bar -->
<div id="bulk-actions" class="alert alert-info" style="display: none;">
    <div class="d-flex justify-content-between align-items-center">
        <span>{{ __('Selected users') }}: <span id="selected-count">0</span></span>
        <div>
            <button type="button" id="bulk-delete" class="btn btn-danger btn-sm">
                <i class="bi bi-trash me-1"></i>
                {{ __('Delete Selected') }}
            </button>
        </div>
    </div>
</div>

<!-- Cập nhật table header -->
<thead>
    <tr>
        <th>
            <input type="checkbox" id="select-all" class="form-check-input">
        </th>
        <th>{{ __('Avatar') }}</th>
        <!-- ... other headers ... -->
    </tr>
</thead>

<!-- Cập nhật table body -->
<tbody>
    @foreach($users as $user)
    <tr>
        <td>
            <input type="checkbox" 
                   class="form-check-input user-checkbox" 
                   name="selected_users[]" 
                   value="{{ $user->id }}"
                   form="bulk-form">
        </td>
        <!-- ... rest of the row ... -->
    </tr>
    @endforeach
</tbody>
```

### 8.4 Export Users

**Thêm method export vào UserController:**
```php
use Illuminate\Support\Facades\Response;

public function export(Request $request)
{
    $users = User::all();
    
    $csvData = [];
    $csvData[] = ['ID', 'Name', 'Email', 'Created At']; // Header
    
    foreach ($users as $user) {
        $csvData[] = [
            $user->id,
            $user->name,
            $user->email,
            $user->created_at->format('Y-m-d H:i:s')
        ];
    }
    
    $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
    
    $handle = fopen('php://temp', 'w');
    foreach ($csvData as $row) {
        fputcsv($handle, $row);
    }
    rewind($handle);
    $csvContent = stream_get_contents($handle);
    fclose($handle);
    
    return Response::make($csvContent, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}
```

**Thêm button export vào index.blade.php:**
```html
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ __('Users Management') }}</h1>
    <div>
        <a href="{{ route('users.export') }}" class="btn btn-success me-2">
            <i class="bi bi-download me-1"></i>
            {{ __('Export CSV') }}
        </a>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            {{ __('Create New User') }}
        </a>
    </div>
</div>
```

## Phần 9: Testing

### 9.1 Feature Tests

**File tests/Feature/UserCrudTest.php:**
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_page_loads()
    {
        $response = $this->get('/users');
        $response->assertStatus(200);
        $response->assertViewIs('users.index');
    }

    public function test_can_create_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/users', $userData);
        
        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_can_update_user()
    {
        $user = User::factory()->create();
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->put("/users/{$user->id}", $updateData);
        
        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->delete("/users/{$user->id}");
        
        $response->assertRedirect('/users');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
```

**Chạy tests:**
```bash
# Chạy tất cả tests
php artisan test

# Chạy specific test file
php artisan test tests/Feature/UserCrudTest.php

# Chạy với coverage
php artisan test --coverage
```

## Phần 10: Performance Optimization

### 10.1 Database Optimization

**Thêm indexes vào migration:**
```php
// database/migrations/add_indexes_to_users_table.php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->index('email');
        $table->index('name');
        $table->index('created_at');
    });
}
```

### 10.2 Query Optimization

**Eager Loading trong Controller:**
```php
public function index(Request $request)
{
    $query = User::with(['posts', 'comments']); // Eager load relationships
    
    // ... rest of the method
}

public function show(User $user)
{
    $user->loadCount(['posts', 'comments']); // Load counts only
    return view('users.show', compact('user'));
}
```

### 10.3 Caching

**Cache user count:**
```php
use Illuminate\Support\Facades\Cache;

public function index()
{
    $userCount = Cache::remember('users_count', 300, function () {
        return User::count();
    });
    
    return view('users.index', [
        'users' => User::paginate(10),
        'userCount' => $userCount
    ]);
}
```

## Phần 11: Security

### 11.1 Form Validation

**Tạo Form Request:**
```bash
php artisan make:request StoreUserRequest
php artisan make:request UpdateUserRequest
```

**File app/Http/Requests/StoreUserRequest.php:**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('Name is required'),
            'name.min' => __('Name must be at least 2 characters'),
            'email.required' => __('Email is required'),
            'email.unique' => __('This email is already taken'),
            'password.required' => __('Password is required'),
            'password.min' => __('Password must be at least 8 characters'),
            'password.confirmed' => __('Password confirmation does not match'),
        ];
    }
}
```

**Sử dụng trong Controller:**
```php
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

public function store(StoreUserRequest $request)
{
    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);
    
    return redirect()->route('users.index')
                    ->with('success', __('User created successfully'));
}
```

### 11.2 CSRF Protection

**Đảm bảo tất cả forms có CSRF token:**
```html
<form method="POST" action="{{ route('users.store') }}">
    @csrf
    <!-- form fields -->
</form>

<form method="POST" action="{{ route('users.update', $user) }}">
    @csrf
    @method('PUT')
    <!-- form fields -->
</form>
```

### 11.3 XSS Protection

**Sử dụng Blade escaping:**
```html
<!-- Safe - automatically escaped -->
{{ $user->name }}

<!-- Unsafe - not escaped, only use for trusted content -->
{!! $user->bio !!}

<!-- Safe way to display HTML content -->
{{ strip_tags($user->bio) }}
```

## Kết luận

Hướng dẫn này cung cấp một giải pháp hoàn chỉnh để tạo giao diện CRUD cho User trong Laravel sử dụng Blade Template. Các tính năng chính bao gồm:

### ✅ Đã hoàn thành:
- **CRUD Operations**: Create, Read, Update, Delete users
- **Responsive Design**: Sử dụng Bootstrap 5 với custom SCSS
- **Form Validation**: Client-side và server-side validation
- **Localization**: Hỗ trợ đa ngôn ngữ với i18n
- **Search & Filter**: Tìm kiếm và lọc users
- **Pagination**: Phân trang cho danh sách lớn
- **Flash Messages**: Thông báo success/error
- **Security**: CSRF protection, XSS prevention
- **Testing**: Feature tests cho các chức năng chính

### 🚀 Tính năng nâng cao:
- **Bulk Actions**: Xóa nhiều users cùng lúc
- **Export**: Xuất danh sách users ra CSV
- **Performance**: Caching và query optimization
- **Relationships**: Hiển thị related data (posts, comments)

### 📝 Best Practices được áp dụng:
- **Clean Code**: Code structure rõ ràng, dễ maintain
- **Security First**: Validation, CSRF, XSS protection
- **User Experience**: Responsive design, loading states
- **Performance**: Efficient queries, caching
- **Accessibility**: Semantic HTML, proper labels

Với hướng dẫn này, bạn có thể tạo ra một hệ thống quản lý users hoàn chỉnh và professional trong Laravel.

## Phần 3: Thiết lập SCSS và Layout

### 3.1 Tạo file SCSS

**File resources/scss/app.scss:**
```scss
// Import Bootstrap
@import "~bootstrap/scss/bootstrap";

// Import Bootstrap Icons
@import "~bootstrap-icons/font/bootstrap-icons";

// Custom variables
:root {
    --primary-color: #3b82f6;
    --secondary-color: #64748b;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
}

// Global styles
body {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8fafc;
}

// Card styles
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    transition: all 0.2s ease-in-out;

    &:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
}

// Button styles
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
    
    &.btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    &.btn-success {
        background-color: var(--success-color);
        border-color: var(--success-color);
    }
    
    &.btn-danger {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }
}

// Table styles
.table {
    border-radius: 12px;
    overflow: hidden;
    
    thead th {
        background-color: #f1f5f9;
        border: none;
        font-weight: 600;
        color: #475569;
    }
    
    tbody tr {
        border-bottom: 1px solid #e2e8f0;
        
        &:hover {
            background-color: #f8fafc;
        }
    }
}

// User avatar
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

// Action buttons
.action-buttons {
    .btn {
        margin-right: 0.25rem;
        
        &:last-child {
            margin-right: 0;
        }
    }
}
```

### 3.2 Cập nhật Layout

**File resources/views/layouts/app.blade.php:**
```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="{{ route('users.index') }}">
                        {{ __('Users') }}
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="container">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
```

### 3.3 Cấu hình Vite

**File vite.config.js:**
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/scss/app.scss', // Include SCSS file
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '~bootstrap': 'node_modules/bootstrap',
            '~bootstrap-icons': 'node_modules/bootstrap-icons',
        }
    }
});
```

## Phần 4: Tạo Views

### 4.1 Danh sách Users (index.blade.php)

**File resources/views/users/index.blade.php:**
```html
@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">{{ __('Users Management') }}</h1>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                {{ __('Create New User') }}
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Avatar') }}</th>
                                    <th>{{ __('Full Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Username') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="user-avatar">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $user->name }}</div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $user->username ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="action-buttons text-center">
                                            <!-- View Button -->
                                            <a href="{{ route('users.show', $user) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="{{ __('View Details') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <!-- Edit Button -->
                                            <a href="{{ route('users.edit', $user) }}" 
                                               class="btn btn-sm btn-outline-warning"
                                               title="{{ __('Edit User') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <!-- Delete Button -->
                                            <form method="POST" 
                                                  action="{{ route('users.destroy', $user) }}" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="{{ __('Delete User') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-people display-1 text-muted"></i>
                        <h4 class="mt-3">{{ __('No users found') }}</h4>
                        <p class="text-muted">{{ __('Start by creating your first user') }}</p>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            {{ __('Create First User') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
```

### 4.2 Form chỉnh sửa User (edit.blade.php)

**File resources/views/users/edit.blade.php:**
```html
@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('Edit User') }}</h4>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('Back to List') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Full Name') }}</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Username (if exists) -->
                    @if(Schema::hasColumn('users', 'username'))
                    <div class="mb-3">
                        <label for="username" class="form-label">{{ __('Username') }}</label>
                        <input type="text" 
                               class="form-control @error('username') is-invalid @enderror" 
                               id="username" 
                               name="username" 
                               value="{{ old('username', $user->username) }}">
                        @error('username')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    @endif

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ __('Update User') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
```

### 4.3 Form tạo User mới (create.blade.php)

**File resources/views/users/create.blade.php:**
```html
@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('Create New User') }}</h4>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('Back to List') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Full Name') }}</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required>
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ __('Create User') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
```

### 4.4 Chi tiết User (show.blade.php)

**File resources/views/users/show.blade.php:**
```html
@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- User Information -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('User Details') }}</h4>
                    <div>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil me-1"></i>
                            {{ __('Edit') }}
                        </a>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>
                            {{ __('Back to List') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="user-avatar mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">{{ __('Full Name') }}:</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Email') }}:</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            @if($user->username)
                            <tr>
                                <th>{{ __('Username') }}:</th>
                                <td>{{ $user->username }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>{{ __('Created At') }}:</th>
                                <td>{{ $user->created_at->format('F d, Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Last Updated') }}:</th>
                                <td>{{ $user->updated_at->format('F d, Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Data (Posts, Comments, etc.) -->
        @if($user->relationLoaded('posts') && $user->posts->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('User Posts') }} ({{ $user->posts->count() }})</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($user->posts as $post)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $post->title }}</h6>
                                <p class="mb-1 text-muted">{{ Str::limit($post->content, 100) }}</p>
                                <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="action-buttons">
                                <a href="#" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                                <a href="#" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Quick Actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i>
                        {{ __('Edit User') }}
                    </a>
                    
                    <form method="POST" 
                          action="{{ route('users.destroy', $user) }}"
                          onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('Delete User') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Statistics') }}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary">{{ $user->posts->count() ?? 0 }}</h4>
                            <small class="text-muted">{{ __('Posts') }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $user->comments->count() ?? 0 }}</h4>
                        <small class="text-muted">{{ __('Comments') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## Phần 5: Cấu hình Routes

**File routes/web.php:**
```php
<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// User routes với resource controller
Route::resource('users', UserController::class);

// Hoặc định nghĩa từng route riêng biệt
/*
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
*/
```

## Phần 6: Localization

### 6.1 Thêm bản dịch

**File resources/lang/vi.json:**
```json
{
    "Users": "Người dùng",
    "Users Management": "Quản lý Người dùng", 
    "Create New User": "Tạo Người dùng Mới",
    "Edit User": "Chỉnh sửa Người dùng",
    "View Details": "Xem Chi tiết",
    "Delete User": "Xóa Người dùng",
    "Update User": "Cập nhật Người dùng",
    "Full Name": "Họ và Tên",
    "Email": "Email",
    "Email Address": "Địa chỉ Email", 
    "Username": "Tên đăng nhập",
    "Password": "Mật khẩu",
    "Confirm Password": "Xác nhận Mật khẩu",
    "Created At": "Ngày tạo",
    "Last Updated": "Cập nhật lần cuối",
    "Actions": "Thao tác",
    "Back to List": "Quay lại Danh sách",
    "Cancel": "Hủy",
    "Save": "Lưu",
    "Edit": "Sửa",
    "Delete": "Xóa",
    "View": "Xem",
    "Avatar": "Ảnh đại diện",
    "No users found": "Không tìm thấy người dùng nào",
    "Start by creating your first user": "Bắt đầu bằng cách tạo người dùng đầu tiên",
    "Create First User": "Tạo Người dùng Đầu tiên",
    "Are you sure you want to delete this user?": "Bạn có chắc chắn muốn xóa người dùng này?",
    "User created successfully": "Tạo người dùng thành công",
    "User updated successfully": "Cập nhật người dùng thành công", 
    "User deleted successfully": "Xóa người dùng thành công",
    "User Details": "Chi tiết Người dùng",
    "Quick Actions": "Thao tác Nhanh",
    "Statistics": "Thống kê",
    "Posts": "Bài viết",
    "Comments": "Bình luận",
    "User Posts": "Bài viết của Người dùng"
}
    "