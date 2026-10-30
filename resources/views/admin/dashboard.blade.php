@extends('layouts.admin')
@section('title', 'Dashboard Utama - SIRAKA')
@section('content')
<div class="mb-6">
  <h2 class="text-xl font-extrabold text-white tracking-tight">Panel {{ $isAdmin ? 'Owner & Manajerial' : 'Operasional Bengkel' }}</h2>
  <p class="text-xs text-neutral-500">Ringkasan operasional bengkel Baba Auto Service.</p>
</div>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  @foreach([
    ['Konsumen', number_format($totalKonsumen,0,',','.') . ' Orang', 'text-white'],
    ['Kendaraan Terdaftar', number_format($totalKendaraan,0,',','.') . ' Unit', 'text-white'],
    ['Servis Berjalan', number_format($servisBerjalan,0,',','.') . ' Mobil', 'text-brand-500'],
    $isAdmin ? ['Pemasukan Bulan Ini', 'Rp ' . number_format($pemasukanBulan,0,',','.'), 'text-emerald-400']
             : ['Servis Selesai Bulan Ini', number_format($servisSelesaiBulan,0,',','.'), 'text-emerald-400']
  ] as [$label, $nilai, $warna])
  <div class="bg-ink-900 p-4 rounded-2xl border border-neutral-800 shadow-sm">
    <span class="text-neutral-500 text-[10px] font-bold uppercase tracking-wider">{{ $label }}</span>
    <div class="text-xl font-extrabold mt-1 break-words {{ $warna }}">{{ $nilai }}</div>
  </div>
  @endforeach
</div>
@if($isAdmin)
<div class="grid gap-4 sm:grid-cols-2 mb-6">
  <div class="bg-ink-900 p-5 rounded-2xl border border-neutral-800">
    <div class="text-xs text-neutral-500">Piutang Pelanggan (Sisa Tagihan)</div>
    <div class="text-2xl font-extrabold text-amber-400 mt-2">Rp {{ number_format($totalPiutang,0,',','.') }}</div>
    <p class="text-xs text-neutral-500 mt-1">{{ $jumlahNotaPiutang }} nota dengan sisa tagihan</p>
  </div>
  <div class="bg-ink-900 p-5 rounded-2xl border border-neutral-800">
    <div class="text-xs text-neutral-500">Total Nilai Nota Lunas (Sejak Awal)</div>
    <div class="text-2xl font-extrabold text-white mt-2">Rp {{ number_format($totalOmzet,0,',','.') }}</div>
    <p class="text-xs text-neutral-500 mt-1">Nilai tagihan lunas, berbeda dengan uang masuk bulan ini</p>
  </div>
</div>
<div class="min-w-0 bg-ink-900 rounded-2xl border border-neutral-800 p-3 sm:p-5 shadow-sm mb-6">
  <h3 class="text-sm font-bold text-white">Tren Pemasukan — 30 Hari Terakhir</h3>
  <p class="text-xs text-neutral-500 mt-1 mb-4">Uang masuk per tanggal pembayaran, termasuk cicilan dan tips, setelah kembalian.</p>
  <div class="h-60 min-w-0 sm:h-72"><canvas id="sirakaPemasukanDash"></canvas></div>
</div>
@endif
<div class="grid gap-4 lg:grid-cols-2 mb-6">
 <div class="min-w-0 bg-ink-900 rounded-2xl border border-neutral-800 p-3 sm:p-5 shadow-sm">
   <h3 class="text-sm font-bold text-white">Status Pekerjaan Servis</h3>
   <p class="text-xs text-neutral-500 mt-1 mb-4">Jumlah servis per status yang tercatat saat ini.</p>
   <div class="h-60 min-w-0 sm:h-64"><canvas id="sirakaStatusDash"></canvas></div>
 </div>
 <div class="min-w-0 bg-ink-900 rounded-2xl border border-neutral-800 p-3 sm:p-5 shadow-sm min-w-0">
  <div class="flex items-start justify-between gap-3 mb-4">
   <div class="min-w-0"><h3 class="text-sm font-bold text-white">5 Sparepart Terlaris Bulan Ini</h3>
   <p class="text-[11px] text-neutral-500 mt-0.5">{{ now()->translatedFormat('F Y') }} · hanya dari transaksi lunas</p></div>
    @if($isAdmin)
    <a href="{{ route('admin.laporan.index', [
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->endOfMonth()->toDateString()
    ]) }}" class="shrink-0 text-xs font-bold text-brand-500 hover:underline">Analisis lengkap</a>
    @endif
  </div>
  @if($sparepartTerlaris->isNotEmpty())
    <div class="relative w-full" style="height: {{ max(210, $sparepartTerlaris->count() * 58 + 65) }}px;">
      <canvas id="sirakaSparepartDash"></canvas>
    </div>
    <p class="mt-3 text-[11px] text-neutral-500">
      Jumlah unit terjual ditampilkan pada ujung batang. Arahkan kursor untuk melihat nama lengkap@if($isAdmin) dan omzet@endif.
    </p>
  @else
    <p class="py-6 text-center text-xs text-neutral-500">Belum ada sparepart dari nota lunas bulan ini.</p>
  @endif
 </div>
</div>
<div class="min-w-0 bg-ink-900 rounded-2xl border border-neutral-800 p-3 sm:p-5 shadow-sm">
 <div class="flex items-center justify-between mb-4"><h3 class="text-sm font-bold text-white">Antrean Servis Terbaru</h3>
 <a href="{{ route('admin.rekam-servis.create') }}" class="text-xs font-bold text-brand-500 hover:underline">+ Input Servis Baru</a></div>
 <div class="overflow-x-auto -mx-5 px-5"><table class="w-full min-w-[640px] text-left text-xs text-neutral-400"><thead class="bg-black/40 border-b border-neutral-800 font-bold text-neutral-500"><tr>
 <th class="p-3">Tanggal</th><th class="p-3">Kendaraan</th><th class="p-3">Keluhan Awal</th><th class="p-3">Diagnosa Awal</th><th class="p-3 text-center">Status</th>
 </tr></thead><tbody class="divide-y divide-neutral-800">
 @forelse($antreanTerbaru as $item)<tr class="hover:bg-neutral-800/40 transition">
 <td class="p-3">{{ $item->tanggal_servis }}</td>
 <td class="p-3 font-bold text-white">{{ $item->kendaraan?->plat_nomor ?? '-' }} <span class="text-neutral-500 font-normal">({{ $item->kendaraan?->konsumen?->nama_lengkap ?? '-' }})</span></td>
 <td class="p-3">{{ $item->keluhan_awal }}</td><td class="p-3 text-brand-400 font-semibold">{{ $item->diagnosa_awal }}</td>
 <td class="p-3 text-center"><span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $item->status === 'selesai' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">{{ str_replace('_',' ',$item->status) }}</span></td></tr>
 @empty <tr><td colspan="5" class="p-8 text-center text-neutral-600">Belum ada aktivitas servis tercatat.</td></tr> @endforelse
 </tbody></table></div>
</div>
@endsection
@php
  // Menyiapkan JSON di PHP agar @json tidak harus membaca closure berlapis.
  // Omzet tidak dikirim ke browser pengguna Anggota.
  $sparepartGrafikDash = $sparepartTerlaris->map(fn ($sp) => [
      'nama' => (string) $sp->nama_barang,
      'unit' => (int) $sp->unit_terjual,
      'omzet' => $isAdmin ? (float) $sp->omzet : null,
  ])->values();
@endphp
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof Chart === 'undefined') return;
  const rupiah = x => 'Rp ' + Number(x || 0).toLocaleString('id-ID');
  const status = @json($statusServis);
  new Chart(document.getElementById('sirakaStatusDash'), {
    type: 'bar',
    data: { labels: Object.keys(status).map(x => x.replaceAll('_', ' ')), datasets: [{label:'Jumlah servis',data:Object.values(status),backgroundColor:'#fb923c'}] },
    options: { responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{ticks:{color:'#a3a3a3'}},y:{beginAtZero:true,ticks:{color:'#a3a3a3',precision:0}}} }
  });
  @if($isAdmin)
  const harian = @json($grafikPemasukan);
  new Chart(document.getElementById('sirakaPemasukanDash'), {
    type:'line',data:{labels:harian.map(x=>x.tanggal),datasets:[{label:'Pemasukan',data:harian.map(x=>x.total),borderColor:'#34d399',backgroundColor:'rgba(52,211,153,.12)',fill:true,tension:.3}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#d4d4d4'}},tooltip:{callbacks:{label:c=>rupiah(c.parsed.y)}}},scales:{x:{ticks:{color:'#a3a3a3',maxTicksLimit:8}},y:{beginAtZero:true,ticks:{color:'#a3a3a3',callback:v=>rupiah(v)}}}}
  });
  @endif

  const spareparts = @json($sparepartGrafikDash);
  const canvasSparepart = document.getElementById('sirakaSparepartDash');
  if (canvasSparepart && spareparts.length > 0) {
    function duaBaris(nama) {
      const batas = 21;
      const kata = String(nama).split(/\s+/);
      const hasil = [''];
      for (const k of kata) {
        const i = hasil.length - 1;
        const gabungan = (hasil[i] + ' ' + k).trim();
        if (gabungan.length <= batas) hasil[i] = gabungan;
        else if (hasil.length < 2) hasil.push(k);
        else {
          const sudah = hasil[1];
          const sisa = ' ' + k;
          hasil[1] = (sudah + sisa).length > batas
            ? (sudah.length > batas - 3 ? sudah.slice(0, batas - 3) : sudah).replace(/\s+$/, '') + '…'
            : sudah + sisa;
          break;
        }
      }
      return hasil.map(b => b.length > batas ? b.slice(0, batas - 1) + '…' : b);
    }

    const labelUnit = {
      id: 'sirakaLabelUnitDash',
      afterDatasetsDraw(chart) {
        const ctx = chart.ctx;
        const area = chart.chartArea;
        const batang = chart.getDatasetMeta(0).data;
        ctx.save();
        ctx.font = '600 11px Arial, sans-serif';
        ctx.fillStyle = '#f5f5f5';
        ctx.textBaseline = 'middle';
        batang.forEach((bar, i) => {
          const label = spareparts[i].unit + ' unit';
          const lebar = ctx.measureText(label).width;
          const ruang = area.right - bar.x;
          if (ruang >= lebar + 9) {
            ctx.textAlign = 'left';
            ctx.fillText(label, bar.x + 7, bar.y);
          } else {
            ctx.textAlign = 'right';
            ctx.fillText(label, bar.x - 7, bar.y);
          }
        });
        ctx.restore();
      }
    };

    new Chart(canvasSparepart, {
      type: 'bar',
      data: {
        labels: spareparts.map(sp => duaBaris(sp.nama)),
        datasets: [{
          label: 'Unit Terjual',
          data: spareparts.map(sp => sp.unit),
          backgroundColor: '#f97316',
          borderRadius: 5,
          barThickness: 18,
          maxBarThickness: 18
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: {top: 8, right: 14, bottom: 0, left: 2} },
        plugins: {
          legend: {display: false},
          tooltip: {
            callbacks: {
              title: items => spareparts[items[0].dataIndex].nama,
              label: item => {
                const sp = spareparts[item.dataIndex];
                const hasil = ['Terjual: ' + sp.unit + ' unit'];
                if (sp.omzet !== null) hasil.push('Omzet: ' + rupiah(sp.omzet));
                return hasil;
              }
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            suggestedMax: Math.max(...spareparts.map(sp => sp.unit)) + 1,
            ticks: {color:'#a3a3a3',precision:0,stepSize:1},
            grid: {color:'rgba(255,255,255,.06)'},
            title: {display:true,text:'Jumlah Unit Terjual',color:'#a3a3a3'}
          },
          y: {ticks:{color:'#e5e5e5',autoSkip:false,font:{size:11}},grid:{display:false}}
        }
      },
      plugins: [labelUnit]
    });
  }
});
</script>
@endpush