@extends('layouts.app')

@section('title', 'Detail Penerimaan')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800">
            📦 Detail Penerimaan
        </h1>

        <div class="flex gap-2">
            <a href="{{ route('penerimaan.index') }}"
               class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded transition">
                ← Kembali
            </a>

            @php
                $persen = $progress->total_pengadaan > 0
                    ? round(($progress->total_diterima / $progress->total_pengadaan) * 100)
                    : 0;
            @endphp

            <div>
                Progress: {{ $persen }}%
                <button onclick="toggleModalTambahDetail(true)"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded transition">
                    + Tambah Detail Penerimaan
                </button>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-xl p-6 shadow-lg">

        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold text-indigo-900">
                    Pengadaan #{{ $header->idpengadaan ?? '-' }}
                </h2>
                <p class="text-sm text-indigo-600 mt-1">
                    ID Penerimaan: #{{ $header->idpenerimaan }}
                </p>
            </div>

            <div class="text-right">
                <p class="text-sm text-gray-600">Status Penerimaan</p>
                <span class="inline-block px-4 py-2 rounded-lg font-semibold text-sm mt-1
                    {{ $persen >= 100 ? 'bg-green-500 text-white' : 'bg-yellow-400 text-gray-800' }}">
                    {{ $persen >= 100 ? '✓ Selesai' : '⏳ Dalam Proses' }}
                </span>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="grid grid-cols-3 gap-6 mb-4">
            <div class="bg-white/70 backdrop-blur rounded-lg p-4">
                <p class="text-xs text-gray-600 mb-1">Vendor</p>
                <p class="font-bold text-gray-900 text-lg">{{ $header->nama_vendor ?? '-' }}</p>
            </div>

            <div class="bg-white/70 backdrop-blur rounded-lg p-4">
                <p class="text-xs text-gray-600 mb-1">Total Nilai Pengadaan</p>
                <p class="font-bold text-indigo-700 text-lg">
                    Rp {{ number_format($header->total_nilai_pengadaan ?? 0, 0, ',', '.') }}
                </p>
            </div>

            <div class="bg-white/70 backdrop-blur rounded-lg p-4">
                <p class="text-xs text-gray-600 mb-1">Dibuat Oleh</p>
                <p class="font-semibold text-gray-900">{{ $header->diterima_oleh ?? '-' }}</p>
                <p class="text-xs text-gray-500">
                    {{ \Carbon\Carbon::parse($header->created_at)->format('d M Y, H:i') }}
                </p>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur rounded-lg p-4">
            <div class="flex justify-between items-center mb-3">
                <strong class="text-sm text-gray-700">Progress Penerimaan Total</strong>
                <span class="text-2xl font-bold {{ $persen >= 100 ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ $persen }}%
                </span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden shadow-inner">
                <div class="{{ $persen >= 100 ? 'bg-gradient-to-r from-green-400 to-green-600' : 'bg-gradient-to-r from-yellow-400 to-yellow-600' }}
                            h-4 rounded-full transition-all duration-500 ease-out relative"
                     style="width: {{ $persen }}%">
                    <div class="absolute inset-0 bg-white/30 animate-pulse"></div>
                </div>
            </div>
        </div>
    </div>


    <hr class="border-gray-200">

    <div class="space-y-4">

        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Riwayat Detail Penerimaan
        </h2>

        @php
            $groupedDetails = $detail->groupBy(fn($item) =>
                \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s')
            );
            $detailNumber = 1;
        @endphp

        @foreach ($groupedDetails as $timestamp => $items)
            <div class="bg-white border-2 border-gray-200 rounded-xl shadow-md hover:shadow-xl transition duration-300">

                {{-- Header --}}
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b-2 border-gray-200 rounded-t-xl">
                    <div class="flex justify-between items-center">

                        <div>
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <span class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm">
                                    Detail #{{ $detailNumber++ }}
                                </span>
                                Penerimaan Batch
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                 {{ \Carbon\Carbon::parse($timestamp)->format('d F Y, H:i') }} WIB
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-gray-500">Total Item</p>
                            <p class="text-2xl font-bold text-indigo-600">{{ $items->count() }}</p>
                        </div>

                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 border-b-2 border-gray-300">
                                <tr>
                                    <th class="p-3 text-left font-semibold text-gray-700">No</th>
                                    <th class="p-3 text-left font-semibold text-gray-700">Nama Barang</th>
                                    <th class="p-3 text-center font-semibold text-gray-700">Jumlah Diterima</th>
                                    <th class="p-3 text-center font-semibold text-gray-700">Harga Satuan</th>
                                    <th class="p-3 text-right font-semibold text-gray-700">Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php $subtotalBatch = 0; @endphp

                                @foreach ($items as $index => $d)
                                    @php $subtotalBatch += floatval($d->sub_total_terima ?? 0); @endphp

                                    <tr class="border-b hover:bg-indigo-50 transition">
                                        <td class="p-3">{{ $index + 1 }}</td>
                                        <td class="p-3 font-medium">{{ $d->nama_barang }}</td>
                                        <td class="p-3 text-center">
                                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full font-semibold text-xs">
                                                {{ number_format($d->jumlah_terima, 0, ',', '.') }} unit
                                            </span>
                                        </td>
                                        <td class="p-3 text-center">
                                            Rp {{ number_format($d->harga_satuan_terima, 0, ',', '.') }}
                                        </td>
                                        <td class="p-3 text-right font-bold text-indigo-700">
                                            Rp {{ number_format($d->sub_total_terima, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot class="bg-indigo-50 border-t-2 border-indigo-200">
                                <tr>
                                    <td colspan="4" class="p-4 text-right font-bold text-gray-700">
                                        Subtotal Batch:
                                    </td>
                                    <td class="p-4 text-right font-bold text-indigo-700 text-lg">
                                        Rp {{ number_format($subtotalBatch, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>

            </div>
        @endforeach
    </div>


    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-xl p-6 shadow-xl">
        <div class="flex justify-between items-center text-white">
            <div>
                <p class="text-sm opacity-90 mb-1">Total Nilai Penerimaan Keseluruhan</p>
                <p class="text-4xl font-bold">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</p>
            </div>

            <div class="text-right">
                <p class="text-sm opacity-90 mb-1">Total Detail Penerimaan</p>
                <p class="text-4xl font-bold">{{ $groupedDetails->count() }}</p>
            </div>
        </div>
    </div>


    @if (count($itemBelumDiterima) > 0)
        <div class="bg-gradient-to-br from-red-50 to-orange-50 border-2 border-red-300 rounded-xl p-6 shadow-lg">

            <div class="flex items-center gap-3 mb-4">
                <div class="bg-red-500 text-white p-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-red-800">⚠️ Barang yang Belum Diterima</h2>
                    <p class="text-sm text-red-600">
                        Masih ada {{ count($itemBelumDiterima) }} item yang belum diterima dari pengadaan ini
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-2 border-red-200 rounded-lg overflow-hidden">

                    <thead class="bg-red-600 text-white">
                        <tr>
                            <th class="p-3 text-left">No</th>
                            <th class="p-3 text-left">Nama Barang</th>
                            <th class="p-3 text-center">Total Pengadaan</th>
                            <th class="p-3 text-center">Sudah Diterima</th>
                            <th class="p-3 text-center">Sisa Belum Diterima</th>
                            <th class="p-3 text-center">Persentase</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white">
                        @foreach ($itemBelumDiterima as $index => $item)
                            @php
                                $persentase = $item->jumlah_pengadaan > 0
                                    ? round(($item->sudah_diterima / $item->jumlah_pengadaan) * 100)
                                    : 0;
                            @endphp

                            <tr class="border-b border-red-100 hover:bg-red-50 transition">
                                <td class="p-3">{{ $index + 1 }}</td>
                                <td class="p-3 font-medium">{{ $item->nama_barang }}</td>
                                <td class="p-3 text-center">
                                    <span class="bg-gray-100 px-3 py-1 rounded-full">
                                        {{ $item->jumlah_pengadaan }} unit
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-semibold">
                                        {{ $item->sudah_diterima }} unit
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full font-bold">
                                        {{ $item->sisa }} unit
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex items-center gap-2 justify-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-red-500 h-2 rounded-full transition-all"
                                                 style="width: {{ $persentase }}%">
                                            </div>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-600">{{ $persentase }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            @if ($persen < 100)
                <div class="mt-4 flex justify-end">
                    <button onclick="toggleModalTambahDetail(true)"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold 
                                   flex items-center gap-2 transition shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Terima Barang yang Tersisa
                    </button>
                </div>
            @endif

        </div>
    @endif



    <div id="modalTambahDetail"
         class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">

        <div class="bg-white rounded-lg w-2/3 p-6 max-h-[90vh] overflow-y-auto">

            <h2 class="text-lg font-semibold mb-4">Tambah Detail Penerimaan</h2>

            <form method="POST" action="{{ route('penerimaan.addDetail', $header->idpenerimaan) }}">
                @csrf

                <div id="itemsContainer" class="space-y-3 mb-4">

                    @foreach ($itemBelumDiterima as $index => $item)
                        <div class="flex gap-3 items-end border rounded-lg p-3 bg-gray-50">

                            <input type="hidden"
                                   name="items[{{ $index }}][idbarang]"
                                   value="{{ $item->idbarang }}">

                            <div class="flex-1">
                                <label class="text-xs text-gray-600">Nama Barang</label>
                                <input type="text" value="{{ $item->nama_barang }}" readonly
                                       class="w-full border rounded px-2 py-1 bg-gray-100">
                            </div>

                            <div class="w-1/4">
                                <label class="text-xs text-gray-600">Jumlah Terima</label>
                                <input type="number"
                                       name="items[{{ $index }}][jumlah_terima]"
                                       max="{{ $item->sisa }}"
                                       min="0"
                                       placeholder="Maks: {{ $item->sisa }}"
                                       class="w-full border rounded px-2 py-1"
                                       required>
                                <small class="text-gray-500">Sisa: {{ $item->sisa }}</small>
                            </div>

                            <div class="w-1/4">
                                <label class="text-xs text-gray-600">Harga Satuan</label>
                                <input type="number"
                                       name="items[{{ $index }}][harga_satuan_terima]"
                                       value="{{ $item->harga_satuan }}"
                                       class="w-full border rounded px-2 py-1 bg-gray-100"
                                       readonly>
                            </div>

                        </div>
                    @endforeach

                </div>

                <div class="flex justify-end mt-4 space-x-2">
                    <button type="button" onclick="toggleModalTambahDetail(false)"
                            class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-1 rounded">
                        Batal
                    </button>

                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded">
                        Simpan
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
    function toggleModalTambahDetail(show) {
        document.getElementById('modalTambahDetail').classList.toggle('hidden', !show);
    }
</script>

@endsection
