@extends('layouts.app')

@section('title', 'Detail Retur ke Vendor')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('retur.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium mb-2 inline-block">
                ← Kembali ke Daftar Retur
            </a>
            <h1 class="text-2xl font-bold">
                🔴 Detail Retur ke Vendor
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
        <div class="bg-gradient-to-r from-red-600 to-orange-600 px-6 py-4">
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
                <label class="text-sm font-semibold text-gray-600">Dibuat Oleh</label>
                <p class="text-lg text-gray-800">{{ $retur->username ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">ID Penerimaan</label>
                <p class="text-lg text-gray-800">
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg font-semibold">
                        #{{ $retur->idpenerimaan ?? '-' }}
                    </span>
                </p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Vendor</label>
                <p class="text-lg text-gray-800 font-semibold">{{ $retur->nama_vendor ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">ID Pengadaan</label>
                <p class="text-lg text-gray-800">
                    <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-lg font-semibold">
                        #{{ $retur->idpengadaan ?? '-' }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    {{-- Detail Barang --}}
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="bg-gradient-to-r from-red-600 to-orange-600 px-6 py-4">
            <h2 class="text-xl font-semibold text-white">📦 Detail Barang Retur</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="p-3 text-left font-semibold text-gray-700">No</th>
                        <th class="p-3 text-left font-semibold text-gray-700">Nama Barang</th>
                        <th class="p-3 text-center font-semibold text-gray-700">Jumlah Retur</th>
                        <th class="p-3 text-center font-semibold text-gray-700">Satuan</th>
                        <th class="p-3 text-left font-semibold text-gray-700">Alasan Retur</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($details as $index => $detail)
                    <tr class="border-b hover:bg-red-50 transition">
                        <td class="p-3 text-gray-700">{{ $index + 1 }}</td>
                        <td class="p-3">
                            <span class="font-semibold text-gray-800">{{ $detail->nama_barang }}</span>
                        </td>
                        <td class="p-3 text-center">
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full font-bold text-sm">
                                {{ $detail->jumlah }} unit
                            </span>
                        </td>
                        <td class="p-3 text-center text-gray-700">{{ $detail->nama_satuan }}</td>
                        <td class="p-3">
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 px-3 py-2 rounded">
                                <span class="text-gray-700 text-sm">{{ $detail->alasan ?? '-' }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="font-medium">Tidak ada detail barang</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($details->count() > 0)
                <tfoot class="bg-red-50 border-t-2 border-red-200">
                    <tr>
                        <td colspan="2" class="p-4 text-right font-bold text-gray-700">
                            Total Item Diretur:
                        </td>
                        <td class="p-4 text-center">
                            <span class="bg-red-600 text-white px-4 py-2 rounded-full font-bold">
                                {{ $details->sum('jumlah') }} unit
                            </span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('retur.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-semibold">
            ← Kembali
        </a>
        
        @if($retur->status == 'N')
        <form action="{{ route('retur.destroy', $retur->idretur) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                    onclick="return confirm('⚠️ Hapus retur ini? Stok akan dikembalikan!')"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                🗑️ Hapus Retur
            </button>
        </form>
        @endif
    </div>
</div>
@endsection