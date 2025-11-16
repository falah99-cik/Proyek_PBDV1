<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

    public function getItemsPenerimaan($idpenerimaan)
    {
        try {
            $items = DB::table('detail_penerimaan')
                ->join('barang', 'detail_penerimaan.idbarang', '=', 'barang.idbarang')
                ->join('satuan', 'barang.idsatuan', '=', 'satuan.idsatuan')
                ->where('detail_penerimaan.idpenerimaan', $idpenerimaan)
                ->select(
                    'detail_penerimaan.iddetail_penerimaan',
                    'detail_penerimaan.idbarang',
                    'barang.nama as nama_barang',
                    'barang.stok',
                    'satuan.nama_satuan',
                    'detail_penerimaan.jumlah_terima',
                    'detail_penerimaan.harga_satuan_terima'
                )
                ->get();

            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

            // Insert retur
            $idRetur = DB::table('retur')->insertGetId([
                'idpenerimaan' => $request->idpenerimaan,
                'iduser' => Auth::id(),
                'status' => 'N', // Pending
                'created_at' => now()
            ]);

            // Insert detail dan kurangi stok
            foreach ($request->items as $item) {
                // Validasi stok
                $barang = DB::table('barang')->where('idbarang', $item['idbarang'])->first();

                if ($barang->stok < $item['jumlah']) {
                    throw new \Exception("Stok tidak cukup untuk barang: {$barang->nama}");
                }

                $stokSebelum = $barang->stok;
                $stokSesudah = $stokSebelum - $item['jumlah'];

                // Insert detail retur
                DB::table('detail_retur')->insert([
                    'idretur' => $idRetur,
                    'idbarang' => $item['idbarang'],
                    'jumlah' => $item['jumlah'],
                    'alasan' => $item['alasan'],
                    'iddetail_penerimaan' => $item['iddetail_penerimaan'] ?? null,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'created_at' => now()
                ]);

                // Kurangi stok barang
                DB::table('barang')
                    ->where('idbarang', $item['idbarang'])
                    ->decrement('stok', $item['jumlah']);

                // Insert ke kartu stok
                DB::table('kartu_stok')->insert([
                    'jenis_transaksi' => 'R', // R = Retur
                    'masuk' => 0,
                    'keluar' => $item['jumlah'],
                    'stock' => $stokSesudah,
                    'idtransaksi' => $idRetur,
                    'idbarang' => $item['idbarang'],
                    'created_at' => now()
                ]);
            }

            DB::commit();

            return redirect()->route('retur.index')
                ->with('success', '✅ Retur berhasil dibuat. Stok telah dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
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
                'detail_retur.alasan',
                'detail_retur.stok_sebelum',
                'detail_retur.stok_sesudah',
                'barang.stok as stok_sekarang'
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

    public function getBarangPenerimaan($idpenerimaan)
    {
        try {
            $barang = DB::select('CALL sp_get_barang_untuk_retur(?)', [$idpenerimaan]);

            return response()->json([
                'success' => true,
                'data' => $barang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data barang: ' . $e->getMessage()
            ], 500);
        }
    }
}
