<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // ✅ Tambahkan ini

class ReturController extends Controller
{
    public function index()
    {
        // Ambil data retur
        $retur = DB::table('v_retur_lengkap')->get();

        // ✅ TAMBAHKAN: Ambil data penerimaan untuk dropdown filter (jika ada)
        $penerimaan = DB::table('penerimaan')
            ->join('pengadaan', 'penerimaan.idpengadaan', '=', 'pengadaan.idpengadaan')
            ->join('vendor', 'pengadaan.vendor_idvendor', '=', 'vendor.idvendor')
            ->select(
                'penerimaan.idpenerimaan',
                'penerimaan.created_at',
                'vendor.nama_vendor'
            )
            ->where('penerimaan.status', 'S')
            ->get();

        // ✅ TAMBAHKAN: Ambil data penjualan untuk dropdown filter (jika ada)
        $penjualan = DB::table('penjualan')
            ->select(
                'idpenjualan',
                'created_at',
                'total_nilai'
            )
            ->get();

        return view('retur.index', compact('retur', 'penerimaan', 'penjualan'));
    }

    public function create()
    {
        // Ambil data penerimaan yang sudah selesai
        $penerimaan = DB::table('penerimaan')
            ->join('pengadaan', 'penerimaan.idpengadaan', '=', 'pengadaan.idpengadaan')
            ->join('vendor', 'pengadaan.vendor_idvendor', '=', 'vendor.idvendor')
            ->select(
                'penerimaan.idpenerimaan',
                'penerimaan.created_at',
                'vendor.nama_vendor',
                'pengadaan.total_nilai'
            )
            ->where('penerimaan.status', 'S')
            ->get();

        // Ambil data penjualan
        $penjualan = DB::table('penjualan')
            ->select(
                'idpenjualan',
                'created_at',
                'total_nilai'
            )
            ->get();

        return view('retur.create', compact('penerimaan', 'penjualan'));
    }

    public function getBarangPenerimaan($idpenerimaan)
    {
        $barang = DB::table('detail_penerimaan')
            ->join('barang', 'detail_penerimaan.idbarang', '=', 'barang.idbarang')
            ->join('satuan', 'barang.idsatuan', '=', 'satuan.idsatuan')
            ->select(
                'barang.idbarang',
                'barang.nama as nama_barang',
                'satuan.nama_satuan',
                'detail_penerimaan.jumlah_terima',
                'detail_penerimaan.harga_satuan_terima',
                'detail_penerimaan.iddetail_penerimaan'
            )
            ->where('detail_penerimaan.idpenerimaan', $idpenerimaan)
            ->get();

        return response()->json($barang);
    }

    public function getBarangPenjualan($idpenjualan)
    {
        $barang = DB::table('detail_penjualan')
            ->join('barang', 'detail_penjualan.idbarang', '=', 'barang.idbarang')
            ->join('satuan', 'barang.idsatuan', '=', 'satuan.idsatuan')
            ->select(
                'barang.idbarang',
                'barang.nama as nama_barang',
                'satuan.nama_satuan',
                'detail_penjualan.jumlah',
                'detail_penjualan.harga_satuan'
            )
            ->where('detail_penjualan.idpenjualan', $idpenjualan)
            ->get();

        return response()->json($barang);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $jenisRetur = $request->jenis_retur;
            $idReferensi = $request->id_referensi;
            $idUser = Auth::id(); // ✅ Perbaiki ini dari auth()->user()->id menjadi Auth::id()

            // Jika menggunakan tabel user dengan kolom iduser
            $user = Auth::user();
            $idUser = $user->iduser; // ✅ Sesuaikan dengan kolom di tabel user

            // ✅ Simpan idpenjualan atau idpenerimaan
            $dataRetur = [
                'iduser' => $idUser,
                'jenis_retur' => $jenisRetur,
                'status' => 'N',
                'created_at' => now()
            ];

            // Tentukan field mana yang diisi berdasarkan jenis retur
            if ($jenisRetur === 'penerimaan') {
                $dataRetur['idpenerimaan'] = $idReferensi;
                $dataRetur['idpenjualan'] = null;
            } else {
                $dataRetur['idpenjualan'] = $idReferensi;
                $dataRetur['idpenerimaan'] = null;
            }

            $idRetur = DB::table('retur')->insertGetId($dataRetur);

            // Simpan detail barang
            $barangList = json_decode($request->barang_list, true);

            if (empty($barangList)) {
                throw new \Exception('Tidak ada barang untuk retur');
            }

            foreach ($barangList as $barang) {
                $detailData = [
                    'idretur' => $idRetur,
                    'idbarang' => $barang['idbarang'],
                    'jumlah' => $barang['jumlah'],
                    'alasan' => $barang['alasan'] ?? null,
                    'created_at' => now()
                ];

                // Jika retur penerimaan, simpan juga iddetail_penerimaan
                if ($jenisRetur === 'penerimaan' && isset($barang['iddetail_penerimaan'])) {
                    $detailData['iddetail_penerimaan'] = $barang['iddetail_penerimaan'];
                }

                DB::table('detail_retur')->insert($detailData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data retur berhasil disimpan',
                'idretur' => $idRetur
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error retur: ' . $e->getMessage()); // ✅ Perbaiki ini

            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        // Detail retur
        $retur = DB::table('v_retur_lengkap')
            ->where('idretur', $id)
            ->first();

        // Detail barang retur
        $detailBarang = DB::table('detail_retur')
            ->join('barang', 'detail_retur.idbarang', '=', 'barang.idbarang')
            ->join('satuan', 'barang.idsatuan', '=', 'satuan.idsatuan')
            ->select(
                'barang.nama as nama_barang',
                'satuan.nama_satuan',
                'detail_retur.jumlah',
                'detail_retur.alasan'
            )
            ->where('detail_retur.idretur', $id)
            ->get();

        return view('retur.show', compact('retur', 'detailBarang'));
    }

    public function approve($id)
    {
        try {
            DB::beginTransaction();

            // Update status retur
            DB::table('retur')
                ->where('idretur', $id)
                ->update([
                    'status' => 'Y',
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retur berhasil diapprove'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approve retur: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal approve: ' . $e->getMessage()
            ], 500);
        }
    }
}
