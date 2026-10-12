<!DOCTYPE html>
<html>
<head>
<title>Edit Produk</title>
<style>
    html, body{
    height:100%;
}

body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#F6EFE8;

    /* === STICKY FOOTER FIX === */
    display:flex;
    flex-direction:column;
}
</style>

</head>
<body>

@include('partials.navbar')

<h2>Edit Produk</h2>

<form action="{{ route('admin.produk.update') }}" method="POST" enctype="multipart/form-data">
@csrf
<input type="hidden" name="id" value="{{ $produk->id }}">

<input name="nama_produk" value="{{ $produk->nama_produk }}" required>
<br><br>

<input name="harga" type="number" value="{{ $produk->harga }}" required>
<br><br>

<img src="{{ $produk->foto_url }}" width="100">
<br><br>

<input type="file" name="foto">
<br><small>Kosongkan jika tidak ingin ganti gambar</small>
<br><br>

<button type="submit">Update</button>
</form>
@include('partials.footer')

</body>
</html>
