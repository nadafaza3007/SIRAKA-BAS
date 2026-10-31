@extends('layouts.admin')

@section('title', 'Transaksi Servis - ' . $servis->kendaraan->plat_nomor)

@section('content')
@php
    $nota = $servis->nota;
    $rekomendasiLama = \App\Models\NotaTransaksi::query()
        ->join('rekam_servis as riwayat_lama', 'riwayat_lama.id', '=', 'nota_transaksis.rekam_servis_id')
        ->where('riwayat_lama.kendaraan_id', $servis->kendaraan_id)
        ->where('riwayat_lama.id', '<>', $servis->id)
        ->where('nota_transaksis.status_rekomendasi', 'belum')
        ->whereRaw("TRIM(COALESCE(nota_transaksis.catatan_nota, '')) <> ''")
        ->orderByDesc('riwayat_lama.tanggal_servis')
        ->orderByDesc('riwayat_lama.id')
        ->get(['nota_transaksis.id', 'nota_transaksis.catatan_nota', 'riwayat_lama.tanggal_servis']);
    $pembayaran = $nota ? $nota->pembayaran : collect();
    $totalDibayar = (int) round($pembayaran->sum('nominal_dialokasikan'));
    $sisaTagihan = max(0, (int) round($nota?->total_biaya ?? 0) - $totalDibayar);
    $totalTips = (int) round($pembayaran->sum('tips'));
    $totalKembalian = (int) round($pembayaran->sum('kembalian'));
    $totalUangDiterima = (int) round($pembayaran->sum('jumlah_diterima'));
    $lunas = $nota?->status_pembayaran === 'lunas';
    $pekerjaanSelesai = $servis->status === 'selesai'
        && $servis->km_keluar !== null;
    $bolehEdit = !$nota && $servis->status !== 'selesai';
    $bolehBayar = auth()->check()
        && in_array(auth()->user()->role, ['admin', 'anggota'], true);

    $notaAkhir = $lunas && $pekerjaanSelesai;
    $tahap = !$nota ? 1 : ($pekerjaanSelesai ? 4 : 3);
    $langkah = [
        1 => 'Tagihan Servis',
        2 => 'Pembayaran',
        3 => 'Data Akhir',
        4 => 'Penyelesaian',
    ];

    $judul = !$nota ? 'ESTIMASI BIAYA SERVIS'
        : ($notaAkhir ? 'NOTA PEMBAYARAN' : 'TAGIHAN SERVIS');
    $labelCetak = !$nota ? 'Estimasi'
        : ($notaAkhir ? 'Nota' : 'Tagihan');

    $statusDok = !$nota ? 'Estimasi'
        : ($lunas ? 'Lunas' : ($totalDibayar > 0 ? 'Dibayar Sebagian' : 'Belum Dibayar'));

    $subtotal = (int) round($servis->subtotal());
    $diskon = (int) round($nota?->diskon ?? 0);
    $total = $nota ? (int) round($nota->total_biaya) : $subtotal;
    $nomor = $nota?->no_nota
        ?? 'EST-RS-' . str_pad((string) $servis->id, 6, '0', STR_PAD_LEFT);

    $rp = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    // Hanya untuk tampilan nota (jumlah per tabel). Tidak disimpan ke mana pun.
    $subSparepart = $servis->detailSpareparts
        ->sum(fn($i) => $i->harga_jual_saat_transaksi * $i->qty);
    $barisJasa = fn($i) => max(0,
        $i->harga_saat_transaksi * ($i->qty ?? 1) - ($i->diskon ?? 0));
    $subJasa = $servis->detailJasas->sum(fn($i) => $barisJasa($i));
    $adaBiaya = $servis->detailSpareparts->isNotEmpty()
        || $servis->detailJasas->isNotEmpty();

    // Link WhatsApp saja: tidak mengirim PDF dan tidak mengubah transaksi.
    $konsumenWa = $servis->kendaraan?->konsumen;
    $nomorAsliWa = trim((string) ($konsumenWa?->no_hp ?? ''));
    $nomorBersihWa = preg_replace('/[\s().-]+/', '', $nomorAsliWa);
    $nomorBersihWa = preg_replace('/^\+/', '', $nomorBersihWa);
    if (str_starts_with($nomorBersihWa, '0')) {
        $nomorBersihWa = '62' . substr($nomorBersihWa, 1);
    } elseif (str_starts_with($nomorBersihWa, '8')) {
        $nomorBersihWa = '62' . $nomorBersihWa;
    }
    $nomorWaValid = preg_match('/^628[0-9]{7,12}$/', $nomorBersihWa) === 1;
    $pesanWa = 'Halo ' . ($konsumenWa?->nama_lengkap ?? 'Pelanggan')
        . ', berikut informasi ' . ($nota ? 'nota' : 'estimasi')
        . ' servis kendaraan ' . $servis->kendaraan->plat_nomor
        . ' (' . $nomor . '). Silakan lihat rincian resmi melalui SIRAKA atau hubungi bengkel.';
    $tautanWa = $nomorWaValid
        ? 'https://wa.me/' . $nomorBersihWa . '?text=' . rawurlencode($pesanWa)
        : null;
@endphp

<div class="mx-auto min-w-0 max-w-5xl space-y-5">

    @if($errors->any())
        <div class="no-print rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300">
            <strong>Periksa kembali data:</strong>
            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="no-print flex flex-col items-stretch justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <a href="{{ route('admin.rekam-servis.index') }}"
                class="text-xs font-bold text-neutral-400 hover:text-white">
                <i class="fa-solid fa-arrow-left mr-1"></i>
                Kembali ke Rekam Servis
            </a>
            <h1 class="mt-2 text-xl font-black text-white">
                Transaksi Servis
            </h1>
            <p class="mt-1 text-xs text-neutral-400">
                {{ $servis->kendaraan->plat_nomor }} &bull; {{ $nomor }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2 [&>button]:flex-1 sm:[&>button]:flex-none">
            @if($tautanWa)
                <a href="{{ $tautanWa }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-3 text-xs font-bold text-white hover:bg-green-700">
                    <i class="fa-brands fa-whatsapp"></i>
                    Hubungi Pelanggan via WhatsApp
                </a>
            @endif
            <button type="button" onclick="cetakSiraka('thermal')"
                class="rounded-xl border border-neutral-700 bg-neutral-800 px-4 py-3 text-xs font-bold text-white hover:bg-neutral-700">
                <i class="fa-solid fa-receipt mr-1"></i>
                {{ $labelCetak }} Thermal
            </button>

            <button type="button" onclick="cetakSiraka('a4')"
                class="rounded-xl bg-orange-500 px-4 py-3 text-xs font-bold text-white hover:bg-orange-600">
                <i class="fa-solid fa-print mr-1"></i>
                {{ $labelCetak }} A4
            </button>
        </div>
    </div>

    {{-- PROGRES TRANSAKSI --}}
    <section class="no-print min-w-0 overflow-x-auto rounded-2xl border border-neutral-800 bg-neutral-900 p-3 sm:p-5">
        <ol class="flex items-center">
            @foreach($langkah as $no => $label)
                @php
                    $terlewati = $tahap > $no || $tahap === 4;
                    $aktif = $tahap === $no && $tahap !== 4;
                @endphp

                <li class="flex min-w-0 items-center {{ $no < 4 ? 'flex-1' : '' }}">
                    <button type="button"
                        data-tahap="{{ $no }}"
                        @disabled($no > $tahap)
                        class="flex min-w-0 items-center gap-2 disabled:cursor-not-allowed">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-black
                            {{ $terlewati ? 'bg-emerald-500 text-white' :
                               ($aktif ? 'bg-orange-500 text-white' : 'bg-neutral-800 text-neutral-500') }}">
                            @if($terlewati)
                                <i class="fa-solid fa-check"></i>
                            @else
                                {{ $no }}
                            @endif
                        </span>
                        <span class="hidden text-xs font-bold sm:inline
                            {{ $aktif ? 'text-white' :
                               ($terlewati ? 'text-emerald-400' : 'text-neutral-500') }}">
                            {{ $label }}
                        </span>
                    </button>

                    @if($no < 4)
                        <span class="mx-2 h-px flex-1
                            {{ $tahap > $no ? 'bg-emerald-500/50' : 'bg-neutral-700' }}">
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>

        <p id="deskripsiTahap" class="mt-4 text-xs leading-5 text-neutral-400">
            @if($tahap === 1)
                Susun estimasi biaya, periksa sparepart, jasa, dan diskon sebelum menerbitkan tagihan.
            @elseif($tahap === 2)
                Tagihan sudah diterbitkan. Bayar penuh, cicil, atau bayar nanti. Pembayaran tambahan boleh kapan saja.
            @elseif($tahap === 3)
                Setelah pengerjaan selesai, isi KM keluar dan catatan rekomendasi meskipun belum lunas.
            @else
                Pantau sisa tagihan. Nota bercap LUNAS tersedia jika pekerjaan selesai dan seluruh tagihan telah dibayar.
            @endif
        </p>
    </section>

    {{-- TAHAP 1: ESTIMASI --}}
    <div data-panel="1" class="space-y-4">

        @if($bolehEdit)
            <div class="no-print grid grid-cols-1 gap-4 md:grid-cols-2">

                <section class="rounded-2xl border border-neutral-800 bg-neutral-900 p-5">
                    <h2 class="mb-4 text-sm font-black text-white">
                        <i class="fa-solid fa-boxes-stacked mr-2 text-orange-400"></i>
                        Suku Cadang
                    </h2>

                    <form method="POST"
                        action="{{ route('admin.rekam-servis.sparepart.tambah', $servis->id) }}"
                        class="space-y-3">
                        @csrf

                        <label class="block text-xs font-bold text-neutral-300">
                            Pilih Sparepart
                        </label>
                        <select name="sparepart_id" required
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-sm text-white">
                            <option value="">Pilih sparepart</option>
                            @foreach($spareparts as $sp)
                                <option value="{{ $sp->id }}" data-harga="{{ $sp->harga_jual }}">
                                    {{ $sp->nama_barang }} — {{ $rp($sp->harga_jual) }} (Stok {{ $sp->stok }})
                                <option value="{{ $sp->id }}">
                            @endforeach
                        </select>

                        <div class="flex items-end gap-3">
                            <div class="w-24">
                                <label class="mb-2 block text-xs text-neutral-300">
                                    Jumlah
                                </label>
                                <input type="number" name="qty" min="1" value="1"
                                    required
                                    class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-center text-sm text-white">
                            </div>
                            <button type="submit"
                                class="flex-1 rounded-xl bg-orange-500 p-3 text-sm font-bold text-white">
                                Tambah Sparepart
                            </button>
                        </div>
                    </form>
                </section>

                <section class="rounded-2xl border border-neutral-800 bg-neutral-900 p-5">
                    <h2 class="mb-4 text-sm font-black text-white">
                        <i class="fa-solid fa-screwdriver-wrench mr-2 text-sky-400"></i>
                        Jasa Servis
                    </h2>

                    <form method="POST"
                        action="{{ route('admin.rekam-servis.jasa.tambah', $servis->id) }}"
                        class="space-y-3">
                        @csrf

                        <label class="block text-xs font-bold text-neutral-300">
                            Pilih Jasa
                        </label>
                        <select name="jasa_id" required
                            class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-sm text-white">
                            <option value="">Pilih jasa servis</option>
                            @foreach($jasas as $jasa)
                                <option value="{{ $jasa->id }}">
                                    {{ $jasa->nama_jasa }} —
                                    {{ $rp($jasa->harga) }}
                                </option>
                            @endforeach
                        </select>

                        <label class="block text-xs font-bold text-neutral-300">
                            Tarif Khusus (opsional)
                        </label>
                        <div class="flex gap-3">
                            <input type="number" name="harga_custom" min="0"
                                step="1" placeholder="Tarif master"
                                class="min-w-0 flex-1 rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-sm text-white">
                            <button type="submit"
                                class="rounded-xl bg-sky-600 px-4 py-3 text-sm font-bold text-white">
                                Tambah Jasa
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        @endif

        {{-- DOKUMEN ESTIMASI / TAGIHAN / NOTA --}}
        @include('partials.nota-resmi')

        @if(!$nota)
            <section class="no-print rounded-2xl border border-neutral-800 bg-neutral-900 p-5">
                <form method="POST"
                    action="{{ route('admin.rekam-servis.nota.konfirmasi', $servis->id) }}"
                    class="space-y-4"
                    data-confirm="Pastikan biaya sudah benar. Setelah diterbitkan, tagihan akan dikunci."
                    data-confirm-title="Terbitkan tagihan?"
                    data-confirm-ok="Ya, terbitkan">
                    @csrf

                    <h3 class="text-sm font-black text-white">
                        Konfirmasi Estimasi Biaya
                    </h3>
                    <p class="text-xs text-neutral-400">
                        Periksa kembali semua biaya. Tagihan akan diterbitkan
                        setelah estimasi dikonfirmasi.
                    </p>

                    <div class="max-w-xs">
                        <label for="diskonRibuan" class="mb-2 block text-xs text-neutral-300">
                            Diskon Total (Rp)
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="diskon_ribuan" id="diskonRibuan"
                                value="{{ old('diskon_ribuan', 0) }}" min="0" max="999999999" step="1" required
                                class="w-32 min-w-0 rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-right text-sm text-white"
                                placeholder="0">
                            <div class="flex-1 rounded-xl border border-neutral-700 bg-neutral-800/50 p-3 text-sm text-neutral-400" aria-hidden="true">,000</div>
                        </div>
                        <p class="mt-2 text-xs text-neutral-400">
                            Total: <strong id="diskonTotal" class="text-white">Rp 0</strong>
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach([5, 10, 15, 20] as $cepat)
                                <button type="button" data-diskon-ribuan="{{ $cepat }}"
                                    class="rounded-lg border border-neutral-700 px-3 py-2 text-xs text-white transition hover:bg-neutral-700">
                                    Rp {{ number_format($cepat * 1000, 0, ',', '.') }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit"
                        @disabled($servis->detailSpareparts->isEmpty() && $servis->detailJasas->isEmpty())
                        class="w-full rounded-xl bg-orange-500 p-3 text-sm font-bold text-white disabled:opacity-50">
                        Terbitkan Tagihan Servis
                    </button>
                </form>
            </section>

        @else
            <div class="no-print flex flex-wrap items-center gap-3 rounded-xl border border-neutral-800 bg-neutral-900 px-4 py-3">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-neutral-800 text-neutral-400">
                    <i class="fa-solid fa-lock"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-white">
                        Estimasi Telah Dikonfirmasi
                    </p>

                    <p class="mt-1 text-[11px] text-neutral-400">
                        Rincian biaya telah dikonfirmasi dan digunakan sebagai tagihan servis.
                    </p>
                </div>

                <span class="rounded-full border border-neutral-700 bg-neutral-800 px-3 py-1 text-[10px] font-bold text-neutral-300">
                    Terkunci
                </span>

            </div>
        @endif

    </div>

    {{-- TAHAP 2: PEMBAYARAN (BOLEH SEBELUM DATA AKHIR) --}}
    <section data-panel="2" class="hidden rounded-2xl border border-neutral-800 bg-neutral-900 p-5 no-print">
        <h3 class="text-base font-black text-white">Pembayaran Tagihan Servis</h3>
        @if($nota)
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-neutral-800 p-4"><p class="text-xs text-neutral-400">Total Tagihan</p><p class="mt-1 text-lg font-black text-white">{{ $rp($total) }}</p></div>
                <div class="rounded-xl bg-neutral-800 p-4"><p class="text-xs text-neutral-400">Sudah Dibayar</p><p class="mt-1 text-lg font-black text-emerald-400">{{ $rp($totalDibayar) }}</p></div>
                <div class="rounded-xl bg-neutral-800 p-4"><p class="text-xs text-neutral-400">Sisa Tagihan</p><p class="mt-1 text-lg font-black text-orange-400">{{ $rp($sisaTagihan) }}</p></div>
            </div>
            <div class="mt-4 rounded-xl border border-neutral-700 p-4">
                <h4 class="text-sm font-bold text-white">Riwayat Pembayaran</h4>
                @forelse($pembayaran as $bayar)
                    <div class="mt-3 flex flex-wrap justify-between gap-2 border-t border-neutral-700 pt-3 text-xs text-neutral-300">
                        <div><strong class="text-white">{{ $bayar->no_bukti ?: 'Pembayaran #'.$bayar->id }}</strong><br>{{ $bayar->tanggal_bayar?->format('d/m/Y H:i') ?? '-' }} · {{ strtoupper($bayar->metode_pembayaran) }} · Petugas: {{ $bayar->penerima?->name ?? 'Tidak diketahui' }}</div>
                        <div class="text-right"><strong class="text-emerald-400">{{ $rp($bayar->nominal_dialokasikan) }}</strong><br>Tips {{ $rp($bayar->tips) }} · Kembalian {{ $rp($bayar->kembalian) }}</div>
                    </div>
                @empty
                    <p class="mt-3 text-xs text-neutral-400">Belum ada pembayaran. Pelanggan boleh membayar setelah servis selesai.</p>
                @endforelse
            </div>
            @if($sisaTagihan > 0 && $bolehBayar)
                <form method="POST" action="{{ route('admin.rekam-servis.nota.bayar', $servis->id) }}" id="formPembayaran" class="mt-5 space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-2 block text-xs text-neutral-300">Metode Pembayaran</label>
                            <select name="metode_pembayaran" id="metodePembayaran" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-white">
                                @foreach(['tunai'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS','debit'=>'Debit'] as $kode=>$label)
                                    <option value="{{ $kode }}" @selected(old('metode_pembayaran','tunai')===$kode)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label class="mb-2 block text-xs text-neutral-300">Jumlah Diterima (Rp)</label>
                            <input type="number" name="jumlah_diterima" id="uangDiterima" required min="1" max="9999999999" step="1" value="{{ old('jumlah_diterima', $sisaTagihan) }}" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-white">
                        </div>
                    </div>
                    <fieldset id="opsiKelebihan" class="hidden rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
                        <p class="text-xs font-bold text-amber-400">Kelebihan pembayaran: <span id="labelLebih">Rp 0</span></p>
                        <div class="mt-3 flex flex-wrap gap-4 text-sm text-white">
                            <label><input type="radio" name="pilihan_kelebihan" value="kembalian" @checked(old('pilihan_kelebihan','kembalian')==='kembalian')> Berikan Kembalian</label>
                            <label><input type="radio" name="pilihan_kelebihan" value="tips" @checked(old('pilihan_kelebihan')==='tips')> Catat Sebagai Tips</label>
                        </div>
                    </fieldset>
                    <div class="rounded-xl border border-neutral-700 p-4 text-xs text-neutral-300 space-y-2">
                        <div class="flex justify-between"><span>Uang diterima</span><strong id="previewDiterima">Rp 0</strong></div>
                        <div class="flex justify-between"><span>Dialokasikan ke Tagihan</span><strong id="previewAlokasi">Rp 0</strong></div>
                        <div class="flex justify-between"><span>Kembalian</span><strong id="previewKembalian">Rp 0</strong></div>
                        <div class="flex justify-between"><span>Tips</span><strong id="previewTips">Rp 0</strong></div>
                        <div class="flex justify-between"><span>Pemasukan Bengkel</span><strong id="previewPemasukan">Rp 0</strong></div>
                    </div>
                    <label class="block text-xs text-neutral-300">Catatan Pembayaran (opsional)</label>
                    <textarea name="catatan" maxlength="2000" rows="2" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-sm text-white">{{ old('catatan') }}</textarea>
                    <p id="pesanBayar" class="text-xs text-neutral-400">Pembayaran sebagian diperbolehkan.</p>
                    <button type="submit" id="btnBayar" class="w-full rounded-xl bg-emerald-600 p-3 text-sm font-bold text-white">Simpan Pembayaran</button>
                </form>
                <p class="mt-3 text-xs text-neutral-400">Bayar Nanti: tidak perlu mencatat pembayaran Rp0. Lanjutkan ke Data Akhir menggunakan tombol Berikutnya.</p>
            @elseif($lunas)
                <p class="mt-4 text-sm font-bold text-emerald-400">Tagihan telah LUNAS. Data Akhir tetap harus dilengkapi jika pekerjaan belum selesai.</p>
            @endif
        @else
            <p class="mt-4 text-sm text-amber-400">Terbitkan tagihan terlebih dahulu.</p>
        @endif
    </section>

    {{-- TAHAP 3: DATA AKHIR --}}
    <section data-panel="3" class="hidden rounded-2xl border border-neutral-800 bg-neutral-900 p-5 no-print">
        <h3 class="text-base font-black text-white">Data Akhir Pekerjaan</h3>
        @if($nota && !$pekerjaanSelesai)
            <p class="mt-2 text-xs text-neutral-400">Isi setelah servis selesai. Pelanggan boleh belum membayar, mencicil, atau sudah lunas.</p>
            <form method="POST" action="{{ route('admin.rekam-servis.selesaikan', $servis->id) }}" class="mt-5 space-y-4">
                @csrf
                <label class="block text-xs text-neutral-300">KM Keluar</label>
                <input type="number" name="km_keluar" required min="{{ $servis->km_masuk ?? 0 }}" max="999999999" value="{{ old('km_keluar', $servis->km_keluar) }}" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-white">
                <p class="text-xs text-neutral-400">KM Masuk: {{ number_format($servis->km_masuk ?? 0, 0, ',', '.') }} KM</p>
                <label class="block text-xs text-neutral-300">Catatan Rekomendasi (opsional)</label>
                <textarea name="catatan_nota" rows="3" maxlength="5000" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-3 text-white">{{ old('catatan_nota', $nota->catatan_nota ?? '') }}</textarea>
                @if($rekomendasiLama->isNotEmpty())
                    <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4">
                        <p class="text-sm font-bold text-amber-300">Rekomendasi dari Servis Sebelumnya</p>
                        <p class="mt-1 text-xs text-neutral-400">Centang hanya jika pekerjaan terkait benar-benar telah ditangani pada servis ini. Bila satu catatan berisi beberapa tindakan, centang setelah semuanya ditangani.</p>
                        @foreach($rekomendasiLama as $rekomendasi)
                            <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-lg border border-neutral-700 p-3">
                                <input type="checkbox" name="rekomendasi_selesai[]" value="{{ $rekomendasi->id }}" @checked(in_array($rekomendasi->id, old('rekomendasi_selesai', []))) class="mt-1 rounded border-neutral-500">
                                <span class="text-xs text-neutral-200">
                                    <span class="block font-bold text-amber-200">{{ \Carbon\Carbon::parse($rekomendasi->tanggal_servis)->format('d/m/Y') }}</span>
                                    <span class="mt-1 block whitespace-pre-line">{{ $rekomendasi->catatan_nota }}</span>
                                    <span class="mt-1 block text-neutral-400">Sudah ditangani dalam pekerjaan servis saat ini</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
                <button type="submit" class="w-full rounded-xl bg-sky-600 p-3 text-sm font-bold text-white">Simpan Data Akhir Pekerjaan</button>
            </form>
        @elseif($pekerjaanSelesai)
            <div class="mt-4 rounded-xl bg-neutral-800 p-4 text-sm text-neutral-200">
                <p><strong>Data Akhir Tersimpan</strong></p>
                <p class="mt-2">KM Keluar: {{ number_format($servis->km_keluar, 0, ',', '.') }} KM</p>
                <p class="mt-2">Catatan: {{ $nota?->catatan_nota ?: 'Tidak ada catatan tambahan.' }}</p>
                @if($nota && trim((string) $nota->catatan_nota) !== '')
                    <div class="mt-3 rounded-xl border border-neutral-700 p-3">
                        <p class="text-xs text-neutral-400">Tindak lanjut rekomendasi:</p>
                        <p class="mt-1 text-sm font-semibold {{ $nota->status_rekomendasi === 'sudah' ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $nota->status_rekomendasi === 'sudah' ? 'Sudah ditangani' : ($nota->status_rekomendasi === 'belum' ? 'Belum ditangani' : 'Belum diklasifikasikan') }}
                        </p>

                    </div>
                @endif
            </div>
        @else
            <p class="mt-4 text-sm text-amber-400">Terbitkan tagihan terlebih dahulu.</p>
        @endif
    </section>

    {{-- TAHAP 4: PENYELESAIAN --}}
    <section data-panel="4" class="hidden rounded-2xl border border-neutral-800 bg-neutral-900 p-6 text-center no-print">
        @if($notaAkhir)
            <i class="fa-solid fa-circle-check text-4xl text-emerald-400"></i>
            <h3 class="mt-3 text-lg font-black text-white">Transaksi Selesai — LUNAS</h3>
            <p class="mt-2 text-sm text-neutral-400">Data Akhir lengkap dan seluruh tagihan dibayar. Nota Akhir dapat dicetak.</p>
        @elseif($pekerjaanSelesai)
            <i class="fa-solid fa-clock text-4xl text-amber-400"></i>
            <h3 class="mt-3 text-lg font-black text-white">Pekerjaan Selesai — Tagihan Belum Lunas</h3>
            <p class="mt-2 text-sm text-neutral-400">Sisa tagihan {{ $rp($sisaTagihan) }}. Pembayaran lanjutan tetap tersedia di tahap Pembayaran.</p>
        @else
            <h3 class="mt-3 text-lg font-black text-white">Menunggu Data Akhir</h3>
        @endif
        <p class="mt-3 font-bold text-white">{{ $nomor }}</p>
    </section>

    {{-- NAVIGASI TAHAP --}}
    <div class="no-print flex items-center justify-between gap-3 rounded-xl border border-neutral-800 bg-neutral-900 p-4">
        <button type="button" id="btnKembaliTahap"
            class="rounded-xl border border-neutral-700 px-4 py-3 text-xs font-bold text-white disabled:opacity-40">
            <i class="fa-solid fa-arrow-left mr-1"></i>
            Sebelumnya
        </button>

        <p id="infoNavigasi" class="text-center text-[11px] text-neutral-500">
            Tahapan sebelumnya dapat dilihat kembali.
        </p>

        <button type="button" id="btnLanjutTahap"
            class="rounded-xl bg-neutral-800 px-4 py-3 text-xs font-bold text-white disabled:opacity-40">
            Berikutnya
            <i class="fa-solid fa-arrow-right ml-1"></i>
        </button>
    </div>
</div>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
/* ================= LAYAR ================= */
:root {
    --nota-navy:#0b3954;
    --nota-garis:2px solid #0b3954;
    --nota-font:'Inter', 'Segoe UI', Arial, sans-serif;
}

/* Satu font untuk seluruh nota (layar, A4, dan thermal) */
#printArea,
#printArea *,
#printArea.nota-lunas:after {
    font-family: var(--nota-font) !important;
}

#printArea {position:relative; isolation:isolate; font-size:12px}
#printArea.nota-lunas:after {
    content:"LUNAS"; position:absolute; left:50%; top:50%;
    transform:translate(-50%,-50%) rotate(-28deg);
    font-weight:900; font-size:100px; letter-spacing:10px;
    color:#b91c1c; opacity:.10; pointer-events:none; z-index:10
}

/* Kop: logo + nama bengkel */
.nota-kop {
    display:flex; align-items:center; gap:18px;
    padding-bottom:14px; border-bottom:3px solid var(--nota-navy);
}
.nota-logo {width:80px; height:80px; object-fit:contain; flex:none}
.nota-logo-cadangan {
    width:84px; height:84px; flex:none; border-radius:50%;
    background:var(--nota-navy); color:#fff; font-weight:900; font-size:24px;
    align-items:center; justify-content:center;
}
.nota-nama {
    font-size:26px; font-weight:900; letter-spacing:.04em; line-height:1.1;
    color:var(--nota-navy); margin:0;
}
.nota-tagline {margin:5px 0 0; font-size:12px; font-weight:600; color:#475569}
.nota-alamat {margin:3px 0 0; font-size:11px; color:#64748b}

/* Judul + nomor/tanggal */
.nota-judul {
    display:flex; flex-wrap:wrap; align-items:flex-end;
    justify-content:space-between; gap:12px; padding:18px 0 14px;
}
.nota-judul-teks {
    margin:0; font-size:26px; font-weight:900; letter-spacing:.02em;
    color:var(--nota-navy); line-height:1.1;
}
.nota-meta {display:flex}
.nota-meta-sel {display:flex; flex-direction:column; gap:2px; padding:0 14px}
.nota-meta-sel + .nota-meta-sel {border-left:1px solid #94a3b8}
.nota-meta-sel span {font-size:10px; letter-spacing:.06em; color:#64748b}
.nota-meta-sel strong {font-size:13px; color:#0f172a}

/* Info pelanggan & kendaraan: "label : isi" */
.nota-info {display:grid; grid-template-columns:1fr 1fr; gap:4px 36px}
.kv-blok {margin:0}
.kv {display:grid; grid-template-columns:92px 1fr; column-gap:8px; line-height:1.7}
.kv dt {display:flex; justify-content:space-between; color:#475569}
.kv dt:after {content:":"}
.kv dd {margin:0; min-width:0; overflow-wrap:anywhere}

/* Seksi & tabel */
.nota-seksi {margin-top:18px}
.nota-seksi-judul {
    margin:0 0 6px; font-size:12px; font-weight:900;
    text-transform:uppercase; letter-spacing:.04em; color:var(--nota-navy);
}
.nota-table-wrap {width:100%; overflow-x:auto}

/* Semua garis tabel TEBAL (2px) */
.nota-table {
    width:100%; min-width:480px; border-collapse:collapse;
    border:var(--nota-garis); font-size:12px; text-align:left;
}
.nota-table th {
    background:var(--nota-navy); color:#fff; padding:7px 10px;
    font-size:11px; font-weight:700; letter-spacing:.03em;
    border:var(--nota-garis);
}
.nota-table td {
    padding:7px 10px; border:var(--nota-garis); vertical-align:top;
}
.nota-table .col-no {width:42px; text-align:center}
.nota-table tfoot td {
    background:#fff; border:var(--nota-garis);
    font-weight:800; padding:9px 10px;
}
.nota-table tfoot .ttl-label {text-align:center}
.nota-ket-kecil {margin-top:2px; font-size:10px; font-weight:400; color:#64748b}
.nota-kosong {
    margin-top:18px; padding:24px 0; text-align:center; font-size:12px;
    color:#64748b; border:2px dashed #94a3b8; border-radius:8px;
}

/* Metode pembayaran + total */
.nota-bottom {
    display:grid; grid-template-columns:1fr 1fr; gap:28px;
    margin-top:22px; break-inside:avoid; page-break-inside:avoid;
}
.nota-bottom-kiri p {margin:0; line-height:1.6}
.nota-abu {color:#64748b}
.nota-jarak {margin-top:10px !important}
.nota-total-box {width:100%; max-width:340px; margin-left:auto}
.tot {display:flex; justify-content:space-between; gap:16px; padding:3px 0}
.tot span {color:#475569}
.tot strong {text-align:right}
.tot-utama {
    margin:4px 0; padding:6px 0; font-size:13px;
    border-top:2px solid var(--nota-navy); border-bottom:2px solid var(--nota-navy);
}
.tot-utama span {color:#0f172a; font-weight:800}
.tot-catatan {margin-top:4px}

/* Tanda tangan */
.nota-ttd {
    display:flex; justify-content:space-between; gap:24px;
    margin-top:34px; break-inside:avoid; page-break-inside:avoid;
}
.ttd-kolom {min-width:180px}
.ttd-kolom p {margin:0}
.ttd-ruang {height:100px}
.ttd-kanan {text-align:right}
.ttd-nama {font-weight:800}

@media (max-width:640px) {
    .nota-info, .nota-bottom {grid-template-columns:1fr}
    .nota-total-box {max-width:none}
    .nota-kop {gap:12px}
    .nota-logo, .nota-logo-cadangan {width:64px; height:64px}
    .nota-nama {font-size:20px}
}

/* Catatan rekomendasi (panel data akhir) */
.catatan-rekomendasi {
    display:flex; flex-direction:column; align-items:stretch;
    justify-content:flex-start; gap:4px;
}
.catatan-rekomendasi__judul,
.catatan-rekomendasi__isi {display:block; margin:0; padding:0;}
.catatan-rekomendasi__judul {
    font-size:11px; font-weight:700; line-height:16px; color:#a3a3a3;
}
.catatan-rekomendasi__isi {
    font-size:12px; line-height:18px; color:#e5e5e5;
    white-space:pre-line; overflow-wrap:anywhere;
}

/* ================= CETAK ================= */
@media print {
    @page {margin:10mm}
    body * {visibility:hidden !important}
    #printArea,#printArea * {
        visibility:visible !important;
        -webkit-print-color-adjust:exact; print-color-adjust:exact;
    }
    #printArea {
        position:absolute !important; top:0 !important; left:0 !important;
        width:100% !important; max-width:none !important;
        padding:0 !important; margin:0 !important;
        border:0 !important; border-radius:0 !important;
        background:white !important; box-shadow:none !important;
        font-size:11px;
    }
    #printArea .no-print,
    #printArea .no-print * {display:none !important; visibility:hidden !important}

    #printArea .nota-info {grid-template-columns:1fr 1fr !important}
    #printArea .nota-bottom {grid-template-columns:1fr 1fr !important}
    #printArea .nota-table-wrap {overflow:visible !important}
    #printArea .nota-table {min-width:0 !important}
    #printArea .nota-table thead {display:table-header-group}
    #printArea .nota-table tr {break-inside:avoid; page-break-inside:avoid}
    #printArea .nota-table th,
    #printArea .nota-table td {padding:5px 8px; overflow-wrap:anywhere}
    #printArea .nota-judul {padding:14px 0 10px}
    #printArea .nota-seksi {margin-top:14px}
    #printArea .nota-bottom {margin-top:16px}
    #printArea .nota-ttd {margin-top:22px}
    #printArea .ttd-ruang {height:28mm}

    /* ===== Thermal 72mm =====
       Semua selector diawali "body.print-thermal #printArea" agar
       spesifisitasnya setara dengan aturan A4 di atas (selector ber-ID)
       dan menang karena urutannya di bawah. */
    body.print-thermal #printArea {
        width:72mm !important; max-width:72mm !important;
        font-size:10px !important; line-height:1.4 !important;
    }
    body.print-thermal #printArea * {
        color:#000 !important; background:transparent !important;
    }
    body.print-thermal #printArea.nota-lunas:after {content:none !important}

    /* Kop: logo + nama bengkel, rata tengah */
    body.print-thermal #printArea .nota-kop {
        display:flex !important; flex-direction:column !important;
        align-items:center !important; gap:3px !important; text-align:center !important;
        padding:0 0 6px !important; border-bottom:1px dashed #555 !important;
    }
    body.print-thermal #printArea .nota-logo,
    body.print-thermal #printArea .nota-logo-cadangan {
        width:16mm !important; height:16mm !important;
    }
    body.print-thermal #printArea .nota-logo {
        display:block !important; margin:0 auto !important;
        filter:grayscale(1) contrast(1.4);
    }
    body.print-thermal #printArea .nota-logo-cadangan {
        border:1px solid #000 !important; font-size:12px !important;
    }
    body.print-thermal #printArea .nota-nama {font-size:13px !important; letter-spacing:0 !important}
    body.print-thermal #printArea .nota-tagline,
    body.print-thermal #printArea .nota-alamat {
        font-size:8px !important; line-height:1.3 !important; margin:2px 0 0 !important;
    }

    /* Judul + nomor/tanggal */
    body.print-thermal #printArea .nota-judul {
        display:block !important; padding:8px 0 6px !important; text-align:center !important;
        border-bottom:1px dashed #555 !important;
    }
    body.print-thermal #printArea .nota-judul-teks {
        font-size:12px !important; letter-spacing:.06em !important;
    }
    body.print-thermal #printArea .nota-meta {display:block !important; margin-top:6px !important}
    body.print-thermal #printArea .nota-meta-sel {
        flex-direction:row !important; justify-content:space-between !important;
        align-items:baseline !important; gap:6px !important;
        padding:1px 0 !important; border:0 !important;
    }
    body.print-thermal #printArea .nota-meta-sel span {
        font-size:9px !important; letter-spacing:0 !important; white-space:nowrap !important;
    }
    body.print-thermal #printArea .nota-meta-sel strong {
        font-size:10px !important; text-align:right !important; overflow-wrap:anywhere !important;
    }

    /* Info pelanggan & kendaraan: 1 kolom, ditumpuk */
    body.print-thermal #printArea .nota-info {
        display:block !important; padding:0 !important;
    }
    body.print-thermal #printArea .kv-blok {
        margin:0 !important; padding:6px 0 !important;
        border-bottom:1px dashed #aaa !important;
    }
    body.print-thermal #printArea .kv {
        grid-template-columns:20mm 1fr !important; column-gap:4px !important;
        line-height:1.45 !important;
    }
    body.print-thermal #printArea .kv dd {text-align:left !important}

    /* Tabel jadi daftar baris */
    body.print-thermal #printArea .nota-seksi {margin-top:8px !important}
    body.print-thermal #printArea .nota-seksi-judul {
        font-size:10px !important; margin:0 0 4px !important;
        padding-bottom:3px !important; border-bottom:1px solid #000 !important;
    }
    body.print-thermal #printArea .nota-table-wrap {overflow:visible !important}
    body.print-thermal #printArea .nota-table {border:0 !important; min-width:0 !important}
    body.print-thermal #printArea .nota-table thead,
    body.print-thermal #printArea .nota-table .col-no {display:none !important}
    body.print-thermal #printArea .nota-table,
    body.print-thermal #printArea .nota-table tbody,
    body.print-thermal #printArea .nota-table tfoot {
        display:block !important; width:100% !important;
    }
    body.print-thermal #printArea .nota-table tbody tr {
        display:block !important; padding:5px 0 !important;
        border-bottom:1px dashed #aaa !important;
    }
    body.print-thermal #printArea .nota-table td {
        display:flex !important; width:100% !important;
        justify-content:space-between !important; gap:8px !important;
        padding:1px 0 !important; border:0 !important;
        font-size:10px !important; text-align:right !important;
    }
    body.print-thermal #printArea .nota-table td.col-desk {
        display:block !important; font-weight:700 !important; text-align:left !important;
        padding-bottom:2px !important;
    }
    body.print-thermal #printArea .nota-table td[data-label]:before {
        content:attr(data-label); text-align:left !important;
    }
    body.print-thermal #printArea .nota-ket-kecil {font-size:9px !important}
    body.print-thermal #printArea .nota-table tfoot tr {
        display:flex !important; justify-content:space-between !important;
        padding:5px 0 0 !important; border:0 !important;
    }
    body.print-thermal #printArea .nota-table tfoot td {
        width:auto !important; font-weight:800 !important; padding:0 !important;
    }

    /* Pembayaran + total */
    body.print-thermal #printArea .nota-bottom {
        display:block !important; margin-top:10px !important;
    }
    body.print-thermal #printArea .nota-bottom-kiri {margin-bottom:8px !important}
    body.print-thermal #printArea .nota-total-box {
        max-width:100% !important; margin:8px 0 0 !important;
        padding:6px 0 0 !important; border-top:1px dashed #555 !important;
    }
    body.print-thermal #printArea .tot {padding:2px 0 !important; gap:8px !important}
    body.print-thermal #printArea .tot-utama {
        margin:3px 0 !important; padding:4px 0 !important; font-size:11px !important;
        border-top:1px dashed #555 !important; border-bottom:1px dashed #555 !important;
    }
    body.print-thermal #printArea .nota-ttd {display:none !important}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const maxTahap = @json($tahap);
    const total = @json($total);
    const subtotal = @json($subtotal);
    const sisa = @json($sisaTagihan);

    let aktif = @json(!$nota ? 1 : ($pekerjaanSelesai ? 4 : 2));
    const panels = [...document.querySelectorAll('[data-panel]')];
    const kembali = document.getElementById('btnKembaliTahap');
    const lanjut = document.getElementById('btnLanjutTahap');

    function tampilkan(no) {
        aktif = Math.max(1, Math.min(maxTahap, no));
        panels.forEach(el => el.classList.toggle(
            'hidden', Number(el.dataset.panel) !== aktif
        ));
        document.querySelectorAll('[data-tahap]').forEach(btn => {
            const selected = Number(btn.dataset.tahap) === aktif;
            btn.classList.toggle('opacity-60', !selected);
            btn.setAttribute('aria-current', selected ? 'step' : 'false');
        });
        kembali.disabled = aktif === 1;
        lanjut.disabled = aktif === maxTahap;
        const info = document.getElementById('infoNavigasi');
        info.textContent = aktif < maxTahap
            ? 'Anda sedang melihat tahap sebelumnya.'
            : 'Tahap transaksi saat ini.';

        const deskripsi = document.getElementById('deskripsiTahap');
        const teks = {
            1: @json('Periksa estimasi atau tagihan servis. Rincian yang telah dikonfirmasi bersifat hanya-baca.'),
            2: @json('Pembayaran awal atau cicilan berikutnya. Bisa juga bayar nanti.'),
            3: @json('Data akhir pekerjaan: KM keluar dan catatan rekomendasi.'),
            4: @json('Penyelesaian pekerjaan dan pelunasan tagihan.'),
        };
        if (deskripsi) deskripsi.textContent = teks[aktif];
    }

    kembali.addEventListener('click', () => tampilkan(aktif - 1));
    lanjut.addEventListener('click', () => tampilkan(aktif + 1));
    document.querySelectorAll('[data-tahap]').forEach(btn => {
        btn.addEventListener('click', () => tampilkan(Number(btn.dataset.tahap)));
    });
    tampilkan(aktif);

    const rp = n => 'Rp ' + Math.round(Number(n) || 0).toLocaleString('id-ID');

    // Tampilan diskon nominal ribuan, mengikuti form dan batas backend.
    const inputDiskon = document.getElementById('diskonRibuan');
    function hitungDiskon() {
        if (!inputDiskon) return;
        const nilai = Number(inputDiskon.value);
        const ribuan = Number.isSafeInteger(nilai) && nilai >= 0 ? Math.min(nilai, 999999999) : 0;
        const diskon = Math.min(subtotal, ribuan * 1000);
        const labelHasil = document.getElementById('diskonTotal');
        if (labelHasil) labelHasil.textContent = rp(diskon);
        const labelDiskonNota = document.getElementById('labelDiskon');
        const labelTotalNota = document.getElementById('labelTotal');
        if (labelDiskonNota) labelDiskonNota.textContent = '- ' + rp(diskon);
        if (labelTotalNota) labelTotalNota.textContent = rp(subtotal - diskon);
    }
    inputDiskon?.addEventListener('input', hitungDiskon);
    document.querySelectorAll('[data-diskon-ribuan]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!inputDiskon) return;
            inputDiskon.value = btn.dataset.diskonRibuan;
            hitungDiskon();
        });
    });
    hitungDiskon();

    // Perhitungan pembayaran
    const form = document.getElementById('formPembayaran');
    const metode = document.getElementById('metodePembayaran');
    const uang = document.getElementById('uangDiterima');

    function hitungBayar() {
        if (!form) return;
        const tunai = metode.value === 'tunai';
        uang.readOnly = false;
        const diterima = Number(uang.value);
        const valid = uang.value.trim() !== '' && Number.isSafeInteger(diterima)
            && diterima >= 1 && diterima <= 9999999999 && (tunai || diterima <= sisa);
        const dialokasikan = valid ? Math.min(diterima, sisa) : 0;
        const lebih = valid && tunai ? Math.max(0, diterima - sisa) : 0;
        const radio = form.querySelector('input[name="pilihan_kelebihan"]:checked');
        const tips = radio?.value === 'tips' ? lebih : 0;
        const kembalian = lebih - tips;
        document.getElementById('opsiKelebihan').classList.toggle('hidden', lebih <= 0);
        document.getElementById('labelLebih').textContent = rp(lebih);
        document.getElementById('previewDiterima').textContent = rp(diterima);
        document.getElementById('previewAlokasi').textContent = rp(dialokasikan);
        document.getElementById('previewKembalian').textContent = rp(kembalian);
        document.getElementById('previewTips').textContent = rp(tips);
        document.getElementById('previewPemasukan').textContent = rp(dialokasikan + tips);
        document.getElementById('btnBayar').disabled = !valid;
        document.getElementById('pesanBayar').textContent = !valid
            ? 'Masukkan nominal valid; pembayaran non-tunai tidak boleh melebihi sisa.'
            : dialokasikan < sisa ? 'Pembayaran sebagian, sisa setelah ini: ' + rp(sisa - dialokasikan)
            : 'Pembayaran ini melunasi tagihan.';
    }

    metode?.addEventListener('change', hitungBayar);
    uang?.addEventListener('input', hitungBayar);
    form?.querySelectorAll('[name="pilihan_kelebihan"]').forEach(radio => {
        radio.addEventListener('change', hitungBayar);
    });
    hitungBayar();

    form?.addEventListener('submit', function (event) {
        hitungBayar();
        if (document.getElementById('btnBayar').disabled) {
            event.preventDefault();
        }
    });
});


function cetakSiraka(format) {
    const panelDokumen = document.querySelector('[data-panel="1"]');
    if (!panelDokumen) return;

    const sebelumnyaTersembunyi = panelDokumen.classList.contains('hidden');
    panelDokumen.classList.remove('hidden');

    const thermal = format === 'thermal';
    document.body.classList.toggle('print-thermal', thermal);

    // Ukuran kertas khusus thermal (rol 72mm, tinggi mengikuti isi)
    let styleHalaman = null;
    if (thermal) {
        styleHalaman = document.createElement('style');
        styleHalaman.textContent = '@page { size: 72mm auto; margin: 3mm; }';
        document.head.appendChild(styleHalaman);
    }

    let sudahDipulihkan = false;
    const pulihkan = () => {
        if (sudahDipulihkan) return;
        sudahDipulihkan = true;
        document.body.classList.remove('print-thermal');
        styleHalaman?.remove();
        if (sebelumnyaTersembunyi) panelDokumen.classList.add('hidden');
        window.removeEventListener('afterprint', pulihkan);
    };
    window.addEventListener('afterprint', pulihkan, {once:true});
    window.print();
}

</script>
@endsection