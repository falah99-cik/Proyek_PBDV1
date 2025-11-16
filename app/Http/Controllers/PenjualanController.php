<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenjualanController extends Controller
{
    public function index()
    {
        $margin = DB::table('margin_penjualan')->where('status', 1)->first();

        // ✅ PERBAIKAN: Ambil hanya barang yang stoknya > 0
        $barang = DB::table('v_harga_jual_dengan_ppn')
            ->where('stok_aktual', '>', 0) // Hanya barang dengan stok tersedia
            ->orderBy('nama_barang', 'asc')
            ->get();

        $penjualan = DB::table('penjualan')
            ->join('user', 'penjualan.iduser', '=', 'user.iduser')
            ->select('penjualan.*', 'user.username')
            ->orderByDesc('idpenjualan')
            ->get();

        return view('penjualan.index', compact('margin', 'barang', 'penjualan'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $idUser = Auth::id();
            $idMargin = DB::table('margin_penjualan')->where('status', 1)->value('idmargin_penjualan');

            // ✅ Validasi stok sebelum menyimpan
            foreach ($request->barang as $b) {
                $stok = DB::table('v_harga_jual_dengan_ppn')
                    ->where('idbarang', $b['idbarang'])
                    ->value('stok_aktual');

                if ($stok < $b['jumlah']) {
                    DB::rollBack();
                    return back()->with('error', "Stok tidak cukup untuk barang ID: {$b['idbarang']}. Stok tersedia: {$stok}");
                }
            }

            // Insert penjualan utama
            $idPenjualan = DB::table('penjualan')->insertGetId([
                'iduser' => $idUser,
                'idmargin_penjualan' => $idMargin,
                'created_at' => now(),
                'subtotal_nilai' => 0,
                'ppn' => 0,
                'total_nilai' => 0,
            ]);

            $subtotal = 0;

            // Simpan detail barang
            foreach ($request->barang as $b) {
                $harga = DB::selectOne('SELECT fn_harga_jual_barang(?) AS harga', [$b['idbarang']])->harga ?? 0;
                $jumlah = (int) $b['jumlah'];
                $sub = $harga * $jumlah;

                DB::table('detail_penjualan')->insert([
                    'idpenjualan' => $idPenjualan,
                    'idbarang' => $b['idbarang'],
                    'jumlah' => $jumlah,
                    'harga_satuan' => $harga,
                    'subtotal' => $sub,
                ]);

                $subtotal += $sub;
            }

            // Hitung total dan PPN otomatis dari DB
            $ppnPersen = DB::selectOne('SELECT fn_get_ppn_aktif() AS ppn')->ppn;
            $ppn = $subtotal * ($ppnPersen / 100);
            $total = $subtotal + $ppn;

            DB::table('penjualan')->where('idpenjualan', $idPenjualan)->update([
                'subtotal_nilai' => $subtotal,
                'ppn' => $ppn,
                'total_nilai' => $total,
            ]);

            DB::commit();
            return redirect()->route('penjualan.index')->with('success', 'Transaksi penjualan berhasil disimpan!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan penjualan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $penjualan = DB::table('penjualan')
            ->join('user', 'penjualan.iduser', '=', 'user.iduser')
            ->select('penjualan.*', 'user.username')
            ->where('penjualan.idpenjualan', $id)
            ->first();

        $detail = DB::table('v_penjualan_detail_otomatis')
            ->where('idpenjualan', $id)
            ->get();

        if (!$penjualan) {
            return redirect()->route('penjualan.index')->with('error', 'Data penjualan tidak ditemukan.');
        }

        return view('penjualan.show', compact('penjualan', 'detail'));
    }
}
