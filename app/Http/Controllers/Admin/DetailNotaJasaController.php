<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jasa;
use App\Models\NotaTransaksi;
use App\Models\RekamServis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DetailNotaJasaController extends Controller
{
    public function tambah($id, Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'anggota'], true), 403);

        // Form mengirim persen berformat Indonesia (mis. "11,36"); ubah ke titik
        // agar lolos validasi numeric.
        if ($request->filled('diskon_persen')) {
            $request->merge([
                'diskon_persen' => str_replace(',', '.', (string) $request->input('diskon_persen')),
            ]);
        }

        $data = $request->validate([
            'jasa_id' => ['required', 'exists:jasas,id'],
            'harga_custom' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'diskon_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'diskon_nominal' => ['nullable', 'string'],
        ]);

        $result = DB::transaction(function () use ($id, $data, $request) {
            $servis = RekamServis::whereKey($id)->lockForUpdate()->firstOrFail();

            if (
                $servis->status === 'selesai' ||
                NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()
            ) {
                throw ValidationException::withMessages([
                    'jasa' => 'Transaksi sudah dikunci. Rincian jasa tidak dapat diubah.',
                ]);
            }

            $jasa = Jasa::findOrFail($data['jasa_id']);
            $harga = $request->filled('harga_custom')
                ? (float) $data['harga_custom']
                : (float) $jasa->harga;
            $qty = 1;
            $bruto = $harga;

            // Konversi diskon: nominal atau persentase, mengikuti input yang diisi.
            $diskonPersen = (float) ($data['diskon_persen'] ?? 0);
            $diskonNominalRaw = str_replace('.', '', $data['diskon_nominal'] ?? '0');
            $diskonNominal = is_numeric($diskonNominalRaw)
                ? (float) $diskonNominalRaw
                : 0;

            if ($diskonPersen > 0 && $diskonNominal == 0) {
                $diskonNominal = round($bruto * $diskonPersen / 100);
            }

            if ($diskonNominal > 0 && $diskonPersen == 0) {
                $diskonPersen = $bruto > 0
                    ? round($diskonNominal / $bruto * 100, 2)
                    : 0;
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

            return [
                'id' => $detailId,
                'subtotal' => (float) $servis->subtotal(),
            ];
        }, 3);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Jasa servis berhasil ditambahkan.',
                'detail_id' => $result['id'],
                'subtotal' => $result['subtotal'],
            ], 201);
        }

        return back()->with('success', 'Jasa berhasil ditambahkan.');
    }

    public function hapus($id, $detailId, Request $request)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'anggota'], true), 403);

        $subtotal = DB::transaction(function () use ($id, $detailId) {
            $servis = RekamServis::whereKey($id)->lockForUpdate()->firstOrFail();

            if (
                $servis->status === 'selesai' ||
                NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()
            ) {
                throw ValidationException::withMessages([
                    'jasa' => 'Transaksi sudah selesai dan terkunci. Rincian jasa tidak dapat diubah.',
                ]);
            }

            $terhapus = DB::table('detail_jasas')
                ->where('rekam_servis_id', $servis->id)
                ->where('id', $detailId)
                ->delete();

            abort_if($terhapus === 0, 404, 'Detail jasa tidak ditemukan.');

            $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);

            return (float) $servis->subtotal();
        }, 3);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Jasa berhasil dihapus.',
                'subtotal' => $subtotal,
            ]);
        }

        return back()->with('success', 'Jasa berhasil dihapus dari nota.');
    }
}