<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Tampilkan semua data produk.
     * Beserta pemiliknya (user)
     */
    public function index()
    {

    }

    /**
     * cari produk berdasarkan nama
     * dan ikuti relasinya
     */
    public function search(Request $req)
    {
        try {
            // validasi minimal 3 huruf buat pencarian
            $validated = $req->validate([
                'teks' => 'required|min:3',
            ], [
                // pesan eror costum
                'teks.required' => ':Attribute jangan dikosngkan lah!',
                'teks.min' => 'Ini :attribut kurang dari :min bos!',
            ], [
                //custom attributs
                'teks' => 'huruf'
            ]);

            // proses pencarian produk berdasarkan teks yang dikirim
            $products = Product::query()
                ->where('name', 'like', '%' . $req->teks . '%')
                ->with('user')
                ->get();
            // return hasil pencarian
            return response()->json([
                'pesan' => 'Sukses!',
                'data' => $products,
            ]);
        }
        //use Illuminate\Validation\ValidationException;
        catch (ValidationException $ex) {
            return response()->json([
                'pesan' => 'Gagal',
                'data' => $ex->getMessage(),
            ]);
        }
        // untuk memanggil relasi terkait, sebutkan
        // nama method relasi yang ada di model tersebut 
        // Gunakan  method with() untuk menyertakn relasi table
        // pada data yang di panggil
        $products = Product::query()
            ->where('is_available', true)
            ->with('user')
            ->get();
        // Format respon ada status (sukses/gagal) dan data
        return response()->json([
            'status' => 'Sukses',
            'data' => $products
        ]);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
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
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
