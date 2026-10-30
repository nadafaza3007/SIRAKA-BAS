@extends('layouts.admin')

@section('title', 'Data Konsumen - SIRAKA')

@section('content')

@php
    $input = 'w-full min-w-0 rounded-xl border border-neutral-700 bg-neutral-800/60 px-3 py-2.5 text-base sm:text-sm text-white placeholder:text-xs placeholder:font-normal placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30';
@endphp

{{-- HEADER --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-xl font-extrabold tracking-tight text-white">
            Data Konsumen dan Kendaraan
        </h2>
        <p class="mt-1 text-xs text-neutral-500">
            Kelola identitas konsumen dan kendaraan yang terhubung dengan masing-masing konsumen.
        </p>
    </div>

    <button type="button"
        onclick="openModal('modalTambahKonsumen')"
        class="inline-flex w-fit items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600">
        <i class="fa-solid fa-user-plus fa-fw"></i>
        <span>Tambah konsumen</span>
    </button>
</div>

{{-- TABEL --}}
<div class="overflow-hidden rounded-2xl border border-neutral-800 bg-ink-900 shadow-sm">
    <div class="overflow-x-auto overscroll-x-contain">

        <table class="w-full min-w-[900px] table-fixed text-left text-xs text-neutral-400">

            <colgroup>
                <col class="w-[220px]">
                <col class="w-[170px]">
                <col>
                <col class="w-[100px]">
                <col class="w-[120px]">
            </colgroup>

            <thead class="border-b border-neutral-800 bg-black/40 text-[11px] font-bold tracking-wide text-neutral-500">
                <tr>
                    <th class="px-4 py-3.5">Konsumen</th>
                    <th class="px-4 py-3.5">No. HP / WhatsApp</th>
                    <th class="px-4 py-3.5">Kendaraan terdaftar</th>
                    <th class="px-4 py-3.5 text-center">Jumlah</th>
                    <th class="px-4 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-neutral-800">

                @forelse ($konsumen as $item)
                    <tr class="transition hover:bg-neutral-800/40">

                        {{-- KONSUMEN --}}
                        <td class="px-4 py-4 align-middle">
                            <div class="truncate text-sm font-bold text-white">
                                {{ $item->nama_lengkap }}
                            </div>
                            <div class="mt-1 text-[11px] text-neutral-600">
                                ID #{{ $item->id }}
                            </div>
                        </td>

                        {{-- NO HP --}}
                        <td class="px-4 py-4 align-middle">
                            <div class="flex items-center gap-2 font-mono text-neutral-300">
                                <i class="fa-brands fa-whatsapp fa-fw text-sm text-emerald-500"></i>
                                <span class="truncate">{{ $item->no_hp }}</span>
                            </div>
                        </td>

                        {{-- KENDARAAN --}}
                        <td class="px-4 py-4 align-middle">
                            <div class="flex flex-wrap gap-2">
                                @forelse($item->kendaraans as $k)
                                    <div class="inline-flex items-center gap-2 rounded-lg border border-neutral-700 bg-neutral-800 px-2.5 py-1.5 text-[11px] leading-none">
                                        <span class="font-extrabold uppercase tracking-wide text-brand-400">{{ $k->plat_nomor }}</span>
                                        <span class="text-neutral-300">{{ $k->merk }} {{ $k->tipe_model }}</span>
                                        @if($k->tahun)
                                            <span class="text-neutral-500">{{ $k->tahun }}</span>
                                        @endif
                                    </div>
                                @empty
                                    <span class="inline-flex items-center gap-2 text-[11px] text-neutral-600">
                                        <i class="fa-solid fa-car-side fa-fw"></i>
                                        <span>Belum ada kendaraan</span>
                                    </span>
                                @endforelse
                            </div>
                        </td>

                        {{-- JUMLAH --}}
                        <td class="px-4 py-4 text-center align-middle">
                            <span class="text-base font-extrabold text-white">{{ $item->kendaraans->count() }}</span>
                            <span class="ml-1 text-[11px] text-neutral-500">unit</span>
                        </td>

                        {{-- AKSI --}}
                        <td class="px-4 py-4 text-right align-middle">
                            <a href="{{ route('admin.konsumen.edit', $item->id) }}"
                               class="inline-flex h-9 items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-brand-500/20 bg-brand-500/10 px-3.5 text-[11px] font-bold text-brand-400 transition hover:border-brand-500 hover:bg-brand-500 hover:text-white">
                                <i class="fa-solid fa-sliders text-[11px]" aria-hidden="true"></i>
                                <span class="leading-none">Kelola</span>
                            </a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center">
                                <div class="grid h-12 w-12 place-items-center rounded-full bg-neutral-800 text-neutral-600">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <p class="mt-3 font-bold text-neutral-400">Belum ada data konsumen</p>
                                <p class="mt-1 text-[11px] leading-5 text-neutral-600">
                                    Tambahkan konsumen beserta kendaraan pertamanya untuk mulai menggunakan sistem.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>
</div>

{{-- =========================================================
     MODAL TAMBAH KONSUMEN
========================================================== --}}
<div id="modalTambahKonsumen"
     role="dialog" aria-modal="true" aria-labelledby="judulModalKonsumen"
     onclick="if (event.target === this) closeModal('modalTambahKonsumen')"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4 backdrop-blur-sm">

    <div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-neutral-800 bg-ink-900 shadow-2xl">

        {{-- Header tetap --}}
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-neutral-800 px-6 py-4">
            <div>
                <h3 id="judulModalKonsumen" class="text-sm font-extrabold text-white">Tambah konsumen</h3>
                <p class="mt-1 text-[11px] text-neutral-500">
                    Isi identitas konsumen dan satu atau beberapa kendaraannya.
                </p>
            </div>
            <button type="button" onclick="closeModal('modalTambahKonsumen')"
                    class="grid h-8 w-8 place-items-center rounded-lg text-neutral-500 transition hover:bg-neutral-800 hover:text-white"
                    aria-label="Tutup">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('admin.konsumen.store') }}" method="POST" class="flex min-h-0 flex-1 flex-col text-xs">
            @csrf

            {{-- Isi yang bisa di-scroll --}}
            <div class="min-h-0 flex-1 space-y-6 overflow-y-auto px-6 py-5">

                <section>
                    <h4 class="text-xs font-bold text-white">Identitas konsumen</h4>
                    <p class="mt-1 text-[11px] leading-4 text-neutral-500">
                        Nomor HP dipakai untuk menghubungkan akun pelanggan dengan data konsumen.
                    </p>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="namaLengkapBaru" class="mb-1.5 block font-semibold text-neutral-300">Nama lengkap *</label>
                            <input id="namaLengkapBaru" type="text" name="nama_lengkap"
                                   value="{{ old('nama_lengkap') }}" required
                                   placeholder="Masukkan Nama Lengkap Pelanggan"
                                   class="{{ $input }} placeholder:text-xs">
                        </div>
                        <div>
                            <label for="noHpBaru" class="mb-1.5 block font-semibold text-neutral-300">No. HP / WhatsApp *</label>
                            <input id="noHpBaru" type="text" name="no_hp"
                                   value="{{ old('no_hp') }}" required
                                   placeholder="Masukkan No. HP / WhatsApp"
                                   class="{{ $input }} font-mono placeholder:text-xs">
                        </div>
                    </div>
                </section>

                <section>
                    <h4 class="text-xs font-bold text-white">Data kendaraan</h4>
                    <p class="mt-1 text-[11px] leading-4 text-neutral-500">
                        Minimal satu kendaraan. Kendaraan lain bisa ditambahkan sekarang atau nanti lewat menu Kelola.
                    </p>

                    <div id="kendaraanBaruContainer" class="mt-4 space-y-4"></div>

                    <button type="button" onclick="tambahFormKendaraan()"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-brand-500/40 bg-brand-500/5 px-4 py-3 font-bold text-brand-400 transition hover:border-brand-500 hover:bg-brand-500/10">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah kendaraan lagi</span>
                    </button>
                </section>
            </div>

            {{-- Footer tetap --}}
            <div class="flex shrink-0 justify-end gap-2 border-t border-neutral-800 px-6 py-4">
                <button type="button" onclick="closeModal('modalTambahKonsumen')"
                        class="rounded-xl bg-neutral-800 px-4 py-2.5 font-bold text-neutral-200 transition hover:bg-neutral-700">
                    Batal
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan data</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const INPUT = @json($input);
const TAHUN_MAKS = new Date().getFullYear() + 1;

/* Data lama (jika validasi gagal) supaya isian tidak hilang */
const KENDARAAN_LAMA = Object.values(@json(old('kendaraan', [])));

let nextIndex = 0;

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    modal.querySelector('input')?.focus();
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

const field = (label, control, span = '') =>
    `<div class="${span}">
        <label class="mb-1.5 block font-semibold text-neutral-300">${label}</label>
        ${control}
     </div>`;

function kendaraanCard(i) {
    const n = (key) => `kendaraan[${i}][${key}]`;

    const el = document.createElement('div');
    el.className = 'vehicle-item rounded-2xl border border-neutral-800 bg-black/20 p-4';

    el.innerHTML = `
        <div class="mb-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-400">
                    <i class="fa-solid fa-car text-xs"></i>
                </span>
                <span class="text-xs font-bold text-white" data-judul></span>
            </div>
            <button type="button" data-hapus
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-500/10 px-2.5 py-1.5 text-[11px] font-bold text-rose-400 transition hover:bg-rose-500 hover:text-white">
                <i class="fa-solid fa-trash text-[10px]"></i>
                <span>Hapus</span>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-6">
            
            ${field('Plat nomor *',
                `<input type="text" name="${n('plat_nomor')}" data-f="plat_nomor" required
                    placeholder="Masukkan Plat Nomor"
                    class="${INPUT} font-bold uppercase tracking-wide text-brand-400 placeholder:text-xs placeholder:font-normal placeholder:normal-case placeholder:tracking-normal">`,
                'col-span-2')}
            ${field('Merk *',
                `<input type="text" name="${n('merk')}" data-f="merk" required class="${INPUT}">`,
                'col-span-1 sm:col-span-2')}
            ${field('Tipe / model *',
                `<input type="text" name="${n('tipe_model')}" data-f="tipe_model" required class="${INPUT}">`,
                'col-span-1 sm:col-span-2')}
            ${field('Tahun',
                `<input type="number" name="${n('tahun')}" data-f="tahun" min="1900" max="${TAHUN_MAKS}" class="${INPUT}">`,
                'col-span-1')}
            ${field('CC mesin',
                `<input type="number" name="${n('cc')}" data-f="cc" min="300" max="10000" class="${INPUT}">`,
                'col-span-1')}
            ${field('Transmisi',
                `<select name="${n('transmisi')}" data-f="transmisi" class="${INPUT}">
                    <option value="">Pilih</option>
                    <option value="manual">Manual</option>
                    <option value="otomatis">Otomatis</option>
                    <option value="cvt">CVT</option>
                 </select>`,
                'col-span-1 sm:col-span-2')}
            ${field('Bahan bakar',
                `<select name="${n('jenis_bahan_bakar')}" data-f="jenis_bahan_bakar" class="${INPUT}">
                    <option value="">Pilih</option>
                    <option value="gasoline">Gasoline</option>
                    <option value="diesel">Diesel</option>
                 </select>`,
                'col-span-1 sm:col-span-2')}
        </div>
    `;

    el.querySelector('[data-hapus]').addEventListener('click', () => {
        el.remove();
        nomori();
    });

    return el;
}

/* Nomor urut dan tombol hapus diperbarui setiap ada perubahan */
function nomori() {
    const cards = document.querySelectorAll('#kendaraanBaruContainer .vehicle-item');

    cards.forEach((card, idx) => {
        card.querySelector('[data-judul]').textContent = 'Kendaraan ' + (idx + 1);
        card.querySelector('[data-hapus]').classList.toggle('hidden', cards.length === 1);
    });
}

function tambahFormKendaraan(data = {}) {
    const container = document.getElementById('kendaraanBaruContainer');
    const card = kendaraanCard(nextIndex++);

    /* Nilai diisi lewat properti DOM, bukan string HTML, supaya aman. */
    card.querySelectorAll('[data-f]').forEach(el => {
        el.value = data[el.dataset.f] ?? '';
    });

    container.appendChild(card);
    nomori();
}

document.addEventListener('DOMContentLoaded', () => {
    if (KENDARAAN_LAMA.length) {
        KENDARAAN_LAMA.forEach(d => tambahFormKendaraan(d));
    } else {
        tambahFormKendaraan();
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal('modalTambahKonsumen');
});

@if($errors->any() && old('nama_lengkap'))
document.addEventListener('DOMContentLoaded', () => openModal('modalTambahKonsumen'));
@endif
</script>
@endpush