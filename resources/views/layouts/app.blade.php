{{-- Autor: Marbin --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.app_title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/techgate.css') }}" rel="stylesheet">
</head>
<body>

    <div class="tg-topbar">
        <div class="container d-flex align-items-center justify-content-center gap-3 flex-wrap">
            <span>{{ __('messages.topbar_shipping') }}</span>
            <span class="d-none d-md-inline">{{ __('messages.topbar_secure') }}</span>
            @if($navClima)
                <span class="tg-weather d-none d-sm-inline">{{ __('messages.weather_in') }}: {{ $navClima['temperatura'] }}°C · {{ $navClima['descripcion'] }}</span>
            @endif

            {{-- Selector de idioma --}}
            <span class="tg-lang ms-md-3">
                <a href="{{ route('language.switch', 'es') }}" class="{{ app()->getLocale() === 'es' ? 'active' : '' }}">ES</a>
                <span class="divider">|</span>
                <a href="{{ route('language.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
            </span>
        </div>
    </div>

    <header class="tg-header">
        <div class="container d-flex align-items-center gap-3 flex-wrap">
            <a class="tg-brand" href="{{ route('products.index') }}">TechGate<span class="dot"></span></a>

            @auth
                <form class="tg-search" method="GET" action="{{ route('products.index') }}">
                    <input type="text" name="buscar" class="form-control" placeholder="{{ __('messages.search_placeholder') }}"
                           value="{{ request('buscar') }}">
                    <button type="submit" aria-label="{{ __('messages.search') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    </button>
                </form>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    <div class="dropdown">
                        <a class="tg-hlink dropdown-toggle" href="#" role="button" id="userMenu"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="d-none d-md-inline">{{ \Illuminate\Support\Str::words(auth()->user()->getNombre(), 1, '') }}</span>
                            <span class="tg-role">{{ auth()->user()->esAdministrador() ? __('messages.role_admin') : __('messages.role_user') }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end tg-user-menu" aria-labelledby="userMenu">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('messages.menu_personal_data') }}</a>
                            </li>
                            @if(auth()->user()->esUsuario())
                                <li>
                                    <a class="dropdown-item" href="{{ route('orders.index') }}">{{ __('messages.menu_my_orders') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('payment-methods.index') }}">{{ __('messages.menu_payment') }}</a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">{{ __('messages.logout') }}</button>
                                </form>
                            </li>
                        </ul>
                    </div>

                    @if(auth()->user()->esUsuario())
                        <a class="tg-cart" href="{{ route('cart.index') }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="18" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                            <span class="d-none d-sm-inline">{{ $navCartCount > 0 ? '$'.number_format($navCartTotal, 2) : __('messages.cart') }}</span>
                            <span class="count">{{ $navCartCount }}</span>
                        </a>
                    @endif
                </div>
            @else
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <a class="tg-hlink" href="{{ route('login') }}">{{ __('messages.login') }}</a>
                    <a class="tg-cart" href="{{ route('register') }}">{{ __('messages.register') }}</a>
                </div>
            @endauth
        </div>
    </header>

    @auth
        <nav class="tg-nav">
            <div class="container">
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.index') && ! request('category_id') ? 'active' : '' }}">{{ __('messages.nav_catalog') }}</a>
                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.index') ? 'active' : '' }}">{{ __('messages.nav_categories') }}</a>

                @if(auth()->user()->esUsuario())
                    <a href="{{ route('comparator.index') }}" class="{{ request()->routeIs('comparator.index') ? 'active' : '' }}">{{ __('messages.nav_comparator') }}</a>
                    <a href="{{ route('builder.index') }}" class="{{ request()->routeIs('builder.index') ? 'active' : '' }}">{{ __('messages.nav_builder') }}</a>
                    <a href="{{ route('allied.index') }}" class="{{ request()->routeIs('allied.index') ? 'active' : '' }}">{{ __('messages.nav_allied_products') }}</a>
                @endif

                @if(auth()->user()->esAdministrador())
                    <div class="sep"></div>
                    <a class="admin-link" href="{{ route('products.create') }}">{{ __('messages.nav_add_product') }}</a>
                    <a class="admin-link" href="{{ route('categories.create') }}">{{ __('messages.nav_add_category') }}</a>
                    <a class="admin-link" href="{{ route('orders.admin') }}">{{ __('messages.nav_store_orders') }}</a>
                @endif
            </div>
        </nav>
    @endauth

    <main>
        <div class="container mt-3">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <footer class="tg-footer tg-footer-min">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="tg-brand text-white" style="font-size:1.25rem">TechGate</div>
            <div class="copy">© {{ date('Y') }} TechGate · {{ __('messages.footer_copy') }}</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
