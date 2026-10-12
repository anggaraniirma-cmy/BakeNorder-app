<div class="tabel-scroll">
    <table class="tabel-pesanan">
        <thead>
            <tr>
                <th>Pelanggan</th>
                <th>Produk</th>
                <th class="ta-r">Total</th>
                <th>Dibuat</th>
                <th>Tanggal Jadi</th>
                <th>Jam</th>
                <th>Bayar</th>
                <th>Status</th>
                <th class="ta-c">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pesanan as $ps)
                @php
                    $status = $ps->status ?: 'Menunggu';
                    $badge = $status === 'Selesai' ? 'done' : ($status === 'Ditolak' ? 'reject' : 'wait');
                    $produkTeks = $ps->detail->map(fn ($d) => ($d->produk->nama_produk ?? 'Produk') . ' (' . $d->jumlah . '×)')->implode(', ');
                    $metode = $ps->pembayaran->metode_pembayaran ?? 'COD';
                @endphp
                <tr>
                    <td data-label="Pelanggan">
                        <span class="pelanggan">{{ $ps->nama ?: 'Pelanggan' }}</span>
                        <span class="sub-alamat" title="{{ $ps->alamat }}">{{ \Illuminate\Support\Str::limit($ps->alamat, 40) }}</span>
                    </td>
                    <td data-label="Produk">
                        <span class="produk-teks">{{ $produkTeks ?: '-' }}</span>
                    </td>
                    <td data-label="Total" class="ta-r">
                        <span class="total-teks">Rp {{ number_format($ps->total, 0, ',', '.') }}</span>
                    </td>
                    <td data-label="Dibuat">
                        <span class="tgl-teks">{{ optional($ps->created_at)->locale('id')->translatedFormat('l, d M Y') }}</span>
                        <span class="sub-jam">{{ optional($ps->created_at)->format('H:i') }}</span>
                    </td>
                    <td data-label="Tanggal Jadi">
                        {{ optional($ps->tanggal_jadi)->locale('id')->translatedFormat('d M Y') ?: '-' }}
                    </td>
                    <td data-label="Jam">
                        {{ $ps->jam_pengambilan ? substr($ps->jam_pengambilan, 0, 5) : '-' }}
                    </td>
                    <td data-label="Bayar">
                        <span class="metode">{{ $metode }}</span>
                    </td>
                    <td data-label="Status">
                        <span class="badge badge-{{ $badge }}">{{ $status }}</span>
                    </td>
                    <td data-label="Aksi" class="ta-c">
                        <form action="{{ route('admin.status.update') }}" method="POST" class="form-status">
                            @csrf
                            <input type="hidden" name="id" value="{{ $ps->id }}">
                            <select name="status" aria-label="Ubah status pesanan {{ $ps->nama }}">
                                <option value="Menunggu" @selected($status === 'Menunggu')>Menunggu</option>
                                <option value="Selesai"  @selected($status === 'Selesai')>Selesai</option>
                                <option value="Ditolak"  @selected($status === 'Ditolak')>Ditolak</option>
                            </select>
                            <button type="submit" class="btn-mini">Update</button>
                        </form>
                        <div class="aksi-baris">
                            <a href="{{ route('admin.kasir.struk', $ps->id) }}" target="_blank" class="btn-mini ghost">🧾 Nota</a>
                            @if ($ps->latitude && $ps->longitude)
                                <a href="https://www.google.com/maps?q={{ $ps->latitude }},{{ $ps->longitude }}" target="_blank" class="btn-mini ghost">📍 Maps</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-ikon">📭</div>
                            <p>Tidak ada pesanan yang cocok dengan filter.</p>
                            <span>Coba ubah hari, rentang tanggal, atau kata kunci pencarian.</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($pesanan->hasPages())
    <div class="paginasi">
        {{ $pesanan->links() }}
    </div>
@elseif ($pesanan->total() > 0)
    <div class="paginasi-info">Menampilkan {{ $pesanan->count() }} dari {{ $pesanan->total() }} pesanan</div>
@endif
