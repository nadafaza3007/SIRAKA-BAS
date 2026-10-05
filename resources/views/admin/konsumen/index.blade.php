@extends('layouts.admin')

@section('title', 'Data Konsumen - SIRAKA')

@section('content')
<!-- Banner Notifikasi Alert (Sukses / Error Duplikasi) -->
@if(session('success'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-3 shadow-lg">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-extrabold text-white tracking-tight">Data Konsumen & Kendaraan</h2>
        <p class="text-xs text-neutral-500">Mendukung relasi satu konsumen ke banyak unit kendaraan (Multi-Unit).</p>
    </div>
    <button onclick="document.getElementById('modalTambahKonsumen').classList.remove('hidden')" class="px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 flex items-center gap-2 w-fit">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Konsumen Baru</span>
    </button>
</div>

<!-- Tabel Data Konsumen -->
<div class="bg-ink-900 rounded-2xl border border-neutral-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-neutral-400">
            <thead class="bg-black/40 border-b border-neutral-800 uppercase font-bold text-neutral-500">
                <tr>
                    <th class="p-4">Konsumen</th>
                    <th class="p-4">No. HP / WhatsApp</th>
                    <th class="p-4">Kendaraan Terdaftar</th>
                    <th class="p-4 text-right">Jumlah Unit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse ($konsumen as $item)
                    <tr class="hover:bg-neutral-800/40 transition">
                        <td class="p-4 font-bold text-white">{{ $item->nama_lengkap }}</td>
                        <td class="p-4 text-neutral-300 font-mono">{{ $item->no_hp }}</td>
                        <td class="p-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($item->kendaraans as $k)
                                    <span class="px-2.5 py-1 rounded-lg bg-neutral-800 text-white text-[11px] font-semibold border border-neutral-700">
                                        <span class="font-bold text-brand-400">{{ $k->plat_nomor }}</span> ({{ $k->merk }} {{ $k->tipe_model }})
                                    </span>
                                @empty
                                    <span class="text-neutral-600 italic text-[11px]">Belum ada kendaraan</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="p-4 text-right">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-neutral-800 text-neutral-300">
                                {{ $item->kendaraans->count() }} Unit
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-neutral-600">Belum ada data konsumen terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Konsumen & Kendaraan -->
<div id="modalTambahKonsumen" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-ink-900 border border-neutral-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 border-b border-neutral-800 pb-3">
            <h3 class="font-extrabold text-sm text-white">Tambah Konsumen & Kendaraan</h3>
            <button onclick="document.getElementById('modalTambahKonsumen').classList.add('hidden')" class="text-neutral-500 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="{{ route('admin.konsumen.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            
            <!-- Identitas Konsumen -->
            <div class="space-y-3">
                <span class="text-[10px] font-extrabold text-brand-400 uppercase tracking-wider block">1. Identitas Konsumen</span>
                <div>
                    <label class="block font-bold mb-1 text-neutral-300">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Contoh: Nada Faza Suhaila">
                </div>
                <div>
                    <label class="block font-bold mb-1 text-neutral-300">No. HP / WhatsApp *</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}" required class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="083166644732">
                </div>
            </div>

            <hr class="border-neutral-800 my-2">

            <!-- Identitas Kendaraan -->
            <div class="space-y-3">
                <span class="text-[10px] font-extrabold text-brand-400 uppercase tracking-wider block">2. Data Kendaraan Utama</span>
                <div>
                    <label class="block font-bold mb-1 text-neutral-300">Plat Nomor Kendaraan *</label>
                    <input type="text" name="kendaraan[0][plat_nomor]" required class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl uppercase font-bold text-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="BM 1234 XX">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1 text-neutral-300">Merk *</label>
                        <input type="text" name="kendaraan[0][merk]" required class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Toyota">
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-neutral-300">Tipe / Model *</label>
                        <input type="text" name="kendaraan[0][tipe_model]" required class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Avanza Veloz">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-neutral-800">
                <button type="button" onclick="document.getElementById('modalTambahKonsumen').classList.add('hidden')" class="px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-neutral-200 rounded-xl font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-bold shadow-md shadow-brand-500/20">Simpan Data</button>
            </div>
        </form>
    </div>
</div>
@endsection