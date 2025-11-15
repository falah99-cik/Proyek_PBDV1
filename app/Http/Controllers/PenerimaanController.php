<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenerimaanController extends Controller
{
    public function index()
    {
        $penerimaan = DB::table('v_penerimaan_semua')
            ->orderByDesc('created_at')
            ->get();

        $pengadaan = DB::table('v_pengadaan_terbuka')->get();
        $barangs   = DB::table('v_barang_aktif')->get();

        return view('penerimaan.index', compact('penerimaan', 'pengadaan', 'barangs'));
    }

    public function create()
    {
        return view('penerimaan.create', [
            'pengadaan' => DB::table('v_pengadaan_terbuka')->get(),
            'barangs'   => DB::table('v_barang_aktif')->get()
        ]);
    }

    public function show($id)
    {
        $header = DB::table('v_detail_penerimaan_header')
            ->where('idpenerimaan', $id)
            ->first();

        if (!$header) abort(404);

        $detail = DB::table('v_detail_penerimaan_barang')
            ->where('idpenerimaan', $id)
            ->get();

        $total = $detail->sum(fn($d) => (float) $d->sub_total_terima);

        // ✅ Cek item yang belum diterima dari pengadaan
        $itemBelumDiterima = DB::select("
            SELECT 
                dp.idbarang,
                b.nama AS nama_barang,
                dp.jumlah AS jumlah_pengadaan,
                COALESCE(SUM(dpr.jumlah_terima), 0) AS sudah_diterima,
                dp.jumlah - COALESCE(SUM(dpr.jumlah_terima), 0) AS sisa,
                dp.harga_satuan
            FROM detail_pengadaan dp
            JOIN barang b ON b.idbarang = dp.idbarang
            LEFT JOIN detail_penerimaan dpr ON dpr.idbarang = dp.idbarang
                AND dpr.idpenerimaan = ?
            WHERE dp.idpengadaan = ?
            GROUP BY dp.idbarang, b.nama, dp.jumlah, dp.harga_satuan
            HAVING sisa > 0
        ", [$id, $header->idpengadaan]);

        // ✅ Hitung progress keseluruhan pengadaan
        $progressData = DB::selectOne("
            SELECT 
                COALESCE(SUM(dp.jumlah), 0) AS total_pengadaan,
                COALESCE((
                    SELECT SUM(dpr.jumlah_terima)
                    FROM detail_penerimaan dpr
                    JOIN penerimaan pr ON pr.idpenerimaan = dpr.idpenerimaan
                    WHERE pr.idpengadaan = ?
                ), 0) AS total_diterima
            FROM detail_pengadaan dp
            WHERE dp.idpengadaan = ?
        ", [$header->idpengadaan, $header->idpengadaan]);

        $progress = $progressData->total_pengadaan > 0
            ? round(($progressData->total_diterima / $progressData->total_pengadaan) * 100)
            : 0;

        return view('penerimaan.show', [
            'header' => $header,
            'detail' => $detail,
            'total'  => $total,
            'itemBelumDiterima' => $itemBelumDiterima,
            'progress' => $progress,
            'title'  => "Detail Penerimaan #{$header->idpenerimaan}"
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'idpengadaan' => 'required|integer',
            'items'       => 'required|array|min:1'
        ]);

        try {
            DB::beginTransaction();

            $idUser      = Auth::user()->iduser;
            $idPengadaan = $request->idpengadaan;
            $items       = json_encode($request->items);

            // ✅ Cek apakah sudah ada penerimaan untuk pengadaan ini
            $penerimaanExists = DB::table('penerimaan')
                ->where('idpengadaan', $idPengadaan)
                ->orderByDesc('idpenerimaan')
                ->first();

            if ($penerimaanExists && $penerimaanExists->status != 'S') {
                // ✅ Jika sudah ada dan belum selesai, tambah ke detail yang ada
                $idpenerimaan = $penerimaanExists->idpenerimaan;

                // Insert detail baru
                foreach ($request->items as $item) {
                    $subtotal = $item['jumlah_terima'] * $item['harga_satuan_terima'];

                    DB::table('detail_penerimaan')->insert([
                        'idpenerimaan' => $idpenerimaan,
                        'idbarang' => $item['idbarang'],
                        'jumlah_terima' => $item['jumlah_terima'],
                        'harga_satuan_terima' => $item['harga_satuan_terima'],
                        'sub_total_terima' => $subtotal
                    ]);

                    // Update kartu stok
                    $stokAkhir = DB::table('kartu_stok')
                        ->where('idbarang', $item['idbarang'])
                        ->orderByDesc('idkartu_stok')
                        ->value('stock') ?? 0;

                    $stokAkhir += $item['jumlah_terima'];

                    DB::table('kartu_stok')->insert([
                        'jenis_transaksi' => 'M',
                        'masuk' => $item['jumlah_terima'],
                        'keluar' => 0,
                        'stock' => $stokAkhir,
                        'idtransaksi' => $idpenerimaan,
                        'idbarang' => $item['idbarang'],
                        'created_at' => now()
                    ]);
                }
            } else {
                // ✅ Jika belum ada, buat penerimaan baru dengan stored procedure
                DB::statement("CALL sp_add_penerimaan_fix(?, ?, ?, @out_id)", [
                    $idPengadaan,
                    $idUser,
                    $items
                ]);

                $out = DB::selectOne("SELECT @out_id AS idpenerimaan");
                $idpenerimaan = $out->idpenerimaan ?? null;
            }

            // ✅ Update status berdasarkan kelengkapan
            $this->updateStatusPenerimaan($idPengadaan);

            DB::commit();

            if (!$idpenerimaan) {
                return back()->with('error', 'Gagal menyimpan penerimaan.');
            }

            return redirect()
                ->route('penerimaan.show', $idpenerimaan)
                ->with('success', 'Penerimaan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Kesalahan: " . $e->getMessage());
        }
    }

    // ✅ Method untuk update status otomatis
    private function updateStatusPenerimaan($idPengadaan)
    {
        $data = DB::selectOne("
            SELECT 
                COALESCE(SUM(dp.jumlah), 0) AS total_pengadaan,
                COALESCE((
                    SELECT SUM(dpr.jumlah_terima)
                    FROM detail_penerimaan dpr
                    JOIN penerimaan pr ON pr.idpenerimaan = dpr.idpenerimaan
                    WHERE pr.idpengadaan = ?
                ), 0) AS total_diterima
            FROM detail_pengadaan dp
            WHERE dp.idpengadaan = ?
        ", [$idPengadaan, $idPengadaan]);

        if ($data->total_diterima >= $data->total_pengadaan && $data->total_pengadaan > 0) {
            // Semua sudah diterima
            DB::table('penerimaan')
                ->where('idpengadaan', $idPengadaan)
                ->update(['status' => 'S']);

            DB::table('pengadaan')
                ->where('idpengadaan', $idPengadaan)
                ->update(['status' => 'S']);
        } else {
            // Masih ada yang belum diterima
            DB::table('penerimaan')
                ->where('idpengadaan', $idPengadaan)
                ->where('status', '!=', 'S')
                ->update(['status' => 'P']);
        }
    }

    public function getBarangByPengadaan($id)
    {
        try {
            $data = DB::select("CALL sp_get_barang_pengadaan(?)", [$id]);

            $mapped = array_map(function ($row) {
                return [
                    'idbarang'          => (int) $row->idbarang,
                    'nama_barang'       => $row->nama_barang,
                    'harga'             => (int) $row->harga,
                    'jumlah_pengadaan'  => (int) $row->jumlah_pengadaan,
                    'jumlah_diterima'   => (int) $row->jumlah_diterima,
                    'sisa'              => (int) $row->sisa,
                ];
            }, $data);

            return response()->json($mapped, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }

    // ✅ Method baru untuk tambah detail penerimaan
    public function addDetail(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array|min:1'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->items as $item) {
                $subtotal = $item['jumlah_terima'] * $item['harga_satuan_terima'];

                DB::table('detail_penerimaan')->insert([
                    'idpenerimaan' => $id,
                    'idbarang' => $item['idbarang'],
                    'jumlah_terima' => $item['jumlah_terima'],
                    'harga_satuan_terima' => $item['harga_satuan_terima'],
                    'sub_total_terima' => $subtotal
                ]);

                // Update kartu stok
                $stokAkhir = DB::table('kartu_stok')
                    ->where('idbarang', $item['idbarang'])
                    ->orderByDesc('idkartu_stok')
                    ->value('stock') ?? 0;

                $stokAkhir += $item['jumlah_terima'];

                DB::table('kartu_stok')->insert([
                    'jenis_transaksi' => 'M',
                    'masuk' => $item['jumlah_terima'],
                    'keluar' => 0,
                    'stock' => $stokAkhir,
                    'idtransaksi' => $id,
                    'idbarang' => $item['idbarang'],
                    'created_at' => now()
                ]);
            }

            // Update status
            $penerimaan = DB::table('penerimaan')->find($id);
            $this->updateStatusPenerimaan($penerimaan->idpengadaan);

            DB::commit();

            return redirect()
                ->route('penerimaan.show', $id)
                ->with('success', 'Detail penerimaan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Kesalahan: " . $e->getMessage());
        }
    }
}
