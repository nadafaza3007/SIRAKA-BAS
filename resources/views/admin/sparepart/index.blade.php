@extends('layouts.admin')

@section('title', 'Master Suku Cadang - SIRAKA')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-extrabold text-white tracking-tight">Master Suku Cadang</h2>
        <p class="text-xs text-neutral-500">Kelola stok barang, harga modal, harga jual, dan margin keuntungan.</p>
    </div>
    <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 flex items-center gap-2 w-fit">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Suku Cadang</span>
    </button>
</div>

<!-- Tabel: tampil di layar >= sm -->
<div class="hidden sm:block bg-ink-900 rounded-2xl border border-neutral-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-neutral-400">
            <thead class="bg-black/40 border-b border-neutral-800 uppercase font-bold text-neutral-500">
                <tr>
                    <th class="p-4">Nama Barang</th>
                    <th class="p-4">Harga Modal</th>
                    <th class="p-4">Harga Jual</th>
                    <th class="p-4">Margin Untung</th>
                    <th class="p-4 text-center">Jenis</th>
                    <th class="p-4 text-center">Stok</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse ($spareparts as $item)
                    <tr class="hover:bg-neutral-800/40 transition">
                        <td class="p-4">
                            <div class="font-bold text-white">{{ $item->nama_barang }}</div>
                            <div class="text-[11px] text-neutral-500">Kode: {{ $item->kode_barang ?? '-' }} | Toko: {{ $item->nama_toko ?? '-' }}</div>
                        </td>
                        <td class="p-4">Rp {{ number_format($item->harga_modal, 0, ',', '.') }}</td>
                        <td class="p-4 font-semibold text-brand-400">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/10 text-emerald-400">
                                +Rp {{ number_format($item->harga_jual - $item->harga_modal, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $item->isBarangPo() ? 'bg-violet-500/10 text-violet-300' : 'bg-sky-500/10 text-sky-300' }}">{{ $item->isBarangPo() ? 'PO' : 'Stok Bengkel' }}</span>
                        </td>
                        <td class="p-4 text-center">
                            @if ($item->isBarangPo())
                                <span class="text-neutral-500">—</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $item->stok <= 2 ? 'bg-amber-500/10 text-amber-400' : 'bg-neutral-800 text-neutral-300' }}">{{ $item->stok }}</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
    <div class="flex justify-end items-center gap-3">

        <a href="{{ route('admin.sparepart.edit', $item->id) }}"
           class="text-sky-400 hover:text-sky-300 font-bold">
            Edit
        </a>

        <form action="{{ route('admin.sparepart.destroy', $item->id) }}"
              method="POST"
              onsubmit="return confirm('Hapus suku cadang ini?')">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="text-rose-500 hover:text-rose-400 font-bold">
                Hapus
            </button>
        </form>

    </div>
</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-neutral-600">Belum ada suku cadang terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Kartu list: tampil di mobile, mirror mockup Master Sparepart -->
<div class="sm:hidden space-y-3">
    @forelse ($spareparts as $item)
        <div class="bg-ink-900 rounded-2xl border border-neutral-800 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="px-1.5 py-0.5 rounded bg-neutral-800 text-neutral-400 text-[10px] font-bold">{{ $item->kode_barang ?? '-' }}</span>
                    <h3 class="font-bold text-white text-sm">{{ $item->nama_barang }}</h3>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->isBarangPo() ? 'bg-violet-500/10 text-violet-300' : ($item->stok <= 2 ? 'bg-rose-500/10 text-rose-400' : 'bg-emerald-500/10 text-emerald-400') }}">
                    {{ $item->isBarangPo() ? 'PO' : ($item->stok <= 0 ? 'Habis' : 'Tersedia') }}
                </span>
            </div>
            <div class="space-y-1 text-xs mb-3">
                <div class="flex justify-between text-neutral-500"><span>Supplier</span><span class="text-neutral-300 font-semibold">{{ $item->nama_toko ?? '-' }}</span></div>
                <div class="flex justify-between text-neutral-500"><span>Harga Modal</span><span class="text-neutral-300 font-semibold">Rp {{ number_format($item->harga_modal, 0, ',', '.') }}</span></div>
                <div class="flex justify-between text-neutral-500"><span>Harga Jual</span><span class="text-brand-400 font-bold">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</span></div>
                <div class="flex justify-between text-neutral-500"><span>Jenis Barang</span><span class="text-white font-bold">{{ $item->isBarangPo() ? 'Pre-Order (PO)' : 'Stok Bengkel' }}</span></div>
                <div class="flex justify-between text-neutral-500"><span>Stok Gudang</span><span class="text-white font-bold">{{ $item->isBarangPo() ? '— (tidak menggunakan stok)' : $item->stok . ' unit' }}</span></div>
                <div class="flex justify-between text-neutral-500"><span>Margin Untung</span><span class="text-emerald-400 font-bold">Rp {{ number_format($item->marginKeuntungan(), 0, ',', '.') }}</span></div>
            </div>
            <div class="flex gap-2 pt-2 border-t border-neutral-800">
                <a href="{{ route('admin.sparepart.edit', $item->id) }}"
                    class="w-full py-2 bg-sky-500/10 text-sky-400 rounded-xl font-bold text-xs text-center">
                    Edit
                </a>
                <form action="{{ route('admin.sparepart.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus suku cadang ini?')" class="w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2 bg-rose-500/10 text-rose-400 rounded-xl font-bold text-xs">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="p-8 text-center text-neutral-600 text-xs bg-ink-900 rounded-2xl border border-neutral-800">Belum ada suku cadang terdaftar.</div>
    @endforelse
</div>

<!-- Modal Tambah Suku Cadang -->
<div id="modalTambah" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-ink-900 border border-neutral-800 rounded-2xl w-full max-w-md p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 border-b border-neutral-800 pb-3">
            <h3 class="font-extrabold text-sm text-white">Tambah Suku Cadang</h3>
            <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-neutral-500 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="{{ route('admin.sparepart.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            @if ($errors->any())
                <div class="rounded-lg border border-rose-500/40 bg-rose-500/10 text-rose-300 p-3">{{ $errors->first() }}</div>
            @endif
            <div>
                <label class="block font-bold mb-1 text-neutral-300">Nama Barang *</label>
                <input type="text" name="nama_barang" required class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Oli Shell Helix 10W-40">
            </div>
            <div>
                <label class="block font-bold mb-1 text-neutral-300">Jenis Ketersediaan *</label>
                <select name="jenis_ketersediaan" id="jenisKetersediaan" required onchange="toggleStokSparepart()" class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white rounded-xl">
                    <option value="stok" {{ old('jenis_ketersediaan', 'stok') === 'stok' ? 'selected' : '' }}>Barang Stok Bengkel</option>
                    <option value="po" {{ old('jenis_ketersediaan') === 'po' ? 'selected' : '' }}>Barang Pre-Order (PO)</option>
                </select>
                <p class="mt-1 text-[11px] text-neutral-500">Barang PO tidak memiliki stok persediaan; Qty transaksi servis tetap wajib.</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold mb-1 text-neutral-300">Kode Barang</label>
                    <input type="text" name="kode_barang" class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="KOD-01">
                </div>
                <div id="wrapStokAwal">
                    <label class="block font-bold mb-1 text-neutral-300">Stok Awal *</label>
                    <input type="number" name="stok" id="stokAwal" min="0" required value="{{ old('stok', 0) }}" class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">

    <div>
        <label class="block font-bold mb-1 text-neutral-300">
            Harga Modal (Rp) *
        </label>

        <input type="text"
               id="modalPrice"
               inputmode="numeric"
               autocomplete="off"
               required
               placeholder="Contoh: 15.000"
               class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">

        <input type="hidden" name="harga_modal" id="modalPriceRaw">
    </div>

    <div>
        <label class="block font-bold mb-1 text-neutral-300">
            Harga Jual (Rp) *
        </label>

        <input type="text"
               id="jualPrice"
               inputmode="numeric"
               autocomplete="off"
               required
               placeholder="Contoh: 20.000"
               class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">

        <input type="hidden" name="harga_jual" id="jualPriceRaw">
    </div>

</div>
            <div class="p-2.5 rounded-xl bg-brand-500/10 text-brand-400 text-[11px] font-semibold flex justify-between border border-brand-500/20">
                <span>Estimasi Margin / Keuntungan:</span>
                <span id="labelMargin" class="font-bold">Rp 0</span>
            </div>
            <div>
                <label class="block font-bold mb-1 text-neutral-300">Nama Toko / Supplier</label>
                <input type="text" name="nama_toko" class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Sumber Makmur Motor">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-neutral-200 rounded-xl font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-bold shadow-md shadow-brand-500/20">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleStokSparepart() {
    const po = document.getElementById('jenisKetersediaan').value === 'po';
    const wrap = document.getElementById('wrapStokAwal');
    const stok = document.getElementById('stokAwal');
    wrap.classList.toggle('hidden', po);
    stok.disabled = po;
    stok.required = !po;
}
document.addEventListener('DOMContentLoaded', function () { toggleStokSparepart(); @if ($errors->any()) document.getElementById('modalTambah').classList.remove('hidden'); @endif });
// Mengubah angka menjadi format ribuan Indonesia
function formatAngka(angka) {
    return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Mengambil angka tanpa titik
function ambilAngka(nilai) {
    return nilai.replace(/\D/g, '');
}

// Format input harga modal
document.getElementById('modalPrice').addEventListener('input', function () {

    let angka = ambilAngka(this.value);

    this.value = formatAngka(angka);

    document.getElementById('modalPriceRaw').value = angka;

    hitungMargin();
});

// Format input harga jual
document.getElementById('jualPrice').addEventListener('input', function () {

    let angka = ambilAngka(this.value);

    this.value = formatAngka(angka);

    document.getElementById('jualPriceRaw').value = angka;

    hitungMargin();
});

// Menghitung margin otomatis
function hitungMargin() {

    const modal = Number(
        document.getElementById('modalPriceRaw').value
    ) || 0;

    const jual = Number(
        document.getElementById('jualPriceRaw').value
    ) || 0;

    const margin = jual - modal;

    document.getElementById('labelMargin').innerText =
        'Rp ' + margin.toLocaleString('id-ID');
}
</script>
@endsection