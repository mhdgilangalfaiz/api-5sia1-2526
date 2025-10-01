<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Tampilkan semua data Produk.
     * Beserta pemiliknya (user)
     */
    public function index()
    {
        // untuk memanggil relasi terkait, sebutkan 
        // nama method yang ada di model tersebut.
        // gunakan method with() untuk menyertakan relasi tabel
        // pada data yang dipanggil.
        $products = Product::query()
            ->where('is_available', true) // Hanya produk tersedia
            ->with('user')                // sertakan pemiliknya
            ->get();                      // eksekusi query
        // format respon ada status (Sukses/Gagal) dan data
        return response()->json([
            'status' => 'Sukses',
            'data' => $products
        ]);
    }

    /**
     * Cari produk berdasarkan 'name'
     * dan ikutkan relasinya
     */
    public function search(Request $req)
    {
        try {
            // validasi minimal 3 huruf untuk pencarian
            $validate = $req->validate([
                'teks' => 'required|min:3',
            ], [
                // pesan error costum
                'teks.required' => ':Attribute jangan dikosongkan',
                'teks.min' => 'Ini :attribute kurang dari :min',
            ], [
                // costum attribute
                'teks' => 'huruf'
            ]);

            //proses pencarian produk berdasarkan teks yang dikirim
            $products = Product::query()
                ->where('name', 'like', '%' . $req->teks . '%')
                ->with('user')
                ->get();
            return response()->json([
                'pesan' => 'Sukses!',
                'data' => $products,
            ]);

        } catch (ValidationException $ex) {
            return response()->json([
                'pesan' => 'Gagal!',
                'data' => $ex->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validate = $request->validate(
                [
                    'user_id' => 'nullable|exists:users,id',
                    'name' => 'required|string|max:255',
                    'image_path' => 'nullable|string',
                    'stock' => 'required|integer',
                    'price' => 'required|numeric',
                    'description' => 'required|string',
                ],
                [
                    'name.required' => 'Nama produk jangan dikosongkan',
                    'price.required' => 'Harga wajib diisi',
                    'price.numeric' => 'Harga harus berupa angka',
                    'stock.required' => 'Stok wajib diisi',
                    'stock.integer' => 'Stok harus berupa angka'
                ]
            );

            $product = Product::create($validate);

            return response()->json([
                'status' => 'Sukses',
                'data' => $product
            ]);

        } catch (ValidationException $ex) {
            return response()->json([
                'pesan' => 'Gagal!',
                'data' => $ex->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // cari produk berdasarkan id
            $product = Product::with('user')->findOrFail($id);

            return response()->json([
                'status' => 'Product ini ada!',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Gagal',
                'pesan' => 'Produk dengan ID ' . $id . ' tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r, $id)
    {
        try {
            // untuk mencari produk berdasarkan id
            $product = Product::findOrFail($id);

            $validate = $r->validate([
                'name' => 'string|max:22',
                'price' => 'numeric|min:0',
                'image_path' => 'nullable|string',
                'stock' => 'integer|min:0',
                'description' => 'nullable|string',
            ]);
            $product->update([
                'name' => $data['name'] ?? $product->name,
                'price' => $data['price'] ?? $product->price,
                'image_path' => $data['image_path'] ?? $product->image_path,
                'stock' => $data['stock'] ?? $product->stock,
                'description' => $data['description'] ?? $product->description,
            ]);
            $product->update(array_filter($validate, function ($value) {
                return $value !== null;
            }));

            return response()->json([
                'status' => 'Produk Berhasil Diubah',
            ]);

        } catch (ValidationException $ex) {
            return response()->json([
                'pesan' => 'Gagal Mengubah Data',
                'error' => $ex->validator->errors(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Cari produk berdasarkan id
            $product = Product::findOrFail($id);

            // Hapus produk
            $product->delete();

            return response()->json([
                'status' => 'Sukses',
                'message' => 'Produk berhasil dihapus',
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'Gagal',
                'error' => $ex->getMessage()
            ], 404);
        }
    }
}