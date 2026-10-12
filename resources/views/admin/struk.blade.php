<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $pesanan->id }} | BakeNOrder</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            background: #e9e2da;
            color: #000;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px 40px;
        }

        /* ===== KERTAS STRUK ===== */
        .struk {
            width: 80mm;
            max-width: 100%;
            background: #fff;
            padding: 14px 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,.15);
            font-size: 12px;
            line-height: 1.5;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }

        .brand-nama { font-size: 16px; font-weight: bold; letter-spacing: .5px; }
        .brand-tag  { font-size: 10px; letter-spacing: 1px; }

        .garis {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            gap: 8px;
        }

        table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        table td { padding: 2px 0; vertical-align: top; }
        .td-nama { word-break: break-word; }
        .td-qty  { white-space: nowrap; text-align: center; width: 34px; }
        .td-harga{ white-space: nowrap; text-align: right; width: 74px; }

        .total {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
            padding: 6px 0;
        }

        .bayar-row {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
        }

        .footer-struk {
            text-align: center;
            font-size: 10.5px;
            margin-top: 10px;
            line-height: 1.6;
        }

        /* ===== TOMBOL (tidak ikut tercetak) ===== */
        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
            width: 80mm;
            max-width: 100%;
        }
        .toolbar button,
        .toolbar a {
            flex: 1;
            text-align: center;
            text-decoration: none;
            padding: 11px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }
        .btn-cetak { background: #8B5A2B; color: #fff; }
        .btn-cetak:hover { background: #6d4420; }
        .btn-kembali { background: #fff; color: #8B5A2B; border: 1.5px solid #8B5A2B; }

        /* ===== ATURAN CETAK ===== */
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none !important; }
            .struk {
                width: auto;
                max-width: 80mm;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
            @page { margin: 4mm; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <button type="button" class="btn-cetak" onclick="window.print()">🖨 Cetak Struk</button>
    <a href="{{ route('admin.kasir') }}" class="btn-kembali">← Kasir</a>
</div>

<div class="struk">
    <div class="center">
        <div class="brand-nama">PURNAMA</div>
        <div class="brand-tag">CAKE AND COOKIES</div>
        <div class="brand-tag">BakeNOrder</div>
    </div>

    <hr class="garis">

    <div class="meta"><span>No. Pesanan</span><span class="bold">#{{ $pesanan->id }}</span></div>
    <div class="meta"><span>Tanggal</span><span>{{ optional($pesanan->created_at)->format('d/m/Y H:i') }}</span></div>
    <div class="meta"><span>Pelanggan</span><span>{{ $pesanan->nama ?: 'Umum' }}</span></div>
    <div class="meta"><span>Kasir</span><span>{{ auth()->user()->nama ?? '-' }}</span></div>

    <hr class="garis">

    <table>
        @foreach ($pesanan->detail as $d)
            <tr>
                <td class="td-nama">{{ $d->produk->nama_produk ?? 'Produk' }}</td>
                <td class="td-qty">{{ $d->jumlah }}x</td>
                <td class="td-harga">{{ number_format($d->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <hr class="garis">

    <div class="total">
        <span>TOTAL</span>
        <span>Rp {{ number_format($pesanan->total, 0, ',', '.') }}</span>
    </div>

    <div class="bayar-row">
        <span>Metode</span>
        <span>{{ $pesanan->pembayaran->metode_pembayaran ?? 'Tunai' }}</span>
    </div>

    <hr class="garis">

    <div class="footer-struk">
        Terima kasih telah berbelanja<br>
        Kue custom untuk momen spesialmu<br>
        WA: 0813-3024-3018
    </div>
</div>

<script>
    // otomatis buka dialog cetak saat halaman struk dibuka
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 350);
    });
</script>

</body>
</html>
