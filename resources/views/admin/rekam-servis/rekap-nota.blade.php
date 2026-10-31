@extends('layouts.admin')

@section('title', 'Rekap Nota Servis - SIRAKA')

@section('content')
@php $rp = fn($v) => 'Rp ' . number_format((float)$v, 0, ',', '.'); @endphp
<div class="mx-auto max-w-7xl space-y-5 pb-10">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('admin.rekam-servis.index') }}" class="text-xs font-bold text-neutral-400 hover:text-white">← Kembali ke Rekam Servis</a>
            <h2 class="mt-2 text-xl font-extrabold text-white">Rekap Nota Servis Seluruh Pelanggan</h2>
            <p class="mt-1 text-xs text-neutral-400">Satu laporan berdasarkan pelanggan → kendaraan → nota → sparepart dan jasa.</p>
        </div>
        <a href="{{ route('admin.rekam-servis.rekap-nota.export', ['start_date'=>$startDate,'end_date'=>$endDate]) }}"
           class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-xs font-bold text-white hover:bg-emerald-700">
            <i class="fa-solid fa-file-excel"></i> Ekspor Excel (.xlsx)
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-rose-700 bg-rose-900/20 p-4 text-xs text-rose-300">{{ $errors->first() }}</div>
    @endif

    <form method="GET" action="{{ route('admin.rekam-servis.rekap-nota.index') }}"
          class="grid gap-3 rounded-2xl border border-neutral-800 bg-ink-900 p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <label class="block text-xs font-bold text-neutral-300">Tanggal mulai
            <input type="date" name="start_date" required value="{{ $startDate }}"
                   class="mt-2 w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-sm text-white">
        </label>
        <label class="block text-xs font-bold text-neutral-300">Tanggal akhir
            <input type="date" name="end_date" required min="{{ $startDate }}" value="{{ $endDate }}"
                   class="mt-2 w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-sm text-white">
        </label>
        <button type="submit" class="rounded-xl bg-brand-500 px-5 py-3 text-xs font-bold text-white hover:bg-brand-600">Tampilkan Rekap</button>
    </form>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['Pelanggan', $pelanggan->count()],
            ['Nota / Kunjungan', $servis->count()],
            ['Total Tagihan', $rp($totalTagihan)],
            ['Total Dibayar', $rp($totalDibayar)],
        ] as [$label, $nilai])
            <div class="rounded-2xl border border-neutral-800 bg-ink-900 p-4">
                <p class="text-xs text-neutral-400">{{ $label }}</p>
                <p class="mt-2 text-lg font-bold text-white">{{ $nilai }}</p>
            </div>
        @endforeach
    </div>
    <div class="rounded-xl border border-neutral-800 bg-ink-900 p-4 text-xs text-neutral-400">
        <strong class="text-neutral-100">Keterangan:</strong> Filter menggunakan tanggal servis dan hanya mencakup servis yang sudah memiliki nota.
        Di file Excel, identitas pelanggan, kendaraan, dan nota digabung secara vertikal; rincian sparepart dan jasa berada pada kolom terpisah.
        Harga dan tarif transaksi bersifat historis. Tarif Master Jasa adalah nilai saat laporan diekspor. Pembayaran adalah kondisi saat ekspor, termasuk cicilan setelah tanggal servis.
        Nilai diskon dan total tagihan dihitung satu kali per nota.
    </div>

    <div class="overflow-hidden rounded-2xl border border-neutral-800 bg-ink-900">
        <div class="border-b border-neutral-800 p-4"><h3 class="text-sm font-bold text-white">Pratinjau Per Pelanggan</h3></div>
        @forelse($pelanggan as $group)
            @php
                $k = $group->first()->kendaraan->konsumen;
                $tagihan = $group->sum(fn($s)=>(float)$s->nota->total_biaya);
                $bayar = $group->sum(fn($s)=>(float)$s->nota->pembayaran->sum('nominal_dialokasikan'));
            @endphp
            <details class="border-b border-neutral-800 p-4 last:border-0">
                <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-2 text-xs text-white">
                    <span class="font-bold">{{ $k->nama_lengkap }} <span class="text-neutral-500">· {{ $k->no_hp }}</span></span>
                    <span class="text-neutral-400">{{ $group->count() }} nota · {{ $rp($tagihan) }} · Sisa {{ $rp(max(0,$tagihan-$bayar)) }}</span>
                </summary>
                <div class="mt-3 space-y-3">
                    @foreach($group->groupBy('kendaraan_id') as $byKendaraan)
                        @php $kendaraan = $byKendaraan->first()->kendaraan; @endphp
                        <div class="rounded-xl border border-neutral-700 bg-neutral-900 p-3">
                            <p class="text-xs font-bold text-orange-300">{{ $kendaraan->plat_nomor }} — {{ $kendaraan->merk }} {{ $kendaraan->tipe_model }}</p>
                            <div class="mt-2 overflow-x-auto">
                                <table class="w-full min-w-[740px] text-left text-xs text-neutral-300">
                                    <thead class="text-neutral-500"><tr><th class="py-2">Tanggal</th><th>No. Nota</th><th>Sparepart</th><th>Jasa</th><th class="text-right">Tagihan</th><th class="text-right">Dibayar</th></tr></thead>
                                    <tbody>
                                    @foreach($byKendaraan as $s)
                                        <tr class="border-t border-neutral-800">
                                            <td class="py-2">{{ \Carbon\Carbon::parse($s->tanggal_servis)->format('d/m/Y') }}</td>
                                            <td>{{ $s->nota->no_nota }}</td>
                                            <td>{{ $s->detailSpareparts->count() }} item</td>
                                            <td>{{ $s->detailJasas->count() }} item</td>
                                            <td class="text-right">{{ $rp($s->nota->total_biaya) }}</td>
                                            <td class="text-right">{{ $rp($s->nota->pembayaran->sum('nominal_dialokasikan')) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        @empty
            <p class="p-8 text-center text-xs text-neutral-400">Belum ada nota servis pada periode ini.</p>
        @endforelse
    </div>
</div>
@endsection