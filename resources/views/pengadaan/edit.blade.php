@extends('layouts.app')

@section('title', 'Edit Pengadaan')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    
    {{-- Header Section --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">✏️ Edit Pengadaan</h1>
            <p class="text-sm text-gray-600 mt-1">Pengadaan #{{ $pengadaan->idpengadaan }}</p>
        </div>
        <a href="{{ route('pengadaan.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- Alert Messages --}}
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded relative" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded relative" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Status Warning --}}
    @if($pengadaan->status !== 'P')
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 px-4 py-3 rounded relative">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">Pengadaan ini tidak dapat diedit karena status sudah 
                    <strong>{{ $pengadaan->status === 'S' ? 'Selesai' : 'Dibatalkan' }}</strong>
                </span>
            </div>
        </div>
    @endif

    <form action="{{ route('pengadaan.update', $pengadaan->idpengadaan) }}" 
          method="POST" 
          id="formEditPengadaan"
          class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Info Card --}}
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-xl p-6 shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- Vendor Selection --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Vendor / Supplier
                    </label>
                    <select name="vendor_idvendor" 
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            {{ $pengadaan->status !== 'P' ? 'disabled' : '' }}
                            required>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->idvendor }}" 
                                    {{ $pengadaan->vendor_idvendor == $v->idvendor ? 'selected' : '' }}>
                                {{ $v->nama_vendor }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Tanggal Pengadaan
                    </label>
                    <input type="text" 
                           value="{{ \Carbon\Carbon::parse($pengadaan->timestamp)->format('d M Y, H:i') }}"
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 bg-gray-100 text-gray-700"
                           readonly>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Status
                    </label>
                    <div class="flex items-center gap-2 mt-2.5">
                        @if($pengadaan->status == 'P')
                            <span class="px-4 py-2 rounded-lg bg-yellow-100 text-yellow-800 font-semibold text-sm">
                                ⏳ Sedang Diproses
                            </span>
                        @elseif($pengadaan->status == 'S')
                            <span class="px-4 py-2 rounded-lg bg-green-100 text-green-800 font-semibold text-sm">
                                ✓ Selesai
                            </span>
                        @else
                            <span class="px-4 py-2 rounded-lg bg-red-100 text-red-800 font-semibold text-sm">
                                ✗ Dibatalkan
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Barang --}}
        <div class="bg-white shadow-xl rounded-xl overflow-hidden">
            
            {{-- Header Table --}}
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Daftar Barang
                    </h3>
                    @if($pengadaan->status === 'P')
                        <button type="button" 
                                id="btnTambahBarang"
                                class="bg-white hover:bg-gray-100 text-indigo-600 px-4 py-2 rounded-lg font-semibold transition flex items-center gap-2 shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Tambah Barang
                        </button>
                    @endif
                </div>
            </div>

            {{-- Table Content --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">No</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama Barang</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700">Jumlah</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700">Harga Satuan</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">Subtotal</th>
                            @if($pengadaan->status === 'P')
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="barangList">
                        @foreach ($pengadaan->details as $index => $d)
                            <tr class="barang-item border-b hover:bg-indigo-50 transition">
                                <td class="px-4 py-3 text-gray-600">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    @if($pengadaan->status === 'P')
                                        <select name="items[{{ $index }}][idbarang]" 
                                                class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 select-barang"
                                                required>
                                            @foreach ($barang as $b)
                                                <option value="{{ $b->idbarang }}" 
                                                        {{ $b->idbarang == $d->idbarang ? 'selected' : '' }}>
                                                    {{ $b->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <span class="font-medium text-gray-800">{{ $d->barang->nama }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" 
                                           name="items[{{ $index }}][jumlah]" 
                                           value="{{ $d->jumlah }}"
                                           min="1"
                                           class="w-24 border-2 border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-indigo-500 input-jumlah"
                                           {{ $pengadaan->status !== 'P' ? 'readonly' : '' }}
                                           required>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" 
                                           name="items[{{ $index }}][harga_satuan]" 
                                           value="{{ $d->harga_satuan }}"
                                           min="0"
                                           class="w-32 border-2 border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-indigo-500 input-harga"
                                           {{ $pengadaan->status !== 'P' ? 'readonly' : '' }}
                                           required>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="subtotal-item font-bold text-indigo-700">
                                        Rp {{ number_format($d->jumlah * $d->harga_satuan, 0, ',', '.') }}
                                    </span>
                                </td>
                                @if($pengadaan->status === 'P')
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" 
                                                class="btn-hapus bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary Section --}}
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-t-2 border-gray-200">
                <div class="max-w-md ml-auto space-y-2">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span id="displaySubtotal" class="font-semibold text-gray-800">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">PPN (10%):</span>
                        <span id="displayPPN" class="font-semibold text-gray-800">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t-2 border-gray-300">
                        <span class="font-bold text-gray-800">TOTAL:</span>
                        <span id="displayTotal" class="text-2xl font-bold text-indigo-700">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        @if($pengadaan->status === 'P')
            <div class="flex justify-between items-center gap-4">
                
                {{-- Cancel Procurement Button --}}
                <button type="button"
                        onclick="openCancelModal()"
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center gap-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batalkan Pengadaan
                </button>

                {{-- Save Changes Button --}}
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center gap-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        @endif
    </form>

</div>

{{-- Modal Cancel Procurement --}}
<div id="cancelModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl w-1/3 p-6 shadow-2xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-red-100 p-3 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Batalkan Pengadaan</h2>
                <p class="text-sm text-gray-600">Tindakan ini tidak dapat dibatalkan</p>
            </div>
        </div>

        <form action="{{ route('pengadaan.cancel', $pengadaan->idpengadaan) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Alasan Pembatalan</label>
                <textarea name="alasan" 
                          rows="4"
                          class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                          placeholder="Jelaskan alasan pembatalan pengadaan ini..."
                          required></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" 
                        onclick="closeCancelModal()"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    Batal
                </button>
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                    Ya, Batalkan Pengadaan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- JavaScript --}}
<script>
let itemIndex = {{ count($pengadaan->details) }};
const isEditable = {{ $pengadaan->status === 'P' ? 'true' : 'false' }};

// Tambah Barang Baru
document.getElementById('btnTambahBarang')?.addEventListener('click', function() {
    const tbody = document.getElementById('barangList');
    const row = document.createElement('tr');
    row.classList.add('barang-item', 'border-b', 'hover:bg-indigo-50', 'transition');
    
    row.innerHTML = `
        <td class="px-4 py-3 text-gray-600">${itemIndex + 1}</td>
        <td class="px-4 py-3">
            <select name="items[${itemIndex}][idbarang]" 
                    class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 select-barang"
                    required>
                <option value="">-- Pilih Barang --</option>
                @foreach ($barang as $b)
                    <option value="{{ $b->idbarang }}">{{ $b->nama }}</option>
                @endforeach
            </select>
        </td>
        <td class="px-4 py-3">
            <input type="number" 
                   name="items[${itemIndex}][jumlah]" 
                   value="1"
                   min="1"
                   class="w-24 border-2 border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-indigo-500 input-jumlah"
                   required>
        </td>
        <td class="px-4 py-3">
            <input type="number" 
                   name="items[${itemIndex}][harga_satuan]" 
                   value="0"
                   min="0"
                   class="w-32 border-2 border-gray-300 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-indigo-500 input-harga"
                   required>
        </td>
        <td class="px-4 py-3 text-right">
            <span class="subtotal-item font-bold text-indigo-700">Rp 0</span>
        </td>
        <td class="px-4 py-3 text-center">
            <button type="button" 
                    class="btn-hapus bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    itemIndex++;
    updateTotal();
    updateRowNumbers();
});

// Hapus Barang
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-hapus')) {
        if (confirm('Hapus barang ini dari daftar?')) {
            e.target.closest('.barang-item').remove();
            updateTotal();
            updateRowNumbers();
        }
    }
});

// Update Subtotal Real-time
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('input-jumlah') || e.target.classList.contains('input-harga')) {
        const row = e.target.closest('.barang-item');
        const jumlah = parseFloat(row.querySelector('.input-jumlah').value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
        const subtotal = jumlah * harga;
        
        row.querySelector('.subtotal-item').textContent = 
            'Rp ' + subtotal.toLocaleString('id-ID');
        
        updateTotal();
    }
});

// Hitung Total Keseluruhan
function updateTotal() {
    let subtotal = 0;
    
    document.querySelectorAll('.barang-item').forEach(row => {
        const jumlah = parseFloat(row.querySelector('.input-jumlah').value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
        subtotal += (jumlah * harga);
    });
    
    const ppn = subtotal * 0.1;
    const total = subtotal + ppn;
    
    document.getElementById('displaySubtotal').textContent = 
        'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('displayPPN').textContent = 
        'Rp ' + ppn.toLocaleString('id-ID');
    document.getElementById('displayTotal').textContent = 
        'Rp ' + total.toLocaleString('id-ID');
}

// Update Nomor Urut
function updateRowNumbers() {
    document.querySelectorAll('.barang-item').forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
    });
}

// Modal Functions
function openCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}

// Validasi Submit
document.getElementById('formEditPengadaan').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.barang-item');
    
    if (items.length === 0) {
        e.preventDefault();
        alert('Minimal harus ada 1 barang!');
        return;
    }
    
    let valid = true;
    items.forEach(row => {
        const idbarang = row.querySelector('.select-barang')?.value;
        const jumlah = parseFloat(row.querySelector('.input-jumlah').value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
        
        if (!idbarang || jumlah <= 0 || harga < 0) {
            valid = false;
        }
    });
    
    if (!valid) {
        e.preventDefault();
        alert('Pastikan semua data barang sudah lengkap dan valid!');
    }
});

// Initialize
updateTotal();
</script>
@endsection