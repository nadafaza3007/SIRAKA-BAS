@extends('layouts.admin')

@section('title', 'Koreksi Diagnosa - SIRAKA')

@section('content')
<style>
.siraka-row { background: rgba(127,127,127,.10); border: 1px solid rgba(127,127,127,.22); }
</style>
<div class="max-w-xl bg-ink-900 p-6 rounded-2xl border border-neutral-800 shadow-sm">
    <h2 class="text-lg font-extrabold text-white mb-1">Koreksi Diagnosa Pasca Fisik Mekanik</h2>
    <p class="text-xs text-neutral-500 mb-4">Kendaraan: <span class="font-bold text-white">{{ $servis->kendaraan->plat_nomor }}</span> ({{ $servis->kendaraan->konsumen->nama_lengkap }})</p>

    <div class="siraka-row p-3 rounded-xl text-xs mb-4">
        <span class="text-neutral-500 font-semibold block text-[10px] uppercase">Keluhan Awal:</span>
        <span class="text-neutral-300 font-medium">{{ $servis->keluhan_awal }}</span>
        <span class="text-neutral-500 font-semibold block text-[10px] uppercase mt-2">Diagnosa Awal Sistem:</span>
        <span class="text-brand-400 font-bold">{{ $servis->diagnosa_awal }}</span>
    </div>

    <form action="{{ route('admin.rekam-servis.koreksi', $servis->id) }}" method="POST" class="space-y-4 text-xs">
        @csrf
        <div>
            <label class="block font-bold mb-1 text-neutral-300">Diagnosa Akhir (Temuan Mekanik) *</label>
            <input type="text" name="diagnosa_akhir" required value="{{ $servis->diagnosa_awal }}" class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <div>
            <label class="block font-bold mb-1 text-neutral-300">Tindakan Servis / Perbaikan *</label>
            <textarea name="tindakan_servis" rows="3" required class="w-full p-2.5 bg-neutral-800/60 border border-neutral-700 text-white placeholder-neutral-500 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Contoh: Penggantian kampas rem depan dan pengurasan minyak rem"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('admin.rekam-servis.index') }}" class="px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-neutral-200 rounded-xl font-bold">Batal</a>
            <button type="submit"
            style="background-color: #FB923C;"
            onmouseover="this.style.backgroundColor='#F97316'"
            onmouseout="this.style.backgroundColor='#FB923C'"
            class="px-4 py-2 text-white rounded-xl font-bold shadow-md">
            Simpan Diagnosa Akhir
        </button>
        </div>
    </form>
</div>
@endsection
