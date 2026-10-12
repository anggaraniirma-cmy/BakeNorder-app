<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard | Admin BakeNOrder</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/purnama_circle.png') }}">
</head>
<body>

@include('partials.navbar')

<div class="dash-wrap">

    <!-- ===== HEADER ===== -->
    <div class="dash-head">
        <div>
            <h1>Dashboard Admin</h1>
            <p class="dash-sub">Ringkasan penjualan &amp; riwayat pesanan BakeNOrder</p>
        </div>
        <div class="head-aksi">
            <a href="{{ route('admin.kasir') }}" class="btn-head">🧾 Kasir</a>
            <a href="{{ route('admin.pengiriman') }}" class="btn-head ghost">🚚 Pengiriman</a>
        </div>
    </div>

    <!-- ===== RINGKASAN ===== -->
    <div class="ringkasan" id="ringkasan">
        <div class="kartu-ringkas">
            <div class="kr-ikon">📦</div>
            <div>
                <span class="kr-label">Pesanan Hari Ini</span>
                <strong class="kr-angka" data-kunci="pesanan_hari_ini">{{ $ringkasan['pesanan_hari_ini'] }}</strong>
            </div>
        </div>
        <div class="kartu-ringkas">
            <div class="kr-ikon">💰</div>
            <div>
                <span class="kr-label">Penjualan Hari Ini</span>
                <strong class="kr-angka" data-kunci="penjualan_hari_ini" data-rupiah="1">Rp {{ number_format($ringkasan['penjualan_hari_ini'], 0, ',', '.') }}</strong>
                <span class="kr-catatan">hanya status Selesai</span>
            </div>
        </div>
        <div class="kartu-ringkas">
            <div class="kr-ikon warn">⏳</div>
            <div>
                <span class="kr-label">Menunggu Diproses</span>
                <strong class="kr-angka" data-kunci="menunggu">{{ $ringkasan['menunggu'] }}</strong>
            </div>
        </div>
        <div class="kartu-ringkas">
            <div class="kr-ikon">🚚</div>
            <div>
                <span class="kr-label">Diambil Hari Ini</span>
                <strong class="kr-angka" data-kunci="diambil_hari_ini">{{ $ringkasan['diambil_hari_ini'] }}</strong>
                <span class="kr-catatan">sesuai tanggal jadi</span>
            </div>
        </div>
    </div>

    <!-- ===== RIWAYAT PESANAN ===== -->
    <section class="panel">
        <div class="panel-head">
            <h2>Riwayat Pesanan</h2>
            <span class="poll-info">
                <span id="pollDot" class="dot"></span>
                <span id="pollTeks">Terakhir diperbarui: {{ now()->format('H:i:s') }}</span>
            </span>
        </div>

        <!-- Filter -->
        <form method="GET" action="{{ route('admin.dashboard') }}" class="filter-bar" id="filterForm">
            <input type="hidden" name="hari" value="{{ $filter['hari'] }}">

            <div class="filter-hari">
                <a href="{{ request()->fullUrlWithQuery(['hari' => null, 'page' => null]) }}"
                   class="chip {{ $filter['hari'] === null ? 'aktif' : '' }}">Semua Hari</a>
                @foreach ($hariList as $i => $nama)
                    <a href="{{ request()->fullUrlWithQuery(['hari' => $i, 'page' => null]) }}"
                       class="chip {{ $filter['hari'] === $i ? 'aktif' : '' }}">{{ $nama }}</a>
                @endforeach
            </div>

            <div class="filter-baris">
                <div class="filter-field">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="">Semua Status</option>
                        @foreach ($statusList as $s)
                            <option value="{{ $s }}" @selected($filter['status'] === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label for="dari">Dari Tanggal</label>
                    <input type="date" name="dari" id="dari" value="{{ $filter['dari'] }}">
                </div>
                <div class="filter-field">
                    <label for="sampai">Sampai Tanggal</label>
                    <input type="date" name="sampai" id="sampai" value="{{ $filter['sampai'] }}">
                </div>
                <div class="filter-field grow">
                    <label for="cari">Cari Pelanggan</label>
                    <input type="text" name="cari" id="cari" value="{{ $filter['cari'] }}" placeholder="Nama pelanggan..." maxlength="100">
                </div>
                <div class="filter-aksi">
                    <button type="submit" class="btn-filter">Terapkan</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn-reset">Reset</a>
                </div>
            </div>
        </form>

        <!-- Tabel pesanan (di-refresh otomatis oleh polling) -->
        <div id="wadahPesanan">
            @include('admin._pesanan_tabel', ['pesanan' => $pesanan])
        </div>
    </section>

    <!-- ===== KELOLA PRODUK ===== -->
    <section class="panel">
        <div class="panel-head">
            <h2>Kelola Produk</h2>
            <span class="panel-info">{{ $produk->count() }} produk</span>
        </div>

        <details class="form-tambah">
            <summary>+ Tambah Produk Baru</summary>
            <form action="{{ route('admin.produk.tambah') }}" method="POST" enctype="multipart/form-data" class="form-produk">
                @csrf
                <div class="filter-baris">
                    <div class="filter-field grow">
                        <label for="nama_produk">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk" required maxlength="100">
                    </div>
                    <div class="filter-field">
                        <label for="harga">Harga (Rp)</label>
                        <input type="number" name="harga" id="harga" required min="0">
                    </div>
                    <div class="filter-field grow">
                        <label for="foto">Foto</label>
                        <input type="file" name="foto" id="foto" accept="image/*" required>
                    </div>
                    <div class="filter-aksi">
                        <button type="submit" class="btn-filter">Tambah</button>
                    </div>
                </div>
            </form>
        </details>

        <div class="grid-produk">
            @forelse ($produk as $p)
                <div class="kartu-produk">
                    <img src="{{ $p->foto_url }}" alt="{{ $p->nama_produk }}" loading="lazy">
                    <div class="kp-body">
                        <span class="kp-nama">{{ $p->nama_produk }}</span>
                        <span class="kp-harga">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                    </div>
                    <div class="kp-aksi">
                        <a href="{{ route('admin.produk.edit', $p->id) }}" class="btn-mini ghost">Edit</a>
                        <a href="{{ route('admin.produk.hapus', $p->id) }}" class="btn-mini hapus"
                           onclick="return confirm('Hapus produk {{ $p->nama_produk }}?')">Hapus</a>
                    </div>
                </div>
            @empty
                <p class="kosong">Belum ada produk.</p>
            @endforelse
        </div>
    </section>

</div>

<script>
(function () {
    const wadah   = document.getElementById('wadahPesanan');
    const form    = document.getElementById('filterForm');
    const dot     = document.getElementById('pollDot');
    const teks    = document.getElementById('pollTeks');
    const endpoint = "{{ route('admin.pesanan.data') }}";

    const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    let sedangKirimStatus = false;

    // tandai saat admin submit update status -> tahan auto-refresh sesaat
    document.addEventListener('submit', function (e) {
        if (e.target.closest('.form-status')) sedangKirimStatus = true;
    });

    function terapkanRingkasan(data) {
        document.querySelectorAll('.kr-angka').forEach(el => {
            const kunci = el.dataset.kunci;
            if (data[kunci] === undefined) return;
            el.textContent = el.dataset.rupiah ? rupiah(data[kunci]) : data[kunci];
        });
    }

    async function perbarui() {
        if (sedangKirimStatus) return;
        if (document.activeElement && document.activeElement.matches('#wadahPesanan input, #wadahPesanan select')) return;

        const params = new URLSearchParams();
        new FormData(form).forEach((v, k) => { if (v !== '') params.set(k, v); });
        const halSekarang = new URLSearchParams(location.search).get('page');
        if (halSekarang) params.set('page', halSekarang);

        try {
            const res = await fetch(endpoint + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (sedangKirimStatus) return;

            wadah.innerHTML = data.html;
            terapkanRingkasan(data.ringkasan);

            dot.classList.remove('err');
            teks.textContent = 'Terakhir diperbarui: ' + data.waktu;
        } catch (err) {
            dot.classList.add('err');
            teks.textContent = 'Gagal memperbarui — periksa koneksi';
        } finally {
            sedangKirimStatus = false;
        }
    }

    setInterval(perbarui, 12000);
})();
</script>

@include('partials.footer')

</body>
</html>
