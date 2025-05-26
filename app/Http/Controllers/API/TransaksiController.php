<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Orderan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    // 1. Menampilkan Semua Transaksi yang pernah dilakukan 
    public function index()
    {
        $transaksis = Transaksi::with(['orderan.menu', 'orderan.ukuran'])
            ->orderByDesc('tanggal')
            ->get();

        $result = '';
        foreach ($transaksis as $transaksi) {
            $orderanList = $transaksi->orderan;
            $result .= $this->generateStruk($transaksi, $orderanList) . "\n\n";
        }

        return response($result, 200)->header('Content-Type', 'text/plain');
    }

    // 2. Melakukan Pembayaran terhadar berbagai orderan yang telah dilakukan 
    public function checkout(Request $request)
    {
        $request->validate([
            'id_orderan' => 'required|array|min:1',
            'id_orderan.*' => 'integer|exists:orderan,id_orderan',
            'nama_pembeli' => 'required|string|max:255',
            'pembayaran' => 'required|in:Tunai / Cash,Transfer Bank,E-Wallet,QRIS',
            'nominal' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $orderanList = Orderan::with(['ukuran', 'menu'])
                ->whereIn('id_orderan', $request->id_orderan)
                ->whereNull('id_transaksi')
                ->get();

            if ($orderanList->count() !== count($request->id_orderan)) {
                DB::rollBack();
                return response()->json(['message' => 'Ada orderan yang sudah dibayar atau tidak ditemukan.'], 400);
            }

            $totalBayar = $orderanList->sum('sub_total');

            if ($request->nominal < $totalBayar) {
                DB::rollBack();
                return response()->json(['message' => 'Uang bayar kurang dari total harga.'], 400);
            }

            $transaksi = Transaksi::create([
                'tanggal' => now(),
                'nama_pembeli' => $request->nama_pembeli,
                'pembayaran' => $request->pembayaran,
                'total_harga' => $totalBayar,
                'nominal' => $request->nominal,
                'kembalian' => $request->nominal - $totalBayar,
                'status' => 'selesai',
            ]);

            Orderan::whereIn('id_orderan', $request->id_orderan)
                ->update(['id_transaksi' => $transaksi->id_transaksi]);

            DB::commit();

            $struk = $this->generateStruk($transaksi, $orderanList);
            return response($struk, 200)->header('Content-Type', 'text/plain');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat checkout: ' . $e->getMessage()], 500);
        }
    }

    // 3. Menampilkan Struk Orderan berdasarkan id transaksi 
    public function showStruk($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $orderanList = Orderan::with(['menu', 'ukuran'])
            ->where('id_transaksi', $id)
            ->get();

        if ($orderanList->isEmpty()) {
            return response()->json(['message' => 'Orderan tidak ditemukan untuk transaksi ini'], 404);
        }

        $struk = $this->generateStruk($transaksi, $orderanList);
        return response($struk, 200)->header('Content-Type', 'text/plain');
    }

    // 4. Pengupdetan data transaksi berdasarkan id transaksi yang telah dilakukan 
    public function update(Request $request, $id)
    {
        $transaksi = Transaksi::find($id);
        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_pembeli' => 'sometimes|string|max:255',
            'pembayaran' => 'sometimes|in:Tunai / Cash,Transfer Bank,E-Wallet,QRIS',
            'status' => 'sometimes|in:selesai,dibatalkan,pending',
            'nominal' => 'sometimes|numeric|min:0',
        ]);

        $transaksi->update($request->only(['nama_pembeli', 'pembayaran', 'status', 'nominal']));

        $orderanList = Orderan::with(['menu', 'ukuran'])
            ->where('id_transaksi', $id)
            ->get();

        $struk = $this->generateStruk($transaksi, $orderanList);
        return response($struk, 200)->header('Content-Type', 'text/plain');
    }

    // 5. Menghapus transaksi tertentu menggunakan id_transaksi 
    public function destroy($id)
    {
        $transaksi = Transaksi::find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        Orderan::where('id_transaksi', $id)
            ->update(['id_transaksi' => null]);

        $transaksi->delete();

        return response()->json(['message' => 'Transaksi berhasil dihapus']);
    }

    // Tampilan Stuk hasil transaksi 
    private function generateStruk($transaksi, $orderanList)
    {
        $tanggal = Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i');
        $line = str_repeat('-', 42) . "\n";

        $header = $line;
        $header .= str_pad("Caffe Suka Cita", 42, ' ', STR_PAD_BOTH) . "\n";
        $header .= str_pad("Jl. Indonesia Suram No. 2025", 42, ' ', STR_PAD_BOTH) . "\n";
        $header .= str_pad("Telp: +6282-3345-6755", 42, ' ', STR_PAD_BOTH) . "\n";
        $header .= $line;
        $header .= "Tanggal : $tanggal\n";
        $header .= "Pembeli : {$transaksi->nama_pembeli}\n";
        $header .= "Metode  : {$transaksi->pembayaran}\n";
        $header .= $line;

        $body = "Item                             Subtotal\n";
        $body .= $line;

        foreach ($orderanList as $order) {
            $namaMenu = $order->menu->nama_menu ?? '-';
            $namaUkuran = $order->ukuran->nama_ukuran ?? '-';
            $item = "{$namaMenu} ({$namaUkuran})";
            $subtotal = "Rp " . number_format($order->sub_total, 0, ',', '.');

            $body .= str_pad(substr($item, 0, 30), 30) . str_pad($subtotal, 12, ' ', STR_PAD_LEFT) . "\n";

            $topping = $order->topping
                ? "+ {$order->topping} (Rp " . number_format($order->harga_topping, 0, ',', '.') . ")"
                : "+ Tanpa Topping";

            $body .= "  {$topping}\n";
            $body .= "  Qty: {$order->jumlah}\n";
        }

        $body .= $line;

        $footer = str_pad("Total Bayar", 30) . str_pad("Rp " . number_format($transaksi->total_harga, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) . "\n";
        $footer .= str_pad("Nominal", 30) . str_pad("Rp " . number_format($transaksi->nominal, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) . "\n";
        $footer .= str_pad("Kembalian", 30) . str_pad("Rp " . number_format($transaksi->kembalian, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) . "\n";
        $footer .= str_pad("Status", 30) . str_pad(ucfirst($transaksi->status), 12, ' ', STR_PAD_LEFT) . "\n";
        $footer .= $line;
        $footer .= str_pad("Terima kasih telah berbelanja", 42, ' ', STR_PAD_BOTH) . "\n";
        $footer .= str_pad("Kunjungan Anda kami nantikan kembali", 42, ' ', STR_PAD_BOTH) . "\n";
        $footer .= $line;

        return $header . $body . $footer;
    }
}
