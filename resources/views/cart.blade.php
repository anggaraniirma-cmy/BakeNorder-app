<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Keranjang Belanja | BakeNOrder</title>
<link rel="icon" type="image/png" href="{{ asset('assets/purnama_circle.png') }}">
<link rel="stylesheet" href="{{ asset('css/cart.css') }}">
</head>
<body>

@include('partials.navbar')

<div class="container">
<div class="box">
    <!-- HEADER -->
    <div class="cart-head">
        <div class="cart-title">
            <span class="cart-ico">🛒</span>
            <div>
                <h3>Keranjang Belanja</h3>
                <p class="cart-sub">
                    {{ empty($cart) ? 'Belum ada item di keranjang' : count($cart).' item siap dipesan' }}
                </p>
            </div>
        </div>

        @if (!empty($cart))
            <span class="cart-count">{{ count($cart) }} Item</span>
        @endif
    </div>

    @if (session('cart_sukses'))
        <div class="cart-alert">✓ {{ session('cart_sukses') }}</div>
    @endif

    @if (empty($cart))
        <div class="empty">
            <div class="empty-ico">🧺</div>
            <p>Keranjang kamu masih kosong</p>
            <span class="empty-hint">Yuk pilih kue favoritmu dulu 🍰</span>
            <a href="{{ route('home') }}" class="btn-back">← Kembali Belanja</a>
        </div>

    @else
        <div class="cart-list">
            @foreach ($cart as $id => $item)
                @php
                    $subtotal = $item['harga'] * $item['jumlah'];
                    $initial  = mb_strtoupper(mb_substr($item['nama'], 0, 1));
                @endphp
                <div class="cart-row">
                    <div class="cart-thumb">{{ $initial }}</div>

                    <div class="cart-info">
                        <span class="cart-name">{{ $item['nama'] }}</span>
                        <span class="cart-price">Rp {{ number_format($item['harga'], 0, ',', '.') }} / pcs</span>
                    </div>

                    <div class="cart-qty">×{{ $item['jumlah'] }}</div>

                    <div class="cart-subtotal">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>

                    <a href="{{ route('cart.hapus', $id) }}"
                       class="btn-hapus"
                       onclick="return confirm('Hapus {{ $item['nama'] }} dari keranjang?')">
                        🗑 Hapus
                    </a>
                </div>
            @endforeach
        </div>

        <!-- HAPUS SEMUA -->
        <div class="cart-bulk">
            <form action="{{ route('cart.kosongkan') }}" method="POST"
                  onsubmit="return confirm('Hapus SEMUA item dari keranjang?');">
                @csrf
                <button type="submit" class="btn-hapus-semua">🗑 Hapus Semua Keranjang</button>
            </form>
        </div>

        <div class="cart-total">
            <span>Total Pembayaran</span>
            <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
        </div>

        <div class="actions">
            <a href="{{ route('home') }}" class="btn-back">← Kembali Belanja</a>

            @auth
                <a href="{{ route('home') }}" class="btn-checkout">Checkout</a>
            @else
                <a href="{{ route('login') }}" class="btn-login">Login dulu untuk Checkout</a>
            @endauth
        </div>
    @endif
</div>
</div>

@include('partials.footer')

@include('partials.transisi')

</body>
</html>
