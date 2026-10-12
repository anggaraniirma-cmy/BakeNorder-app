<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Produk | Admin BakeNOrder</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/admin-form.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/purnama_circle.png') }}">
</head>
<body>

@include('partials.navbar')

<div class="form-wrap">
    <div class="form-top">
        <h1>Edit Produk</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn-kembali">← Dashboard</a>
    </div>

    <div class="kartu">
        <form action="{{ route('admin.produk.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $produk->id }}">

            <div class="field-grup">
                <label for="nama_produk">Nama Produk</label>
                <input name="nama_produk" id="nama_produk" value="{{ $produk->nama_produk }}" required maxlength="100">
            </div>

            <div class="field-grup">
                <label for="harga">Harga (Rp)</label>
                <input name="harga" id="harga" type="number" value="{{ $produk->harga }}" min="0" required>
            </div>

            <div class="field-grup">
                <label>Foto Saat Ini</label>
                <div class="foto-preview">
                    <img src="{{ $produk->foto_url }}" alt="{{ $produk->nama_produk }}">
                    <span class="foto-info">Upload foto baru di bawah untuk menggantinya.</span>
                </div>
                <input type="file" name="foto" accept="image/*">
                <small class="helper">Kosongkan jika tidak ingin ganti gambar</small>
            </div>

            <button type="submit" class="btn-simpan">Update Produk</button>
        </form>
    </div>
</div>

@include('partials.footer')

</body>
</html>
