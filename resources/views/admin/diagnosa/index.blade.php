@extends('layouts.admin')

@section('title', 'Master Diagnosa - SIRAKA')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-extrabold text-white tracking-tight">Aturan Keyword Matching Diagnosa</h2>
        <p class="text-xs text-neutral-500">Mendeteksi kata kunci keluhan untuk memberikan saran diagnosa otomatis.</p>
    </div>
    <button onclick="document.getElementById('modalTambahDiagnosa').classList.remove('hidden')" class="px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 flex items-center gap-2 w-fit">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Aturan Baru</span>
    </button>
</div>

<!-- Tabel: tampil di layar >= sm -->
<div class="hidden sm:block bg-ink-900 rounded-2xl border border-neutral-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-neutral-400">
            <thead class="bg-black/40 border-b border-neutral-800 uppercase font-bold text-neutral-500">
                <tr>
                    <th class="p-4">Kata Kunci Keluhan</th>
                    <th class="p-4">Diagnosa Otomatis Sistem</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse ($aturan as $item)
                    <tr class="hover:bg-neutral-800/40 transition">
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-md bg-brand-500/10 text-brand-400 font-semibold">
                                {{ $item->kata_kunci }}
                            </span>
                        </td>
                        <td class="p-4 font-bold text-white">{{ $item->diagnosa_terkait }}</td>
                        <td class="p-4 text-right">
                            <form action="{{ route('admin.diagnosa.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus aturan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-400 font-bold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-8 text-center text-neutral-600">Belum ada aturan kata kunci diagnosa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Kartu list: tampil di mobile -->
<div class="sm:hidden space-y-3">
    @forelse ($aturan as $item)
        <div class="bg-ink-900 rounded-2xl border border-neutral-800 p-4 shadow-sm">
            <span class="inline-block px-2.5 py-1 rounded-md bg-brand-500/10 text-brand-400 font-semibold text-xs mb-2">{{ $item->kata_kunci }}</span>
            <p class="font-bold text-white text-sm mb-3">{{ $item->diagnosa_terkait }}</p>
            <form action="{{ route('admin.diagnosa.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus aturan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-2 bg-rose-500/10 text-rose-400 rounded-xl font-bold text-xs">Hapus</button>
            </form>
        </div>
    @empty
        <div class="p-8 text-center text-neutral-600 text-xs bg-ink-900 rounded-2xl border border-neutral-800">Belum ada aturan kata kunci diagnosa.</div>
    @endforelse
</div>

<!-- Modal Tambah Aturan -->
<div id="modalTambahDiagnosa" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-ink-900 border border-neutral-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4 border-b border-neutral-800 pb-3">
            <h3 class="font-extrabold text-sm text-white">Tambah Aturan Diagnosa</h3>
            <button onclick="document.getElementById('modalTambahDiagnosa').classList.add('hidden')" class="text-neutral-500 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="{{ route('admin.diagnosa.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold mb-1 text-neutral-300">Kata Kunci Keluhan *</label>
                <input type="text" name="kata_kunci" required class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Masukkan Kata Kunci Keluhan">
                <span class="text-[10px] text-neutral-500 mt-1 block">Gunakan tanda koma untuk kata kunci jamak.</span>
            </div>
            <div>
                <label class="block font-bold mb-1 text-neutral-300">Diagnosa Terkait *</label>
                <input type="text" name="diagnosa_terkait" required class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Masukkan Diagnosa Terkait">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalTambahDiagnosa').classList.add('hidden')" class="px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-neutral-200 rounded-xl font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-bold shadow-md shadow-brand-500/20">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection