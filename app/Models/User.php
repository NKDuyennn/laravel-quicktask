<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    // fillable là biến chứa danh sách các trường trong white_list
    // protected $fillable = [
    //     'email',
    //     'password',
    //     'first_name',
    //     'last_name',
    //     'is_active',
    //     'username',
    // ];

    // guarded là biến chứa danh sách các trường trong black_list

    protected $guarded = [
        // Các trường không được phép gán hàng loạt sẽ được liệt kê tại đây
        // Ví dụ: 'admin_only_field',
        'is_admin',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

     public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps();
    }

    // Example of a custom accessor using Attribute
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->attributes['first_name'] . ' ' . $this->attributes['last_name'],
        );
    }
    // Example of a custom mutator using Attribute
    protected function username(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Str::slug($value),
        );
    }
}
