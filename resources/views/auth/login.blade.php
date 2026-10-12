<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | BakeNOrder</title>
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<link rel="icon" type="image/png" href="{{ asset('assets/purnama_circle.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>

@if (session('error'))
    <script>alert('{{ session('error') }}');</script>
@endif

<div class="split-container">

    <!-- KIRI: Branding -->
    <div class="split-left">
        <div class="brand-content">
            <h1 class="brand-name">BakeNOrder</h1>
            <p class="brand-tagline">Cake &amp; Cookies</p>
            <p class="brand-desc">Pesan kue favoritmu dengan<br>mudah, cepat, dan lezat.</p>
        </div>
    </div>

    <!-- KANAN: Form Login -->
    <div class="split-right">
        <div class="form-box">

            <div class="form-header">
                <h2>Selamat datang!</h2>
                <p>Masuk ke akun BakeNOrder kamu</p>
            </div>

            <form action="{{ route('login') }}" method="POST" onsubmit="rememberLogin()">
                @csrf

                <div class="field">
                    <span class="field-icon">✉️</span>
                    <input type="email" id="email" name="email" placeholder="Email" required autocomplete="email">
                </div>

                <div class="field">
                    <span class="field-icon">🔒</span>
                    <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                    <span class="toggle" onclick="togglePassword(this)" aria-label="Tampilkan password">🙈</span>
                </div>

                <div class="remember-box">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="btn-login">Login</button>

            </form>

            <div class="link">
                Belum punya akun? <a href="{{ route('register') }}">Regis sekarang</a><br></br>
                <a href="{{ route('home') }}"><- Kembali ke Beranda</a>
            </div>

        </div>
    </div>

</div>

<script>
function togglePassword(icon) {
    const pass = document.getElementById("password");
    if (pass.type === "password") {
        pass.type = "text";
        icon.textContent = "🙉";
    } else {
        pass.type = "password";
        icon.textContent = "🙈";
    }
}

function rememberLogin() {
    if (document.getElementById("remember").checked) {
        localStorage.setItem("email", document.getElementById("email").value);
        localStorage.setItem("password", document.getElementById("password").value);
    } else {
        localStorage.removeItem("email");
        localStorage.removeItem("password");
    }
}

window.onload = function () {
    if (localStorage.getItem("email")) {
        document.getElementById("email").value = localStorage.getItem("email");
        document.getElementById("password").value = localStorage.getItem("password");
        document.getElementById("remember").checked = true;
    }
};
</script>

@include('partials.transisi')

</body>
</html>
