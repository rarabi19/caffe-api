<?php

namespace App\Http\Controllers\API;
use id;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class KategoriController extends Controller
{
    // 1. Menampilkan kategori tertentu tanpa id
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Kategori::select('nama_kategori')->get()
        ]);
    }
    
    // 2. Menampilkan semua kategori
    public function index2()
    {
        return response()->json([
            'success' => true,
            'data' => Kategori::all()
        ]);
    }

    // 3. Menampilkan kategori berdasarkan ID tertentu
    public function show($id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $kategori
        ]);
    }
}