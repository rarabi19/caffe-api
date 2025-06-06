<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\IdGenerator;

class Kategori extends Model
{
        protected $table = 'kategori'; 
        protected $primaryKey = 'id_kategori';

        protected $fillable = ['nama_kategori'];
}