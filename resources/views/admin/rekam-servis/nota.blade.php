@extends('layouts.admin')

@section('title', 'Nota Transaksi Servis - ' . $servis->kendaraan->plat_nomor)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Nav Back & Action Buttons (Hidden on Print) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <a href="{{ route('admin.rekam-servis.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-neutral-400 hover:text-white transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Rekam Servis</span>
        </a>

        <div class="flex items-center gap-2">
            <button onclick="pilihFormatDanCetak('thermal')" class="px-3.5 py-2 rounded-xl bg-neutral-800 hover:bg-neutral-700 text-neutral-200 text-xs font-bold transition flex items-center gap-1.5 border border-neutral-700">
                <i class="fa-solid fa-receipt text-amber-400"></i>
                <span>Format Struk Kasir (Thermal)</span>
            </button>
            <button onclick="pilihFormatDanCetak('standar')" class="px-3.5 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-extrabold shadow-md shadow-brand-500/20 transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Standar (A4/Faktur)</span>
            </button>
        </div>
    </div>

    <!-- Panel Pengisian Item (Hidden on Print & If Finished) -->
    @if($servis->status !== 'selesai')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 no-print">
            <!-- Tambah Sparepart -->
            <div class="p-5 bg-ink-900 border border-neutral-800 rounded-3xl text-xs space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-white text-xs flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-brand-500"></i>
                        <span>Input Suku Cadang (Sparepart)</span>
                    </span>
                    <a href="{{ route('admin.sparepart.index') }}" class="text-[10px] text-brand-400 font-bold hover:underline">+ Master</a>
                </div>
                <form action="{{ route('admin.rekam-servis.sparepart.tambah', $servis->id) }}" method="POST" class="space-y-2">
                    @csrf
                    <div>
                        <select name="sparepart_id" required class="w-full p-2.5 bg-neutral-800/80 border border-neutral-700 text-white rounded-xl focus:ring-2 focus:ring-brand-500">
                            <option value="">-- Pilih Sparepart Dari Master --</option>
                            @foreach($spareparts as $sp)
                                <option value="{{ $sp->id }}">
                                    {{ $sp->nama_barang }} (Stok: {{ $sp->stok }}) - Rp {{ number_format($sp->harga_jual, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <input type="number" name="qty" value="1" min="1" required placeholder="Qty"
                            class="w-24 p-2 bg-neutral-800/80 border border-neutral-700 text-white rounded-xl text-center font-bold">
                        <button type="submit" class="flex-1 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-bold transition">
                            + Tambah Suku Cadang
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tambah Jasa -->
            <div class="p-5 bg-ink-900 border border-neutral-800 rounded-3xl text-xs space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-white text-xs flex items-center gap-2">
                        <i class="fa-solid fa-screwdriver-wrench text-sky-400"></i>
                        <span>Input Ongkos Jasa Servis</span>
                    </span>
                    <a href="{{ route('admin.jasa.index') }}" class="text-[10px] text-sky-400 font-bold hover:underline">+ Master</a>
                </div>
                <form action="{{ route('admin.rekam-servis.jasa.tambah', $servis->id) }}" method="POST" class="space-y-2">
                    @csrf
                    <div>
                        <select name="jasa_id" id="jasaSelect" required class="w-full p-2.5 bg-neutral-800/80 border border-neutral-700 text-white rounded-xl focus:ring-2 focus:ring-sky-500">
                            <option value="">-- Pilih Tarif Jasa --</option>
                            @foreach($jasas as $js)
                                <option value="{{ $js->id }}">
                                    {{ $js->nama_jasa }} - Rp {{ number_format($js->harga, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <input type="number" name="harga_custom" placeholder="Tarif custom (opsional)"
                            class="flex-1 p-2 bg-neutral-800/80 border border-neutral-700 text-white rounded-xl placeholder-neutral-500">
                        <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl font-bold transition">
                            + Tambah Jasa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Container Nota Cetak -->
    <div id="printArea" class="bg-ink-900 border border-neutral-800 rounded-3xl p-6 sm:p-10 shadow-2xl text-neutral-200">
        <!-- Header Identitas Bengkel & Logo dengan Background Hitam -->
        <div class="flex justify-between items-start pb-4 border-b border-neutral-800 nota-header">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-black border border-neutral-800 rounded-2xl shadow-md flex items-center justify-center logo-box">
                    <img src="{{ asset('images/logo-siraka.png') }}" alt="Logo SIRAKA" class="w-12 h-12 object-contain rounded-xl">
                </div>
                <div>
                    <h1 class="text-base font-black tracking-wider text-white">SIRAKA BENGKEL AUTOMOTIVE</h1>
                    <p class="text-[10px] text-brand-400 font-bold tracking-wide">SISTEM INFORMASI PERAWATAN KENDARAAN</p>
                    <p class="text-[10px] text-neutral-400">Jl. Soebrantas No. 88, Panam, Pekanbaru &bull; Telp/WA: 0812-3456-7890</p>
                </div>
            </div>
            <div class="text-right">
                <span class="px-2.5 py-0.5 rounded-full bg-brand-500/10 border border-brand-500/30 text-brand-400 font-black text-[10px] uppercase tracking-wider block w-fit ml-auto">
                    {{ $servis->nota ? 'NOTA RESMI' : 'PRATINJAU NOTA' }}
                </span>
                <p class="text-xs font-mono font-bold text-white mt-1">{{ $servis->nota ? $servis->nota->no_nota : 'DRAFT-RS-'.$servis->id }}</p>
                <p class="text-[10px] text-neutral-500">{{ \Carbon\Carbon::parse($servis->tanggal_servis)->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <!-- Info Konsumen & Kendaraan -->
        <div class="grid grid-cols-2 gap-4 py-3 border-b border-neutral-800 text-xs nota-info">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-neutral-500 block mb-0.5">Kepada Pelanggan:</span>
                <p class="font-bold text-white">{{ $servis->kendaraan->konsumen->nama_lengkap }}</p>
                <p class="text-neutral-400 text-[11px]">{{ $servis->kendaraan->konsumen->no_hp }}</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-neutral-500 block mb-0.5">Identitas Kendaraan:</span>
                <p class="font-black text-white tracking-wider">{{ $servis->kendaraan->plat_nomor }}</p>
                <p class="text-neutral-400 text-[11px]">{{ $servis->kendaraan->merk }} {{ $servis->kendaraan->tipe_model }} ({{ number_format($servis->km_akhir, 0, ',', '.') }} KM)</p>
            </div>
        </div>

        <!-- Diagnosa & Tindakan -->
        <div class="py-3 border-b border-neutral-800 text-xs space-y-0.5">
            <div class="flex justify-between">
                <span class="text-neutral-400">Diagnosa Fisik Mekanik:</span>
                <span class="font-bold text-white">{{ $servis->diagnosa_akhir ?? $servis->diagnosa_awal }}</span>
            </div>
            @if($servis->tindakan_servis)
                <div class="flex justify-between">
                    <span class="text-neutral-400">Tindakan Perbaikan:</span>
                    <span class="font-medium text-neutral-300">{{ $servis->tindakan_servis }}</span>
                </div>
            @endif
        </div>

        <!-- Tabel Item Transaksi -->
        <div class="py-3">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-neutral-800 text-[10px] font-extrabold text-neutral-400 uppercase tracking-wider">
                        <th class="pb-1.5">Deskripsi Suku Cadang & Jasa</th>
                        <th class="pb-1.5 text-center">Tipe</th>
                        <th class="pb-1.5 text-center">Qty</th>
                        <th class="pb-1.5 text-right">Harga Satuan</th>
                        <th class="pb-1.5 text-right">Subtotal</th>
                        @if($servis->status !== 'selesai')
                            <th class="pb-1.5 text-center no-print">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/60">
                    <!-- Suku Cadang -->
                    @forelse($servis->detailSpareparts as $spDetail)
                        <tr class="hover:bg-neutral-800/30">
                            <td class="py-2 font-semibold text-white">{{ $spDetail->sparepart->nama_barang ?? 'Sparepart' }}</td>
                            <td class="py-2 text-center text-neutral-300 font-medium">Barang</td>
                            <td class="py-2 text-center font-bold">{{ $spDetail->qty }}</td>
                            <td class="py-2 text-right font-mono">Rp {{ number_format($spDetail->harga_jual_saat_transaksi, 0, ',', '.') }}</td>
                            <td class="py-2 text-right font-mono font-bold text-white">Rp {{ number_format($spDetail->harga_jual_saat_transaksi * $spDetail->qty, 0, ',', '.') }}</td>
                            @if($servis->status !== 'selesai')
                                <td class="py-2 text-center no-print">
                                    <form action="{{ route('admin.rekam-servis.sparepart.hapus', [$servis->id, $spDetail->id]) }}" method="POST" onsubmit="return confirm('Hapus item ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300 p-1"><i class="fa-solid fa-xmark"></i></button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                    @endforelse

                    <!-- Jasa -->
                    @forelse($servis->detailJasas as $jasaDetail)
                        <tr class="hover:bg-neutral-800/30">
                            <td class="py-2 font-semibold text-white">{{ $jasaDetail->jasa->nama_jasa ?? 'Jasa Servis' }}</td>
                            <td class="py-2 text-center text-sky-400 font-medium">Jasa</td>
                            <td class="py-2 text-center font-bold">1</td>
                            <td class="py-2 text-right font-mono">Rp {{ number_format($jasaDetail->harga_saat_transaksi, 0, ',', '.') }}</td>
                            <td class="py-2 text-right font-mono font-bold text-white">Rp {{ number_format($jasaDetail->harga_saat_transaksi, 0, ',', '.') }}</td>
                            @if($servis->status !== 'selesai')
                                <td class="py-2 text-center no-print">
                                    <form action="{{ route('admin.rekam-servis.jasa.hapus', [$servis->id, $jasaDetail->id]) }}" method="POST" onsubmit="return confirm('Hapus jasa ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300 p-1"><i class="fa-solid fa-xmark"></i></button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                    @endforelse

                    @if($servis->detailSpareparts->isEmpty() && $servis->detailJasas->isEmpty())
                        <tr>
                            <td colspan="6" class="py-6 text-center text-neutral-500 italic">
                                Belum ada suku cadang atau jasa yang ditambahkan pada transaksi ini.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- REKAPITULASI BIAYA & TOTAL (Dikeluarkan dari Form agar PASTI TAMPIL saat cetak) -->
        @php
            $subtotalCalc = $servis->subtotal();
            $diskonExisting = $servis->nota ? $servis->nota->diskon : 0;
            $inputRibuanExisting = $diskonExisting > 0 ? ($diskonExisting / 1000) : 0;
            $totalCalc = max(0, $subtotalCalc - $diskonExisting);
        @endphp

        <div class="pt-4 border-t border-neutral-800 flex flex-col md:flex-row justify-between items-start gap-4">
            <!-- Catatan Garansi -->
            <div class="text-[11px] text-neutral-400 space-y-1 max-w-xs">
                @if($servis->catatanRekomendasis->isNotEmpty())
                    <div class="p-2.5 bg-amber-500/10 border border-amber-500/20 rounded-xl text-neutral-300">
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block mb-0.5">Catatan Rekomendasi:</span>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($servis->catatanRekomendasis as $rek)
                                <li>{{ $rek->catatan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <p class="italic">Garansi pengerjaan 1 minggu atau 1.000 KM. Terima kasih atas kepercayaan Anda di SIRAKA BENGKEL.</p>
            </div>

            <!-- Box Ringkasan Harga -->
            <div class="p-3.5 rounded-2xl border border-neutral-700 bg-neutral-900/80 space-y-2 text-xs w-full sm:w-72 ml-auto nota-total-box">
                <!-- Subtotal -->
                <div class="flex justify-between text-neutral-300">
                    <span>Subtotal Biaya:</span>
                    <span class="font-mono font-bold text-white">Rp {{ number_format($subtotalCalc, 0, ',', '.') }}</span>
                </div>

                <!-- Informasi Potongan Harga (PASTI MUNCUL KARENA DI LUAR FORM) -->
                <div class="flex justify-between text-neutral-300 text-[11px]">
                    <span>Potongan Harga:</span>
                    <span id="labelNominalDiskon" class="font-mono text-rose-400 font-bold">- Rp {{ number_format($diskonExisting, 0, ',', '.') }}</span>
                </div>

                <!-- Total Tagihan Akhir (PASTI MUNCUL KARENA DI LUAR FORM) -->
                <div class="flex justify-between text-sm font-black text-white pt-2 border-t border-neutral-700">
                    <span>Total Tagihan:</span>
                    <span id="labelTotalBiaya" class="text-brand-400 font-mono text-sm">Rp {{ number_format($totalCalc, 0, ',', '.') }}</span>
                </div>

                <!-- FORM INPUT DISKON & TOMBOL AKSI (Hanya tampil di layar / no-print) -->
                <div class="no-print pt-2 border-t border-neutral-800">
                    <form action="{{ route('admin.rekam-servis.nota.konfirmasi', $servis->id) }}" method="POST" id="formKonfirmasiNota" class="space-y-2">
                        @csrf
                        
                        @if($servis->status !== 'selesai')
                            <div>
                                <label class="block text-[10px] font-bold text-neutral-400 mb-0.5">Potongan Harga (Ribuan):</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-2.5 text-xs font-bold text-neutral-400">Rp</span>
                                    <input type="number" id="diskonRibuanInput" value="{{ $inputRibuanExisting }}" min="0" step="1" 
                                        class="w-full p-1.5 pl-8 pr-12 bg-neutral-800 border border-neutral-700 text-white rounded-xl font-mono text-right font-bold text-xs focus:ring-2 focus:ring-brand-500"
                                        placeholder="0">
                                    <span class="absolute right-2.5 text-xs font-bold text-neutral-400">.000</span>
                                </div>
                            </div>
                        @endif

                        <input type="hidden" name="diskon" id="diskonRupiahInput" value="{{ $diskonExisting }}">
                        <input type="hidden" name="metode_cetak" id="metodeCetakInput" value="{{ $servis->nota->metode_cetak ?? 'standar' }}">

                        <div>
                            @if($servis->status == 'selesai')
                                <div class="w-full py-2 bg-neutral-800 border border-neutral-700 text-emerald-400 font-black text-xs rounded-xl flex items-center justify-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-lock"></i>
                                    <span>Transaksi Selesai & Terkunci</span>
                                </div>
                            @else
                                <button type="submit" class="w-full py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-black text-xs rounded-xl shadow-lg shadow-emerald-500/25 transition flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Konfirmasi & Selesaikan Servis</span>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Khusus Cetak Agar Tampil Sempurna -->
<style>
@media print {
    .no-print, sidebar, header, nav, button, form {
        display: none !important;
    }
    body {
        background-color: white !important;
        color: black !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    #printArea {
        border: none !important;
        background: transparent !important;
        color: black !important;
        box-shadow: none !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    #printArea * {
        color: black !important;
    }
    .logo-box {
        background-color: black !important;
        border-color: black !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .nota-total-box {
        background-color: #f3f4f6 !important;
        border: 1px solid #d1d5db !important;
        color: black !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .nota-total-box * {
        color: black !important;
    }
    #labelNominalDiskon {
        color: #b91c1c !important;
    }
    #labelTotalBiaya {
        color: #1d4ed8 !important;
    }

    body.print-thermal {
        width: 78mm !important;
        font-size: 12px !important;
    }
    body.print-thermal .nota-header {
        flex-direction: column !important;
        text-align: center !important;
        align-items: center !important;
    }
    body.print-thermal .nota-info {
        grid-template-columns: 1fr !important;
        text-align: center !important;
    }
}
</style>

<!-- Script Kalkulasi Otomatis -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const subtotal = {{ $subtotalCalc }};
        const inputRibuan = document.getElementById('diskonRibuanInput');
        const hiddenRupiah = document.getElementById('diskonRupiahInput');
        const labelDiskon = document.getElementById('labelNominalDiskon');
        const labelTotal = document.getElementById('labelTotalBiaya');

        function hitungTotalBiaya() {
            let angkaRibuan = parseFloat(inputRibuan ? inputRibuan.value : {{ $inputRibuanExisting }}) || 0;
            
            if (angkaRibuan < 0) {
                angkaRibuan = 0;
                if (inputRibuan) inputRibuan.value = 0;
            }

            const nominalDiskon = angkaRibuan * 1000;
            const totalTagihan = Math.max(0, subtotal - nominalDiskon);

            if (hiddenRupiah) hiddenRupiah.value = Math.round(nominalDiskon);
            if (labelDiskon) labelDiskon.textContent = '- Rp ' + Math.round(nominalDiskon).toLocaleString('id-ID');
            if (labelTotal) labelTotal.textContent = 'Rp ' + Math.round(totalTagihan).toLocaleString('id-ID');
        }

        if (inputRibuan) {
            inputRibuan.addEventListener('input', hitungTotalBiaya);
            inputRibuan.addEventListener('keyup', hitungTotalBiaya);
            inputRibuan.addEventListener('change', hitungTotalBiaya);
        }

        hitungTotalBiaya();
    });

    function pilihFormatDanCetak(metode) {
        const inputMetode = document.getElementById('metodeCetakInput');
        if (inputMetode) {
            inputMetode.value = metode;
        }

        if (metode === 'thermal') {
            document.body.classList.add('print-thermal');
        } else {
            document.body.classList.remove('print-thermal');
        }

        window.print();
    }
</script>
@endsection