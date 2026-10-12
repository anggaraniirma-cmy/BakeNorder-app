<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Nama hari Indonesia untuk filter (index 0 = Senin ... 6 = Minggu).
     */
    private const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    /**
     * Dashboard admin: ringkasan + produk + riwayat pesanan (terfilter & paginasi).
     */
    public function dashboard(Request $request)
    {
        $filter = $this->ambilFilter($request);

        $pesanan = $this->queryPesanan($filter)
            ->paginate(10)
            ->withQueryString();

        // Ringkasan (angka nyata dari DB, hindari JOIN ganda)
        $hariIni   = now()->toDateString();
        $ringkasan = [
            'pesanan_hari_ini'    => Pesanan::whereDate('created_at', $hariIni)->count(),
            'penjualan_hari_ini'  => (int) Pesanan::whereDate('created_at', $hariIni)
                                        ->where('status', 'Selesai')
                                        ->sum('total'),
            'menunggu'            => Pesanan::where('status', 'Menunggu')->count(),
            'diambil_hari_ini'    => Pesanan::whereDate('tanggal_jadi', $hariIni)
                                        ->whereIn('status', ['Menunggu', 'Selesai'])
                                        ->count(),
        ];

        // Produk: tampilkan ringkas (5 terbaru), sisanya di halaman kelola
        $produk = Produk::orderByDesc('id')->get();

        return view('admin.dashboard', [
            'ringkasan'    => $ringkasan,
            'produk'       => $produk,
            'pesanan'      => $pesanan,
            'filter'       => $filter,
            'hariList'     => self::HARI,
            'statusList'   => ['Menunggu', 'Selesai', 'Ditolak'],
        ]);
    }

    /**
     * Validasi & normalisasi parameter filter dari request.
     */
    private function ambilFilter(Request $request): array
    {
        $hari = $request->query('hari');
        $hari = ($hari !== null && $hari !== '' && is_numeric($hari) && (int) $hari >= 0 && (int) $hari <= 6)
            ? (int) $hari
            : null;

        $status = $request->query('status');
        $status = in_array($status, ['Menunggu', 'Selesai', 'Ditolak'], true) ? $status : null;

        $dari = $request->query('dari');
        $dari = ($dari && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) ? $dari : null;

        $sampai = $request->query('sampai');
        $sampai = ($sampai && preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) ? $sampai : null;

        // Pastikan rentang tidak terbalik
        if ($dari && $sampai && $dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        $cari = trim((string) $request->query('cari', ''));
        $cari = $cari === '' ? null : mb_substr($cari, 0, 100);

        return compact('hari', 'status', 'dari', 'sampai', 'cari');
    }

    /**
     * Query riwayat pesanan dengan filter hari (hari pembuatan), rentang tanggal,
     * status, dan pencarian nama pelanggan.
     */
    private function queryPesanan(array $filter)
    {
        $query = Pesanan::with(['detail.produk', 'pembayaran'])
            ->orderByDesc('created_at');

        // Filter hari pembuatan (0=Senin ... 6=Minggu, sesuai WEEKDAY MySQL)
        if ($filter['hari'] !== null) {
            $query->whereRaw('WEEKDAY(created_at) = ?', [$filter['hari']]);
        }

        // Rentang tanggal pembuatan
        if ($filter['dari']) {
            $query->whereDate('created_at', '>=', $filter['dari']);
        }
        if ($filter['sampai']) {
            $query->whereDate('created_at', '<=', $filter['sampai']);
        }

        if ($filter['status']) {
            $query->where('status', $filter['status']);
        }

        if ($filter['cari']) {
            $query->where('nama', 'like', '%' . $this->escapeLike($filter['cari']) . '%');
        }

        return $query;
    }

    /**
     * Escape karakter wildcard LIKE agar pencarian aman.
     */
    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /**
     * Endpoint polling: ringkasan + baris pesanan untuk halaman & filter aktif.
     */
    public function pesananData(Request $request)
    {
        $filter = $this->ambilFilter($request);

        $paginator = $this->queryPesanan($filter)
            ->paginate(10)
            ->withQueryString();

        $hariIni = now()->toDateString();

        return response()->json([
            'ringkasan' => [
                'pesanan_hari_ini'   => Pesanan::whereDate('created_at', $hariIni)->count(),
                'penjualan_hari_ini' => (int) Pesanan::whereDate('created_at', $hariIni)
                                            ->where('status', 'Selesai')
                                            ->sum('total'),
                'menunggu'           => Pesanan::where('status', 'Menunggu')->count(),
                'diambil_hari_ini'   => Pesanan::whereDate('tanggal_jadi', $hariIni)
                                            ->whereIn('status', ['Menunggu', 'Selesai'])
                                            ->count(),
            ],
            'html'  => view('admin._pesanan_tabel', ['pesanan' => $paginator])->render(),
            'total' => $paginator->total(),
            'waktu' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Halaman kasir (POS) untuk admin.
     */
    public function kasir()
    {
        $produk = Produk::orderBy('nama_produk')->get();

        return view('admin.kasir', compact('produk'));
    }

    /**
     * Simpan transaksi kasir: pesanan + detail + pembayaran.
     */
    public function kasirStore(Request $request)
    {
        $validated = $request->validate([
            'nama'              => 'nullable|string|max:100',
            'items'             => 'required|array',
            'items.*.id'        => 'required|integer|exists:produk,id',
            'items.*.jumlah'    => 'required|integer|min:1',
            'metode_pembayaran' => 'required|in:Tunai,Transfer,COD',
            'bayar'             => 'nullable|integer|min:0',
        ]);

        $produk = Produk::whereIn('id', collect($validated['items'])->pluck('id'))->get()->keyBy('id');

        $items = collect($validated['items'])->map(function ($row) use ($produk) {
            $p        = $produk->get($row['id']);
            $jumlah   = (int) $row['jumlah'];
            $subtotal = $p->harga * $jumlah;

            return [
                'produk'   => $p,
                'jumlah'   => $jumlah,
                'subtotal' => $subtotal,
            ];
        });

        $total = $items->sum('subtotal');

        if ($total <= 0) {
            return redirect()->route('admin.kasir')->with('kasir_error', 'Keranjang kasir masih kosong.');
        }

        $metode = $validated['metode_pembayaran'];
        $bayar  = (int) ($validated['bayar'] ?? 0);
        $kembali = ($metode === 'Tunai' && $bayar > 0) ? max(0, $bayar - $total) : 0;

        $kasir = DB::transaction(function () use ($validated, $items, $total, $metode, $request) {
            $pesanan = Pesanan::create([
                'user_id'         => $request->user()->id,
                'nama'            => $validated['nama'] ?: 'Pelanggan Kasir',
                'tanggal_jadi'    => now()->toDateString(),
                'jam_pengambilan' => now()->format('H:i'),
                'alamat'          => 'Pembelian di tempat (kasir)',
                'total'           => $total,
                'status'          => 'Selesai',
            ]);

            foreach ($items as $item) {
                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'produk_id'  => $item['produk']->id,
                    'jumlah'     => $item['jumlah'],
                    'subtotal'   => $item['subtotal'],
                ]);
            }

            Pembayaran::create([
                'pesanan_id'        => $pesanan->id,
                'metode_pembayaran' => $metode,
                'bukti_transfer'    => '',
            ]);

            return $pesanan;
        });

        return redirect()->route('admin.kasir')->with('kasir_sukses', [
            'pesanan_id' => $kasir->id,
            'total'      => $total,
            'metode'     => $metode,
            'bayar'      => $bayar,
            'kembali'    => $kembali,
        ]);
    }

    /**
     * Halaman struk yang bisa dicetak (thermal / kertas kecil).
     */
    public function struk($id)
    {
        $pesanan = Pesanan::with(['detail.produk', 'pembayaran'])->findOrFail($id);

        return view('admin.struk', compact('pesanan'));
    }

    /**
     * Halaman pengiriman (delivery): daftar pesanan berkoordinat + navigasi.
     */
    public function pengiriman()
    {
        $tokoLat = Pengaturan::ambil('toko_latitude');
        $tokoLng = Pengaturan::ambil('toko_longitude');

        $pesanan = Pesanan::with(['detail.produk', 'pembayaran'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('id')
            ->get();

        // Hitung jarak (haversine) dari toko bila koordinat toko tersedia
        $adaToko = $tokoLat !== null && $tokoLng !== null;

        $daftar = $pesanan->map(function ($p) use ($tokoLat, $tokoLng, $adaToko) {
            $jarak = null;
            if ($adaToko) {
                $jarak = $this->hitungJarak((float) $tokoLat, (float) $tokoLng, (float) $p->latitude, (float) $p->longitude);
            }

            return [
                'pesanan' => $p,
                'jarak_km' => $jarak,
            ];
        });

        // Urutkan dari terdekat bila ada jarak
        if ($adaToko) {
            $daftar = $daftar->sortBy('jarak_km')->values();
        }

        return view('admin.pengiriman', [
            'daftar'    => $daftar,
            'tokoLat'   => $tokoLat,
            'tokoLng'   => $tokoLng,
            'adaToko'   => $adaToko,
        ]);
    }

    /**
     * Simpan koordinat toko (titik asal pengiriman).
     */
    public function simpanLokasiToko(Request $request)
    {
        $validated = $request->validate([
            'toko_latitude'  => 'required|numeric|between:-90,90',
            'toko_longitude' => 'required|numeric|between:-180,180',
        ]);

        Pengaturan::simpan('toko_latitude', $validated['toko_latitude']);
        Pengaturan::simpan('toko_longitude', $validated['toko_longitude']);

        return redirect()->route('admin.pengiriman')->with('toko_sukses', 'Lokasi toko berhasil disimpan.');
    }

    /**
     * Hitung jarak dua koordinat dalam km (rumus haversine).
     */
    private function hitungJarak(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371; // radius bumi (km)
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }

    public function tambahProduk(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:100',
            'harga'       => 'required|integer',
            'deskripsi'   => 'nullable|string',
            'foto'        => 'required|image',
        ]);

        $file = $request->file('foto');
        $foto = $file->getClientOriginalName();
        $file->move(public_path('assets'), $foto);

        Produk::create([
            'nama_produk' => $validated['nama_produk'],
            'harga'       => $validated['harga'],
            'deskripsi'   => $validated['deskripsi'] ?? '',
            'foto'        => $foto,
        ]);

        return redirect()->route('admin.dashboard');
    }

    public function editProduk($id)
    {
        $produk = Produk::findOrFail($id);

        return view('admin.editproduk', compact('produk'));
    }

    public function updateProduk(Request $request)
    {
        $validated = $request->validate([
            'id'          => 'required|integer|exists:produk,id',
            'nama_produk' => 'required|string|max:100',
            'harga'       => 'required|integer',
            'foto'        => 'nullable|image',
        ]);

        $produk = Produk::findOrFail($validated['id']);

        $produk->nama_produk = $validated['nama_produk'];
        $produk->harga       = $validated['harga'];

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $foto = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets'), $foto);
            $produk->foto = $foto;
        }

        $produk->save();

        return redirect()->route('admin.dashboard');
    }

    public function hapusProduk($id)
    {
        Produk::where('id', $id)->delete();

        return redirect()->route('admin.dashboard');
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'id'     => 'required|integer|exists:pesanan,id',
            'status' => 'required|in:Menunggu,Selesai,Ditolak',
        ]);

        Pesanan::where('id', $validated['id'])->update(['status' => $validated['status']]);

        return redirect()->route('admin.dashboard');
    }
}
