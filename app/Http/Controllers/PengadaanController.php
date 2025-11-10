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
        $pengadaan = DB::table('v_pengadaan_semua')->orderByDesc('idpengadaan')->get();
        $vendors = Vendor::all();
        $barang = Barang::where('status', 1)->get();

        return view('pengadaan.index', compact('pengadaan', 'vendors', 'barang'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'vendor_idvendor' => 'required|exists:vendor,idvendor',
            'items' => 'required|array|min:1',
            'items.*.idbarang' => 'required|exists:barang,idbarang',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.harga' => 'required|numeric|min:1',
        ], [
            'items.required' => '❌ Daftar barang tidak boleh kosong.',
            'items.*.idbarang.required' => '❌ Pilih barang terlebih dahulu.',
            'items.*.jumlah.min' => '❌ Jumlah minimal 1.',
            'items.*.harga.min' => '❌ Harga harus lebih dari 0.'
        ]);

        try {
            $userId = Auth::user()->iduser;
            $vendorId = $request->vendor_idvendor;
            $items = json_encode($request->items);

            $result = DB::select('CALL sp_add_pengadaan_otomatis_fix(?, ?, ?, @out_id)', [
                $userId,
                $vendorId,
                $items
            ]);

            $response = $result[0] ?? null;

            if (!empty($response->idpengadaan)) {
                return redirect()->route('pengadaan.index')
                    ->with('success', $response->message);
            } else {
                return redirect()->route('pengadaan.index')
                    ->with('error', $response->message ?? '❌ Gagal menyimpan pengadaan.');
            }
        } catch (\Exception $e) {
            return redirect()->route('pengadaan.index')
                ->with('error', '❌ Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $detail = DB::table('v_detail_pengadaan')->where('idpengadaan', $id)->get();
        $pengadaan = $detail->first();

        if (!$pengadaan) abort(404, 'Data pengadaan tidak ditemukan');

        return view('pengadaan.show', compact('pengadaan', 'detail'));
    }


    public function edit($id)
    {
        $pengadaan = Pengadaan::with(['vendor', 'details'])->findOrFail($id);
        $vendors = Vendor::all();
        $barang = DB::table('barang')->where('status', 1)->get();

        return view('pengadaan.edit', compact('pengadaan', 'vendors', 'barang'));
    }

    public function update(Request $request, $id)
    {
        try {
            $pengadaan = Pengadaan::findOrFail($id);

            if ($pengadaan->status !== 'P') {
                return back()->with('error', 'Pengadaan tidak dapat diedit karena sudah selesai atau dibatalkan.');
            }

            DB::transaction(function () use ($request, $pengadaan) {
                $items = json_encode($request->items);

                // hapus detail lama
                DB::table('detail_pengadaan')->where('idpengadaan', $pengadaan->idpengadaan)->delete();

                // insert detail baru
                $count = 0;
                $total = 0;
                foreach ($request->items as $item) {
                    $subtotal = $item['jumlah'] * $item['harga'];
                    DB::table('detail_pengadaan')->insert([
                        'idpengadaan' => $pengadaan->idpengadaan,
                        'idbarang' => $item['idbarang'],
                        'jumlah' => $item['jumlah'],
                        'harga_satuan' => $item['harga'],
                        'sub_total' => $subtotal,
                    ]);
                    $total += $subtotal;
                    $count++;
                }

                $pengadaan->update([
                    'vendor_idvendor' => $request->vendor_idvendor,
                    'subtotal_nilai' => $total,
                    'ppn' => round($total * 0.1),
                    'total_nilai' => $total + round($total * 0.1),
                ]);
            });

            return redirect()->route('pengadaan.index')->with('success', 'Pengadaan berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel($id, Request $request)
    {
        $pengadaan = Pengadaan::findOrFail($id);

        if ($pengadaan->status === 'S') {
            return back()->with('error', 'Pengadaan yang sudah selesai tidak bisa dibatalkan.');
        }

        $pengadaan->status = 'B';
        $pengadaan->save();

        DB::table('log_aktivitas')->insert([
            'iduser' => Auth::id(),
            'aktivitas' => 'Pengadaan',
            'aksi' => 'CANCEL',
            'deskripsi' => 'Pembatalan pengadaan #' . $pengadaan->idpengadaan .
                ' oleh ' . Auth::user()->username .
                ' dengan alasan: ' . $request->input('alasan'),
            'waktu' => now(),
        ]);

        return redirect()->route('pengadaan.index')->with('success', 'Pengadaan berhasil dibatalkan.');
    }

    public function create()
    {
        $vendor = Vendor::all();
        $barang = Barang::where('status', 1)->get();

        return view('pengadaan.create', compact('vendor', 'barang'));
    }

    public function getHargaBarang($idbarang)
    {
        try {
            // Ambil harga dari tabel barang
            $barang = DB::table('v_harga_otomatis_barang')->where('idbarang', $idbarang)->first();

            // Jika belum ada harga, ambil dari detail_pengadaan terakhir
            if ($barang && ($barang->harga == 0 || $barang->harga === null)) {
                $hargaTerakhir = DB::table('detail_pengadaan')
                    ->where('idbarang', $idbarang)
                    ->orderByDesc('iddetail_pengadaan')
                    ->value('harga_satuan');

                $barang->harga = $hargaTerakhir ?? 0;
            }

            return response()->json([
                'success' => true,
                'idbarang' => $barang->idbarang ?? null,
                'nama' => $barang->nama ?? '-',
                'harga' => $barang->harga ?? 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil harga barang: ' . $e->getMessage()
            ], 500);
        }
    }
}
