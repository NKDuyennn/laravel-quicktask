# Hướng Dẫn Testing cho Laravel QuickTask

## Cài Đặt và Chuẩn Bị

### 1. Cài đặt dependencies testing
```bash
# Đảm bảo PHPUnit đã được cài đặt
composer install

# Cài đặt thêm các package hỗ trợ testing (nếu cần)
composer require --dev phpunit/phpunit
```

### 2. Cấu hình database testing
Đảm bảo file `.env.testing` có cấu hình:
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
MAIL_MAILER=array
```

### 3. Chạy migrations và seeders cho testing
```bash
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing
```

## Cấu Trúc Test

### Unit Tests (`tests/Unit/`)
- **Models/UserTest.php**: Test cho User model
- **Models/TaskTest.php**: Test cho Task model  
- **Models/RoleTest.php**: Test cho Role model
- **Helpers/DateHelperTest.php**: Test cho helper functions
- **Policies/UserPolicyTest.php**: Test cho user policies

### Feature Tests (`tests/Feature/`)
- **Controllers/UserControllerTest.php**: Test cho UserController
- **Controllers/TaskControllerTest.php**: Test cho TaskController

## Chạy Tests

### Chạy tất cả tests
```bash
php artisan test
```

### Chạy unit tests
```bash
php artisan test --testsuite=Unit
```

### Chạy feature tests  
```bash
php artisan test --testsuite=Feature
```

### Chạy test specific file
```bash
php artisan test tests/Unit/Models/UserTest.php
```

### Chạy test với coverage
```bash
php artisan test --coverage
```

### Chạy test với detail output
```bash
php artisan test --verbose
```

## Các Test Case Đã Được Tạo

### 1. User Model Tests
- ✅ Tạo user với dữ liệu hợp lệ
- ✅ Accessor `full_name` hoạt động đúng
- ✅ Mutator `username` tạo slug
- ✅ Quan hệ `hasMany` với Task
- ✅ Quan hệ `belongsToMany` với Role
- ✅ Admin user được gán role admin khi tạo
- ✅ User thường được gán role user khi tạo
- ✅ Không thể xóa admin user cuối cùng
- ✅ Có thể xóa admin khi có nhiều admin
- ✅ Role được cập nhật khi thay đổi trạng thái admin
- ✅ Password được ẩn khi serialize
- ✅ Field `is_admin` được bảo vệ khỏi mass assignment

### 2. Task Model Tests
- ✅ Tạo task thành công
- ✅ Quan hệ `belongsTo` với User
- ✅ Có timestamps
- ✅ Sử dụng HasFactory trait

### 3. Role Model Tests
- ✅ Tạo role thành công
- ✅ Quan hệ `belongsToMany` với User
- ✅ Quan hệ có timestamps
- ✅ Sử dụng HasFactory trait
- ✅ Không có field nào bị guarded

### 4. DateHelper Tests
- ✅ `formatDateYMD()` với Carbon instance
- ✅ `formatDateYMD()` với string date
- ✅ `formatDateYMD()` với timestamp
- ✅ `formatDateYMD()` với null trả về 'N/A'
- ✅ `formatDateDMY()` các trường hợp tương tự
- ✅ `formatDateYMDHIS()` các trường hợp tương tự
- ✅ `formatDateDMYHIS()` các trường hợp tương tự
- ✅ Tất cả helper functions tồn tại

### 5. UserPolicy Tests
- ✅ Admin có thể xem danh sách users
- ✅ User thường không thể xem danh sách users
- ✅ Admin có thể xem bất kỳ user nào
- ✅ User có thể xem profile của mình
- ✅ User không thể xem profile người khác
- ✅ Admin có thể tạo user
- ✅ User thường không thể tạo user
- ✅ Admin có thể cập nhật bất kỳ user nào
- ✅ User có thể cập nhật profile của mình
- ✅ User không thể cập nhật profile người khác
- ✅ Các quyền delete, restore, forceDelete tương tự

### 6. UserController Tests
- ✅ Admin có thể truy cập users index
- ✅ User thường không thể truy cập users index
- ✅ Guest được redirect đến login
- ✅ Admin có thể xem form tạo user
- ✅ Admin có thể tạo user mới
- ✅ Admin có thể xem chi tiết user
- ✅ User có thể xem chi tiết của mình
- ✅ User không thể xem chi tiết người khác
- ✅ Admin có thể cập nhật user
- ✅ User có thể cập nhật profile của mình
- ✅ Admin có thể xóa user
- ✅ Validation lỗi khi tạo user với dữ liệu không hợp lệ
- ✅ Users index sử dụng eager loading

### 7. TaskController Tests
- ✅ Có thể lấy tất cả tasks
- ✅ Có thể lấy tasks khi không có task nào
- ✅ Index trả về JSON response
- ✅ Task model có relationships đúng
- ✅ Tasks thuộc về user đúng

## Debug và Troubleshooting

### 1. Lỗi Database
```bash
# Reset database testing
php artisan migrate:fresh --env=testing

# Kiểm tra database connection
php artisan tinker --env=testing
>>> DB::connection()->getPdo()
```

### 2. Lỗi Factory
```bash
# Test factory riêng lẻ
php artisan tinker --env=testing
>>> User::factory()->create()
>>> Task::factory()->create()
>>> Role::factory()->create()
```

### 3. Xem chi tiết test failure
```bash
php artisan test --verbose --stop-on-failure
```

### 4. Debug trong test
```php
// Trong test method
dd($response->getContent());
dump($user->toArray());
```

## Best Practices

### 1. Test Naming
- Sử dụng snake_case cho tên test methods
- Bắt đầu với `test_` hoặc sử dụng `/** @test */` annotation
- Tên test nên mô tả hành vi được test

### 2. Test Structure
- **Arrange**: Chuẩn bị dữ liệu
- **Act**: Thực hiện hành động
- **Assert**: Kiểm tra kết quả

### 3. Database Testing
- Luôn sử dụng `RefreshDatabase` trait
- Tạo dữ liệu test bằng factories
- Không phụ thuộc vào dữ liệu có sẵn

### 4. Authentication Testing
- Sử dụng `actingAs()` để mock user đăng nhập
- Test cả authenticated và unauthenticated cases
- Test authorization với các roles khác nhau

## Mở Rộng Tests

### Thêm tests cho:
1. **Middleware tests**
2. **API endpoint tests**
3. **Form request validation tests**
4. **Event/Listener tests**
5. **Job tests**
6. **Service class tests**

### Test Coverage Goals:
- Unit tests: >90%
- Feature tests: >80%
- Overall: >85%

## Tài Liệu Tham Khảo
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Factories](https://laravel.com/docs/database-testing#defining-model-factories)
