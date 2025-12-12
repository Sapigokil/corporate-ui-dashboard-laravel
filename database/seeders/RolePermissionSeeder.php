<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bersihkan Cache (Wajib!)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Hapus data lama untuk clean install
        DB::table('roles')->delete();
        DB::table('permissions')->delete();
        // Hati-hati dengan penghapusan data di tabel pivot, lebih aman jika migrate:fresh

        // =========================================================
        // 2. BUAT SEMUA PERMISSIONS
        // =========================================================
        $permissionsList = [
            'dashboard-view','manage-master',
            'siswa-view', 'siswa-create', 'siswa-edit', 'siswa-delete',
            'guru-manage',
            'mapel-manage',
            'nilai-view', 'nilai-input', 'nilai-input-sikap',
            'walikelas-manage-catatan',
            'laporan-view-rekap', 'laporan-view-absensi',
            'cetak-generate-rapor', 'cetak-print-legger',
            'pengaturan-manage-users', 
            // Permission yang hilang dari route sebelumnya (jika perlu)
            'pengaturan-access' 
        ];

        foreach ($permissionsList as $permission) {
            // Pastikan tidak ada typo di sini
            Permission::create(['name' => $permission]);
        }
        
        // =========================================================
        // 3. BUAT ROLE
        // =========================================================
        $adminRole = Role::create(['name' => 'Admin']);
        $guruRole = Role::create(['name' => 'Guru']);
        $waliMuridRole = Role::create(['name' => 'Wali Murid']);
        
        // =========================================================
        // 4. ASSIGN PERMISSIONS
        // =========================================================
        
        // Admin: Mendapatkan SEMUA izin
        $adminRole->givePermissionTo(Permission::all());

        // Guru: Hanya subset izin
        $guruRole->givePermissionTo([
            'dashboard-view',
            'siswa-view', 'guru-manage', 'mapel-manage',
            'nilai-view', 'nilai-input', 'nilai-input-sikap',
            'walikelas-manage-catatan',
            'laporan-view-absensi', 'cetak-print-legger'
        ]);
        
        // Wali Murid: Hanya subset izin
        $waliMuridRole->givePermissionTo([
            'dashboard-view', 
            'siswa-view', 
            'nilai-view', 
            'laporan-view-absensi'
        ]);

        // 5. ASSIGN ROLE KE USER PERTAMA (Untuk Pengujian)
        $user = User::first(); 
        if ($user) {
            $user->assignRole($adminRole);
        }
    }
}
