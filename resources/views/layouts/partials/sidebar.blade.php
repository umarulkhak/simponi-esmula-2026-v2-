<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <!-- ✅ Tambahkan mb-4 di sini untuk jarak ke menu -->
    <div class="app-brand demo mb-4">
        <a href="{{ route('operator.beranda') }}" class="app-brand-link d-flex align-items-center">
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
        <li class="menu-item {{ \Route::is('operator.beranda') ? 'active' : '' }}">
            <a href="{{ route('operator.beranda') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Beranda</div>
            </a>
        </li>

        <!-- Data User -->
        <li class="menu-item {{ \Route::is('user.*') ? 'active' : '' }}">
            <a href="{{ route('user.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div>Data User</div>
            </a>
        </li>

        <!-- Data Rekening -->
        <li class="menu-item {{ \Route::is('banksekolah.*') ? 'active' : '' }}">
            <a href="{{ route('banksekolah.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-building"></i>
                <div>Rekening Sekolah</div>
            </a>
        </li>

        <!-- Data Wali -->
        <li class="menu-item {{ \Route::is('wali.*') ? 'active' : '' }}">
            <a href="{{ route('wali.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div>Data Wali Murid</div>
            </a>
        </li>

        <!-- Data Siswa -->
        <li class="menu-item {{ \Route::is('siswa.*') ? 'active' : '' }}">
            <a href="{{ route('siswa.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                <div>Data Siswa</div>
            </a>
        </li>

        <!-- Data Biaya -->
        <li class="menu-item {{ \Route::is('biaya.*') ? 'active' : '' }}">
            <a href="{{ route('biaya.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-coin-stack"></i>
                <div>Data Biaya</div>
            </a>
        </li>

        <!-- Data Tagihan -->
        <li class="menu-item {{ \Route::is('tagihan.*') ? 'active' : '' }}">
            <a href="{{ route('tagihan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-receipt"></i>
                <div>Data Tagihan</div>
            </a>
        </li>

        <!-- Hapus Tagihan Massal -->
        <li class="menu-item {{ \Route::is('tagihan.hapus.massal') ? 'active' : '' }}">
            <a href="{{ route('tagihan.hapus.massal') }}" class="menu-link text-danger">
                <i class="menu-icon tf-icons bx bx-trash-alt text-danger"></i>
                <div>Hapus Tagihan Massal</div>
            </a>
        </li>

        <!-- Data Pembayaran -->
        <li class="menu-item {{ \Route::is('pembayaran.*') ? 'active' : '' }}">
            <a href="{{ route('pembayaran.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-credit-card"></i>
                <div>Data Pembayaran</div>
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
