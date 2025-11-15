@extends('layouts.app')

@section('title', 'Data Pengadaan Barang')

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Daftar Pengadaan Barang</h2>

    {{--  Notifikasi --}}
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

    {{-- Tombol Tambah --}}
    <div class="mb-4 flex justify-between">
        <a href="{{ route('pengadaan.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Tambah Pengadaan
        </a>
    </div>

    {{-- Tabel Data --}}
    <table class="w-full border text-sm text-left">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="p-2 border">No</th>
                <th class="p-2 border">Vendor</th>
                <th class="p-2 border">Tanggal</th>
                <th class="p-2 border">Total Item</th>
                <th class="p-2 border">Total Nilai</th>
                <th class="p-2 border">PPN</th>
                <th class="p-2 border">Status</th>
                <th class="p-2 border text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengadaan as $i => $p)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2">{{ $i + 1 }}</td>
                    <td class="p-2">{{ $p->nama_vendor }}</td>
                    <td class="p-2">{{ \Carbon\Carbon::parse($p->timestamp)->format('d-m-Y') }}</td>
                    <td class="p-2 text-center">{{ $p->total_item }}</td>
                    <td class="p-2">Rp {{ number_format($p->total_nilai, 0, ',', '.') }}</td>
                    <td class="p-2">Rp {{ number_format($p->ppn, 0, ',', '.') }}</td>
                    <td class="p-2">
@if($p->status == 'S')
    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Selesai</span>

@elseif($p->status == 'P')
    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Sedang Diproses</span>

@elseif($p->status == 'B')
    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Batal</span>

@else
    <span class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs">Tidak Diketahui</span>
@endif

</td>
                    <td class="p-2 text-center space-x-1">
                        <a href="{{ route('pengadaan.show', $p->idpengadaan) }}" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs">Detail</a>
                        @if($p->status == 'P')
                            <a href="{{ route('pengadaan.edit', $p->idpengadaan) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-3 text-center text-gray-500">Belum ada data pengadaan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
