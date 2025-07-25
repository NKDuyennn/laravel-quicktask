# 8-Auth

## Tài liệu tham khảo
- [Laravel Starter Kits](https://laravel.com/docs/11.x/starter-kits)
- [Laravel Jetstream](https://jetstream.laravel.com/introduction.html)

---

## Authentication Starter Kits trong Laravel

### Starter Kit là gì?

**Starter Kit** là một bộ công cụ được cài đặt sẵn giúp khởi tạo nhanh các tính năng authentication cơ bản cho ứng dụng Laravel mà không cần phải code từ đầu.

**Bao gồm:**
- Đăng nhập (Login)
- Đăng ký (Register) 
- Đăng xuất (Logout)
- Quên mật khẩu (Forgot Password)
- Reset mật khẩu (Reset Password)
- Xác thực email (Email Verification)
- Chỉnh sửa profile (Profile Management)

### So sánh các Authentication Packages

| Đặc điểm | Laravel Breeze | Laravel Jetstream | Laravel Fortify |
|----------|----------------|-------------------|-----------------|
| **Độ phức tạp** | ⭐ Đơn giản | ⭐⭐⭐ Phức tạp | ⭐⭐ Trung bình |
| **Frontend** | Blade, React, Vue | Livewire, Inertia.js | API Only |
| **Features** | Cơ bản | Đầy đủ + nâng cao | Chỉ backend |
| **2FA** | ❌ | ✅ | ✅ |
| **Team Management** | ❌ | ✅ | ❌ |
| **Session Management** | ❌ | ✅ | ✅ |
| **API Tokens** | ❌ | ✅ | ✅ |
| **Dành cho** | Người mới, dự án đơn giản | Dự án lớn, nhiều tính năng | API, SPA |

#### **Laravel Jetstream**
```bash
composer require laravel/jetstream
php artisan jetstream:install livewire
```

**Ưu điểm:**
- ✅ Đầy đủ tính năng authentication nâng cao
- ✅ Hỗ trợ teams, roles, permissions
- ✅ Two-factor authentication
- ✅ Session management
- ✅ API token management

**Nhược điểm:**
- ❌ Phức tạp cho người mới
- ❌ Nhiều config và options khó hiểu
- ❌ Khó customize khi chưa hiểu rõ

#### **Laravel Breeze** (Recommended cho người mới)
```bash
composer require laravel/breeze --dev
php artisan breeze:install
```

**Ưu điểm:**
- ✅ Đơn giản, dễ hiểu
- ✅ Code rõ ràng, dễ customize
- ✅ Nhẹ, không có features dư thừa
- ✅ Perfect cho học tập và dự án nhỏ

**Nhược điểm:**
- ❌ Ít features hơn Jetstream
- ❌ Không có team management, 2FA

---

## Cài đặt Laravel Breeze

### Bước 1: Cài đặt package

```bash
composer require laravel/breeze --dev
```

Sau khi cài đặt, trong `composer.json` sẽ xuất hiện:
```json
"require-dev": {
    "laravel/breeze": "^2.0"
}
```

### Bước 2: Install và setup vào project

```bash
php artisan breeze:install --help
```

**Các options có thể chọn:**
- `blade` - Sử dụng Blade templates (mặc định)
- `react` - Sử dụng React + Inertia.js
- `vue` - Sử dụng Vue.js + Inertia.js  
- `api` - Chỉ API routes (không có views)

**Ví dụ cài đặt với Blade và Dark mode:**
```bash
php artisan breeze:install blade --dark
```

### Bước 3: Compile assets và migrate

```bash
npm install && npm run build
php artisan migrate
```

---

## Config và Customization

### Cấu trúc sau khi cài đặt

Sau khi cài đặt xong, truy cập `http://127.0.0.1:8000` sẽ thấy:
- **Login** link ở góc phải
- **Register** link ở góc phải
- Tất cả chức năng authentication đã hoạt động hoàn chỉnh

### Files được tạo ra:

#### **Controllers:**
```
app/Http/Controllers/Auth/
├── AuthenticatedSessionController.php    # Login/Logout
├── ConfirmablePasswordController.php     # Confirm Password
├── EmailVerificationNotificationController.php
├── EmailVerificationPromptController.php
├── NewPasswordController.php             # Reset Password
├── PasswordController.php                # Change Password
├── PasswordResetLinkController.php       # Forgot Password
├── RegisteredUserController.php          # Register
└── VerifyEmailController.php             # Email Verification
```

#### **Views:**
```
resources/views/auth/
├── confirm-password.blade.php
├── forgot-password.blade.php
├── login.blade.php
├── register.blade.php
├── reset-password.blade.php
└── verify-email.blade.php
```

#### **Routes:**
```php
// routes/auth.php - Tự động được include
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create']);
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create']);
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    // ...
});
```

### Customization khi database đã thay đổi

Nếu đã sửa đổi model `User` và database (ví dụ thêm field `is_admin`), cần config lại:

#### **B1: Sửa RegisteredUserController.php**

```php
// app/Http/Controllers/Auth/RegisteredUserController.php

public function store(Request $request): RedirectResponse
{
    // Validate input
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        // Thêm validation cho fields mới nếu cần
    ]);

    // Cách 1: Sử dụng unguard() (KHÔNG khuyến nghị)
    User::unguard(); // Tắt mass assignment protection
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'is_admin' => false, // Set default value
    ]);
    User::reguard(); // Bật lại mass assignment protection

    // Cách 2: Tạo object và gán từng thuộc tính (KHUYẾN NGHỊ)
    $user = new User();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->is_admin = false; // Set default
    $user->save();

    // Hoặc Cách 3: Thêm vào $fillable trong User model
    // Trong app/Models/User.php
    // protected $fillable = ['name', 'email', 'password', 'is_admin'];

    event(new Registered($user));
    Auth::login($user);

    return redirect(route('dashboard', absolute: false));
}
```

#### **B2: Sửa form register**

```blade
{{-- resources/views/auth/register.blade.php --}}

<form method="POST" action="{{ route('register') }}">
    @csrf

    <!-- Name -->
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <!-- Email Address -->
    <div class="mt-4">
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <!-- Password -->
    <div class="mt-4">
        <x-input-label for="password" :value="__('Password')" />
        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <!-- Confirm Password -->
    <div class="mt-4">
        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <!-- Admin Role (Optional - chỉ cho admin tạo user) -->
    @if(auth()->check() && auth()->user()->is_admin)
    <div class="mt-4">
        <label for="is_admin" class="inline-flex items-center">
            <input id="is_admin" type="checkbox" class="rounded" name="is_admin" value="1">
            <span class="ms-2 text-sm text-gray-600">{{ __('Admin User') }}</span>
        </label>
    </div>
    @endif

    <div class="flex items-center justify-end mt-4">
        <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
            {{ __('Already registered?') }}
        </a>

        <x-primary-button class="ms-4">
            {{ __('Register') }}
        </x-primary-button>
    </div>
</form>
```

---

## Các kiến thức khác thường dùng

### 1. **Custom Authentication Logic**

```php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();

    $request->session()->regenerate();

    // Custom logic sau khi login
    $user = auth()->user();
    
    // Log login activity
    Log::info('User logged in', ['user_id' => $user->id, 'ip' => $request->ip()]);
    
    // Redirect based on role
    if ($user->is_admin) {
        return redirect()->intended(route('admin.dashboard'));
    }
    
    return redirect()->intended(route('dashboard'));
}
```

### 2. **Middleware Authentication**

```php
// Protecting routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('posts', PostController::class);
});

// Admin only routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

### 3. **Custom Validation Rules**

```php
// app/Http/Requests/Auth/LoginRequest.php

public function rules(): array
{
    return [
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
        'g-recaptcha-response' => ['required'], // Add captcha
    ];
}

public function authenticate(): void
{
    $this->ensureIsNotRateLimited();

    // Custom authentication logic
    if (!Auth::attempt([
        'email' => $this->email,
        'password' => $this->password,
        'is_active' => true, // Only allow active users
    ], $this->boolean('remember'))) {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    RateLimiter::clear($this->throttleKey());
}
```

### 4. **Email Verification Customization**

```php
// config/auth.php
'verification' => [
    'expire' => 60, // Verification link expires in 60 minutes
],

// Custom email verification notification
// app/Notifications/VerifyEmailNotification.php
class VerifyEmailNotification extends Notification
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $this->verificationUrl($notifiable))
            ->line('If you did not create an account, no further action is required.');
    }
}
```

### 5. **Multi-Authentication Guards**

```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'admin' => [
        'driver' => 'session',
        'provider' => 'admins',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    'admins' => [
        'driver' => 'eloquent',
        'model' => App\Models\Admin::class,
    ],
],

// Usage
Auth::guard('admin')->attempt($credentials);
```

---

## Quiz - Đáp án

### 1. Bạn biết những starter kit Authentication nào của Laravel?

Laravel cung cấp **3 starter kits** chính cho Authentication:

#### **1. Laravel Breeze** ⭐ (Khuyến nghị cho người mới)
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

**Đặc điểm:**
- ✅ **Đơn giản, nhẹ** - chỉ có features cơ bản nhất
- ✅ **Dễ hiểu** - code rõ ràng, logic đơn giản
- ✅ **Dễ customize** - ít abstraction, dễ chỉnh sửa
- ✅ **Perfect cho learning** - người mới dễ nắm bắt

**Features:**
- Login/Logout
- Registration
- Password Reset
- Email Verification
- Profile Management

**Frontend Options:**
- Blade templates (default)
- React + Inertia.js
- Vue.js + Inertia.js
- API only

#### **2. Laravel Jetstream** ⭐⭐⭐ (Cho dự án lớn)
```bash
composer require laravel/jetstream
php artisan jetstream:install livewire
```

**Đặc điểm:**
- ✅ **Full-featured** - nhiều tính năng nâng cao
- ✅ **Production-ready** - sẵn sàng cho dự án thực tế
- ✅ **Team management** - quản lý teams, roles
- ❌ **Phức tạp** - nhiều config, khó hiểu cho người mới

**Features:**
- Tất cả features của Breeze
- **Two-factor authentication** (2FA)
- **Team management** với roles & permissions
- **API token management**
- **Session management** 
- **Profile photos**
- **Browser session management**

**Frontend Options:**
- Livewire + Blade
- Inertia.js + Vue.js

#### **3. Laravel Fortify** ⭐⭐ (Backend only)
```bash
composer require laravel/fortify
```

**Đặc điểm:**
- ✅ **Headless** - chỉ cung cấp backend logic
- ✅ **API-first** - perfect cho SPA, mobile apps
- ✅ **Flexible** - tự do thiết kế frontend
- ❌ **Không có views** - phải tự code frontend

**Features:**
- Registration & Authentication
- Password Reset
- Email Verification  
- Two-factor Authentication
- API token management

#### **4. Laravel Sanctum** (API Authentication)
```bash
composer require laravel/sanctum
```

**Đặc điểm:**
- ✅ **API tokens** - cho mobile/SPA apps
- ✅ **SPA authentication** - cookie-based cho same-domain
- ✅ **Lightweight** - thay thế Passport cho use cases đơn giản

#### **So sánh tổng quan:**

| Use Case | Recommended Kit |
|----------|----------------|
| **Học Laravel, dự án nhỏ** | Laravel Breeze |
| **Dự án lớn, cần team management** | Laravel Jetstream |  
| **API only, SPA, Mobile** | Laravel Fortify + Sanctum |
| **Cần custom UI hoàn toàn** | Laravel Fortify |

### 2. Trong quicktask bạn sử dụng starter kit nào? Khi cần customize logic thì cần sửa ở đâu?

#### **Starter Kit được sử dụng: Laravel Breeze**

**Lý do chọn Breeze:**
- ✅ **Phù hợp cho học tập** - logic đơn giản, dễ hiểu
- ✅ **Quicktask là dự án nhỏ** - không cần features phức tạp
- ✅ **Dễ customize** - code rõ ràng, ít abstraction
- ✅ **Nhanh chóng setup** - cài đặt và chạy ngay

#### **Khi cần customize logic, sửa ở đâu:**

#### **1. Controllers (Logic Backend)** 📁 `app/Http/Controllers/Auth/`

```php
// Registration logic
RegisteredUserController.php
├── create() method - hiển thị form register  
└── store() method - xử lý đăng ký user mới

// Login/Logout logic  
AuthenticatedSessionController.php
├── create() method - hiển thị form login
├── store() method - xử lý đăng nhập
└── destroy() method - xử lý đăng xuất

// Password Reset logic
PasswordResetLinkController.php - gửi link reset
NewPasswordController.php - xử lý reset password

// Profile Management
ProfileController.php - quản lý thông tin cá nhân
```

**Ví dụ customize Registration:**
```php
// app/Http/Controllers/Auth/RegisteredUserController.php
public function store(Request $request): RedirectResponse
{
    // Custom validation
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'phone' => ['required', 'string', 'max:15'], // Custom field
    ]);

    // Custom user creation logic
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone' => $request->phone, // Custom field
        'is_admin' => false, // Default role
        'status' => 'active', // Default status
    ]);

    // Custom post-registration logic
    $user->assignRole('user'); // Assign default role
    
    // Send welcome email
    Mail::to($user)->send(new WelcomeEmail($user));
    
    event(new Registered($user));
    Auth::login($user);

    return redirect(route('dashboard'));
}
```

#### **2. Views (Frontend)** 📁 `resources/views/auth/`

```blade
register.blade.php - Form đăng ký
login.blade.php - Form đăng nhập  
forgot-password.blade.php - Form quên mật khẩu
reset-password.blade.php - Form reset mật khẩu
verify-email.blade.php - Thông báo xác thực email
```

**Ví dụ customize Register form:**
```blade
{{-- resources/views/auth/register.blade.php --}}
<form method="POST" action="{{ route('register') }}">
    @csrf
    
    <!-- Existing fields -->
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" :value="old('name')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <!-- Custom field -->
    <div class="mt-4">
        <x-input-label for="phone" :value="__('Phone Number')" />
        <x-text-input id="phone" name="phone" :value="old('phone')" required />
        <x-input-error :messages="$errors->get('phone')" />
    </div>

    <!-- Custom role selection (admin only) -->
    @if(auth()->check() && auth()->user()->is_admin)
    <div class="mt-4">
        <x-input-label for="role" :value="__('Role')" />
        <select id="role" name="role" class="block mt-1 w-full">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
    </div>
    @endif
</form>
```

#### **3. Routes** 📁 `routes/auth.php`

```php
// Customize authentication routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    
    // Custom routes
    Route::get('admin/register', [AdminRegistrationController::class, 'create'])
                ->name('admin.register');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Custom profile routes
    Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
});
```

#### **4. Middleware** 📁 `app/Http/Middleware/`

```php
// Custom authentication middleware
class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->status !== 'active') {
            Auth::logout();
            return redirect('/login')->with('error', 'Account suspended');
        }
        
        return $next($request);
    }
}
```

#### **5. Models** 📁 `app/Models/User.php`

```php
// Customize User model
class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'is_admin'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    // Custom methods
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
    
    public function canAccessAdminPanel(): bool
    {
        return $this->is_admin && $this->status === 'active';
    }
}
```

#### **Workflow customize:**

```
1. Identify cần sửa gì:
   ├── Logic backend → Controllers
   ├── Giao diện → Views  
   ├── Routes → routes/auth.php
   ├── Validation → Request classes
   └── Database → Models

2. Test thoroughly:
   ├── Register new user
   ├── Login/Logout
   ├── Password reset
   └── Profile management

3. Handle edge cases:
   ├── Validation errors
   ├── Database constraints
   └── Security considerations
```