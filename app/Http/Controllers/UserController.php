<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Validation\Rules;
use DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
        // Eloquent ORM
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        //
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
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
    public function destroy(User $user)
    {
        // Eloquent ORM
        $user->delete();
        return redirect()->route('users.index');
    }
}
