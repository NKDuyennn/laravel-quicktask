# 7-Middleware

## Middleware là gì?

**Middleware** là một màng lọc nằm giữa **client** và **server**. Khi người dùng gửi request từ client, dữ liệu sẽ đi qua middleware trước khi đến controller, và response cũng sẽ đi qua middleware trước khi trả về client.

```
Client → Middleware → Controller → Middleware → Client
```

**Ứng dụng thực tế:**
- Xác thực người dùng (Authentication)
- Phân quyền truy cập (Authorization) 
- Kiểm tra CSRF token
- Rate limiting (giới hạn số request)
- Ghi log request
- Xử lý CORS

---

## Cách tạo Middleware

### Tạo Middleware mới

```bash
php artisan make:middleware --help
```

**Tạo middleware CheckAdmin:**
```bash
php artisan make:middleware CheckAdmin
```

Middleware sẽ được tạo trong `app/Http/Middleware/CheckAdmin.php`

### Cấu trúc Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Logic xử lý middleware ở đây
        
        return $next($request); // Cho phép request tiếp tục
    }
}
```

**Hàm `handle()`** là nơi xử lý logic chính của middleware:
- `$request`: Object chứa thông tin request
- `$next`: Closure để chuyển request đến middleware/controller tiếp theo

---

## Đăng ký Middleware trong Kernel.php

Vào file `app/Http/Kernel.php` để xem và đăng ký middleware.

### 3 loại Middleware

#### 1. **Global Middleware**
```php
protected $middleware = [
    \App\Http\Middleware\TrustProxies::class,
    \App\Http\Middleware\HandleCors::class,
    // Middleware này chạy cho MỌI request
];
```

**Đặc điểm:**
- Chạy với **tất cả** request gửi lên server
- Không thể bỏ qua
- Thứ tự thực thi quan trọng

#### 2. **Group Middleware** 
```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        // Áp dụng cho nhóm routes 'web'
    ],

    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // Áp dụng cho nhóm routes 'api'
    ],
];
```

**Sự khác biệt giữa 'web' và 'api':**

| Đặc điểm | Web Group | API Group |
|----------|-----------|-----------|
| **Session** | Có hỗ trợ session | Không có session (stateless) |
| **CSRF Protection** | Có kiểm tra CSRF token | Không có CSRF protection |
| **Cookie Encryption** | Mã hóa cookies | Không cần cookies |
| **View Error Pages** | Trả về HTML error pages | Trả về JSON error responses |
| **Rate Limiting** | Mặc định 60 req/min | Mặc định 60 req/min |
| **Use Case** | Web application, form submissions | API endpoints, mobile apps |

#### 3. **Alias Middleware (Route Middleware)**
```php
protected $middlewareAliases = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'admin' => \App\Http\Middleware\CheckAdmin::class, // Thêm middleware custom
];
```

**Đặc điểm:**
- Áp dụng cho các route cụ thể
- Có thể kết hợp nhiều middleware
- Linh hoạt trong việc áp dụng

---

## Thực hiện với CheckAdmin Middleware

### 1. Thêm Alias Middleware vào Kernel.php

```php
// app/Http/Kernel.php
protected $middlewareAliases = [
    // ... existing middleware
    'admin' => \App\Http\Middleware\CheckAdmin::class,
];
```

### 2. Setup logic cho CheckAdmin.php

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // Kiểm tra user có tồn tại và là admin không
        if ($user && $user->is_admin) {
            return $next($request); // Cho phép tiếp tục
        }
        
        // Nếu không phải admin, trả về lỗi 401 Unauthorized
        abort(401, 'Unauthorized. Admin access required.');
    }
}
```

**Logic giải thích:**
- Lấy thông tin user hiện tại qua `auth()->user()`
- Kiểm tra user tồn tại và có quyền admin (`is_admin = true`)
- Nếu hợp lệ: cho phép request tiếp tục với `$next($request)`
- Nếu không hợp lệ: dừng request và trả về lỗi 401

### 3. Áp dụng Middleware cho Route cụ thể

```php
// routes/web.php
Route::get('/users/create', [UserController::class, 'create'])
    ->name('users.create')
    ->middleware(['admin']); // Áp dụng middleware admin

// Hoặc áp dụng nhiều middleware
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->name('admin.dashboard')
    ->middleware(['auth', 'admin']); // Cần đăng nhập VÀ là admin
```

### 4. Test với 2 trường hợp

**Trường hợp 1: Chưa đăng nhập**
```php
// Truy cập /users/create → Lỗi 401 Unauthorized
```

**Trường hợp 2: Đã đăng nhập (để test)**
```php
// routes/web.php (chỉ để test)
Auth::loginUsingId(1); // Đăng nhập user có ID = 1

// Lưu ý: Chỉ dùng để test, không để trong production
```

---

## Các kiến thức khác thường dùng

### 1. **Middleware Parameters**
```php
// Truyền tham số cho middleware
Route::get('/posts', [PostController::class, 'index'])
    ->middleware('role:admin,editor');

// Trong middleware handle method
public function handle($request, Closure $next, ...$roles)
{
    if (in_array($user->role, $roles)) {
        return $next($request);
    }
    abort(403);
}
```

### 2. **Before & After Middleware**
```php
public function handle($request, Closure $next)
{
    // Before middleware - trước khi request đến controller
    if (!auth()->check()) {
        return redirect('/login');
    }
    
    $response = $next($request);
    
    // After middleware - sau khi controller xử lý xong
    Log::info('Request completed', ['url' => $request->url()]);
    
    return $response;
}
```

### 3. **Middleware Groups cho Routes**
```php
// Áp dụng group middleware cho nhóm routes
Route::group(['middleware' => ['web']], function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/about', [HomeController::class, 'about']);
});

// Hoặc
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
});
```

### 4. **Excluding Middleware**
```php
// Loại trừ middleware khỏi một route cụ thể
Route::get('/api/public', [ApiController::class, 'public'])
    ->withoutMiddleware([VerifyCsrfToken::class]);
```

### 5. **Middleware Priority**
```php
// app/Http/Kernel.php
protected $middlewarePriority = [
    \Illuminate\Session\Middleware\StartSession::class,
    \App\Http\Middleware\Authenticate::class,
    \App\Http\Middleware\CheckAdmin::class,
    // Thứ tự ưu tiên thực thi
];
```

---

## Quiz - Đáp án

### 1. Middleware dùng để làm gì?

**Middleware** là một cơ chế **filtering** (lọc) trong Laravel được sử dụng để:

#### **Chức năng chính:**
- **Xác thực (Authentication)**: Kiểm tra user đã đăng nhập chưa
- **Phân quyền (Authorization)**: Kiểm tra quyền truy cập của user
- **Bảo mật**: Validation, CSRF protection, XSS prevention
- **Rate Limiting**: Giới hạn số lượng request từ một IP
- **Logging & Monitoring**: Ghi log request, response time
- **Data Transformation**: Modify request/response data
- **CORS Handling**: Xử lý Cross-Origin Resource Sharing

#### **Workflow:**
```
HTTP Request → Global Middleware → Route Middleware → Controller → Response → Middleware → HTTP Response
```

#### **Ví dụ thực tế:**
```php
// Middleware xác thực
if (!auth()->check()) {
    return redirect('/login');
}

// Middleware phân quyền  
if (!$user->hasPermission('delete_posts')) {
    abort(403, 'Access denied');
}

// Middleware rate limiting
if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
    abort(429, 'Too many requests');
}
```

### 2. Phân biệt Global Middleware, Group Middleware và Route Middleware

| Đặc điểm | Global Middleware | Group Middleware | Route Middleware |
|----------|-------------------|------------------|------------------|
| **Phạm vi áp dụng** | Tất cả requests | Nhóm routes cụ thể | Route riêng lẻ |
| **Vị trí đăng ký** | `$middleware` array | `$middlewareGroups` array | `$middlewareAliases` array |
| **Cách sử dụng** | Tự động | Áp dụng cho route group | Chỉ định trong route |
| **Có thể tắt** | Không | Không | Có |
| **Thứ tự thực thi** | Luôn đầu tiên | Sau Global | Sau Group |

#### **Global Middleware**
```php
// Kernel.php
protected $middleware = [
    \App\Http\Middleware\TrustProxies::class,
    \Illuminate\Http\Middleware\HandleCors::class,
];
```

**Đặc điểm:**
- ✅ Chạy với **MỌI** request (không thể bỏ qua)
- ✅ Xử lý các vấn đề bảo mật cơ bản
- ✅ Thiết lập environment cho request
- ❌ Không linh hoạt, không thể customize cho từng route

**Use cases:** CORS, Proxy handling, Basic security

#### **Group Middleware**
```php
// Kernel.php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Session\Middleware\StartSession::class,
    ],
    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];

// routes/web.php (tự động áp dụng 'web' group)
Route::get('/', [HomeController::class, 'index']);

// routes/api.php (tự động áp dụng 'api' group) 
Route::get('/users', [UserController::class, 'index']);
```

**Đặc điểm:**
- ✅ Áp dụng cho **nhóm routes** có đặc điểm chung
- ✅ Tách biệt logic web và API
- ✅ Dễ quản lý cho các route cùng loại
- ❌ Ít linh hoạt hơn route middleware

**Use cases:** Web vs API routes, Admin panel routes, Public vs Private sections

#### **Route Middleware (Alias Middleware)**
```php
// Kernel.php
protected $middlewareAliases = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'admin' => \App\Http\Middleware\CheckAdmin::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
];

// routes/web.php
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth', 'admin']);

Route::post('/posts', [PostController::class, 'store'])
    ->middleware(['auth', 'verified']);
```

**Đặc điểm:**
- ✅ **Linh hoạt nhất** - áp dụng cho route cụ thể
- ✅ Có thể kết hợp nhiều middleware
- ✅ Có thể truyền parameters
- ✅ Có thể loại trừ khỏi route
- ❌ Cần khai báo thủ công cho từng route

**Use cases:** Authentication, Role-based access, Feature flags, A/B testing

#### **Thứ tự thực thi:**
```
1. Global Middleware (luôn chạy đầu tiên)
2. Group Middleware (web/api group)  
3. Route Middleware (auth, admin, etc.)
4. Controller Method
5. Route Middleware (after processing)
6. Group Middleware (after processing)
7. Global Middleware (after processing)
```

#### **Ví dụ tổng hợp:**
```php
// Request đến /admin/users/create sẽ đi qua:

// 1. Global Middleware
\App\Http\Middleware\TrustProxies::class

// 2. Web Group Middleware  
\App\Http\Middleware\EncryptCookies::class
\App\Http\Middleware\VerifyCsrfToken::class

// 3. Route Middleware
'auth' => kiểm tra đăng nhập
'admin' => kiểm tra quyền admin

// 4. UserController@create

// 5. Response qua middleware theo thứ tự ngược lại
```