<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTopping extends Model
{
    protected $table = 'order_topping';
    protected $primaryKey = 'id_order_topping';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_orderan', 
        'id_topping'
    ];

    public function topping()
    {
        return $this->belongsTo(Topping::class, 'id_topping');
    }
}
