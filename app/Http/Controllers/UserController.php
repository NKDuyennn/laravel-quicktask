<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Validation\Rules;
use Exception;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // Sử dụng Gate thay vì middleware
        Gate::authorize('admin-access');

        // // Lazy Loading 
        // // select * from users
        // // select * from tasks where user_id = 1
        // // select * from tasks where user_id = 2
        // // select * from tasks where user_id = 3
        // return view('users.index', [
        //     'users' => User::all(),
        // ]);

        // Eager Loading
        // select * from users
        // select * from tasks where user_id in (1, 2, 3)
        return view('users.index', [
            'users' => User::with('tasks')->get(),
        ]);

        // Lazy Eager Loading
        // return view('users.index', [
        //     'users' => User::all()->load('tasks'),
        // ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Sử dụng Gate để kiểm tra quyền truy cập
        Gate::authorize('admin-access');

        // Eloquent ORM
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Sử dụng Gate để kiểm tra quyền truy cập
        Gate::authorize('admin-access');

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Eloquent ORM
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->is_admin = $request->is_admin ?? false; 
        $user->is_active = true; 
        $user->save();

        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Sử dụng Policy để kiểm tra quyền truy cập
        $this->authorize('view', $user);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Sử dụng Policy để kiểm tra quyền truy cập
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Sử dụng Policy để kiểm tra quyền truy cập
        $this->authorize('update', $user);

        // Eloquent ORM 
        $user->username = $request->name;
        $user->save();

        // // Query Builder
        // DB::table('users')->where('id', $user->id)
        //     ->update(['username' => $request->name]);
        
        // // Eloquent ORM user query builder
        // User::where('id', $user->id)
        //     ->update(['username' => $request->name]);
        
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    /*
    public function destroy(User $user)
    {
        // Eloquent ORM
        $user->delete();
        return redirect()->route('users.index');
    }
    */
    public function destroy(User $user)
    {
        // Sử dụng Gate để kiểm tra quyền truy cập
        Gate::authorize('admin-access');
        
        try {
            DB::transaction(function () use ($user) {
                // Xoa task cua user
                $user->tasks()->delete();
                // detach role cua user
                $user->roles()->detach();
                // Xoa user
                $user->delete();
            });
            
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
