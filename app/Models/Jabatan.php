<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'jabatans'; // Tentukan nama tabel jika diperlukan

    // Menentukan kolom yang dapat diisi secara massal
    protected $fillable = [
        'jabatan_id',
        'name',
    ];
}
