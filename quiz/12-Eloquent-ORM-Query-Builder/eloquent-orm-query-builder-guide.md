# Eloquent ORM và Query Builder - Hướng dẫn chi tiết

## 1. Eloquent ORM Query Builder

### Ví dụ về Eloquent ORM:
```php
public function update(Request $request, User $user)
{
    $user->username = $request->name;
    $user->save();
    return redirect()->back();
}
```

### Ví dụ về Query Builder:
```php
public function update(Request $request, User $user)
{
    DB::table('users')->where('id', $user->id)
        ->update(['username' => $request->name]);
    return redirect()->back();
}
```

## 2. Sự khác nhau giữa Eloquent ORM và Query Builder

### Cách hoạt động:
- **Eloquent ORM**: Là một cách tiếp cận hướng đối tượng (Object-Oriented) để tương tác với cơ sở dữ liệu. Mỗi bảng trong database được đại diện bởi một Model class.
- **Query Builder**: Là một cách tiếp cận hướng thủ tục (Procedural) sử dụng fluent interface để xây dựng và thực thi các truy vấn SQL.

### Điểm khác biệt quan trọng:
- Khi sử dụng **Eloquent ORM**, code sẽ chạy qua các **accessor** và **mutator** của model
- Khi sử dụng **Query Builder**, sẽ **không** chạy qua accessor và mutator

### Ưu điểm và nhược điểm:

#### Eloquent ORM:
**Ưu điểm:**
- Code dễ đọc, dễ hiểu và dễ bảo trì
- Hỗ trợ đầy đủ các tính năng OOP như accessor, mutator
- Hỗ trợ quan hệ giữa các model (relationships)
- Tích hợp sẵn các tính năng như soft deletes, timestamps
- Hỗ trợ các event model (creating, created, updating, updated, etc.)

**Nhược điểm:**
- Hiệu suất có thể thấp hơn so với Query Builder trong một số trường hợp
- Tiêu tốn nhiều bộ nhớ hơn do phải load toàn bộ model object
- Không linh hoạt với các truy vấn SQL phức tạp

#### Query Builder:
**Ưu điểm:**
- Hiệu suất cao hơn, đặc biệt với các truy vấn lớn
- Linh hoạt hơn trong việc xây dựng truy vấn phức tạp
- Tiêu tốn ít bộ nhớ hơn
- Có thể sử dụng raw SQL khi cần thiết

**Nhược điểm:**
- Code khó đọc và khó bảo trì hơn
- Không hỗ trợ các tính năng của Eloquent như accessor, mutator
- Không có relationship tự động
- Phải tự quản lý timestamps và soft deletes

## 3. Kết hợp Eloquent ORM với Query Builder

Eloquent ORM vẫn có thể gọi Query Builder để thực hiện các truy vấn phức tạp hơn nếu cần thiết:

```php
// Eloquent ORM sử dụng Query Builder
User::where('id', $user->id)
    ->update(['username' => $request->name]);
```

## 4. Eager Loading

### Khái niệm:
**Eager Loading** là một kỹ thuật trong Eloquent ORM để giảm số lượng truy vấn đến cơ sở dữ liệu khi lấy dữ liệu liên quan.

### Vấn đề N+1 Query:
```blade
@foreach ($users as $index => $user)
    <tr>
        <th class="text-gray-900 dark:text-gray-100 text-center" scope="row">{{ ++$index }}</th>
        <td class="text-gray-900 dark:text-gray-100 text-center">{{ $user->fullname }}</td>
        <td class="text-gray-900 dark:text-gray-100 text-center">{{ $user->username }}</td>
        <td class="text-gray-900 dark:text-gray-100 text-center">
            @foreach ($user->tasks as $task)
                {{ $task->name }}
            @endforeach
        </td>
    </tr>
@endforeach
```

Khi đó sẽ bị lỗi **N+1 query**, vì mỗi lần lặp qua `$user->tasks` sẽ thực hiện một truy vấn riêng để lấy các task của user đó.

### Lazy Loading vs Eager Loading:

#### Lazy Loading (Mặc định):
- Chỉ lấy dữ liệu của model chính
- Không lấy dữ liệu liên quan cho đến khi được gọi
- Dẫn đến vấn đề N+1 query

#### Eager Loading:
```php
public function index()
{
    return view('users.index', [
        'users' => User::with('tasks')->get(),
    ]);
}
```

Eloquent ORM sẽ thực hiện:
1. Một truy vấn để lấy tất cả người dùng
2. Một truy vấn khác để lấy tất cả các task liên quan

Tổng cộng chỉ **2 truy vấn** thay vì N+1.

### Lazy Eager Loading:
```php
// Lazy Eager Loading
return view('users.index', [
    'users' => User::all()->load('tasks'),
]);
```

### Sự khác nhau giữa with() và load():

#### with() (Eager Loading):
- Tải dữ liệu liên quan **ngay khi truy vấn chính được thực hiện**
- Sử dụng khi bạn biết trước sẽ cần dữ liệu liên quan
- Hiệu quả hơn khi chắc chắn sẽ sử dụng data relationship

#### load() (Lazy Eager Loading):
- Tải dữ liệu liên quan **sau khi truy vấn chính đã được thực hiện**
- Sử dụng khi đã có collection và muốn load thêm relationship
- Tiết kiệm bộ nhớ hơn khi không chắc chắn sẽ sử dụng tất cả relationship

## Quiz - Câu trả lời

### 1. Cách hoạt động của Eloquent ORM và Query Builder

**Eloquent ORM:**
- Hoạt động theo mô hình OOP, mỗi bảng được đại diện bởi một Model class
- Các thao tác database được thực hiện thông qua các method của object
- Tự động chạy qua accessor, mutator và các event của model
- Hỗ trợ relationship và các tính năng ORM khác

**Query Builder:**
- Hoạt động theo mô hình procedural với fluent interface
- Xây dựng truy vấn SQL thông qua các method chain
- Thực thi trực tiếp SQL mà không qua các layer xử lý của model
- Trả về kết quả dạng array hoặc object đơn giản

### 2. Ưu/nhược điểm của chúng

**Eloquent ORM:**
- ✅ Code dễ đọc, dễ bảo trì
- ✅ Hỗ trợ đầy đủ tính năng ORM (accessor, mutator, relationship, events)
- ✅ Tích hợp sẵn soft deletes, timestamps
- ❌ Hiệu suất thấp hơn Query Builder
- ❌ Tiêu tốn nhiều bộ nhớ hơn

**Query Builder:**
- ✅ Hiệu suất cao, tiêu tốn ít bộ nhớ
- ✅ Linh hoạt với truy vấn phức tạp
- ✅ Có thể sử dụng raw SQL
- ❌ Code khó đọc, khó bảo trì
- ❌ Không có các tính năng ORM tự động

### 3. Khi nào nên dùng Query Builder hoặc Eloquent ORM?

**Nên dùng Eloquent ORM khi:**
- Phát triển ứng dụng với logic business phức tạp
- Cần sử dụng relationship giữa các model
- Cần tính năng accessor, mutator, events
- Ưu tiên code dễ đọc, dễ bảo trì
- Dự án có nhiều developer cùng làm việc

**Nên dùng Query Builder khi:**
- Cần hiệu suất cao (xử lý data lớn)
- Truy vấn SQL phức tạp mà Eloquent khó xử lý
- Chỉ cần lấy data đơn giản không cần các tính năng ORM
- Tối ưu hóa performance cho các API endpoint có lượng truy cập lớn

### 4. Phân biệt Lazy loading và Eager loading

**Lazy Loading:**
- Chỉ load data của model chính khi truy vấn
- Load relationship chỉ khi được gọi đến
- Dẫn đến N+1 query problem
- Tiết kiệm bộ nhớ ban đầu nhưng có thể chậm khi cần relationship

**Eager Loading:**
- Load cả data chính và relationship trong cùng lúc
- Giảm số lượng query xuống 2 (1 cho main model, 1 cho relationship)
- Sử dụng nhiều bộ nhớ hơn ngay từ đầu
- Nhanh hơn khi chắc chắn sẽ sử dụng relationship

### 5. Phân biệt giữa with() và load()

**with() - Eager Loading:**
```php
$users = User::with('tasks')->get(); // Load relationship ngay khi query
```
- Load relationship ngay trong lúc query main model
- Sử dụng khi biết trước sẽ cần relationship
- Hiệu quả hơn khi chắc chắn sử dụng data relationship

**load() - Lazy Eager Loading:**
```php
$users = User::all();
$users->load('tasks'); // Load relationship sau khi đã có collection
```
- Load relationship sau khi đã có collection
- Sử dụng khi đã có data và muốn load thêm relationship
- Linh hoạt hơn, có thể load có điều kiện