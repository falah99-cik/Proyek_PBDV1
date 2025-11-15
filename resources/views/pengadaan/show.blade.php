@extends('layouts.app')

@section('title', 'Detail Pengadaan')

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Detail Pengadaan #{{ $header->idpengadaan }}</h2>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <p><strong>Vendor:</strong> {{ $header->nama_vendor }}</p>
            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($header->timestamp)->format('d-m-Y H:i') }}</p>
        </div>
        <div>
            <p><strong>Status:</strong>
                @if($header->status == 'S')
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Selesai</span>
                @elseif($header->status == 'B')
                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Batal</span>
                @else
                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Sedang Diproses</span>
                @endif
            </p>
        </div>
    </div>

    <h3 class="font-medium mb-2">Rincian Barang</h3>
    <table class="w-full border text-sm">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="p-2 border">No</th>
                <th class="p-2 border">Nama Barang</th>
                <th class="p-2 border">Jumlah</th>
                <th class="p-2 border">Harga Satuan</th>
                <th class="p-2 border">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detail as $i => $d)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2">{{ $i + 1 }}</td>
                    <td class="p-2">{{ $d->nama_barang }}</td>
                    <td class="p-2 text-center">{{ $d->jumlah }}</td>
                    <td class="p-2">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                    <td class="p-2">Rp {{ number_format($d->sub_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-right mt-4">
        <p><strong>Subtotal:</strong> Rp {{ number_format($header->subtotal_nilai, 0, ',', '.') }}</p>
        <p><strong>PPN:</strong> Rp {{ number_format($header->ppn, 0, ',', '.') }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($header->total_nilai, 0, ',', '.') }}</p>
    </div>

    <div class="mt-4">
        <a href="{{ route('pengadaan.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Kembali</a>
    </div>
</div>
@endsection
