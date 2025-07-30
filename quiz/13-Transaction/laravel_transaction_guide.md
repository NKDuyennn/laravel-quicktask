# Chapter 13: Laravel Transaction - Hướng dẫn và Thực hành

## 1. Mục đích sử dụng Transaction

Transaction là một cơ chế quan trọng trong cơ sở dữ liệu giúp đảm bảo tính toàn vẹn dữ liệu (Data Integrity) khi thực hiện nhiều thao tác liên quan với nhau.

### Đặc điểm ACID của Transaction:
- **Atomicity**: Tất cả các thao tác trong transaction phải thành công hoặc tất cả đều thất bại
- **Consistency**: Dữ liệu phải luôn ở trạng thái nhất quán
- **Isolation**: Các transaction không ảnh hưởng lẫn nhau khi chạy đồng thời
- **Durability**: Kết quả của transaction được lưu trữ vĩnh viễn

## 2. Transaction dùng trong trường hợp nào?

### Các trường hợp phổ biến:

1. **Chuyển tiền ngân hàng**
   ```php
   // Ví dụ: Chuyển 1,000,000 VND từ tài khoản A sang tài khoản B
   DB::transaction(function () {
       // Trừ tiền từ tài khoản A
       Account::where('id', 1)->decrement('balance', 1000000);
       // Cộng tiền vào tài khoản B
       Account::where('id', 2)->increment('balance', 1000000);
       // Tạo lịch sử giao dịch
       Transaction::create([
           'from_account' => 1,
           'to_account' => 2,
           'amount' => 1000000
       ]);
   });
   ```

2. **Đặt hàng e-commerce**
   ```php
   DB::transaction(function () use ($orderData, $items) {
       // Tạo đơn hàng
       $order = Order::create($orderData);
       
       // Tạo chi tiết đơn hàng và giảm tồn kho
       foreach ($items as $item) {
           OrderItem::create([
               'order_id' => $order->id,
               'product_id' => $item['product_id'],
               'quantity' => $item['quantity'],
               'price' => $item['price']
           ]);
           
           // Giảm số lượng tồn kho
           Product::where('id', $item['product_id'])
                  ->decrement('stock', $item['quantity']);
       }
   });
   ```

3. **Xóa user và các dữ liệu liên quan**
   ```php
   DB::transaction(function () use ($user) {
       // Xóa tasks của user
       $user->tasks()->delete();
       // Xóa roles của user
       $user->roles()->detach();
       // Xóa user
       $user->delete();
   });
   ```

## 3. Cách dùng Transaction trong Laravel

### 3.1. Sử dụng DB::transaction()

```php
use Illuminate\Support\Facades\DB;

// Cách 1: Sử dụng Closure
DB::transaction(function () {
    // Các thao tác database
});

// Cách 2: Với try-catch
try {
    DB::transaction(function () {
        // Các thao tác database
        if ($someCondition) {
            throw new Exception('Something went wrong');
        }
    });
} catch (Exception $e) {
    // Xử lý lỗi
}
```

### 3.2. Sử dụng Manual Transaction

```php
DB::beginTransaction();

try {
    // Các thao tác database
    $user = User::create($userData);
    $user->tasks()->create($taskData);
    
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    throw $e;
}
```

### 3.3. Nested Transactions với Savepoints

```php
DB::transaction(function () {
    // Transaction chính
    $user = User::create($userData);
    
    DB::transaction(function () use ($user) {
        // Nested transaction (sử dụng savepoint)
        $user->tasks()->create($taskData);
        $user->roles()->attach($roleIds);
    });
});
```

## 4. Savepoint trong Transaction

### Savepoint là gì?

Savepoint là một điểm đánh dấu trong transaction cho phép rollback về một điểm cụ thể thay vì rollback toàn bộ transaction.

### Ví dụ sử dụng Savepoint:

```php
DB::beginTransaction();

try {
    // Thao tác 1
    $user = User::create($userData);
    
    // Tạo savepoint
    DB::statement('SAVEPOINT sp1');
    
    try {
        // Thao tác 2 (có thể thất bại)
        $user->tasks()->create($riskyTaskData);
        
        // Tạo savepoint khác
        DB::statement('SAVEPOINT sp2');
        
        // Thao tác 3
        $user->roles()->attach($roleIds);
        
    } catch (Exception $e) {
        // Rollback về savepoint sp1 (giữ lại user đã tạo)
        DB::statement('ROLLBACK TO SAVEPOINT sp1');
        
        // Tiếp tục với thao tác khác
        $user->tasks()->create($defaultTaskData);
    }
    
    DB::commit();
    
} catch (Exception $e) {
    DB::rollBack();
    throw $e;
}
```

## 5. Task: Xóa User và các đối tượng liên quan

### 5.1. Cập nhật UserController.php

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Exception;

class UserController extends Controller
{
    // ... các method khác giữ nguyên

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            DB::transaction(function () use ($user) {
                // Xóa tất cả tasks của user
                $user->tasks()->delete();
                
                // Xóa tất cả roles của user (detach từ pivot table)
                $user->roles()->detach();
                
                // Có thể thêm các model khác nếu có
                // Ví dụ: xóa comments, posts, orders, etc.
                // $user->comments()->delete();
                // $user->posts()->delete();
                
                // Cuối cùng xóa user
                $user->delete();
            });
            
            return redirect()->route('users.index')
                           ->with('success', 'User và tất cả dữ liệu liên quan đã được xóa thành công!');
                           
        } catch (Exception $e) {
            return redirect()->route('users.index')
                           ->with('error', 'Có lỗi xảy ra khi xóa user: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete users với transaction
     */
    public function bulkDestroy(Request $request)
    {
        $userIds = $request->input('user_ids', []);
        
        if (empty($userIds)) {
            return redirect()->back()->with('error', 'Không có user nào được chọn!');
        }

        try {
            DB::transaction(function () use ($userIds) {
                foreach ($userIds as $userId) {
                    $user = User::findOrFail($userId);
                    
                    // Xóa tasks
                    $user->tasks()->delete();
                    
                    // Xóa roles
                    $user->roles()->detach();
                    
                    // Xóa user
                    $user->delete();
                }
            });
            
            $count = count($userIds);
            return redirect()->route('users.index')
                           ->with('success', "Đã xóa thành công {$count} user(s) và tất cả dữ liệu liên quan!");
                           
        } catch (Exception $e) {
            return redirect()->route('users.index')
                           ->with('error', 'Có lỗi xảy ra khi xóa users: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete với transaction (nếu sử dụng SoftDeletes)
     */
    public function softDestroy(User $user)
    {
        try {
            DB::transaction(function () use ($user) {
                // Soft delete tasks
                $user->tasks()->delete(); // Nếu Task model có SoftDeletes
                
                // Detach roles (không cần soft delete cho pivot table)
                $user->roles()->detach();
                
                // Soft delete user
                $user->delete(); // Sẽ là soft delete nếu User model có SoftDeletes trait
            });
            
            return redirect()->route('users.index')
                           ->with('success', 'User đã được đánh dấu xóa (soft delete) thành công!');
                           
        } catch (Exception $e) {
            return redirect()->route('users.index')
                           ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
```

### 5.2. Cập nhật User Model (nếu cần)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes; // Nếu muốn sử dụng soft delete
// ... other imports

class User extends Authenticatable
{
    // use SoftDeletes; // Uncomment nếu muốn sử dụng soft delete
    use HasApiTokens, HasFactory, Notifiable;

    // ... code hiện tại giữ nguyên

    /**
     * Boot method để xử lý events
     */
    protected static function boot()
    {
        parent::boot();
        
        // Event khi xóa user
        static::deleting(function ($user) {
            // Có thể thêm logic kiểm tra trước khi xóa
            if ($user->is_admin && User::where('is_admin', true)->count() <= 1) {
                throw new Exception('Không thể xóa admin cuối cùng!');
            }
        });
    }

    /**
     * Scope để lấy users có thể xóa được
     */
    public function scopeDeletable($query)
    {
        return $query->where('is_admin', false)
                    ->orWhere(function ($q) {
                        $q->where('is_admin', true)
                          ->whereIn('id', function ($subQuery) {
                              $subQuery->select('id')
                                       ->from('users')
                                       ->where('is_admin', true)
                                       ->skip(1); // Giữ lại ít nhất 1 admin
                          });
                    });
    }
}
```

### 5.3. Tạo Routes cho các method mới

```php
// routes/web.php
Route::resource('users', UserController::class);

// Thêm routes cho bulk delete
Route::delete('users/bulk-destroy', [UserController::class, 'bulkDestroy'])
     ->name('users.bulk-destroy');

// Route cho soft delete (nếu sử dụng)
Route::patch('users/{user}/soft-destroy', [UserController::class, 'softDestroy'])
     ->name('users.soft-destroy');
```

### 5.4. View để test (users/index.blade.php)

```html
<!-- Thêm vào view index để test bulk delete -->
<form action="{{ route('users.bulk-destroy') }}" method="POST" 
      onsubmit="return confirm('Bạn có chắc muốn xóa các user đã chọn?')">
    @csrf
    @method('DELETE')
    
    @foreach($users as $user)
        <div>
            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}">
            {{ $user->full_name }} ({{ $user->email }})
            
            <!-- Nút xóa đơn lẻ -->
            <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Bạn có chắc muốn xóa user này?')">
                    Xóa
                </button>
            </form>
        </div>
    @endforeach
    
    <button type="submit">Xóa các user đã chọn</button>
</form>

<!-- Hiển thị thông báo -->
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
```

## 6. Best Practices

### 6.1. Xử lý Exception đúng cách

```php
public function complexOperation()
{
    try {
        DB::transaction(function () {
            // Các thao tác phức tạp
        });
    } catch (ValidationException $e) {
        // Xử lý lỗi validation riêng
        return response()->json(['errors' => $e->errors()], 422);
    } catch (ModelNotFoundException $e) {
        // Xử lý lỗi không tìm thấy model
        return response()->json(['error' => 'Resource not found'], 404);
    } catch (Exception $e) {
        // Xử lý lỗi chung
        Log::error('Transaction failed: ' . $e->getMessage());
        return response()->json(['error' => 'Internal server error'], 500);
    }
}
```

### 6.2. Sử dụng Database Events

```php
// Trong User model
protected static function boot()
{
    parent::boot();
    
    static::deleting(function ($user) {
        // Kiểm tra ràng buộc trước khi xóa
        if ($user->orders()->where('status', 'pending')->exists()) {
            throw new Exception('Không thể xóa user có đơn hàng đang chờ xử lý!');
        }
    });
}
```

### 6.3. Testing Transaction

```php
// tests/Feature/UserControllerTest.php
public function test_user_deletion_removes_all_related_data()
{
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);
    $role = Role::factory()->create();
    $user->roles()->attach($role);
    
    $this->delete(route('users.destroy', $user));
    
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    $this->assertDatabaseMissing('role_user', [
        'user_id' => $user->id,
        'role_id' => $role->id
    ]);
}
```

## 7. Kết luận

Transaction là một công cụ mạnh mẽ để đảm bảo tính toàn vẹn dữ liệu. Khi sử dụng cần lưu ý:

- Luôn wrap các thao tác liên quan trong transaction
- Xử lý exception đúng cách
- Sử dụng savepoint cho các trường hợp phức tạp
- Test kỹ lưỡng các tình huống edge case
- Cân nhắc performance khi transaction quá lớn

Với code trên, bạn đã có một hệ thống xóa user an toàn và đảm bảo tính toàn vẹn dữ liệu.