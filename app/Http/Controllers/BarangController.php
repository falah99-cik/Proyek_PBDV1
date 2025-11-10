<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'semua');  // default semua
        $filterJenis = $request->get('jenis', 'semua'); // default semua

        // 🔍 Ambil data dari view v_master_barang dengan filter dinamis
        $query = DB::table('v_master_barang');

        if ($status !== 'semua') {
            $query->where('status', ucfirst($status)); // Aktif / Nonaktif
        }

        if ($filterJenis !== 'semua') {
            $query->where('jenis_barang', $filterJenis);
        }

        $barang = $query->orderBy('nama_barang', 'asc')->get();
        $jenisBarang = DB::table('jenis_barang')->pluck('nama_jenis');
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
            'idjenis' => 'nullable|integer',
            'idsatuan' => 'required|integer',
        ]);

        Barang::create([
            'nama' => $request->nama,
            'idjenis' => $request->idjenis,
            'idsatuan' => $request->idsatuan,
            'status' => 1
        ]);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $barang->update([
            'nama' => $request->nama,
            'idjenis' => $request->idjenis,
            'idsatuan' => $request->idsatuan,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

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
