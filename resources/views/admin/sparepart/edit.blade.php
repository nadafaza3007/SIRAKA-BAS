@extends('layouts.admin')

@section('title', 'Edit Suku Cadang - SIRAKA')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    <div>
        <a href="{{ route('admin.sparepart.index') }}"
           class="text-xs text-brand-400">
            <i class="fa-solid fa-arrow-left mr-1"></i>
            Kembali ke Master Sparepart
        </a>

        <h2 class="text-xl font-extrabold text-white mt-3">
            Edit Suku Cadang
        </h2>
        <p class="text-xs text-neutral-500 mt-1">
            Perbarui informasi suku cadang.
        </p>
    </div>

    @if($errors->any())
        <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
            <ul class="list-disc pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-ink-900 border border-neutral-800 rounded-2xl p-5 sm:p-6">

        <form action="{{ route('admin.sparepart.update', $sparepart->id) }}"
              method="POST"
              class="space-y-4">

            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-neutral-300 mb-2">
                    Nama Barang *
                </label>
                <input type="text"
                       name="nama_barang"
                       required
                       value="{{ old('nama_barang', $sparepart->nama_barang) }}"
                       class="w-full p-3 bg-neutral-800 border border-neutral-700 text-white rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-300 mb-2">
                    Kode Barang
                </label>
                <input type="text"
                       name="kode_barang"
                       value="{{ old('kode_barang', $sparepart->kode_barang) }}"
                       class="w-full p-3 bg-neutral-800 border border-neutral-700 text-white rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-300 mb-2">
                    Supplier
                </label>
                <input type="text"
                       name="nama_toko"
                       value="{{ old('nama_toko', $sparepart->nama_toko) }}"
                       class="w-full p-3 bg-neutral-800 border border-neutral-700 text-white rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-300 mb-2">
                    Jenis Ketersediaan *
                </label>

                <select name="jenis_ketersediaan"
                        id="jenisKetersediaanEdit"
                        required
                        class="w-full p-3 bg-neutral-800 border border-neutral-700 text-white rounded-xl text-sm">

                    <option value="stok"
                        {{ old('jenis_ketersediaan', $sparepart->jenis_ketersediaan) === 'stok' ? 'selected' : '' }}>
                        Barang Stok Bengkel
                    </option>

                    <option value="po"
                        {{ old('jenis_ketersediaan', $sparepart->jenis_ketersediaan) === 'po' ? 'selected' : '' }}>
                        Barang Pre-Order (PO)
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-300 mb-2">
                    Stok Saat Ini
                </label>

                <input type="text"
                       value="{{ $sparepart->isBarangPo() ? 'Tidak menggunakan stok' : $sparepart->stok . ' unit' }}"
                       readonly
                       class="w-full p-3 bg-neutral-800/50 border border-neutral-700 text-neutral-400 rounded-xl text-sm">

                <p class="text-[11px] text-neutral-500 mt-1">
                    Perubahan jumlah stok dilakukan melalui menu Stok Suku Cadang.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs font-bold text-neutral-300 mb-2">
                        Harga Modal (Rp) *
                    </label>

                    <input type="text"
                           id="editHargaModal"
                           inputmode="numeric"
                           autocomplete="off"
                           value="{{ number_format((float) old('harga_modal', $sparepart->harga_modal), 0, ',', '.') }}"
                           class="format-rupiah w-full p-3 bg-neutral-800 border border-neutral-700 text-white rounded-xl text-sm"
                           required>

                    <input type="hidden"
                           name="harga_modal"
                           id="editHargaModalRaw"
                           value="{{ (int) old('harga_modal', $sparepart->harga_modal) }}">
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-300 mb-2">
                        Harga Jual (Rp) *
                    </label>

                    <input type="text"
                           id="editHargaJual"
                           inputmode="numeric"
                           autocomplete="off"
                           value="{{ number_format((float) old('harga_jual', $sparepart->harga_jual), 0, ',', '.') }}"
                           class="format-rupiah w-full p-3 bg-neutral-800 border border-neutral-700 text-white rounded-xl text-sm"
                           required>

                    <input type="hidden"
                           name="harga_jual"
                           id="editHargaJualRaw"
                           value="{{ (int) old('harga_jual', $sparepart->harga_jual) }}">
                </div>

            </div>

            <div class="p-3 rounded-xl bg-brand-500/10 text-brand-400 border border-brand-500/20 flex justify-between text-xs font-bold">
                <span>Estimasi Margin / Keuntungan</span>
                <span id="editMargin">Rp 0</span>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-neutral-800">

                <a href="{{ route('admin.sparepart.index') }}"
                   class="px-5 py-3 bg-neutral-800 text-neutral-300 rounded-xl text-xs font-bold">
                    Batal
                </a>

                <button type="submit"
                        class="px-5 py-3 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Perubahan
                </button>

            </div>

        </form>
    </div>
</div>

<script>
function angkaMentah(value) {
    return value.replace(/\D/g, '');
}

function formatRupiah(value) {
    const angka = angkaMentah(value);

    if (!angka) return '';

    return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function setupHargaEdit(inputId, hiddenId) {
    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);

    input.addEventListener('input', function () {
        const angka = angkaMentah(this.value);

        this.value = formatRupiah(angka);
        hidden.value = angka;
        hitungMarginEdit();
    });
}

function hitungMarginEdit() {
    const modal = Number(document.getElementById('editHargaModalRaw').value) || 0;
    const jual = Number(document.getElementById('editHargaJualRaw').value) || 0;

    document.getElementById('editMargin').innerText =
        'Rp ' + (jual - modal).toLocaleString('id-ID');
}

document.addEventListener('DOMContentLoaded', function () {
    setupHargaEdit('editHargaModal', 'editHargaModalRaw');
    setupHargaEdit('editHargaJual', 'editHargaJualRaw');
    hitungMarginEdit();
});
</script>
@endsection