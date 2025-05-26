<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Ukuran extends Model
{
    protected $table = 'ukuran';
    protected $primaryKey = 'id_ukuran';
    public $incrementing = true; 
    protected $keyType = 'int';

    protected $fillable = [
        'nama_ukuran', 
        'harga_ukuran'
    ];

    public function orderan()
    {
        return $this->hasMany(Orderan::class, 'id_ukuran');
    }
}
