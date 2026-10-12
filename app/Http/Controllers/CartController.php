<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Halaman keranjang belanja.
     */
    public function index(Request $request)
    {
        $cart  = $request->session()->get('cart', []);
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['harga'] * $item['jumlah'];
        }

        return view('cart', compact('cart', 'total'));
    }

    /**
     * Tambah produk ke keranjang (setara konfigurasi/tambahcart.php).
     */
    public function tambah(Request $request)
    {
        $id     = (int) $request->query('id');
        $jumlah = max(1, (int) $request->query('jumlah', 1));
        $from   = $request->query('from', 'menu');

        $produk = Produk::find($id);

        if (!$produk) {
            return redirect()->route('home');
        }

        $cart = $request->session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['jumlah'] += $jumlah;
        } else {
            $cart[$id] = [
                'id'     => $produk->id,
                'nama'   => $produk->nama_produk,
                'harga'  => $produk->harga,
                'jumlah' => $jumlah,
            ];
        }

        $request->session()->put('cart', $cart);

        if ($from === 'terlaris') {
            return redirect()->route('home');
        }

        return redirect()->route('home', ['menu' => 'all']);
    }

    /**
     * Hapus item dari keranjang (setara konfigurasi/hapuscart.php).
     */
    public function hapus(Request $request, $id)
    {
        $cart = $request->session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $request->session()->put('cart', $cart);
        }

        return back();
    }

    /**
     * Kosongkan seluruh keranjang.
     */
    public function kosongkan(Request $request)
    {
        $request->session()->forget('cart');

        return redirect()->route('cart')->with('cart_sukses', 'Semua item di keranjang sudah dihapus.');
    }
}
