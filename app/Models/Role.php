<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [
        // Các trường không được phép gán hàng loạt sẽ được liệt kê tại đây
        // Ví dụ: 'admin_only_field',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
            // ->withPivot('created_at', 'updated_at');
    }
}
