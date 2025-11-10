<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use App\Models\JenisBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'aktif');
        $filterJenis = $request->get('jenis', 'all');

        $barang = DB::select("CALL sp_get_master_barang(?, ?)", [$status, $filterJenis]);
        $jenisBarang = DB::table('jenis_barang')->get();
        $satuan = DB::table('satuan')->get();

        return view('barang.index', [
            'barang' => $barang,
            'status' => $status,
            'filterJenis' => $filterJenis,
            'jenisBarang' => $jenisBarang,
            'satuan' => $satuan
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'jenis' => 'required|string|max:1',
            'idsatuan' => 'required|integer',
        ]);

        Barang::create([
            'nama' => $request->nama,
            'jenis' => strtoupper($request->jenis),
            'idsatuan' => $request->idsatuan,
            'status' => 1
        ]);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * Update barang.
     */
    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->update([
            'nama' => $request->nama,
            'jenis' => strtoupper($request->jenis),
            'idsatuan' => $request->idsatuan,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    /**
     * Hapus barang.
     */
    public function destroy($id)
    {
        Barang::findOrFail($id)->delete();
        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus.');
    }

    public function getHarga($id)
    {
        $barang = Barang::find($id);
        return response()->json(['harga' => $barang ? $barang->harga : 0]);
    }

    public function toggleStatus($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->status = $barang->status == 1 ? 0 : 1;
        $barang->save();

        return redirect()->route('barang.index')->with('success', 'Status barang berhasil diperbarui!');
    }
}
