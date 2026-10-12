<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun | BakeNOrder</title>
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
<link rel="icon" type="image/png" href="{{ asset('assets/purnama_circle.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>

<div class="split-container">

    <!-- KIRI: Branding -->
    <div class="split-left">
        <div class="brand-content">
            <h1 class="brand-name">BakeNOrder</h1>
            <p class="brand-tagline">Cake &amp; Cookies</p>
            <p class="brand-desc">Bergabung dan mulai pesan<br>kue favoritmu sekarang.</p>
        </div>
    </div>

    <!-- KANAN: Form Register -->
    <div class="split-right">
        <div class="form-box">

            <div class="form-header">
                <h2>Buat akun baru</h2>
                <p>Daftar dan nikmati kemudahan BakeNOrder</p>
            </div>

            @if ($errors->any())
                <div style="color:red;margin-bottom:12px;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="field">
                    <span class="field-icon">👤</span>
                    <input type="text" name="nama" placeholder="Nama Lengkap" value="{{ old('nama') }}" required autocomplete="name">
                </div>

                <div class="field">
                    <span class="field-icon">✉️</span>
                    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required autocomplete="email">
                </div>

                <div class="field">
                    <span class="field-icon">🔒</span>
                    <input type="password" id="password" name="password" placeholder="Password" required autocomplete="new-password">
                    <span class="toggle" onclick="togglePassword(this)" aria-label="Tampilkan password">🙈</span>
                </div>

                <button type="submit" class="btn-daftar">Daftar Akun</button>

            </form>

            <div class="link">
                Sudah punya akun? <a href="{{ route('login') }}">Login sekarang</a><br></br>
                <a href="{{ route('home') }}"><- Kembali ke Beranda</a>
            </div>

        </div>
    </div>

</div>

<script>
function togglePassword(icon) {
    const input = document.getElementById("password");
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "🙉";
    } else {
        input.type = "password";
        icon.textContent = "🙈";
    }
}
</script>

@include('partials.transisi')

</body>
</html>
