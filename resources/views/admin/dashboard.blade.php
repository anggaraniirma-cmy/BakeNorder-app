<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/purnama_circle.png') }}">
</head>
<body>

@include('partials.navbar')

<!-- TAMBAH PRODUK -->
<div class="box">
    <h3>Tambah Produk</h3>
    <form action="{{ route('admin.produk.tambah') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="text" name="nama_produk" placeholder="Nama Produk" required>
        <input type="number" name="harga" placeholder="Harga" required>
        <input type="file" name="foto" required>
        <button type="submit">Tambah Produk</button>
    </form>
</div>

<!-- DATA PRODUK -->
<div class="box">
    <h3>Data Produk</h3>
    <table>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Harga</th>
            <th>Foto</th>
            <th>Aksi</th>
        </tr>
        @foreach ($produk as $i => $p)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $p->nama_produk }}</td>
            <td>Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
            <td><img src="{{ $p->foto_url }}" width="60"></td>
            <td>
                <a href="{{ route('admin.produk.edit', $p->id) }}">Edit</a>
                |
                <a href="{{ route('admin.produk.hapus', $p->id) }}" onclick="return confirm('Hapus produk ini?')">Hapus</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>

<!-- DATA PESANAN -->
<div class="box">
    <h3>Pesanan Masuk</h3>
    <table>
        <tr>
            <th>Nama</th>
            <th>Daftar Kue</th>
            <th>Total</th>
            <th>Tanggal Jadi</th>
            <th>Jam Ambil</th>
            <th>Alamat</th>
            <th>Lokasi</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Struk</th>
        </tr>
        @foreach ($pesanan as $ps)
        <tr>
            <td>{{ $ps->nama }}</td>
            <td>
                <ul style="padding-left:15px;">
                @foreach ($ps->detail as $d)
                    <li>{{ $d->produk->nama_produk ?? 'Produk' }} ({{ $d->jumlah }}x)</li>
                @endforeach
                </ul>
            </td>
            <td>Rp {{ number_format($ps->total, 0, ',', '.') }}</td>
            <td>{{ $ps->tanggal_jadi }}</td>
            <td>{{ $ps->jam_pengambilan ? substr($ps->jam_pengambilan, 0, 5) : '-' }}</td>
            <td>{{ $ps->alamat }}</td>
            <td>
                @if ($ps->latitude && $ps->longitude)
                    <a href="https://www.google.com/maps?q={{ $ps->latitude }},{{ $ps->longitude }}" target="_blank">📍 Maps</a>
                @else
                    -
                @endif
            </td>
            <td>
                @if (!empty($ps->pembayaran->metode_pembayaran))
                    {{ $ps->pembayaran->metode_pembayaran }}
                @else
                    <span style="color:red;">COD</span>
                @endif
            </td>
            <td>
                <form action="{{ route('admin.status.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $ps->id }}">
                    <select name="status">
                        <option value="Menunggu" {{ $ps->status == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="Selesai"  {{ $ps->status == 'Selesai'  ? 'selected' : '' }}>Selesai</option>
                        <option value="Ditolak"  {{ $ps->status == 'Ditolak'  ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </td>
            <td>
                <a href="{{ route('admin.kasir.struk', $ps->id) }}" target="_blank">🖨 Cetak</a>
            </td>
        </tr>
        @endforeach
    </table>
</div>

<!-- DATA PEMBAYARAN -->
<div class="box">
    <h3>Data Pembayaran</h3>
    <table>
        <tr>
            <th>Nama</th>
            <th>Metode</th>
            <th>Bukti</th>
            <th>Tanggal</th>
        </tr>
        @foreach ($pembayaran as $pb)
        <tr>
            <td>{{ $pb->pesanan->nama ?? '-' }}</td>
            <td>
                @if (!empty($pb->metode_pembayaran))
                    {{ $pb->metode_pembayaran }}
                @else
                    <span style="color:red;">COD</span>
                @endif
            </td>
            <td>
                @if (!empty($pb->bukti_transfer))
                    <img src="{{ asset('assets/' . $pb->bukti_transfer) }}" width="80">
                @else
                    <span style="color:red;">COD</span>
                @endif
            </td>
            <td>{{ $pb->tanggal_bayar }}</td>
        </tr>
        @endforeach
    </table>
</div>

@include('partials.footer')

</body>
</html>
