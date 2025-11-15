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

        return view('penerimaan.show', [
            'header' => $header,
            'detail' => $detail,
            'total'  => $total,
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

            DB::statement("CALL sp_add_penerimaan_fix(?, ?, ?, @out_id)", [
                $idPengadaan,
                $idUser,
                $items
            ]);

            $out = DB::selectOne("SELECT @out_id AS idpenerimaan");
            $idpenerimaan = $out->idpenerimaan ?? null;

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
}
