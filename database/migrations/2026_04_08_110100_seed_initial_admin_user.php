<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => env('ADMIN_EMAIL', 'admin@marthina.com.br')],
            [
                'name' => 'Administrador',
                'is_admin' => true,
                'password' => Hash::make(env('ADMIN_PASSWORD', 'change-me')),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('users')->where('email', env('ADMIN_EMAIL', 'admin@marthina.com.br'))->delete();
    }
};
