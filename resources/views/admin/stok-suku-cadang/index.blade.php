@extends('layouts.admin')

@section('title', 'Stok Suku Cadang - SIRAKA')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-extrabold text-white">
                Stok Suku Cadang
            </h2>
            <p class="text-xs text-neutral-500 mt-1">
                Pengelolaan persediaan suku cadang bengkel.
            </p>
        </div>

        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.stok-suku-cadang.riwayat') }}"
               class="px-4 py-2.5 rounded-xl bg-neutral-800 text-neutral-200 text-xs font-bold hover:bg-neutral-700">
                <i class="fa-solid fa-clock-rotate-left mr-1"></i>
                Riwayat Stok
            </a>

            <a href="{{ route('admin.stok-suku-cadang.create') }}"
               class="px-4 py-2.5 rounded-xl bg-brand-500 text-white text-xs font-bold hover:bg-brand-600">
                <i class="fa-solid fa-plus mr-1"></i>
                Barang Masuk
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="bg-ink-900 border border-neutral-800 rounded-2xl p-5">
            <p class="text-xs text-neutral-500">Jenis Suku Cadang</p>
            <h3 class="text-2xl font-extrabold text-white mt-2">
                {{ $totalBarang }}
            </h3>
        </div>

        <div class="bg-ink-900 border border-neutral-800 rounded-2xl p-5">
            <p class="text-xs text-neutral-500">Total Stok Tersedia</p>
            <h3 class="text-2xl font-extrabold text-brand-400 mt-2">
                {{ number_format($totalStok, 0, ',', '.') }}
            </h3>
        </div>

        <div class="bg-ink-900 border border-neutral-800 rounded-2xl p-5">
            <p class="text-xs text-neutral-500">Stok Menipis (≤ 2)</p>
            <h3 class="text-2xl font-extrabold text-amber-400 mt-2">
                {{ $stokMenipis }}
            </h3>
        </div>

    </div>

    <div class="bg-ink-900 border border-neutral-800 rounded-2xl overflow-hidden">

        <div class="p-4 border-b border-neutral-800">
            <h3 class="font-bold text-white text-sm">
                Daftar Persediaan Suku Cadang
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">

                <thead class="bg-neutral-900 text-neutral-400">
                    <tr>
                        <th class="p-4">No.</th>
                        <th class="p-4">Kode Barang</th>
                        <th class="p-4">Nama Suku Cadang</th>
                        <th class="p-4">Supplier</th>
                        <th class="p-4 text-center">Stok</th>
                        <th class="p-4 text-center">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-neutral-800">

                    @forelse($spareparts as $index => $item)

                        <tr class="hover:bg-neutral-800/40">

                            <td class="p-4 text-neutral-500">
                                {{ $index + 1 }}
                            </td>

                            <td class="p-4">
                                {{ $item->kode_barang ?? '-' }}
                            </td>

                            <td class="p-4 font-bold text-white">
                                {{ $item->nama_barang }}
                            </td>

                            <td class="p-4 text-neutral-400">
                                {{ $item->nama_toko ?? '-' }}
                            </td>

                            <td class="p-4 text-center font-bold text-white">
                                {{ number_format($item->stok, 0, ',', '.') }}
                            </td>

                            <td class="p-4 text-center">
                                @if($item->stok <= 0)
                                    <span class="px-2 py-1 rounded-lg bg-rose-500/10 text-rose-400">
                                        Habis
                                    </span>
                                @elseif($item->stok <= 2)
                                    <span class="px-2 py-1 rounded-lg bg-amber-500/10 text-amber-400">
                                        Menipis
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-400">
                                        Tersedia
                                    </span>
                                @endif
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="p-8 text-center text-neutral-500">
                                Belum ada data stok suku cadang.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection