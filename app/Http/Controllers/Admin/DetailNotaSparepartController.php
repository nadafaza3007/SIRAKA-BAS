<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekamServis;
use App\Models\NotaTransaksi;
use App\Models\Sparepart;
use App\Models\Jasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetailNotaSparepartController extends Controller
{
    public function tambah($id, Request $request)
    {
    $data = $request->validate([
        'sparepart_id' => ['required', 'exists:spareparts,id'],
        'qty' => ['required', 'integer', 'min:1', 'max:999'],
        'diskon_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
        'diskon_nominal' => ['nullable', 'string'],
    ]);

    $result = DB::transaction(function () use ($id, $data) {
        $servis = RekamServis::whereKey($id)->lockForUpdate()->firstOrFail();
        
        if ($servis->status === 'selesai' || NotaTransaksi::where('rekam_servis_id', $id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'sparepart' => 'Tagihan sudah diterbitkan atau servis selesai.',
            ]);
        }

        $sparepart = Sparepart::whereKey($data['sparepart_id'])->lockForUpdate()->firstOrFail();
        $tambahan = (int) $data['qty'];

        if ($sparepart->isBarangStok() && (int) $sparepart->stok < $tambahan) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'qty' => 'Stok tidak mencukupi. Tersedia ' . $sparepart->stok . '.',
            ]);
        }

        // Hitung subtotal
        $subtotal = (float) $sparepart->harga_jual * $tambahan;

        // Konversi diskon
        $diskonPersen = (float) ($data['diskon_persen'] ?? 0);
        $diskonNominalRaw = str_replace('.', '', $data['diskon_nominal'] ?? '0');
        $diskonNominal = is_numeric($diskonNominalRaw) ? (float) $diskonNominalRaw : 0;

        // Jika yang diisi persen, hitung nominal
        if ($diskonPersen > 0 && $diskonNominal == 0) {
            $diskonNominal = round($subtotal * $diskonPersen / 100);
        }
        // Jika yang diisi nominal, hitung persen
        if ($diskonNominal > 0 && $diskonPersen == 0) {
            $diskonPersen = $subtotal > 0 ? round($diskonNominal / $subtotal * 100, 2) : 0;
        }

        // Batasi diskon tidak lebih dari subtotal
        $diskonNominal = min($diskonNominal, $subtotal);

        $detailLama = DB::table('detail_spareparts')
            ->where('rekam_servis_id', $servis->id)
            ->where('sparepart_id', $sparepart->id)->first();

        if ($detailLama) {
            $qtyBaru = (int) $detailLama->qty + $tambahan;
            DB::table('detail_spareparts')->where('id', $detailLama->id)
                ->update([
                    'qty' => $qtyBaru,
                    'diskon_persen' => $diskonPersen,
                    'diskon_nominal' => $diskonNominal,
                    'updated_at' => now()
                ]);
            $detailId = $detailLama->id;
            $hargaSatuan = (float) $detailLama->harga_jual_saat_transaksi;
        } else {
            $qtyBaru = $tambahan;
            $hargaSatuan = (float) $sparepart->harga_jual;
            $detailId = DB::table('detail_spareparts')->insertGetId([
                'rekam_servis_id' => $servis->id,
                'sparepart_id' => $sparepart->id,
                'qty' => $qtyBaru,
                'harga_modal_saat_transaksi' => $sparepart->harga_modal ?? 0,
                'harga_jual_saat_transaksi' => $hargaSatuan,
                'diskon_persen' => $diskonPersen,
                'diskon_nominal' => $diskonNominal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($sparepart->isBarangStok()) {
            $sparepart->decrement('stok', $tambahan);
        }
        $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);

        return [
            'id' => $detailId,
            'name' => $sparepart->nama_barang,
            'qty' => $qtyBaru,
            'unit_price' => $hargaSatuan,
            'subtotal' => (float) $servis->subtotal(),
            'servis_id' => $servis->id,
        ];
    }, 3);

    return back()->with('success', 'Sparepart berhasil ditambahkan.');
}

    public function ubahQty($id, $detailId, Request $request)
    {
        $data = $request->validate(['qty' => ['required', 'integer', 'min:1', 'max:999']]);
        $result = DB::transaction(function () use ($id, $detailId, $data) {
            $servis = RekamServis::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($servis->status === 'selesai' || NotaTransaksi::where('rekam_servis_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'qty' => 'Tagihan sudah diterbitkan atau servis selesai. Rincian tidak dapat diubah.',
                ]);
            }
            $detail = DB::table('detail_spareparts')
                ->where('rekam_servis_id', $servis->id)->where('id', $detailId)->first();
            if (! $detail) {
                abort(404, 'Detail sparepart tidak ditemukan.');
            }
            $sparepart = Sparepart::whereKey($detail->sparepart_id)->lockForUpdate()->firstOrFail();
            $qtyBaru = (int) $data['qty'];
            $selisih = $qtyBaru - (int) $detail->qty;
            if ($sparepart->isBarangStok() && $selisih > 0 && (int) $sparepart->stok < $selisih) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'qty' => 'Stok tidak mencukupi. Tersedia ' . $sparepart->stok . '.',
                ]);
            }
            DB::table('detail_spareparts')->where('id', $detail->id)
                ->update(['qty' => $qtyBaru, 'updated_at' => now()]);
            if ($sparepart->isBarangStok()) {
                if ($selisih > 0) {
                    $sparepart->decrement('stok', $selisih);
                } elseif ($selisih < 0) {
                    $sparepart->increment('stok', -$selisih);
                }
            }
            $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);
            return [
                'qty' => $qtyBaru,
                'item_subtotal' => (float) $detail->harga_jual_saat_transaksi * $qtyBaru,
                'subtotal' => (float) $servis->subtotal(),
            ];
        }, 3);
        return response()->json([
            'message' => 'Jumlah sparepart diperbarui.',
            'qty' => $result['qty'],
            'item_subtotal' => $result['item_subtotal'],
            'subtotal' => $result['subtotal'],
        ]);
    }

    public function hapus($id, $detailId, Request $request)
    {
        $subtotal = DB::transaction(function () use ($id, $detailId) {
            $servis = RekamServis::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($servis->status === 'selesai' || NotaTransaksi::where('rekam_servis_id', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'sparepart' => 'Tagihan sudah diterbitkan atau servis selesai. Rincian tidak dapat diubah.',
                ]);
            }
            $detail = DB::table('detail_spareparts')
                ->where('rekam_servis_id', $servis->id)->where('id', $detailId)->first();
            if (! $detail) {
                abort(404, 'Detail sparepart tidak ditemukan.');
            }
            $sparepart = Sparepart::whereKey($detail->sparepart_id)->lockForUpdate()->firstOrFail();
            DB::table('detail_spareparts')->where('id', $detail->id)->delete();
            if ($sparepart->isBarangStok()) {
                $sparepart->increment('stok', (int) $detail->qty);
            }
            $servis->load(['detailSpareparts.sparepart', 'detailJasas.jasa']);
            return (float) $servis->subtotal();
        }, 3);
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sparepart berhasil dihapus.',
                'subtotal' => $subtotal,
            ]);
        }
        return back()->with('success', 'Sparepart berhasil dihapus dan stok dikembalikan.');
    }
}