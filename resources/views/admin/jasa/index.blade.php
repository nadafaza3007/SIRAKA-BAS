@extends('layouts.admin')

@section('title', 'Master Jasa - SIRAKA')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-extrabold text-white tracking-tight">Master Tarif Jasa Servis</h2>
        <p class="text-xs text-neutral-500">Kelola tarif standar jasa servis dan pengerjaan bengkel.</p>
    </div>
    <button onclick="document.getElementById('modalTambahJasa').classList.remove('hidden')" class="px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-500/20 flex items-center gap-2 w-fit">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Jasa Baru</span>
    </button>
</div>

<!-- Tabel: tampil di layar >= sm -->
<div class="hidden sm:block bg-ink-900 rounded-2xl border border-neutral-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-neutral-400">
            <thead class="bg-black/40 border-b border-neutral-800 uppercase font-bold text-neutral-500">
                <tr>
                    <th class="p-4">Nama Layanan Jasa</th>
                    <th class="p-4">Tarif Jasa</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse ($jasas as $item)
                    <tr class="hover:bg-neutral-800/40 transition">
                        <td class="p-4 font-bold text-white">{{ $item->nama_jasa }}</td>
                        <td class="p-4 font-semibold text-brand-400">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <form action="{{ route('admin.jasa.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus jasa ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-400 font-bold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center text-neutral-600">Belum ada tarif jasa tersimpan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Kartu list: tampil di mobile -->
<div class="sm:hidden space-y-3">
    @forelse ($jasas as $item)
        <div class="bg-ink-900 rounded-2xl border border-neutral-800 p-4 shadow-sm flex items-center justify-between">
            <div>
                <h3 class="font-bold text-white text-sm">{{ $item->nama_jasa }}</h3>
                <p class="text-brand-400 font-bold text-sm mt-0.5">Rp {{ number_format($item->harga, 0, ',', '.') }}</p>
                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400">{{ $item->status }}</span>
            </div>
            <form action="{{ route('admin.jasa.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus jasa ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        </div>
    @empty
        <div class="p-8 text-center text-neutral-600 text-xs bg-ink-900 rounded-2xl border border-neutral-800">Belum ada tarif jasa tersimpan.</div>
    @endforelse
</div>

<!-- Modal Tambah Jasa -->
<div id="modalTambahJasa" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-ink-900 border border-neutral-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4 border-b border-neutral-800 pb-3">
            <h3 class="font-extrabold text-sm text-white">Tambah Jasa Servis</h3>
            <button onclick="document.getElementById('modalTambahJasa').classList.add('hidden')" class="text-neutral-500 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="{{ route('admin.jasa.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold mb-1 text-neutral-300">Nama Jasa / Pekerjaan *</label>
                <input type="text" name="nama_jasa" required class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Masukkan Nama Jasa / Pekerjaan ">
            </div>
            <div>
                <label class="block font-bold mb-1 text-neutral-300">Tarif (Rp) *</label>
                <input
                    type="text"
                    id="harga_display"
                    inputmode="numeric"
                    autocomplete="off"
                    required
                    class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    placeholder="Masukkan Tarif (Rp)">
                <input type="hidden" name="harga" id="harga">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalTambahJasa').classList.add('hidden')" class="px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-neutral-200 rounded-xl font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-bold shadow-md shadow-brand-500/20">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hargaDisplay = document.getElementById('harga_display');
    const hargaInput = document.getElementById('harga');

    if (!hargaDisplay || !hargaInput) return;

    hargaDisplay.addEventListener('input', function () {
        // Ambil angka saja
        let angka = this.value.replace(/\D/g, '');

        // Format pemisah ribuan menggunakan titik
        this.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        // Nilai asli untuk dikirim ke database
        hargaInput.value = angka;
    });
});
</script>
@endsection