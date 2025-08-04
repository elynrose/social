<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Simplified Custom Styles -->
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .navbar-brand { font-weight: 600; color: #007bff !important; }
        .navbar-nav .nav-link { font-weight: 500; padding: 0.5rem 1rem; }
        .dropdown-menu { border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .notification-badge { font-size: 0.7rem; padding: 0.25rem 0.5rem; }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="/dashboard">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            
                            <!-- Content Management -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-file-alt"></i> Content
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/posts"><i class="fas fa-edit"></i> Posts</a></li>
                                    <li><a class="dropdown-item" href="/campaigns"><i class="fas fa-bullhorn"></i> Campaigns</a></li>
                                    <li><a class="dropdown-item" href="/comments"><i class="fas fa-comments"></i> Comments</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/approval"><i class="fas fa-check-circle"></i> Approvals</a></li>
                                </ul>
                            </li>

                            <!-- Social Media -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-share-alt"></i> Social Media
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/social-accounts"><i class="fas fa-link"></i> Connected Accounts</a></li>
                                    <li><a class="dropdown-item" href="/mentions"><i class="fas fa-at"></i> Mentions</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/calendar"><i class="fas fa-calendar"></i> Calendar</a></li>
                                    <li><a class="dropdown-item" href="/scheduler"><i class="fas fa-clock"></i> Scheduler</a></li>
                                </ul>
                            </li>

                            <!-- Analytics -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-chart-line"></i> Analytics
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/analytics"><i class="fas fa-chart-bar"></i> Overview</a></li>
                                    <li><a class="dropdown-item" href="/analytics/overview"><i class="fas fa-chart-pie"></i> Detailed Analytics</a></li>
                                    <li><a class="dropdown-item" href="/analytics/posts"><i class="fas fa-file-alt"></i> Top Posts</a></li>
                                    <li><a class="dropdown-item" href="/analytics/engagement"><i class="fas fa-users"></i> Engagement</a></li>
                                </ul>
                            </li>

                            <!-- Business -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-building"></i> Business
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/tenants"><i class="fas fa-users"></i> Tenants</a></li>
                                    <li><a class="dropdown-item" href="/billing"><i class="fas fa-credit-card"></i> Billing</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/compliance"><i class="fas fa-shield-alt"></i> Compliance</a></li>
                                </ul>
                            </li>

                            <!-- Notifications -->
                            <li class="nav-item">
                                <a class="nav-link position-relative" href="/notifications">
                                    <i class="fas fa-bell"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="display: none;">
                                        0
                                    </span>
                                </a>
                            </li>

                            <!-- Admin -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog"></i> Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="/admin/api-configurations"><i class="fas fa-plug"></i> API Configurations</a></li>
                                </ul>
                            </li>
                        @endauth
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

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
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

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>