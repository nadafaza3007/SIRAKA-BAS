<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanInspeksi;
use App\Models\Konsumen;
use App\Models\Sparepart;
use App\Models\Jasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LaporanInspeksiController extends Controller
{
    public function index()
    {
    $inspeksis = LaporanInspeksi::with(['kendaraan.konsumen', 'user'])
        ->latest()
        ->get();
    return view('admin.inspeksi.index', compact('inspeksis'));

    }

    public function create()
    {
    $konsumens = Konsumen::with(['kendaraans' => fn($q) => $q->orderBy('plat_nomor')])
        ->orderBy('nama_lengkap')->get();
    $spareparts = Sparepart::latest()->get();
    $jasas = Jasa::latest()->get();

    $konsumensData = $konsumens->map(fn($k) => [
        'id' => $k->id,
        'nama' => $k->nama_lengkap,
        'no_hp' => $k->no_hp,
        'kendaraans' => $k->kendaraans->map(fn($v) => [
            'id' => $v->id,
            'plat_nomor' => $v->plat_nomor,
            'merk' => $v->merk,
            'tipe_model' => $v->tipe_model,
            'tahun' => $v->tahun,
            'cc' => $v->cc,
            'transmisi' => $v->transmisi,
            'transmisi_label' => $v->transmisi_label,
        ])->values()->all(),
    ])->values()->all();

    return view('admin.inspeksi.create', compact('konsumens', 'spareparts', 'jasas', 'konsumensData'));

    }

    public function store(Request $request)
    {
    $validated = $request->validate([
        'konsumen_id' => ['nullable','integer','exists:konsumens,id'],
        'nama_konsumen_inspeksi' => ['required','string','max:255'],
        'no_wa_inspeksi' => ['required','string','max:25','regex:/^\+?[0-9\s().-]{8,25}$/'],
        'tanggal_inspeksi' => ['required','date'],
        'km_kendaraan' => ['nullable','integer','min:0'],
        'rating_score' => ['required','integer','min:1','max:10'],
        'plat_nomor' => ['nullable','string','max:20'],
        'merk' => ['nullable','string','max:100'],
        'tipe_model' => ['nullable','string','max:100'],
        'tahun' => ['nullable','integer','min:1900','max:'.(date('Y') + 1)],
        'cc' => ['nullable','string','regex:/^\d{1,3}([,.]\d{1,2})?$/'],
        'transmisi' => ['nullable','in:manual,otomatis,cvt'],
    ]);
    $konsumen = !empty($validated['konsumen_id'])
        ? Konsumen::findOrFail($validated['konsumen_id']) : null;
    if ($konsumen && trim($validated['nama_konsumen_inspeksi']) !== trim($konsumen->nama_lengkap)) {
        throw ValidationException::withMessages(['nama_konsumen_inspeksi' => 'Nama pelanggan tidak sesuai pilihan rekomendasi.']);
    }
    DB::transaction(function () use ($request, $validated, $konsumen) {
        // Inspeksi bersifat mandiri: TIDAK menambah record konsumens maupun kendaraans.
        $kendaraanId = null;
        $namaSnapshot = $konsumen ? $konsumen->nama_lengkap : trim($validated['nama_konsumen_inspeksi']);
        $waSnapshot = trim($validated['no_wa_inspeksi']);
        $ccInspeksi = !empty($validated['cc']) ? str_replace(',', '.', $validated['cc']) : null;

        // 2. Simpan Inspeksi (km_kendaraan boleh null)
        $inspeksiId = DB::table('laporan_inspeksis')->insertGetId([
            'konsumen_id' => $konsumen?->id,
            'kendaraan_id' => null,
            'nama_konsumen_inspeksi' => $namaSnapshot,
            'no_wa_inspeksi' => $waSnapshot,
            'plat_nomor_inspeksi' => $request->filled('plat_nomor') ? strtoupper(trim($request->plat_nomor)) : null,
            'merk_inspeksi' => $request->filled('merk') ? trim($request->merk) : null,
            'tipe_model_inspeksi' => $request->filled('tipe_model') ? trim($request->tipe_model) : null,
            'tahun_inspeksi' => $request->tahun ?: null,
            'cc_inspeksi' => $ccInspeksi,
            'transmisi_inspeksi' => $request->transmisi ?: null,
            'nomor_inspeksi' => LaporanInspeksi::generateNomorInspeksi(),
            'tanggal_inspeksi' => $request->tanggal_inspeksi,
            'km_kendaraan' => !empty($request->km_kendaraan) ? (int) $request->km_kendaraan : null, // ✅ Handle null
            'rating_score' => $request->rating_score,
            'ex_banjir' => $request->ex_banjir ?? 'tidak',
            'ex_tabrak' => $request->ex_tabrak ?? 'tidak',
            'kondisi_mesin' => $request->kondisi_mesin,
            'kondisi_transmisi' => $request->kondisi_transmisi,
            'kondisi_kaki_kaki' => $request->kondisi_kaki_kaki,
            'kondisi_eksterior' => $request->kondisi_eksterior,
            'kondisi_interior' => $request->kondisi_interior,
            'saran' => $request->saran,
            'user_id' => auth()->id(),
            'status' => 'selesai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Simpan Detail (sama seperti sebelumnya)
        foreach (['kelistrikan', 'interior', 'kaki_kaki', 'exterior', 'mesin', 'dokumen'] as $section) {
            if ($request->has($section)) {
                $tableName = 'inspeksi_' . $section;
                foreach ($request->input($section) as $item) {
                    if (!empty($item['komponen']) || !empty($item['dokumen'])) {
                        $payload = ['laporan_inspeksi_id' => $inspeksiId, 'created_at' => now(), 'updated_at' => now()];
                        
                        if ($section === 'kaki_kaki') {
                            $payload['komponen'] = $item['komponen'] ?? '';
                            $payload['status_kiri'] = $item['status_kiri'] ?? 'Baik';
                            $payload['status_kanan'] = $item['status_kanan'] ?? 'Baik';
                            $payload['catatan'] = $item['catatan'] ?? null;
                        } elseif ($section === 'dokumen') {
                            $payload['dokumen'] = $item['dokumen'] ?? '';
                            $payload['status'] = $item['status'] ?? 'Ya';
                            $payload['keterangan'] = $item['keterangan'] ?? null;
                        } else {
                            $payload['komponen'] = $item['komponen'] ?? '';
                            $payload['status'] = $item['status'] ?? 'Baik';
                            $payload['keterangan'] = $item['keterangan'] ?? null;
                        }
                        DB::table($tableName)->insert($payload);
                    }
                }
            }
        }

        // 4. Simpan Estimasi (sama seperti sebelumnya)
        if ($request->has('estimasi')) {
            foreach ($request->input('estimasi') as $item) {
                if (!empty($item['nama_item'])) {
                    DB::table('inspeksi_estimasi')->insert([
                        'laporan_inspeksi_id' => $inspeksiId,
                        'kategori' => $item['kategori'] ?? 'engine',
                        'sparepart_id' => !empty($item['sparepart_id']) ? $item['sparepart_id'] : null,
                        'jasa_id' => !empty($item['jasa_id']) ? $item['jasa_id'] : null,
                        'nama_item' => $item['nama_item'],
                        'qty' => 1,
                        'harga_part' => $item['harga_part'] ?? 0,
                        'harga_jasa' => $item['harga_jasa'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    });

    return redirect()->route('admin.inspeksi.index')->with('success', 'Laporan inspeksi berhasil dibuat.');

    }

    public function show($id)
    {
    $inspeksi = LaporanInspeksi::with([
        'konsumen', 
        'kendaraan', 
        'user', 
        'kelistrikan', 
        'interior', 
        'kakiKaki',
        'exterior', 
        'mesin', 
        'dokumen', 
        'estimasi.sparepart', 
        'estimasi.jasa',
    ])->findOrFail($id);
    return view('admin.inspeksi.show', compact('inspeksi'));

    }

    public function destroy($id)
    {
    LaporanInspeksi::findOrFail($id)->delete();
    return redirect()->route('admin.inspeksi.index')->with('success', 'Laporan inspeksi dihapus.');

    }
}