@extends('layouts.pelanggan')

@section('title', 'Detail Rekam Servis - ' . ($servis->kendaraan->plat_nomor ?? 'Nota'))

@section('content')
<div class="mb-5 no-print">
    <a href="{{ route('pelanggan.rekam-servis') }}" class="inline-flex items-center gap-2 text-xs font-bold text-neutral-400 hover:text-white transition">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Kembali ke Daftar Rekam Servis</span>
    </a>
</div>

<div class="bg-ink-900 border border-neutral-800 rounded-3xl p-6 sm:p-8 shadow-xl">
    <!-- Header Nota / Faktur -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-6 border-b border-neutral-800 gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-lg bg-brand-500/10 border border-brand-500/20 text-brand-400 font-extrabold text-xs">
                    SIRAKA &bull; Baba Auto Service
                </span>
                <span class="text-xs text-neutral-500">Bengkel Perawatan & Perbaikan Mobil</span>
            </div>
            <h1 class="text-xl font-black text-white">Lembar Rekam Medis Servis</h1>
            <p class="text-xs text-neutral-400 mt-0.5">ID Servis: #RS-{{ str_pad($servis->id, 5, '0', STR_PAD_LEFT) }} &bull; Tanggal: {{ \Carbon\Carbon::parse($servis->tanggal_servis)->translatedFormat('d F Y') }}</p>
        </div>

        <div class="flex items-center gap-2 no-print">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-xs shadow-md shadow-brand-500/20 transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Nota / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Data Konsumen & Kendaraan (2 Kolom) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-6 text-xs">
        <div class="p-4 bg-black/40 rounded-2xl border border-neutral-800/80 space-y-1.5">
            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-neutral-500">Data Pelanggan</span>
            <div class="flex justify-between">
                <span class="text-neutral-400">Nama Konsumen:</span>
                <span class="text-white font-bold">{{ $servis->kendaraan->konsumen->nama_lengkap ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-neutral-400">No. WhatsApp / HP:</span>
                <span class="text-white font-medium">{{ $servis->kendaraan->konsumen->no_hp ?? '-' }}</span>
            </div>
        </div>

        <div class="p-4 bg-black/40 rounded-2xl border border-neutral-800/80 space-y-1.5">
            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-neutral-500">Data Kendaraan</span>
            <div class="flex justify-between">
                <span class="text-neutral-400">Nomor Polisi (Plat):</span>
                <span class="text-brand-400 font-black tracking-wider">{{ $servis->kendaraan->plat_nomor }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-neutral-400">Unit / Tipe:</span>
                <span class="text-white font-medium">{{ $servis->kendaraan->merk }} {{ $servis->kendaraan->tipe_model }} {{ $servis->kendaraan->tahun ? '('.$servis->kendaraan->tahun.')' : '' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-neutral-400">Kilometer Servis:</span>
                <span class="text-white font-bold">{{ number_format($servis->km_akhir, 0, ',', '.') }} KM</span>
            </div>
        </div>
    </div>

    <!-- Diagnosa & Tindakan -->
    <div class="p-5 bg-neutral-900/50 rounded-2xl border border-neutral-800 space-y-3 text-xs mb-6">
        <div>
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-neutral-500 block">Keluhan Konsumen</span>
            <p class="text-neutral-300 font-medium mt-0.5">"{{ $servis->keluhan_awal }}"</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-neutral-800/80">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-neutral-500 block">Diagnosa Awal Sistem</span>
                <p class="text-neutral-400 font-semibold mt-0.5">{{ $servis->diagnosa_awal }}</p>
            </div>
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-500 block">Diagnosa Final Pemeriksaan Fisik</span>
                <p class="text-white font-bold mt-0.5">{{ $servis->diagnosa_akhir ?? 'Menunggu konfirmasi cek fisik mekanik' }}</p>
            </div>
        </div>
        @if($servis->tindakan_servis)
            <div class="pt-2 border-t border-neutral-800/80">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-neutral-500 block">Tindakan Servis / Pekerjaan Selesai</span>
                <p class="text-neutral-200 font-medium mt-0.5">{{ $servis->tindakan_servis }}</p>
            </div>
        @endif
    </div>

    <!-- Tabel Rincian Suku Cadang & Jasa -->
    <div class="mb-6">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-neutral-400 mb-2">Rincian Komponen Suku Cadang & Jasa</h3>
        <div class="border border-neutral-800 rounded-2xl overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/40 border-b border-neutral-800 text-neutral-400 font-bold text-[11px]">
                    <tr>
                        <th class="p-3">Item / Uraian Pekerjaan</th>
                        <th class="p-3 text-center">Tipe</th>
                        <th class="p-3 text-center">Qty</th>
                        <th class="p-3 text-right">Tarif / Harga</th>
                        <th class="p-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/80 text-neutral-300">
                    @if(relation_loaded($servis, 'detailSpareparts'))
                        @foreach($servis->detailSpareparts as $item)
                            <tr>
                                <td class="p-3 font-semibold text-white">{{ $item->sparepart->nama_barang ?? 'Suku Cadang' }}</td>
                                <td class="p-3 text-center"><span class="px-2 py-0.5 rounded bg-neutral-800 text-[10px] text-neutral-400">Sparepart</span></td>
                                <td class="p-3 text-center">{{ $item->qty }}</td>
                                <td class="p-3 text-right">Rp {{ number_format($item->harga_jual_saat_transaksi, 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-bold text-white">Rp {{ number_format($item->harga_jual_saat_transaksi * $item->qty, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @endif

                    @if(relation_loaded($servis, 'detailJasas'))
                        @foreach($servis->detailJasas as $jasa)
                            <tr>
                                <td class="p-3 font-semibold text-white">{{ $jasa->jasa->nama_jasa ?? 'Jasa Servis' }}</td>
                                <td class="p-3 text-center"><span class="px-2 py-0.5 rounded bg-neutral-800 text-[10px] text-sky-400">Jasa Mekanik</span></td>
                                <td class="p-3 text-center">1</td>
                                <td class="p-3 text-right">Rp {{ number_format($jasa->harga_saat_transaksi, 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-bold text-white">Rp {{ number_format($jasa->harga_saat_transaksi, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @endif

                    @if((!relation_loaded($servis, 'detailSpareparts') || $servis->detailSpareparts->isEmpty()) && (!relation_loaded($servis, 'detailJasas') || $servis->detailJasas->isEmpty()))
                        <tr>
                            <td colspan="5" class="p-6 text-center text-neutral-500 italic">Rincian item belum diinput atau sedang dalam pengerjaan.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Catatan Rekomendasi Lanjutan -->
    @if(relation_loaded($servis, 'catatanRekomendasis') && $servis->catatanRekomendasis->isNotEmpty())
        <div class="mb-6 p-4 rounded-2xl bg-amber-500/5 border border-amber-500/25 text-xs">
            <span class="block text-[11px] font-extrabold uppercase tracking-wider text-amber-400 mb-1.5 flex items-center gap-2">
                <i class="fa-solid fa-bell"></i>
                <span>Catatan Rekomendasi Servis Lanjutan Dari Bengkel</span>
            </span>
            <ul class="space-y-1.5 text-neutral-300">
                @foreach($servis->catatanRekomendasis as $rek)
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-amber-500 mt-1 text-[10px]"></i>
                        <div>
                            <span>{{ $rek->catatan }}</span>
                            <span class="ml-2 text-[10px] font-bold uppercase {{ $rek->status_konfirmasi == 'sudah' ? 'text-emerald-400' : 'text-neutral-500' }}">
                                (Status: {{ $rek->status_konfirmasi == 'sudah' ? 'Sudah Dikerjakan' : 'Perlu Diperhatikan' }})
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Total Tagihan & Status -->
    <div class="flex flex-col sm:flex-row justify-between items-end pt-4 border-t border-neutral-800 text-xs gap-4">
        <div class="text-neutral-500 text-[11px]">
            <p>Terima kasih atas kepercayaan Anda merawat kendaraan di Baba Auto Service.</p>
            <p>Simpan lembar digital ini sebagai referensi historis perawatan kendaraan Anda.</p>
        </div>

        <div class="w-full sm:w-64 space-y-1.5 text-right">
            @if($servis->nota)
                <div class="flex justify-between text-neutral-400">
                    <span>Subtotal:</span>
                    <span>Rp {{ number_format($servis->nota->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($servis->nota->diskon > 0)
                    <div class="flex justify-between text-emerald-400">
                        <span>Potongan Diskon:</span>
                        <span>- Rp {{ number_format($servis->nota->diskon, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-black text-white pt-1.5 border-t border-neutral-800">
                    <span>Total Tagihan:</span>
                    <span class="text-brand-400">Rp {{ number_format($servis->nota->total_biaya, 0, ',', '.') }}</span>
                </div>
                <div class="text-[10px] text-emerald-400 font-extrabold uppercase tracking-wider">
                    Status: {{ $servis->nota->status_pembayaran }} &bull; Nota #{{ $servis->nota->no_nota }}
                </div>
            @else
                <div class="text-right text-neutral-500 italic">
                    Tagihan nota belum diterbitkan.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection