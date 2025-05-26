<?php

namespace App\Models;
use App\Models\Orderan;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{

    protected $table = 'menu';
    protected $primaryKey = 'id_menu';
    public $incrementing = true;

    protected $fillable = [
        'id_menu', 
        'id_kategori', 
        'img_url', 
        'nama_menu', 
        'deskripsi', 
        'harga_dasar'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function orderan()
    {
        return $this->hasMany(Orderan::class, 'id_menu');
    }
}
