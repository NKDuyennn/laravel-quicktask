<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('name');
            $table->foreignId('user_id')
                    ->constrained('users') // tạo khóa ngoại tới bảng users
                    ->onDelete('cascade'); // khi xóa user thì xóa task
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
