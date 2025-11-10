@extends('layouts.app')

@section('title', 'Data Barang')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Data Barang</h1>

    {{-- Alert success --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('success') }}</div>
    @endif

    {{-- Tombol tambah --}}
    <button onclick="toggleModal(true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
        + Tambah Barang
    </button>

    {{-- Filter status & jenis --}}
    <div class="flex gap-3 mt-4">
        <form method="GET" action="{{ route('barang.index') }}" class="flex gap-2 items-center">
            <label for="status" class="text-sm font-medium">Status:</label>
            <select name="status" id="status" class="border rounded px-2 py-1 text-sm">
                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua</option>
                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
            </select>

            <label for="jenis" class="text-sm font-medium">Jenis Barang:</label>
            <select name="jenis" id="jenis" class="border rounded px-2 py-1 text-sm">
                <option value="all" {{ request('jenis') == 'all' ? 'selected' : '' }}>Semua Jenis</option>
                @foreach($jenisBarang as $j)
    <option value="{{ $j->idjenis }}" {{ request('jenis') == $j->idjenis ? 'selected' : '' }}>
        {{ $j->nama_jenis }}
    </option>
@endforeach

            </select>

            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white text-sm px-3 py-1 rounded">
                Tampilkan
            </button>
        </form>
    </div>

    {{-- Tabel Barang --}}
    <div class="bg-white shadow rounded-lg p-4 overflow-x-auto">
        <table class="table-auto w-full text-sm border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">No</th>
                    <th class="p-2 text-left">Nama Barang</th>
                    <th class="p-2 text-center">Jenis Barang</th>
                    <th class="p-2 text-center">Satuan</th>
                    <th class="p-2 text-center">Status</th>
                    <th class="p-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barang as $index => $b)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2">{{ $index + 1 }}</td>
                    <td class="p-2">{{ $b->nama_barang ?? $b->nama }}</td>
                    <td class="p-2 text-center">{{ $b->nama_jenis ?? '-' }}</td>
                    <td class="p-2 text-center">{{ $b->nama_satuan ?? '-' }}</td>
                    <td class="p-2 text-center">
                        <form action="{{ route('barang.toggleStatus', $b->idbarang) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <button type="submit"
                                class="px-2 py-1 rounded text-xs font-semibold transition
                                    {{ $b->status == 1 
                                        ? 'bg-green-100 text-green-700 hover:bg-green-200' 
                                        : 'bg-gray-200 text-gray-600 hover:bg-gray-300' }}">
                                {{ $b->status == 1 ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                    </td>
                    <td class="p-2 text-center space-x-2">
                        <button 
                            onclick="editBarang({{ json_encode($b) }})"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                            Edit
                        </button>

                        <form action="{{ route('barang.destroy', $b->idbarang) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs"
                                onclick="return confirm('Hapus barang ini?')">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-3 text-center text-gray-500">Tidak ada data barang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Form Barang --}}
    <div id="barangModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-1/3 p-6">
            <h2 id="modalTitle" class="text-lg font-semibold mb-4">Tambah Barang</h2>
            <form id="barangForm" method="POST" action="{{ route('barang.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium">Nama Barang</label>
                        <input type="text" name="nama" id="nama" class="w-full border rounded px-2 py-1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Jenis Barang</label>
                        <select name="idjenis" id="idjenis" class="w-full border rounded px-2 py-1" required>
                            <option value="">-- Pilih Jenis --</option>
                            @foreach($jenisBarang as $j)
    <option value="{{ $j->idjenis }}" {{ request('jenis') == $j->idjenis ? 'selected' : '' }}>
        {{ $j->nama_jenis }}
    </option>
@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Satuan</label>
                        <select name="idsatuan" id="idsatuan" class="w-full border rounded px-2 py-1">
                            @foreach($satuan as $s)
                                <option value="{{ $s->idsatuan }}">{{ $s->nama_satuan }}</option>
                            @endforeach
                        </select>
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
function toggleModal(show) {
    const modal = document.getElementById('barangModal');
    modal.classList.toggle('hidden', !show);

    if (!show) {
        const form = document.getElementById('barangForm');
        form.reset();
        document.getElementById('modalTitle').innerText = 'Tambah Barang';
        form.action = "{{ route('barang.store') }}";
        form.querySelector('input[name=\"_method\"]')?.remove();
    }
}

function editBarang(data) {
    toggleModal(true);
    document.getElementById('modalTitle').innerText = 'Edit Barang';
    const form = document.getElementById('barangForm');
    form.action = `/barang/${data.idbarang}`;
    if (!form.querySelector('input[name=\"_method\"]')) {
        form.insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"_method\" value=\"PUT\">');
    }

    document.getElementById('nama').value = data.nama;
    document.getElementById('idjenis').value = data.idjenis ?? '';
    document.getElementById('idsatuan').value = data.idsatuan ?? '';
}
</script>
@endsection
