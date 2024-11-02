<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments'; // Tentukan nama tabel jika diperlukan

    // Menentukan kolom yang dapat diisi secara massal
    protected $fillable = [
        'id',
        'name',
    ];
}
