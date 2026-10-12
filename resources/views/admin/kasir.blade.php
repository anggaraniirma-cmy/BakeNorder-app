<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kasir | Admin BakeNOrder</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/kasir.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/purnama_circle.png') }}">
</head>
<body>

@include('partials.navbar')

<div class="kasir-wrap">

    <!-- ================= KIRI: PRODUK ================= -->
    <section class="kasir-produk">
        <div class="kasir-head">
            <div>
                <h2>🧾 Kasir</h2>
                <p class="sub">Pilih produk untuk ditambahkan ke keranjang</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="link-dash">← Dashboard</a>
        </div>

        <input type="text" id="cari" class="cari" placeholder="🔍 Cari produk...">

        @if ($produk->isEmpty())
            <div class="kosong">Belum ada produk di database.</div>
        @else
            <div class="grid-produk" id="gridProduk">
                @foreach ($produk as $p)
                    <button type="button" class="kartu-produk"
                            data-id="{{ $p->id }}"
                            data-nama="{{ $p->nama_produk }}"
                            data-harga="{{ (int) $p->harga }}"
                            data-key="{{ strtolower($p->nama_produk) }}">
                        <img src="{{ $p->foto_url }}" alt="{{ $p->nama_produk }}" loading="lazy">
                        <span class="nama">{{ $p->nama_produk }}</span>
                        <span class="harga">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    <!-- ================= KANAN: KERANJANG ================= -->
    <aside class="kasir-keranjang">
        <h3>🛒 Keranjang</h3>

        @if (session('kasir_error'))
            <div class="alert alert-error">{{ session('kasir_error') }}</div>
        @endif

        <div id="keranjangKosong" class="keranjang-kosong">
            Keranjang masih kosong.<br><span>Klik produk di sebelah kiri.</span>
        </div>

        <div id="daftarItem" class="daftar-item"></div>

        <form action="{{ route('admin.kasir.store') }}" method="POST" id="formKasir">
            @csrf
            <div id="hiddenItems"></div>

            <div class="total-bar">
                <span>Total</span>
                <strong id="totalTeks">Rp 0</strong>
            </div>

            <label>Nama Pelanggan <small>(opsional)</small></label>
            <input type="text" name="nama" placeholder="Contoh: Budi" maxlength="100">

            <label>Metode Pembayaran</label>
            <select name="metode_pembayaran" id="metode" required>
                <option value="Tunai">Tunai</option>
                <option value="Transfer">Transfer</option>
                <option value="COD">COD</option>
            </select>

            <div id="blokTunai">
                <label>Uang Diterima</label>
                <input type="number" name="bayar" id="bayar" min="0" step="500" placeholder="0">

                <div class="quick-cash">
                    <button type="button" data-v="pas">Uang Pas</button>
                    <button type="button" data-v="20000">20.000</button>
                    <button type="button" data-v="50000">50.000</button>
                    <button type="button" data-v="100000">100.000</button>
                </div>

                <div class="kembalian">
                    <span>Kembalian</span>
                    <strong id="kembalianTeks">Rp 0</strong>
                </div>
            </div>

            <button type="submit" class="btn-bayar" id="btnBayar" disabled>
                Bayar &amp; Simpan
            </button>
        </form>
    </aside>

</div>

<!-- ================= MODAL STRUK ================= -->
@if (session('kasir_sukses'))
    @php $s = session('kasir_sukses'); @endphp
    <div class="modal-overlay" id="modalStruk">
        <div class="struk">
            <div class="struk-ikon">✓</div>
            <h3>Transaksi Berhasil</h3>
            <p class="struk-no">No. Pesanan #{{ $s['pesanan_id'] }}</p>

            <div class="struk-row"><span>Total</span><strong>Rp {{ number_format($s['total'], 0, ',', '.') }}</strong></div>
            <div class="struk-row"><span>Metode</span><strong>{{ $s['metode'] }}</strong></div>
            @if ($s['metode'] === 'Tunai' && $s['bayar'] > 0)
                <div class="struk-row"><span>Diterima</span><strong>Rp {{ number_format($s['bayar'], 0, ',', '.') }}</strong></div>
                <div class="struk-row kembali"><span>Kembalian</span><strong>Rp {{ number_format($s['kembali'], 0, ',', '.') }}</strong></div>
            @endif

            <div class="struk-aksi">
                <a href="{{ route('admin.kasir.struk', $s['pesanan_id']) }}" target="_blank" class="btn-cetak-struk">🖨 Cetak Struk</a>
            </div>
            <div class="struk-aksi sekunder">
                <a href="{{ route('admin.kasir') }}" class="btn-ghost">Transaksi Baru</a>
                <a href="{{ route('admin.dashboard') }}" class="btn-ghost">Dashboard</a>
            </div>
        </div>
    </div>
@endif

<script>
(function () {
    const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const daftarItem   = document.getElementById('daftarItem');
    const keranjangKosong = document.getElementById('keranjangKosong');
    const totalTeks    = document.getElementById('totalTeks');
    const hiddenItems  = document.getElementById('hiddenItems');
    const btnBayar     = document.getElementById('btnBayar');
    const bayarInput   = document.getElementById('bayar');
    const kembalianTeks = document.getElementById('kembalianTeks');
    const metodeSelect = document.getElementById('metode');
    const blokTunai    = document.getElementById('blokTunai');

    /** state keranjang: { id: {id, nama, harga, jumlah} } */
    const cart = {};

    function total() {
        return Object.values(cart).reduce((s, it) => s + it.harga * it.jumlah, 0);
    }

    function render() {
        const items = Object.values(cart);
        daftarItem.innerHTML = '';
        keranjangKosong.style.display = items.length ? 'none' : 'block';

        items.forEach(it => {
            const row = document.createElement('div');
            row.className = 'item-row';
            row.innerHTML = `
                <div class="item-info">
                    <span class="item-nama">${it.nama}</span>
                    <span class="item-harga">${rupiah(it.harga)} × ${it.jumlah}</span>
                </div>
                <div class="item-qty">
                    <button type="button" data-act="min" data-id="${it.id}">−</button>
                    <span>${it.jumlah}</span>
                    <button type="button" data-act="plus" data-id="${it.id}">+</button>
                </div>
                <div class="item-sub">${rupiah(it.harga * it.jumlah)}</div>
                <button type="button" class="item-hapus" data-act="del" data-id="${it.id}">🗑</button>
            `;
            daftarItem.appendChild(row);
        });

        totalTeks.textContent = rupiah(total());
        btnBayar.disabled = items.length === 0;
        hitungKembalian();
    }

    function hitungKembalian() {
        const bayar = Number(bayarInput.value || 0);
        const kembali = Math.max(0, bayar - total());
        kembalianTeks.textContent = rupiah(kembali);
    }

    // tambah produk
    document.querySelectorAll('.kartu-produk').forEach(card => {
        card.addEventListener('click', () => {
            const id = card.dataset.id;
            if (cart[id]) {
                cart[id].jumlah += 1;
            } else {
                cart[id] = {
                    id: id,
                    nama: card.dataset.nama,
                    harga: Number(card.dataset.harga),
                    jumlah: 1,
                };
            }
            render();
        });
    });

    // kontrol qty / hapus
    daftarItem.addEventListener('click', e => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const id = btn.dataset.id, act = btn.dataset.act;
        if (!cart[id]) return;
        if (act === 'plus') cart[id].jumlah += 1;
        if (act === 'min') { cart[id].jumlah -= 1; if (cart[id].jumlah <= 0) delete cart[id]; }
        if (act === 'del') delete cart[id];
        render();
    });

    // pencarian produk
    const cari = document.getElementById('cari');
    if (cari) {
        cari.addEventListener('input', () => {
            const q = cari.value.trim().toLowerCase();
            document.querySelectorAll('.kartu-produk').forEach(card => {
                card.style.display = card.dataset.key.includes(q) ? '' : 'none';
            });
        });
    }

    // metode pembayaran -> tampil/sembunyikan blok tunai
    metodeSelect.addEventListener('change', () => {
        blokTunai.style.display = metodeSelect.value === 'Tunai' ? 'block' : 'none';
    });
    blokTunai.style.display = metodeSelect.value === 'Tunai' ? 'block' : 'none';

    bayarInput.addEventListener('input', hitungKembalian);

    // tombol uang cepat
    document.querySelectorAll('.quick-cash button').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.v === 'pas') {
                bayarInput.value = total();
            } else {
                bayarInput.value = btn.dataset.v;
            }
            hitungKembalian();
        });
    });

    // kirim: siapkan hidden inputs items[]
    document.getElementById('formKasir').addEventListener('submit', e => {
        const items = Object.values(cart);
        if (!items.length) { e.preventDefault(); return; }
        hiddenItems.innerHTML = '';
        items.forEach((it, i) => {
            hiddenItems.insertAdjacentHTML('beforeend',
                `<input type="hidden" name="items[${i}][id]" value="${it.id}">
                 <input type="hidden" name="items[${i}][jumlah]" value="${it.jumlah}">`);
        });
    });

    render();

    // tutup modal struk saat klik overlay
    const modal = document.getElementById('modalStruk');
    if (modal) {
        modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });
    }
})();
</script>

@include('partials.footer')

@include('partials.transisi')

</body>
</html>
