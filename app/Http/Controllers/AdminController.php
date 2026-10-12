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
     * Dashboard admin: produk, pesanan masuk, pembayaran.
     */
    public function dashboard()
    {
        $produk = Produk::orderByDesc('id')->get();

        $pesanan = Pesanan::with(['detail.produk', 'pembayaran'])
            ->orderByDesc('id')
            ->get();

        $pembayaran = Pembayaran::with('pesanan')
            ->orderByDesc('id')
            ->get();

        return view('admin.dashboard', compact('produk', 'pesanan', 'pembayaran'));
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
