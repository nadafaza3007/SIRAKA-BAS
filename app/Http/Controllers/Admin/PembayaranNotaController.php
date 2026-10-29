<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotaTransaksi;
use App\Models\PembayaranNota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranNotaController extends Controller
{
    public function bayar(Request $request, $id)
    {
        abort_unless(
            auth()->check() && in_array(auth()->user()->role, ['admin', 'anggota'], true),
            403
        );

        $data = $request->validate([
            'jumlah_diterima' => ['required', 'integer', 'min:1', 'max:9999999999'],
            'metode_pembayaran' => ['required', 'in:tunai,transfer,qris,debit'],
            'pilihan_kelebihan' => ['nullable', 'in:kembalian,tips'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $lunasSekarang = DB::transaction(function () use ($id, $data) {
            // Mengunci nota untuk mencegah dua petugas membayar sisa yang sama.
            $nota = NotaTransaksi::where('rekam_servis_id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            $totalTagihan = (int) round((float) $nota->total_biaya);

            // Hanya dana yang dialokasikan ke tagihan, bukan tips/kembalian.
            $sudahDibayar = (int) round((float) PembayaranNota::where('nota_transaksi_id', $nota->id)
                ->sum('nominal_dialokasikan'));
            $sisa = max(0, $totalTagihan - $sudahDibayar);

            if ($sisa <= 0 || $nota->status_pembayaran === 'lunas') {
                throw ValidationException::withMessages([
                    'pembayaran' => 'Tagihan sudah lunas. Tidak dapat menerima pembayaran tambahan.',
                ]);
            }

            $diterima = (int) $data['jumlah_diterima'];
            $metode = $data['metode_pembayaran'];

            if ($metode !== 'tunai' && $diterima > $sisa) {
                throw ValidationException::withMessages([
                    'jumlah_diterima' => 'Pembayaran non-tunai tidak boleh melebihi sisa tagihan.',
                ]);
            }

            $dialokasikan = min($diterima, $sisa);
            $kelebihan = max(0, $diterima - $dialokasikan);
            $pilihan = $data['pilihan_kelebihan'] ?? 'kembalian';

            if ($metode !== 'tunai' && $pilihan === 'tips') {
                throw ValidationException::withMessages([
                    'pilihan_kelebihan' => 'Tips hanya diperbolehkan untuk kelebihan pembayaran tunai.',
                ]);
            }

            $tips = ($metode === 'tunai' && $pilihan === 'tips') ? $kelebihan : 0;
            $kembalian = $kelebihan - $tips;
            $sisaBaru = $sisa - $dialokasikan;
            $lunas = $sisaBaru === 0;

            $pembayaran = PembayaranNota::create([
                'nota_transaksi_id' => $nota->id,
                'user_id' => auth()->id(),
                'jumlah_diterima' => $diterima,
                // Pada pembayaran bertahap: jumlah tagihan yang dilunasi pada baris ini.
                'jumlah_tagihan' => $dialokasikan,
                'nominal_dialokasikan' => $dialokasikan,
                'tips' => $tips,
                'kembalian' => $kembalian,
                'metode_pembayaran' => $metode,
                'tanggal_bayar' => now(),
                'catatan' => $data['catatan'] ?? null,
            ]);

            // Nomor bukti tidak bergantung pada jumlah cicilan atau nomor nota.
            $pembayaran->update([
                'no_bukti' => 'BYR-' . str_pad((string) $pembayaran->id, 8, '0', STR_PAD_LEFT),
            ]);

            $nota->update([
                'status_pembayaran' => $lunas ? 'lunas' : 'dibayar_sebagian',
                'tanggal_lunas' => $lunas ? now() : null,
            ]);

            return $lunas;
        }, 3);

        return redirect()
            ->route('admin.rekam-servis.nota', $id)
            ->with('success', $lunasSekarang
                ? 'Pembayaran diterima. Tagihan sudah lunas. Nota Akhir tersedia setelah Data Akhir lengkap.'
                : 'Pembayaran sebagian berhasil dicatat. Sisa tagihan dapat dibayar kemudian.');
    }
}