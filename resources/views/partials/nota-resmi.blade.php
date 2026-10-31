        <article id="printArea"
            class="rounded-2xl border border-neutral-300 bg-white p-6 text-neutral-800 sm:p-10
                   {{ $notaAkhir ? 'nota-lunas' : '' }}">

            {{-- KOP: logo + nama bengkel (memakai <div>, bukan <header>, agar tidak
                 ikut disembunyikan oleh CSS layout admin saat cetak) --}}
            <div class="nota-kop">
                <img src="{{ asset('images/logo_bengkel.jpe') }}"
                    alt="Logo Baba Auto Service" class="nota-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="nota-logo-cadangan" style="display:none" aria-hidden="true">BAS</div>

                <div class="nota-kop-teks">
                    <h2 class="nota-nama">BABA AUTO SERVICE</h2>
                    <p class="nota-tagline">SIRAKA &bull; SISTEM INFORMASI PERAWATAN KENDARAAN</p>
                    <p class="nota-alamat">Jl. Soebrantas No. 88, Panam, Pekanbaru</p>
                </div>
            </div>

            {{-- JUDUL + NOMOR/TANGGAL --}}
            <div class="nota-judul">
                <h3 class="nota-judul-teks">{{ $judul }}</h3>
                <div class="nota-meta">
                    <div class="nota-meta-sel">
                        <span>NO. DOKUMEN</span>
                        <strong>{{ $nomor }}</strong>
                    </div>
                    <div class="nota-meta-sel">
                        <span>TANGGAL</span>
                        <strong>{{ \Carbon\Carbon::parse($servis->tanggal_servis)->format('d/m/Y') }}</strong>
                    </div>
                </div>
            </div>

            {{-- PELANGGAN & KENDARAAN --}}
            <div class="nota-info">
                <dl class="kv-blok">
                    <div class="kv"><dt>Nama</dt><dd><strong>{{ $servis->kendaraan->konsumen->nama_lengkap }}</strong></dd></div>
                    <div class="kv"><dt>Telepon</dt><dd>{{ $servis->kendaraan->konsumen->no_hp ?: '-' }}</dd></div>
                    <div class="kv"><dt>Diagnosis</dt><dd>{{ $servis->diagnosa_akhir ?? $servis->diagnosa_awal ?? '-' }}</dd></div>
                    @if($servis->tindakan_servis)
                        <div class="kv"><dt>Tindakan</dt><dd>{{ $servis->tindakan_servis }}</dd></div>
                    @endif
                </dl>

                <dl class="kv-blok">
                    <div class="kv"><dt>Plat Nomor</dt><dd><strong>{{ $servis->kendaraan->plat_nomor }}</strong></dd></div>
                    <div class="kv"><dt>Merk / Tipe</dt><dd>{{ $servis->kendaraan->merk }} {{ $servis->kendaraan->tipe_model }}</dd></div>
                    <div class="kv"><dt>KM Masuk</dt><dd>{{ number_format($servis->km_masuk ?? 0, 0, ',', '.') }} KM</dd></div>
                    @if($servis->km_keluar !== null)
                        <div class="kv"><dt>KM Keluar</dt><dd>{{ number_format($servis->km_keluar, 0, ',', '.') }} KM</dd></div>
                    @endif
                </dl>
            </div>

            {{-- BIAYA SUKU CADANG --}}
            @if($servis->detailSpareparts->isNotEmpty())
                <section class="nota-seksi">
                    <h4 class="nota-seksi-judul">Biaya Suku Cadang</h4>
                    <div class="nota-table-wrap">
                        <table class="nota-table">
                            <thead>
                                <tr>
                                    <th class="col-no">No</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Total</th>
                                    @if($bolehEdit)
                                        <th class="no-print text-right">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($servis->detailSpareparts as $item)
                                    <tr>
                                        <td class="col-no">{{ $loop->iteration }}</td>
                                        <td class="col-desk">
                                            {{ $item->sparepart->nama_barang ?? 'Sparepart' }}
                                        </td>
                                        <td class="text-center" data-label="Qty">{{ $item->qty }}</td>
                                        <td class="text-right" data-label="Harga">
                                            {{ $rp($item->harga_jual_saat_transaksi) }}
                                        </td>
                                        <td class="text-right font-bold" data-label="Jumlah">
                                            {{ $rp($item->harga_jual_saat_transaksi * $item->qty) }}
                                        </td>
                                        @if($bolehEdit)
                                            <td class="no-print text-right">
                                                <form method="POST"
                                                    action="{{ route('admin.rekam-servis.sparepart.hapus', [$servis->id, $item->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        data-confirm="Hapus sparepart ini dari rincian?"
                                                        class="font-bold text-rose-600">Hapus</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="ttl-label">Jumlah total</td>
                                    <td class="text-right">{{ $rp($subSparepart) }}</td>
                                    @if($bolehEdit)<td class="no-print"></td>@endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            @endif

            {{-- BIAYA JASA SERVIS --}}
            @if($servis->detailJasas->isNotEmpty())
                <section class="nota-seksi">
                    <h4 class="nota-seksi-judul">Biaya Jasa Servis</h4>
                    <div class="nota-table-wrap">
                        <table class="nota-table">
                            <thead>
                                <tr>
                                    <th class="col-no">No</th>
                                    <th>Deskripsi</th>
                                    <th class="text-right">Total</th>
                                    @if($bolehEdit)
                                        <th class="no-print text-right">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($servis->detailJasas as $item)
                                    @php
                                        $qtyJasa = (int) ($item->qty ?? 1);
                                        $diskonJasa = (float) ($item->diskon ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="col-no">{{ $loop->iteration }}</td>
                                        <td class="col-desk">
                                            {{ $item->jasa->nama_jasa ?? 'Jasa Servis' }}
                                            @if($qtyJasa > 1 || $diskonJasa > 0)
                                                <div class="nota-ket-kecil">
                                                    {{ $qtyJasa }} &times; {{ $rp($item->harga_saat_transaksi) }}@if($diskonJasa > 0), diskon {{ $rp($diskonJasa) }}@endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-right font-bold" data-label="Jumlah">
                                            {{ $rp($barisJasa($item)) }}
                                        </td>
                                        @if($bolehEdit)
                                            <td class="no-print text-right">
                                                <form method="POST"
                                                    action="{{ route('admin.rekam-servis.jasa.hapus', [$servis->id, $item->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        data-confirm="Hapus jasa ini dari rincian?"
                                                        class="font-bold text-rose-600">Hapus</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="ttl-label">Jumlah total</td>
                                    <td class="text-right">{{ $rp($subJasa) }}</td>
                                    @if($bolehEdit)<td class="no-print"></td>@endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            @endif
    <section class="nota-seksi">
        <h4 class="nota-seksi-judul">Biaya Suku Cadang</h4>
        <div class="nota-table-wrap">
            <table class="nota-table">
                <thead>
                    <tr>
                        <th class="col-no">No</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">Diskon</th>
                        <th class="text-right">Total</th>
                        @if($bolehEdit)
                            <th class="no-print text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($servis->detailSpareparts as $item)
                        @php
                            $subtotalSP = $item->harga_jual_saat_transaksi * $item->qty;
                            $diskonSP = $item->diskon_nominal ?? 0;
                            $totalSP = $subtotalSP - $diskonSP;
                        @endphp
                        <tr>
                            <td class="col-no">{{ $loop->iteration }}</td>
                            <td class="col-desk">
                                {{ $item->sparepart->nama_barang ?? 'Sparepart' }}
                            </td>
                            <td class="text-center" data-label="Qty">{{ $item->qty }}</td>
                            <td class="text-right" data-label="Harga">
                                {{ $rp($item->harga_jual_saat_transaksi) }}
                            </td>
                            <td class="text-right" data-label="Subtotal">
                                {{ $rp($subtotalSP) }}
                            </td>
                            @if($diskonSP > 0)
                            <td class="text-right text-rose-500" data-label="Diskon">
                                <div class="text-[10px] italic">disc {{ number_format($item->diskon_persen, 2) }}%</div>
                                <div>({{ $rp($diskonSP) }})</div>
                            </td>
                            @else
                            <td class="text-right" data-label="Diskon">-</td>
                            @endif
                            <td class="text-right font-bold" data-label="Total">
                                {{ $rp($totalSP) }}
                            </td>
                            @if($bolehEdit)
                                <td class="no-print text-right">
                                    <form method="POST" action="{{ route('admin.rekam-servis.sparepart.hapus', [$servis->id, $item->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-confirm="Hapus sparepart ini dari rincian?" class="font-bold text-rose-600">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="ttl-label">Jumlah total</td>
                        <td class="text-right">{{ $rp($subSparepart) }}</td>
                        <td class="text-right">-</td>
                        <td class="text-right font-bold">{{ $rp($subSparepart - $servis->detailSpareparts->sum('diskon_nominal')) }}</td>
                        @if($bolehEdit)<td class="no-print"></td>@endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
@endif

            {{-- BIAYA JASA SERVIS --}}
            @if($servis->detailJasas->isNotEmpty())
    <section class="nota-seksi">
        <h4 class="nota-seksi-judul">Biaya Jasa Servis</h4>
        <div class="nota-table-wrap">
            <table class="nota-table">
                <thead>
                    <tr>
                        <th class="col-no">No</th>
                        <th>Deskripsi</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">Diskon</th>
                        <th class="text-right">Total</th>
                        @if($bolehEdit)
                            <th class="no-print text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($servis->detailJasas as $item)
                        @php
                            $qtyJasa = (int) ($item->qty ?? 1);
                            $subtotalJasa = $item->harga_saat_transaksi * $qtyJasa;
                            $diskonJasa = $item->diskon_nominal ?? 0;
                            $totalJasa = $subtotalJasa - $diskonJasa;
                        @endphp
                        <tr>
                            <td class="col-no">{{ $loop->iteration }}</td>
                            <td class="col-desk">
                                {{ $item->jasa->nama_jasa ?? 'Jasa Servis' }}
                            </td>
                            <td class="text-right" data-label="Subtotal">
                                {{ $rp($subtotalJasa) }}
                            </td>
                            @if($diskonJasa > 0)
                            <td class="text-right text-rose-500" data-label="Diskon">
                                <div class="text-[10px] italic">disc {{ number_format($item->diskon_persen, 2) }}%</div>
                                <div>({{ $rp($diskonJasa) }})</div>
                            </td>
                            @else
                            <td class="text-right" data-label="Diskon">-</td>
                            @endif
                            <td class="text-right font-bold" data-label="Total">
                                {{ $rp($totalJasa) }}
                            </td>
                            @if($bolehEdit)
                                <td class="no-print text-right">
                                    <form method="POST" action="{{ route('admin.rekam-servis.jasa.hapus', [$servis->id, $item->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-confirm="Hapus jasa ini dari rincian?" class="font-bold text-rose-600">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="ttl-label">Jumlah total</td>
                        <td class="text-right">{{ $rp($subJasa) }}</td>
                        <td class="text-right">-</td>
                        <td class="text-right font-bold">{{ $rp($subJasa - $servis->detailJasas->sum('diskon_nominal')) }}</td>
                        @if($bolehEdit)<td class="no-print"></td>@endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
@endif

            @unless($adaBiaya)
                <p class="nota-kosong">Belum ada biaya servis.</p>
            @endunless

            {{-- METODE PEMBAYARAN (kiri) + TOTAL (kanan) --}}
            <div class="nota-bottom">
                <div class="nota-bottom-kiri">
                    <h4 class="nota-seksi-judul">Pembayaran</h4>
                    @if($pembayaran->isNotEmpty())
                        @foreach($pembayaran as $bayar)
                            <p><strong>{{ strtoupper($bayar->metode_pembayaran) }}</strong> — {{ $rp($bayar->nominal_dialokasikan) }}</p>
                            <p class="nota-abu">{{ $bayar->tanggal_bayar?->format('d/m/Y H:i') ?? '-' }}</p>
                        @endforeach
                    @elseif($nota)
                        <p class="nota-abu">Belum ada pembayaran</p>
                    @else
                        <p class="nota-abu">Estimasi biaya, belum menjadi tagihan.</p>
                    @endif

                    @if($nota?->catatan_nota)
                        <h4 class="nota-seksi-judul nota-jarak">Catatan Pemeriksaan Lanjutan</h4>
                        <p class="whitespace-pre-line">{{ $nota->catatan_nota }}</p>
                    @endif

                    @foreach($servis->catatanRekomendasis as $catatan)
                        <p class="nota-jarak">{{ $catatan->catatan }}</p>
                    @endforeach

                </div>

                <div class="nota-total-box">
                    <div class="tot"><span>SUB TOTAL</span><strong>{{ $rp($subtotal) }}</strong></div>
                    <div class="tot"><span>DISKON</span><strong id="labelDiskon">- {{ $rp($diskon) }}</strong></div>
                    <div class="tot tot-utama"><span>TOTAL TAGIHAN</span><strong id="labelTotal">{{ $rp($total) }}</strong></div>

                    @if($nota)
                        <div class="tot"><span>TOTAL DIBAYAR</span><strong>{{ $rp($totalDibayar) }}</strong></div>
                        <div class="tot"><span>SISA TAGIHAN</span><strong>{{ $rp($sisaTagihan) }}</strong></div>
                        @if($totalTips > 0)
                            <div class="tot"><span>TIPS</span><strong>{{ $rp($totalTips) }}</strong></div>
                        @endif
                        @if($totalKembalian > 0)
                            <div class="tot"><span>KEMBALIAN</span><strong>{{ $rp($totalKembalian) }}</strong></div>
                        @endif
                    @endif

                    <div class="tot tot-catatan"><span>CATATAN</span><strong>{{ $statusDok }}</strong></div>
                </div>
            </div>

            {{-- TANDA TANGAN --}}
            <div class="nota-ttd">
                <div class="ttd-kolom">
                    <p>Pelanggan</p>
                    <div class="ttd-ruang"></div>
                    <p class="ttd-nama">( {{ $servis->kendaraan->konsumen->nama_lengkap }} )</p>
                </div>
                <div class="ttd-kolom ttd-kanan">
                    <p>Hormat kami,</p>
                    <div class="ttd-ruang"></div>
                    <p class="ttd-nama">( Baba Auto Service )</p>
                </div>
            </div>
        </article>
