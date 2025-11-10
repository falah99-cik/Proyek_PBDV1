<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Tampilkan daftar vendor (dengan filter status).
     * Default: hanya vendor aktif yang tampil.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'aktif'); // default tampil vendor aktif

        // Ambil dari view v_master_vendor agar tampil nama + status
        $query = DB::table('v_master_vendor');

        if ($status === 'aktif') {
            $query->where('status', 'Aktif');
        }

        $vendors = $query->orderBy('nama_vendor', 'asc')->get();

        return view('vendor.index', [
            'vendors' => $vendors,
            'status' => $status
        ]);
    }

    /**
     * Simpan vendor baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_vendor' => 'required|string|max:100',
            'badan_hukum' => 'required|string|max:1', // P = PT, C = CV, D = UD
        ]);

        Vendor::create([
            'nama_vendor' => $request->nama_vendor,
            'badan_hukum' => strtoupper($request->badan_hukum),
            'status' => 'A', // A = Aktif
        ]);

        return redirect()->route('vendor.index')->with('success', 'Vendor baru berhasil ditambahkan.');
    }

    /**
     * Ubah status aktif/nonaktif vendor.
     */
    public function toggleStatus($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->status = $vendor->status === 'A' ? 'N' : 'A';
        $vendor->save();

        return redirect()->route('vendor.index')->with('success', 'Status vendor berhasil diperbarui.');
    }

    /**
     * Update data vendor.
     */
    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'nama_vendor' => $request->nama_vendor,
            'badan_hukum' => strtoupper($request->badan_hukum),
            'status' => $request->status ?? 'A',
        ]);

        return redirect()->route('vendor.index')->with('success', 'Data vendor berhasil diperbarui.');
    }

    /**
     * Hapus vendor.
     */
    public function destroy($id)
    {
        Vendor::findOrFail($id)->delete();
        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil dihapus.');
    }
}
