<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    // ... method create tetap sama ...
    public function create()
    {
        return view('auth.signin');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Ambil input dari form (kita sebut fieldnya 'login' agar umum)
        $login = $request->input('login');
        
        // 2. Cek apakah input tersebut adalah format email yang valid
        // Jika format email, maka kolom db yang dicek adalah 'email', jika bukan maka 'username'
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // // 3. Cari User secara manual untuk pengecekan data
        // $user = \App\Models\User::where($fieldType, $login)->first();

        // // 4. Cek apakah password cocok (Manual Check)
        // $passwordCheck = false;
        // if ($user) {
        //     $passwordCheck = \Illuminate\Support\Facades\Hash::check($request->input('password'), $user->password);
        // }

        // // ==========================================
        // // TAMPILKAN DATA (DD)
        // // ==========================================
        // dd([
        //     '1. Input Login' => $login,
        //     '2. Tipe Terdeteksi' => $fieldType,
        //     '3. Input Password' => $request->input('password'),
        //     '4. Apakah User Ditemukan di DB?' => $user ? 'YA (Data Ada)' : 'TIDAK (Data Kosong)',
        //     '5. Data User' => $user ? $user->toArray() : null,
        //     '6. Password di Database (Hash)' => $user ? $user->password : '-',
        //     '7. Hasil Cek Password (Hash::check)' => $passwordCheck ? 'COCOK' : 'TIDAK COCOK',
        // ]);
        
        // 3. Susun credentials
        $credentials = [
            $fieldType => $login,
            'password' => $request->input('password')
        ];

        $rememberMe = $request->rememberMe ? true : false;

        // 4. Lakukan Attempt Login
        if (Auth::attempt($credentials, $rememberMe)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // 5. Jika gagal
        return back()->withErrors([
            'message' => 'Username/Email atau Password salah.',
        ])->withInput($request->only('login')); // Kembalikan input 'login'
    }

    // ... method destroy tetap sama ...
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/sign-in');
    }
}