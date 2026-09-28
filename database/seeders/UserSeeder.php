<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'              => 'Admin',
                'email'             => 'superadmin@gmail.com',
                // [R4 Fix] Jangan hardcode password di kode. Baca dari .env atau gunakan default saat development.
                // Set SEEDER_ADMIN_PASSWORD di .env untuk production.
                'password'          => Hash::make(env('SEEDER_ADMIN_PASSWORD', 'ChangeMe_' . substr(md5(random_bytes(4)), 0, 8))),
                'tipe'              => 'super admin',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Kasir 1',
                'email'             => 'kasir1@kasir.com',
                'password'          => Hash::make(env('SEEDER_KASIR_PASSWORD', 'ChangeMe_' . substr(md5(random_bytes(4)), 0, 8))),
                'tipe'              => 'kasir',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Kasir 2',
                'email'             => 'kasir2@kasir.com',
                'password'          => Hash::make(env('SEEDER_KASIR_PASSWORD', 'ChangeMe_' . substr(md5(random_bytes(4)), 0, 8))),
                'tipe'              => 'kasir',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Petugas Stok',
                'email'             => 'stok@gudang.com',
                'password'          => Hash::make(env('SEEDER_STOK_PASSWORD', 'ChangeMe_' . substr(md5(random_bytes(4)), 0, 8))),
                'tipe'              => 'petugas_stok',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);

            if ($userData['tipe'] === 'admin') {
                $user->assignRole('super_admin');
            } elseif ($userData['tipe'] === 'kasir') {
                // Ensure role exists as ShieldSeeder might not create it
                $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
                // Give basic permissions if needed, or just create the role
                $user->assignRole($role);
            } elseif ($userData['tipe'] === 'petugas_stok') {
                $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'petugas_stok', 'guard_name' => 'web']);
                $user->assignRole($role);
            }
        }
    }
}
