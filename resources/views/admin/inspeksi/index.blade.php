@extends('layouts.admin')
@section('title', 'Laporan Inspeksi - SIRAKA')
@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-xl font-extrabold tracking-tight text-white">
            Laporan Inspeksi Kendaraan
        </h2>
        <p class="mt-1 text-xs text-neutral-500">
            Kelola seluruh laporan inspeksi kendaraan yang telah dilakukan.
        </p>
    </div>
    <a href="{{ route('admin.inspeksi.create') }}"
       class="siraka-mobile-action flex w-full items-center justify-center gap-2 rounded-xl sm:w-fit bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600">
        <i class="fa-solid fa-plus"></i>
        <span>Buat Inspeksi Baru</span>
    </a>
</div>

{{-- HP: kartu yang mudah dibaca tanpa menggeser tabel. Tabel desktop tetap utuh. --}}
<div class="space-y-3 lg:hidden">
    @forelse($inspeksis as $inspeksi)
    <article class="siraka-mobile-card rounded-2xl border border-neutral-800 bg-ink-900 p-4 shadow-sm">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <div class="break-words text-sm font-bold text-white">{{ $inspeksi->nomor_inspeksi }}</div>
          <div class="mt-1 text-xs text-neutral-400">{{ $inspeksi->tanggal_inspeksi->format('d/m/Y') }}</div>
        </div>
        <span class="shrink-0 rounded-full bg-neutral-800 px-2 py-1 text-[11px] text-amber-400">★ {{ $inspeksi->rating_score }}/10</span>
      </div>
      <div class="mt-3 space-y-1 text-xs">
        <p class="break-words font-semibold text-white">{{ $inspeksi->nama_pelanggan_inspeksi }}</p>
        @if($inspeksi->plat_nomor_inspeksi || $inspeksi->merk_inspeksi || $inspeksi->tipe_model_inspeksi || $inspeksi->kendaraan)
            <p class="text-brand-400">{{ $inspeksi->plat_nomor_inspeksi ?: ($inspeksi->kendaraan?->plat_nomor ?? '-') }}</p>
            <p class="break-words text-neutral-400">{{ $inspeksi->merk_inspeksi ?? '' }} {{ $inspeksi->tipe_model_inspeksi ?? '' }}</p>
        @else
            <p class="text-neutral-500 italic">Data kendaraan tidak diisi</p>
        @endif
        <p class="pt-1 font-bold text-emerald-400">Rp {{ number_format($inspeksi->total_estimasi, 0, ',', '.') }}</p>
      </div>
      <div class="mt-4 flex gap-2">
        @php
            $waLink = $inspeksi->link_whatsapp_inspeksi;
        @endphp
        @if($waLink)
        <a href="{{ $waLink }}" target="_blank" class="siraka-mobile-action flex items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-xs text-emerald-400" aria-label="Hubungi via WhatsApp">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
        @endif
        <a href="{{ route('admin.inspeksi.show', $inspeksi->id) }}" class="siraka-mobile-action flex flex-1 items-center justify-center rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white">Lihat Detail</a>
        <button type="button" onclick="konfirmasiHapus({{ $inspeksi->id }})" class="siraka-mobile-action flex items-center justify-center rounded-xl border border-rose-500/30 px-4 py-2 text-xs text-rose-400" aria-label="Hapus inspeksi">Hapus</button>
      </div>
    </article>
    @empty
      <div class="rounded-xl border border-neutral-800 bg-ink-900 p-6 text-center text-xs text-neutral-500">Belum ada laporan inspeksi.</div>
    @endforelse
</div>
<div class="hidden overflow-hidden rounded-2xl border border-neutral-800 bg-ink-900 shadow-sm lg:block">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[950px] text-left text-xs text-neutral-400">
            <thead class="border-b border-neutral-800 bg-black/40 font-bold uppercase text-neutral-500">
                <tr>
                    <th class="p-4">No. Inspeksi</th>
                    <th class="p-4">Tanggal</th>
                    <th class="p-4">Konsumen</th>
                    <th class="p-4">Kendaraan</th>
                    <th class="p-4 text-center">Rating</th>
                    <th class="p-4 text-right">Total Estimasi</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse ($inspeksis as $inspeksi)
                    <tr class="transition hover:bg-neutral-800/40">
                        <td class="p-4">
                            <div class="font-bold text-white">{{ $inspeksi->nomor_inspeksi }}</div>
                            <div class="mt-1 text-[10px] text-neutral-600">ID #{{ $inspeksi->id }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-neutral-300">
                                {{ $inspeksi->tanggal_inspeksi->format('d/m/Y') }}
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-white">{{ $inspeksi->nama_pelanggan_inspeksi }}</div>
                            <div class="mt-1 text-[10px] text-neutral-600">{{ $inspeksi->nomor_wa_pelanggan_inspeksi }}</div>
                        </td>
                        <td class="p-4">
                            @if($inspeksi->plat_nomor_inspeksi || $inspeksi->merk_inspeksi || $inspeksi->tipe_model_inspeksi || $inspeksi->kendaraan)
                                <div class="font-extrabold uppercase tracking-wide text-brand-400">
                                    {{ $inspeksi->plat_nomor_inspeksi ?: ($inspeksi->kendaraan?->plat_nomor ?? '-') }}
                                </div>
                                <div class="mt-1 text-[10px] text-neutral-500">
                                    {{ $inspeksi->merk_inspeksi ?? '' }} {{ $inspeksi->tipe_model_inspeksi ?? '' }}
                                </div>
                            @else
                                <div class="text-neutral-500 italic text-[10px]">Data kendaraan tidak diisi</div>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <span class="inline-flex items-center gap-1 rounded-full border border-neutral-700 bg-neutral-800 px-2.5 py-1 text-[10px] font-bold text-neutral-300">
                                <i class="fa-solid fa-star text-yellow-500"></i>
                                {{ $inspeksi->rating_score }}/10
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <div class="font-bold text-emerald-400">
                                Rp {{ number_format($inspeksi->total_estimasi, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @php
            $waLink = $inspeksi->link_whatsapp_inspeksi;
        @endphp

                                @if($waLink)
                                <a href="{{ $waLink }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-[10px] font-bold text-emerald-400 transition hover:border-emerald-500 hover:bg-emerald-500 hover:text-white" title="Hubungi via WhatsApp">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                                @endif

                                <a href="{{ route('admin.inspeksi.show', $inspeksi->id) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg border border-brand-500/20 bg-brand-500/10 px-3 py-1.5 text-[10px] font-bold text-brand-400 transition hover:border-brand-500 hover:bg-brand-500 hover:text-white">
                                    <i class="fa-solid fa-eye"></i>
                                    Detail
                                </a>
                                <form id="formHapus{{ $inspeksi->id }}" action="{{ route('admin.inspeksi.destroy', $inspeksi->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                        <button type="button" onclick="konfirmasiHapus({{ $inspeksi->id }})" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-500/20 bg-rose-500/10 px-3 py-1.5 text-[10px] font-bold text-rose-400 transition hover:border-rose-500 hover:bg-rose-500 hover:text-white">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-neutral-800 text-neutral-600">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </div>
                                <p class="mt-3 font-bold text-neutral-400">Belum ada laporan inspeksi</p>
                                <p class="mt-1 text-[11px] leading-5 text-neutral-600">
                                    Buat laporan inspeksi pertama untuk memulai.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function konfirmasiHapus(id) {
    Swal.fire({
        title: 'Hapus Laporan?',
        text: 'Laporan inspeksi akan dihapus secara permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f97316',
        cancelButtonColor: '#525252',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#141414',
        color: '#e5e5e5',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formHapus' + id).submit();
        }
    });
}

@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false,
        background: '#141414',
        color: '#e5e5e5',
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}',
        background: '#141414',
        color: '#e5e5e5',
    });
@endif
</script>
@endpush
@endsection