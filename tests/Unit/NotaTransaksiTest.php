<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\NotaTransaksi;
use App\Models\RekamServis;

class NotaTransaksiTest extends TestCase
{
    /**
     * NT-01
     * Menguji label pembayaran lunas.
     */
    public function test_label_pembayaran_lunas(): void
    {
        $nota = new NotaTransaksi([
            'status_pembayaran' => 'lunas',
        ]);

        $this->assertSame(
            'LUNAS',
            $nota->status_pembayaran_label
        );
    }

    /**
     * NT-02
     * Menguji label pembayaran sebagian.
     */
    public function test_label_pembayaran_sebagian(): void
    {
        $nota = new NotaTransaksi([
            'status_pembayaran' => 'dibayar_sebagian',
        ]);

        $this->assertSame(
            'DIBAYAR SEBAGIAN',
            $nota->status_pembayaran_label
        );
    }

    /**
     * NT-03
     * Menguji label pembayaran belum lunas.
     */
    public function test_label_pembayaran_belum_lunas(): void
    {
        $nota = new NotaTransaksi([
            'status_pembayaran' => 'belum_lunas',
        ]);

        $this->assertSame(
            'BELUM LUNAS',
            $nota->status_pembayaran_label
        );
    }

    /**
     * NT-04
     * Menguji status pembayaran yang tidak dikenal.
     */
    public function test_label_status_tidak_dikenal_menghasilkan_belum_lunas(): void
    {
        $nota = new NotaTransaksi([
            'status_pembayaran' => 'status_tidak_dikenal',
        ]);

        $this->assertSame(
            'BELUM LUNAS',
            $nota->status_pembayaran_label
        );
    }

    /**
     * NT-05
     * Menguji total modal sparepart tanpa rekam servis.
     */
    public function test_modal_sparepart_nol_jika_tidak_ada_rekam_servis(): void
    {
        $nota = new NotaTransaksi();

        $nota->setRelation('rekamServis', null);

        $this->assertSame(
            0.0,
            $nota->totalModalSparepart()
        );
    }

    /**
     * NT-06
     * Menguji pengambilan modal sparepart dari rekam servis.
     */
    public function test_modal_sparepart_mengambil_nilai_dari_rekam_servis(): void
    {
        $rekamServis = $this->createMock(RekamServis::class);

        $rekamServis
            ->expects($this->once())
            ->method('totalModalSparepart')
            ->willReturn(150000.0);

        $nota = new NotaTransaksi();

        $nota->setRelation('rekamServis', $rekamServis);

        $this->assertSame(
            150000.0,
            $nota->totalModalSparepart()
        );
    }

    /**
     * NT-07
     * Menguji perhitungan laba bersih.
     */
    public function test_laba_bersih_dihitung_dengan_benar(): void
    {
        $rekamServis = $this->createMock(RekamServis::class);

        $rekamServis
            ->expects($this->once())
            ->method('totalModalSparepart')
            ->willReturn(150000.0);

        $nota = new NotaTransaksi([
            'total_biaya' => 500000,
        ]);

        $nota->setRelation('rekamServis', $rekamServis);

        $this->assertSame(
            350000.0,
            $nota->labaBersih()
        );
    }

    /**
     * NT-08
     * Menguji format tanggal nota.
     */
    public function test_tanggal_label_menggunakan_format_indonesia(): void
    {
        $nota = new NotaTransaksi([
            'tanggal_cetak' => '2026-11-04',
        ]);

        $this->assertSame(
            '04 November 2026',
            $nota->tanggal_label
        );
    }

    /**
     * NT-09
     * Menguji tanggal nota kosong.
     */
    public function test_tanggal_label_menghasilkan_strip_jika_kosong(): void
    {
        $nota = new NotaTransaksi([
            'tanggal_cetak' => null,
        ]);

        $this->assertSame(
            '-',
            $nota->tanggal_label
        );
    }

    /**
     * NT-10
     * Menguji status nota lunas.
     */
    public function test_status_lunas_menghasilkan_true(): void
    {
        $nota = new NotaTransaksi([
            'status_pembayaran' => 'lunas',
        ]);

        $this->assertTrue($nota->lunas);
    }

    /**
     * NT-11
     * Menguji status nota belum lunas.
     */
    public function test_status_belum_lunas_menghasilkan_false(): void
    {
        $nota = new NotaTransaksi([
            'status_pembayaran' => 'belum_lunas',
        ]);

        $this->assertFalse($nota->lunas);
    }
}