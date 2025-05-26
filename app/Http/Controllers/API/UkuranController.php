<?php

namespace App\Http\Controllers\API;

use App\Models\Ukuran;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class UkuranController extends Controller
{
     // 1. Menampilkan semua ukuran dan harga tanpa id
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Ukuran::select('nama_ukuran', 'harga_ukuran')->get()
        ]);
    }
    
     // 2. Menampilkan semua ukuran, harga termasuk id
    public function index2()
    {
        $ukurans = Ukuran::all();

        return response()->json([
            'success' => true,
            'data' => $ukurans
        ]);
    }

    // 3. Menampilkan ukurannya saja
    public function index3()
    {
        return response()->json([
            'success' => true,
            'data' => Ukuran::select('nama_ukuran')->get()
        ]);
    }
    // 4. Menampilkan ukuran berdasarkan ID tertentu
        public function show($id)
    {
        $ukuran = Ukuran::find($id);

    if (!$ukuran) {
        return response()->json([
            'success' => false,
            'message' => 'Ukuran tidak ditemukan'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $ukuran
    ]);
    }
}