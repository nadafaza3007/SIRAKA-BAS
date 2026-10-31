@extends('layouts.admin')

@section('title', 'Kelola Konsumen - SIRAKA')

@section('content')

@php
    $input = 'w-full rounded-xl border border-neutral-700 bg-neutral-800/60 px-3 py-2.5 text-xs text-white placeholder-neutral-500 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30';
    $label = 'mb-1.5 block text-xs font-semibold text-neutral-300';

    // ✅ Helper: konversi cc ke Liter dengan koma
    $ccToLiter = function($cc) {
        if (!$cc) return '';
        return number_format($cc / 1000, 1, ',', '');
    };
@endphp

{{-- HEADER --}}
<div class="mb-6">
    <a href="{{ route('admin.konsumen.index') }}"
       class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-neutral-500 transition hover:text-white">
        <i class="fa-solid fa-arrow-left fa-fw"></i>
        <span>Kembali ke Data Konsumen</span>
    </a>

    <h2 class="text-xl font-extrabold tracking-tight text-white">
        Kelola {{ $konsumen->nama_lengkap }}
    </h2>
    <p class="mt-1 text-xs text-neutral-500">
        Perbarui identitas pelanggan dan kelola kendaraan yang terhubung.
    </p>
</div>

{{-- NOTIFIKASI --}}
@if($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-4">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation fa-fw mt-0.5 text-rose-400"></i>
            <div>
                <p class="text-xs font-bold text-rose-400">Data belum dapat disimpan.</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-[11px] text-rose-300">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="grid items-start gap-6 xl:grid-cols-3">

    {{-- =========================================================
         AREA UTAMA
    ========================================================== --}}
    <div class="space-y-6 xl:col-span-2">

        {{-- IDENTITAS + KENDARAAN TERDAFTAR --}}
        <section class="rounded-2xl border border-neutral-800 bg-ink-900 shadow-sm">

            <div class="flex items-center gap-3 border-b border-neutral-800 px-5 py-4">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-500/10 text-brand-400">
                    <i class="fa-solid fa-user"></i>
                </span>
                <div>
                    <h3 class="text-sm font-extrabold text-white">Identitas konsumen</h3>
                    <p class="mt-0.5 text-[11px] text-neutral-500">Data utama pelanggan bengkel.</p>
                </div>
            </div>

            <form action="{{ route('admin.konsumen.update', $konsumen->id) }}" method="POST" class="p-5">
                @csrf
                @method('PUT')

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="nama_lengkap" class="{{ $label }}">Nama lengkap *</label>
                        <input id="nama_lengkap" type="text" name="nama_lengkap"
                               value="{{ old('nama_lengkap', $konsumen->nama_lengkap) }}" required
                               class="{{ $input }}">
                    </div>

                    <div>
                        <label for="no_hp" class="{{ $label }}">No. HP / WhatsApp *</label>
                        <input id="no_hp" type="text" name="no_hp"
                               value="{{ old('no_hp', $konsumen->no_hp) }}" required
                               class="{{ $input }} font-mono">
                    </div>
                </div>

                {{-- KENDARAAN TERDAFTAR --}}
                @if($konsumen->kendaraans->isNotEmpty())

                    <div class="my-6 border-t border-neutral-800"></div>

                    <div class="mb-4">
                        <h4 class="text-sm font-extrabold text-white">Kendaraan terdaftar</h4>
                        <p class="mt-1 text-[11px] leading-5 text-neutral-500">
                            Mengubah plat, merk, atau model tidak menghapus riwayat servis karena riwayat terhubung lewat ID kendaraan.
                        </p>
                    </div>

                    <div class="space-y-4">
                        @foreach($konsumen->kendaraans as $kendaraan)
                            @php $p = 'kendaraan.' . $kendaraan->id . '.'; @endphp

                            <div class="rounded-2xl border border-neutral-800 bg-black/20 p-4">

                                <div class="mb-4 flex items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-neutral-800 text-brand-400">
                                        <i class="fa-solid fa-car-side"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-extrabold uppercase tracking-wide text-white">
                                            {{ $kendaraan->plat_nomor }}
                                        </p>
                                        <p class="mt-0.5 truncate text-[11px] text-neutral-500">
                                            {{ $kendaraan->merk }} {{ $kendaraan->tipe_model }} &middot; ID #{{ $kendaraan->id }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-6">

                                    <div class="col-span-2">
                                        <label class="{{ $label }}">Plat nomor *</label>
                                        <input type="text" name="kendaraan[{{ $kendaraan->id }}][plat_nomor]"
                                               value="{{ old($p . 'plat_nomor', $kendaraan->plat_nomor) }}" required
                                               class="{{ $input }} font-bold uppercase tracking-wide text-brand-400">
                                    </div>

                                    <div class="col-span-1 sm:col-span-2">
                                        <label class="{{ $label }}">Merk *</label>
                                        <input type="text" name="kendaraan[{{ $kendaraan->id }}][merk]"
                                               value="{{ old($p . 'merk', $kendaraan->merk) }}" required
                                               class="{{ $input }}">
                                    </div>

                                    <div class="col-span-1 sm:col-span-2">
                                        <label class="{{ $label }}">Tipe / model *</label>
                                        <input type="text" name="kendaraan[{{ $kendaraan->id }}][tipe_model]"
                                               value="{{ old($p . 'tipe_model', $kendaraan->tipe_model) }}" required
                                               class="{{ $input }}">
                                    </div>

                                    <div class="col-span-1">
                                        <label class="{{ $label }}">Tahun</label>
                                        <input type="number" name="kendaraan[{{ $kendaraan->id }}][tahun]"
                                               value="{{ old($p . 'tahun', $kendaraan->tahun) }}"
                                               min="1900" max="{{ date('Y') + 1 }}"
                                               class="{{ $input }}">
                                    </div>

                                    {{-- ✅ PERUBAHAN: CC dengan format Liter (2,2) --}}
                                    <div class="col-span-1">
    <label class="{{ $label }}">CC mesin (Liter)</label>
    <input type="text" 
           name="kendaraan[{{ $kendaraan->id }}][cc]"
           value="{{ old($p . 'cc', $kendaraan->cc ? number_format($kendaraan->cc, 1, ',', '') : '') }}"
           placeholder="Contoh: 2,2"
           class="{{ $input }}">
    <p class="mt-1 text-[10px] text-neutral-500">Dalam Liter, pakai koma (contoh: 1,5 / 2,0 / 2,4)</p>
</div>

                                    <div class="col-span-1 sm:col-span-2">
                                        <label class="{{ $label }}">Transmisi</label>
                                        <select name="kendaraan[{{ $kendaraan->id }}][transmisi]" class="{{ $input }}">
                                            <option value="">Pilih</option>
                                            @foreach(['manual' => 'Manual', 'otomatis' => 'Otomatis', 'cvt' => 'CVT'] as $val => $teks)
                                                <option value="{{ $val }}"
                                                    @selected(old($p . 'transmisi', $kendaraan->transmisi) === $val)>
                                                    {{ $teks }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-span-1 sm:col-span-2">
                                        <label class="{{ $label }}">Bahan bakar</label>
                                        <select name="kendaraan[{{ $kendaraan->id }}][jenis_bahan_bakar]" class="{{ $input }}">
                                            <option value="">Pilih</option>
                                            @foreach(['gasoline' => 'Gasoline', 'diesel' => 'Diesel'] as $val => $teks)
                                                <option value="{{ $val }}"
                                                    @selected(old($p . 'jenis_bahan_bakar', $kendaraan->jenis_bahan_bakar) === $val)>
                                                    {{ $teks }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex justify-end border-t border-neutral-800 pt-4">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan perubahan</span>
                    </button>
                </div>
            </form>
        </section>

        {{-- TAMBAH KENDARAAN --}}
        <section class="rounded-2xl border border-neutral-800 bg-ink-900 shadow-sm">

            <button type="button"
                id="tombolTambah"
                onclick="toggleTambahKendaraan()"
                aria-expanded="false"
                aria-controls="formTambahKendaraan"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left">

                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-plus"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold text-white">Tambah kendaraan</h3>
                        <p class="mt-0.5 text-[11px] text-neutral-500">
                            Hubungkan kendaraan baru ke konsumen ini.
                        </p>
                    </div>
                </div>

                <i id="toggleTambahIcon" class="fa-solid fa-chevron-down fa-fw text-neutral-500 transition-transform"></i>
            </button>

            <div id="formTambahKendaraan" class="hidden border-t border-neutral-800 p-5">
                <form action="{{ route('admin.konsumen.kendaraan.store', $konsumen->id) }}"
                      method="POST" class="space-y-4">
                    @csrf

                    <div id="kendaraanTambahContainer" class="space-y-4"></div>

                    <button type="button" onclick="tambahKendaraanBaru()"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-emerald-500/30 bg-emerald-500/5 px-4 py-2.5 text-xs font-bold text-emerald-400 transition hover:border-emerald-500 hover:bg-emerald-500/10">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah kendaraan lagi</span>
                    </button>

                    <div class="flex justify-end border-t border-neutral-800 pt-4">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-600">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Simpan kendaraan</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    {{-- =========================================================
         SIDEBAR
    ========================================================== --}}
    <aside class="space-y-4 xl:sticky xl:top-6">

        <div class="rounded-2xl border border-neutral-800 bg-ink-900 p-5">
            <h3 class="text-sm font-extrabold text-white">Ringkasan</h3>

            <dl class="mt-4 divide-y divide-neutral-800 text-xs">
                <div class="py-3 first:pt-0">
                    <dt class="text-neutral-500">Nama</dt>
                    <dd class="mt-1 font-semibold text-white">{{ $konsumen->nama_lengkap }}</dd>
                </div>

                <div class="py-3">
                    <dt class="text-neutral-500">No. HP</dt>
                    <dd class="mt-1 flex items-center gap-2 font-mono text-neutral-200">
                        <i class="fa-brands fa-whatsapp fa-fw text-sm text-emerald-500"></i>
                        <span>{{ $konsumen->no_hp }}</span>
                    </dd>
                </div>

                <div class="py-3 last:pb-0">
                    <dt class="text-neutral-500">Kendaraan</dt>
                    <dd class="mt-1">
                        <span class="text-xl font-extrabold text-white">{{ $konsumen->kendaraans->count() }}</span>
                        <span class="ml-1 text-neutral-400">unit</span>
                    </dd>
                </div>
            </dl>
        </div>

        <section class="rounded-2xl border border-neutral-800 bg-ink-900 p-5">
            <h3 class="text-sm font-extrabold text-white">Rekomendasi Terakhir per Kendaraan</h3>
            <p class="mt-1 text-xs text-neutral-400">Catatan rekomendasi yang belum ditangani.</p>
            <div class="mt-4 space-y-3">
                @forelse($konsumen->kendaraans as $kendaraan)
                    @php $catatan = $kendaraan->rekomendasiNotaTerbuka->first(); @endphp
                    <div class="rounded-xl border border-neutral-700 p-3">
                        <p class="text-xs font-bold text-white">{{ $kendaraan->plat_nomor }} · {{ $kendaraan->merk }} {{ $kendaraan->tipe_model }}</p>
                        @if($catatan)
                            <p class="mt-2 whitespace-pre-line text-xs text-amber-200">{{ $catatan->catatan_nota }}</p>
                        @else
                            <p class="mt-2 text-xs text-neutral-500">Tidak ada rekomendasi aktif.</p>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-neutral-500">Belum ada kendaraan.</p>
                @endforelse
            </div>
        </section>

        <div class="flex items-start gap-3 rounded-2xl border border-sky-500/20 bg-sky-500/5 p-4">
            <i class="fa-solid fa-circle-info fa-fw mt-0.5 text-sky-400"></i>
            <p class="text-[11px] leading-5 text-sky-300/80">
                Kendaraan yang ditambahkan di halaman ini otomatis tersedia sebagai pilihan plat saat membuat rekam servis baru.
            </p>
        </div>
    </aside>
</div>

@endsection

@push('scripts')
<script>
const INPUT = @json($input);
const TAHUN_MAKS = new Date().getFullYear() + 1;

let nextIndex = 0;

function toggleTambahKendaraan() {
    const form   = document.getElementById('formTambahKendaraan');
    const icon   = document.getElementById('toggleTambahIcon');
    const tombol = document.getElementById('tombolTambah');

    const buka = form.classList.toggle('hidden') === false;

    icon.classList.toggle('rotate-180', buka);
    tombol.setAttribute('aria-expanded', buka ? 'true' : 'false');
}

const field = (label, control, span = '') =>
    `<div class="${span}">
        <label class="mb-1.5 block text-xs font-semibold text-neutral-300">${label}</label>
        ${control}
     </div>`;

function kendaraanCard(i) {
    const n = (key) => `kendaraan[${i}][${key}]`;

    const el = document.createElement('div');
    el.className = 'vehicle-new rounded-2xl border border-neutral-800 bg-black/20 p-4';

    el.innerHTML = `
        <div class="mb-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-500/10 text-emerald-400">
                    <i class="fa-solid fa-plus text-xs"></i>
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
                `<input type="text" name="${n('plat_nomor')}" required placeholder="BM 1234 ABC"
                    class="${INPUT} font-bold uppercase tracking-wide text-brand-400">`,
                'col-span-2')}
            ${field('Merk *',
                `<input type="text" name="${n('merk')}" required placeholder="Toyota" class="${INPUT}">`,
                'col-span-1 sm:col-span-2')}
            ${field('Tipe / model *',
                `<input type="text" name="${n('tipe_model')}" required placeholder="Avanza" class="${INPUT}">`,
                'col-span-1 sm:col-span-2')}
            ${field('Tahun',
                `<input type="number" name="${n('tahun')}" min="1900" max="${TAHUN_MAKS}"
                    placeholder="${TAHUN_MAKS - 1}" class="${INPUT}">`,
                'col-span-1')}
            ${field('CC mesin (Liter)',
                `<input type="text" name="${n('cc')}" placeholder="Contoh: 2,2" class="${INPUT}">`,
                'col-span-1')}
            ${field('Transmisi',
                `<select name="${n('transmisi')}" class="${INPUT}">
                    <option value="">Pilih</option>
                    <option value="manual">Manual</option>
                    <option value="otomatis">Otomatis</option>
                    <option value="cvt">CVT</option>
                 </select>`,
                'col-span-1 sm:col-span-2')}
            ${field('Bahan bakar',
                `<select name="${n('jenis_bahan_bakar')}" class="${INPUT}">
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

function nomori() {
    const cards = document.querySelectorAll('#kendaraanTambahContainer .vehicle-new');

    cards.forEach((card, idx) => {
        card.querySelector('[data-judul]').textContent = 'Kendaraan baru ' + (idx + 1);
        card.querySelector('[data-hapus]').classList.toggle('hidden', cards.length === 1);
    });
}

function tambahKendaraanBaru() {
    document.getElementById('kendaraanTambahContainer')
        .appendChild(kendaraanCard(nextIndex++));
    nomori();
}

document.addEventListener('DOMContentLoaded', tambahKendaraanBaru);
</script>
@endpush