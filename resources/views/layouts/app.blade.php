{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Warehouse Management') }} | @yield('title')</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }

        .content {
            padding: 1.5rem;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
            margin-bottom: 1.5rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Warehouse Management') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('profile') }}">
                                        <i class="bi bi-person"></i> {{ __('Profile') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('change-password') }}">
                                        <i class="bi bi-key"></i> {{ __('Change Password') }}
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right"></i> {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        @auth
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                        <div class="position-sticky pt-3">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('home') || request()->routeIs('dashboard') ? 'active' : '' }}"
                                        href="{{ route('dashboard') }}">
                                        <i class="bi bi-speedometer"></i> Dashboard
                                    </a>
                                </li>

                                @canany(['view branches', 'view warehouses'])
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="collapse" href="#locationMenu" role="button"
                                            aria-expanded="{{ request()->routeIs('branches.*') || request()->routeIs('warehouses.*') ? 'true' : 'false' }}"
                                            aria-controls="locationMenu">
                                            <i class="bi bi-geo-alt"></i> Lokasi <i class="bi bi-chevron-down float-end"></i>
                                        </a>
                                        <div class="collapse {{ request()->routeIs('branches.*') || request()->routeIs('warehouses.*') ? 'show' : '' }}"
                                            id="locationMenu">
                                            <ul class="nav flex-column ms-3">
                                                @can('view branches')
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}"
                                                            href="{{ route('branches.index') }}">
                                                            <i class="bi bi-diagram-3"></i> Cabang
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('view warehouses')
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"
                                                            href="{{ route('warehouses.index') }}">
                                                            <i class="bi bi-building"></i> Gudang
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </li>
                                @endcanany

                                @canany(['view products', 'view categories', 'view suppliers'])
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="collapse" href="#productMenu" role="button"
                                            aria-expanded="{{ request()->routeIs('products.*') || request()->routeIs('categories.*') || request()->routeIs('suppliers.*') ? 'true' : 'false' }}"
                                            aria-controls="productMenu">
                                            <i class="bi bi-box"></i> Produk <i class="bi bi-chevron-down float-end"></i>
                                        </a>
                                        <div class="collapse {{ request()->routeIs('products.*') || request()->routeIs('categories.*') || request()->routeIs('suppliers.*') ? 'show' : '' }}"
                                            id="productMenu">
                                            <ul class="nav flex-column ms-3">
                                                @can('view products')
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                                                            href="{{ route('products.index') }}">
                                                            <i class="bi bi-box-seam"></i> Produk
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('view categories')
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                                                            href="{{ route('categories.index') }}">
                                                            <i class="bi bi-tags"></i> Kategori
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('view suppliers')
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
                                                            href="{{ route('suppliers.index') }}">
                                                            <i class="bi bi-truck"></i> Supplier
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </li>
                                @endcanany

                                @canany(['view stocks', 'adjust stocks', 'transfer stocks'])
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="collapse" href="#stockMenu" role="button"
                                            aria-expanded="{{ request()->routeIs('stocks.*') || request()->routeIs('stock-adjustments.*') ? 'true' : 'false' }}"
                                            aria-controls="stockMenu">
                                            <i class="bi bi-clipboard-data"></i> Stok <i
                                                class="bi bi-chevron-down float-end"></i>
                                        </a>
                                        <div class="collapse {{ request()->routeIs('stocks.*') || request()->routeIs('stock-adjustments.*') ? 'show' : '' }}"
                                            id="stockMenu">
                                            <ul class="nav flex-column ms-3">
                                                @can('view stocks')
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ request()->routeIs('stocks.index') || request()->routeIs('stocks.show') ? 'active' : '' }}"
                                                            href="{{ route('stocks.index') }}">
                                                            <i class="bi bi-list-check"></i> Data Stok
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('adjust stocks')
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ request()->routeIs('stock-adjustments.*') ? 'active' : '' }}"
                                                            href="{{ route('stock-adjustments.index') }}">
                                                            <i class="bi bi-pencil-square"></i> Penyesuaian Stok
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </li>
                                @endcanany

                                @can('view reports')
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="collapse" href="#reportMenu" role="button"
                                            aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}"
                                            aria-controls="reportMenu">
                                            <i class="bi bi-file-earmark-text"></i> Laporan <i
                                                class="bi bi-chevron-down float-end"></i>
                                        </a>
                                        <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}"
                                            id="reportMenu">
                                            <ul class="nav flex-column ms-3">
                                                <li class="nav-item">
                                                    <a class="nav-link {{ request()->routeIs('reports.stock') ? 'active' : '' }}"
                                                        href="{{ route('reports.stock') }}">
                                                        <i class="bi bi-file-earmark-text"></i> Laporan Stok
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link {{ request()->routeIs('reports.stock-movement') ? 'active' : '' }}"
                                                        href="{{ route('reports.stock-movement') }}">
                                                        <i class="bi bi-file-earmark-text"></i> Laporan Pergerakan Stok
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link {{ request()->routeIs('reports.stock-valuation') ? 'active' : '' }}"
                                                        href="{{ route('reports.stock-valuation') }}">
                                                        <i class="bi bi-file-earmark-text"></i> Laporan Valuasi Stok
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                @endcan

                                @canany(['view users', 'view roles'])
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="collapse" href="#userMenu" role="button"
                                            aria-expanded="{{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'true' : 'false' }}"
                                            aria-controls="userMenu">
                                            <i class="bi bi-people"></i> Pengguna & Akses <i
                                                class="bi bi-chevron-down float-end"></i>
                                        </a>
                                        <div class="collapse {{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'show' : '' }}"
                                            id="userMenu">
                                            <ul class="nav flex-column ms-3">
                                                @can('view users')
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                                                            href="{{ route('users.index') }}">
                                                            <i class="bi bi-person"></i> Pengguna
                                                        </a>
                                                    </li>
                                                @endcan

                                                @can('view roles')
                                                    <li class="nav-item">
                                                        <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                                                            href="{{ route('roles.index') }}">
                                                            <i class="bi bi-shield"></i> Peran & Hak Akses
                                                        </a>
                                                    </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </li>
                                @endcanany
                            </ul>
                        </div>
                    </div>

                    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                        <div
                            class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1 class="h2">@yield('title')</h1>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                @yield('actions')
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('info'))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @yield('content')
                    </main>
                </div>
            </div>
        @else
            <main class="py-4">
                @yield('content')
            </main>
        @endauth
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Initialize Select2
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            // Confirm delete
            $('form.delete-form').on('submit', function() {
                return confirm('Apakah Anda yakin ingin menghapus data ini?');
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
