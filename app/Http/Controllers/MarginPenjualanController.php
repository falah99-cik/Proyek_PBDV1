<?php

namespace App\Http\Controllers;

use App\Models\MarginPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarginPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all'); // default tampil semua

        $query = DB::table('margin_penjualan')->select('*');

        // Filter status jika dipilih "aktif"
        if ($status === 'aktif') {
            $query->where('status', 1);
        }

        $margin = $query->orderBy('persen', 'asc')->get();

        return view('margin_penjualan.index', compact('margin', 'status'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'persen' => 'required|numeric|min:0|max:100',
        ]);

        MarginPenjualan::create([
            'persen' => $request->persen,
            'status' => 1
        ]);

        return redirect()->route('margin_penjualan.index')->with('success', 'Margin berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $margin = MarginPenjualan::findOrFail($id);

        $margin->update([
            'persen_margin' => $request->persen_margin,
            'status' => $request->status ?? 1
        ]);

        return redirect()->route('margin_penjualan.index')->with('success', 'Margin berhasil diperbarui!');
    }

    public function destroy($id)
    {
        MarginPenjualan::findOrFail($id)->delete();

        return redirect()->route('margin_penjualan.index')->with('success', 'Margin berhasil dihapus.');
    }

    public function activate($id)
    {
        $margin = MarginPenjualan::findOrFail($id);
        DB::statement('CALL sp_set_margin_aktif(?)', [$id]);

        return redirect()->route('margin_penjualan.index')
            ->with('success', 'Margin ' . $margin->persen_margin . '% telah diaktifkan!');
    }

    public function toggle($id)
    {
        DB::statement('CALL sp_toggle_margin_penjualan(?)', [$id]);
        return redirect()->route('margin_penjualan.index')
            ->with('success', 'Status margin berhasil diperbarui!');
    }
}
