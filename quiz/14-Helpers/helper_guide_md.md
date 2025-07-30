# Chapter 14: Helper trong Laravel - Giải thích và Ứng dụng

## Tác dụng của Helper

Helper trong Laravel là những function tiện ích được định nghĩa globally, cho phép bạn sử dụng ở bất kỳ đâu trong ứng dụng mà không cần import hay khởi tạo class. Helper giúp:

- **Tái sử dụng code dễ dàng:** Viết một lần, dùng nhiều nơi
- **Xử lý các logic phổ biến:** Format date, string, number, currency...
- **Làm code sạch và dễ đọc hơn:** Thay thế những đoạn code lặp lại
- **Tránh lặp lại code:** DRY principle (Don't Repeat Yourself)
- **Tăng hiệu suất phát triển:** Không cần tạo class cho logic đơn giản

---

## Cách tạo file Helper trong Laravel

### Bước 1: Tạo thư mục và file helper

```bash
# Tạo thư mục Helpers nếu chưa có
mkdir app/Helpers

# Tạo file DateHelper.php
touch app/Helpers/DateHelper.php
```

### Bước 2: Đăng ký helper trong `composer.json`

Thêm vào phần `autoload` trong file `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        },
        "files": [
            "app/Helpers/DateHelper.php"
        ]
    }
}
```

### Bước 3: Chạy lệnh autoload

```bash
composer dump-autoload
```

---

## Áp dụng vào project

### Tạo DateHelper.php

Tạo file `app/Helpers/DateHelper.php` với nội dung:

```php
<?php

if (!function_exists('formatDateYMD')) {
    /**
     * Format date to Y/m/d format
     *
     * @param mixed $date
     * @return string
     */
    function formatDateYMD($date)
    {
        if (!$date) {
            return 'N/A';
        }
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format('Y/m/d');
    }
}

if (!function_exists('formatDateDMY')) {
    /**
     * Format date to d/m/Y format
     *
     * @param mixed $date
     * @return string
     */
    function formatDateDMY($date)
    {
        if (!$date) {
            return 'N/A';
        }
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format('d/m/Y');
    }
}

if (!function_exists('formatDateDMYWithTime')) {
    /**
     * Format date to d/m/Y H:i format
     *
     * @param mixed $date
     * @return string
     */
    function formatDateDMYWithTime($date)
    {
        if (!$date) {
            return 'N/A';
        }
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format('d/m/Y H:i');
    }
}

if (!function_exists('formatDateYMDWithTime')) {
    /**
     * Format date to Y/m/d H:i format
     *
     * @param mixed $date
     * @return string
     */
    function formatDateYMDWithTime($date)
    {
        if (!$date) {
            return 'N/A';
        }
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format('Y/m/d H:i');
    }
}

if (!function_exists('formatDateVietnamese')) {
    /**
     * Format date to Vietnamese format
     *
     * @param mixed $date
     * @return string
     */
    function formatDateVietnamese($date)
    {
        if (!$date) {
            return 'N/A';
        }
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        $vietnameseDays = [
            'Monday' => 'Thứ Hai',
            'Tuesday' => 'Thứ Ba', 
            'Wednesday' => 'Thứ Tư',
            'Thursday' => 'Thứ Năm',
            'Friday' => 'Thứ Sáu',
            'Saturday' => 'Thứ Bảy',
            'Sunday' => 'Chủ Nhật'
        ];
        
        $dayName = $vietnameseDays[$date->format('l')];
        
        return $dayName . ', ngày ' . $date->format('d/m/Y \l\ú\c H:i');
    }
}
```

### Cập nhật show.blade.php

Áp dụng helper vào file `resources/views/users/show.blade.php`:

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
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">{{ __('User Information') }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Các field khác giữ nguyên -->
                            
                            <!-- Updated Created At section using Helper -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Created At') }}
                                </label>
                                <div class="mt-1 space-y-1">
                                    <!-- Format d/m/Y H:i -->
                                    <p class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="font-medium">D/M/Y:</span> {{ formatDateDMYWithTime($user->created_at) }}
                                    </p>
                                    <!-- Format Y/m/d H:i -->
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Y/M/D:</span> {{ formatDateYMDWithTime($user->created_at) }}
                                    </p>
                                    <!-- Vietnamese format -->
                                    <p class="text-xs text-gray-500 dark:text-gray-500 italic">
                                        {{ formatDateVietnamese($user->created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Updated Tasks Section with date helpers -->
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

                    <!-- Action Buttons giữ nguyên -->
                    <div class="flex flex-wrap gap-3">
                        <!-- ... -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

---

## Hướng dẫn triển khai chi tiết

### 1. Tạo file Helper

```bash
# Tạo thư mục nếu chưa có
mkdir app/Helpers

# Tạo file DateHelper.php và copy nội dung helper functions
```

### 2. Đăng ký Helper trong composer.json

Cập nhật file `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        },
        "files": [
            "app/Helpers/DateHelper.php"
        ]
    }
}
```

### 3. Chạy lệnh autoload

```bash
composer dump-autoload
```

### 4. Cập nhật file show.blade.php

Copy và thay thế nội dung file hiện tại với code đã cập nhật helper.

---

## Kết quả đạt được

Sau khi áp dụng helper, phần hiển thị `created_at` sẽ có các định dạng:

- **D/M/Y:** `30/07/2025 14:30` (định dạng Việt Nam)
- **Y/M/D:** `2025/07/30 14:30` (định dạng quốc tế)  
- **Vietnamese:** `Thứ Tư, ngày 30/07/2025 lúc 14:30`

### Ưu điểm của Helper

1. **Tái sử dụng:** Các helper functions có thể sử dụng ở bất kỳ đâu trong project
2. **Không cần import:** Functions có sẵn globally, không cần use hay import
3. **Dễ bảo trì:** Logic tập trung tại một nơi, dễ sửa đổi
4. **Performance:** Không cần khởi tạo class, gọi trực tiếp function

---

## Mở rộng Helper

### Tạo thêm helper functions khác

```php
// String Helper
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currency = 'VND') {
        return number_format($amount) . ' ' . $currency;
    }
}

// Array Helper  
if (!function_exists('arrayToString')) {
    function arrayToString($array, $separator = ', ') {
        return implode($separator, $array);
    }
}

// Status Helper
if (!function_exists('getStatusBadge')) {
    function getStatusBadge($status) {
        $badges = [
            'active' => '<span class="badge badge-success">Active</span>',
            'inactive' => '<span class="badge badge-danger">Inactive</span>',
            'pending' => '<span class="badge badge-warning">Pending</span>',
        ];
        
        return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }
}
```

### Sử dụng trong Blade

```php
<!-- Currency Helper -->
<p>Giá: {{ formatCurrency(150000) }}</p>
<!-- Output: Giá: 150,000 VND -->

<!-- Array Helper -->
<p>Tags: {{ arrayToString(['Laravel', 'PHP', 'Helper']) }}</p>
<!-- Output: Tags: Laravel, PHP, Helper -->

<!-- Status Helper -->
{!! getStatusBadge($user->status) !!}
```

---

## Best Practices

1. **Luôn kiểm tra function_exists()** để tránh conflict
2. **Đặt tên function rõ ràng** và có prefix nếu cần
3. **Thêm docblock** cho từng function
4. **Handle null/empty values** một cách an toàn
5. **Nhóm các helper** theo chức năng (DateHelper, StringHelper...)
6. **Test helper functions** để đảm bảo hoạt động đúng

Các helper này giúp code Laravel của bạn sạch hơn, dễ đọc hơn và tái sử dụng tốt hơn!