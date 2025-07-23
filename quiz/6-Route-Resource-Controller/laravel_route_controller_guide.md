# 6-Route-Resource-Controller

## Hướng dẫn tham khảo
- [Laravel Documentation - Controllers](https://laravel.com/docs/12.x/controllers)
- [Viblo - Controller Laravel](https://viblo.asia/p/tap-14-controller-laravel-Ljy5VXOkZra)

---

## Cách tạo Controller

### Tạo trong `app\Http\Controllers`

```bash
php artisan make:controller --help
```

### Các loại Controller thường dùng:

**1. Tạo Resource Controller**
```bash
php artisan make:controller TaskController --resource
```
> Resource Controller tự động tạo controller với các phương thức cơ bản: index, show, create, store, edit, update, destroy

**2. Tạo Resource Controller với Model**
```bash
php artisan make:controller TaskController --resource --model="App\Models\Task"
```
> Tự động bind model và type-hint trong các phương thức, giúp Laravel tự động inject model instance

**3. Tạo Controller cho User**
```bash
php artisan make:controller UserController --resource --model="App\Models\User"
```

---

## Kết nối Controller với Route (URL)

### Làm thế nào để các function trong controller kết nối với route hiển thị lên giao diện?

Vào file `routes\web.php` để đăng ký các route kết nối với các method trong controller.

### **Cách 1: Định nghĩa từng route riêng lẻ**

```php
// routes/web.php
Route::get('/users/index', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
```

### **Cách 2: Sử dụng Resource Route (Khuyến nghị)**

```php
// routes/web.php
Route::resource('/tasks', TaskController::class);
```

> Cách này tự động tạo tất cả 7 routes chuẩn RESTful với đầy đủ route name

### **Lợi ích của Resource Route:**
- Tự động tạo đầy đủ 7 routes chuẩn RESTful
- Tuân thủ convention over configuration
- Code ngắn gọn, dễ maintain
- Tự động generate route names theo chuẩn

### **Các kiến thức khác thường dùng:**

**1. Resource Route với tùy chọn:**
```php
// Chỉ tạo một số routes cụ thể
Route::resource('users', UserController::class)->only(['index', 'show', 'create', 'store']);

// Loại trừ một số routes
Route::resource('users', UserController::class)->except(['destroy']);
```

**2. Nested Resource Routes:**
```php
Route::resource('users.posts', PostController::class);
// Tạo routes như: /users/{user}/posts, /users/{user}/posts/{post}, etc.
```

**3. Route Model Binding:**
```php
// Tự động inject model instance dựa trên ID trong URL
Route::get('/users/{user}', [UserController::class, 'show']);
// Laravel tự động tìm User với ID tương ứng và inject vào method
```

---

## Quiz - Đáp án

### 1. Mô tả cấu trúc một route trong Laravel

**Cấu trúc cơ bản của một route:**

```php
Route::{HTTP_METHOD}('{URI}', [{Controller}::class, '{method}'])->name('{route_name}');
```

**Các thành phần:**
- **HTTP_METHOD**: GET, POST, PUT, PATCH, DELETE
- **URI**: Đường dẫn URL (có thể chứa parameters như `{id}`, `{user}`)
- **Controller::class**: Tên class controller
- **method**: Tên method trong controller
- **route_name**: Tên route để sử dụng trong helper `route()`

**Ví dụ chi tiết:**
```php
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
```
- **GET**: HTTP method
- **/users/{user}/edit**: URI với parameter `{user}`
- **UserController::class**: Controller class
- **edit**: Method name trong controller
- **users.edit**: Route name

### 2. Các hàm trong Resource Controller và phương thức/công dụng tương ứng

| STT | Method | HTTP Verb | URI | Route Name | Công dụng |
|-----|--------|----------|-----|------------|-----------|
| 1 | **index()** | GET | `/tasks` | `tasks.index` | Hiển thị danh sách tất cả resources |
| 2 | **create()** | GET | `/tasks/create` | `tasks.create` | Hiển thị form tạo mới resource |
| 3 | **store()** | POST | `/tasks` | `tasks.store` | Lưu resource mới vào database |
| 4 | **show()** | GET | `/tasks/{task}` | `tasks.show` | Hiển thị chi tiết một resource cụ thể |
| 5 | **edit()** | GET | `/tasks/{task}/edit` | `tasks.edit` | Hiển thị form chỉnh sửa resource |
| 6 | **update()** | PUT/PATCH | `/tasks/{task}` | `tasks.update` | Cập nhật resource trong database |
| 7 | **destroy()** | DELETE | `/tasks/{task}` | `tasks.destroy` | Xóa resource khỏi database |

**Mô tả chi tiết từng method:**

**index()**: Thường dùng để hiển thị trang danh sách, có thể có pagination, search, filter
```php
public function index()
{
    $tasks = Task::paginate(10);
    return view('tasks.index', compact('tasks'));
}
```

**create()**: Hiển thị form tạo mới, có thể load dữ liệu cho dropdown, select
```php
public function create()
{
    return view('tasks.create');
}
```

**store()**: Xử lý dữ liệu từ form create, validate và lưu vào database
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|max:255'
    ]);
    
    Task::create($validated);
    return redirect()->route('tasks.index');
}
```

**show()**: Hiển thị chi tiết một record cụ thể
```php
public function show(Task $task)
{
    return view('tasks.show', compact('task'));
}
```

**edit()**: Hiển thị form chỉnh sửa với dữ liệu hiện tại
```php
public function edit(Task $task)
{
    return view('tasks.edit', compact('task'));
}
```

**update()**: Xử lý dữ liệu từ form edit và cập nhật database
```php
public function update(Request $request, Task $task)
{
    $validated = $request->validate([
        'name' => 'required|max:255'
    ]);
    
    $task->update($validated);
    return redirect()->route('tasks.show', $task);
}
```

**destroy()**: Xóa record khỏi database
```php
public function destroy(Task $task)
{
    $task->delete();
    return redirect()->route('tasks.index');
}
```

---

## Best Practices

1. **Sử dụng Resource Route** khi có đầy đủ 7 operations CRUD
2. **Route Model Binding** để tự động inject model instances
3. **Validation** trong store() và update() methods
4. **Redirect với flash messages** sau khi thực hiện actions
5. **Authorization** sử dụng Policies hoặc Gates khi cần thiết