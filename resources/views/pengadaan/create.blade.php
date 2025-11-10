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
        <div id="barang-container" class="space-y-3">
            <div class="barang-item grid grid-cols-5 gap-2 items-center">
                <select name="items[0][idbarang]" class="border p-2 rounded" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach($barang as $b)
                        <option value="{{ $b->idbarang }}">{{ $b->nama }}</option>
                    @endforeach
                </select>
                <input type="number" name="items[0][jumlah]" class="border p-2 rounded jumlah" placeholder="Jumlah" min="1" required>
                <td class="p-2 text-center">
    <input type="number" name="harga_satuan[]" 
           class="harga_satuan w-full border rounded px-2 py-1 text-center bg-gray-100"
           readonly>
</td>

                <input type="text" class="border p-2 rounded bg-gray-100 total-item" placeholder="Subtotal" readonly>
                <button type="button" class="hapusBarang bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">X</button>
            </div>
        </div>

        {{-- Tombol Tambah & Simpan --}}
        <div class="flex justify-between items-center mt-4">
            <button type="button" id="tambahBarang"
                class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 transition">
                + Tambah Barang
            </button>
            <button type="submit"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                Simpan Pengadaan
            </button>
        </div>

        {{-- Total Akhir --}}
        <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold text-gray-700">Total Pengadaan:</h3>
            <p class="text-2xl font-bold text-blue-600" id="grandTotal">Rp 0</p>
        </div>
    </form>
</div>

{{-- JavaScript --}}
<script>
let index = 1;

// ➕ Tambah Barang
document.getElementById('tambahBarang').addEventListener('click', function() {
    const container = document.getElementById('barang-container');
    const div = document.createElement('div');
    div.classList.add('barang-item', 'grid', 'grid-cols-5', 'gap-2', 'items-center', 'mt-2');
    div.innerHTML = `
        <select name="items[${index}][idbarang]" class="border p-2 rounded" required>
            <option value="">-- Pilih Barang --</option>
            @foreach($barang as $b)
                <option value="{{ $b->idbarang }}">{{ $b->nama }}</option>
            @endforeach
        </select>
        <input type="number" name="items[${index}][jumlah]" class="border p-2 rounded jumlah" placeholder="Jumlah" min="1" required>
        <input type="number" name="items[${index}][harga]" class="border p-2 rounded harga" placeholder="Harga Satuan" min="1" required>
        <input type="text" class="border p-2 rounded bg-gray-100 total-item" placeholder="Subtotal" readonly>
        <button type="button" class="hapusBarang bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">X</button>
    `;
    container.appendChild(div);
    index++;
});

// 🧮 Hitung subtotal per barang & total keseluruhan
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('jumlah') || e.target.classList.contains('harga')) {
        const row = e.target.closest('.barang-item');
        const jumlah = parseInt(row.querySelector('.jumlah').value) || 0;
        const harga = parseInt(row.querySelector('.harga').value) || 0;
        const subtotal = jumlah * harga;
        const totalItem = row.querySelector('.total-item');
        totalItem.value = 'Rp ' + subtotal.toLocaleString('id-ID');
        hitungGrandTotal();
    }
});

// ❌ Hapus Barang
document.addEventListener('DOMContentLoaded', function() {
    const selectBarang = document.querySelectorAll('.select-barang');
    
    selectBarang.forEach(select => {
        select.addEventListener('change', function() {
            const idBarang = this.value;
            if (!idBarang) return;

            fetch(`/barang/${idBarang}/harga`)
                .then(res => res.json())
                .then(data => {
                    const inputHarga = this.closest('tr').querySelector('.input-harga');
                    if (data.harga) {
                        inputHarga.value = data.harga;
                    } else {
                        inputHarga.value = 0;
                    }
                });
        });
    });
});

// 💰 Hitung Total Keseluruhan
function hitungGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.barang-item').forEach(item => {
        const jumlah = parseInt(item.querySelector('.jumlah').value) || 0;
        const harga = parseInt(item.querySelector('.harga').value) || 0;
        grandTotal += jumlah * harga;
    });
    document.getElementById('grandTotal').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
}

// 🚫 Validasi sebelum submit
document.getElementById('formPengadaan').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.barang-item');
    if (items.length === 0) {
        e.preventDefault();
        alert('❌ Tambahkan minimal 1 barang sebelum menyimpan.');
        return;
    }

    for (const item of items) {
        const idbarang = item.querySelector('select').value;
        const jumlah = item.querySelector('.jumlah').value;
        const harga = item.querySelector('.harga').value;
        if (!idbarang || jumlah <= 0 || harga <= 0) {
            e.preventDefault();
            alert('❌ Pastikan semua barang, jumlah, dan harga sudah diisi dengan benar.');
            return;
        }
    }
});

function getHarga(select) {
    const idbarang = select.value;
    const row = select.closest('tr');
    const hargaInput = row.querySelector('.harga_satuan');
    const jumlahInput = row.querySelector('.jumlah');
    const subtotalInput = row.querySelector('.subtotal');

    if (!idbarang) {
        hargaInput.value = 0;
        subtotalInput.value = 0;
        return;
    }

    fetch(`/pengadaan/barang/${idbarang}/harga`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                hargaInput.value = data.harga;
                subtotalInput.value = (parseInt(jumlahInput.value) || 0) * data.harga;
                hitungTotalPengadaan();
            } else {
                alert(data.message);
            }
        })
        .catch(err => console.error('Error fetch harga:', err));
}

function hitungTotalPengadaan() {
    let total = 0;
    document.querySelectorAll('.subtotal').forEach(el => {
        total += parseInt(el.value) || 0;
    });
    document.getElementById('totalPengadaan').innerText = 'Rp ' + total.toLocaleString('id-ID');
}
</script>
@endsection
