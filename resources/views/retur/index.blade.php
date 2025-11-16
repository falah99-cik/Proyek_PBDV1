@extends('layouts.app')

@section('title', 'Retur ke Vendor')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">🔄 Retur ke Vendor</h1>
        <button onclick="toggleModal(true)" 
            class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition font-semibold shadow-lg">
            + Buat Retur Baru
        </button>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-800 px-4 py-3 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Info Penjelasan --}}
    <div class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-300 rounded-xl p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-red-900 mb-3">ℹ️ Apa itu Retur ke Vendor?</h3>
        <div class="bg-white rounded-lg p-4 border-l-4 border-red-500">
            <div class="flex items-start gap-3">
                <div class="bg-red-100 rounded-full p-2 mt-1">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-red-800 text-lg mb-1">🔴 Retur ke Vendor</p>
                    <p class="text-sm text-gray-700 mb-2">Ketika barang yang diterima dari vendor ada yang <strong>rusak/cacat/tidak sesuai</strong></p>
                    <div class="bg-red-50 rounded p-3 text-sm space-y-1">
                        <p class="text-red-700">📉 Stok akan <strong>OTOMATIS BERKURANG</strong> saat retur dibuat</p>
                        <p class="text-xs text-red-600">✓ Tracking: Stok sebelum & sesudah retur tercatat</p>
                        <p class="text-xs text-red-600">✓ Kartu stok terupdate otomatis</p>
                        <p class="text-xs text-red-600 mt-2">Contoh: Laptop yang diterima layarnya pecah, langsung diretur dan stok dikurangi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Retur --}}
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="bg-gradient-to-r from-red-600 to-orange-600 px-6 py-4">
            <h2 class="text-xl font-semibold text-white">📋 Daftar Retur ke Vendor</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="p-3 text-left font-semibold text-gray-700">No</th>
                        <th class="p-3 text-left font-semibold text-gray-700">ID Retur</th>
                        <th class="p-3 text-left font-semibold text-gray-700">Tanggal</th>
                        <th class="p-3 text-left font-semibold text-gray-700">Vendor</th>
                        <th class="p-3 text-left font-semibold text-gray-700">ID Pengadaan</th>
                        <th class="p-3 text-center font-semibold text-gray-700">Status</th>
                        <th class="p-3 text-left font-semibold text-gray-700">Dibuat Oleh</th>
                        <th class="p-3 text-center font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($retur as $index => $r)
                    <tr class="border-b hover:bg-red-50 transition">
                        <td class="p-3">{{ $index + 1 }}</td>
                        <td class="p-3 font-semibold text-red-700">#{{ $r->idretur }}</td>
                        <td class="p-3">{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="p-3 font-medium">{{ $r->nama_vendor ?? 'N/A' }}</td>
                        <td class="p-3 text-center">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                #{{ $r->idpengadaan ?? '-' }}
                            </span>
                        </td>
                        <td class="p-3 text-center">
                            @if($r->status == 'Y')
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    ✓ Approved
                                </span>
                            @elseif($r->status == 'N')
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    ⏳ Pending
                                </span>
                            @else
                                <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    Unknown
                                </span>
                            @endif
                        </td>
                        <td class="p-3">{{ $r->username ?? 'N/A' }}</td>
                        <td class="p-3 text-center space-x-2">
                            <a href="{{ route('retur.show', $r->idretur) }}" 
                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Detail
                            </a>
                            
                            @if($r->status == 'N')
                            <form action="{{ route('retur.updateStatus', $r->idretur) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" 
                                        onclick="return confirm('Approve retur ini?')"
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Approve
                                </button>
                            </form>

                            <form action="{{ route('retur.destroy', $r->idretur) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('⚠️ Hapus retur ini? Stok akan dikembalikan!')"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-6 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="font-medium">Belum ada data retur</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Retur --}}
    <div id="returModal" 
         class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden shadow-2xl">
            {{-- Header Modal --}}
            <div class="bg-gradient-to-r from-red-600 to-orange-600 px-6 py-5 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-white">📦 Buat Retur ke Vendor</h2>
                <button onclick="toggleModal(false)" class="text-white hover:text-gray-200 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body Modal --}}
            <div class="overflow-y-auto max-h-[calc(90vh-80px)] p-6">
                <form method="POST" action="{{ route('retur.store') }}" id="returForm">
                    @csrf

                    {{-- Step 1: Pilih Penerimaan --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">1</span>
                            <h3 class="text-lg font-semibold text-gray-800">Pilih Penerimaan dari Vendor</h3>
                        </div>
                        
                        <select id="penerimaanSelect" name="idpenerimaan" 
                                class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
                                required>
                            <option value="">-- Pilih Penerimaan --</option>
                            @foreach($penerimaan as $p)
                                <option value="{{ $p->idpenerimaan }}">
                                    #{{ $p->idpenerimaan }} - {{ $p->nama_vendor }} 
                                    ({{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">💡 Pilih penerimaan yang berisi barang rusak/cacat</p>
                    </div>

                    {{-- Step 2: Detail Barang --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">2</span>
                            <h3 class="text-lg font-semibold text-gray-800">Pilih Barang yang Diretur</h3>
                        </div>
                        
                        <div id="returItems" class="space-y-3 bg-gray-50 rounded-lg p-4 min-h-[100px]">
                            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-sm font-medium">Pilih penerimaan terlebih dahulu</p>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button type="button" onclick="toggleModal(false)" 
                                class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-semibold shadow-md hover:shadow-lg">
                            💾 Simpan Retur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Script --}}
<script>
function toggleModal(show) {
    const modal = document.getElementById('returModal');
    modal.classList.toggle('hidden', !show);
    
    if (!show) {
        document.getElementById('returForm').reset();
        document.getElementById('returItems').innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-sm font-medium">Pilih penerimaan terlebih dahulu</p>
            </div>
        `;
    }
}

async function loadBarangPenerimaan(idpenerimaan) {
    try {
        const response = await fetch(`/retur/get-barang-penerimaan/${idpenerimaan}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            // Tampilkan data barang
            return result.data;
        } else {
            throw new Error(result.message || 'Gagal memuat data');
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Gagal memuat data barang: ' + error.message);
        return [];
    }
}

// Auto hide notifikasi
setTimeout(() => {
    const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>

@endsection