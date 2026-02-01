<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class KasirSetupSeeder extends Seeder
{
    /**
     * Setup complete kasir role with limited permissions.
     * 
     * Kasir dapat:
     * - View penjualan (untuk cetak laporan)
     * - View pembayaran (histori pembayaran)
     * - View stok (cek stok dari backend)
     * - Akses dashboard
     */
    public function run(): void
    {
        $this->command->info('=== KASIR SETUP SEEDER ===');

        // 1. Get or create kasir role
        $kasirRole = Role::firstOrCreate([
            'name' => 'kasir',
            'guard_name' => 'web'
        ]);

        // 2. Get or create panel_user role (required for panel access)
        $panelUserRole = Role::firstOrCreate([
            'name' => 'panel_user',
            'guard_name' => 'web'
        ]);

        $this->command->info('✓ Roles created/verified');

        // 3. Define LIMITED permissions for kasir
        $kasirPermissions = [
            // Dashboard access - REQUIRED
            'page_Dashboard',

            // Penjualan - VIEW ONLY (untuk cetak laporan)
            'view_penjualan',
            'view_any_penjualan',

            // Pembayaran - VIEW ONLY (histori pembayaran)
            'view_pembayaran',
            'view_any_pembayaran',

            // Stok - VIEW ONLY (cek stok)
            'view_stok',
            'view_any_stok',

            // Produk - VIEW ONLY (untuk lihat detail produk saat cek stok)
            'view_produk',
            'view_any_produk',
        ];

        // 4. Clear existing permissions first (untuk clean slate)
        $kasirRole->syncPermissions([]);
        $this->command->info('✓ Cleared old permissions');

        // 5. Assign new permissions
        $assignedCount = 0;
        $notFoundCount = 0;

        foreach ($kasirPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();

            if ($permission) {
                $kasirRole->givePermissionTo($permission);
                $this->command->info("  ✓ {$permissionName}");
                $assignedCount++;
            } else {
                $this->command->warn("  ✗ Permission not found: {$permissionName}");
                $notFoundCount++;
            }
        }

        $this->command->info("✓ Assigned {$assignedCount} permissions to kasir role");

        if ($notFoundCount > 0) {
            $this->command->warn("⚠ {$notFoundCount} permissions not found. Run 'php artisan shield:generate --all' first.");
        }

        // 6. Assign roles to kasir users
        $kasirUsers = User::where('tipe', 'kasir')->get();

        if ($kasirUsers->isEmpty()) {
            $this->command->warn('⚠ No users with tipe=kasir found.');
        } else {
            $this->command->info('Assigning roles to kasir users...');

            foreach ($kasirUsers as $user) {
                // Remove super_admin if accidentally assigned
                if ($user->hasRole('super_admin')) {
                    $user->removeRole('super_admin');
                    $this->command->warn("  ✗ Removed super_admin from: {$user->name}");
                }

                // Assign kasir role
                if (!$user->hasRole('kasir')) {
                    $user->assignRole('kasir');
                    $this->command->info("  ✓ Assigned kasir role to: {$user->name}");
                }

                // Assign panel_user role (required for panel access)
                if (!$user->hasRole('panel_user')) {
                    $user->assignRole('panel_user');
                    $this->command->info("  ✓ Assigned panel_user role to: {$user->name}");
                }
            }

            $this->command->info("✓ Configured {$kasirUsers->count()} kasir users");
        }

        // 7. Summary
        $this->command->info('');
        $this->command->info('=== KASIR SETUP COMPLETE ===');
        $this->command->info('Kasir dapat:');
        $this->command->info('  ✓ Akses dashboard');
        $this->command->info('  ✓ View penjualan (cetak laporan)');
        $this->command->info('  ✓ View pembayaran (histori)');
        $this->command->info('  ✓ View stok (cek stok)');
        $this->command->info('  ✓ View produk (detail produk)');
        $this->command->info('');
        $this->command->info('Kasir TIDAK dapat:');
        $this->command->info('  ✗ Create/Edit/Delete apapun');
        $this->command->info('  ✗ Akses Settings');
        $this->command->info('  ✗ Akses Users/Roles');
        $this->command->info('  ✗ Akses Pembelian');
        $this->command->info('  ✗ Akses Pelanggan');
    }
}
