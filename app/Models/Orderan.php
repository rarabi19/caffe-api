<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orderan extends Model
{
    protected $table = 'orderan';
    protected $primaryKey = 'id_orderan';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'id_transaksi', 
        'id_menu', 
        'id_ukuran', 
        'jumlah', 
        'sub_total'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }

    public function ukuran()
    {
        return $this->belongsTo(Ukuran::class, 'id_ukuran');
    }

    public function toppings()
    {
        return $this->belongsToMany(Topping::class, 'order_topping', 'id_orderan', 'id_topping');
    }
    public function transaksi()
    {
    return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }


    public function calculateSubTotal()
    {
        $hargaMenu = $this->menu?->harga ?? 0;
        $hargaUkuran = $this->ukuran?->harga_ukuran ?? 0;
        $hargaTopping = $this->toppings->sum('harga_topping');

        return ($hargaMenu + $hargaUkuran + $hargaTopping) * $this->jumlah;
    }

    protected static function booted()
    {
        static::saving(function ($orderan) {
            if (!$orderan->relationLoaded('menu')) {
                $orderan->load('menu');
            }
            if (!$orderan->relationLoaded('ukuran')) {
                $orderan->load('ukuran');
            }
            if (!$orderan->relationLoaded('toppings')) {
                $orderan->load('toppings');
            }

            $orderan->sub_total = $orderan->calculateSubTotal();
        });
    }
}
