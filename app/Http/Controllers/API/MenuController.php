<?php

namespace App\Http\Controllers\API;

use App\Models\Menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    // 1. Menampilkan semua menu tanpa id
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Menu::select('img_url', 'nama_menu', 'harga', 'deskripsi')->get()
        ]);
    }
    // 2. Menampilkan semua menu 
    public function index2()
    {
        return response()->json([
            'success' => true,
            'data' => Menu::with('kategori')->get()
        ]);
    }
    // 3. Menampilkan hanya gambar, nama menu, dan harga
    public function simpleList()
    {
        return response()->json([
            'success' => true,
            'data' => Menu::select('img_url', 'nama_menu', 'harga')->get()
        ]);
    }
    // 4. Menampilkan menu berdasarkan ID menu tertentu
    public function show($id)
    {
        $menu = Menu::with('kategori')->find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $menu
        ]);
    }
    // 5. Menampilkan list menu berdasarkan id_kategori
    public function byKategori($id_kategori)
    {
        $menu = Menu::where('id_kategori', $id_kategori)->get();

        return response()->json([
            'success' => true,
            'data' => $menu
        ]);
    }
}
