<?php

namespace App\Http\Controllers\API;

use App\Models\Orderan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrderanController extends Controller
{
    // 1. Tambah order
    public function buatOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array|min:1',
            'orders.*.id_menu' => 'required|exists:menu,id_menu',
            'orders.*.id_ukuran' => 'required|exists:ukuran,id_ukuran',
            'orders.*.jumlah' => 'required|integer|min:1',
            'orders.*.id_topping' => 'array',
            'orders.*.id_topping.*' => 'exists:topping,id_topping',
        ]);

        $orderanList = [];

        foreach ($request->orders as $data) {
            $orderan = Orderan::create([
                'id_transaksi' => null,
                'id_menu' => $data['id_menu'],
                'id_ukuran' => $data['id_ukuran'],
                'jumlah' => $data['jumlah'],
            ]);

            $orderan->toppings()->sync($data['id_topping'] ?? []);
            $orderan->load('menu', 'ukuran', 'toppings');
            $orderanList[] = $orderan;
        }

        $totalHarga = collect($orderanList)->sum('sub_total');

        return response()->json([
            'checkout' => $this->generateCheckoutData($orderanList, $totalHarga),
            'message' => 'Order berhasil dibuat'
        ]);
    }

    // 2. Update order
    public function updateOrderan(Request $request, $id_orderan)
    {
        $request->validate([
            'id_menu' => 'required|exists:menu,id_menu',
            'id_ukuran' => 'required|exists:ukuran,id_ukuran',
            'jumlah' => 'required|integer|min:1',
            'id_topping' => 'array',
            'id_topping.*' => 'exists:topping,id_topping',
        ]);

        $orderan = Orderan::find($id_orderan);
        if (!$orderan) {
            return response()->json(['message' => 'Orderan tidak ditemukan'], 404);
        }

        $orderan->id_menu = $request->id_menu;
        $orderan->id_ukuran = $request->id_ukuran;
        $orderan->jumlah = $request->jumlah;
        $orderan->save();

        $orderan->toppings()->sync($request->id_topping ?? []);
        $orderan->load('menu', 'ukuran', 'toppings');

        $totalHarga = $orderan->sub_total;

        return response()->json([
            'checkout' => $this->generateCheckoutData(collect([$orderan]), $totalHarga),
            'message' => 'Order berhasil diperbarui'
        ]);
    }

    // 3. Tampilkan satu orderan
    public function ListOrderan($id_orderan)
    {
        $orderan = Orderan::with('menu', 'ukuran', 'toppings')->where('id_orderan', $id_orderan)->first();

        if (!$orderan) {
            return response()->json(['message' => 'Orderan tidak ditemukan'], 404);
        }

        return response()->json([
            'checkout' => $this->generateCheckoutData(collect([$orderan]), $orderan->sub_total),
            'message' => 'Data orderan ditemukan'
        ]);
    }

    // 4. Hapus
    public function hapusOrderan($id_orderan)
    {
        $orderan = Orderan::find($id_orderan);
        if (!$orderan) {
            return response()->json(['message' => 'Orderan tidak ditemukan'], 404);
        }

        $orderan->toppings()->detach();
        $orderan->delete();

        return response()->json(['message' => 'Orderan berhasil dihapus']);
    }

    // ✅ Ubah: Generate checkout-style data
    private function generateCheckoutData($orderans, $totalHarga)
    {
        $data = [];

        foreach ($orderans as $order) {
            $menu = $order->menu;
            $ukuran = $order->ukuran;
            $toppings = $order->toppings;

            $data[] = [
                'nama_produk' => $menu->nama_menu ?? '-',
                'img_url' => $menu->img_url ?? null,
                'harga_menu' => $menu->harga ?? 0,
                'ukuran' => [
                    'nama_ukuran' => $ukuran->nama_ukuran ?? '-',
                    'harga_ukuran' => $ukuran->harga_ukuran ?? 0
                ],
                'jumlah' => $order->jumlah,
                'topping' => $toppings->map(function ($t) {
                    return [
                        'nama_topping' => $t->nama_topping,
                        'harga_topping' => $t->harga_topping
                    ];
                }),
                'subtotal' => $order->sub_total,
            ];
        }

        return [
            'items' => $data,
            'total_bayar' => $totalHarga,
        ];
    }
}
