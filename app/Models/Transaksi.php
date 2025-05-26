<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $primaryKey = 'id_transaksi';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'tanggal', 
        'nama_pembeli', 
        'pembayaran', 
        'status', 
        'total_harga', 
        'nominal', 
        'kembalian'
    ];
        protected $dates = ['tanggal', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function orderan()
    {
        return $this->hasMany(Orderan::class, 'id_transaksi');
    }
}
