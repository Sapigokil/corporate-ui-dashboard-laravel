<?php

// app/Http/Controllers/RoleController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role; // Penting: Menggunakan Model Role dari Spatie
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    // Fungsi untuk menampilkan daftar Role
    public function index()
    {
        // Ambil semua Role, dan muat relasi permissions dan users
        $roles = Role::with('permissions', 'users')->get(); 
        
        return view('user.roles.index', compact('roles'));
    }

    // ... fungsi create, store, edit, update lainnya
    
    // Fungsi Edit akan digunakan untuk mengatur Izin/Permissions
    public function edit(Role $role)
    {
        // Ambil semua permission yang tersedia
        $permissions = Permission::all();
    
    // Kirim objek role dan semua permissions ke view
    return view('user.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        // 1. Validasi Input
        $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,name,' . $role->id, // Name unik, kecuali diri sendiri
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name', // Pastikan semua permission yang dikirim valid
        ]);

        // 2. Update Nama Role (jika diizinkan)
        // Jika Anda membiarkan nama role bisa diedit di view roles.edit
        // $role->update(['name' => $request->role_name]);

        // 3. Sinkronisasi Permissions (Penting!)
        
        // Ambil permissions yang dikirim dari form (array of permission names)
        $newPermissions = $request->input('permissions', []);

        // Gunakan syncPermissions dari Spatie untuk mengganti semua permission lama
        // dengan list yang baru.
        $role->syncPermissions($newPermissions); 
        
        // 4. Redirect dengan Pesan Sukses
        return redirect()->route('roles.index')
                         ->with('success', 'Role "' . $role->name . '" dan Izin berhasil diperbarui.');
    }
    
    public function create()
    {
        // Ambil semua permission yang tersedia
        $permissions = Permission::all();

        return view('user.roles.create', compact('permissions'));
    }
    
    // FUNGSI STORE: Menyimpan Role baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);
        
        // 1. Buat Role Baru
        $role = Role::create(['name' => strtolower($request->name)]); // Simpan nama role dalam huruf kecil (best practice Spatie)
        
        // 2. Sinkronisasi Izin (Permissions)
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }
        
        return redirect()->route('roles.index')
                         ->with('success', 'Role ' . Str::title($role->name) . ' berhasil dibuat dengan izin yang dipilih.');
    }// ... (Fungsi destroy, create, store)

    public function destroy(Role $role)
    {
        // 1. Tentukan Role yang Krusial
        $protectedRoles = ['admin', 'guru', 'wali murid'];

        // 2. Cek apakah Role termasuk yang krusial
        if (in_array(strtolower($role->name), $protectedRoles)) {
            return redirect()->route('roles.index')
                             ->with('error', 'Role ' . Str::title($role->name) . ' adalah Role krusial dan tidak dapat dihapus.');
        }

        // 3. Cek apakah Role masih memiliki pengguna yang terhubung
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                             ->with('error', 'Role ' . Str::title($role->name) . ' tidak dapat dihapus karena masih terhubung dengan ' . $role->users()->count() . ' pengguna.');
        }

        // 4. Proses Penghapusan
        $roleName = Str::title($role->name);
        $role->delete();

        return redirect()->route('roles.index')
                         ->with('success', 'Role ' . $roleName . ' berhasil dihapus.');
    }
}
