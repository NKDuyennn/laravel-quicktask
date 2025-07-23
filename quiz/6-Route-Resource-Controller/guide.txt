==============================6-Route-Resource-Controller=================
Hướng dẫn https://laravel.com/docs/12.x/controllers
https://viblo.asia/p/tap-14-controller-laravel-Ljy5VXOkZra

---------------------------- Cách tạo controller
Tạo trong app\Http\Controllers
php artisan make:controller --help
- Tạo Resource Controller
php artisan make:controller TaskController --resource;
(Resource Controller là tạo controller có các phương thức cơ bản index, show, edit, store, .....)
- Tạo Resource Controller với model
php artisan make:controller TaskController --resource --model="App\Models\Task";
- Taọ tiếp tới User
php artisan make:controller UserController --resource --model="App\Models\User";

---------------------------- Kết nối controller với route(URL)
Làm như thế nào để các func trong controller có thể kết nối với route hiện lên giao diện?
- Vào trong thư mục routes\web.php thực hiện đăng ký các route để chạy các hàm trong controller
- Các quy tắc đặt tên route theo resource controller crud thì xem ở hướng dẫn
- Cách 1 trong routes\web.php tạo 
Route::get('/users/index', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
- Cách 2 trong routes\web.php tạo
Route::resource('/tasks', TaskController::class);
- Cách cách trên các route đã có đầy đủ route.name
Cả 2 cách đều làm đúng quy tắc 
- Các kiến thức khác thường dùng nếu có ....


===================================Quiz========================================
1. Mô tả cấu trúc một route trong Laravel.
2. Kể tên các hàm trong Resources Controller và phương thức/công dụng tương ứng.
