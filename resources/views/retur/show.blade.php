@extends('layouts.app')

@section('title', 'Detail Retur')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('retur.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium mb-2 inline-block">
                ← Kembali ke Daftar Retur
            </a>
            <h1 class="text-2xl font-bold">
                @if($retur->jenis_retur == 'penerimaan')
                    🔴 Detail Retur ke Vendor
                @else
                    🔵 Detail Retur dari Customer
                @endif
            </h1>
        </div>
        
        @if($retur->status == 'N')
        <form action="{{ route('retur.updateStatus', $retur->idretur) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="Y">
            <button type="submit" 
                    onclick="return confirm('Approve retur ini?')"
                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition font-semibold shadow-lg">
                ✓ Approve Retur
            </button>
        </form>
        @endif
    </div>

    {{-- Info Retur --}}
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
            <h2 class="text-xl font-semibold text-white">📋 Informasi Retur</h2>
        </div>
        <div class="p-6 grid md:grid-cols-2 gap-6">
            <div>
                <label class="text-sm font-semibold text-gray-600">ID Retur</label>
                <p class="text-lg font-bold text-gray-800">#{{ $retur->idretur }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Tanggal Retur</label>
                <p class="text-lg text-gray-800">{{ \Carbon\Carbon::parse($retur->created_at)->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Jenis Retur</label>
                <p class="text-lg">
                    @if($retur->jenis_retur == 'penerimaan')
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-700">
                            🔴 Ke Vendor
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-700">
                            🔵 Dari Customer
                        </span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Status</label>
                <p class="text-lg">
                    @if($retur->status == 'Y')
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">
                            ✓ Approved
                        </span>
                    @elseif($retur->status == 'N')
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-700">
                            ⏳ Pending
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-200 text-gray-700">
                            ❌ Rejected
                        </span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Referensi Transaksi</label>
                <p class="text-lg text-gray-800">
                    @if($retur->jenis_retur == 'penerimaan')
                        Penerimaan #{{ $retur->idpenerimaan ?? '-' }}
                    @else
                        Penjualan #{{ $retur->idpenjualan ?? '-' }}
                    @endif
                </p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Dibuat Oleh</label>
                <p class="text-lg text-gray-800">{{ $retur->username ?? $retur->nama_user ?? 'User tidak tersedia' }}</p>
            </div>
            @if($retur->jenis_retur == 'penerimaan')
            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-gray-600">Vendor</label>
                <p class="text-lg text-gray-800">{{ $retur->nama_vendor ?? 'Vendor tidak tersedia' }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Detail Barang --}}
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
            <h2 class="text-xl font-semibold text-white">📦 Detail Barang Retur</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="p-3 text-left font-semibold text-gray-700">No</th>
                        <th class="p-3 text-left font-semibold text-gray-700">Nama Barang</th>
                        <th class="p-3 text-center font-semibold text-gray-700">Jumlah</th>
                        <th class="p-3 text-center font-semibold text-gray-700">Satuan</th>
                        <th class="p-3 text-left font-semibold text-gray-700">Alasan Retur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($details as $index => $detail)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-700">{{ $index + 1 }}</td>
                        <td class="p-3">
                            <span class="font-semibold text-gray-800">{{ $detail->nama_barang }}</span>
                        </td>
                        <td class="p-3 text-center">
                            <span class="font-bold text-gray-800">{{ $detail->jumlah }}</span>
                        </td>
                        <td class="p-3 text-center text-gray-700">{{ $detail->nama_satuan }}</td>
                        <td class="p-3">
                            <span class="text-gray-700">{{ $detail->alasan ?? '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            <p class="font-medium">Tidak ada detail barang</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection