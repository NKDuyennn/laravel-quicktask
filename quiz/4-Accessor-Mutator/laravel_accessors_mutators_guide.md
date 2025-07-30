# Laravel Accessors & Mutators - Hướng dẫn và Quiz

## Giới thiệu về Accessors & Mutators

Accessors & Mutators trong Laravel là các phương thức đặc biệt giúp format dữ liệu khi lấy ra (get) và đưa vào (set) database. Hiểu đơn giản, chúng giống như **getter** và **setter** trong lập trình hướng đối tượng.

**Tài liệu tham khảo:** [Laravel Eloquent Mutators](https://laravel.com/docs/12.x/eloquent-mutators#accessors-and-mutators)

### Khái niệm cơ bản:
- **Accessors (Getter):** Định nghĩa cách dữ liệu được format khi lấy ra từ model
- **Mutators (Setter):** Định nghĩa cách dữ liệu được xử lý trước khi lưu vào database

---

## Cách Setup Accessors & Mutators

### Cấu trúc cơ bản của Attribute

Từ Laravel 9+, chúng ta sử dụng class `Attribute` để định nghĩa accessors và mutators:

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function attributeName(): Attribute
{
    return Attribute::make(
        get: fn ($value) => // Logic cho accessor,
        set: fn ($value) => // Logic cho mutator,
    );
}
```

---

## Accessors - Định nghĩa cách lấy dữ liệu

### Ví dụ: Tạo Accessor cho fullName

**Trong Model User:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Model
{
    protected $fillable = ['first_name', 'last_name', 'email'];

    /**
     * Accessor cho full name
     * Kết hợp first_name và last_name thành full name
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->attributes['first_name'] . ' ' . $this->attributes['last_name'],
        );
    }
}
```

### Test trong Tinker

```bash
php artisan tinker
```

```php
// Tất cả các cách gọi sau đều cho kết quả giống nhau
User::find(1)->fullname      // 'John Doe'
User::find(1)->fullName      // 'John Doe' 
User::find(1)->full_name     // 'John Doe'
```

**Giải thích:** Laravel tự động convert giữa các format naming convention:
- `fullName` (camelCase - tên method)
- `full_name` (snake_case - cách gọi thông thường)
- `fullname` (lowercase)

### Ví dụ khác về Accessors

```php
// Format ngày tháng
protected function createdAtFormatted(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $this->created_at->format('d/m/Y H:i'),
    );
}

// Capitalize tên
protected function name(): Attribute
{
    return Attribute::make(
        get: fn ($value) => ucfirst($value),
    );
}

// Tính tuổi từ ngày sinh
protected function age(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $this->birth_date ? 
            now()->diffInYears($this->birth_date) : null,
    );
}
```

---

## Mutators - Định nghĩa cách lưu dữ liệu

### Ví dụ: Tạo Mutator cho username

**Trong Model User:**

```php
use Illuminate\Support\Str;

class User extends Model
{
    /**
     * Mutator cho username
     * Tự động chuyển username thành slug format
     */
    protected function username(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Str::slug($value),
        );
    }
}
```

### Test trong Tinker

```php
$u = User::find(1);
$u->username = 'test user';      // Gán giá trị mới
echo $u->username;               // Output: 'test user' (chưa qua mutator)

$u->username;                    // Output: 'test-user' (đã qua mutator)
$u->save();                      // Lưu vào database với giá trị 'test-user'
```

### Giải thích hành vi của Mutator

**Tại sao có sự khác biệt này?**

1. **Khi gán `$u->username = 'test user'`:**
   - Laravel lưu giá trị gốc vào `$attributes` array
   - Mutator chưa được kích hoạt
   - Echo ngay lập tức sẽ trả về giá trị gốc

2. **Khi gọi `$u->username` lần sau:**
   - Laravel phát hiện có mutator được định nghĩa
   - Áp dụng logic mutator: `Str::slug('test user')` = `'test-user'`
   - Trả về giá trị đã được xử lý

3. **Khi gọi `$u->save()`:**
   - Giá trị đã qua mutator (`'test-user'`) được lưu vào database
   - Không phải giá trị gốc (`'test user'`)

### Ví dụ khác về Mutators

```php
// Hash password tự động
protected function password(): Attribute
{
    return Attribute::make(
        set: fn ($value) => bcrypt($value),
    );
}

// Chuyển email thành lowercase
protected function email(): Attribute
{
    return Attribute::make(
        set: fn ($value) => strtolower($value),
    );
}

// Loại bỏ khoảng trắng thừa
protected function name(): Attribute
{
    return Attribute::make(
        set: fn ($value) => trim($value),
    );
}

// Chuyển đổi số điện thoại
protected function phone(): Attribute
{
    return Attribute::make(
        set: fn ($value) => preg_replace('/[^0-9]/', '', $value),
    );
}
```

---

## Kết hợp Accessor và Mutator

Bạn có thể định nghĩa cả accessor và mutator cho cùng một attribute:

```php
protected function name(): Attribute
{
    return Attribute::make(
        get: fn ($value) => ucfirst($value),           // Capitalize khi lấy ra
        set: fn ($value) => strtolower(trim($value)),  // Lowercase + trim khi lưu vào
    );
}
```

---

## Ví dụ thực tế

### Model Product với nhiều Accessors/Mutators

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'description', 'slug'];

    /**
     * Tự động tạo slug từ name
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Str::slug($this->name ?? $value),
        );
    }

    /**
     * Format giá với đơn vị VNĐ
     */
    protected function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => number_format($this->price) . ' VNĐ',
        );
    }

    /**
     * Lưu giá dưới dạng cents (x100)
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    /**
     * Excerpt của description
     */
    protected function excerpt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Str::limit($this->description, 100),
        );
    }
}
```

### Sử dụng:

```php
$product = new Product();
$product->name = 'iPhone 15 Pro Max';
$product->price = 29990000;  // Sẽ được lưu là 2999000000 (x100)
$product->description = 'Đây là mô tả rất dài về sản phẩm...';

$product->save();

// Khi lấy ra:
echo $product->price;           // 29990000 (đã chia 100)
echo $product->price_formatted; // 29.990.000 VNĐ
echo $product->excerpt;         // Đây là mô tả rất dài về sản phẩm...
echo $product->slug;            // iphone-15-pro-max
```

---

## Tips và Best Practices

### 1. Naming Convention
```php
// Method name: camelCase
protected function firstName(): Attribute

// Có thể gọi bằng nhiều cách:
$user->firstName    // camelCase
$user->first_name   // snake_case (recommended)
$user->firstname    // lowercase
```

### 2. Performance Considerations
```php
// Nên cache kết quả nếu accessor phức tạp
protected function expensiveCalculation(): Attribute
{
    return Attribute::make(
        get: function ($value) {
            if (!isset($this->attributes['_expensive_cache'])) {
                $this->attributes['_expensive_cache'] = $this->doExpensiveWork();
            }
            return $this->attributes['_expensive_cache'];
        },
    );
}
```

### 3. Null Safety
```php
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn ($value) => trim(
            ($this->attributes['first_name'] ?? '') . ' ' . 
            ($this->attributes['last_name'] ?? '')
        ) ?: null,
    );
}
```

---

## QUIZ - Kiểm tra kiến thức

### Câu 1: Accessors/Mutators dùng để làm gì?

**Trả lời:**

Accessors và Mutators là các phương thức đặc biệt trong Laravel Eloquent được sử dụng để:

#### **Accessors (Getter):**
- **Mục đích:** Format và biến đổi dữ liệu khi lấy ra từ model
- **Khi được gọi:** Mỗi khi truy cập attribute từ model instance
- **Use cases phổ biến:**
  - Format ngày tháng: `2024-01-15` → `15/01/2024`
  - Kết hợp các trường: `first_name + last_name` → `full_name`
  - Tính toán giá trị: `birth_date` → `age`
  - Format tiền tệ: `1000000` → `1.000.000 VNĐ`
  - Capitalize text: `john doe` → `John Doe`

#### **Mutators (Setter):**
- **Mục đích:** Xử lý và biến đổi dữ liệu trước khi lưu vào database
- **Khi được gọi:** Khi gán giá trị cho attribute của model
- **Use cases phổ biến:**
  - Hash password: `123456` → `$2y$10$...`
  - Tạo slug: `Hello World` → `hello-world`
  - Normalize data: `JOHN@GMAIL.COM` → `john@gmail.com`
  - Sanitize input: `  Hello World  ` → `Hello World`
  - Convert format: `01/01/2024` → `2024-01-01`

#### **Lợi ích:**
1. **Tự động hóa:** Không cần remember format dữ liệu mỗi lần sử dụng
2. **Consistency:** Đảm bảo dữ liệu luôn có format nhất quán
3. **Clean Code:** Logic xử lý dữ liệu được tập trung tại model
4. **Reusable:** Có thể sử dụng ở bất kỳ đâu mà model được gọi

### Câu 2: Tạo Accessors/Mutators như thế nào?

**Trả lời:**

#### **Cú pháp cơ bản (Laravel 9+):**

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Model
{
    protected function attributeName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => // Logic cho accessor,
            set: fn ($value) => // Logic cho mutator,
        );
    }
}
```

#### **Các bước tạo chi tiết:**

**Bước 1: Import class Attribute**
```php
use Illuminate\Database\Eloquent\Casts\Attribute;
```

**Bước 2: Tạo method với naming convention**
- Method name phải là `camelCase`
- Return type phải là `Attribute`
- Method phải là `protected`

**Bước 3: Định nghĩa logic**

#### **Chỉ có Accessor:**
```php
protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $this->first_name . ' ' . $this->last_name,
    );
}
```

#### **Chỉ có Mutator:**
```php
protected function email(): Attribute
{
    return Attribute::make(
        set: fn ($value) => strtolower($value),
    );
}
```

#### **Cả Accessor và Mutator:**
```php
protected function name(): Attribute
{
    return Attribute::make(
        get: fn ($value) => ucfirst($value),          // Format khi lấy
        set: fn ($value) => strtolower(trim($value)), // Xử lý khi lưu
    );
}
```

#### **Ví dụ phức tạp hơn:**

```php
protected function avatar(): Attribute
{
    return Attribute::make(
        get: function ($value) {
            // Nếu có avatar thì return full URL, không thì return default
            if ($value) {
                return asset('storage/avatars/' . $value);
            }
            return asset('images/default-avatar.png');
        },
        set: function ($value) {
            // Xử lý upload file và return filename
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                return $value->store('avatars', 'public');
            }
            return $value;
        },
    );
}

protected function tags(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value ? explode(',', $value) : [],
        set: fn ($value) => is_array($value) ? implode(',', $value) : $value,
    );
}

protected function metadata(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value ? json_decode($value, true) : [],
        set: fn ($value) => json_encode($value),
    );
}
```

#### **Cách gọi Accessors/Mutators:**

```php
// Tất cả các cách sau đều tương đương:
$user->fullName      // camelCase (tên method)
$user->full_name     // snake_case (recommended)
$user->fullname      // lowercase

// Với mutator:
$user->email = 'JOHN@EXAMPLE.COM';  // Sẽ được convert thành 'john@example.com'
$user->save();
```

#### **Lưu ý quan trọng:**

1. **Method visibility:** Phải là `protected`, không phải `public` hay `private`
2. **Return type:** Phải return `Attribute` object
3. **Naming:** Method name sẽ được convert tự động giữa camelCase và snake_case
4. **Performance:** Accessor được gọi mỗi lần access attribute, nên tránh logic quá phức tạp
5. **Database:** Mutator chỉ được áp dụng khi lưu vào database, không phải khi đọc từ database