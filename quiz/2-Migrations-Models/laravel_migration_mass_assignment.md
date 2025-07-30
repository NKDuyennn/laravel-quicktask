# Laravel Migration và Mass Assignment - Câu hỏi và Trả lời

## PHẦN 1: MIGRATION

### 1. Migration là gì?

Migration là một tính năng quan trọng trong Laravel cho phép quản lý và kiểm soát cấu trúc cơ sở dữ liệu thông qua code. Migration hoạt động như một "version control" cho database, cho phép:

- Tạo, sửa đổi và xóa các bảng trong database
- Thêm, sửa, xóa các cột trong bảng
- Tạo các index, khóa ngoại
- Đồng bộ cấu trúc database giữa các môi trường khác nhau
- Rollback các thay đổi về trạng thái trước đó
- Chia sẻ cấu trúc database với team thông qua version control system

### 2. Hàm up() và down() trong một class migration dùng để làm gì?

**Hàm up():**
- Chứa các lệnh để thực hiện các thay đổi lên database
- Được gọi khi chạy lệnh `php artisan migrate`
- Định nghĩa cấu trúc mới hoặc thay đổi cần áp dụng

**Hàm down():**
- Chứa các lệnh để hoàn tác (rollback) những thay đổi trong hàm up()
- Được gọi khi chạy lệnh `php artisan migrate:rollback`
- Phải thực hiện ngược lại những gì hàm up() đã làm

**Ví dụ:**
```php
public function up()
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('users');
}
```

### 3. Các câu lệnh Migration thông dụng

**Tạo migration:**
```bash
php artisan make:migration create_users_table
php artisan make:migration add_column_to_table_name --table=table_name
php artisan make:migration drop_column_from_table_name --table=table_name
```

**Chạy migration:**
```bash
php artisan migrate                    # Chạy tất cả migration chưa chạy
php artisan migrate --force           # Chạy migration trong production
php artisan migrate:rollback          # Rollback batch migration cuối
php artisan migrate:rollback --step=3 # Rollback 3 batch cuối
php artisan migrate:reset             # Rollback tất cả migration
php artisan migrate:refresh           # Rollback tất cả và chạy lại
php artisan migrate:fresh             # Drop tất cả table và chạy lại
```

**Kiểm tra trạng thái:**
```bash
php artisan migrate:status            # Xem trạng thái các migration
```

**Các method thông dụng trong Schema:**
```php
// Tạo bảng
Schema::create('table_name', function (Blueprint $table) {
    $table->id();                     // Primary key auto increment
    $table->string('name', 100);      // VARCHAR(100)
    $table->text('description');      // TEXT
    $table->integer('age');           // INTEGER
    $table->boolean('is_active');     // BOOLEAN
    $table->timestamp('created_at');  // TIMESTAMP
    $table->timestamps();             // created_at và updated_at
});

// Sửa đổi bảng
Schema::table('table_name', function (Blueprint $table) {
    $table->string('new_column');     // Thêm cột mới
    $table->dropColumn('old_column'); // Xóa cột
    $table->renameColumn('old', 'new'); // Đổi tên cột
});

// Xóa bảng
Schema::drop('table_name');
Schema::dropIfExists('table_name');
```

## PHẦN 2: MASS ASSIGNMENT

### 1. Mass assignment là gì?

Mass assignment là tính năng cho phép gán giá trị cho nhiều thuộc tính của model cùng một lúc thông qua một mảng dữ liệu. Điều này rất tiện lợi khi xử lý dữ liệu từ form hoặc API.

**Ví dụ:**
```php
// Thay vì gán từng thuộc tính
$user = new User;
$user->name = $request->name;
$user->email = $request->email;
$user->password = bcrypt($request->password);
$user->save();

// Mass assignment cho phép làm như này
$user = User::create($request->all());
```

**Lợi ích:**
- Giảm code boilerplate
- Dễ dàng xử lý nhiều trường dữ liệu
- Code ngắn gọn và dễ đọc

### 2. Cách xử lý Mass assignment trong Laravel

Laravel cung cấp hai cách chính để kiểm soát mass assignment:

**2.1. Sử dụng thuộc tính $fillable (Whitelist):**
```php
class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
```

**2.2. Sử dụng thuộc tính $guarded (Blacklist):**
```php
class User extends Model
{
    protected $guarded = [
        'id',
        'is_admin',
        'created_at',
        'updated_at',
    ];
}
```

**2.3. Sử dụng force fill để bỏ qua bảo vệ:**
```php
$user = new User;
$user->forceFill($request->all())->save();
```

### 3. Tại sao Laravel có cả thuộc tính "fillable" và "guarded"?

Laravel cung cấp cả hai thuộc tính để mang lại sự linh hoạt trong việc bảo vệ mass assignment:

**$fillable (Whitelist approach):**
- Chỉ cho phép mass assign các trường được liệt kê
- Phù hợp khi model có ít trường cần cho phép mass assign
- Bảo mật cao hơn vì mặc định tất cả đều bị chặn
- Thường dùng cho các model nhạy cảm

**$guarded (Blacklist approach):**
- Chặn mass assign các trường được liệt kê, cho phép tất cả các trường khác
- Phù hợp khi model có nhiều trường cần cho phép mass assign
- Tiện lợi hơn khi có nhiều trường
- Cần cẩn thận hơn về bảo mật

**Lưu ý:** Không nên sử dụng cả hai cùng lúc. Nếu cả hai đều được định nghĩa, Laravel sẽ ưu tiên $fillable.

### 4. Cách cập nhật các trường trong blacklist

Khi các trường nằm trong blacklist ($guarded) hoặc không có trong whitelist ($fillable), có các cách sau để cập nhật:

**4.1. Gán trực tiếp từng thuộc tính:**
```php
$user = User::find(1);
$user->is_admin = true;
$user->save();
```

**4.2. Sử dụng forceFill():**
```php
$user = User::find(1);
$user->forceFill(['is_admin' => true])->save();

// Hoặc
User::where('id', 1)->update(['is_admin' => true]);
```

**4.3. Tạm thời thêm vào fillable:**
```php
$user = User::find(1);
$user->fillable(array_merge($user->getFillable(), ['is_admin']));
$user->fill(['is_admin' => true]);
$user->save();
```

**4.4. Sử dụng setAttribute():**
```php
$user = User::find(1);
$user->setAttribute('is_admin', true);
$user->save();
```

**4.5. Sử dụng Query Builder (bỏ qua Eloquent protection):**
```php
DB::table('users')->where('id', 1)->update(['is_admin' => true]);
```

**Khuyến nghị:** Nên sử dụng cách gán trực tiếp hoặc forceFill() để đảm bảo tính rõ ràng và bảo mật trong code.