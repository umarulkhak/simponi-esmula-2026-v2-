<!DOCTYPE html>
<!--
==========================================================
 SNEAT - Bootstrap 5 HTML Admin Template
==========================================================
 Penulis     : SIMPONI Team
 Tujuan      : Layout utama aplikasi Simponi (Wali Dashboard)
 Fitur       :
   - Responsive layout (mobile/desktop)
   - Sidebar menu dinamis (highlight active route)
   - Navbar dengan search & user dropdown
   - Flash message & error handling
   - Integrasi Select2, Masking Rupiah, Font Awesome
   - Footer & copyright otomatis
   - ✅ WARNA PRIMARY DIUBAH KE DARK (#1A1A1A)
==========================================================
-->
<html
  lang="id"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('sneat/assets/') }}"
  data-template="vertical-menu-template-free"
>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="description" content="Sistem Informasi Pembayaran Online (Simponi) - Dashboard Wali" />
    <meta name="theme-color" content="#1A1A1A">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('sneat/assets/img/logo-fav.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/animate.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('sneat/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('font/css/all.min.css') }}">

    <!-- Helpers -->
    <script src="{{ asset('sneat/assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('sneat/assets/js/config.js') }}"></script>

<!-- Custom CSS -->
<style>
    /* === OVERRIDE WARNA PRIMARY UNTUK USER WALI === */
    :root {
        --bs-primary: #2A363B;
        --bs-primary-rgb: 42, 54, 59;
        --bs-primary-text: #ffffff;
        --bs-primary-border: #444444;
        --bs-primary-hover: #334045;
        --bs-primary-active: #1E2C2F;
        --bs-primary-focus: #3A4D52;

        /* Override Sidebar */
        --bs-sidebar-bg: #f8f9fa;
        --bs-sidebar-text: #000000;
        --bs-sidebar-active: #2A363B;
        --bs-sidebar-hover: #e9ecef;
        --bs-sidebar-border: #ddd;

        /* Override ikon sidebar di mobile */
        --bs-icon-color: #000000;
        --bs-icon-hover-color: #2A363B;
    }

    /* Override elemen yang pakai warna primary */
    .btn-primary {
        background-color: var(--bs-primary) !important;
        border-color: var(--bs-primary-border) !important;
        color: var(--bs-primary-text) !important;
    }
    .btn-primary:hover {
        background-color: var(--bs-primary-hover) !important;
        border-color: var(--bs-primary-border) !important;
    }
    .btn-primary:focus {
        box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.5) !important;
    }
    .btn-primary:active,
    .btn-primary.active {
        background-color: var(--bs-primary-active) !important;
        border-color: var(--bs-primary-border) !important;
    }

    /* Badge */
    .badge.bg-primary {
        background-color: var(--bs-primary) !important;
        color: var(--bs-primary-text) !important;
    }

    /* Alert */
    .alert.alert-primary {
        background-color: var(--bs-primary) !important;
        color: var(--bs-primary-text) !important;
        border-color: var(--bs-primary-border) !important;
    }

    /* Navbar & Sidebar */
    .navbar-brand,
    .navbar-nav .nav-link,
    .menu-inner .menu-item .menu-link {
        color: var(--bs-primary-text) !important;
    }
    .navbar-nav .nav-link:hover,
    .menu-inner .menu-item .menu-link:hover {
        color: var(--bs-primary-text) !important;
    }

    /* Avatar */
    .avatar.bg-primary {
        background-color: var(--bs-primary) !important;
    }

    /* Progress Bar */
    .progress-bar.bg-primary {
        background-color: var(--bs-primary) !important;
    }

    /* Form Control Focus */
    .form-control:focus {
        border-color: var(--bs-primary-border) !important;
        box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25) !important;
    }

    /* === OVERRIDE SIDEBAR === */
    .layout-menu {
        background-color: var(--bs-sidebar-bg) !important;
        color: var(--bs-sidebar-text) !important;
        border-right: 1px solid var(--bs-sidebar-border) !important;
    }
    .layout-menu .menu-inner {
        padding: 1rem 0 !important;
    }
    .layout-menu .menu-inner .menu-item {
        margin-bottom: 0.5rem !important;
    }
    .layout-menu .menu-inner .menu-item .menu-link {
        color: var(--bs-sidebar-text) !important;
        padding: 0.75rem 1rem !important;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }
    .layout-menu .menu-inner .menu-item .menu-link:hover {
        background-color: var(--bs-sidebar-hover) !important;
        color: var(--bs-primary-hover) !important;
    }
    .layout-menu .menu-inner .menu-item.active .menu-link {
        background-color: var(--bs-primary) !important;
        color: var(--bs-primary-text) !important;
        border-left: 4px solid var(--bs-primary) !important;
    }
    .layout-menu .menu-inner .menu-item.active .menu-link i {
        color: var(--bs-primary-text) !important;
    }

    /* === OVERRIDE IKON DI MOBILE === */
    .menu-inner .menu-item .menu-link i {
        color: var(--bs-icon-color) !important;
        font-size: 1.2rem;
    }
    .menu-inner .menu-item .menu-link:hover i {
        color: var(--bs-icon-hover-color) !important;
    }

    /* === OVERRIDE ICON MENU DI NAVBAR === */
    .layout-navbar .bx-menu {
        color: #2A363B !important;
        font-size: 1.5rem;
    }

    /* === OVERRIDE ICON USER DI AVATAR === */
    .layout-navbar .dropdown-user .avatar i {
        color: #ffffff !important;
    }

    /* === OVERRIDE ACTIVE MENU ITEM (HAPUS WARNA UNGU) === */
    .bg-menu-theme .menu-inner > .menu-item.active:before {
        background: var(--bs-primary) !important; /* Ganti dari #696cff */
    }

    /* === OVERRIDE TEXT COLOR DI ACTIVE MENU === */
    .menu-inner .menu-item.active .menu-link {
        color: var(--bs-primary-text) !important;
    }

</style>
</head>

<body>
    <!-- Layout Wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar Menu -->
            @include('layouts.partials.sidebar_wali')

            <!-- Main Content -->
            <div class="layout-page">

                <!-- Navbar -->
                @include('layouts.partials.navbar_wali')

                <!-- Content Wrapper -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Flash Messages -->
                        @include('flash::message')

                        <!-- Error Messages -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading fw-bold mb-1">Terjadi Kesalahan!</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Main Content -->
                        @yield('content')

                    </div>

                    <!-- Footer -->
                    @include('layouts.partials.footer_wali')

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- / Content Wrapper -->

            </div>
            <!-- / Layout Page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout Wrapper -->

    <!-- Core JS -->
    <script src="{{ asset('sneat/assets/vendor/libs/jquery/jquery.js') }}" defer></script>
    <script>
        if (typeof window.jQuery === 'undefined') {
            document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer><\/script>');
        }
    </script>
    <script src="{{ asset('sneat/assets/vendor/libs/popper/popper.js') }}" defer></script>
    <script src="{{ asset('sneat/assets/vendor/js/bootstrap.bundle.min.js') }}" defer></script>
    <script src="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}" defer></script>
    <script src="{{ asset('sneat/assets/vendor/js/menu.js') }}" defer></script>

    <!-- Vendors JS -->
    <script src="{{ asset('sneat/assets/vendor/libs/apex-charts/apexcharts.js') }}" defer></script>

    <!-- Main JS -->
    <script src="{{ asset('sneat/assets/js/main.js') }}" defer></script>
    <script src="{{ asset('sneat/assets/js/dashboards-analytics.js') }}" defer></script>

    <!-- Third Party JS -->
    <script src="{{ asset('sneat/js/select2.min.js') }}" defer></script>
    <script src="{{ asset('sneat/js/jquery.mask.min.js') }}" defer></script>

    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2
            $('.select2').select2({
                width: '100%',
                placeholder: "Pilih..."
            });

            // Initialize Rupiah Masking
            $('.rupiah').mask("#.##0", {
                reverse: true
            });

            // Auto dismiss flash messages
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>

    <!-- Stack Scripts -->
    @stack('scripts')
</body>
</html>
