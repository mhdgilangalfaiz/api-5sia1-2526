<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    // izinkan semua kolom diisi secara massal (mass assignment)
    protected $guarded = [];

    // format data saat dipanggil
    protected $casts = [
        'is_available' => 'boolean',
    ];

    // sembunyikan kolom tertentu
    protected $hidden = ['image_path'];

    //sisipkan data baru pada objek product
    protected $appends = ['image_url'];

    // format alamat gambar menjadi url
    // use Illuminate\Database\Eloquent\Casts\Attribute;
    public function imageUrl(): Attribute
    {
        // Use Illuminate/support/Facades/Storage;
        return Attribute::make(
            // getL format data saat dipanggil di database
            // ternary (short) if untuk memeriksa kolom image_path
            get: fn() => $this->image_path
            // return url foto
            ? Storage::disk('public')
                ->url($this->image_path)
            // return null jika tidak ada    
            : null,
            // set: format data yang akan disimpan di database
        );
    }

    // ini sambungannya...
    // use Illuminate/Database/Eloquent/Relations/BelongsTo
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
