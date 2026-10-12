<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap');

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

header {
    background: #fff;
    padding: 0 40px;
    border-bottom: 1px solid #f0e6da;
    position: sticky;
    top: 0;
    z-index: 999;
    font-family: 'Poppins', Arial, sans-serif;
}

.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 70px;
}

/* ===== LOGO ===== */

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.logo img {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: contain;
}

.logo-text strong {
    display: block;
    font-size: 14px;
    font-weight: 800;
    color: #1a1a1a;
    letter-spacing: 0.5px;
    line-height: 1.1;
}

.logo-text small {
    display: block;
    font-size: 9px;
    font-weight: 700;
    color: #8B5A2B;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

/* ===== NAV LINKS ===== */

nav {
    display: flex;
    align-items: center;
    gap: 6px;
}

nav a {
    text-decoration: none;
    color: #555;
    font-size: 13px;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 20px;
    transition: background 0.2s, color 0.2s;
}

nav a:hover {
    background: #f5ede4;
    color: #8B5A2B;
}

nav a.kontak {
    background: #8B5A2B;
    color: #fff;
    padding: 7px 18px;
}

nav a.kontak:hover {
    background: #734621;
    color: #fff;
}

nav a.cart-link {
    font-size: 18px;
    padding: 6px 10px;
}

/* ===== HAMBURGER ===== */

.hamburger {
    display: none;
    flex-direction: column;
    gap: 5px;
    cursor: pointer;
    padding: 6px;
    border: none;
    background: none;
    border-radius: 8px;
    transition: background 0.2s;
}

.hamburger:hover {
    background: #f5ede4;
}

.hamburger span {
    display: block;
    width: 22px;
    height: 2px;
    background: #8B5A2B;
    border-radius: 2px;
    transition: all 0.3s;
}

.hamburger.open span:nth-child(1) {
    transform: translateY(7px) rotate(45deg);
}

.hamburger.open span:nth-child(2) {
    opacity: 0;
}

.hamburger.open span:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg);
}

/* ===== MOBILE MENU ===== */

.mobile-menu {
    display: none;
    flex-direction: column;
    padding: 12px 0 16px;
    border-top: 1px solid #f0e6da;
}

.mobile-menu a {
    text-decoration: none;
    color: #555;
    font-size: 14px;
    font-weight: 600;
    padding: 11px 16px;
    border-radius: 10px;
    transition: background 0.2s, color 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.mobile-menu a:hover {
    background: #f5ede4;
    color: #8B5A2B;
}

.mobile-menu a.kontak {
    margin-top: 8px;
    background: #8B5A2B;
    color: #fff;
    justify-content: center;
    font-size: 14px;
}

.mobile-menu a.kontak:hover {
    background: #734621;
    color: #fff;
}

.mobile-menu.open {
    display: flex;
}

/* ===== RESPONSIVE ===== */

@media (max-width: 768px) {
    header {
        padding: 0 20px;
    }

    nav {
        display: none;
    }

    .hamburger {
        display: flex;
    }
}
</style>

<header>
    <div class="navbar">

        <!-- LOGO -->
        <a href="{{ route('home') }}" class="logo">
            <img src="{{ asset('assets/purnama.jpeg') }}" alt="Logo BakeNOrder">

            <div class="logo-text">
                <strong>BakeNOrder</strong>
                <small>Cake and Cookies</small>
            </div>
        </a>

        <!-- DESKTOP NAV -->
        <nav>

            @if (auth()->check() && auth()->user()->isAdmin())

                <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>

                <a href="{{ route('admin.kasir') }}">Kasir</a>

                <a href="{{ route('admin.pengiriman') }}">Pengiriman</a>

                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="kontak" style="border:none;cursor:pointer;font-family:inherit;font-size:13px;font-weight:600;padding:7px 18px;border-radius:20px;background:#8B5A2B;color:#fff;">Logout</button>
                </form>

            @else

                <a href="{{ route('home') }}">Home</a>

                <a href="{{ route('nota') }}">Pesanan</a>

                <a href="{{ route('cart') }}" class="cart-link">🛒</a>

                @auth

                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="kontak" style="border:none;cursor:pointer;font-family:inherit;font-size:13px;font-weight:600;padding:7px 18px;border-radius:20px;background:#8B5A2B;color:#fff;">Logout</button>
                    </form>

                @else

                    <a href="{{ route('login') }}" class="kontak">Login</a>

                @endauth

            @endif

        </nav>

        <!-- HAMBURGER -->
        <button
            class="hamburger"
            id="hamburger"
            aria-label="Buka menu"
            aria-expanded="false"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>

    <!-- MOBILE MENU -->
    <div class="mobile-menu" id="mobileMenu">

        @if (auth()->check() && auth()->user()->isAdmin())

            <a href="{{ route('admin.dashboard') }}">📊 Dashboard Admin</a>

            <a href="{{ route('admin.kasir') }}">🧾 Kasir</a>

            <a href="{{ route('admin.pengiriman') }}">🚚 Pengiriman</a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="kontak" style="width:100%;border:none;cursor:pointer;font-family:inherit;">Logout</button>
            </form>

        @else

            <a href="{{ route('home') }}">🏠 Home</a>

            <a href="{{ route('nota') }}">📋 Pesanan</a>

            <a href="{{ route('cart') }}">🛒 Keranjang</a>

            @auth

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="kontak" style="width:100%;border:none;cursor:pointer;font-family:inherit;">Logout</button>
                </form>

            @else

                <a href="{{ route('login') }}" class="kontak">Login</a>

            @endauth

        @endif

    </div>
</header>

<script>
const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobileMenu');

hamburger.addEventListener('click', () => {

    const isOpen = mobileMenu.classList.toggle('open');

    hamburger.classList.toggle('open', isOpen);

    hamburger.setAttribute('aria-expanded', isOpen);
});

document.addEventListener('click', (e) => {

    if (
        !hamburger.contains(e.target) &&
        !mobileMenu.contains(e.target)
    ) {

        mobileMenu.classList.remove('open');

        hamburger.classList.remove('open');

        hamburger.setAttribute('aria-expanded', 'false');
    }

});
</script>
