<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException as ValidationException;

// default endpoint API: http://api-5sia1.test/api
// route ambil semua data user
//method: GET

// Product Controller
// Menampung semua logika dan perintah yangdiarahkan dari endpoint url berikut
use App\Http\Controllers\ProductController;

/**
 * API Resource untuk model product
 */
// 1. Ambil semua produk beserta pemiliknya (user)
// action url = [NamaController::class, 'method']
Route::get('/products/semuanya', [ProductController::class, 'index']);
// 2. Cari produk tersedia berdasarkan nama
Route::get('/products/cari', [ProductController::class, 'search']);


Route::get('/users', function () {
    //panggil semua data user dan simpan dalam variabel $users
    //Method with() digunakan untuk mengikutsertakan relasi
    //relasi yang disebutkan sesuai dengan nama method pada model
    $users = User::query()->with('products')->get();
    //convert ke dalam format JSON
    $json_users = json_encode($users);
    //berikan data (response) json ke aplikasi yang meminta (request)
    return $json_users;
});

// route cari user berdasarkan id
// method: GET
Route::get('/user/find', function (Request $request) {
    //cari user
    // $user = User::find($request->id);
    // cari user dengan relasi
    $user = User::query()
        ->where('id', $request->id)
        ->with('products')
        ->get();
    return json_encode($user);
});

// route cari user berdasarkan kemiripan nama atau email
// method: GET

Route::get('/user/search', function (Request $request) {
    // cari user berdasarkan string nama
    $users = User::where('nama', 'like', '%' . $request->name . '%')
        ->orWhere('email', 'like', '%' . $request->nama . '%')->get();
    // SELECT * FROM users WHERE name OR email LIKE '%ahmad%';
    return json_encode($users);
});

// Registrasi User
// Parameter name,email,phone,password
// password harus di hash sebelum disimpan ke tabel
Route::post('/register', function (Request $r) {
    //validasi data
    try {
        $validated = $r->validate([
            //param => rules
            'nama' => 'required|max:255',
            'surel' => 'required|email|unique:users,email',
            'telp' => 'required|unique:users,phone',
            'sandi' => 'required|min:6'
        ]);
        //tambahkan data user baru
        $new_user = User::query()->create([
            //field => params
            'name' => $r->nama,
            'email' => $r->surel,
            'phone' => $r->telp,
            'password' => Hash::make($r->sandi)
        ]);
        // return data user
        return response()->json($new_user);
    } catch (ValidationException $e) {
        return $e->validator->errors();
    }
});

// Ubah Data User
// Parameter name,surel,telp,sandi
// method 'PUT' atau 'PATCH'
// data user yang diubah berdasarkan id yang di kirim
// pada contoh ini , id akan di langsung di asosiasikan ke model User
Route::put('/user/edit/{user}', function (Request $r, User $user) {
    try {
        //validasi ubah data
        $validated = $r->validate([
            'nama' => 'max:255',
            'surel' => 'email|unique:users,email,' . $user->id,
            'telp' => 'unique:users,phone,' . $user->id,
            'sandi' => 'nullable|min:6'
        ]);
        //==================================
        // CARA SEDERHANA
        //==================================
        // $user->update([
        //     'name'      => $r->nama ?? $user->name,
        //     'email'     => $r->surel ?? $user->email,
        //     'phone'     => $r->telp ?? $user->phone,
        //     // jika ada data password, maka hash dan simpan
        //     // jika tidak ada, gunakan password lama
        //     'password'  => $r->sandi ? Hash::make($r->sandi) : $user->password
        // ]);
        //==================================

        // salin data yang diterima ke variable baru
        $data = $r->all();
        // jika ada data password pada array $data
        if (array_key_exists('sandi', $data)) {
            // replace isi 'sandi' dengan hasil hash 'sandi'
            $data['sandi'] = Hash::make($data['sandi']);
        }
        //ubah data user
        $user->update([
            'name' => $r->nama ?? $user->name,
            'email' => $r->surel ?? $user->email,
            'phone' => $r->telp ?? $user->phone,
            'password' => $data['sandi'] ?? $user->password
        ]);
        // kembalikan data user yang sudah di ubah beserta pesan sukses
        return response()->json([
            'pesan' => 'Sukses diubah!',
            'user' => $user,
        ]);

    } catch (ValidationException $e) {
        return $e->validator->errors();
    }
});

// Hapus Data User
// method 'DELETE'
// request dilakukan dengan menyertakan id user yang akan dihapus
Route::delete('/user/delete', function (Request $r) {
    // temukan user berdasarkan id yang dikirim
    $user = User::find($r->id);
    // respon jika user tidak ditemukan
    if (!$user)
        return response()->json([
            'pesan' => 'Gagal! user tidak ditemukan.',
        ]);

    //hapus data user
    $user->delete();
    return response()->json([
        'pesan' => 'Sukses! user berhasil dihapus.',
    ]);
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');