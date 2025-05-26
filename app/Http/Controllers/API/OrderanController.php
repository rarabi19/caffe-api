<?php

namespace App\Http\Controllers\API;

use App\Models\Orderan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;

class OrderanController extends Controller
{
    // 1. Menambahkan Pesanan/ order
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

        $struk = $this->generateStruk(null, $orderanList, $totalHarga);

        return response($struk, 200)->header('Content-Type', 'text/plain');
    }

    // 2. Update orderan
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
        $orderan->toppings()->sync($request->id_topping ?? []);
        $orderan->load('menu', 'ukuran', 'toppings');
        $orderan->save();

        if ($orderan->id_transaksi) {
            $orderans = Orderan::with('menu', 'ukuran', 'toppings')
                ->where('id_transaksi', $orderan->id_transaksi)
                ->get();
            $totalHarga = $orderans->sum('sub_total');

            $struk = $this->generateStruk($orderan->id_transaksi, $orderans, $totalHarga);
            return response($struk, 200)->header('Content-Type', 'text/plain');
        }

        $struk = $this->generateStruk(null, collect([$orderan]), $orderan->sub_total);
        return response($struk, 200)->header('Content-Type', 'text/plain');
    }

    // 3. Tampilkan struk berdasarkan id_orderan
    public function ListOrderan($id_orderan)
    {
        $orderan = Orderan::with('menu', 'ukuran', 'toppings')
            ->where('id_orderan', $id_orderan)
            ->first();

        if (!$orderan) {
            return response()->json(['message' => 'Orderan tidak ditemukan'], 404);
        }

        $totalHarga = $orderan->sub_total;
        $struk = $this->generateStruk($orderan->id_transaksi, collect([$orderan]), $totalHarga);

        return response($struk, 200)->header('Content-Type', 'text/plain');
    }
    // 4. Hapus orderan (DELETE)
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

    // generate struk orderan
    private function generateStruk($id_transaksi, $orderans, $totalHarga)
{
    $line = str_repeat('-', 42) . "\n";
    $struk = $line;
    $struk .= "            Caffe Suka Cita\n";
    $struk .= "     Jl. Indonesia Suram No. 2025\n";
    $struk .= "       Contact: 0822-3345-6755\n";
    $struk .= $line;

    if ($id_transaksi) {
        $struk .= "ID Transaksi : $id_transaksi\n";
        $struk .= $line;
    }

    foreach ($orderans as $order) {
        $menu = $order->menu->nama_menu ?? '-';
        $ukuran = $order->ukuran->nama_ukuran ?? '-';
        $jumlah = $order->jumlah;
        $harga = number_format($order->sub_total, 0, ',', '.');

        $struk .= "$menu ($ukuran) x$jumlah\n";

        foreach ($order->toppings as $topping) {
            $nama = $topping->nama_topping;
            $hargaTop = number_format($topping->harga_topping, 0, ',', '.');
            $label = "  + $nama";
            $struk .= str_pad($label, 30) . ": Rp" . str_pad($hargaTop, 10, ' ', STR_PAD_LEFT) . "\n";
        }

        $labelSubtotal = "  Subtotal";
        $struk .= str_pad($labelSubtotal, 30) . ": Rp" . str_pad($harga, 10, ' ', STR_PAD_LEFT) . "\n";
        $struk .= $line;
    }

    $totalFormatted = number_format($totalHarga, 0, ',', '.');
    $struk .= str_pad("Total Bayar", 30) . ": Rp" . str_pad($totalFormatted, 10, ' ', STR_PAD_LEFT) . "\n";
    $struk .= $line;
    $struk .= "Terima kasih telah melakukan pembelian\n";
    $struk .= "Pesanan Anda sedang diproses ✨\n";
    $struk .= $line;

    return $struk;
    }
}
