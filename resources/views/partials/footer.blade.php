<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

footer {
    background: #5c3d1e;
    color: #f5e6d3;
    font-family: 'Poppins', Arial, sans-serif;
    margin-top: auto;
}

.footer-main {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 40px;
    padding: 48px 60px 36px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

/* ===== BRAND ===== */
.footer-brand .brand-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.footer-brand .brand-logo img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.footer-brand .brand-logo strong {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    line-height: 1.1;
}

.footer-brand .brand-logo small {
    display: block;
    font-size: 9px;
    font-weight: 600;
    color: #d4a96a;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.footer-brand p {
    font-size: 13px;
    color: #c8a882;
    line-height: 1.8;
    max-width: 240px;
    margin: 0 0 20px;
}

/* Sosmed */
.footer-socials {
    display: flex;
    gap: 10px;
}

.footer-socials a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    color: #fff;
    font-size: 16px;
    text-decoration: none;
    transition: background 0.2s, transform 0.2s;
}

.footer-socials a:hover {
    background: #8B5A2B;
    transform: translateY(-2px);
}

/* ===== KOLOM ===== */
.footer-col h4 {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin: 0 0 16px;
}

.footer-col ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.footer-col ul li a {
    text-decoration: none;
    color: #c8a882;
    font-size: 13px;
    transition: color 0.2s, padding-left 0.2s;
    display: inline-block;
}

.footer-col ul li a:hover {
    color: #fff;
    padding-left: 4px;
}

/* Info kontak */
.footer-col .info-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
    font-size: 13px;
    color: #c8a882;
    line-height: 1.5;
}

.footer-col .info-item span.ic {
    font-size: 15px;
    flex-shrink: 0;
    margin-top: 1px;
}

.footer-col .info-item a {
    color: #c8a882;
    text-decoration: none;
    transition: color 0.2s;
}

.footer-col .info-item a:hover { color: #fff; }

/* ===== BOTTOM BAR ===== */
.footer-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 60px;
    font-size: 12px;
    color: #a07850;
    flex-wrap: wrap;
    gap: 8px;
}

.footer-bottom a {
    color: #a07850;
    text-decoration: none;
    transition: color 0.2s;
}

.footer-bottom a:hover { color: #fff; }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .footer-main {
        grid-template-columns: 1fr;
        gap: 28px;
        padding: 36px 24px 28px;
    }

    .footer-brand p { max-width: 100%; }

    .footer-bottom {
        padding: 14px 24px;
        flex-direction: column;
        text-align: center;
        gap: 6px;
    }
}
</style>

<footer>
    <div class="footer-main">

        <!-- BRAND -->
        <div class="footer-brand">
            <div class="brand-logo">
                <img src="{{ asset('assets/purnama.jpeg') }}" alt="Logo BakeNOrder">
                <div>
                    <strong>BakeNOrder</strong>
                    <small>Cake and Cookies</small>
                </div>
            </div>
            <p>Hadirkan kebahagiaan lewat setiap gigitan. Kue custom berkualitas untuk momen spesialmu.</p>
            <div class="footer-socials">
                <a href="https://wa.me/6281330243018?" title="WhatsApp">💬</a>
                <a href="#" title="Instagram">📸</a>
                <a href="#" title="Facebook">👍</a>
            </div>
        </div>

        <!-- NAVIGASI -->
        <div class="footer-col">
            <h4>Navigasi</h4>
            <ul>
                <li><a href="{{ route('home') }}">Beranda</a></li>
                <li><a href="{{ route('nota') }}">Pesanan Saya</a></li>
                <li><a href="{{ route('cart') }}">Keranjang</a></li>
                <li><a href="{{ route('login') }}">Login</a></li>
            </ul>
        </div>

        <!-- KONTAK -->
        <div class="footer-col">
            <h4>Hubungi Kami</h4>
            <div class="info-item">
                <span class="ic">💬</span>
                <a href="https://wa.me/6281330243018?">WhatsApp Kami</a>
            </div>
            <div class="info-item">
                <span class="ic">🏦</span>
                <span>BRI · a.n Purnama</span>
            </div>
            <div class="info-item">
                <span class="ic">🕐</span>
                <span>Senin – Sabtu<br>08.00 – 20.00 WIB</span>
            </div>
            <div class="info-item">
                <span class="ic">📍</span>
                <span>Indonesia</span>
            </div>
        </div>

    </div>

    <div class="footer-bottom">
        <span>© 2026 BakeNOrder · Purnama Cake and Cookies. All rights reserved.</span>
        <span>Made with 🍰 for sweet moments</span>
    </div>
</footer>
