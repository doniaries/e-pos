<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[
            {
                "name":"super_admin",
                "guard_name":"web",
                "permissions":[
                    "view_distributor","view_any_distributor","create_distributor","update_distributor","restore_distributor","restore_any_distributor","replicate_distributor","reorder_distributor","delete_distributor","delete_any_distributor","force_delete_distributor","force_delete_any_distributor",
                    "view_kategori::produk","view_any_kategori::produk","create_kategori::produk","update_kategori::produk","restore_kategori::produk","restore_any_kategori::produk","replicate_kategori::produk","reorder_kategori::produk","delete_kategori::produk","delete_any_kategori::produk","force_delete_kategori::produk","force_delete_any_kategori::produk",
                    "view_log::produk","view_any_log::produk","create_log::produk","update_log::produk","restore_log::produk","restore_any_log::produk","replicate_log::produk","reorder_log::produk","delete_log::produk","delete_any_log::produk","force_delete_log::produk","force_delete_any_log::produk",
                    "view_pelanggan","view_any_pelanggan","create_pelanggan","update_pelanggan","restore_pelanggan","restore_any_pelanggan","replicate_pelanggan","reorder_pelanggan","delete_pelanggan","delete_any_pelanggan","force_delete_pelanggan","force_delete_any_pelanggan",
                    "view_pembayaran","view_any_pembayaran","create_pembayaran","update_pembayaran","restore_pembayaran","restore_any_pembayaran","replicate_pembayaran","reorder_pembayaran","delete_pembayaran","delete_any_pembayaran","force_delete_pembayaran","force_delete_any_pembayaran",
                    "view_penjualan","view_any_penjualan","create_penjualan","update_penjualan","restore_penjualan","restore_any_penjualan","replicate_penjualan","reorder_penjualan","delete_penjualan","delete_any_penjualan","force_delete_penjualan","force_delete_any_penjualan",
                    "view_penjualan::detail","view_any_penjualan::detail","create_penjualan::detail","update_penjualan::detail","restore_penjualan::detail","restore_any_penjualan::detail","replicate_penjualan::detail","reorder_penjualan::detail","delete_penjualan::detail","delete_any_penjualan::detail","force_delete_penjualan::detail","force_delete_any_penjualan::detail",
                    "view_produk","view_any_produk","create_produk","update_produk","restore_produk","restore_any_produk","replicate_produk","reorder_produk","delete_produk","delete_any_produk","force_delete_produk","force_delete_any_produk",
                    "view_role","view_any_role","create_role","update_role","delete_role","delete_any_role",
                    "view_satuan","view_any_satuan","create_satuan","update_satuan","restore_satuan","restore_any_satuan","replicate_satuan","reorder_satuan","delete_satuan","delete_any_satuan","force_delete_satuan","force_delete_any_satuan",
                    "view_setting","view_any_setting","create_setting","update_setting","restore_setting","restore_any_setting","replicate_setting","reorder_setting","delete_setting","delete_any_setting","force_delete_setting","force_delete_any_setting",
                    "view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user"
                ]
            },
            {
                "name":"petugas_stok",
                "guard_name":"web",
                "permissions":[
                    "view_log::produk","view_any_log::produk","create_log::produk",
                    "view_produk","view_any_produk",
                    "view_kategori::produk","view_any_kategori::produk",
                    "view_satuan","view_any_satuan"
                ]
            },
            {
                "name":"kasir",
                "guard_name":"web",
                "permissions":[
                    "view_pembayaran","view_any_pembayaran",
                    "view_penjualan","view_any_penjualan",
                    "view_penjualan::detail","view_any_penjualan::detail",
                    "view_produk","view_any_produk"
                ]
            }
        ]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
