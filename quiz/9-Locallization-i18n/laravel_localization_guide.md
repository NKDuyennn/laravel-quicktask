# Laravel Localization (i18n) - Hướng dẫn chi tiết

## Giới thiệu
Localization (i18n) là quá trình thiết lập giao diện ứng dụng theo ngôn ngữ của người dùng, giúp ứng dụng có thể hỗ trợ nhiều ngôn ngữ khác nhau.

## Cách tạo file bản dịch

### Cách 1: Tạo thư mục lang thủ công

#### Bước 1: Tạo cấu trúc thư mục
```bash
php artisan lang:publish
# hoặc
php artisan lang:publish --help
```

#### Bước 2: Tạo thư mục ngôn ngữ
- Tạo thư mục `lang/vi` cho tiếng Việt
- Tạo file `vi.json` trong thư mục `lang/vi`

#### Bước 3: Cấu trúc file vi.json
```json
{
    "Welcome": "Chào mừng",
    "Hello": "Xin chào",
    "Dashboard": "Bảng điều khiển",
    "Login": "Đăng nhập",
    "Register": "Đăng ký"
}
```

**Lưu ý:** Laravel có package lang hỗ trợ sẵn tại: https://github.com/Laravel-Lang/lang/tree/main/locales/

### Cách 2: Sử dụng package Laravel-Lang (Khuyến nghị)

#### Bước 1: Cài đặt package
```bash
composer require laravel-lang/common --dev
```

#### Bước 2: Thêm ngôn ngữ
```bash
php artisan lang:add vi
```

#### Bước 3: Cập nhật bản dịch
```bash
php artisan lang:update
```

**Kết quả:** Sau khi hoàn thành, bạn sẽ có thư mục `lang/vi` và file `vi.json` với các bản dịch đã được chuẩn bị sẵn.

**Tham khảo:** 
- GitHub: https://github.com/Laravel-Lang/lang
- Documentation: https://laravel-lang.com/packages-lang.html

## Cách sử dụng bản dịch

### 1. Thay đổi ngôn ngữ mặc định
Trong file `config/app.php`:
```php
'locale' => 'vi', // Thay đổi từ 'en' thành 'vi'
```

### 2. Tạo tính năng chuyển đổi ngôn ngữ động

#### Tạo Controller
```bash
php artisan make:controller LanguageController
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Chuyển đổi ngôn ngữ và lưu vào session
     */
    public function changeLanguage(Request $request, $language)
    {
        // Lưu ngôn ngữ vào session
        Session::put('lang', $language);
        
        // Trở về trang trước đó
        return redirect()->back();
    }
}
```

#### Tạo Middleware
```bash
php artisan make:middleware Localization
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Xử lý request và thiết lập ngôn ngữ
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra session có ngôn ngữ hay không
        if (Session::get('lang')) {
            App::setLocale(Session::get('lang'));
        }
        
        return $next($request);
    }
}
```

#### Đăng ký Middleware

**Trong `app/Http/Kernel.php` (Laravel 10 trở xuống):**
```php
protected $middlewareAliases = [
    'locale' => \App\Http\Middleware\Localization::class,
];

// Hoặc áp dụng cho tất cả route
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\Localization::class,
        // Các middleware khác...
    ],
];
```

**Trong `bootstrap/app.php` (Laravel 11 trở lên):**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\Localization::class,
    ]);
})
```

#### Thêm Route
Trong file `routes/web.php`:
```php
Route::get('/locale/{lang}', [LanguageController::class, 'changeLanguage'])->name('locale');
```

#### Tạo giao diện chuyển đổi ngôn ngữ
Trong file `resources/views/layouts/navigation.blade.php`:
```html
<div class="language-switcher">
    <a href="{{ route('locale', 'vi') }}" class="btn btn-sm">
        {{ __('VI') }}
    </a>
    <a href="{{ route('locale', 'en') }}" class="btn btn-sm">
        {{ __('EN') }}
    </a>
</div>
```

### 3. Sử dụng bản dịch trong code

#### Cách sử dụng cơ bản
```php
// Sử dụng helper function __()
{{ __('Dashboard') }}
{{ __('Welcome') }}

// Sử dụng facade
{{ trans('messages.welcome') }}
```

#### Test route
```
http://localhost:8000/locale/vi
http://localhost:8000/locale/en
```

## Trả lời Quiz

### 1. Cách truyền tham số vào bản dịch

Laravel hỗ trợ truyền tham số vào bản dịch thông qua placeholders:

**Trong file bản dịch (vi.json):**
```json
{
    "welcome_user": "Chào mừng :name đến với website!",
    "items_count": "Bạn có :count sản phẩm trong giỏ hàng",
    "user_profile": "Hồ sơ của :user_name (ID: :user_id)"
}
```

**Cách sử dụng:**
```php
// Truyền 1 tham số
{{ __('welcome_user', ['name' => 'John']) }}
// Kết quả: "Chào mừng John đến với website!"

// Truyền nhiều tham số
{{ __('user_profile', ['user_name' => 'Alice', 'user_id' => 123]) }}
// Kết quả: "Hồ sơ của Alice (ID: 123)"

// Sử dụng trong Controller
$message = __('items_count', ['count' => $cartItems->count()]);
```

**Lưu ý:** Sử dụng dấu `:` trước tên tham số trong chuỗi bản dịch.

### 2. Tạo bản dịch cho số ít và số nhiều (Pluralization)

Laravel hỗ trợ pluralization thông qua pipe (`|`) để phân biệt singular và plural:

**Trong file bản dịch (vi.json):**
```json
{
    "comment_count": "{0} Không có bình luận|{1} :count bình luận|[2,*] :count bình luận",
    "apple_count": "{0} Không có táo|{1} Một quả táo|[2,*] :count quả táo",
    "user_online": "{0} Không có người dùng trực tuyến|{1} :count người dùng trực tuyến|[2,*] :count người dùng trực tuyến"
}
```

**Cú pháp nâng cao:**
```json
{
    "product_review": "{0} Chưa có đánh giá|{1} :count đánh giá|{2} :count đánh giá|[3,10] :count đánh giá|[11,*] :count+ đánh giá"
}
```

**Cách sử dụng:**
```php
// Sử dụng trans_choice() hoặc __() với số lượng
{{ trans_choice('comment_count', 0) }}
// Kết quả: "Không có bình luận"

{{ trans_choice('comment_count', 1, ['count' => 1]) }}
// Kết quả: "1 bình luận"

{{ trans_choice('comment_count', 5, ['count' => 5]) }}
// Kết quả: "5 bình luận"

// Trong Controller
$commentText = trans_choice('comment_count', $comments->count(), [
    'count' => $comments->count()
]);
```

**Ví dụ thực tế:**
```php
// Trong Blade template
@if($comments->count() > 0)
    <h3>{{ trans_choice('comment_count', $comments->count(), ['count' => $comments->count()]) }}</h3>
@else
    <p>{{ trans_choice('comment_count', 0) }}</p>
@endif
```

**Kết quả:**
- 0 comments: "Không có bình luận"
- 1 comment: "1 bình luận"  
- 5 comments: "5 bình luận"

## Mẹo và Best Practices

### 1. Tổ chức file bản dịch
```
lang/
├── en/
│   ├── auth.php
│   ├── messages.php
│   └── validation.php
├── vi/
│   ├── auth.php
│   ├── messages.php
│   └── validation.php
├── en.json
└── vi.json
```

### 2. Sử dụng fallback locale
Trong `config/app.php`:
```php
'fallback_locale' => 'en',
```

### 3. Kiểm tra ngôn ngữ hiện tại
```php
$currentLocale = app()->getLocale();
// hoặc
$currentLocale = App::getLocale();
```

### 4. Validate ngôn ngữ trước khi chuyển đổi
```php
public function changeLanguage(Request $request, $language)
{
    $supportedLanguages = ['en', 'vi'];
    
    if (in_array($language, $supportedLanguages)) {
        Session::put('lang', $language);
    }
    
    return redirect()->back();
}
```

## Kết luận

Localization trong Laravel cung cấp một hệ thống mạnh mẽ và linh hoạt để xây dựng ứng dụng đa ngôn ngữ. Bằng cách sử dụng package Laravel-Lang và thiết lập middleware phù hợp, bạn có thể dễ dàng tạo một trải nghiệm người dùng tốt cho nhiều quốc gia khác nhau.