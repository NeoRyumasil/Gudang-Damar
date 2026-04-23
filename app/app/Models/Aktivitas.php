<?php

namespace App\Models;

use Illuminate\Support\Collection;

class Aktivitas
{
    public static function all(array $filters = []): Collection
    {
        $pesanan     = self::fromPesanan($filters);
        $barangLog   = self::fromAktivitasBarang($filters);
        $servis      = self::fromServis($filters);

        $merged = $pesanan->concat($barangLog)->concat($servis);

        return $merged->sortByDesc(function ($item) {
            return $item['tanggal_transaksi'] ?? '';
        })->values();
    }

    protected static function fromPesanan(array $filters = []): Collection
    {
        $query = Pesanan::query()->with('barang');

        if (!empty($filters['search'])) {
            $query->where('nama_barang', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggalpemesanan', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggalpemesanan', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($row) {
            $hargaSatuan = $row->harga ?? ($row->barang->harga ?? 0);
            $totalHarga  = $hargaSatuan * $row->jumlah;

            return [
                'id'                => $row->id_pesanan,
                'jenis'             => 'Pesanan',
                'sub_jenis'         => null,
                'nama_barang'       => $row->nama_barang,
                'bahan'             => $row->bahan,
                'kategori'          => $row->bahan,
                'jumlah'            => $row->jumlah,
                'harga_satuan'      => $hargaSatuan,
                'total_harga'       => $totalHarga,
                'pendapatan'        => $row->pendapatan ?? ($row->tanggalterkirim ? $totalHarga : 0),
                'catatan'           => $row->catatan,
                'bentuk'            => $row->bentuk,
                'ukuran'            => $row->ukuran,
                'ketebalan'         => $row->ketebalan,
                'tanggalpemesanan'  => $row->tanggalpemesanan,
                'tanggalterkirim'   => $row->tanggalterkirim,
                'tanggal_transaksi' => $row->tanggalpemesanan,
                'status'            => $row->tanggalterkirim ? 'Selesai' : 'Dipesan',
            ];
        });
    }

    /**
     * ✨ Semua aktivitas barang dari tabel aktivitas_barang (jual & ubah_stok).
     * Setiap baris log = 1 baris di Riwayat (histori lengkap).
     */
    protected static function fromAktivitasBarang(array $filters = []): Collection
    {
        $query = AktivitasBarang::query();

        if (!empty($filters['search'])) {
            $query->where('nama_barang', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal', '<=', $filters['end_date']);
        }

        return $query->orderByDesc('tanggal')->get()->map(function ($row) {
            $isJual = $row->jenis === 'jual';

            return [
                'id'                => $row->id_aktivitas,       // pakai ID log (unik per transaksi)
                'jenis'             => 'Barang',
                'sub_jenis'         => $isJual ? 'Terjual' : 'Stok',
                'nama_barang'       => $row->nama_barang,
                'bahan'             => null,
                'kategori'          => '-',
                'jumlah'            => $row->jumlah,
                'harga_satuan'      => $isJual ? $row->harga_satuan : null,   // restok tidak tampilkan harga
                'total_harga'       => $isJual ? $row->pendapatan : null,     // restok bukan pendapatan
                'pendapatan'        => $row->pendapatan,
                'catatan'           => $row->catatan,
                'bentuk'            => null,
                'ukuran'            => null,
                'ketebalan'         => null,
                'tanggalpemesanan'  => $row->tanggal,
                'tanggalterkirim'   => $isJual ? $row->tanggal : null,
                'tanggal_transaksi' => $row->tanggal,
                'status'            => $isJual ? 'Terjual' : 'Dibeli',
            ];
        });
    }

    protected static function fromServis(array $filters = []): Collection
    {
        $query = Servis::query();

        if (!empty($filters['search'])) {
            $query->where('nama_barang', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggalpemesanan', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggalpemesanan', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($row) {
            $isSelesai = !empty($row->tanggalterkirim)
                && !str_starts_with($row->tanggalterkirim, '1970-01-01')
                && !str_starts_with($row->tanggalterkirim, '0000-00-00');

            return [
                'id'                => $row->id_pesanan,
                'jenis'             => 'Servis',
                'sub_jenis'         => null,
                'nama_barang'       => $row->nama_barang,
                'bahan'             => $row->bahan,
                'kategori'          => $row->bahan,
                'jumlah'            => $row->jumlah,
                'harga_satuan'      => $row->harga,
                'total_harga'       => $row->harga ?? 0,
                'pendapatan'        => $row->pendapatan ?? ($isSelesai ? ($row->harga ?? 0) : 0),
                'catatan'           => $row->catatan,
                'bentuk'            => $row->bentuk_barang,
                'ukuran'            => null,
                'ketebalan'         => null,
                'tanggalpemesanan'  => $row->tanggalpemesanan,
                'tanggalterkirim'   => $row->tanggalterkirim,
                'tanggal_transaksi' => $row->tanggalpemesanan,
                'status'            => $isSelesai ? 'Selesai' : 'Diproses',
            ];
        });
    }

    /**
     * Statistik total untuk summary cards.
     */
    public static function stats(): array
    {
        $totalPesanan = Pesanan::count();
        $totalServis  = Servis::count();
        $totalBarang  = Barang::sum('jumlah') ?? 0;

        // Total transaksi sekarang include jumlah log barang
        $totalLogBarang = AktivitasBarang::count();

        // ✨ Pendapatan dari log barang jenis 'jual' (akurat per transaksi)
        $pendapatanPesanan = Pesanan::whereNotNull('tanggalterkirim')->sum('pendapatan') ?? 0;
        $pendapatanServis  = Servis::whereNotNull('pendapatan')->sum('pendapatan') ?? 0;
        $pendapatanBarang  = AktivitasBarang::where('jenis', 'jual')->sum('pendapatan') ?? 0;

        // Total barang terjual = dari log, bukan akumulasi di tabel barang
        $totalBarangTerjual = AktivitasBarang::where('jenis', 'jual')->sum('jumlah') ?? 0;

        return [
            'total_transaksi'       => $totalPesanan + $totalServis + $totalLogBarang,
            'total_barang_terjual'  => $totalBarangTerjual,
            'total_stok_barang'     => $totalBarang,
            'total_pendapatan'      => $pendapatanPesanan + $pendapatanServis + $pendapatanBarang,
            'pendapatan_pesanan'    => $pendapatanPesanan,
            'pendapatan_servis'     => $pendapatanServis,
            'pendapatan_barang'     => $pendapatanBarang,
            'total_pesanan'         => $totalPesanan,
            'total_servis'          => $totalServis,
        ];
    }
}