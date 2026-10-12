<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengiriman | Admin BakeNOrder</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/pengiriman.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/purnama_circle.png') }}">
</head>
<body>

@include('partials.navbar')

<div class="kirim-wrap">

    <div class="kirim-head">
        <div>
            <h2>🚚 Pengiriman</h2>
            <p class="sub">Daftar pesanan dengan titik lokasi pembeli — urut dari yang terdekat.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="link-dash">← Dashboard</a>
    </div>

    @if (session('toko_sukses'))
        <div class="kirim-alert">✓ {{ session('toko_sukses') }}</div>
    @endif

    <!-- ============ LOKASI TOKO ============ -->
    <div class="toko-box">
        <div class="toko-judul">
            <h3>📍 Titik Awal (Lokasi Toko)</h3>
            <p>Dipakai sebagai titik asal rute pengiriman.</p>
        </div>

        <form action="{{ route('admin.pengiriman.toko') }}" method="POST" class="toko-form" id="formToko">
            @csrf
            <div class="toko-fields">
                <label>Latitude
                    <input type="text" name="toko_latitude" id="tokoLat" value="{{ $tokoLat }}" placeholder="-7.1234567" required>
                </label>
                <label>Longitude
                    <input type="text" name="toko_longitude" id="tokoLng" value="{{ $tokoLng }}" placeholder="112.1234567" required>
                </label>
            </div>
            <div class="toko-aksi">
                <button type="button" class="btn-gps-toko" id="btnGpsToko">📍 Ambil dari Lokasi Saya</button>
                <button type="submit" class="btn-simpan-toko">Simpan Lokasi Toko</button>
            </div>
            <p class="toko-status" id="tokoStatus"></p>
        </form>

        @if ($adaToko)
            <p class="toko-terisi">✓ Titik toko aktif: <strong>{{ $tokoLat }}, {{ $tokoLng }}</strong>
                — <a href="https://www.google.com/maps?q={{ $tokoLat }},{{ $tokoLng }}" target="_blank">Lihat di Maps</a>
            </p>
        @else
            <p class="toko-kosong">⚠ Titik toko belum diatur. Isi dulu agar jarak & rute bisa dihitung.</p>
        @endif
    </div>

    <!-- ============ DAFTAR PESANAN ============ -->
    <div class="kirim-listahead">
        <h3>Pesanan Siap Antar</h3>
        <span class="kirim-count">{{ $daftar->count() }} pesanan berkoordinat</span>
    </div>

    @if ($daftar->isEmpty())
        <div class="kirim-kosong">
            Belum ada pesanan dengan titik lokasi.<br>
            <span>Pesanan muncul di sini setelah pembeli menekan tombol "Gunakan Lokasi Saya (GPS)" di form pesanan.</span>
        </div>
    @else
        <div class="kirim-grid">
            @foreach ($daftar as $row)
                @php
                    $p = $row['pesanan'];
                    $lat = $p->latitude;
                    $lng = $p->longitude;
                    $namaProduk = $p->detail->map(fn($d) => ($d->produk->nama_produk ?? 'Produk') . ' (' . $d->jumlah . 'x)')->implode(', ');
                    $status = $p->status ?? 'Menunggu';
                    $statusKelas = $status === 'Selesai' ? 'done' : ($status === 'Ditolak' ? 'reject' : 'wait');
                    $kurir = $adaToko ? "https://www.google.com/maps/dir/?api=1&origin={$tokoLat},{$tokoLng}&destination={$lat},{$lng}&travelmode=driving" : "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}&travelmode=driving";
                @endphp
                <div class="kirim-kartu">
                    <div class="kirim-kartu-head">
                        <div class="kk-nama">{{ $p->nama ?: 'Pelanggan' }}</div>
                        <span class="badge badge-{{ $statusKelas }}">{{ $status }}</span>
                    </div>

                    <div class="kk-produk">{{ $namaProduk ?: 'Produk' }}</div>

                    <div class="kk-info">
                        <div><span class="kk-k">Tanggal</span><span>{{ optional($p->tanggal_jadi)->format('d M Y') }}{{ $p->jam_pengambilan ? ' · ' . substr($p->jam_pengambilan, 0, 5) : '' }}</span></div>
                        <div><span class="kk-k">Alamat</span><span>{{ $p->alamat }}</span></div>
                        <div><span class="kk-k">Koordinat</span><span class="kk-koord">{{ $lat }}, {{ $lng }}</span></div>
                        <div><span class="kk-k">Total</span><span>Rp {{ number_format($p->total, 0, ',', '.') }} <em class="kk-bayar">({{ $p->pembayaran->metode_pembayaran ?? 'COD' }})</em></span></div>
                        @if ($row['jarak_km'] !== null)
                            <div><span class="kk-k">Jarak dari toko</span><span class="kk-jarak">{{ $row['jarak_km'] }} km</span></div>
                        @endif
                    </div>

                    <div class="kk-aksi">
                        <a href="{{ $kurir }}" target="_blank" class="btn-navigasi">🧭 Navigasi</a>
                        <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank" class="btn-lihat">📍 Lihat Titik</a>
                        <a href="{{ route('admin.kasir.struk', $p->id) }}" target="_blank" class="btn-nota">🧾 Nota</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<script>
(function () {
    var btn = document.getElementById('btnGpsToko');
    var status = document.getElementById('tokoStatus');
    var latIn = document.getElementById('tokoLat');
    var lngIn = document.getElementById('tokoLng');
    if (!btn) return;

    btn.addEventListener('click', function () {
        if (!navigator.geolocation) {
            status.textContent = "Browser ini tidak mendukung lokasi.";
            status.className = "toko-status err";
            return;
        }
        btn.disabled = true;
        status.textContent = "Mengambil lokasi toko... izinkan akses lokasi.";
        status.className = "toko-status loading";

        navigator.geolocation.getCurrentPosition(function (pos) {
            latIn.value = pos.coords.latitude.toFixed(7);
            lngIn.value = pos.coords.longitude.toFixed(7);
            status.textContent = "✓ Koordinat terisi. Jangan lupa klik \"Simpan Lokasi Toko\".";
            status.className = "toko-status ok";
            btn.disabled = false;
        }, function (err) {
            status.textContent = "Gagal mengambil lokasi: " + (err.code === 1 ? "izin ditolak." : "tidak tersedia / waktu habis.");
            status.className = "toko-status err";
            btn.disabled = false;
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
    });
})();
</script>

@include('partials.footer')

@include('partials.transisi')

</body>
</html>
