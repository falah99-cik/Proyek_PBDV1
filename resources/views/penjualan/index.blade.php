@extends('layouts.app')

@section('title', 'Data Penjualan')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Data Penjualan</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded">{{ session('error') }}</div>
    @endif

    <button onclick="toggleModal(true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
        + Tambah Penjualan
    </button>

    {{-- 📦 Daftar Penjualan --}}
    <div class="bg-white shadow rounded-lg p-4 overflow-x-auto">
        <table class="table-auto w-full text-sm border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">No</th>
                    <th class="p-2 text-left">Tanggal</th>
                    <th class="p-2 text-center">Kasir</th>
                    <th class="p-2 text-center">Subtotal</th>
                    <th class="p-2 text-center">PPN</th>
                    <th class="p-2 text-center">Total</th>
                    <th class="p-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualan as $i => $p)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2">{{ $i + 1 }}</td>
                    <td class="p-2">{{ \Carbon\Carbon::parse($p->created_at)->format('d-m-Y H:i') }}</td>
                    <td class="p-2 text-center">{{ $p->username ?? '-' }}</td>
                    <td class="p-2 text-center">Rp {{ number_format($p->subtotal_nilai, 0, ',', '.') }}</td>
                    <td class="p-2 text-center">Rp {{ number_format($p->ppn, 0, ',', '.') }}</td>
                    <td class="p-2 text-center font-semibold">Rp {{ number_format($p->total_nilai, 0, ',', '.') }}</td>
                    <td class="p-2 text-center">
        <a href="{{ route('penjualan.show', $p->idpenjualan) }}" 
           class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs">
           Detail
        </a>
    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 🧾 Modal Tambah Penjualan --}}
    <div id="penjualanModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-1/2 p-6">
            <h2 class="text-lg font-semibold mb-4">Tambah Penjualan</h2>
            <form id="penjualanForm" method="POST" action="{{ route('penjualan.store') }}">
                @csrf
                <input type="hidden" name="idmargin_penjualan" value="{{ $margin->idmargin_penjualan ?? 1 }}">

                <div class="space-y-3">
                    {{-- Pilih Barang --}}
<div>
    <label class="block text-sm font-medium">Pilih Barang</label>
    <select id="barangSelect" class="w-full border rounded px-2 py-1">
        <option value="">-- Pilih Barang --</option>
        @foreach($barang as $b)
            <option 
                value="{{ $b->idbarang }}" 
                data-nama="{{ $b->nama_barang }}"
                data-harga="{{ $b->harga_jual_dengan_ppn }}"
                data-stok="{{ $b->stok_aktual }}">
                {{ $b->nama_barang }} (Stok: {{ $b->stok_aktual }}) - Rp {{ number_format($b->harga_jual_dengan_ppn, 0, ',', '.') }}
            </option>
        @endforeach
    </select>
</div>

                    {{-- Input jumlah --}}
                    <div class="flex space-x-3">
                        <input type="number" id="jumlah" placeholder="Jumlah" class="w-1/2 border rounded px-2 py-1">
                        <button type="button" onclick="tambahItem()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-1 rounded">
                            Tambah
                        </button>
                    </div>

                    {{-- Tabel barang yang dipilih --}}
                    <table class="w-full border text-sm mt-3" id="tableItems">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-2">Nama Barang</th>
                                <th class="p-2 text-center">Jumlah</th>
                                <th class="p-2 text-center">Harga</th>
                                <th class="p-2 text-center">Subtotal</th>
                                <th class="p-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    {{-- Total Otomatis --}}
                    <div class="border-t pt-3 text-right space-y-1">
                        <p>Subtotal: <span id="subtotalDisplay">Rp 0</span></p>
                        <p>PPN ({{ $margin->persen ?? 10 }}%): <span id="ppnDisplay">Rp 0</span></p>
                        <p class="font-semibold text-lg">Total: <span id="totalDisplay">Rp 0</span></p>
                    </div>
                </div>

                <div class="flex justify-end mt-4 space-x-2">
                    <button type="button" onclick="toggleModal(false)" class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-1 rounded">Batal</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let items = [];
let ppnRate = {{ $margin->persen ?? 10 }};

function toggleModal(show) {
    document.getElementById('penjualanModal').classList.toggle('hidden', !show);
    if (!show) {
        items = [];
        document.querySelector("#tableItems tbody").innerHTML = "";
        updateTotal();
    }
}

function tambahItem() {
    const barangSelect = document.getElementById('barangSelect');
    const jumlah = parseInt(document.getElementById('jumlah').value);
    const idbarang = barangSelect.value;
    const nama = barangSelect.options[barangSelect.selectedIndex]?.dataset.nama;
    const harga = parseFloat(barangSelect.options[barangSelect.selectedIndex]?.dataset.harga);
    const stok = parseInt(barangSelect.options[barangSelect.selectedIndex]?.dataset.stok);

    if (!idbarang || jumlah <= 0) {
        alert("Pilih barang dan isi jumlah dengan benar!");
        return;
    }
    
    // ✅ Validasi stok
    if (jumlah > stok) {
        alert(`Stok tidak cukup! Stok tersedia: ${stok}`);
        return;
    }

    const subtotal = jumlah * harga;
    items.push({ idbarang, nama, jumlah, harga, subtotal });
    renderTable();
    updateTotal();

    document.getElementById('barangSelect').value = '';
    document.getElementById('jumlah').value = '';
}

function renderTable() {
    const tbody = document.querySelector("#tableItems tbody");
    tbody.innerHTML = '';
    items.forEach((item, index) => {
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td class="p-2">${item.nama}</td>
                <td class="p-2 text-center">${item.jumlah}</td>
                <td class="p-2 text-center">Rp ${item.harga.toLocaleString('id-ID')}</td>
                <td class="p-2 text-center">Rp ${(item.subtotal).toLocaleString('id-ID')}</td>
                <td class="p-2 text-center">
                    <button onclick="hapusItem(${index})" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">Hapus</button>
                </td>
            </tr>
        `);
    });

    const form = document.getElementById("penjualanForm");
    form.querySelectorAll('input[name^="barang"]').forEach(e => e.remove());
    items.forEach((item, i) => {
        form.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="barang[${i}][idbarang]" value="${item.idbarang}">
            <input type="hidden" name="barang[${i}][jumlah]" value="${item.jumlah}">
            <input type="hidden" name="barang[${i}][harga_satuan]" value="${item.harga}">
        `);
    });
}

function hapusItem(index) {
    items.splice(index, 1);
    renderTable();
    updateTotal();
}

function updateTotal() {
    const subtotal = items.reduce((sum, item) => sum + item.subtotal, 0);
    const ppn = subtotal * (ppnRate / 100);
    const total = subtotal + ppn;

    document.getElementById('subtotalDisplay').innerText = `Rp ${subtotal.toLocaleString('id-ID')}`;
    document.getElementById('ppnDisplay').innerText = `Rp ${ppn.toLocaleString('id-ID')}`;
    document.getElementById('totalDisplay').innerText = `Rp ${total.toLocaleString('id-ID')}`;
}
</script>
@endsection
