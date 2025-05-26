<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Topping extends Model
{
    protected $table = 'topping';

    protected $primaryKey = 'id_topping';
    protected $fillable = [
        'nama_topping', 
        'harga_topping'
    ];

    public function orderan()
    {
        return $this->belongsToMany(Orderan::class, 'orderan_topping', 'id_topping', 'id_orderan');
    }
}