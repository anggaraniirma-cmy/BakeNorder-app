<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pesanan</title>
    <link rel="stylesheet" href="{{ asset('css/nota.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/purnama_circle.png') }}">
</head>
<body>

@include('partials.navbar')

<!-- TAMPILAN LIST -->
<div class="list-view" id="listView">
    <div class="list-scroll">

        @if ($all_pesanan->isEmpty())
            <div class="empty-state">
                <span style="font-size:48px;">📦</span>
                <p>Belum ada pesanan</p>
            </div>
        @endif

        @foreach ($all_pesanan as $idx => $p)
            @php
                $status = $p->status ?? 'Menunggu';
                $metode = !empty($p->pembayaran->metode_pembayaran) ? $p->pembayaran->metode_pembayaran : 'COD';
                $preview = $p->detail->first();
                $preview_text = $preview && $preview->produk
                    ? $preview->produk->nama_produk . ' (' . $preview->jumlah . 'x) · ' . $metode
                    : $metode;
                $status_class = $status === 'Selesai' ? 'done' : ($status === 'Ditolak' ? 'reject' : 'wait');
                $created_fmt = !empty($p->created_at) ? $p->created_at->format('d M Y, H:i') : '-';
            @endphp
        <div class="nota-item" onclick="showDetail({{ $idx }})">
            <div class="avatar av-{{ $status_class }}">
                @if ($status === 'Selesai') ✓
                @elseif ($status === 'Ditolak') ✕
                @else ⏳ @endif
            </div>
            <div class="item-body">
                <div class="item-name">{{ $p->nama }}</div>
                <div class="item-preview">{{ $preview_text }}</div>
            </div>
            <div class="item-meta">
                <div class="item-date">{{ $created_fmt }}</div>
                <div class="badge badge-{{ $status_class }}">{{ $status }}</div>
            </div>
        </div>
        @endforeach

    </div>
</div>

<!-- TAMPILAN DETAIL -->
<div class="detail-view" id="detailView">
    <div class="detail-header">
        <button class="back-btn" onclick="showList()">&#8592;</button>
        <div>
            <div class="detail-title" id="dTitle"></div>
            <div class="detail-sub" id="dSub"></div>
        </div>
    </div>
    <div class="detail-body" id="dBody"></div>
</div>

<script>
const pesananData = @json($pesananData);
const assetBase = "{{ asset('assets') }}";

function statusClass(s) {
    return s === 'Selesai' ? 'done' : s === 'Ditolak' ? 'reject' : 'wait';
}

function formatRp(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID');
}

function formatDate(dt) {
    if (!dt) return '-';
    const d = new Date(dt);
    return d.toLocaleDateString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric'
    }) + ', ' + d.toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit'
    });
}

function showDetail(i) {
    const o = pesananData[i];
    document.getElementById('dTitle').textContent = o.nama;
    document.getElementById('dSub').textContent = 'Dipesan: ' + formatDate(o.created_at);

    const sc = statusClass(o.status);
    const metodeHtml = o.metode === 'COD'
        ? `<span class="cod-badge">COD</span>`
        : `<strong>${o.metode}</strong>`;

    let produkHtml = '';
    if (o.produk.length > 0) {
        o.produk.forEach(p => {
            produkHtml += `
                <div class="produk-item">
                    <span class="produk-name">${p.nama} (${p.qty}x)</span>
                    <span class="produk-harga">${formatRp(p.harga)}</span>
                </div>`;
        });
    } else {
        produkHtml = `<div class="produk-item error-item">Produk tidak ditemukan</div>`;
    }

    let buktiHtml = '';
    if (o.bukti) {
        buktiHtml = `
            <div class="detail-section">
                <div class="section-label">Bukti Transfer</div>
                <img src="${assetBase}/${o.bukti}" style="max-width:100%;border-radius:8px;">
            </div>`;
    }

    document.getElementById('dBody').innerHTML = `
        <div class="detail-section">
            <div class="section-label">Info Pesanan</div>
            <div class="info-row">
                <span class="info-key">Nama</span>
                <span class="info-val">${o.nama}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Alamat</span>
                <span class="info-val">${o.alamat}</span>
            </div>
            ${o.lat && o.lng ? `
            <div class="info-row">
                <span class="info-key">Lokasi GPS</span>
                <span class="info-val">
                    <a href="https://www.google.com/maps?q=${o.lat},${o.lng}" target="_blank">📍 Buka di Maps</a>
                </span>
            </div>` : ''}
            <div class="info-row">
                <span class="info-key">Tanggal Jadi</span>
                <span class="info-val">${o.tanggal}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Jam Pengambilan</span>
                <span class="info-val">${o.jam || '-'}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Dibuat Pada</span>
                <span class="info-val">${formatDate(o.created_at)}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Metode Bayar</span>
                <span class="info-val">${metodeHtml}</span>
            </div>
        </div>
        <div class="detail-section">
            <div class="section-label">Daftar Pesanan</div>
            ${produkHtml}
            <div class="total-row">
                <span class="total-label">Total Bayar</span>
                <span class="total-val">${formatRp(o.total)}</span>
            </div>
        </div>
        ${buktiHtml}
        <div class="detail-section">
            <div class="section-label">Status Pesanan</div>
            <div class="status-pill status-${sc}">
                ${o.status === 'Selesai' ? '✔ Pesanan Selesai' : o.status === 'Ditolak' ? '❌ Pesanan Ditolak' : '⏳ Pesanan Menunggu'}
            </div>
        </div>`;

    document.getElementById('listView').style.display = 'none';
    document.getElementById('detailView').style.display = 'flex';
}

function showList() {
    document.getElementById('detailView').style.display = 'none';
    document.getElementById('listView').style.display = 'flex';
}
</script>

@include('partials.footer')
</body>
</html>
