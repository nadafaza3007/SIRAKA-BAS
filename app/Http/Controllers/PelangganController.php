<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\Konsumen;
use App\Models\RekamServis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PelangganController extends Controller
{
    /**
     * ================================================================
     * DASHBOARD PELANGGAN
     * ================================================================
     */
    public function dashboard()
    {
        $user = Auth::user();
        $konsumen = $this->konsumen();

        /*
        |--------------------------------------------------------------------------
        | Nama depan pelanggan
        |--------------------------------------------------------------------------
        */
        $namaLengkap = $konsumen?->nama_lengkap
            ?? $user?->name
            ?? 'Pelanggan';

        $namaDepan = strtok(
            trim((string) $namaLengkap),
            ' '
        ) ?: 'Pelanggan';


        /*
        |--------------------------------------------------------------------------
        | Jika akun belum terhubung dengan konsumen
        |--------------------------------------------------------------------------
        |
        | Kita tidak memakai pelanggan.belum-terhubung karena pada struktur
        | view Anda saat ini file tersebut belum ada.
        |
        */
        if (! $konsumen) {
            return view('pelanggan.dashboard', [
                'namaDepan' => $namaDepan,
                'totalKendaraan' => 0,
                'jumlahPerluServis' => 0,
                'perluDipantau' => collect(),
                'servisTerakhir' => null,
                'aktivitas' => collect(),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Kendaraan pelanggan
        |--------------------------------------------------------------------------
        */
        $kendaraans = $konsumen
            ->kendaraans()
            ->denganStatus()
            ->orderBy('merk')
            ->orderBy('tipe_model')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Aktivitas servis terbaru
        |--------------------------------------------------------------------------
        */
        $aktivitas = $this
            ->riwayatQuery($konsumen)
            ->with([
                'kendaraan',
                'nota',
            ])
            ->limit(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Kendaraan perlu servis
        |--------------------------------------------------------------------------
        */
        $jumlahPerluServis = $kendaraans
            ->filter(function ($kendaraan) {
                return $kendaraan->status_servis === 'perlu_servis';
            })
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Kendaraan yang perlu dipantau
        |--------------------------------------------------------------------------
        |
        | Termasuk:
        | - perlu servis
        | - sedang dikerjakan
        |
        */
        $perluDipantau = $kendaraans
            ->filter(function ($kendaraan) {
                return in_array(
                    $kendaraan->status_servis,
                    [
                        'perlu_servis',
                        'dikerjakan',
                    ],
                    true
                );
            })
            ->values();


        return view('pelanggan.dashboard', [
            'namaDepan' => $namaDepan,
            'totalKendaraan' => $kendaraans->count(),
            'jumlahPerluServis' => $jumlahPerluServis,
            'perluDipantau' => $perluDipantau,
            'servisTerakhir' => $aktivitas->first(),
            'aktivitas' => $aktivitas,
        ]);
    }


    /**
     * ================================================================
     * DAFTAR KENDARAAN PELANGGAN
     * ================================================================
     */
    public function kendaraan()
    {
        $konsumen = $this->konsumen();


        /*
        |--------------------------------------------------------------------------
        | Belum terhubung ke konsumen
        |--------------------------------------------------------------------------
        */
        if (! $konsumen) {
            return view(
                'pelanggan.kendaraan.kendaraan',
                [
                    'kendaraans' => collect(),
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Kendaraan pelanggan
        |--------------------------------------------------------------------------
        |
        | denganStatus() menyiapkan:
        | - servisTerakhir
        | - rekam_servis_count
        | - status_servis
        | - status_label
        | - status_tone
        | - alasan_perhatian
        |
        */
        $kendaraans = $konsumen
            ->kendaraans()
            ->denganStatus()
            ->orderBy('merk')
            ->orderBy('tipe_model')
            ->get();


        return view(
            'pelanggan.kendaraan.kendaraan',
            [
                'kendaraans' => $kendaraans,
            ]
        );
    }


    /**
     * ================================================================
     * SEMUA RIWAYAT SERVIS
     * ================================================================
     */
    public function riwayat(Request $request)
    {
        return $this->tampilRiwayat(
            request: $request,
            kendaraan: null
        );
    }


    /**
     * ================================================================
     * RIWAYAT SERVIS SATU KENDARAAN
     * ================================================================
     */
    public function riwayatKendaraan(
        Request $request,
        Kendaraan $kendaraan
    ) {
        $konsumen = $this->konsumen();

        /*
        |--------------------------------------------------------------------------
        | Konsumen harus tersedia
        |--------------------------------------------------------------------------
        */
        abort_if(
            ! $konsumen,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Kendaraan harus milik pelanggan yang sedang login
        |--------------------------------------------------------------------------
        */
        abort_if(
            (int) $kendaraan->konsumen_id
                !== (int) $konsumen->id,
            404
        );


        return $this->tampilRiwayat(
            request: $request,
            kendaraan: $kendaraan
        );
    }


    /**
     * ================================================================
     * METHOD INTERNAL UNTUK HALAMAN RIWAYAT
     * ================================================================
     */
    private function tampilRiwayat(
        Request $request,
        ?Kendaraan $kendaraan = null
    ) {
        $konsumen = $this->konsumen();

        $searchPlat = trim(
            (string) $request->query(
                'plat',
                ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Jika akun belum terhubung
        |--------------------------------------------------------------------------
        */
        if (! $konsumen) {

            $riwayat = RekamServis::query()
                ->whereRaw('1 = 0')
                ->paginate(10)
                ->withQueryString();


            return view(
                'pelanggan.riwayat-servis.riwayatServis',
                [
                    'kendaraans' => collect(),
                    'kendaraanAktif' => null,
                    'riwayat' => $riwayat,
                    'searchPlat' => $searchPlat,
                    'totalKendaraan' => 0,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Pastikan filter kendaraan adalah kendaraan milik pelanggan
        |--------------------------------------------------------------------------
        */
        if ($kendaraan) {
            abort_if(
                (int) $kendaraan->konsumen_id
                    !== (int) $konsumen->id,
                404
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Semua kendaraan pelanggan
        |--------------------------------------------------------------------------
        |
        | Daftar ini digunakan untuk tombol filter kendaraan.
        |
        */
        $kendaraans = $konsumen
            ->kendaraans()
            ->orderBy('merk')
            ->orderBy('tipe_model')
            ->get();


        $totalKendaraan = $kendaraans->count();


        /*
        |--------------------------------------------------------------------------
        | Query riwayat
        |--------------------------------------------------------------------------
        */
        $query = $this
            ->riwayatQuery($konsumen)
            ->with([
                'kendaraan',
                'user',
                'detailJasas.jasa',
                'detailSpareparts.sparepart',
                'catatanRekomendasis',
                'nota',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Filter berdasarkan kendaraan
        |--------------------------------------------------------------------------
        */
        if ($kendaraan) {
            $query->where(
                'kendaraan_id',
                $kendaraan->id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Filter pencarian nomor plat
        |--------------------------------------------------------------------------
        |
        | Spasi dihilangkan agar:
        |
        | BM 1234 AA
        |
        | tetap dapat dicari dengan:
        |
        | BM1234AA
        |
        */
        if ($searchPlat !== '') {

            $platDicari = strtoupper(
                str_replace(
                    ' ',
                    '',
                    $searchPlat
                )
            );


            $query->whereHas(
                'kendaraan',
                function (Builder $kendaraanQuery) use ($platDicari) {

                    $kendaraanQuery->whereRaw(
                        "REPLACE(UPPER(plat_nomor), ' ', '') LIKE ?",
                        [
                            '%' . $platDicari . '%',
                        ]
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $riwayat = $query
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | VIEW RIWAYAT
        |--------------------------------------------------------------------------
        |
        | Folder:
        |
        | resources/views/pelanggan/riwayat-servis/
        |
        | File:
        |
        | riwayatServis.blade.php
        |
        */
        return view(
            'pelanggan.riwayat-servis.riwayatServis',
            [
                'kendaraans' => $kendaraans,
                'kendaraanAktif' => $kendaraan,
                'riwayat' => $riwayat,
                'searchPlat' => $searchPlat,
                'totalKendaraan' => $totalKendaraan,
            ]
        );
    }


    /**
     * ================================================================
     * DETAIL SATU REKAM SERVIS
     * ================================================================
     */
    public function detailServis($id)
    {
        $konsumen = $this->konsumen();


        /*
        |--------------------------------------------------------------------------
        | Akun belum terhubung
        |--------------------------------------------------------------------------
        */
        if (! $konsumen) {
            return redirect()
                ->route('pelanggan.riwayat')
                ->with(
                    'error',
                    'Akun Anda belum terhubung dengan data pelanggan.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Ambil detail servis lengkap
        |--------------------------------------------------------------------------
        */
        $servis = RekamServis::with([
                'kendaraan.konsumen',
                'user',
                'detailSpareparts.sparepart',
                'detailJasas.jasa',
                'catatanRekomendasis',
                'nota',
            ])
            ->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Validasi kepemilikan
        |--------------------------------------------------------------------------
        */
        if (
            ! $servis->kendaraan
            || (int) $servis->kendaraan->konsumen_id
                !== (int) $konsumen->id
        ) {
            return redirect()
                ->route('pelanggan.riwayat')
                ->with(
                    'error',
                    'Anda tidak memiliki otorisasi melihat rekam kendaraan ini.'
                );
        }


        return view(
            'pelanggan.detail',
            [
                'servis' => $servis,
            ]
        );
    }


    /**
     * ================================================================
     * ROUTE LAMA / KOMPATIBILITAS
     * ================================================================
     *
     * Halaman rekam-servis lama sekarang diarahkan
     * ke halaman Riwayat Servis baru.
     */
    public function rekamServis(Request $request)
    {
        $parameter = [];


        if ($request->filled('plat')) {
            $parameter['plat'] = $request->query('plat');
        }


        return redirect()->route(
            'pelanggan.riwayat',
            $parameter
        );
    }


    /**
     * ================================================================
     * QUERY DASAR RIWAYAT SERVIS
     * ================================================================
     */
    private function riwayatQuery(
        Konsumen $konsumen
    ): Builder {
        return RekamServis::query()
            ->whereIn(
                'kendaraan_id',
                $konsumen
                    ->kendaraans()
                    ->select('id')
            )
            ->orderByDesc('tanggal_servis')
            ->orderByDesc('id');
    }


    /**
     * ================================================================
     * MENGAMBIL KONSUMEN DARI AKUN LOGIN
     * ================================================================
     *
     * Prioritas:
     *
     * 1. users.konsumen_id
     * 2. users.no_hp = konsumens.no_hp
     *
     */
    private function konsumen(): ?Konsumen
    {
        $user = Auth::user();


        if (! $user) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Prioritas pertama: konsumen_id
        |--------------------------------------------------------------------------
        */
        if ($user->konsumen_id) {

            $konsumen = Konsumen::find(
                $user->konsumen_id
            );


            if ($konsumen) {
                return $konsumen;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Cadangan: nomor HP
        |--------------------------------------------------------------------------
        */
        if ($user->no_hp) {

            return Konsumen::where(
                'no_hp',
                $user->no_hp
            )->first();
        }


        return null;
    }
}