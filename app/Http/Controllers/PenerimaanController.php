<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenerimaanController extends Controller
{
    public function index()
    {
        return view('penerimaan.index', [
            'penerimaan' => DB::table('v_penerimaan_semua')->orderByDesc('created_at')->get(),
            'pengadaan'  => DB::table('v_pengadaan_terbuka')->get(),
            'barangs'    => DB::table('v_barang_aktif')->get()
        ]);
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
        $header = DB::table('v_detail_penerimaan_header')->where('idpenerimaan', $id)->first();

        if (!$header) {
            abort(404);
        }

        $detail = DB::table('v_detail_penerimaan_barang')->where('idpenerimaan', $id)->get();
        $itemBelumDiterima = DB::select("CALL sp_get_item_belum_diterima(?)", [$header->idpengadaan]);
        $progress = DB::selectOne("CALL sp_get_progress_penerimaan(?)", [$header->idpengadaan]);

        return view('penerimaan.show', [
            'header'            => $header,
            'detail'            => $detail,
            'itemBelumDiterima' => $itemBelumDiterima,
            'progress'          => $progress
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

            DB::statement("CALL sp_add_penerimaan_fix(?, ?, ?, @out)", [
                $request->idpengadaan,
                Auth::user()->iduser,
                json_encode($request->items)
            ]);

            $id = DB::selectOne("SELECT @out AS idpenerimaan")->idpenerimaan;

            DB::commit();

            return redirect()->route('penerimaan.show', $id)
                ->with('success', 'Penerimaan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function getBarangByPengadaan($id)
    {
        return response()->json(DB::select("CALL sp_get_barang_pengadaan(?)", [$id]));
    }

    public function addDetail(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array|min:1'
        ]);

        try {
            DB::beginTransaction();

            DB::statement("CALL sp_add_detail_penerimaan(?, ?)", [
                $id,
                json_encode($request->items)
            ]);

            DB::commit();

            return redirect()->route('penerimaan.show', $id)
                ->with('success', 'Detail berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
