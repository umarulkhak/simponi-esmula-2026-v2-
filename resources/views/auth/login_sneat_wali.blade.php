<!DOCTYPE html>
<!--
|--------------------------------------------------------------------------
| VIEW: Login Page
|--------------------------------------------------------------------------
| Penulis     : SIMPONI Team
| Tujuan      : Halaman login untuk sistem Simponi SMP Muhammadiyah Larangan
| Fitur       :
|   - Logo gambar (PNG/JPG) di tengah
|   - Teks branding di bawahnya
|   - Toggle password berfungsi
|   - Clean code, responsif, dokumentasi jelas
-->
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('sneat/assets/') }}"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Login | SIMPONI - SMP Muhammadiyah Larangan</title>
    <meta name="description" content="Sistem Informasi Pembayaran Online SMP Muhammadiyah Larangan" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('sneat/assets/img/logo-fav.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com  " />
    <link rel="preconnect" href="https://fonts.gstatic.com  " crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('sneat/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/pages/page-auth.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('sneat/assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('sneat/assets/js/config.js') }}"></script>
  </head>

  <body>
    <!-- Content -->
    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Login Card -->
          <div class="card">
            <div class="card-body">
              <!-- Logo — CENTERED IMAGE + TEXT BELOW -->
              <div class="text-center mb-4">
                <!-- Gambar Logo Sekolah -->
                <img
                  src="{{ asset('sneat/assets/img/logo-esmula.png') }}"
                  alt="Logo SMP Muhammadiyah Larangan"
                  style="width: 72px; height: auto; border-radius: 10px;"
                />

                <!-- Teks Branding -->
                <h5 class="fw-bold mt-3 mb-1">Sistem Pembayaran Online</h5>
                <h5 class="fw-bold">SMP Muhammadiyah Larangan</h5>
              </div>
              <!-- /Logo -->
              <p class="text-center mb-3">Akses seluruh layanan pembayaran sekolah dengan mudah dan cepat</p>

              <!-- Form Login -->
              <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input
                    type="text"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Masukkan email"
                    required
                    autofocus
                  />
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3 form-password-toggle">
                  <label class="form-label" for="password">Password</label>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="password"
                      class="form-control @error('password') is-invalid @enderror"
                      name="password"
                      placeholder="••••••••••••"
                      required
                    />
                    <span class="input-group-text cursor-pointer" id="togglePassword">
                      <i class="bx bx-hide"></i>
                    </span>
                  </div>
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }} />
                    <label class="form-check-label" for="remember"> Ingat Saya </label>
                  </div>
                </div>

                <div class="mb-3">
                  <button class="btn btn-dark d-grid w-100" type="submit">Login</button>
                </div>
              </form>


              @if (Route::has('password.request'))
                <div class="text-center">
                  <small class="text-muted">
                    Lupa password?
                    <a href="#" class="fw-bold text-decoration-none text-dark">
                      Reset sandi
                    </a>
                  </small>
                </div>
              @endif

              <div class="text-center mt-1">
                <small class="text-muted">
                  <a href="https://umarulkhak.github.io/gitprofile/" class="fw-bold text-dark">© {{ date('Y') }} SMP Muhammadiyah Larangan • Simponi</a>
                </small>
              </div>
            </div>
          </div>
          <!-- /Login Card -->
        </div>
      </div>
    </div>
    <!-- / Content -->

    <!-- Core JS -->
    <script src="{{ asset('sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('sneat/assets/js/main.js') }}"></script>
  </body>
</html>
