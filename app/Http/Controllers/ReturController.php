<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReturController extends Controller
{
    public function index()
    {
        // Ambil semua retur (hanya dari penerimaan)
        $retur = DB::table('retur')
            ->join('penerimaan', 'retur.idpenerimaan', '=', 'penerimaan.idpenerimaan')
            ->join('pengadaan', 'penerimaan.idpengadaan', '=', 'pengadaan.idpengadaan')
            ->join('vendor', 'pengadaan.vendor_idvendor', '=', 'vendor.idvendor')
            ->join('user', 'retur.iduser', '=', 'user.iduser')
            ->select(
                'retur.*',
                'vendor.nama_vendor',
                'user.username',
                'penerimaan.idpengadaan'
            )
            ->orderByDesc('retur.created_at')
            ->get();

        // Ambil penerimaan yang sudah selesai
        $penerimaan = DB::table('penerimaan')
            ->join('pengadaan', 'penerimaan.idpengadaan', '=', 'pengadaan.idpengadaan')
            ->join('vendor', 'pengadaan.vendor_idvendor', '=', 'vendor.idvendor')
            ->where('penerimaan.status', 'S')
            ->select(
                'penerimaan.idpenerimaan',
                'penerimaan.created_at',
                'vendor.nama_vendor',
                'pengadaan.idpengadaan'
            )
            ->orderByDesc('penerimaan.created_at')
            ->get();

        return view('retur.index', compact('retur', 'penerimaan'));
    }

    // ✅ PERBAIKAN: Method untuk mengambil barang dari penerimaan
    public function getItemsPenerimaan($idpenerimaan)
    {
        try {
            // Validasi penerimaan exists
            $penerimaan = DB::table('penerimaan')
                ->where('idpenerimaan', $idpenerimaan)
                ->first();

            if (!$penerimaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penerimaan tidak ditemukan'
                ], 404);
            }

            // Ambil detail penerimaan dengan info barang lengkap
            $items = DB::table('detail_penerimaan as dp')
                ->join('barang as b', 'dp.idbarang', '=', 'b.idbarang')
                ->join('satuan as s', 'b.idsatuan', '=', 's.idsatuan')
                ->where('dp.idpenerimaan', $idpenerimaan)
                ->select(
                    'dp.iddetail_penerimaan',
                    'dp.idbarang',
                    'b.nama as nama_barang',
                    's.nama_satuan',
                    'dp.jumlah_terima',
                    'dp.harga_satuan_terima',
                    // ✅ Hitung stok dari fn_hitung_stok_barang
                    DB::raw('fn_hitung_stok_barang(b.idbarang) as stok')
                )
                ->get();

            // ✅ Cek apakah ada item yang bisa diretur
            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada barang dalam penerimaan ini'
                ], 404);
            }

            // ✅ Filter hanya item yang stoknya > 0
            $filteredItems = $items->filter(function ($item) {
                return $item->stok > 0;
            });

            if ($filteredItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada barang yang bisa diretur (stok habis)'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $filteredItems->values()->all() // Reset array index
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    // ✅ Alias untuk backward compatibility
    public function getBarangPenerimaan($idpenerimaan)
    {
        return $this->getItemsPenerimaan($idpenerimaan);
    }

    public function store(Request $request)
    {
        $request->validate([
            'idpenerimaan' => 'required|exists:penerimaan,idpenerimaan',
            'items' => 'required|array|min:1',
            'items.*.idbarang' => 'required|exists:barang,idbarang',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.alasan' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // ✅ Validasi penerimaan exists
            $penerimaan = DB::table('penerimaan')
                ->where('idpenerimaan', $request->idpenerimaan)
                ->first();

            if (!$penerimaan) {
                return back()->with('error', 'Penerimaan tidak ditemukan');
            }

            // ✅ INSERT RETUR - HANYA kolom yang ADA di database
            $idRetur = DB::table('retur')->insertGetId([
                'idpenerimaan' => $request->idpenerimaan,
                'iduser' => Auth::id(),
                'status' => 'N', // Pending
                'created_at' => now()
            ]);

            // Insert detail dan proses stok
            foreach ($request->items as $item) {
                // ✅ Validasi stok menggunakan kartu_stok
                $stokData = DB::selectOne("
                SELECT 
                    COALESCE(SUM(masuk), 0) - COALESCE(SUM(keluar), 0) as stok_aktual
                FROM kartu_stok
                WHERE idbarang = ?
            ", [$item['idbarang']]);

                $stokAktual = $stokData ? $stokData->stok_aktual : 0;

                if ($stokAktual < $item['jumlah']) {
                    DB::rollBack();
                    $barang = DB::table('barang')->where('idbarang', $item['idbarang'])->first();
                    return back()->with('error', "❌ Stok tidak cukup untuk: {$barang->nama}. Stok tersedia: {$stokAktual}");
                }

                // Insert detail retur
                DB::table('detail_retur')->insert([
                    'idretur' => $idRetur,
                    'idbarang' => $item['idbarang'],
                    'jumlah' => $item['jumlah'],
                    'alasan' => $item['alasan'],
                    'iddetail_penerimaan' => $item['iddetail_penerimaan'] ?? null
                ]);

                // ✅ Kartu stok akan otomatis diupdate oleh trigger
                // Trigger: trg_after_insert_detail_retur
            }

            DB::commit();

            return redirect()->route('retur.index')
                ->with('success', '✅ Retur berhasil dibuat. Stok telah dikurangi otomatis.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Retur Store Error: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return redirect()->back()
                ->with('error', '❌ Gagal: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        // Ambil header retur
        $retur = DB::table('retur')
            ->join('penerimaan', 'retur.idpenerimaan', '=', 'penerimaan.idpenerimaan')
            ->join('pengadaan', 'penerimaan.idpengadaan', '=', 'pengadaan.idpengadaan')
            ->join('vendor', 'pengadaan.vendor_idvendor', '=', 'vendor.idvendor')
            ->join('user', 'retur.iduser', '=', 'user.iduser')
            ->where('retur.idretur', $id)
            ->select(
                'retur.*',
                'vendor.nama_vendor',
                'user.username',
                'penerimaan.idpengadaan'
            )
            ->first();

        if (!$retur) {
            return redirect()->route('retur.index')
                ->with('error', 'Retur tidak ditemukan');
        }

        // Ambil detail barang
        $details = DB::table('detail_retur')
            ->join('barang', 'detail_retur.idbarang', '=', 'barang.idbarang')
            ->join('satuan', 'barang.idsatuan', '=', 'satuan.idsatuan')
            ->where('detail_retur.idretur', $id)
            ->select(
                'barang.nama as nama_barang',
                'satuan.nama_satuan',
                'detail_retur.jumlah',
                'detail_retur.alasan'
            )
            ->get();

        return view('retur.show', compact('retur', 'details'));
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $retur = DB::table('retur')->where('idretur', $id)->first();

            if (!$retur) {
                throw new \Exception('Retur tidak ditemukan');
            }

            if ($retur->status == 'Y') {
                throw new \Exception('Retur sudah diapprove sebelumnya');
            }

            DB::table('retur')
                ->where('idretur', $id)
                ->update([
                    'status' => 'Y',
                    'updated_at' => now()
                ]);

            DB::commit();

            return redirect()->back()
                ->with('success', '✅ Retur berhasil diapprove!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', '❌ ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $retur = DB::table('retur')->where('idretur', $id)->first();

            if (!$retur) {
                throw new \Exception('Retur tidak ditemukan');
            }

            if ($retur->status == 'Y') {
                throw new \Exception('Tidak bisa menghapus retur yang sudah diapprove');
            }

            // Kembalikan stok yang sudah dikurangi
            $details = DB::table('detail_retur')->where('idretur', $id)->get();

            foreach ($details as $detail) {
                DB::table('barang')
                    ->where('idbarang', $detail->idbarang)
                    ->increment('stok', $detail->jumlah);

                // Hapus kartu stok terkait
                DB::table('kartu_stok')
                    ->where('idtransaksi', $id)
                    ->where('jenis_transaksi', 'R')
                    ->where('idbarang', $detail->idbarang)
                    ->delete();
            }

            // Hapus detail dan retur
            DB::table('detail_retur')->where('idretur', $id)->delete();
            DB::table('retur')->where('idretur', $id)->delete();

            DB::commit();

            return redirect()->route('retur.index')
                ->with('success', '✅ Retur berhasil dihapus dan stok dikembalikan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', '❌ ' . $e->getMessage());
        }
    }
}
