<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekamServis;
use App\Models\Kendaraan;
use App\Models\Konsumen;
use App\Models\NotaTransaksi;
use App\Models\AturanDiagnosa;
use App\Models\Sparepart;
use App\Models\Jasa;
use Illuminate\Http\Request;

/**
 * Tahap 7: proses inti Rekam Servis dipindahkan dari closure routes/web.php.
 * Proses mutasi transaksi nota, sparepart, jasa, pembayaran, dan penyelesaian
 * tetap berada pada implementasi aktif sebelumnya untuk tahap berikutnya.
 */
class RekamServisController extends Controller
{
    public function index()
    {
        $servis = RekamServis::with([
            'kendaraan',
            'kendaraan.konsumen',
            'nota',
        ])
            ->latest()
            ->get();

        return view('admin.rekam-servis.index', compact('servis'));
    }

    public function create()
    {
        $konsumens = Konsumen::with([
            'kendaraans' => function ($query) {
                $query->with('rekomendasiNotaTerbuka')->orderBy('plat_nomor');
            },
        ])
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin.rekam-servis.create', compact('konsumens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'konsumen_id'  => ['required', 'exists:konsumens,id'],
            'kendaraan_id' => ['required', 'exists:kendaraans,id'],
            'km_masuk'     => ['required', 'integer', 'min:0'],
            'keluhan_awal' => ['required', 'string', 'max:2000'],
        ], [
            'konsumen_id.required'  => 'Konsumen wajib dipilih.',
            'konsumen_id.exists'    => 'Data konsumen tidak ditemukan.',
            'kendaraan_id.required' => 'Plat kendaraan wajib dipilih.',
            'kendaraan_id.exists'   => 'Kendaraan yang dipilih tidak ditemukan.',
            'km_masuk.required'     => 'Kilometer terkini wajib diisi.',
            'km_masuk.integer'      => 'Kilometer harus berupa angka.',
            'km_masuk.min'          => 'Kilometer tidak boleh kurang dari 0.',
            'keluhan_awal.required' => 'Keluhan konsumen wajib diisi.',
        ]);

        $kendaraan = Kendaraan::where('id', $request->kendaraan_id)
            ->where('konsumen_id', $request->konsumen_id)
            ->first();

        if (! $kendaraan) {
            return back()
                ->withInput()
                ->withErrors([
                    'kendaraan_id' => 'Kendaraan tersebut tidak terdaftar atas nama konsumen yang dipilih.',
                ]);
        }

        $keluhanInput = trim($request->keluhan_awal);

        $semuaAturan     = AturanDiagnosa::all();
        $diagnosaTerpilih = 'Pengecekan Umum';
        $skorTertinggi   = 0;

        $hitungKemiripan = function ($teks1, $teks2) {
            $stopWords = ['dan', 'atau', 'di', 'ke', 'dari', 'yang', 'untuk', 'pada', 'dengan', 'ada', 'ini', 'itu', 'saat', 'ketika'];
            $bersihkan = function ($str) use ($stopWords) {
                $str       = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $str));
                $kataArray = preg_split('/\s+/', trim($str));
                return array_values(array_diff($kataArray, $stopWords, ['']));
            };
            $kata1 = array_unique($bersihkan($teks1));
            $kata2 = array_unique($bersihkan($teks2));
            if (empty($kata1) || empty($kata2)) {
                return 0;
            }
            $irisan    = array_intersect($kata1, $kata2);
            $gabungan  = array_unique(array_merge($kata1, $kata2));
            if (count($gabungan) === 0) {
                return 0;
            }
            return count($irisan) / count($gabungan);
        };

        foreach ($semuaAturan as $aturan) {
            if (stripos($keluhanInput, $aturan->kata_kunci) !== false) {
                $skorTertinggi    = 1.0;
                $diagnosaTerpilih = $aturan->diagnosa_terkait ?? $aturan->hasil_diagnosa;
                break;
            }
            $skor = $hitungKemiripan($keluhanInput, $aturan->kata_kunci);
            if ($skor > $skorTertinggi) {
                $skorTertinggi    = $skor;
                $diagnosaTerpilih = $aturan->diagnosa_terkait ?? $aturan->hasil_diagnosa;
            }
        }

        if ($skorTertinggi < 0.15) {
            $diagnosaTerpilih = 'Pengecekan Umum';
        }

        RekamServis::create([
            'kendaraan_id' => $kendaraan->id,
            'km_masuk'     => $request->km_masuk,
            'keluhan_awal' => $keluhanInput,
            'diagnosa_awal' => $diagnosaTerpilih,
            'tanggal_servis' => now(),
            'status'       => 'menunggu_pengerjaan',
            'user_id'      => auth()->id(),
        ]);

        return redirect()
            ->route('admin.rekam-servis.index')
            ->with('success', 'Rekam servis ' . $kendaraan->plat_nomor . ' berhasil dibuat dengan deteksi diagnosa otomatis.');
    }

    public function formKoreksi($id)
    {
        $servis = RekamServis::with(['kendaraan.konsumen'])->findOrFail($id);

        if ($servis->status === 'selesai'
            || NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()) {
            return redirect()->route('admin.rekam-servis.index')
                ->with('error', 'Servis ini sudah memiliki tagihan dan tidak dapat dikoreksi lagi.');
        }

        return view('admin.rekam-servis.koreksi', compact('servis'));
    }

    public function simpanKoreksi(Request $request, $id)
    {
        $request->validate([
            'diagnosa_akhir'  => ['required', 'string', 'max:2000'],
            'tindakan_servis' => ['required', 'string', 'max:5000'],
        ]);

        $servis = RekamServis::findOrFail($id);

        if ($servis->status === 'selesai'
            || NotaTransaksi::where('rekam_servis_id', $servis->id)->exists()) {
            return redirect()->route('admin.rekam-servis.index')
                ->with('error', 'Servis ini sudah memiliki tagihan dan tidak dapat dikoreksi lagi.');
        }

        $servis->update([
            'diagnosa_akhir'  => $request->diagnosa_akhir,
            'tindakan_servis' => $request->tindakan_servis,
            'status'          => 'sedang_dikerjakan',
        ]);

        return redirect()
            ->route('admin.rekam-servis.index')
            ->with('success', 'Koreksi diagnosa dan tindakan berhasil disimpan.');
    }

    public function nota($id)
    {
        $servis = RekamServis::with([
            'kendaraan.konsumen',
            'detailSpareparts.sparepart',
            'detailJasas.jasa',
            'catatanRekomendasis',
            'nota.pembayaran.penerima',
        ])->findOrFail($id);

        $spareparts = Sparepart::latest()->get();
        $jasas      = Jasa::latest()->get();

        return view('admin.rekam-servis.nota', compact('servis', 'spareparts', 'jasas'));
    }
}