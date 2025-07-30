# Laravel Seeder, Factory & Faker - Hướng dẫn và Quiz

## Giới thiệu tổng quan

Seeder, Factory và Faker là ba công cụ quan trọng trong Laravel để tạo và quản lý dữ liệu test/sample cho ứng dụng:

- **Seeder**: Gieo dữ liệu cố định, thường là dữ liệu khởi tạo hệ thống
- **Factory**: Tạo dữ liệu test hàng loạt với cấu trúc định sẵn
- **Faker**: Thư viện tạo dữ liệu giả ngẫu nhiên và realistic

---

## 5.1 Seeder - Công cụ gieo dữ liệu khởi tạo

### Khái niệm và mục đích

**Seeder** là công cụ dùng để gieo (seed) dữ liệu đầu vào cho database một cách có tổ chức và có thể lặp lại.

**Thường sử dụng khi:**
- Tạo tài khoản admin ban đầu
- Thêm dữ liệu cấu hình hệ thống (settings, roles, permissions)
- Import dữ liệu master (countries, categories, etc.)
- Chỉnh sửa dữ liệu đã có trước đó

### Cách tạo và sử dụng Seeder

#### Bước 1: Tạo Seeder

```bash
# Xem các tùy chọn có sẵn
php artisan make:seeder --help

# Tạo seeder mới
php artisan make:seeder CreateInitialAdminAccount
php artisan make:seeder CreateRolesAndPermissions
php artisan make:seeder ImportCountriesData
```

#### Bước 2: Viết logic trong Seeder

**File: `database/seeders/CreateInitialAdminAccount.php`**

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateInitialAdminAccount extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạm thời bỏ qua mass assignment protection
        User::unguard();

        // Kiểm tra xem admin đã tồn tại chưa
        if (!User::where('email', 'admin.account@sun-asterisk.com')->exists()) {
            User::create([
                'email' => 'admin.account@sun-asterisk.com',
                'password' => Hash::make('admin123'), // Hash password
                'first_name' => 'Admin',
                'last_name' => 'Account',
                'is_active' => true,
                'username' => 'admin-account',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Admin account created successfully!');
        } else {
            $this->command->info('Admin account already exists!');
        }

        // Bật lại mass assignment protection
        User::reguard();
    }
}
```

#### Bước 3: Đăng ký Seeder trong DatabaseSeeder

**File: `database/seeders/DatabaseSeeder.php`**

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CreateInitialAdminAccount::class,
            CreateRolesAndPermissions::class,
            ImportCountriesData::class,
        ]);
    }
}
```

#### Bước 4: Chạy Seeder

```bash
# Chạy tất cả seeders
php artisan db:seed

# Chạy seeder cụ thể
php artisan db:seed --class=CreateInitialAdminAccount

# Chạy seeders trong môi trường production (cần --force)
php artisan db:seed --force
```

### Ví dụ Seeder nâng cao

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class CreateRolesAndPermissions extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables
        Role::truncate();
        Permission::truncate();

        // Create permissions
        $permissions = [
            'users.view',
            'users.create', 
            'users.edit',
            'users.delete',
            'posts.view',
            'posts.create',
            'posts.edit',
            'posts.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $editorRole = Role::create(['name' => 'editor']);
        $userRole = Role::create(['name' => 'user']);

        // Assign permissions to roles
        $adminRole->permissions()->attach(Permission::all());
        $editorRole->permissions()->attach(
            Permission::where('name', 'like', 'posts.%')->get()
        );

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Roles and permissions created successfully!');
    }
}
```

### Một số kiến thức khác về Seeder

#### Chạy Seeder với fresh migration

```bash
# Drop tất cả tables, chạy migration và seeder
php artisan migrate:fresh --seed

# Tương đương với:
php artisan migrate:fresh
php artisan db:seed
```

#### Seeder với Environment

```php
public function run(): void
{
    if (app()->environment('production')) {
        $this->command->warn('This seeder should not run in production!');
        return;
    }

    // Logic seeding...
}
```

#### Sử dụng Transaction

```php
use Illuminate\Support\Facades\DB;

public function run(): void
{
    DB::transaction(function () {
        // All seeding operations here
        User::create([...]);
        Role::create([...]);
    });
}
```

---

## 5.2 Factory - Công cụ tạo dữ liệu test hàng loạt

### Khái niệm và mục đích

**Factory** được sử dụng trong tình huống cần tạo ra hàng loạt dữ liệu test với cấu trúc tương tự nhưng nội dung khác nhau.

**Thường sử dụng khi:**
- Tạo dữ liệu test cho development
- Tạo dữ liệu sample cho demo
- Testing với nhiều records
- Load testing với big data

### Cách tạo và sử dụng Factory

#### Bước 1: Tạo Factory

```bash
# Xem các tùy chọn
php artisan make:factory --help

# Tạo factory cho model Task
php artisan make:factory TaskFactory --model="App\Models\Task"

# Tạo factory cho model User (nếu chưa có)
php artisan make:factory UserFactory --model="App\Models\User"
```

#### Bước 2: Định nghĩa dữ liệu trong Factory

**File: `database/factories/TaskFactory.php`**

```php
<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4), // "Generate Random Task Title"
            'description' => $this->faker->paragraph(3), // Lorem ipsum text
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'user_id' => User::factory(), // Tạo user mới hoặc dùng existing
            'is_active' => $this->faker->boolean(80), // 80% chance true
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Define states cho factory
     */
    public function completed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }

    public function highPriority(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'high',
                'due_date' => $this->faker->dateTimeBetween('now', '+3 days'),
            ];
        });
    }

    public function forUser(User $user): Factory
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }
}
```

**File: `database/factories/UserFactory.php`**

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'username' => $this->faker->unique()->userName(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_active' => $this->faker->boolean(90), // 90% active
            'is_admin' => false,
            'phone' => $this->faker->phoneNumber(),
            'birth_date' => $this->faker->date('Y-m-d', '-18 years'),
            'address' => $this->faker->address(),
        ];
    }

    /**
     * State: user chưa verify email
     */
    public function unverified(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State: admin user
     */
    public function admin(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
            'email' => 'admin@example.com',
            'username' => 'admin',
        ]);
    }
}
```

#### Bước 3: Sử dụng Factory trong Tinker

```bash
php artisan tinker
```

```php
// Tạo user giả (không lưu database)
User::factory()->make();

// Tạo user và lưu vào database
$user = User::factory()->create();

// Tạo nhiều users
User::factory(5)->create();

// Tạo user với state
User::factory()->admin()->create();
User::factory()->unverified()->create();

// Tạo user với custom attributes
User::factory()->create([
    'email' => 'test@example.com',
    'first_name' => 'John'
]);

// Tạo tasks cho user
$user = User::factory()->create();
Task::factory(10)->forUser($user)->create();

// Tạo tasks với states
Task::factory(5)->completed()->create();
Task::factory(3)->highPriority()->create();

// Tạo relationships
User::factory()
    ->has(Task::factory(5)->completed())
    ->create();

// Equivalent to:
User::factory()
    ->hasTasks(5, ['status' => 'completed'])
    ->create();
```

### Sử dụng Factory trong Seeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Task;

class DevelopmentDataSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo 50 users với tasks
        User::factory(50)
            ->has(Task::factory(rand(1, 10)))
            ->create();

        // Tạo admin với nhiều tasks
        $admin = User::factory()->admin()->create();
        Task::factory(20)->forUser($admin)->create();

        // Tạo dữ liệu theo tỷ lệ
        User::factory(100)->create()->each(function ($user) {
            // 70% users có tasks
            if (rand(1, 100) <= 70) {
                Task::factory(rand(1, 5))->forUser($user)->create();
            }
        });
    }
}
```

### Faker - Các method hữu ích

```php
// Text
$faker->word                    // 'aut'
$faker->words(3, true)          // 'aut pariatur sit'
$faker->sentence(4)             // 'Aut pariatur sit mollitia.'
$faker->sentences(3, true)      // 'Aut pariatur sit. Mollitia...'
$faker->paragraph(3)            // Lorem ipsum paragraph

// Numbers
$faker->randomNumber(2)         // 42
$faker->numberBetween(1, 100)   // 79
$faker->randomFloat(2, 0, 100)  // 48.84

// Dates
$faker->date('Y-m-d')          // '2023-06-15'
$faker->dateTime()             // DateTime object
$faker->dateTimeBetween('-1 year', 'now')
$faker->time('H:i:s')          // '14:30:00'

// Person
$faker->name                   // 'John Doe'
$faker->firstName              // 'John'
$faker->lastName               // 'Doe'
$faker->email                  // 'john@example.com'
$faker->phoneNumber            // '+1-234-567-8900'

// Internet
$faker->url                    // 'https://example.com'
$faker->slug(3)                // 'aut-pariatur-sit'
$faker->userName               // 'john.doe'
$faker->password(8, 12)        // 'aB3$kL9@'

// Address
$faker->address                // '123 Main St, City, State 12345'
$faker->city                   // 'New York'
$faker->country                // 'United States'
$faker->postcode               // '12345'

// Random selections
$faker->randomElement(['a', 'b', 'c'])     // 'b'
$faker->boolean(70)                        // true (70% chance)
$faker->optional(0.8)->word                // 'word' or null
```

### Một số kiến thức khác về Factory

#### Factory Callbacks

```php
public function definition(): array
{
    return [
        'title' => $this->faker->sentence(),
        'content' => $this->faker->paragraph(),
    ];
}

public function configure(): Factory
{
    return $this->afterCreating(function (Task $task) {
        // Logic sau khi tạo task
        $task->generateSlug();
        $task->save();
    });
}
```

#### Factory với Relationships

```php
// HasMany relationship
User::factory()
    ->has(Task::factory(3)->highPriority(), 'tasks')
    ->create();

// BelongsTo relationship  
Task::factory()
    ->for(User::factory()->admin(), 'user')
    ->create();

// ManyToMany relationship
User::factory()
    ->hasAttached(Role::factory(2), ['created_at' => now()])
    ->create();
```

---

## QUIZ - Kiểm tra kiến thức

### Câu 1: Seeder/Factory/Faker dùng để làm gì?

**Trả lời:**

#### **Seeder**
**Mục đích:** Gieo (seed) dữ liệu khởi tạo và cấu hình cho ứng dụng

**Chức năng chính:**
- **Dữ liệu khởi tạo hệ thống:** Tạo tài khoản admin, roles, permissions ban đầu
- **Dữ liệu master:** Import countries, categories, settings, configurations
- **Dữ liệu cố định:** Những dữ liệu cần thiết cho hệ thống hoạt động
- **Data migration:** Chỉnh sửa, cập nhật dữ liệu existing trong database

**Đặc điểm:**
- Dữ liệu có cấu trúc cố định và ý nghĩa cụ thể
- Thường chạy một lần khi setup project
- Có thể chạy lại mà không gây duplicate (với proper checking)

#### **Factory**
**Mục đích:** Tạo dữ liệu test/sample hàng loạt với cấu trúc nhất quán

**Chức năng chính:**
- **Development testing:** Tạo dữ liệu để test features trong quá trình phát triển
- **Demo data:** Tạo dữ liệu mẫu để demo cho client
- **Unit testing:** Tạo test data cho automated tests
- **Performance testing:** Generate big data để test performance
- **UI testing:** Tạo dữ liệu để test responsive, pagination, etc.

**Đặc điểm:**
- Dữ liệu có cấu trúc giống nhau nhưng nội dung khác nhau
- Có thể tạo hàng loạt records một cách nhanh chóng
- Hỗ trợ states và relationships

#### **Faker**
**Mục đích:** Thư viện tạo dữ liệu giả realistic và đa dạng

**Chức năng chính:**
- **Realistic data:** Tạo dữ liệu giống thật (names, emails, addresses)
- **Localized data:** Hỗ trợ dữ liệu theo địa phương (Vietnamese names, phone numbers)
- **Various data types:** Text, numbers, dates, images, colors, etc.
- **Random but consistent:** Dữ liệu ngẫu nhiên nhưng hợp lý

**Đặc điểm:**
- Tích hợp sẵn trong Laravel Factory
- Hỗ trợ nhiều loại dữ liệu và format
- Có thể seed theo locale specific

**Mối quan hệ giữa ba công cụ:**
```
Seeder (Orchestrator) 
    ↓
Factory (Data Generator)
    ↓  
Faker (Realistic Content Provider)
```

### Câu 2: Khi nào cần sử dụng Seeder? Khi nào nên sử dụng Factory?

**Trả lời:**

#### **Khi nào cần sử dụng SEEDER:**

**✅ Nên dùng Seeder khi:**

1. **Setup dữ liệu khởi tạo hệ thống:**
   ```php
   // Admin account, default roles
   User::create(['email' => 'admin@app.com', 'is_admin' => true]);
   Role::create(['name' => 'admin']);
   ```

2. **Import dữ liệu master/reference:**
   ```php
   // Countries, currencies, categories
   foreach ($countries as $country) {
       Country::create($country);
   }
   ```

3. **Cấu hình ứng dụng:**
   ```php
   // Application settings
   Setting::create(['key' => 'app_name', 'value' => 'MyApp']);
   ```

4. **Migration dữ liệu:**
   ```php
   // Update existing data structure
   User::whereNull('username')->update(['username' => 'user_' . $user->id]);
   ```

5. **Production deployment:**
   - Tạo dữ liệu cần thiết cho production
   - Setup permissions, roles cho production

**❌ Không nên dùng Seeder khi:**
- Cần tạo hàng loạt dữ liệu test ngẫu nhiên
- Dữ liệu chỉ dùng cho development/testing
- Cần dữ liệu với nội dung đa dạng

#### **Khi nào nên sử dụng FACTORY:**

**✅ Nên dùng Factory khi:**

1. **Development & Testing:**
   ```php
   // Tạo 100 users để test pagination
   User::factory(100)->create();
   ```

2. **Demo preparation:**
   ```php
   // Tạo dữ liệu mẫu để demo cho client
   Post::factory(50)->published()->create();
   ```

3. **Unit/Feature testing:**
   ```php
   // Test relationships
   $user = User::factory()->has(Task::factory(5))->create();
   ```

4. **Performance testing:**
   ```php
   // Load testing với big data
   User::factory(10000)->create();
   ```

5. **UI/UX testing:**
   ```php
   // Test với dữ liệu có độ dài khác nhau
   Post::factory()->create(['title' => $faker->sentence(20)]);
   ```

**❌ Không nên dùng Factory khi:**
- Cần dữ liệu cố định, có ý nghĩa cụ thể
- Deploy lên production (trừ demo sites)
- Dữ liệu cần chính xác, không được ngẫu nhiên

#### **So sánh cụ thể:**

| Tiêu chí | Seeder | Factory |
|----------|---------|---------|
| **Mục đích** | Dữ liệu khởi tạo, cấu hình | Dữ liệu test, demo |
| **Nội dung** | Cố định, có ý nghĩa | Ngẫu nhiên, đa dạng |
| **Môi trường** | All environments | Development/Testing |
| **Số lượng** | Ít records, specific | Nhiều records, bulk |
| **Tần suất chạy** | Một lần setup | Nhiều lần khi cần |
| **Production** | ✅ Có thể chạy | ❌ Không nên chạy |

#### **Kết hợp Seeder + Factory:**

```php
// DatabaseSeeder.php
public function run(): void
{
    // Seeder cho dữ liệu cố định
    $this->call([
        CreateAdminAccount::class,        // Admin user
        CreateRolesSeeder::class,         // System roles  
        ImportCountriesSeeder::class,     // Master data
    ]);

    // Factory cho dữ liệu test (chỉ trong development)
    if (app()->environment(['local', 'staging'])) {
        User::factory(50)->create();
        Task::factory(200)->create();
    }
}
```

**Best Practices:**

1. **Environment-aware seeding:**
   ```php
   if (app()->environment('production')) {
       // Chỉ chạy essential seeders
   }
   ```

2. **Idempotent seeding:**
   ```php
   // Kiểm tra trước khi tạo
   if (!User::where('email', 'admin@app.com')->exists()) {
       User::create([...]);
   }
   ```

3. **Combine both:**
   ```php
   // Seeder tạo admin, Factory tạo test users
   User::create(['email' => 'admin@app.com']); // Seeder
   User::factory(10)->create();                // Factory
   ```