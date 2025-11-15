@extends('layouts.app')

@section('title', 'Detail Penerimaan')

@section('content')
<div class="max-w-5xl mx-auto bg-white shadow rounded-lg p-6 space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">
            Detail Penerimaan #{{ $header->idpenerimaan }}
        </h1>
        <div class="flex gap-2">
            <a href="{{ route('penerimaan.index') }}" 
               class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded transition">
               ← Kembali
            </a>
            
            {{-- ✅ Tombol Tambah Detail (hanya muncul jika progress < 100%) --}}
            @if($progress < 100)
            <button onclick="toggleModalTambahDetail(true)" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded transition">
                + Tambah Detail Penerimaan
            </button>
            @endif
        </div>
    </div>

    {{-- Informasi Umum --}}
    <div class="grid grid-cols-2 gap-4 text-sm text-gray-700">
        <div>
            <p><strong>Vendor:</strong> {{ $header->nama_vendor ?? '-' }}</p>
            <p><strong>ID Pengadaan:</strong> #{{ $header->idpengadaan ?? '-' }}</p>
        </div>
        <div>
            <p><strong>Tanggal:</strong> {{ $header->created_at ?? '-' }}</p>
            <p><strong>Diterima Oleh:</strong> {{ $header->diterima_oleh ?? '-' }}</p>
        </div>
    </div>

    {{-- ✅ Progress Bar --}}
    <div class="bg-gray-50 p-4 rounded-lg">
        <div class="flex justify-between items-center mb-2">
            <strong class="text-sm">Progress Penerimaan:</strong>
            <span class="text-sm font-semibold {{ $progress >= 100 ? 'text-green-700' : 'text-yellow-700' }}">
                {{ $progress }}%
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="{{ $progress >= 100 ? 'bg-green-600' : 'bg-yellow-500' }} h-3 rounded-full transition-all" 
                 style="width: {{ $progress }}%"></div>
        </div>
    </div>

    <p>
        <strong>Status:</strong>
        <span class="px-2 py-1 text-sm rounded font-medium
            {{ $progress >= 100 
                ? 'bg-green-100 text-green-700' 
                : 'bg-yellow-100 text-yellow-700' }}">
            {{ $progress >= 100 ? 'Selesai' : 'Proses' }}
        </span>
    </p>

    <hr class="my-4">

    {{-- Detail Barang Sudah Diterima --}}
    <h2 class="text-lg font-semibold text-gray-800 mb-2">Barang yang Sudah Diterima</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm border border-gray-300 rounded-lg overflow-hidden">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="p-2 text-left">Nama Barang</th>
                    <th class="p-2 text-center">Jumlah Diterima</th>
                    <th class="p-2 text-center">Harga Satuan</th>
                    <th class="p-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($detail as $index => $d)
                <tr class="{{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-indigo-50 transition">
                    <td class="p-2 border-b border-gray-200">{{ $d->nama_barang ?? '-' }}</td>
                    <td class="p-2 border-b border-gray-200 text-center">
                        {{ number_format($d->jumlah_terima ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="p-2 border-b border-gray-200 text-center">
                        Rp {{ number_format($d->harga_satuan_terima ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="p-2 border-b border-gray-200 text-right font-semibold text-gray-700">
                        Rp {{ number_format($d->sub_total_terima ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500 italic">Belum ada detail barang.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-100 font-semibold text-gray-800">
                <tr>
                    <td colspan="3" class="p-3 text-right border-t border-gray-300">Total</td>
                    <td class="p-3 text-right border-t border-gray-300 text-indigo-700">
                        Rp {{ number_format($total ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ✅ Barang yang Belum Diterima --}}
    @if(count($itemBelumDiterima) > 0)
    <hr class="my-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-2 text-red-700">⚠️ Barang yang Belum Diterima</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm border border-red-300 rounded-lg overflow-hidden">
            <thead class="bg-red-600 text-white">
                <tr>
                    <th class="p-2 text-left">Nama Barang</th>
                    <th class="p-2 text-center">Total Pengadaan</th>
                    <th class="p-2 text-center">Sudah Diterima</th>
                    <th class="p-2 text-center">Sisa Belum Diterima</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemBelumDiterima as $item)
                <tr class="hover:bg-red-50 transition">
                    <td class="p-2 border-b">{{ $item->nama_barang }}</td>
                    <td class="p-2 border-b text-center">{{ $item->jumlah_pengadaan }}</td>
                    <td class="p-2 border-b text-center">{{ $item->sudah_diterima }}</td>
                    <td class="p-2 border-b text-center font-bold text-red-700">{{ $item->sisa }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ✅ Modal Tambah Detail Penerimaan --}}
<div id="modalTambahDetail" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-2/3 p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-lg font-semibold mb-4">Tambah Detail Penerimaan</h2>
        
        <form method="POST" action="{{ route('penerimaan.addDetail', $header->idpenerimaan) }}">
            @csrf
            
            <div id="itemsContainer" class="space-y-3 mb-4">
                @foreach($itemBelumDiterima as $index => $item)
                <div class="flex gap-3 items-end border rounded-lg p-3 bg-gray-50">
                    <input type="hidden" name="items[{{ $index }}][idbarang]" value="{{ $item->idbarang }}">
                    
                    <div class="flex-1">
                        <label class="text-xs text-gray-600">Nama Barang</label>
                        <input type="text" value="{{ $item->nama_barang }}" readonly 
                               class="w-full border rounded px-2 py-1 bg-gray-100">
                    </div>
                    
                    <div class="w-1/4">
                        <label class="text-xs text-gray-600">Jumlah Terima</label>
                        <input type="number" 
                               name="items[{{ $index }}][jumlah_terima]" 
                               max="{{ $item->sisa }}"
                               min="1"
                               placeholder="Maks: {{ $item->sisa }}"
                               class="w-full border rounded px-2 py-1"
                               required>
                        <small class="text-gray-500">Sisa: {{ $item->sisa }}</small>
                    </div>
                    
                    <div class="w-1/4">
                        <label class="text-xs text-gray-600">Harga Satuan</label>
                        <input type="number" 
                               name="items[{{ $index }}][harga_satuan_terima]" 
                               value="{{ $item->harga_satuan }}"
                               class="w-full border rounded px-2 py-1 bg-gray-100"
                               readonly>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex justify-end mt-4 space-x-2">
                <button type="button" onclick="toggleModalTambahDetail(false)" 
                        class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-1 rounded">
                    Batal
                </button>
                <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModalTambahDetail(show) {
    document.getElementById('modalTambahDetail').classList.toggle('hidden', !show);
}
</script>
@endsection