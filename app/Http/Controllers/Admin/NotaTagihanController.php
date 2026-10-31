<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotaTransaksi;
use App\Models\RekamServis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaTagihanController extends Controller
{
    public function tambahRekomendasi(int $id, Request $request)
    {
        DB::table('catatan_rekomendasis')->insert([
            'rekam_servis_id' => (int) $id,
            'catatan'         => $request->catatan,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        return redirect("/admin/rekam-servis/{$id}/nota");
    }

    public function terbitkanTagihan(int $id, Request $request)
    {
        abort_unless(
            auth()->check() &&
            in_array(auth()->user()->role, ['admin', 'anggota'], true),
            403
        );

        $data = $request->validate([
            'diskon_persen_total' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'diskon_nominal_total' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($id, $data) {
            $servis = RekamServis::whereKey($id)->lockForUpdate()->firstOrFail();

            if ($servis->status === 'selesai' ||
                NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'nota' => 'Servis ini sudah selesai atau tagihannya sudah diterbitkan.',
                ]);
            }

            $servis->load(['detailSpareparts', 'detailJasas']);

            if ($servis->detailSpareparts->isEmpty() &&
                $servis->detailJasas->isEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'nota' => 'Tambahkan sparepart atau jasa terlebih dahulu.',
                ]);
            }

            $subtotal = (int) round($servis->subtotal());

            // Konversi diskon total
            $diskonPersen = (float) ($data['diskon_persen_total'] ?? 0);
            $diskonNominalRaw = str_replace('.', '', $data['diskon_nominal_total'] ?? '0');
            $diskonNominal = is_numeric($diskonNominalRaw) ? (float) $diskonNominalRaw : 0;

            // Jika yang diisi persen, hitung nominal
            if ($diskonPersen > 0 && $diskonNominal == 0) {
                $diskonNominal = round($subtotal * $diskonPersen / 100);
            }
            // Jika yang diisi nominal, hitung persen
            if ($diskonNominal > 0 && $diskonPersen == 0) {
                $diskonPersen = $subtotal > 0 ? round($diskonNominal / $subtotal * 100, 2) : 0;
            }

            $diskonNominal = min($diskonNominal, $subtotal);

            NotaTransaksi::create([
                'rekam_servis_id' => $servis->id,
                'no_nota' => 'NOTA-' . now()->format('Ymd')
                    . '-RS' . str_pad((string) $servis->id, 6, '0', STR_PAD_LEFT),
                'tanggal_cetak' => now()->toDateString(),
                'tanggal_terbit_tagihan' => now(),
                'subtotal' => $subtotal,
                'diskon' => $diskonNominal,
                'total_biaya' => $subtotal - $diskonNominal,
                'metode_cetak' => 'standar',
                'status_pembayaran' => 'belum_lunas',
                'tanggal_lunas' => null,
                'catatan_nota' => null,
            ]);
        }, 3);

        return redirect()
            ->route('admin.rekam-servis.nota', $id)
            ->with('success', 'Tagihan servis berhasil diterbitkan.');
    }
}