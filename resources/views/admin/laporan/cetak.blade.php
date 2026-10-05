<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan & Laba Bersih - SIRAKA ({{ $startDate }} s/d {{ $endDate }})</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body class="bg-neutral-100 text-neutral-800 p-6 sm:p-10 text-xs">

    <!-- Action Toolbar (Hidden When Printed) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print bg-white p-4 rounded-2xl shadow-sm border border-neutral-200">
        <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center gap-2 font-bold text-neutral-600 hover:text-neutral-900">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Panel Laporan</span>
        </a>
        <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-extrabold shadow flex items-center gap-2 transition">
            <i class="fa-solid fa-print"></i>
            <span>Cetak / Unduh PDF</span>
        </button>
    </div>

    <!-- Paper Sheet Document (UC-13) -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-neutral-200">
        <!-- Kop Surat Bengkel -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-neutral-800 mb-6">
            <div>
                <h1 class="text-2xl font-black tracking-wider text-neutral-900">BABA AUTO SERVICE</h1>
                <p class="text-xs font-bold text-orange-600 uppercase tracking-widest mt-0.5">SISTEM INFORMASI REKAM KENDARAAN & ADMINISTRASI (SIRAKA)</p>
                <p class="text-[11px] text-neutral-500 mt-1">Jl. H.R. Soebrantas No. 88, Panam, Kota Pekanbaru, Riau &bull; WhatsApp: 0812-3456-7890</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded bg-neutral-100 text-neutral-700 font-extrabold text-[11px] uppercase tracking-wider block mb-1">
                    LAPORAN RESMI
                </span>
                <span class="text-[11px] text-neutral-500">Dicetak: {{ date('d/m/Y H:i') }}</span>
                <span class="text-[10px] text-neutral-400 block">Oleh: {{ auth()->user()->name }}</span>
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="text-center mb-6">
            <h2 class="text-base font-extrabold uppercase tracking-wide text-neutral-900">Laporan Rekapitulasi Pendapatan & Laba/Rugi Bersih</h2>
            <p class="text-xs text-neutral-600 mt-1">
                Periode: <strong class="text-neutral-900">{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> s/d <strong class="text-neutral-900">{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
            </p>
        </div>

        <!-- 3 Kotak Ringkasan Metrik -->
        <div class="grid grid-cols-3 gap-4 p-4 rounded-xl bg-neutral-50 border border-neutral-200 mb-6 text-center">
            <div>
                <span class="text-[10px] uppercase font-bold text-neutral-500 block">Total Pendapatan Kotor (Omzet)</span>
                <span class="text-base font-black text-neutral-900 font-mono">Rp {{ number_format($pendapatanKotor, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-neutral-500 block">Total Modal Suku Cadang</span>
                <span class="text-base font-black text-neutral-700 font-mono">Rp {{ number_format($totalModal, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-emerald-600 block">Laba / Rugi Bersih</span>
                <span class="text-base font-black text-emerald-600 font-mono">Rp {{ number_format($labaBersih, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Tabel Rincian Nota Transaksi -->
        <table class="w-full text-left border-collapse border border-neutral-300 text-xs mb-8">
            <thead>
                <tr class="bg-neutral-100 text-neutral-700 font-bold uppercase text-[10px] tracking-wider">
                    <th class="border border-neutral-300 p-2.5">No</th>
                    <th class="border border-neutral-300 p-2.5">No. Nota</th>
                    <th class="border border-neutral-300 p-2.5">Tanggal</th>
                    <th class="border border-neutral-300 p-2.5">Konsumen & Plat</th>
                    <th class="border border-neutral-300 p-2.5 text-right">Modal Part</th>
                    <th class="border border-neutral-300 p-2.5 text-right">Tagihan (Omzet)</th>
                    <th class="border border-neutral-300 p-2.5 text-right">Laba Bersih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $i => $nota)
                    @php
                        $m = $nota->totalModalSparepart();
                        $l = $nota->labaBersih();
                    @endphp
                    <tr class="even:bg-neutral-50">
                        <td class="border border-neutral-300 p-2 text-center">{{ $i + 1 }}</td>
                        <td class="border border-neutral-300 p-2 font-mono font-bold">{{ $nota->no_nota }}</td>
                        <td class="border border-neutral-300 p-2">{{ \Carbon\Carbon::parse($nota->tanggal_cetak)->format('d/m/Y') }}</td>
                        <td class="border border-neutral-300 p-2">
                            <strong>{{ $nota->rekamServis->kendaraan->plat_nomor ?? '-' }}</strong> - {{ $nota->rekamServis->kendaraan->konsumen->nama_lengkap ?? '-' }}
                        </td>
                        <td class="border border-neutral-300 p-2 text-right font-mono">Rp {{ number_format($m, 0, ',', '.') }}</td>
                        <td class="border border-neutral-300 p-2 text-right font-mono font-bold">Rp {{ number_format($nota->total_biaya, 0, ',', '.') }}</td>
                        <td class="border border-neutral-300 p-2 text-right font-mono font-bold text-emerald-700">Rp {{ number_format($l, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="border border-neutral-300 p-6 text-center text-neutral-500 italic">Tidak ada transaksi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-neutral-100 font-black">
                    <td colspan="4" class="border border-neutral-300 p-2.5 text-right uppercase">Total:</td>
                    <td class="border border-neutral-300 p-2.5 text-right font-mono">Rp {{ number_format($totalModal, 0, ',', '.') }}</td>
                    <td class="border border-neutral-300 p-2.5 text-right font-mono text-orange-600">Rp {{ number_format($pendapatanKotor, 0, ',', '.') }}</td>
                    <td class="border border-neutral-300 p-2.5 text-right font-mono text-emerald-700">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Lembar Pengesahan / Tanda Tangan -->
        <div class="flex justify-between items-end pt-8 text-xs">
            <div class="text-neutral-500 text-[10px]">
                <p>Dokumen ini diterbitkan secara otomatis oleh Sistem SIRAKA.</p>
                <p>Arsip operasional resmi Baba Auto Service.</p>
            </div>
            <div class="text-center w-52">
                <p class="text-neutral-600 mb-16">Pekanbaru, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br><strong class="text-neutral-900">Kepala Bengkel / Owner</strong></p>
                <div class="border-t border-neutral-800 pt-1 font-bold text-neutral-900">
                    ( Baba Auto Service )
                </div>
            </div>
        </div>
    </div>

</body>
</html>