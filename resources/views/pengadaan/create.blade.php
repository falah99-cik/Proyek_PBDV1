@extends('layouts.app')

@section('title', 'Tambah Pengadaan')

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Tambah Pengadaan Baru</h2>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-3">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-3">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('pengadaan.store') }}" method="POST" id="formPengadaan">
        @csrf

        {{-- Pilihan Vendor --}}
        <div class="mb-4">
            <label class="block font-medium mb-1">Vendor:</label>
            <select name="vendor_idvendor" class="border rounded p-2 w-full" required>
                <option value="">-- Pilih Vendor --</option>
                @foreach($vendor as $v)
                    <option value="{{ $v->idvendor }}">{{ $v->nama_vendor }}</option>
                @endforeach
            </select>
        </div>

{{-- Daftar Barang --}}
<h3 class="text-md font-semibold mb-2">Daftar Barang:</h3>

{{-- WRAPPER --}}
<div id="barang-wrapper" class="space-y-4">

    {{-- TEMPAT ROW --}}
    <div id="barang-container" class="space-y-4">

        <div class="barang-item grid grid-cols-5 gap-4">

            {{-- Barang --}}
            <div class="flex flex-col">
                <label class="text-xs text-gray-600 mb-1">Barang</label>
                <select name="items[0][idbarang]"
                        class="border p-2 rounded select-barang"
                        onchange="ambilHarga(this)" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach($barang as $b)
                        <option value="{{ $b->idbarang }}">{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Jumlah --}}
            <div class="flex flex-col">
                <label class="text-xs text-gray-600 mb-1">Jumlah</label>
                <input type="number" name="items[0][jumlah]"
                       class="border p-2 rounded jumlah"
                       placeholder="Jumlah" min="1" required>
            </div>

            {{-- Harga Satuan --}}
            <div class="flex flex-col">
                <label class="text-xs text-gray-600 mb-1">Harga Satuan</label>
                <input type="number" name="harga_satuan[]"
                       class="harga_satuan border rounded px-3 py-2 bg-gray-100 text-right"
                       readonly>
            </div>

            {{-- Subtotal --}}
            <div class="flex flex-col">
                <label class="text-xs text-gray-600 mb-1">Subtotal</label>
                <input type="text"
                       class="total-item border rounded px-3 py-2 bg-gray-100 text-right"
                       readonly>
            </div>

            {{-- Hapus --}}
            <div class="flex flex-col">
                <label class="text-xs">&nbsp;</label>
                <button type="button"
                        class="hapusBarang bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded">
                    X
                </button>
            </div>

        </div>
    </div>

    {{-- TOMBOL TAMBAH BARANG HARUS DI LUAR barang-container --}}
    <button type="button" id="tambahBarang"
            class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 transition w-fit">
        + Tambah Barang
    </button>

</div>

{{-- TOMBOL SIMPAN PENGADAAN --}}
<div class="flex justify-end mt-4">
    <button type="submit"
        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
        Simpan Pengadaan
    </button>
</div>

{{-- TOTAL --}}
<div class="mt-6 border-t pt-4">
    <h3 class="font-semibold text-gray-700">Total Pengadaan:</h3>
    <p class="text-2xl font-bold text-blue-600" id="grandTotal">Rp 0</p>
</div>


    </form>
</div>

{{-- JAVASCRIPT --}}
<script>

let index = 1;

document.getElementById('tambahBarang').addEventListener('click', function() {

    const container = document.getElementById('barang-container');
    const row = document.createElement('div');

    row.classList.add('barang-item', 'grid', 'grid-cols-5', 'gap-4');

    row.innerHTML = `
        <div class="flex flex-col">
            <label class="text-xs text-gray-600 mb-1">Barang</label>
            <select name="items[${index}][idbarang]"
                    class="border p-2 rounded select-barang"
                    onchange="ambilHarga(this)"
                    required>
                <option value="">-- Pilih Barang --</option>
                @foreach($barang as $b)
                    <option value="{{ $b->idbarang }}">{{ $b->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col">
            <label class="text-xs text-gray-600 mb-1">Jumlah</label>
            <input type="number"
                   name="items[${index}][jumlah]"
                   class="border p-2 rounded jumlah"
                   placeholder="Jumlah"
                   min="1"
                   required>
        </div>

        <div class="flex flex-col">
            <label class="text-xs text-gray-600 mb-1">Harga Satuan</label>
            <input type="number"
                   name="harga_satuan[]"
                   class="harga_satuan border rounded px-3 py-2 bg-gray-100 text-right"
                   readonly>
        </div>

        <div class="flex flex-col">
            <label class="text-xs text-gray-600 mb-1">Subtotal</label>
            <input type="text"
                   class="total-item border rounded px-3 py-2 bg-gray-100 text-right"
                   readonly>
        </div>

        <div class="flex flex-col">
            <label class="text-xs text-gray-600 mb-1">&nbsp;</label>
            <button type="button"
                    class="hapusBarang bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded">
                X
            </button>
        </div>
    `;

    container.appendChild(row);
    index++;
});

//
// 🔄 Ambil harga otomatis
//
function ambilHarga(select) {
    const idBarang = select.value;
    const row = select.closest('.barang-item');

    const hargaInput = row.querySelector('.harga_satuan');
    const jumlahInput = row.querySelector('.jumlah');
    const subtotalInput = row.querySelector('.total-item');

    if (!idBarang) {
        hargaInput.value = 0;
        subtotalInput.value = 'Rp 0';
        hitungGrandTotal();
        return;
    }

    fetch(`/pengadaan/get-harga/${idBarang}`)
        .then(res => res.json())
        .then(data => {
            const harga = parseInt(data.harga) || 0;
            hargaInput.value = harga;

            const jumlah = parseInt(jumlahInput.value) || 0;
            subtotalInput.value = 'Rp ' + (harga * jumlah).toLocaleString('id-ID');

            hitungGrandTotal();
        });
}

//
// 🧮 Hitung subtotal real-time
//
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('jumlah')) {

        const row = e.target.closest('.barang-item');
        const jumlah = parseInt(row.querySelector('.jumlah').value) || 0;
        const harga = parseInt(row.querySelector('.harga_satuan').value) || 0;

        row.querySelector('.total-item').value =
            'Rp ' + (jumlah * harga).toLocaleString('id-ID');

        hitungGrandTotal();
    }
});

//
// 💰 Hitung total keseluruhan
//
function hitungGrandTotal() {
    let total = 0;

    document.querySelectorAll('.barang-item').forEach(row => {
        const jumlah = parseInt(row.querySelector('.jumlah').value) || 0;
        const harga = parseInt(row.querySelector('.harga_satuan').value) || 0;
        total += jumlah * harga;
    });

    document.getElementById('grandTotal').textContent =
        'Rp ' + total.toLocaleString('id-ID');
}

//
// ❌ Hapus baris
//
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('hapusBarang')) {
        e.target.closest('.barang-item').remove();
        hitungGrandTotal();
    }
});

//
// 🚫 Validasi submit
//
document.getElementById('formPengadaan').addEventListener('submit', function(e) {

    const items = document.querySelectorAll('.barang-item');
    if (items.length === 0) {
        e.preventDefault();
        alert('Tambahkan minimal 1 barang.');
        return;
    }

    for (const item of items) {
        const idbarang = item.querySelector('select').value;
        const jumlah = item.querySelector('.jumlah').value;
        const harga = item.querySelector('.harga_satuan').value;

        if (!idbarang || jumlah <= 0 || harga <= 0) {
            e.preventDefault();
            alert('Data barang belum lengkap!');
            return;
        }
    }
});
</script>
@endsection
