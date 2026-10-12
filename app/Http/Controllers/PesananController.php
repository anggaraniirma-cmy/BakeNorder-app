<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    /**
     * Simpan pesanan baru (setara konfigurasi/simpan_pesanan.php).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'              => 'required|string|max:100',
            'tanggal_jadi'      => 'required|date',
            'jam_pengambilan'   => 'required|date_format:H:i',
            'alamat'            => 'required|string',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'metode_pembayaran' => 'required|in:COD,Transfer',
            'bukti'             => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Keranjang kosong, silakan pilih produk dulu.');
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['harga'] * $item['jumlah'];
        }

        // Upload bukti transfer
        $namaBukti = '';
        if ($request->hasFile('bukti')) {
            $file      = $request->file('bukti');
            $namaBukti = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets'), $namaBukti);
        }

        DB::transaction(function () use ($validated, $cart, $total, $namaBukti, $request) {
            $pesanan = Pesanan::create([
                'user_id'         => $request->user()->id,
                'nama'            => $validated['nama'],
                'tanggal_jadi'    => $validated['tanggal_jadi'],
                'jam_pengambilan' => $validated['jam_pengambilan'],
                'alamat'          => $validated['alamat'],
                'latitude'        => $validated['latitude'] ?? null,
                'longitude'       => $validated['longitude'] ?? null,
                'total'           => $total,
                'status'          => 'Menunggu',
            ]);

            foreach ($cart as $item) {
                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'produk_id'  => $item['id'],
                    'jumlah'     => $item['jumlah'],
                    'subtotal'   => $item['harga'] * $item['jumlah'],
                ]);
            }

            Pembayaran::create([
                'pesanan_id'        => $pesanan->id,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'bukti_transfer'    => $namaBukti,
            ]);
        });

        $request->session()->forget('cart');

        return redirect()->route('nota');
    }

    /**
     * Halaman nota pesanan user (setara users/nota.php).
     */
    public function nota(Request $request)
    {
        $all_pesanan = Pesanan::with(['detail.produk', 'pembayaran'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        $pesananData = $all_pesanan->map(function ($p) {
            $status = $p->status ?? 'Menunggu';
            $metode = $p->pembayaran->metode_pembayaran ?? 'COD';

            $produk = $p->detail->map(function ($d) {
                return [
                    'nama'  => $d->produk->nama_produk ?? 'Produk',
                    'qty'   => (int) $d->jumlah,
                    'harga' => (int) $d->subtotal,
                ];
            })->values()->all();

            return [
                'nama'       => $p->nama,
                'tanggal'    => optional($p->tanggal_jadi)->format('Y-m-d'),
                'jam'        => $p->jam_pengambilan ? substr($p->jam_pengambilan, 0, 5) : '-',
                'created_at' => optional($p->created_at)->toDateTimeString(),
                'alamat'     => $p->alamat,
                'lat'        => $p->latitude,
                'lng'        => $p->longitude,
                'metode'     => $metode,
                'status'     => $status,
                'total'      => (int) $p->total,
                'produk'     => $produk,
                'bukti'      => $p->pembayaran->bukti_transfer ?? '',
            ];
        })->values()->all();

        return view('nota', compact('all_pesanan', 'pesananData'));
    }
}
