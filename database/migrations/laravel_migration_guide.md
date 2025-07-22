# Laravel Migration & Model Guide

## Tạo Table/Models Mới

### Cách 1: Tạo Migration Riêng Biệt

```bash
# Xem các gợi ý câu lệnh
php artisan make:migration --help

# Tạo migration để tạo table mới
php artisan make:migration create_tasks_table --create=tasks
```

**Giải thích:**
- `--create=tasks`: Tạo migration với template để tạo bảng mới có tên `tasks`
- File migration sẽ được tạo trong thư mục `database/migrations/`
- Tên file có format: `YYYY_MM_DD_HHMMSS_create_tasks_table.php`

### Cách 2: Tạo Đồng Thời Model và Migration

```bash
# Xem gợi ý các câu lệnh
php artisan make:model --help

# Tạo đồng thời model Task và migration
php artisan make:model Task -m
```

**Giải thích:**
- `-m` hoặc `--migration`: Tạo migration cùng với model
- Model sẽ được tạo trong `app/Models/Task.php`
- Migration sẽ được tạo trong `database/migrations/`

**Các option khác hữu ích:**
```bash
php artisan make:model Task -mfs  # Tạo model + migration + factory + seeder
php artisan make:model Task -a    # Tạo tất cả (model, migration, factory, seeder, controller, policy)
```

## Cập Nhật Database với Migration

### Tạo Migration Để Cập Nhật Bảng Hiện Có

```bash
# Tạo migration để cập nhật bảng users
php artisan make:migration update_users_table --table=users
```

**Giải thích:**
- `--table=users`: Tạo migration với template để chỉnh sửa bảng hiện có
- Sử dụng khi muốn thêm/xóa/sửa cột trong bảng đã tồn tại

### Ví Dụ Nội Dung Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->timestamp('last_login_at')->nullable();
            $table->dropColumn('name'); // Xóa cột name
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'last_login_at']);
            $table->string('name')->after('id'); // Khôi phục cột name
        });
    }
};
```

### Chạy Migration

```bash
# Chạy tất cả migration chưa được thực thi
php artisan migrate

# Xem trạng thái migration
php artisan migrate:status
```

### Rollback Migration

```bash
# Rollback batch migration gần nhất
php artisan migrate:rollback

# Rollback nhiều batch
php artisan migrate:rollback --step=3

# Rollback tất cả migration
php artisan migrate:reset

# Rollback tất cả và chạy lại
php artisan migrate:refresh

# Rollback, chạy lại và seed data
php artisan migrate:refresh --seed
```

## Các Câu Lệnh Migration Hữu Ích Khác

### Kiểm Tra và Xử Lý Migration

```bash
# Xem danh sách migration và trạng thái
php artisan migrate:status

# Chạy migration trong môi trường production (cần xác nhận)
php artisan migrate --force

# Tạo migration để tạo bảng pivot (many-to-many relationship)
php artisan make:migration create_user_role_table --create=user_role
```

### Fresh Migration (Khuyến Cáo Chỉ Dùng Trong Development)

```bash
# Drop all tables và chạy lại tất cả migration
php artisan migrate:fresh

# Drop all tables, chạy migration và seed data
php artisan migrate:fresh --seed
```

## Best Practices

### 1. Luôn Viết Hàm `down()`
- Đảm bảo hàm `down()` có thể hoàn tác được những thay đổi trong `up()`
- Điều này giúp rollback an toàn khi cần thiết

### 2. Sử Dụng Nullable Cho Cột Mới
```php
$table->string('new_column')->nullable();
```
- Tránh lỗi khi thêm cột vào bảng đã có data

### 3. Backup Trước Khi Migration Trên Production
```bash
# Luôn backup database trước khi migrate
mysqldump -u username -p database_name > backup.sql
```

### 4. Kiểm Tra Migration Trước Khi Deploy
```bash
# Kiểm tra SQL sẽ được chạy (Laravel 8+)
php artisan migrate --pretend
```

## Lưu Ý Quan Trọng

- **Không chỉnh sửa migration đã được deploy**: Tạo migration mới thay vì sửa migration cũ
- **Test migration trên môi trường staging**: Đảm bảo migration hoạt động đúng trước khi deploy production
- **Sử dụng transactions**: Laravel tự động wrap migration trong transaction (trừ một số database không hỗ trợ)
- **Đặt tên migration rõ ràng**: Sử dụng tên mô tả chức năng của migration