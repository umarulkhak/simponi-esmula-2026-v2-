<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <!-- ✅ Tambahkan mb-4 di sini untuk jarak ke menu -->
    <div class="app-brand demo mb-4">
        <a href="{{ route('wali.beranda') }}" class="app-brand-link d-flex align-items-center">
            <!-- Logo -->
            <span class="app-brand-logo demo">
                <img src="{{ asset('sneat/assets/img/logo-esmula.png') }}"
                     alt="Logo Esmula"
                     style="width: 40px; height: 40px; object-fit: contain; border-radius: 50%; vertical-align: middle;">
            </span>

            <!-- Container untuk Judul + Subjudul -->
            <div class="ms-3">
                <div class="app-brand-text demo menu-text fw-bolder">Simponi</div>
                <div class="text-muted small mt-1" style="font-size: 0.7rem; font-weight: 300;">
                    SMP Muhammadiyah Larangan
                </div>
            </div>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ \Route::is('wali.beranda') ? 'active' : '' }}">
            <a href="{{ route('wali.beranda') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Beranda</div>
            </a>
        </li>

        <!-- Data Anak -->
        <li class="menu-item {{ \Route::is('wali.siswa.*') ? 'active' : '' }}">
            <a href="{{ route('wali.siswa.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div>Data Anak</div>
            </a>
        </li>

        <!-- Data Tagihan Anak-->
        <li class="menu-item {{ \Route::is('wali.tagihan.*') ? 'active' : '' }}">
            <a href="{{ route('wali.tagihan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-receipt"></i>
                <div>Data Tagihan</div>
            </a>
        </li>

        <!-- Logout -->
        <li class="menu-item">
            <a href="{{ route('logout') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-power-off me-2"></i>
                <div>Logout</div>
            </a>
        </li>
    </ul>
</aside>
