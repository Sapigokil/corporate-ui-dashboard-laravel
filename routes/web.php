<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\PdfController; // Impor Controller
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InfoSekolahController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\PembelajaranController;
use App\Http\Controllers\MasterEkskulController;
use App\Http\Controllers\PesertaEkskulController;
use App\Http\Controllers\RaporNilaiController;
use App\Http\Controllers\RaporCatatanController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/dashboard');
})->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');

Route::get('/tables', function () {
    return view('tables');
})->name('tables')->middleware('auth');

Route::get('/wallet', function () {
    return view('wallet');
})->name('wallet')->middleware('auth');

Route::get('/RTL', function () {
    return view('RTL');
})->name('RTL')->middleware('auth');

Route::get('/profile', function () {
    return view('account-pages.profile');
})->name('profile')->middleware('auth');

Route::get('/signin', function () {
    return view('account-pages.signin');
})->name('signin');

Route::get('/signup', function () {
    return view('account-pages.signup');
})->name('signup')->middleware('guest');

Route::get('/sign-up', [RegisterController::class, 'create'])
    ->middleware('guest')
    ->name('sign-up');

Route::post('/sign-up', [RegisterController::class, 'store'])
    ->middleware('guest');

Route::get('/sign-in', [LoginController::class, 'create'])
    ->middleware('guest')
    ->name('sign-in');

Route::post('/sign-in', [LoginController::class, 'store'])
    ->middleware('guest');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [ResetPasswordController::class, 'store'])
    ->middleware('guest');

// Route::get('/laravel-examples/user-profile', [ProfileController::class, 'index'])->name('users.profile')->middleware('auth');
// Route::put('/laravel-examples/user-profile/update', [ProfileController::class, 'update'])->name('users.update')->middleware('auth');
// Route::get('/laravel-examples/users-management', [UserController::class, 'index'])->name('users-management')->middleware('auth');

// Route::get('/test-pdf-corporate', [PdfController::class, 'generatePdf']);

// =========================================================
// GRUP ROUTE E-RAPOR: PENGATURAN SISTEM (Membutuhkan Spatie Permission)
// URL Prefix: /pengaturan
// =========================================================

// Middleware 'can:pengaturan-manage-users' memastikan hanya yang punya izin Admin/Operator yang bisa akses
Route::prefix('pengaturan')->middleware(['auth', 'can:pengaturan-manage-users'])->group(function () {
    
    // 1. ROLE MANAGEMENT
    // Route resource untuk Role (users.index, users.edit, users.update, etc.)
    // Nama Route: roles.index, roles.create, roles.edit, roles.update, roles.destroy
    Route::resource('roles', RoleController::class)->except(['show']);
    
    // 2. USER MANAGEMENT
    // Route resource untuk User (users.index, users.edit, users.update, etc.)
    // Nama Route: users.index, users.create, users.edit, users.update, users.destroy
    // Catatan: Jika Anda ingin menggunakan URL /users untuk list user, Anda harus mengganti nama users-management yang sudah ada
    Route::resource('users', UserController::class)->except(['show']);
    
});

// Catatan: Jika Anda ingin mempertahankan route lama:
// Route::get('/laravel-examples/users-management', [UserController::class, 'index'])->name('users-management')->middleware('auth');
// Maka Anda harus MENGHAPUS salah satu dari resource users atau mengganti nama route users.index menjadi users-management.index
// Untuk kesederhanaan, kita ganti yang lama:

Route::get('/laravel-examples/users-management', [UserController::class, 'index'])->name('users-management.index')->middleware('auth');

Route::prefix('master-data')->name('master.')->group(function () {
    // INFO SEKOLAH
    Route::get('/sekolah', [InfoSekolahController::class, 'infoSekolah'])
        ->name('sekolah.index')
        ->middleware('can:manage-master'); // Otorisasi di tingkat route
        
    Route::post('/sekolah', [InfoSekolahController::class, 'update_info_sekolah'])
        ->name('sekolah.update')
        ->middleware('can:manage-master');
    
    Route::resource('guru', GuruController::class)
        ->names('guru') // Memberikan nama master.guru.index, .create, .store, dll.
        ->parameters(['guru' => 'guru']) // Menggunakan singular 'guru' di URL/Binding {guru}
        ->middleware('can:manage-master'); 
        
    // Route Ekspor/Impor
    Route::post('guru/import', [GuruController::class, 'importCsv'])->name('guru.import');
    Route::post('guru/import/xlsx', [GuruController::class, 'importXlsx'])->name('guru.import.xlsx'); // NEW: Excel Import
    Route::get('guru/export/pdf', [GuruController::class, 'exportPdf'])->name('guru.export.pdf');
    Route::get('guru/export/csv', [GuruController::class, 'exportCsv'])->name('guru.export.csv');
        // ... (Route untuk Guru, Siswa, Kelas, dll. akan ditambahkan di sini)
    
    Route::resource('siswa', SiswaController::class)
        ->names('siswa') // Memberikan nama master.siswa.index, .create, .store, dll.
        ->parameters(['siswa' => 'siswa']) // Menggunakan singular 'siswa' di URL/Binding {siswa}
        ->middleware('can:manage-master');
    
    // Tambahan untuk Import/Export (jika perlu)
    Route::get('siswa/export/pdf', [SiswaController::class, 'exportPdf'])->name('siswa.export.pdf');
    Route::get('siswa/export/csv', [SiswaController::class, 'exportCsv'])->name('siswa.export.csv');
    Route::post('siswa/import/csv', [SiswaController::class, 'importCsv'])->name('siswa.import.csv');
    // Route baru untuk Excel. Kita akan membuat method importXlsx jika Anda memutuskan menggunakan library Excel
    Route::post('siswa/import/xlsx', [SiswaController::class, 'importXlsx'])->name('siswa.import.xlsx');

    // Resource Route untuk CRUD dasar (index, create, store, show, edit, update, destroy)
    Route::resource('kelas', KelasController::class)
        ->names('kelas') // Memberikan nama master.kelas.index, .create, .store, dll.
        ->parameters(['kelas' => 'id_kelas']) // Menggunakan id_kelas di URL/Binding {id_kelas}
        ->middleware('can:manage-master'); 
        
    // Route Ekspor
    Route::get('kelas/export/pdf', [KelasController::class, 'exportPdf'])->name('kelas.export.pdf');
    Route::get('kelas/export/csv', [KelasController::class, 'exportCsv'])->name('kelas.export.csv');
    Route::get('kelas/{id_kelas}/export/single', [KelasController::class, 'exportKelas'])->name('kelas.export.single');
    Route::get('kelas/{id_kelas}/anggota', [KelasController::class, 'anggota'])->name('kelas.anggota');
    Route::delete('kelas/anggota/{id_siswa}', [KelasController::class, 'hapusAnggota'])->name('kelas.anggota.delete');

    // TAMBAH: MATA PELAJARAN (MAPEL)
    Route::resource('mapel', MapelController::class)
        ->names('mapel') // Nama route: master.mapel.index, .create, dll.
        ->parameters(['mapel' => 'id_mapel']) // Binding ke {id_mapel}
        ->middleware('can:manage-master');

    //  KOREKSI: PEMBELAJARAN
    Route::prefix('pembelajaran')->group(function () {
        //  KOREKSI METHOD: Menunjuk ke PembelajaranController@dataPembelajaran
        Route::get('/', [PembelajaranController::class, 'dataPembelajaran'])->name('pembelajaran.index'); 
        //  TAMBAH ROUTE CREATE (GET /master-data/pembelajaran/create)
        Route::get('/create', [PembelajaranController::class, 'create'])->name('pembelajaran.create');
        //  TAMBAH ROUTE EDIT
        Route::get('/{id}/edit', [PembelajaranController::class, 'edit'])->name('pembelajaran.edit');
        // Store (POST /master-data/pembelajaran) -> PembelajaranController@store
        Route::post('/', [PembelajaranController::class, 'store'])->name('pembelajaran.store');
        // Update (PUT/PATCH /master-data/pembelajaran/{id}) -> PembelajaranController@update
        Route::match(['put', 'patch'], '/{id}', [PembelajaranController::class, 'update'])->name('pembelajaran.update');
        // Destroy (DELETE /master-data/pembelajaran/{id}) -> PembelajaranController@destroy
        Route::delete('/{id}', [PembelajaranController::class, 'destroy'])->name('pembelajaran.destroy');
        
        // Route Export
        Route::get('/export/pdf', [PembelajaranController::class, 'exportPdf'])->name('pembelajaran.export.pdf');
        Route::get('/export/csv', [PembelajaranController::class, 'exportCsv'])->name('pembelajaran.export.csv');
    })->middleware('can:manage-master');

    Route::prefix('ekskul')->group(function () {
    
        // =========================================================
        // 1. MASTER EKSKUL (List Ekstrakurikuler) -> ekskul.list.*
        // =========================================================
        
        // Pastikan nama rute master.ekskul.list.* diganti menjadi ekskul.list.*
        Route::prefix('list')->name('ekskul.list.')->group(function () {
            
            // INDEX: Menggantikan dataEkskul (dipanggil saat route ekskul.list.index)
            Route::get('/', [MasterEkskulController::class, 'index'])->name('index'); 
            
            // CREATE, STORE
            Route::get('/create', [MasterEkskulController::class, 'create'])->name('create');
            Route::post('/', [MasterEkskulController::class, 'store'])->name('store');

            // EDIT, UPDATE, DELETE
            Route::get('/{id_ekskul}/edit', [MasterEkskulController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], '/{id_ekskul}', [MasterEkskulController::class, 'update'])->name('update');
            Route::delete('/{id_ekskul}', [MasterEkskulController::class, 'destroy'])->name('destroy');
        });


        // =========================================================
        // 2. PESERTA EKSKUL (Data Ekstrakurikuler) -> ekskul.siswa.*
        // =========================================================

        Route::prefix('peserta')->name('ekskul.siswa.')->group(function () {
            
            // INDEX: Menggantikan dataEkskul (dipanggil saat route ekskul.siswa.index)
            Route::get('/', [PesertaEkskulController::class, 'index'])->name('index');
            
            // CREATE, STORE
            Route::get('/create', [PesertaEkskulController::class, 'create'])->name('create');
            Route::post('/', [PesertaEkskulController::class, 'store'])->name('store');
            
            // EDIT, UPDATE, DELETE
            Route::get('/{id_ekskul_siswa}/edit', [PesertaEkskulController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], '/{id_ekskul_siswa}', [PesertaEkskulController::class, 'update'])->name('update');
            Route::delete('/{id_ekskul_siswa}', [PesertaEkskulController::class, 'destroy'])->name('destroy');
        });

    })->middleware('can:manage-master'); // Middleware tetap dipertahankan

    Route::prefix('rapornilai')->name('rapornilai.')->group(function () {
        
        // 1. READ: Dashboard Progres (Route: rapornilai.index)
        Route::get('/', [RaporNilaiController::class, 'index'])->name('index'); 
        
        // 2. CREATE: Menampilkan form input/edit nilai (Route: rapornilai.create)
        Route::get('/create', [RaporNilaiController::class, 'create'])->name('create');
        
        // 3. STORE/UPDATE: Proses Simpan/Update data (Route: rapornilai.store)
        Route::post('/', [RaporNilaiController::class, 'store'])->name('store');
        
        // 4. DELETE: Menghapus data berdasarkan ID Rapor (Route: rapornilai.destroy)
        Route::delete('/{id_rapor}', [RaporNilaiController::class, 'destroy'])->name('destroy'); 

        
        // B. Catatan Wali Kelas 
        Route::prefix('wali')->name('wali.')->group(function () {
            Route::get('/catatan', [RaporCatatanController::class, 'inputCatatan'])->name('catatan');
            Route::post('/simpan', [RaporCatatanController::class, 'simpanCatatan'])->name('simpan');
            Route::get('/get-siswa/{id_kelas}', [RaporCatatanController::class, 'getSiswa'])->name('get_siswa'); 
        });
    });
});