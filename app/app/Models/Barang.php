<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id_barang';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_barang',
        'nama',
        'guna_merek',
        'ukuran',
        'ketebalan',
        'bentuk',
        'bahan',
        'harga',
        'jumlah',
        'total',
    ];

    public function getIdBarangAttribute(): mixed
    {
        return $this->attributes['id_barang'];
    }
}