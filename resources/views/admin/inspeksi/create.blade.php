@extends('layouts.admin')
@section('title', 'Buat Laporan Inspeksi - SIRAKA')
@section('content')

{{-- Tampilan kartu detail inspeksi khusus HP; format field dan name tetap. --}}
<style>
@media (max-width: 639px) {
  [id^="container-"] > .inspeksi-detail-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    align-items: stretch;
    gap: 10px;
    padding: 12px;
    background: #0e0e0e;
    border: 1px solid #303030;
  }
  [id^="container-"] > .inspeksi-detail-row > div {
    min-width: 0;
    grid-column: 1 / -1;
  }
  [id^="container-"] > .inspeksi-detail-row input,
  [id^="container-"] > .inspeksi-detail-row select {
    display: block; width: 100%; min-width: 0;
    min-height: 44px; font-size: 14px;
    padding: 10px; text-overflow: ellipsis;
  }
  [id^="container-"] > .inspeksi-detail-row > div:last-child {
    display: flex; justify-content: flex-end;
  }
  [id^="container-"] > .inspeksi-detail-row > div:last-child button {
    min-height: 42px; min-width: 48px;
    border: 1px solid #4a202e; border-radius: 10px;
    background: rgba(244,63,94,.10);
  }
  #container-kaki_kaki > .inspeksi-detail-row > div:nth-child(2) {grid-column: 1;}
  #container-kaki_kaki > .inspeksi-detail-row > div:nth-child(3) {grid-column: 2;}
  #container-kaki_kaki > .inspeksi-detail-row > div:nth-child(2)::before {content: 'Kiri';}
  #container-kaki_kaki > .inspeksi-detail-row > div:nth-child(3)::before {content: 'Kanan';}
  #container-kaki_kaki > .inspeksi-detail-row > div:nth-child(2)::before,
  #container-kaki_kaki > .inspeksi-detail-row > div:nth-child(3)::before {
    display:block;color:#aaa;font-size:11px;margin-bottom:5px;
  }
  [id^="container-"] > .inspeksi-detail-row > div:first-child::before {
    content:'Komponen'; display:block; color:#aaa; font-size:11px; margin-bottom:5px;
  }
  #container-dokumen > .inspeksi-detail-row > div:first-child::before {content:'Dokumen';}
  [id^="container-"]:not(#container-kaki_kaki) > .inspeksi-detail-row > div:nth-child(2)::before {
    content:'Kondisi / Status'; display:block; color:#aaa; font-size:11px; margin-bottom:5px;
  }
  [id^="container-"]:not(#container-kaki_kaki) > .inspeksi-detail-row > div:nth-child(3)::before,
  #container-kaki_kaki > .inspeksi-detail-row > div:nth-child(4)::before {
    content:'Keterangan / Catatan'; display:block; color:#aaa; font-size:11px; margin-bottom:5px;
  }
}

.estimasi-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(0, 2fr) minmax(0, 2fr) minmax(0, 1.25fr) minmax(0, 1.25fr) 44px;
    gap: 10px;
    align-items: end;
}
.estimasi-field { min-width: 0; }
.estimasi-label { display: none; }
.estimasi-header { display: none; }
@media (min-width: 1024px) { .estimasi-header { display: grid; } }
.estimasi-field select,
.estimasi-field input { width: 100%; min-width: 0; min-height: 42px; }
@media (max-width: 1023px) {
    .estimasi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .estimasi-row { padding: 14px; border: 1px solid #333; background: #101010; }
    .estimasi-row > .estimasi-field:nth-child(-n+3) { grid-column: 1 / -1; }
    .estimasi-row > .estimasi-field:nth-child(4) { grid-column: 1; }
    .estimasi-row > .estimasi-field:nth-child(5) { grid-column: 2; }
    .estimasi-row > .estimasi-hapus { grid-column: 1 / -1; justify-self: end; }
    .estimasi-label { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 600; color: #a3a3a3; }
    .estimasi-field select,
    .estimasi-field input { min-height: 46px; padding: 10px 12px; font-size: 16px; }
    .estimasi-hapus button { min-height: 42px; padding: 0 14px; border: 1px solid #71313b; border-radius: 10px; background: rgba(244,63,94,.1); }
}
</style>

<div class="mb-6">
    <a href="{{ route('admin.inspeksi.index') }}" class="mb-4 inline-flex items-center gap-2 text-xs font-bold text-neutral-500 hover:text-white">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h2 class="text-xl font-extrabold text-white">Buat Laporan Inspeksi Kendaraan</h2>
    <p class="mt-1 text-xs text-neutral-500">Lengkapi data inspeksi sesuai format standar bengkel.</p>
</div>

<form action="{{ route('admin.inspeksi.store') }}" method="POST" class="space-y-6">
    @csrf

    {{-- 1. DATA UTAMA --}}
    <section class="rounded-2xl border border-neutral-800 bg-ink-900 p-5">
        <h3 class="mb-4 text-sm font-extrabold text-brand-400">1. Data Kendaraan & Umum</h3>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="mb-1 block text-[10px] font-bold text-neutral-400">Konsumen *</label>
                <select name="konsumen_id" id="selectKonsumen" required class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white">
                    <option value="">-- Pilih Konsumen --</option>
                    @foreach($konsumens as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_lengkap }} ({{ $k->no_hp }})</option>
                    @endforeach
                </select>
            </div>

            {{-- PILIH JENIS KENDARAAN --}}
            <div class="md:col-span-2 lg:col-span-3">
                <label class="mb-2 block text-[10px] font-bold text-neutral-400">Status Kendaraan *</label>
                <div class="flex gap-4">
                    <label class="flex cursor-pointer items-center gap-2 text-xs text-white">
                        <input type="radio" name="jenis_kendaraan" value="lama" checked onchange="toggleKendaraan('lama')" class="accent-brand-500">
                        Pilih dari Kendaraan Terdaftar
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-xs text-white">
                        <input type="radio" name="jenis_kendaraan" value="baru" onchange="toggleKendaraan('baru')" class="accent-brand-500">
                        Input Kendaraan Baru
                    </label>
                </div>
            </div>

            {{-- BOX: KENDARAAN LAMA --}}
            <div id="box-kendaraan-lama" class="md:col-span-2 lg:col-span-3">
                <label class="mb-1 block text-[10px] font-bold text-neutral-400">Kendaraan <span class="text-rose-500">*</span></label>
                <select name="kendaraan_id" id="selectKendaraan" required class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white">
                    <option value="">-- Pilih Konsumen Dulu --</option>
                </select>

                {{-- INFO KENDARAAN OTOMATIS --}}
                <div id="infoKendaraan" class="mt-3 hidden rounded-xl border border-brand-500/30 bg-brand-500/5 p-3">
                    <p class="mb-2 text-[10px] font-extrabold uppercase tracking-wider text-brand-400">Informasi Kendaraan Terpilih</p>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6 text-xs">
                        <div><p class="text-[10px] text-neutral-500">Plat Nomor</p><p id="infoPlat" class="font-extrabold uppercase text-brand-400">-</p></div>
                        <div><p class="text-[10px] text-neutral-500">Merk</p><p id="infoMerk" class="font-bold text-white">-</p></div>
                        <div><p class="text-[10px] text-neutral-500">Model/Tipe</p><p id="infoModel" class="font-bold text-white">-</p></div>
                        <div><p class="text-[10px] text-neutral-500">Tahun</p><p id="infoTahun" class="font-bold text-white">-</p></div>
                        <div><p class="text-[10px] text-neutral-500">CC</p><p id="infoCC" class="font-bold text-white">-</p></div>
                        <div><p class="text-[10px] text-neutral-500">Transmisi</p><p id="infoTransmisi" class="font-bold text-white">-</p></div>
                    </div>
                </div>
            </div>

            {{-- BOX: KENDARAAN BARU (SEMUA OPTIONAL) --}}
            <div id="box-kendaraan-baru" class="hidden md:col-span-2 lg:col-span-3 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="lg:col-span-3 text-[10px] text-neutral-500 italic">
                    ℹ️ Semua field di bawah ini opsional. Isi sesuai data yang tersedia.
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">Plat Nomor</label>
                    <input type="text" name="plat_nomor" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white uppercase" placeholder="B 1234 CD (opsional)">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">Merk</label>
                    <input type="text" name="merk" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white" placeholder="Toyota (opsional)">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">Tipe / Model</label>
                    <input type="text" name="tipe_model" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white" placeholder="Avanza (opsional)">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">Tahun</label>
                    <input type="number" name="tahun" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white" placeholder="2020 (opsional)">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">CC (Liter, cth: 2,2)</label>
                    <input type="text" name="cc" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white" placeholder="2,2 (opsional)">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">Transmisi</label>
                    <select name="transmisi" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white">
                        <option value="">Pilih (opsional)</option>
                        <option value="manual">Manual</option>
                        <option value="otomatis">Otomatis</option>
                        <option value="cvt">CVT</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-[10px] font-bold text-neutral-400">Tanggal Inspeksi *</label>
                <input type="date" name="tanggal_inspeksi" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold text-neutral-400">Kilometer (KM)</label>
                <input type="number" name="km_kendaraan" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white" placeholder="175000 (opsional)">
            </div>
            <div>
                <label class="mb-1 block text-[10px] font-bold text-neutral-400">Rating Score (1-10) *</label>
                <input type="number" name="rating_score" min="1" max="10" value="5" required class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">Ex Banjir</label>
                    <select name="ex_banjir" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white">
                        <option value="tidak">Tidak</option><option value="ya">Ya</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold text-neutral-400">Ex Tabrak</label>
                    <select name="ex_tabrak" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white">
                        <option value="tidak">Tidak</option><option value="ya">Ya</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-5">
            @php $kondisi = ['mesin', 'transmisi', 'kaki_kaki', 'eksterior', 'interior']; @endphp
            @foreach($kondisi as $k)
            <div>
                <label class="mb-1 block text-[10px] font-bold text-neutral-400">Kondisi {{ ucfirst(str_replace('_',' ',$k)) }}</label>
                <select name="kondisi_{{ $k }}" class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white">
                    <option value="baik">Baik</option><option value="cukup">Cukup</option><option value="buruk">Buruk</option>
                </select>
            </div>
            @endforeach
        </div>
    </section>

    {{-- 2. DETAIL INSPEKSI (DINAMIS) --}}
    <div class="grid gap-6 lg:grid-cols-2">
        @php
            $sections = [
                'kelistrikan' => 'Kelistrikan & Fitur Interior',
                'interior' => 'Kondisi Interior',
                'exterior' => 'Exterior',
                'mesin' => 'Mesin, Transmisi, Steering & Brake',
                'dokumen' => 'Dokumen Kendaraan',
            ];
        @endphp

        @foreach($sections as $key => $title)
        <section class="rounded-2xl border border-neutral-800 bg-ink-900 p-5">
            <div class="mb-3 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-sm font-extrabold text-brand-400">{{ $title }}</h3>
                <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto">
                    <button type="button" onclick="resetDefault('{{ $key }}')" class="text-[10px] font-bold text-sky-400 hover:text-sky-300">
                        <i class="fa-solid fa-rotate-left"></i> Reset Default
                    </button>
                    <button type="button" onclick="addRow('{{ $key }}')" class="text-[10px] font-bold text-emerald-400 hover:text-emerald-300">
                        + Tambah
                    </button>
                </div>
            </div>
            <div class="space-y-2 max-h-96 overflow-y-auto min-w-0" id="container-{{ $key }}"></div>
        </section>
        @endforeach

        {{-- Kaki-Kaki (Dengan Label Kiri & Kanan) --}}
        <section class="rounded-2xl border border-neutral-800 bg-ink-900 p-5 lg:col-span-2">
            <div class="mb-3 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-sm font-extrabold text-brand-400">Kaki-Kaki Depan & Belakang</h3>
                <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto">
                    <button type="button" onclick="resetDefault('kaki_kaki')" class="text-[10px] font-bold text-sky-400 hover:text-sky-300">
                        <i class="fa-solid fa-rotate-left"></i> Reset Default
                    </button>
                    <button type="button" onclick="addRow('kaki_kaki')" class="text-[10px] font-bold text-emerald-400 hover:text-emerald-300">
                        + Tambah
                    </button>
                </div>
            </div>
            <div class="mb-2 hidden sm:grid grid-cols-12 gap-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                <div class="col-span-4">Komponen</div>
                <div class="col-span-2 text-center">Kiri</div>
                <div class="col-span-2 text-center">Kanan</div>
                <div class="col-span-3">Catatan</div>
                <div class="col-span-1"></div>
            </div>
            <div class="space-y-2 max-h-96 overflow-y-auto min-w-0" id="container-kaki_kaki"></div>
        </section>
    </div>

    {{-- 3. ESTIMASI PERBAIKAN (REKOMENDASI) --}}
    <section class="rounded-2xl border border-neutral-800 bg-ink-900 p-4 sm:p-5">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h3 class="min-w-0 flex-1 text-sm font-extrabold text-brand-400">Estimasi Perbaikan (Rekomendasi)</h3>
            <button type="button" onclick="addEstimasi()"
                class="shrink-0 rounded-lg border border-emerald-500/30 px-3 py-2 text-xs font-bold text-emerald-400 hover:bg-emerald-500/10">
                <i class="fa-solid fa-plus mr-1"></i> Tambah Item
            </button>
        </div>

        <div class="estimasi-grid estimasi-header mb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-neutral-500">
            <div>Kategori</div>
            <div>Sparepart</div>
            <div>Jasa</div>
            <div class="text-right">Harga Part</div>
            <div class="text-right">Harga Jasa</div>
            <div></div>
        </div>

        <div class="space-y-3" id="container-estimasi"></div>

        <div class="mt-4 flex justify-end border-t border-neutral-800 pt-4">
            <div class="text-right">
                <p class="text-xs text-neutral-400">Grand Total Estimasi</p>
                <p class="mt-1 text-2xl font-extrabold text-emerald-400" id="grandTotal">Rp 0</p>
            </div>
        </div>
    </section>

    {{-- 4. CATATAN & SARAN --}}
    <section class="rounded-2xl border border-neutral-800 bg-ink-900 p-5">
        <h3 class="mb-3 text-sm font-extrabold text-brand-400">Saran</h3>
        <div>
            <textarea name="saran" rows="3" placeholder="Tulis saran untuk konsumen (opsional)..." class="w-full rounded-xl border border-neutral-700 bg-neutral-800 p-2.5 text-xs text-white"></textarea>
        </div>
    </section>

    <div class="flex justify-end gap-3 border-t border-neutral-800 pt-4">
        <a href="{{ route('admin.inspeksi.index') }}" class="rounded-xl bg-neutral-800 px-5 py-2.5 text-xs font-bold text-neutral-200 hover:bg-neutral-700">Batal</a>
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-brand-500/20 hover:bg-brand-600">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Laporan
        </button>
    </div>
</form>

@push('scripts')
<script>
// ============================================================
// DATA MASTER DARI PHP
// ============================================================
const spareparts = @json($spareparts);
const jasas = @json($jasas);
const konsumensData = @json($konsumensData);

// ============================================================
// KOMPONEN DEFAULT
// ============================================================
const defaultKomponen = {
    kelistrikan: [
        'Kisi-kisi AC', 'Headunit/audio', 'Camera mundur', 'Sensor parkir',
        'Tombol/knob AC', 'Speed Blower AC', 'Blower AC belakang',
        'Suhu AC (derajat celcius)', 'Lampu Kabin', 'Wipper', 'Air Washer',
        'Klakson', 'Lampu Hazard', 'Lampu Kota Depan', 'Lampu besar dekat',
        'Lampu besar jauh/dim', 'Lampu foglamp', 'Lampu sein', 'Lampu rem',
        'Lampu Plat No', 'Door Lock', 'Jendela P/W Depan Kanan',
        'Jendela P/W Depan Kiri', 'Jendela P/W Belakang Kanan',
        'Jendela P/W Belakang Kiri', 'Retrack Spion', 'Mirror Kanan',
        'Mirror Kiri', 'Steering Switch', 'Sliding Jok Elektrik (jika ada)',
        'Sliding Door Elektrik (jika ada)'
    ],
    interior: [
        'Kondisi Dashboard', 'Kondisi Steering', 'Kondisi Lever Transmisi',
        'Kondisi Karpet Dasar', 'Kondisi Jok Depan R/L', 'Kondisi Jok Belakang',
        'Kondisi Door Trim', 'Kondisi Plafon', 'Dongkrak', 'Kunci Roda', 'APAR & P3K'
    ],
    exterior: [
        'Keseluruhan Body (%)', 'Body Repair (jika ada)', 'Kaca Film',
        'Kap mesin', 'Tulang Bullhead', 'Tulang Bumper Depan',
        'Rangka/Chassis Depan', 'Bumper Depan', 'Fender Kanan & Kiri',
        'Pilar A', 'Pintu Depan Kanan', 'Pintu Belakang Kanan',
        'Pintu Depan Kiri', 'Pintu Belakang Kiri', 'Pilar B',
        'Fender Belakang Kanan', 'Fender Belakang Kiri', 'Pilar C',
        'Pintu Bagasi', 'Bumper Belakang', 'Rangka Belakang',
        'Rangka Bawah KKB', 'Atap', 'Mika Lampu Depan', 'Mika Lampu Belakang'
    ],
    mesin: [
        'Oli Mesin', 'Cylinder Head', 'Oil Pump', 'Carter Mesin',
        'Oli Transmisi (Matic)', 'Carter TM Matic', 'Air Coolant',
        'Radiator & Tutup', 'Extra Fan', 'Minyak Rem', 'Kampas Rem',
        'Disc Brake', 'Master Rem', 'Selang Rem', 'Power Steering',
        'Oli Power Steering', 'Timing Belt/Chain', 'Drive Belt',
        'Busi', 'Kabel Busi', 'Coil', 'Filter Udara', 'Filter Bensin',
        'Throttle Body', 'Injector', 'Fuel Pump'
    ],
    dokumen: [
        'Kepemilikan PT', 'Kepemilikan Perorangan', 'Tangan ke',
        'BPKB', 'STNK', 'FotoCopy KTP', 'Faktur', 'Manual Book',
        'Book Service', 'Service Record', 'No Mesin', 'No Rangka',
        'Pajak', 'Samsat Online', 'Tilang ETLE'
    ],
    kaki_kaki: [
        'Ban Depan', 'Ban Belakang', 'Ban Spare',
        'Suspensi Depan', 'Suspensi Belakang',
        'Arm Kanan & Kiri', 'Rack Steer', 'Tie Rod',
        'Link Stabilizer', 'Output Shaft', 'Input Shaft',
        'Propeller Shaft (Jika ada)', 'Spider Joint (Jika ada)',
        'Support Shock Depan', 'Trailink Arm Belakang'
    ]
};

// ============================================================
// TOGGLE KENDARAAN LAMA / BARU
// ============================================================
function toggleKendaraan(jenis) {
    const boxLama = document.getElementById('box-kendaraan-lama');
    const boxBaru = document.getElementById('box-kendaraan-baru');
    const selectKendaraan = document.getElementById('selectKendaraan');

    if (jenis === 'lama') {
        boxLama.classList.remove('hidden');
        boxBaru.classList.add('hidden');
        selectKendaraan.setAttribute('required', 'required');
    } else {
        boxLama.classList.add('hidden');
        boxBaru.classList.remove('hidden');
        selectKendaraan.removeAttribute('required');
        selectKendaraan.value = '';
        document.getElementById('infoKendaraan').classList.add('hidden');
    }
}

// ============================================================
// LOGIC PILIH KONSUMEN -> KENDARAAN
// ============================================================
document.getElementById('selectKonsumen').addEventListener('change', function() {
    const kSelect = document.getElementById('selectKendaraan');
    const konsumenId = parseInt(this.value);

    kSelect.innerHTML = '<option value="">-- Pilih Kendaraan --</option>';
    document.getElementById('infoKendaraan').classList.add('hidden');

    if (!konsumenId) return;

    const konsumen = konsumensData.find(k => k.id === konsumenId);
    if (!konsumen || !konsumen.kendaraans.length) {
        kSelect.innerHTML = '<option value="">Tidak ada kendaraan</option>';
        return;
    }

    konsumen.kendaraans.forEach(k => {
        const opt = document.createElement('option');
        opt.value = k.id;
        opt.textContent = `${k.plat_nomor} - ${k.merk} ${k.tipe_model}`;
        opt.dataset.kendaraan = JSON.stringify(k);
        kSelect.appendChild(opt);
    });
});

// ============================================================
// LOGIC PILIH KENDARAAN -> TAMPILKAN INFO
// ============================================================
document.getElementById('selectKendaraan').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const infoBox = document.getElementById('infoKendaraan');

    if (!selected.dataset.kendaraan) {
        infoBox.classList.add('hidden');
        return;
    }

    const k = JSON.parse(selected.dataset.kendaraan);
    document.getElementById('infoPlat').textContent = k.plat_nomor || '-';
    document.getElementById('infoMerk').textContent = k.merk || '-';
    document.getElementById('infoModel').textContent = k.tipe_model || '-';
    document.getElementById('infoTahun').textContent = k.tahun || '-';
    document.getElementById('infoCC').textContent = k.cc ? k.cc + ' cc' : '-';
    document.getElementById('infoTransmisi').textContent = k.transmisi_label || '-';

    infoBox.classList.remove('hidden');
});

// ============================================================
// TAMBAH BARIS DETAIL INSPEKSI
// ============================================================
function addRow(type, komponenName = '', status = 'Baik', keterangan = '') {
    const container = document.getElementById(`container-${type}`);
    const index = container.children.length;
    let html = '';

    if (type === 'kaki_kaki') {
        html = `
        <div class="inspeksi-detail-row grid grid-cols-12 gap-2 rounded-xl bg-black/20 p-3 items-center">
            <div class="col-span-4">
                <input type="text" name="${type}[${index}][komponen]" value="${komponenName}" placeholder="Komponen"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white">
            </div>
            <div class="col-span-2">
                <select name="${type}[${index}][status_kiri]" class="w-full rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white">
                    <option ${status==='Baik'?'selected':''}>Baik</option>
                    <option ${status==='Cukup'?'selected':''}>Cukup</option>
                    <option ${status==='Buruk'?'selected':''}>Buruk</option>
                    <option ${status==='N/A'?'selected':''}>N/A</option>
                </select>
            </div>
            <div class="col-span-2">
                <select name="${type}[${index}][status_kanan]" class="w-full rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white">
                    <option ${status==='Baik'?'selected':''}>Baik</option>
                    <option ${status==='Cukup'?'selected':''}>Cukup</option>
                    <option ${status==='Buruk'?'selected':''}>Buruk</option>
                    <option ${status==='N/A'?'selected':''}>N/A</option>
                </select>
            </div>
            <div class="col-span-3">
                <input type="text" name="${type}[${index}][catatan]" value="${keterangan}" placeholder="Catatan"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.grid').remove()" class="text-rose-400 hover:text-rose-300">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>`;
    } else if (type === 'dokumen') {
        html = `
        <div class="inspeksi-detail-row grid grid-cols-12 gap-2 rounded-xl bg-black/20 p-3 items-center">
            <div class="col-span-4">
                <input type="text" name="${type}[${index}][dokumen]" value="${komponenName}" placeholder="Dokumen"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white">
            </div>
            <div class="col-span-3">
                <select name="${type}[${index}][status]" class="w-full rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white">
                    <option ${status==='Ya'?'selected':''}>Ya</option>
                    <option ${status==='Tidak'?'selected':''}>Tidak</option>
                    <option ${status==='N/A'?'selected':''}>N/A</option>
                </select>
            </div>
            <div class="col-span-4">
                <input type="text" name="${type}[${index}][keterangan]" value="${keterangan}" placeholder="Keterangan"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.grid').remove()" class="text-rose-400 hover:text-rose-300">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>`;
    } else {
        html = `
        <div class="inspeksi-detail-row grid grid-cols-12 gap-2 rounded-xl bg-black/20 p-3 items-center">
            <div class="col-span-4">
                <input type="text" name="${type}[${index}][komponen]" value="${komponenName}" placeholder="Komponen"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white">
            </div>
            <div class="col-span-3">
                <select name="${type}[${index}][status]" class="w-full rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white">
                    <option ${status==='Baik'?'selected':''}>Baik</option>
                    <option ${status==='Cukup'?'selected':''}>Cukup</option>
                    <option ${status==='Buruk'?'selected':''}>Buruk</option>
                    <option ${status==='N/A'?'selected':''}>N/A</option>
                </select>
            </div>
            <div class="col-span-4">
                <input type="text" name="${type}[${index}][keterangan]" value="${keterangan}" placeholder="Keterangan"
                    class="w-full rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.grid').remove()" class="text-rose-400 hover:text-rose-300">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>`;
    }
    container.insertAdjacentHTML('beforeend', html);
}

// ============================================================
// RESET / ISI DEFAULT KOMPONEN
// ============================================================
function resetDefault(type) {
    const container = document.getElementById(`container-${type}`);
    container.innerHTML = '';
    const defaults = defaultKomponen[type] || [];
    defaults.forEach(komponen => {
        const defaultStatus = type === 'dokumen' ? 'Ya' : 'Baik';
        addRow(type, komponen, defaultStatus, '');
    });
}

// ============================================================
// TAMBAH BARIS ESTIMASI
// ============================================================
function addEstimasi() {
    const container = document.getElementById('container-estimasi');
    const index = container.children.length;

    let spOptions = '<option value="">-- Pilih Sparepart --</option>';
    spareparts.forEach(sp => spOptions += `<option value="${sp.id}" data-harga="${sp.harga_jual}" data-nama="${sp.nama_barang}">${sp.nama_barang}</option>`);

    let jasaOptions = '<option value="">-- Pilih Jasa --</option>';
    jasas.forEach(j => jasaOptions += `<option value="${j.id}" data-harga="${j.harga}" data-nama="${j.nama_jasa}">${j.nama_jasa}</option>`);

    const html = `
    <div class="estimasi-row estimasi-grid rounded-xl bg-black/20 p-3">
        <div class="estimasi-field">
            <label class="estimasi-label">Kategori</label>
            <select name="estimasi[${index}][kategori]" class="kategori-select rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white" onchange="toggleEstimasiFields(this)">
                <option value="">-- Pilih Kategori --</option>
                <option value="engine">Mesin</option>
                <option value="kaki_kaki">Kaki-Kaki</option>
                <option value="kelistrikan">Kelistrikan</option>
                <option value="interior">Interior</option>
                <option value="exterior">Exterior</option>
            </select>
        </div>
        <div class="estimasi-field">
            <label class="estimasi-label">Sparepart</label>
            <select name="estimasi[${index}][sparepart_id]" onchange="fillEstimasi(this, 'sp')" class="sp-select rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white" disabled>
                ${spOptions}
            </select>
        </div>
        <div class="estimasi-field">
            <label class="estimasi-label">Jasa</label>
            <select name="estimasi[${index}][jasa_id]" onchange="fillEstimasi(this, 'jasa')" class="jasa-select rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-xs text-white" disabled>
                ${jasaOptions}
            </select>
        </div>
        <div class="estimasi-field">
            <label class="estimasi-label">Harga Part (Rp)</label>
            <input type="number" name="estimasi[${index}][harga_part]" value="0" onchange="calcTotal()" oninput="calcTotal()" class="harga-part rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-right text-xs text-white" readonly>
        </div>
        <div class="estimasi-field">
            <label class="estimasi-label">Harga Jasa (Rp)</label>
            <input type="number" name="estimasi[${index}][harga_jasa]" value="0" onchange="calcTotal()" oninput="calcTotal()" class="harga-jasa rounded-lg border border-neutral-700 bg-neutral-800 p-2 text-right text-xs text-white" readonly>
        </div>
        <div class="estimasi-hapus flex justify-center lg:justify-end">
            <button type="button" onclick="this.closest('.estimasi-row').remove(); calcTotal()" class="text-rose-400 hover:text-rose-300" aria-label="Hapus item estimasi" title="Hapus item">
                <i class="fa-solid fa-trash"></i><span class="ml-2 text-xs font-semibold lg:hidden">Hapus</span>
            </button>
        </div>
        <input type="hidden" name="estimasi[${index}][nama_item]" class="nama-item">
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
}

function toggleEstimasiFields(select) {
    const row = select.closest('.estimasi-row');
    const spSelect = row.querySelector('.sp-select');
    const jasaSelect = row.querySelector('.jasa-select');
    const hargaPart = row.querySelector('.harga-part');
    const hargaJasa = row.querySelector('.harga-jasa');
    const namaItem = row.querySelector('.nama-item');

    if (select.value === "") {
        spSelect.disabled = true;
        jasaSelect.disabled = true;
        spSelect.value = "";
        jasaSelect.value = "";
        hargaPart.value = 0;
        hargaJasa.value = 0;
        namaItem.value = "";
    } else {
        spSelect.disabled = false;
        jasaSelect.disabled = false;
    }
    calcTotal();
}

function fillEstimasi(select, type) {
    const row = select.closest('.estimasi-row');
    const option = select.options[select.selectedIndex];
    const namaInput = row.querySelector('.nama-item');

    if (type === 'sp') {
        if(option.value) {
            namaInput.value = option.dataset.nama;
            row.querySelector('.harga-part').value = option.dataset.harga;
        } else {
            namaInput.value = '';
            row.querySelector('.harga-part').value = 0;
        }
    } else {
        if(option.value) {
            const currentNama = namaInput.value;
            namaInput.value = currentNama ? currentNama + ' + ' + option.dataset.nama : option.dataset.nama;
            row.querySelector('.harga-jasa').value = option.dataset.harga;
        } else {
            row.querySelector('.harga-jasa').value = 0;
        }
    }
    calcTotal();
}

function calcTotal() {
    let grand = 0;
    document.querySelectorAll('.estimasi-row').forEach(row => {
        const hp = parseFloat(row.querySelector('.harga-part').value) || 0;
        const hj = parseFloat(row.querySelector('.harga-jasa').value) || 0;
        grand += hp + hj;
    });
    document.getElementById('grandTotal').innerText = 'Rp ' + grand.toLocaleString('id-ID');
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    ['kelistrikan', 'interior', 'exterior', 'mesin', 'dokumen', 'kaki_kaki'].forEach(resetDefault);
    addEstimasi();
});
</script>
@endpush
@endsection