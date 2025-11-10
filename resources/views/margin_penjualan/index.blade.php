@extends('layouts.app')

@section('title', 'Margin Penjualan')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">💰 Margin Penjualan</h1>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Status --}}
    <div class="flex justify-between items-center mt-4">
        <button onclick="toggleModal(true)" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
            + Tambah Margin
        </button>

        <form method="GET" action="{{ route('margin_penjualan.index') }}" class="flex gap-2 items-center">
            <label for="status" class="text-sm font-medium">Tampilkan:</label>
            <select name="status" id="status" class="border rounded px-2 py-1 text-sm">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            </select>
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white text-sm px-3 py-1 rounded">
                Filter
            </button>
        </form>
    </div>

    {{-- Tabel Data --}}
    <div class="bg-white shadow rounded-lg p-4 overflow-x-auto mt-4">
        <table class="table-auto w-full text-sm border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">#</th>
                    <th class="p-2 text-center">Persentase Margin</th>
                    <th class="p-2 text-center">Status</th>
                    <th class="p-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($margin as $i => $m)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2">{{ $i + 1 }}</td>
                    <td class="p-2 text-center">{{ $m->persen_margin ?? $m->persen }}%</td>
                    <td class="p-2 text-center">
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            {{ $m->status == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' }}">
                            {{ $m->status == 1 ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="p-2 text-center space-x-2">
                        <form action="{{ route('margin_penjualan.toggle', $m->idmargin_penjualan) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <button type="submit"
                                onclick="return confirm('Aktifkan margin ini dan nonaktifkan yang lain?')"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs">
                                {{ $m->status == 1 ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <button onclick="editMargin({{ json_encode($m) }})"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                            Edit
                        </button>

                        <form action="{{ route('margin_penjualan.destroy', $m->idmargin_penjualan) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Hapus margin ini?')"
                                class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-3 text-center text-gray-500">
                        Tidak ada data margin penjualan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div id="marginModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-1/3 p-6">
            <h2 id="modalTitle" class="text-lg font-semibold mb-4">Tambah Margin</h2>
            <form id="marginForm" method="POST" action="{{ route('margin_penjualan.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium">Persentase Margin (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="persen_margin"
                            id="persen_margin" class="w-full border rounded px-2 py-1" required>
                    </div>
                </div>

                <div class="flex justify-end mt-4 space-x-2">
                    <button type="button" onclick="toggleModal(false)"
                        class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-1 rounded">Batal</button>
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleModal(show) {
    const modal = document.getElementById('marginModal');
    modal.classList.toggle('hidden', !show);

    if (!show) {
        const form = document.getElementById('marginForm');
        form.reset();
        document.getElementById('modalTitle').innerText = 'Tambah Margin';
        form.action = "{{ route('margin_penjualan.store') }}";
        form.querySelector('input[name=\"_method\"]')?.remove();
    }
}

function editMargin(data) {
    toggleModal(true);
    document.getElementById('modalTitle').innerText = 'Edit Margin';
    const form = document.getElementById('marginForm');
    form.action = `/margin-penjualan/${data.idmargin_penjualan}`;
    if (!form.querySelector('input[name=\"_method\"]')) {
        form.insertAdjacentHTML('beforeend', '<input type=\"hidden\" name=\"_method\" value=\"PUT\">');
    }
    document.getElementById('persen_margin').value = data.persen_margin ?? data.persen;
}
</script>
@endsection
