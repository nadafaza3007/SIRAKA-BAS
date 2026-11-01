@extends('layouts.admin')

@section('title', 'Riwayat Stok - SIRAKA')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        <div>
            <a href="{{ route('admin.stok-suku-cadang.index') }}"
               class="text-xs text-brand-400">
                <i class="fa-solid fa-arrow-left mr-1"></i>
                Kembali ke Stok
            </a>

            <h2 class="text-xl font-extrabold text-white mt-3">
                Riwayat Stok Suku Cadang
            </h2>

            <p class="text-xs text-neutral-500 mt-1">
                Catatan perubahan persediaan suku cadang.
            </p>
        </div>

    </div>

    <div class="bg-ink-900 border border-neutral-800 rounded-2xl overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-xs text-left">

                <thead class="bg-neutral-900 text-neutral-400">
                    <tr>
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Nama Suku Cadang</th>
                        <th class="p-4">Jenis Transaksi</th>
                        <th class="p-4 text-center">Jumlah</th>
                        <th class="p-4 text-center">Stok Sebelum</th>
                        <th class="p-4 text-center">Stok Sesudah</th>
                        <th class="p-4">Petugas</th>
                        <th class="p-4">Keterangan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-neutral-800">

                    @forelse($riwayat as $item)

                        <tr class="hover:bg-neutral-800/40">

                            <td class="p-4 whitespace-nowrap text-neutral-400">
                                {{ $item->tanggal->format('d/m/Y') }}
                            </td>

                            <td class="p-4 font-bold text-white">
                                {{ $item->sparepart->nama_barang ?? 'Data dihapus' }}
                            </td>

                            <td class="p-4">

                                @if($item->tipe === 'masuk')
                                    <span class="px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-400">
                                        Barang Masuk
                                    </span>
                                @elseif($item->tipe === 'keluar')
                                    <span class="px-2 py-1 rounded-lg bg-rose-500/10 text-rose-400">
                                        Barang Keluar
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-lg bg-amber-500/10 text-amber-400">
                                        Penyesuaian
                                    </span>
                                @endif

                            </td>

                            <td class="p-4 text-center font-bold">
                                {{ number_format($item->jumlah, 0, ',', '.') }}
                            </td>

                            <td class="p-4 text-center text-neutral-400">
                                {{ number_format($item->stok_sebelum, 0, ',', '.') }}
                            </td>

                            <td class="p-4 text-center font-bold text-white">
                                {{ number_format($item->stok_sesudah, 0, ',', '.') }}
                            </td>

                            <td class="p-4 text-neutral-400">
                                {{ $item->user->name ?? '-' }}
                            </td>

                            <td class="p-4 text-neutral-400">
                                {{ $item->keterangan ?? '-' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="p-8 text-center text-neutral-500">
                                Belum ada riwayat perubahan stok.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="p-4 border-t border-neutral-800">
            {{ $riwayat->links() }}
        </div>

    </div>

</div>
@endsection