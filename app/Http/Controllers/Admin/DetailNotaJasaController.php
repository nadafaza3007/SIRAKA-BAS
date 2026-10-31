<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekamServis;
use App\Models\NotaTransaksi;
use App\Models\Sparepart;
use App\Models\Jasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetailNotaJasaController extends Controller
{
    public function tambah($id, Request $request)
    {
    abort_unless(in_array(auth()->user()->role, ['admin','anggota'], true), 403);
    $servis = RekamServis::findOrFail($id);
    
    if ($servis->status === 'selesai' || NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()) {
        return back()->with('error', 'Transaksi sudah dikunci.');
    }

    $data = $request->validate([
        'jasa_id' => ['required', 'exists:jasas,id'],
        'harga_custom' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
        'diskon_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
        'diskon_nominal' => ['nullable', 'string'],
    ]);

    $jasa = Jasa::findOrFail($data['jasa_id']);
    $harga = $request->filled('harga_custom') ? (float) $data['harga_custom'] : (float) $jasa->harga;
    $qty = 1;
    $bruto = $harga;

    // Konversi diskon
    $diskonPersen = (float) ($data['diskon_persen'] ?? 0);
    $diskonNominalRaw = str_replace('.', '', $data['diskon_nominal'] ?? '0');
    $diskonNominal = is_numeric($diskonNominalRaw) ? (float) $diskonNominalRaw : 0;

    if ($diskonPersen > 0 && $diskonNominal == 0) {
        $diskonNominal = round($bruto * $diskonPersen / 100);
    }
    if ($diskonNominal > 0 && $diskonPersen == 0) {
        $diskonPersen = $bruto > 0 ? round($diskonNominal / $bruto * 100, 2) : 0;
    }

    $diskonNominal = min($diskonNominal, $bruto);

    $detailId = DB::table('detail_jasas')->insertGetId([
        'rekam_servis_id' => $servis->id,
        'jasa_id' => $jasa->id,
        'qty' => $qty,
        'harga_saat_transaksi' => $harga,
        'diskon_persen' => $diskonPersen,
        'diskon_nominal' => $diskonNominal,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);

    return back()->with('success', 'Jasa berhasil ditambahkan.');
}

    public function hapus($id, $detailId, Request $request)
    {
        $servis = RekamServis::findOrFail($id);

        if (
                $servis->status === 'selesai' ||
                NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()
            ) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Transaksi sudah selesai dan terkunci.'], 422)
                : back()->with('error', 'Transaksi sudah selesai dan terkunci.');
        }

        DB::table('detail_jasas')
            ->where('rekam_servis_id', $servis->id)
            ->where('id', $detailId)
            ->delete();

        $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => 'Jasa berhasil dihapus.',
                'subtotal' => $servis->subtotal(),
            ]);
        }

        return back()->with('success', 'Jasa berhasil dihapus dari nota.');
    }
}