<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotaTransaksi;
use App\Models\RekamServis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenyelesaianServisController extends Controller
{
    public function selesaikan(int $id, Request $request)
    {
        abort_unless(
            auth()->check() &&
            in_array(auth()->user()->role, ['admin', 'anggota'], true),
            403
        );

        $data = $request->validate([
            'km_keluar' => ['required', 'integer', 'min:0', 'max:999999999'],
            'catatan_nota' => ['nullable', 'string', 'max:5000'],
            'rekomendasi_selesai' => ['nullable', 'array'],
            'rekomendasi_selesai.*' => ['integer', 'distinct'],
        ]);

        DB::transaction(function () use ($id, $data) {
            $servis = RekamServis::whereKey($id)
                ->lockForUpdate()->firstOrFail();

            $nota = NotaTransaksi::where('rekam_servis_id', $id)
                ->lockForUpdate()->firstOrFail();

            if ($servis->status === 'selesai' && $servis->km_keluar !== null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'servis' => 'Data akhir sudah tersimpan dan terkunci.',
                ]);
            }

            if ((int) $data['km_keluar'] < (int) $servis->km_masuk) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'km_keluar' => 'KM keluar tidak boleh kurang dari KM masuk.',
                ]);
            }

            // Konfirmasi hanya rekomendasi lama milik kendaraan yang sama.
            $ids = $data['rekomendasi_selesai'] ?? [];
            if ($ids) {
                $jumlahValid = NotaTransaksi::query()
                    ->join('rekam_servis as lama', 'lama.id', '=', 'nota_transaksis.rekam_servis_id')
                    ->where('lama.kendaraan_id', $servis->kendaraan_id)
                    ->where('lama.id', '<>', $servis->id)
                    ->where('nota_transaksis.status_rekomendasi', 'belum')
                    ->whereIn('nota_transaksis.id', $ids)
                    ->count();
                if ($jumlahValid !== count($ids)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'rekomendasi_selesai' => 'Rekomendasi tidak valid atau sudah ditangani.',
                    ]);
                }
            }

            $servis->update([
                'km_keluar' => (int) $data['km_keluar'],
                'status' => 'selesai',
            ]);

            $catatanNota = trim((string) ($data['catatan_nota'] ?? ''));
            $nota->update([
                'catatan_nota' => $catatanNota !== '' ? $catatanNota : null,
                'status_rekomendasi' => $catatanNota !== '' ? 'belum' : null,
            ]);
            if ($ids) {
                NotaTransaksi::whereIn('id', $ids)
                    ->where('status_rekomendasi', 'belum')
                    ->update(['status_rekomendasi' => 'sudah']);
            }
        }, 3);

        return redirect()
            ->route('admin.rekam-servis.nota', $id)
            ->with('success', 'Data akhir pekerjaan berhasil disimpan.');
    }
}