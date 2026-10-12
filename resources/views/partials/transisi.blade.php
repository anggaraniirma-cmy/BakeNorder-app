{{--
    Partial transisi global:
    - smooth scroll
    - fade-in halus saat halaman dimuat
    - fade-out halus saat berpindah halaman (link internal)
    Dipakai di semua halaman (kecuali halaman cetak struk).
--}}
<style>
    /* ===== SMOOTH SCROLL ===== */
    html { scroll-behavior: smooth; }

    /* ===== FADE-IN SAAT HALAMAN DIMUAT ===== */
    body { animation: bno-masuk .4s ease-out both; }
    @keyframes bno-masuk {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    /* ===== OVERLAY FADE-OUT SAAT PINDAH HALAMAN ===== */
    #bnoTransisi {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: #F6EFE8;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity .32s ease, visibility 0s linear .32s;
        pointer-events: none;
    }
    #bnoTransisi.aktif {
        opacity: 1;
        visibility: visible;
        pointer-events: all;
        transition: opacity .32s ease, visibility 0s;
    }
    #bnoTransisi .bno-loader {
        width: 34px;
        height: 34px;
        border: 3px solid #ead9c4;
        border-top-color: #8B5A2B;
        border-radius: 50%;
        /* animasi hanya jalan saat overlay aktif -> tidak buang render saat diam */
    }
    #bnoTransisi.aktif .bno-loader {
        animation: bno-putar .7s linear infinite;
    }
    @keyframes bno-putar { to { transform: rotate(360deg); } }

    /* ===== HORMATI PREFERENSI PENGGUNA ===== */
    @media (prefers-reduced-motion: reduce) {
        html { scroll-behavior: auto; }
        body { animation: none; }
        #bnoTransisi { transition: none; }
        #bnoTransisi .bno-loader { animation: none; }
    }
</style>

<div id="bnoTransisi" aria-hidden="true"><div class="bno-loader"></div></div>

<script>
(function () {
    var overlay = document.getElementById('bnoTransisi');
    if (!overlay) return;

    var kurangiGerak = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Reset overlay bila halaman dipulihkan dari cache riwayat (tombol back/forward)
    window.addEventListener('pageshow', function (e) {
        if (e.persisted || overlay.classList.contains('aktif')) {
            overlay.classList.remove('aktif');
        }
    });

    function bolehIntercept(a) {
        if (!a || !a.href) return false;
        if (a.target && a.target !== '' && a.target !== '_self') return false;
        if (a.hasAttribute('download') || a.hasAttribute('data-no-transisi')) return false;
        // hanya tautan internal (origin sama)
        if (a.origin !== window.location.origin) return false;
        // jangan untuk anchor dalam halaman yang sama
        var url = new URL(a.href);
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return false;
        return true;
    }

    document.addEventListener('click', function (e) {
        // hanya klik kiri tanpa tombol pengubah
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var a = e.target.closest && e.target.closest('a');
        if (!bolehIntercept(a)) return;
        if (kurangiGerak) return; // biarkan langsung pindah

        e.preventDefault();
        overlay.classList.add('aktif');
        var tujuan = a.href;
        window.setTimeout(function () { window.location.href = tujuan; }, 300);
    });
})();
</script>
