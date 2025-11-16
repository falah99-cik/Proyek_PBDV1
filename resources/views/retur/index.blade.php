@extends('layouts.app')

@section('title', 'Retur Barang')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">📦 Retur Barang</h1>
        <button onclick="toggleModal(true)" 
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg transition font-semibold shadow-lg">
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
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-indigo-200 rounded-xl p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-indigo-900 mb-3">ℹ️ Apa itu Retur Barang?</h3>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg p-4 border-l-4 border-red-500">
                <div class="flex items-start gap-3">
                    <div class="bg-red-100 rounded-full p-2 mt-1">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-red-800 text-lg mb-1">🔴 Retur ke Vendor</p>
                        <p class="text-sm text-gray-700 mb-2">Ketika barang yang diterima dari vendor ada yang <strong>rusak/cacat</strong></p>
                        <div class="bg-red-50 rounded p-2 text-sm">
                            <p class="text-red-700">📉 Stok akan <strong>berkurang</strong></p>
                            <p class="text-xs text-red-600 mt-1">Contoh: Laptop yang diterima layarnya pecah</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 border-l-4 border-blue-500">
                <div class="flex items-start gap-3">
                    <div class="bg-blue-100 rounded-full p-2 mt-1">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-blue-800 text-lg mb-1">🔵 Retur dari Customer</p>
                        <p class="text-sm text-gray-700 mb-2">Ketika customer <strong>mengembalikan barang</strong> yang sudah dibeli</p>
                        <div class="bg-blue-50 rounded p-2 text-sm">
                            <p class="text-blue-700">📈 Stok akan <strong>bertambah</strong> kembali</p>
                            <p class="text-xs text-blue-600 mt-1">Contoh: Customer batal beli, barang dikembalikan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Retur --}}
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
            <h2 class="text-xl font-semibold text-white">📋 Daftar Retur</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="p-3 text-left font-semibold text-gray-700">ID Retur</th>
                        <th class="p-3 text-center font-semibold text-gray-700">Jenis</th>
                        <th class="p-3 text-left font-semibold text-gray-700">Tanggal</th>
                        <th class="p-3 text-left font-semibold text-gray-700">Barang</th>
                        <th class="p-3 text-center font-semibold text-gray-700">Jumlah</th>
                        <th class="p-3 text-left font-semibold text-gray-700">Alasan</th>
                        <th class="p-3 text-center font-semibold text-gray-700">Status</th>
                        <th class="p-3 text-center font-semibold text-gray-700">User</th>
                        <th class="p-3 text-center font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody>
    @forelse($retur as $r)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ date('d/m/Y H:i', strtotime($r->created_at)) }}</td>
        <td>
            @if($r->jenis_retur == 'penerimaan')
                <span class="badge bg-warning">Retur ke Vendor</span>
                <br><small>ID: {{ $r->idpenerimaan ?? '-' }}</small>
                <br><small>{{ $r->nama_vendor ?? 'Vendor tidak ditemukan' }}</small>
            @else
                <span class="badge bg-info">Retur dari Customer</span>
                <br><small>ID: {{ $r->idpenjualan ?? '-' }}</small>
                <br><small>Penjualan #{{ $r->idpenjualan }}</small>
            @endif
        </td>
        <td>{{ $r->username ?? $r->nama_user ?? '-' }}</td> {{-- ✅ PERBAIKAN DI SINI --}}
        <td>
            @if($r->status == 'N')
                <span class="badge bg-secondary">Pending</span>
            @elseif($r->status == 'Y')
                <span class="badge bg-success">Approved</span>
            @else
                <span class="badge bg-danger">Rejected</span>
            @endif
        </td>
        <td>
            <button class="btn btn-sm btn-info" onclick="lihatDetail({{ $r->idretur }})">
                <i class="fas fa-eye"></i> Detail
            </button>
            @if($r->status == 'N')
            <button class="btn btn-sm btn-success" onclick="approve({{ $r->idretur }})">
                <i class="fas fa-check"></i> Approve
            </button>
            @endif
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="text-center">Belum ada data retur</td>
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
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-5 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-white">📦 Buat Retur Barang</h2>
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

                    {{-- Step 1: Pilih Jenis Retur --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="bg-indigo-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">1</span>
                            <h3 class="text-lg font-semibold text-gray-800">Pilih Jenis Retur</h3>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="jenis_retur" value="penerimaan" class="peer sr-only" required>
                                <div class="border-2 border-gray-300 rounded-xl p-5 transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300 hover:shadow-md">
                                    <div class="flex items-start gap-3">
                                        <div class="bg-red-100 peer-checked:bg-red-200 rounded-full p-3 transition-colors">
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-red-800 text-lg">🔴 Retur ke Vendor</p>
                                            <p class="text-sm text-gray-600 mt-1">Barang rusak/cacat dikembalikan ke vendor</p>
                                            <div class="mt-2 text-xs text-red-700 bg-red-100 rounded px-2 py-1 inline-block">
                                                Stok berkurang ↓
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer group">
                                <input type="radio" name="jenis_retur" value="penjualan" class="peer sr-only" required>
                                <div class="border-2 border-gray-300 rounded-xl p-5 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 hover:shadow-md">
                                    <div class="flex items-start gap-3">
                                        <div class="bg-blue-100 peer-checked:bg-blue-200 rounded-full p-3 transition-colors">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-blue-800 text-lg">🔵 Retur dari Customer</p>
                                            <p class="text-sm text-gray-600 mt-1">Customer mengembalikan barang yang dibeli</p>
                                            <div class="mt-2 text-xs text-blue-700 bg-blue-100 rounded px-2 py-1 inline-block">
                                                Stok bertambah ↑
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Step 2: Pilih Transaksi --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="bg-indigo-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">2</span>
                            <h3 class="text-lg font-semibold text-gray-800">Pilih Transaksi Asal</h3>
                        </div>

                        {{-- Dropdown Penerimaan --}}
                        <div id="penerimaanField" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Penerimaan Barang dari Vendor
                                <span class="text-red-500">*</span>
                            </label>
                            <select id="penerimaanSelect" name="idpenerimaan" 
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                <option value="">-- Pilih transaksi penerimaan --</option>
                                @foreach($penerimaan as $p)
                                    <option value="{{ $p->idpenerimaan }}">
                                        #{{ $p->idpenerimaan }} - {{ $p->nama_vendor }} 
                                        ({{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">💡 Pilih penerimaan yang berisi barang rusak/cacat</p>
                        </div>

                        {{-- Dropdown Penjualan --}}
                        <div id="penjualanField" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Penjualan ke Customer
                                <span class="text-red-500">*</span>
                            </label>
                            <select id="penjualanSelect" name="idpenjualan" 
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                <option value="">-- Pilih transaksi penjualan --</option>
                                @foreach($penjualan as $pj)
                                    <option value="{{ $pj->idpenjualan }}">
                                        {{ $pj->idpenjualan }} - {{ $pj->username }} 
                                        ({{ \Carbon\Carbon::parse($pj->created_at)->format('d/m/Y H:i') }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">💡 Pilih penjualan yang barangnya dikembalikan customer</p>
                        </div>
                    </div>

                    {{-- Step 3: Detail Barang --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="bg-indigo-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">3</span>
                            <h3 class="text-lg font-semibold text-gray-800">Tentukan Barang yang Diretur</h3>
                        </div>
                        
                        <div id="returItems" class="space-y-3 bg-gray-50 rounded-lg p-4 min-h-[100px]">
                            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-sm font-medium">Pilih jenis retur & transaksi terlebih dahulu</p>
                                <p class="text-xs mt-1">Atau klik tombol "Tambah Item Manual" di bawah</p>
                            </div>
                        </div>

                        <button type="button" onclick="tambahItemManual()" 
                                class="mt-4 bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold inline-flex items-center gap-2 transition shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Tambah Item Manual
                        </button>
                        <p class="text-xs text-gray-500 mt-2">💡 Gunakan ini jika ingin input ID barang secara manual</p>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button type="button" onclick="toggleModal(false)" 
                                class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-semibold shadow-md hover:shadow-lg">
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
let itemIndex = 0;

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
                <p class="text-sm font-medium">Pilih jenis retur & transaksi terlebih dahulu</p>
                <p class="text-xs mt-1">Atau klik tombol "Tambah Item Manual" di bawah</p>
            </div>
        `;
        document.getElementById('penerimaanField').classList.add('hidden');
        document.getElementById('penjualanField').classList.add('hidden');
        itemIndex = 0;
    }
}

// Toggle field berdasarkan jenis retur
document.querySelectorAll('input[name="jenis_retur"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const jenis = this.value;
        const penerimaanField = document.getElementById('penerimaanField');
        const penjualanField = document.getElementById('penjualanField');
        
        if (jenis === 'penerimaan') {
            penerimaanField.classList.remove('hidden');
            penjualanField.classList.add('hidden');
            document.getElementById('penjualanSelect').value = '';
        } else if (jenis === 'penjualan') {
            penjualanField.classList.remove('hidden');
            penerimaanField.classList.add('hidden');
            document.getElementById('penerimaanSelect').value = '';
        }
        
        // Clear items
        document.getElementById('returItems').innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-sm font-medium">Pilih transaksi untuk melihat detail barang</p>
            </div>
        `;
    });
});

// Load items dari penerimaan
document.getElementById('penerimaanSelect').addEventListener('change', function() {
    const idpenerimaan = this.value;
    const container = document.getElementById('returItems');
    
    if (!idpenerimaan) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-sm font-medium">Pilih transaksi untuk melihat detail barang</p>
            </div>
        `;
        return;
    }

    // Show loading
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center py-8 text-indigo-600">
            <svg class="animate-spin h-10 w-10 mb-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-medium">Memuat data barang...</p>
        </div>
    `;

    fetch(`/retur/get-items-penerimaan/${idpenerimaan}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            container.innerHTML = '';
            
            if (!data || data.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-6 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="font-medium">Tidak ada barang dalam penerimaan ini</p>
                    </div>
                `;
                return;
            }
            
            data.forEach((item, index) => {
                const itemHtml = `
                    <div class="bg-white border-2 border-gray-300 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="flex-1 grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Barang</label>
                                    <input type="text" name="items[${index}][nama_barang]" value="${item.nama_barang || ''}" readonly
                                           class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 text-sm font-semibold">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        Jumlah Retur <span class="text-red-500">*</span>
                                        <span class="text-xs text-gray-400">(Max: ${item.jumlah || 0})</span>
                                    </label>
                                    <input type="number" name="items[${index}][jumlah]" 
                                           min="1" max="${item.jumlah || 1}" value="1" required
                                           class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                                </div>
                            </div>
                            <div class="flex-shrink-0 w-48">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Alasan <span class="text-red-500">*</span></label>
                                <select name="items[${index}][alasan]" required
                                        class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-red-500 text-sm">
                                    <option value="">Pilih</option>
                                    <option value="Barang Rusak">Barang Rusak</option>
                                    <option value="Barang Cacat">Barang Cacat</option>
                                    <option value="Tidak Sesuai Pesanan">Tidak Sesuai Pesanan</option>
                                    <option value="Kadaluarsa">Kadaluarsa</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += itemHtml;
            });
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="text-center py-6 text-red-600">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="font-medium">❌ Gagal memuat data barang</p>
                    <p class="text-sm text-gray-500 mt-1">${error.message}</p>
                </div>
            `;
        });
});

// Load items dari penjualan
document.getElementById('penjualanSelect').addEventListener('change', function() {
    const idpenjualan = this.value;
    if (!idpenjualan) {
        document.getElementById('returItems').innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-sm font-medium">Pilih transaksi untuk melihat detail barang</p>
            </div>
        `;
        return;
    }

    fetch(`/retur/get-items-penjualan/${idpenjualan}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('returItems');
            container.innerHTML = '';
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-6 text-gray-500">
                        <p class="font-medium">Tidak ada barang dalam penjualan ini</p>
                    </div>
                `;
                return;
            }
            
            data.forEach((item, index) => {
                const itemHtml = `
                    <div class="bg-white border-2 border-gray-300 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="flex-1 grid md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Barang</label>
                                    <input type="text" value="${item.nama_barang}" readonly
                                           class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        Jumlah Retur <span class="text-red-500">*</span>
                                        <span class="text-xs text-gray-400">(Max: ${item.jumlah})</span>
                                    </label>
                                    <input type="number" name="items[${index}][jumlah]" 
                                           min="1" max="${item.jumlah}" value="1" required
                                           class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Alasan <span class="text-red-500">*</span></label>
                                <select name="items[${index}][alasan]" required
                                        class="border-2 border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-sm">
                                    <option value="">Pilih</option>
                                    <option value="Tidak Sesuai">Tidak Sesuai</option>
                                    <option value="Berubah Pikiran">Berubah Pikiran</option>
                                    <option value="Salah Beli">Salah Beli</option>
                                    <option value="Barang Rusak Saat Diterima">Barang Rusak Saat Diterima</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += itemHtml;
            });
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('returItems').innerHTML = `
                <div class="text-center py-6 text-red-600">
                    <p class="font-medium">❌ Gagal memuat data barang</p>
                </div>
            `;
        });
});

// Fungsi untuk menambah item manual
function tambahItemManual() {
    const container = document.getElementById('returItems');
    
    // Hapus placeholder jika ada
    const placeholder = container.querySelector('.text-gray-400');
    if (placeholder && placeholder.parentElement) {
        placeholder.parentElement.remove();
    }
    
    const itemHtml = `
        <div class="bg-white border-2 border-green-300 rounded-lg p-4 hover:shadow-md transition" id="manual-item-${itemIndex}">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div class="flex-1 grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ID Barang <span class="text-red-500">*</span></label>
                        <input type="text" name="items[${itemIndex}][idbarang]" required
                               placeholder="Masukkan ID Barang"
                               class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah <span class="text-red-500">*</span></label>
                        <input type="number" name="items[${itemIndex}][jumlah]" 
                               min="1" value="1" required
                               class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alasan <span class="text-red-500">*</span></label>
                        <select name="items[${itemIndex}][alasan]" required
                                class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-green-500 text-sm">
                            <option value="">Pilih Alasan</option>
                            <option value="Barang Rusak">Barang Rusak</option>
                            <option value="Barang Cacat">Barang Cacat</option>
                            <option value="Tidak Sesuai">Tidak Sesuai</option>
                            <option value="Berubah Pikiran">Berubah Pikiran</option>
                            <option value="Salah Beli">Salah Beli</option>
                            <option value="Kadaluarsa">Kadaluarsa</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <button type="button" onclick="hapusItem('manual-item-${itemIndex}')" 
                        class="flex-shrink-0 text-red-600 hover:text-red-800 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    itemIndex++;
}

function hapusItem(id) {
    const item = document.getElementById(id);
    if (item) {
        item.remove();
    }
    
    // Jika tidak ada item lagi, tampilkan placeholder
    const container = document.getElementById('returItems');
    if (container.children.length === 0) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-sm font-medium">Tidak ada item retur</p>
                <p class="text-xs mt-1">Klik "Tambah Item Manual" untuk menambah</p>
            </div>
        `;
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