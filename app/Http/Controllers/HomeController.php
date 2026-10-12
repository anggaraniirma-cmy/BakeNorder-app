<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Halaman beranda: daftar produk + terlaris.
     */
    public function index(Request $request)
    {
        $produk  = Produk::orderBy('id')->get();
        $terlaris = Produk::limit(4)->get();

        $menuOpen = $request->query('menu') === 'all';

        $cart = $request->session()->get('cart', []);
        $totalCart = 0;
        foreach ($cart as $item) {
            $totalCart += $item['harga'] * $item['jumlah'];
        }

        return view('home', compact('produk', 'terlaris', 'menuOpen', 'cart', 'totalCart'));
    }
}
