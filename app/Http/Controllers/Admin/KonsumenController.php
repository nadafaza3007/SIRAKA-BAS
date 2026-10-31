<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Konsumen;
use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Master Konsumen; metode dipindahkan dari route aktif Tahap 3.
 * Endpoint dan perilaku validasi/penyimpanan tidak diubah.
 */
class KonsumenController extends Controller
{
    public function index()
    {
        $konsumen = Konsumen::with([
            'kendaraans' => function ($query) {
                $query->orderBy('plat_nomor');
            },
        ])
            ->latest()
            ->get();

        return view('admin.konsumen.index', compact('konsumen'));
    }

    public function edit(Konsumen $konsumen)
    {
        $konsumen->load([
            'kendaraans' => function ($query) {
                $query->with('rekomendasiNotaTerbuka')->orderBy('plat_nomor');
            },
        ]);

        return view('admin.konsumen.edit', compact('konsumen'));
    }

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_hp'        => ['required', 'string', 'max:20', 'unique:konsumens,no_hp'],
            'kendaraan'    => ['required', 'array', 'min:1'],
            'kendaraan.*.plat_nomor'   => ['required', 'string', 'max:20'],
            'kendaraan.*.merk'         => ['required', 'string', 'max:100'],
            'kendaraan.*.tipe_model'   => ['required', 'string', 'max:100'],
            'kendaraan.*.tahun'        => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],

            /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
            'kendaraan.*.cc'               => ['nullable', 'string', 'regex:/^\d{1,2}([,.]\d{1,2})?$/'], // ✅ Diubah: hapus min/max, pakai numeric
            'kendaraan.*.transmisi'        => ['nullable', 'string', 'in:manual,otomatis,cvt'],
            'kendaraan.*.jenis_bahan_bakar' => ['nullable', 'string', 'in:gasoline,diesel'],
                ]  , [
            'nama_lengkap.required'              => 'Nama konsumen wajib diisi.',
            'no_hp.required'                     => 'Nomor HP konsumen wajib diisi.',
            'no_hp.unique'                       => 'Nomor HP tersebut sudah terdaftar sebagai konsumen.',
            'kendaraan.required'                 => 'Minimal satu kendaraan harus ditambahkan.',
            'kendaraan.*.plat_nomor.required'    => 'Plat nomor kendaraan wajib diisi.',
            'kendaraan.*.merk.required'          => 'Merk kendaraan wajib diisi.',
            'kendaraan.*.tipe_model.required'    => 'Tipe atau model kendaraan wajib diisi.',
            'kendaraan.*.transmisi.in'           => 'Transmisi harus: manual, otomatis, atau cvt.',
            'kendaraan.*.jenis_bahan_bakar.in'   => 'Bahan bakar harus: gasoline atau diesel.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cek Duplikasi Plat
        |--------------------------------------------------------------------------
        */
        $platDalamForm = [];
        foreach ($request->kendaraan as $data) {
            $platNomor      = strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor'])));
            $platTanpaSpasi = str_replace(' ', '', $platNomor);

            if (in_array($platTanpaSpasi, $platDalamForm, true)) {
                return back()
                    ->withInput()
                    ->with('error', "Plat nomor {$platNomor} dimasukkan lebih dari satu kali.");
            }

            $platAda = Kendaraan::whereRaw("REPLACE(UPPER(plat_nomor), ' ', '') = ?", [$platTanpaSpasi])->exists();
            if ($platAda) {
                return back()
                    ->withInput()
                    ->with('error', "Plat nomor {$platNomor} sudah terdaftar pada kendaraan lain.");
            }

            $platDalamForm[] = $platTanpaSpasi;
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Konsumen dan Kendaraan
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use ($request) {
            $dataKonsumen = Konsumen::create([
                'nama_lengkap' => trim($request->nama_lengkap),
                'no_hp'        => trim($request->no_hp),
            ]);

            foreach ($request->kendaraan as $data) {
                $dataKonsumen->kendaraans()->create([
                    'plat_nomor'   => strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor']))),
                    'merk'         => trim($data['merk']),
                    'tipe_model'   => trim($data['tipe_model']),
                    'tahun'        => ! empty($data['tahun']) ? (int) $data['tahun'] : null,

                    /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
                    'cc'                => ! empty($data['cc']) ? (float) str_replace(',', '.', $data['cc']) : null,
                    'transmisi'         => ! empty($data['transmisi']) ? $data['transmisi'] : null,
                    'jenis_bahan_bakar' => ! empty($data['jenis_bahan_bakar']) ? $data['jenis_bahan_bakar'] : null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Hubungkan User Pelanggan yang Sudah Register Lebih Dulu
            |--------------------------------------------------------------------------
            */
            User::where('role', 'pelanggan')
                ->where('no_hp', $dataKonsumen->no_hp)
                ->whereNull('konsumen_id')
                ->update(['konsumen_id' => $dataKonsumen->id]);
        });

        return redirect()
            ->route('admin.konsumen.index')
            ->with('success', 'Konsumen dan kendaraan berhasil ditambahkan.');
    }

    public function update(Request $request, Konsumen $konsumen)
    {
        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_hp'        => ['required', 'string', 'max:20', 'unique:konsumens,no_hp,' . $konsumen->id],
            'kendaraan'    => ['nullable', 'array'],
            'kendaraan.*.plat_nomor'   => ['required', 'string', 'max:20'],
            'kendaraan.*.merk'         => ['required', 'string', 'max:100'],
            'kendaraan.*.tipe_model'   => ['required', 'string', 'max:100'],
            'kendaraan.*.tahun'        => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],

            /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
            'kendaraan.*.cc'               => ['nullable', 'string', 'regex:/^\d{1,2}([,.]\d{1,2})?$/'], // ✅ Diubah
            'kendaraan.*.transmisi'        => ['nullable', 'string', 'in:manual,otomatis,cvt'],
            'kendaraan.*.jenis_bahan_bakar' => ['nullable', 'string', 'in:gasoline,diesel'],
        ], [
            'kendaraan.*.transmisi.in'         => 'Transmisi harus: manual, otomatis, atau cvt.',
            'kendaraan.*.jenis_bahan_bakar.in' => 'Bahan bakar harus: gasoline atau diesel.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Nomor HP Lama
        |--------------------------------------------------------------------------
        */
        $noHpLama = $konsumen->no_hp;

        /*
        |--------------------------------------------------------------------------
        | Validasi Kendaraan Existing
        |--------------------------------------------------------------------------
        */
        foreach ($request->input('kendaraan', []) as $kendaraanId => $data) {
            $kendaraan = $konsumen->kendaraans()->whereKey($kendaraanId)->firstOrFail();

            $platNomor      = strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor'])));
            $platTanpaSpasi = str_replace(' ', '', $platNomor);

            $platDipakai = Kendaraan::whereRaw("REPLACE(UPPER(plat_nomor), ' ', '') = ?", [$platTanpaSpasi])
                ->where('id', '!=', $kendaraan->id)
                ->exists();

            if ($platDipakai) {
                return back()
                    ->withInput()
                    ->with('error', "Plat nomor {$platNomor} sudah digunakan kendaraan lain.");
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Perubahan
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use ($request, $konsumen, $noHpLama) {
            /*
            |--------------------------------------------------------------------------
            | Update Konsumen
            |--------------------------------------------------------------------------
            */
            $konsumen->update([
                'nama_lengkap' => trim($request->nama_lengkap),
                'no_hp'        => trim($request->no_hp),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update Kendaraan Existing
            |--------------------------------------------------------------------------
            */
            foreach ($request->input('kendaraan', []) as $kendaraanId => $data) {
                $kendaraan = $konsumen->kendaraans()->whereKey($kendaraanId)->firstOrFail();

                $kendaraan->update([
                    'plat_nomor'   => strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor']))),
                    'merk'         => trim($data['merk']),
                    'tipe_model'   => trim($data['tipe_model']),
                    'tahun'        => ! empty($data['tahun']) ? (int) $data['tahun'] : null,

                    /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
                    'cc'                => ! empty($data['cc']) ? (float) str_replace(',', '.', $data['cc']) : null,
                    'transmisi'         => ! empty($data['transmisi']) ? $data['transmisi'] : null,
                    'jenis_bahan_bakar' => ! empty($data['jenis_bahan_bakar']) ? $data['jenis_bahan_bakar'] : null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Sinkron Nomor HP User Pelanggan
            |--------------------------------------------------------------------------
            */
            $userPelanggan = User::where('role', 'pelanggan')
                ->where('konsumen_id', $konsumen->id)
                ->first();

            if ($userPelanggan) {
                $noHpBaru = trim($request->no_hp);

                if ($noHpLama !== $noHpBaru) {
                    $dipakaiUserLain = User::where('no_hp', $noHpBaru)
                        ->where('id', '!=', $userPelanggan->id)
                        ->exists();

                    if (! $dipakaiUserLain) {
                        $userPelanggan->update(['no_hp' => $noHpBaru]);
                    }
                }
            }
        });

        return redirect()
            ->route('admin.konsumen.index')
            ->with('success', 'Data konsumen dan kendaraan berhasil diperbarui.');
    }

    public function storeKendaraan(Request $request, Konsumen $konsumen)
    {
        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'kendaraan'    => ['required', 'array', 'min:1'],
            'kendaraan.*.plat_nomor'   => ['required', 'string', 'max:20'],
            'kendaraan.*.merk'         => ['required', 'string', 'max:100'],
            'kendaraan.*.tipe_model'   => ['required', 'string', 'max:100'],
            'kendaraan.*.tahun'        => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],

            /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
            'kendaraan.*.cc'               => ['nullable', 'string', 'regex:/^\d{1,2}([,.]\d{1,2})?$/'], // ✅ Diubah
            'kendaraan.*.transmisi'        => ['nullable', 'string', 'in:manual,otomatis,cvt'],
            'kendaraan.*.jenis_bahan_bakar' => ['nullable', 'string', 'in:gasoline,diesel'],
        ], [
            'kendaraan.*.transmisi.in'         => 'Transmisi harus: manual, otomatis, atau cvt.',
            'kendaraan.*.jenis_bahan_bakar.in' => 'Bahan bakar harus: gasoline atau diesel.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cek Duplikasi Plat
        |--------------------------------------------------------------------------
        */
        $platDalamForm = [];
        foreach ($request->kendaraan as $data) {
            $platNomor      = strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor'])));
            $platTanpaSpasi = str_replace(' ', '', $platNomor);

            if (in_array($platTanpaSpasi, $platDalamForm, true)) {
                return back()
                    ->withInput()
                    ->with('error', "Plat nomor {$platNomor} dimasukkan lebih dari satu kali.");
            }

            $sudahAda = Kendaraan::whereRaw("REPLACE(UPPER(plat_nomor), ' ', '') = ?", [$platTanpaSpasi])->exists();
            if ($sudahAda) {
                return back()
                    ->withInput()
                    ->with('error', "Plat nomor {$platNomor} sudah terdaftar.");
            }

            $platDalamForm[] = $platTanpaSpasi;
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Kendaraan Baru
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use ($request, $konsumen) {
            foreach ($request->kendaraan as $data) {
                $konsumen->kendaraans()->create([
                    'plat_nomor'   => strtoupper(preg_replace('/\s+/', ' ', trim($data['plat_nomor']))),
                    'merk'         => trim($data['merk']),
                    'tipe_model'   => trim($data['tipe_model']),
                    'tahun'        => ! empty($data['tahun']) ? (int) $data['tahun'] : null,

                    /* ===== FIELD BARU: CC, TRANSMISI, BAHAN BAKAR ===== */
                    'cc'                => ! empty($data['cc']) ? (float) str_replace(',', '.', $data['cc']) : null,
                    'transmisi'         => ! empty($data['transmisi']) ? $data['transmisi'] : null,
                    'jenis_bahan_bakar' => ! empty($data['jenis_bahan_bakar']) ? $data['jenis_bahan_bakar'] : null,
                ]);
            }
        });

        return redirect()
            ->route('admin.konsumen.index')
            ->with('success', 'Kendaraan baru berhasil ditambahkan ke ' . $konsumen->nama_lengkap . '.');
    }
}