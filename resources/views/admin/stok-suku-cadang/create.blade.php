@extends('layouts.admin')

@section('title', 'Barang Masuk - SIRAKA')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    <div>
        <a href="{{ route('admin.stok-suku-cadang.index') }}"
           class="text-xs text-brand-400 hover:text-brand-300">
            <i class="fa-solid fa-arrow-left mr-1"></i>
            Kembali ke Stok Suku Cadang
        </a>

        <h2 class="text-xl font-extrabold text-white mt-3">
            Tambah Barang Masuk
        </h2>

        <p class="text-xs text-neutral-500 mt-1">
            Catat penambahan persediaan suku cadang bengkel.
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

        <form action="{{ route('admin.stok-suku-cadang.store') }}"
              method="POST"
              class="space-y-5">

            @csrf

            <div>
                <label class="block text-xs font-bold text-neutral-300 mb-2">
                    Pilih Suku Cadang *
                </label>

                <select name="sparepart_id"
                        required
                        class="w-full p-3 bg-neutral-800 border border-neutral-700 text-white rounded-xl text-sm">

                    <option value="">-- Pilih Suku Cadang --</option>

                    @foreach($spareparts as $item)
                        <option value="{{ $item->id }}"
                            {{ old('sparepart_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->nama_barang }}
                            (Stok saat ini: {{ $item->stok }})
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-300 mb-2">
                    Jumlah Barang Masuk *
                </label>

                <input type="number"
                       name="jumlah"
                       min="1"
                       max="999999"
                       value="{{ old('jumlah') }}"
                       required
                       placeholder="Contoh: 10"
                       class="w-full p-3 bg-neutral-800 border border-neutral-700 text-white rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-300 mb-2">
                    Tanggal Barang Masuk *
                </label>

                <input type="date"
                       name="tanggal"
                       value="{{ old('tanggal', date('Y-m-d')) }}"
                       required
                       class="w-full p-3 bg-neutral-800 border border-neutral-700 text-white rounded-xl text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-300 mb-2">
                    Keterangan
                </label>

                <textarea name="keterangan"
                          rows="3"
                          placeholder="Contoh: Pembelian stok dari supplier."
                          class="w-full p-3 bg-neutral-800 border border-neutral-700 text-white rounded-xl text-sm">{{ old('keterangan') }}</textarea>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2 pt-3 border-t border-neutral-800">

                <a href="{{ route('admin.stok-suku-cadang.index') }}"
                   class="px-5 py-3 text-center rounded-xl bg-neutral-800 text-neutral-300 text-xs font-bold">
                    Batal
                </a>

                <button type="submit"
                        class="px-5 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    Simpan Barang Masuk
                </button>

            </div>

        </form>

    </div>
</div>
@endsection