<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Pengadaan;
use App\Models\Vendor;
use App\Models\Barang;

class PengadaanController extends Controller
{
    public function index()
    {
        return view('pengadaan.index', [
            'pengadaan' => DB::table('v_pengadaan_semua')->orderByDesc('idpengadaan')->get(),
            'vendors'   => Vendor::all(),
            'barang'    => Barang::where('status', 1)->get()
        ]);
    }

    public function create()
    {
        $vendor = Vendor::all();
        $barang = Barang::where('status', 1)->get();

        return view('pengadaan.create', compact('vendor', 'barang'));
    }

    public function store(Request $request)
    {
        if (!$request->items || count($request->items) === 0) {
            return back()->with('error', 'Item tidak boleh kosong.');
        }

        $items = json_encode($request->items);

        DB::statement("CALL sp_add_pengadaan_lengkap(?, ?, ?, @idpengadaan)", [
            Auth::id(),
            $request->vendor_idvendor,
            $items
        ]);

        $out = DB::selectOne("SELECT @idpengadaan AS id");
        $id  = $out->id ?? null;

        if (!$id) {
            return back()->with('error', 'Pengadaan gagal dibuat.');
        }

        DB::table('pengadaan')
            ->where('idpengadaan', $id)
            ->update(['status' => 'P']);

        return redirect()->route('pengadaan.show', $id)
            ->with('success', 'Pengadaan berhasil dibuat.');
    }

    public function show($id)
    {
        $header = DB::table('v_detail_pengadaan')->where('idpengadaan', $id)->first();
        if (!$header) abort(404);

        $detail = DB::table('v_detail_pengadaan')->where('idpengadaan', $id)->get();

        return view('pengadaan.show', compact('header', 'detail'));
    }

    public function edit($id)
    {
        return view('pengadaan.edit', [
            'pengadaan' => Pengadaan::with(['vendor', 'details'])->findOrFail($id),
            'vendors'   => Vendor::all(),
            'barang'    => Barang::where('status', 1)->get()
        ]);
    }

    public function update(Request $request, $id)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        if ($pengadaan->status !== 'P') {
            return back()->with('error', 'Pengadaan tidak dapat diedit lagi.');
        }

        try {
            DB::transaction(function () use ($request, $pengadaan) {

                DB::table('detail_pengadaan')
                    ->where('idpengadaan', $pengadaan->idpengadaan)
                    ->delete();

                $total = 0;

                foreach ($request->items as $item) {
                    $subtotal = $item['jumlah'] * $item['harga'];

                    DB::table('detail_pengadaan')->insert([
                        'idpengadaan'  => $pengadaan->idpengadaan,
                        'idbarang'     => $item['idbarang'],
                        'jumlah'       => $item['jumlah'],
                        'harga_satuan' => $item['harga'],
                        'sub_total'    => $subtotal
                    ]);

                    $total += $subtotal;
                }

                $ppn = round($total * 0.1);

                $pengadaan->update([
                    'vendor_idvendor' => $request->vendor_idvendor,
                    'subtotal_nilai'  => $total,
                    'ppn'             => $ppn,
                    'total_nilai'     => $total + $ppn
                ]);
            });

            return redirect()->route('pengadaan.index')
                ->with('success', 'Pengadaan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel($id, Request $request)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        if ($pengadaan->status === 'S') {
            return back()->with('error', 'Tidak bisa membatalkan pengadaan selesai.');
        }

        $pengadaan->update(['status' => 'B']);

        return redirect()->route('pengadaan.index')
            ->with('success', 'Pengadaan berhasil dibatalkan.');
    }

    public function getHargaBarang($idbarang)
    {
        $harga = DB::table('barang')
            ->where('idbarang', $idbarang)
            ->value('harga');

        if (!$harga) {
            $harga = DB::table('detail_pengadaan')
                ->where('idbarang', $idbarang)
                ->orderByDesc('iddetail_pengadaan')
                ->value('harga_satuan') ?? 0;
        }

        return response()->json(['harga' => $harga]);
    }
}
