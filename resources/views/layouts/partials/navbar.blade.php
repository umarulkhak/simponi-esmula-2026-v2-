<style>
  .layout-navbar .navbar-dropdown .dropdown-menu {
    min-width: 22rem;
  }
</style>

<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
  <!-- Toggle Sidebar (Mobile) -->
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="bx bx-menu bx-sm"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    {{--
      <!-- Search (currently commented out) -->
      <div class="navbar-nav align-items-center" role="search">
        <div class="nav-item d-flex align-items-center">
          <i class="bx bx-search fs-4 lh-0 me-2"></i>
          <input type="text" class="form-control border-0 shadow-none" placeholder="Cari..." aria-label="Search..." />
        </div>
      </div>
    --}}

    <!-- Right-side Navbar Items -->
    <ul class="navbar-nav flex-row align-items-center ms-auto">

      <!-- Notifications Dropdown -->
        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
            <span class="position-relative">
            <i class="icon-base bx bx-bell icon-md"></i>
            @php
                $unreadCount = auth()->user()->unreadNotifications->count();
            @endphp
            @if ($unreadCount > 0)
                <span class="badge rounded-pill bg-danger badge-notifications border">{{ $unreadCount }}</span>
            @endif
            </span>
        </a>

        <ul class="dropdown-menu dropdown-menu-end p-0" data-bs-popper="static">
            <!-- Header -->
            <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
                <h6 class="mb-0 me-auto">Notification</h6>
                <div class="d-flex align-items-center h6 mb-0">
                <span class="badge bg-label-primary me-2">{{ $unreadCount }} New</span>
                <a href="javascript:void(0)" class="dropdown-notifications-all p-2" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Mark all as read" data-bs-original-title="Mark all as read">
                    <i class="icon-base bx bx-envelope-open text-heading"></i>
                </a>
                </div>
            </div>
            </li>

            <!-- Notifications List -->
            @if ($unreadCount > 0)
            <li class="dropdown-notifications-list scrollable-container ps">
                <ul class="list-group list-group-flush">
                @foreach (auth()->user()->unreadNotifications as $notification)
                    <li class="list-group-item list-group-item-action dropdown-notifications-item">
                        <a href="{{ $notification->data['url'] . '?id=' . $notification->id }}">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                        <p class="mb-0">{{ ucwords($notification->data['messages'] ?? '') }}</p>
                        <small class="text-muted">
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                        <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                        <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                        </div>
                    </div>
                        </a>
                    </li>
                @endforeach
                </ul>
                <div class="ps__rail-x"><div class="ps__thumb-x"></div></div>
                <div class="ps__rail-y"><div class="ps__thumb-y"></div></div>
            </li>
            @else
            <li class="text-center py-4 text-muted">
                <i class="bx bx-check-circle fs-4 mb-2"></i>
                <p class="mb-0">Tidak ada notifikasi baru</p>
            </li>
            @endif

            <!-- View All Button -->
            <li class="border-top">
            <div class="d-grid p-4">
                <a class="btn btn-primary btn-sm d-flex justify-content-center" href="javascript:void(0);">
                <small class="align-middle">View all notifications</small>
                </a>
            </div>
            </li>
        </ul>
        </li>

      <!-- User Display -->
      <li class="nav-item lh-1 me-3">
        <span class="fw-semibold">{{ Auth::check() ? Auth::user()->name : 'Operator' }}</span>
      </li>

      <!-- User Dropdown -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="fa fa-user text-white"></i>
          </div>
        </a>

        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="#">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fa fa-user text-white"></i>
                  </div>
                </div>
                <div class="flex-grow-1">
                  <span class="fw-semibold d-block">{{ Auth::check() ? Auth::user()->name : 'Operator' }}</span>
                  <small class="text-muted">Operator</small>
                </div>
              </div>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#"><i class="fa fa-user me-2"></i> My Profile</a></li>
          <li><a class="dropdown-item" href="#"><i class="fa fa-cog me-2"></i> Settings</a></li>
          {{-- <li>
            <a class="dropdown-item" href="#">
              <span class="d-flex align-items-center">
                <i class="fa fa-credit-card me-2"></i>
                <span>Billing</span>
                <span class="badge bg-danger ms-2">4</span>
              </span>
            </a>
          </li> --}}
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item" href="{{ route('logout') }}">
              <i class="fa fa-power-off me-2"></i> Log Out
            </a>
          </li>
        </ul>
      </li>
    </ul>
  </div>
</nav>
