<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        // KOREKSI: Ganti ->get() menjadi ->paginate()
        $users = User::with('roles')->paginate(15); // Ambil 15 user per halaman
        
        $roles = Role::all(); 

        // Pastikan Anda juga memasukkan 'is_active' ke query jika Anda memerlukannya
        
        return view('user.users.index', compact('users', 'roles'));
    }

    public function edit(User $user)
    {
        // Ambil semua role untuk ditampilkan di dropdown
        $roles = Role::all();

        return view('user.users.edit', compact('user', 'roles'));
    }
    
    // FUNGSI UPDATE: Menyimpan perubahan
    public function update(Request $request, User $user)
    {
        // KOREKSI VALIDASI EMAIL:
        $request->validate([
            'name' => 'required|string|max:255',
            // Email harus unik, KECUALI jika email tersebut adalah email lama user yang sedang diedit
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id, 
            'role_name' => 'required|exists:roles,name',
        ]);
        
        // 1. Update Detail User
        $user->name = $request->name;
        // IZINKAN PERUBAHAN EMAIL
        $user->email = $request->email; 
        
        // Status aktif: jika checkbox dicentang, is_active=1. Jika tidak dicentang, default value adalah 0
        $user->is_active = $request->has('is_active'); 
        $user->save();
        
        // 2. Update Role (Spatie)
        $user->syncRoles([]); 
        $user->assignRole($request->role_name);
        
        return redirect()->route('settings.system.users.index')
                         ->with('success', 'Data pengguna dan role berhasil diperbarui.');
    }

    public function create()
    {
        $roles = Role::all();
        return view('user.users.create', compact('roles'));
    }
    
    // FUNGSI STORE: Menyimpan pengguna baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            // Aturan Password: minimal 8 karakter, harus dikonfirmasi (optional)
            'password' => ['required', 'string', Password::min(8)], 
            'role_name' => 'required|exists:roles,name',
        ]);
        
        // 1. Buat User Baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Wajib di hash
            'is_active' => $request->has('is_active'),
        ]);
        
        // 2. Assign Role (Sangat Penting)
        $user->assignRole($request->role_name);
        
        return redirect()->route('settings.system.users.index')
                         ->with('success', 'Akun pengguna baru berhasil dibuat dan di-assign ke Role ' . $request->role_name . '.');
    }

    public function destroy(User $user)
    {
        // 1. Cek: Jangan biarkan user menghapus akunnya sendiri yang sedang login
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        // 2. (Opsional) Cek: Jangan hapus Super Admin utama jika diperlukan
        if ($user->hasRole('admin') && $user->id == 1) { // Asumsi ID 1 adalah super admin
             return back()->with('error', 'Akun Super Admin utama tidak boleh dihapus.');
        }

        // 3. Hapus User
        $user->delete();

        // 4. Redirect
        return redirect()->route('settings.system.users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }
}
