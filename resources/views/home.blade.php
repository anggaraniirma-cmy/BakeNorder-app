<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BakeNOrder</title>
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
<link rel="icon" type="image/png" href="{{ asset('assets/purnama_circle.png') }}">
</head>

<body>

@include('partials.navbar')

<!-- ================== CONTAINER ================== -->
<div class="container">

    <!-- ===== LEFT ===== -->
    <div class="left">

        <!-- TERLARIS -->
        <div class="box">
            <h3>Terlaris</h3>
            <div class="menu-grid">
            @foreach ($terlaris as $t)
                <div class="card">
                    <img src="{{ $t->foto_url }}">
                    <strong>{{ $t->nama_produk }}</strong>
                    <p>Rp {{ number_format($t->harga, 0, ',', '.') }}</p>

                    <form action="{{ route('cart.tambah') }}" method="GET">
                        <input type="hidden" name="id" value="{{ $t->id }}">
                        <input type="hidden" name="from" class="from-terlaris" value="terlaris">
                        <input type="number" name="jumlah" value="1" min="1" style="width:60px;">
                        <button type="submit">🛒 +Keranjang</button>
                    </form>
                </div>
            @endforeach
            </div>
        </div>

        <!-- MENU -->
        <div class="box">
             <h3>
                Menu
                @if ($menuOpen)
                <a href="{{ route('home') }}" class="lihatsemuanya" id="btn-sembunyikan">Sembunyikan</a>
                @else
                <a href="{{ route('home', ['menu' => 'all']) }}" class="lihatsemuanya">Lihat semuanya ></a>
                @endif
             </h3>

            <div id="semuaProduk" style="{{ $menuOpen ? 'display:block' : 'display:none' }}">
                <div class="menu-grid">
                @foreach ($produk as $m)
                    <div class="card">
                        <img src="{{ $m->foto_url }}">
                        <strong>{{ $m->nama_produk }}</strong>
                        <p>Rp {{ number_format($m->harga, 0, ',', '.') }}</p>

                        <form action="{{ route('cart.tambah') }}" method="GET">
                            <input type="hidden" name="id" value="{{ $m->id }}">
                            <input type="number" name="jumlah" value="1" min="1" style="width:60px;">
                            <button type="submit">🛒 +Keranjang</button>
                        </form>
                    </div>
                @endforeach
                </div>
            </div>
        </div>

    </div>
    <!-- ===== END LEFT ===== -->


    <!-- ===== RIGHT (FORM) ===== -->
    <div class="right">
        <div class="box form">
            <h3>Formulir Pesanan</h3>

            @if (session('error'))
                <p class="form-error">{{ session('error') }}</p>
            @endif

            <form action="{{ route('pesanan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input name="nama" placeholder="Nama" required>

                @if (!empty($cart))
                    <h4>Pesanan Kamu</h4>
                    <ul>
                    @foreach ($cart as $id => $item)
                        @php $subtotal = $item['harga'] * $item['jumlah']; @endphp
                        <li>
                            {{ $item['nama'] }}
                            ({{ $item['jumlah'] }}x)
                            - Rp {{ number_format($subtotal, 0, ',', '.') }}

                            <a href="{{ route('cart.hapus', $id) }}"
                            class="btn-hapus"
                            onclick="return confirm('Hapus {{ $item['nama'] }} dari keranjang?')">
                                🗑 Hapus
                            </a>
                        </li>
                    @endforeach
                    </ul>

                    <p class="total-note"><span>Total</span> <strong>Rp {{ number_format($totalCart, 0, ',', '.') }}</strong></p>
                @else
                    <p class="form-error">Keranjang kosong, silakan pilih produk dulu</p>
                @endif

                <label>Pilih Tanggal Jadi</label>
                <input type="date" name="tanggal_jadi" required>

                <label>Jam Pengambilan</label>
                <input type="time" name="jam_pengambilan" required>

                <textarea name="alamat" id="alamat" placeholder="Masukkan Alamat lengkap" required></textarea>

                <button type="button" class="btn-lokasi" id="btnLokasi">
                    📍 Gunakan Lokasi Saya (GPS)
                </button>
                <p class="lokasi-status" id="lokasiStatus"></p>

                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <select name="metode_pembayaran" required>
                    <option value="">Metode pembayaran</option>
                    <option>COD</option>
                    <option>Transfer</option>
                </select>

                <label>Upload Bukti Transfer jika transfer</label>
                <input type="file" name="bukti" accept="image/*">

                <button>Kirim</button>

            </form>
        </div>
    </div>
    <!-- ===== END RIGHT ===== -->

</div>
<!-- ================= END CONTAINER ================= -->

<script>
@if ($menuOpen)
    localStorage.setItem("menuOpen", "1");
@endif

var btnSembunyikan = document.getElementById("btn-sembunyikan");
if(btnSembunyikan){
    btnSembunyikan.addEventListener("click", function(){
        localStorage.removeItem("menuOpen");
    });
}

if(localStorage.getItem("menuOpen") === "1"){
    document.querySelectorAll(".from-terlaris").forEach(function(el){
        el.value = "menu";
    });
}

window.addEventListener("beforeunload", function() {
    localStorage.setItem("scrollpos", window.scrollY);
});

window.addEventListener("load", function() {
    let scrollpos = localStorage.getItem("scrollpos");
    if(scrollpos){
        window.scrollTo(0, scrollpos);
    }
});

/* ===== GPS: ambil lokasi + reverse geocode ke alamat ===== */
(function () {
    var btnLokasi = document.getElementById("btnLokasi");
    var status    = document.getElementById("lokasiStatus");
    var alamat    = document.getElementById("alamat");
    var latInput  = document.getElementById("latitude");
    var lonInput  = document.getElementById("longitude");
    if (!btnLokasi) return;

    function setStatus(msg, tipe) {
        status.textContent = msg;
        status.className = "lokasi-status" + (tipe ? " " + tipe : "");
    }

    btnLokasi.addEventListener("click", function () {
        if (!navigator.geolocation) {
            setStatus("Browser ini tidak mendukung GPS/lokasi.", "err");
            return;
        }

        btnLokasi.disabled = true;
        setStatus("Mengambil lokasi... izinkan akses lokasi di browser.", "loading");

        navigator.geolocation.getCurrentPosition(function (pos) {
            var lat = pos.coords.latitude.toFixed(7);
            var lon = pos.coords.longitude.toFixed(7);
            latInput.value = lat;
            lonInput.value = lon;

            setStatus("Lokasi didapat. Mengubah ke alamat...", "loading");

            // Reverse geocode via Nominatim (OpenStreetMap)
            var url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&lat="
                + encodeURIComponent(lat) + "&lon=" + encodeURIComponent(lon);

            fetch(url, { headers: { "Accept": "application/json" } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var teks = (data && data.display_name) ? data.display_name : "";
                    if (teks) {
                        alamat.value = teks;
                        setStatus("✓ Alamat terisi otomatis dari lokasi kamu.", "ok");
                    } else {
                        alamat.value = "Lat " + lat + ", Lng " + lon;
                        setStatus("✓ Koordinat tersimpan (alamat tidak ditemukan, silakan lengkapi manual).", "ok");
                    }
                    btnLokasi.disabled = false;
                })
                .catch(function () {
                    alamat.value = "Lat " + lat + ", Lng " + lon;
                    setStatus("✓ Koordinat tersimpan (gagal mengambil nama jalan, silakan lengkapi manual).", "ok");
                    btnLokasi.disabled = false;
                });
        }, function (err) {
            var pesan = "Gagal mengambil lokasi: ";
            if (err.code === 1)      pesan += "izin lokasi ditolak. Aktifkan izin lokasi untuk situs ini.";
            else if (err.code === 2) pesan += "lokasi tidak tersedia.";
            else if (err.code === 3) pesan += "waktu habis, coba lagi.";
            else                     pesan += "terjadi kesalahan.";
            setStatus(pesan, "err");
            btnLokasi.disabled = false;
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
    });
})();
</script>

@include('partials.footer')

@include('partials.transisi')
</body>
</html>
