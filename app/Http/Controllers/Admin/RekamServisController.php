<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekamServis;
use App\Models\Kendaraan;
use App\Models\Sparepart;
use App\Models\Jasa;
use App\Models\AturanDiagnosa;
use App\Models\CatatanRekomendasi;
use App\Models\NotaTransaksi;
use App\Models\DetailNotaSparepart;
use App\Models\DetailNotaJasa;
use Illuminate\Http\Request;

class RekamServisController extends Controller
{
    public function index()
    {
        $servis = RekamServis::with(['kendaraan.konsumen'])->latest()->get();
        return view('admin.rekam-servis.index', compact('servis'));
    }

    public function create()
    {
        $kendaraans = Kendaraan::with('konsumen')->get();
        return view('admin.rekam-servis.create', compact('kendaraans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kendaraan_id' => 'required|exists:kendaraans,id',
            'km_akhir' => 'required|numeric',
            'keluhan_awal' => 'required|string',
        ]);

        // Pencocokan otomatis kata kunci diagnosa awal
        $diagnosaAwal = 'Pengecekan Umum';
        $aturanList = AturanDiagnosa::all();
        foreach ($aturanList as $aturan) {
            if (stripos($request->keluhan_awal, $aturan->kata_kunci) !== false) {
                $diagnosaAwal = $aturan->hasil_diagnosa;
                break;
            }
        }

        RekamServis::create([
            'kendaraan_id' => $request->kendaraan_id,
            'km_akhir' => $request->km_akhir,
            'keluhan_awal' => $request->keluhan_awal,
            'diagnosa_awal' => $diagnosaAwal,
            'tanggal_servis' => now(),
            'status' => 'menunggu_pengerjaan',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.rekam-servis.index')->with('success', 'Rekam servis berhasil dicatat.');
    }

    public function formKoreksi($id)
    {
        $servis = RekamServis::with(['kendaraan.konsumen'])->findOrFail($id);
        return view('admin.rekam-servis.koreksi', compact('servis'));
    }

    public function simpanKoreksi(Request $request, $id)
    {
        $request->validate([
            'diagnosa_akhir' => 'required|string',
            'tindakan_servis' => 'required|string',
        ]);

        $servis = RekamServis::findOrFail($id);
        $servis->update([
            'diagnosa_akhir' => $request->diagnosa_akhir,
            'tindakan_servis' => $request->tindakan_servis,
            'status' => 'siap_cetak_nota',
        ]);

        return redirect()->route('admin.rekam-servis.nota', $servis->id);
    }

    public function cetakNota($id)
    {
        $servis = RekamServis::with(['kendaraan.konsumen', 'detailSpareparts.sparepart', 'detailJasas.jasa', 'catatanRekomendasis', 'nota'])->findOrFail($id);
        $spareparts = Sparepart::where('stok', '>', 0)->get();
        $jasas = Jasa::all();

        return view('admin.rekam-servis.nota', compact('servis', 'spareparts', 'jasas'));
    }

    public function tambahSparepart(Request $request, $id)
    {
        $request->validate([
            'sparepart_id' => 'required|exists:spareparts,id',
            'qty' => 'required|integer|min:1',
        ]);

        $sp = Sparepart::findOrFail($request->sparepart_id);
        
        DetailNotaSparepart::create([
            'rekam_servis_id' => $id,
            'sparepart_id' => $sp->id,
            'qty' => $request->qty,
            'harga_modal_saat_transaksi' => $sp->harga_modal,
            'harga_jual_saat_transaksi' => $sp->harga_jual,
        ]);

        $sp->decrement('stok', $request->qty);

        return back()->with('success', 'Suku cadang berhasil ditambahkan.');
    }

    public function hapusSparepart($id, $detailId)
    {
        $detail = DetailNotaSparepart::where('rekam_servis_id', $id)->findOrFail($detailId);
        if ($detail->sparepart) {
            $detail->sparepart->increment('stok', $detail->qty);
        }
        $detail->delete();

        return back()->with('success', 'Suku cadang dihapus.');
    }

    public function tambahJasa(Request $request, $id)
    {
        $request->validate([
            'jasa_id' => 'required|exists:jasas,id',
        ]);

        $jasa = Jasa::findOrFail($request->jasa_id);
        $harga = $request->harga_custom ?: $jasa->harga;

        DetailNotaJasa::create([
            'rekam_servis_id' => $id,
            'jasa_id' => $jasa->id,
            'harga_saat_transaksi' => $harga,
        ]);

        return back()->with('success', 'Jasa berhasil ditambahkan.');
    }

    public function hapusJasa($id, $detailId)
    {
        DetailNotaJasa::where('rekam_servis_id', $id)->where('id', $detailId)->delete();
        return back()->with('success', 'Jasa dihapus.');
    }

    public function tambahRekomendasi(Request $request, $id)
    {
        $request->validate(['catatan' => 'required|string']);

        CatatanRekomendasi::create([
            'rekam_servis_id' => $id,
            'catatan' => $request->catatan,
            'status_konfirmasi' => 'belum',
        ]);

        return back()->with('success', 'Rekomendasi ditambahkan.');
    }

    public function konfirmasiNota(Request $request, $id)
    {
        $servis = RekamServis::with(['detailSpareparts', 'detailJasas'])->findOrFail($id);
        $subtotal = $servis->subtotal();
        $diskon = $request->diskon ?: 0;
        $totalBiaya = max(0, $subtotal - $diskon);

        NotaTransaksi::updateOrCreate(
            ['rekam_servis_id' => $servis->id],
            [
                'no_nota' => 'NOTA-' . date('Ymd') . '-' . sprintf('%04d', $servis->id),
                'tanggal_cetak' => now(),
                'metode_cetak' => $request->metode_cetak ?: 'standar',
                'diskon' => $diskon,
                'total_biaya' => $totalBiaya,
                'status_pembayaran' => 'lunas',
            ]
        );

        $servis->update(['status' => 'selesai']);

        return back()->with('success', 'Nota dikonfirmasi dan servis telah selesai.');
    }

    public function riwayatIndex(Request $request)
    {
        $query = $request->get('q', '');
        $selectedKendaraanId = $request->get('kendaraan_id');

        $kendaraans = collect();
        if (!empty($query)) {
            $kendaraans = Kendaraan::with(['konsumen', 'rekamServis.catatanRekomendasis'])
                ->where('plat_nomor', 'LIKE', "%{$query}%")
                ->orWhereHas('konsumen', function ($q) use ($query) {
                    $q->where('nama_lengkap', 'LIKE', "%{$query}%");
                })->get();
        }

        $selectedKendaraan = null;
        if ($selectedKendaraanId) {
            $selectedKendaraan = Kendaraan::with(['konsumen', 'rekamServis' => function ($q) {
                $q->latest();
            }, 'rekamServis.detailSpareparts.sparepart', 'rekamServis.detailJasas.jasa', 'rekamServis.catatanRekomendasis', 'rekamServis.nota', 'rekamServis.user'])->find($selectedKendaraanId);
        } elseif ($kendaraans->count() === 1) {
            $selectedKendaraan = $kendaraans->first();
        }

        return view('admin.riwayat.index', compact('query', 'kendaraans', 'selectedKendaraan'));
    }

    public function searchApi(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) return response()->json([]);

        $kendaraans = Kendaraan::with(['konsumen', 'rekamServis'])
            ->where('plat_nomor', 'LIKE', "%{$q}%")
            ->orWhereHas('konsumen', function ($query) use ($q) {
                $query->where('nama_lengkap', 'LIKE', "%{$q}%");
            })->take(5)->get();

        $results = $kendaraans->map(function ($k) {
            $lastServis = $k->rekamServis->sortByDesc('tanggal_servis')->first();
            return [
                'id' => $k->id,
                'plat_nomor' => $k->plat_nomor,
                'merk' => $k->merk,
                'tipe_model' => $k->tipe_model,
                'konsumen_nama' => $k->konsumen->nama_lengkap ?? '-',
                'km_terakhir' => $lastServis ? $lastServis->km_akhir : 0,
                'total_servis' => $k->rekamServis->count(),
            ];
        });

        return response()->json($results);
    }
}