<?php

namespace App\Http\Controllers\API;

use App\Models\Topping;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ToppingController extends Controller
{
     // 1. Menampilkan semua topping tanpa id
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Topping::select( 'nama_topping', 'harga_topping')->get()
        ]);
    }

   // 2. Menampilkan semua topping termasuk id nya
    public function index2()
    {
        return response()->json([
            'success' => true,
            'data' => Topping::all()
        ]);
    }

    // 3. Menampilkan topping berdasarkan ID tertentu
    public function show($id)
    {
        $topping = Topping::find($id);

        if (!$topping) {
            return response()->json([
                'success' => false,
                'message' => 'Topping yang kamu inginkan tidak tersedia'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $topping
        ]);
    }
}