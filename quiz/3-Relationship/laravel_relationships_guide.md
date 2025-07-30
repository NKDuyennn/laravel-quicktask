# Laravel Eloquent Relationships - Hướng dẫn Setup và Sử dụng

## Giới thiệu về Relationships trong Laravel

Laravel Eloquent cung cấp các phương thức mạnh mẽ để quản lý quan hệ giữa các bảng trong cơ sở dữ liệu. Việc setup đúng các quan hệ giúp code trở nên clean và dễ bảo trì hơn.

Tài liệu tham khảo: [Laravel Eloquent Relationships](https://laravel.com/docs/12.x/eloquent-relationships#introduction)

---

## Quan hệ 1-n (One-to-Many Relationship)

### Bước 1: Setup các phương thức trong Models

**Model User (1 - phía có nhiều):**
```php
class User extends Model
{
    // Một user có nhiều tasks
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
```

**Model Task (n - phía thuộc về):**
```php
class Task extends Model
{
    // Một task thuộc về một user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Bước 2: Cập nhật Migration cho khóa ngoại

Cần thêm khóa ngoại `user_id` vào bảng `tasks`:

```php
// Migration file: xxxx_xx_xx_create_tasks_table.php
public function up()
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->foreignId('user_id')->constrained(); // Khóa ngoại
        $table->timestamps();
    });
}
```

**Hoặc nếu đã tạo bảng từ trước, tạo migration mới:**
```bash
php artisan make:migration add_user_id_to_tasks_table --table=tasks
```

```php
public function up()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->foreignId('user_id')->constrained();
    });
}

public function down()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
    });
}
```

### Bước 3: Kiểm tra trong Tinker

```bash
php artisan tinker
```

```php
// Lấy tất cả tasks của user đầu tiên
User::first()->tasks;        // Trả về Collection các Task
User::first()->tasks();      // Trả về Query Builder để tiếp tục query

// Tạo task mới cho user
User::first()->tasks()->create([
    'title' => 'New Task',
    'description' => 'Task description'
]);

// Lấy user từ task
Task::first()->user;
```

---

## Quan hệ n-n (Many-to-Many Relationship)

### Bước 1: Tạo Model và Migration cho Role

```bash
php artisan make:model -m Role
```

**Model Role:**
```php
class Role extends Model
{
    protected $fillable = ['name'];
    
    // Một role có thể thuộc về nhiều users
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
```

### Bước 2: Setup phương thức trong Model User

```php
class User extends Model
{
    // Một user có thể có nhiều roles
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
```

### Bước 3: Tạo bảng trung gian (Pivot Table)

```bash
php artisan make:migration CreateRoleUserPivotTable
```

**Lưu ý quy tắc đặt tên:** Tên bảng trung gian được tạo theo thứ tự alphabetical của hai model name (role_user, không phải user_role).

```php
// Migration: xxxx_xx_xx_create_role_user_pivot_table.php
public function up()
{
    Schema::create('role_user', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('role_id')->constrained()->onDelete('cascade');
        $table->timestamps(); // Optional: nếu muốn track thời gian
        
        // Tránh duplicate records
        $table->unique(['user_id', 'role_id']);
    });
}

public function down()
{
    Schema::dropIfExists('role_user');
}
```

### Bước 4: Chạy Migration

```bash
php artisan migrate
```

### Bước 5: Kiểm tra quan hệ trong Tinker

```bash
php artisan tinker
```

```php
// Sự khác biệt quan trọng:
User::first()->roles;     // Trả về Collection của các Role
User::first()->roles();   // Trả về Query Builder để tiếp tục query

// roles: Lấy ra collection đã load sẵn từ database
// roles(): Lấy ra relationship object để có thể thực hiện thêm query
```

### Bước 6: Tạo các Roles mẫu

```php
App\Models\Role::create(['name' => 'admin']);
App\Models\Role::create(['name' => 'user']);
App\Models\Role::create(['name' => 'editor']);
```

### Bước 7: Thao tác với quan hệ Many-to-Many

#### Attach - Gắn role vào user

```php
// Gắn role có id = 1 cho user đầu tiên
User::first()->roles()->attach(1);
User::first()->roles; // Kiểm tra kết quả

// Gắn thêm role có id = 2
User::first()->roles()->attach(2);
User::first()->roles; // Kiểm tra lại

// Attach nhiều roles cùng lúc
User::first()->roles()->attach([1, 2, 3]);
```

#### Detach - Gỡ bỏ role khỏi user

```php
// Gỡ bỏ role có id = 1
User::first()->roles()->detach(1);
User::first()->roles; // Kiểm tra kết quả

// Gỡ bỏ tất cả roles
User::first()->roles()->detach();
```

#### Toggle - Chuyển đổi trạng thái role

```php
// Nếu user chưa có role id = 2 thì attach, nếu có rồi thì detach
User::first()->roles()->toggle(2);
User::first()->roles; // Kiểm tra

// Chạy lại để thấy sự thay đổi
User::first()->roles()->toggle(2);
User::first()->roles; // Role sẽ biến mất/xuất hiện
```

---

## Optional - Một số hàm hữu ích khác

### Sync - Đồng bộ roles

```php
// Chỉ giữ lại role id = 2, xóa tất cả các role khác
User::first()->roles()->sync([2]);

// Sync với nhiều roles
User::first()->roles()->sync([1, 2, 3]);

// Sync mà không detach các role hiện tại
User::first()->roles()->syncWithoutDetaching([4, 5]);
```

### Làm việc với dữ liệu từ Pivot Table

#### Lấy thông tin pivot

```php
// Lấy role đầu tiên và thông tin pivot
$role = User::first()->roles()->first();
$pivotData = $role->pivot; // Chứa user_id, role_id và các field khác

// Xem các thuộc tính pivot
echo $pivotData->user_id;
echo $pivotData->role_id;
echo $pivotData->created_at; // Nếu có timestamps
```

#### Thêm timestamps vào pivot table

**Cập nhật models:**
```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class)
                    ->withTimestamps(); // Thêm created_at, updated_at
    }
}

class Role extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withTimestamps();
    }
}
```

#### Thêm các trường khác vào pivot table

**Nếu muốn thêm trường `status` vào bảng role_user:**

1. Tạo migration để thêm cột:
```bash
php artisan make:migration add_status_to_role_user_table --table=role_user
```

```php
public function up()
{
    Schema::table('role_user', function (Blueprint $table) {
        $table->string('status')->default('active');
    });
}
```

2. Cập nhật models:
```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class)
                    ->withTimestamps()
                    ->withPivot('status'); // Thêm trường status
    }
}
```

3. Sử dụng:
```php
// Attach với dữ liệu pivot
User::first()->roles()->attach(1, ['status' => 'inactive']);

// Lấy dữ liệu pivot
$role = User::first()->roles()->first();
echo $role->pivot->status; // 'inactive'

// Update pivot data
User::first()->roles()->updateExistingPivot(1, ['status' => 'active']);
```

---

## QUIZ - Kiểm tra kiến thức

### Câu 1: Kể tên các quan hệ của Laravel và phương thức tương ứng

**Trả lời:**

| Quan hệ | Phương thức | Mô tả | Ví dụ |
|---------|-------------|--------|-------|
| **One To One** | `hasOne()`, `belongsTo()` | 1-1, mỗi record ở bảng A tương ứng với đúng 1 record ở bảng B | User hasOne Profile, Profile belongsTo User |
| **One To Many** | `hasMany()`, `belongsTo()` | 1-n, một record ở bảng A có thể có nhiều record ở bảng B | User hasMany Posts, Post belongsTo User |
| **Many To Many** | `belongsToMany()` | n-n, nhiều record ở bảng A tương ứng với nhiều record ở bảng B | User belongsToMany Roles, Role belongsToMany Users |
| **Has One Through** | `hasOneThrough()` | Quan hệ 1-1 qua bảng trung gian | Country hasOneThrough Post (qua User) |
| **Has Many Through** | `hasManyThrough()` | Quan hệ 1-n qua bảng trung gian | Country hasManyThrough Posts (qua Users) |
| **Polymorphic One To One** | `morphOne()`, `morphTo()` | Quan hệ đa hình 1-1 | Post morphOne Image, User morphOne Image |
| **Polymorphic One To Many** | `morphMany()`, `morphTo()` | Quan hệ đa hình 1-n | Post morphMany Comments, User morphMany Comments |
| **Polymorphic Many To Many** | `morphToMany()`, `morphedByMany()` | Quan hệ đa hình n-n | Post morphToMany Tags, Video morphToMany Tags |

### Câu 2: Các hàm attach(), detach(), toggle(), sync() dùng để làm gì?

**Trả lời:**

#### `attach($id, $attributes = [])`
- **Mục đích:** Thêm mối quan hệ mới vào bảng pivot trong quan hệ Many-to-Many
- **Cách hoạt động:** Tạo record mới trong bảng trung gian
- **Ví dụ:**
```php
$user->roles()->attach(1); // Thêm role_id = 1
$user->roles()->attach([1, 2, 3]); // Thêm nhiều roles
$user->roles()->attach(1, ['status' => 'active']); // Thêm với dữ liệu pivot
```

#### `detach($ids = null)`
- **Mục đích:** Xóa mối quan hệ khỏi bảng pivot
- **Cách hoạt động:** Xóa records trong bảng trung gian
- **Ví dụ:**
```php
$user->roles()->detach(1); // Xóa role_id = 1
$user->roles()->detach([1, 2]); // Xóa nhiều roles
$user->roles()->detach(); // Xóa tất cả roles
```

#### `toggle($ids, $touch = true)`
- **Mục đích:** Chuyển đổi trạng thái quan hệ (có thì xóa, không có thì thêm)
- **Cách hoạt động:** Nếu quan hệ tồn tại thì detach, không tồn tại thì attach
- **Ví dụ:**
```php
$user->roles()->toggle(1); // Nếu có role 1 thì xóa, không có thì thêm
$user->roles()->toggle([1, 2, 3]); // Toggle nhiều roles
```

#### `sync($ids, $detaching = true)`
- **Mục đích:** Đồng bộ hóa các mối quan hệ
- **Cách hoạt động:** Chỉ giữ lại những quan hệ được chỉ định, xóa tất cả quan hệ khác
- **Ví dụ:**
```php
$user->roles()->sync([1, 2]); // Chỉ giữ role 1,2, xóa tất cả role khác
$user->roles()->syncWithoutDetaching([3, 4]); // Thêm role 3,4 nhưng không xóa role cũ
```

### Câu 3: Làm sao để lấy dữ liệu từ bảng trung gian trong quan hệ n-n?

**Trả lời:**

#### Cách 1: Sử dụng thuộc tính `pivot`
```php
// Lấy role đầu tiên của user và truy cập dữ liệu pivot
$role = User::first()->roles()->first();
$pivotData = $role->pivot;

echo $pivotData->user_id;    // ID của user
echo $pivotData->role_id;    // ID của role
echo $pivotData->created_at; // Nếu có withTimestamps()
```

#### Cách 2: Cấu hình model để lấy thêm dữ liệu pivot

**Thêm timestamps:**
```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class)
                    ->withTimestamps(); // Thêm created_at, updated_at
    }
}
```

**Thêm các trường khác:**
```php
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class)
                    ->withTimestamps()
                    ->withPivot('status', 'priority', 'notes'); // Các trường tùy chỉnh
    }
}
```

#### Cách 3: Truy vấn trực tiếp bảng pivot
```php
// Lấy tất cả dữ liệu pivot của user
$user = User::with('roles')->find(1);
foreach($user->roles as $role) {
    echo "Role: " . $role->name;
    echo "Status: " . $role->pivot->status;
    echo "Assigned at: " . $role->pivot->created_at;
}
```

#### Cách 4: Sử dụng wherePivot để filter
```php
// Lấy các roles có status = 'active'
$activeRoles = User::first()->roles()
                    ->wherePivot('status', 'active')
                    ->get();

// Lấy roles được assign trong tháng này
$recentRoles = User::first()->roles()
                    ->wherePivot('created_at', '>=', now()->startOfMonth())
                    ->get();
```

#### Cách 5: Update dữ liệu pivot
```php
// Update pivot data
User::first()->roles()->updateExistingPivot(1, [
    'status' => 'inactive',
    'notes' => 'Temporarily disabled'
]);

// Sync với dữ liệu pivot mới
User::first()->roles()->sync([
    1 => ['status' => 'active'],
    2 => ['status' => 'pending'],
    3 => ['status' => 'inactive']
]);
```

**Lưu ý quan trọng:** Để có thể truy cập các trường tùy chỉnh trong bảng pivot, bạn phải khai báo chúng trong phương thức `withPivot()` của relationship.