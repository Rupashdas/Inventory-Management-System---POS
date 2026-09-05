<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <title>@yield('title', 'Dashboard') &middot; {{ config('app.name') }}</title>

    <link rel="icon" type="image/x-icon" href="{{asset('/favicon.ico')}}" />
    <link href="{{asset('css/bootstrap.css')}}" rel="stylesheet" />
    <link href="{{asset('css/animate.min.css')}}" rel="stylesheet" />
    <link href="{{asset('css/fontawesome.css')}}" rel="stylesheet" />
    <link href="{{asset('css/style.css')}}" rel="stylesheet" />
    <link href="{{asset('css/toastify.min.css')}}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="{{asset('css/jquery.dataTables.min.css')}}" rel="stylesheet" />

    {{-- Last, so it wins over Bootstrap and the original stylesheet. --}}
    <link href="{{asset('css/theme.css')}}" rel="stylesheet" />

    <script src="{{asset('js/jquery-3.7.0.min.js')}}"></script>
    <script src="{{asset('js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('js/toastify-js.js')}}"></script>
    <script src="{{asset('js/axios.min.js')}}"></script>
    <script src="{{asset('js/config.js')}}"></script>
    <script src="{{asset('js/bootstrap.bundle.js')}}"></script>
</head>

<body>

<div id="loader" class="LoadingOverlay d-none">
    <div class="Line-Progress">
        <div class="indeterminate"></div>
    </div>
</div>

<nav class="navbar fixed-top px-0">
    <div class="container-fluid">

        <a class="navbar-brand" href="{{ url('/dashboard') }}">
            <span class="icon-nav m-0 h5" onclick="event.preventDefault(); MenuBarClickHandler()">
                <i class="bi bi-list"></i>
            </span>
            <span class="brand-mark">
                <span class="brand-dot"><i class="bi bi-shop"></i></span>
                {{ config('app.name') }}
            </span>
        </a>

        <div class="topbar-user">
            <div class="who d-none d-sm-block">
                <strong>{{ $authUser ? $authUser->firstName . ' ' . $authUser->lastName : 'Signed in' }}</strong>
                <span>{{ $authUser->email ?? '' }}</span>
            </div>
            <div class="user-dropdown">
                <img class="icon-nav-img" src="{{asset('images/user.webp')}}" alt="Account menu"/>
                <div class="user-dropdown-content">
                    <div class="mt-4 text-center">
                        <img class="icon-nav-img" src="{{asset('images/user.webp')}}" alt=""/>
                        <h6 class="mt-2 mb-0">{{ $authUser ? $authUser->firstName . ' ' . $authUser->lastName : 'Account' }}</h6>
                        <hr class="user-dropdown-divider p-0"/>
                    </div>
                    <a href="{{url('/userProfile')}}" class="side-bar-item {{ Request::is('userProfile') ? 'active' : '' }}">
                        <i class="bi bi-person"></i>
                        <span class="side-bar-item-caption">Profile</span>
                    </a>
                    <a href="{{url('/logout')}}" class="side-bar-item">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="side-bar-item-caption">Log out</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>


<div id="sideNavRef" class="side-nav-open">

    <div class="side-nav-section">Overview</div>

    <a href="{{url('/dashboard')}}" class="side-bar-item {{ Request::is('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i>
        <span class="side-bar-item-caption">Dashboard</span>
    </a>

    <div class="side-nav-section">Selling</div>

    <a href="{{url('/salePage')}}" class="side-bar-item {{ Request::is('salePage') ? 'active' : '' }}">
        <i class="bi bi-upc-scan"></i>
        <span class="side-bar-item-caption">New sale</span>
    </a>

    <a href="{{url('/invoicePage')}}" class="side-bar-item {{ Request::is('invoicePage') ? 'active' : '' }}">
        <i class="bi bi-receipt"></i>
        <span class="side-bar-item-caption">Invoices</span>
    </a>

    <a href="{{url('/customerPage')}}" class="side-bar-item {{ Request::is('customerPage') ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span class="side-bar-item-caption">Customers</span>
    </a>

    <div class="side-nav-section">Inventory</div>

    <a href="{{url('/productPage')}}" class="side-bar-item {{ Request::is('productPage') ? 'active' : '' }}">
        <i class="bi bi-box-seam"></i>
        <span class="side-bar-item-caption">Products &amp; stock</span>
    </a>

    <a href="{{url('/categoryPage')}}" class="side-bar-item {{ Request::is('categoryPage') ? 'active' : '' }}">
        <i class="bi bi-tags"></i>
        <span class="side-bar-item-caption">Categories</span>
    </a>

    <div class="side-nav-section">Reporting</div>

    <a href="{{url('/reportPage')}}" class="side-bar-item {{ Request::is('reportPage') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span class="side-bar-item-caption">Reports</span>
    </a>

</div>


<div id="contentRef" class="content">
    @yield('content')
</div>


<script>
    function MenuBarClickHandler() {
        let sideNav = document.getElementById('sideNavRef');
        let content = document.getElementById('contentRef');
        if (sideNav.classList.contains("side-nav-open")) {
            sideNav.classList.add("side-nav-close");
            sideNav.classList.remove("side-nav-open");
            content.classList.add("content-expand");
            content.classList.remove("content");
        } else {
            sideNav.classList.remove("side-nav-close");
            sideNav.classList.add("side-nav-open");
            content.classList.remove("content-expand");
            content.classList.add("content");
        }
    }

</script>

</body>
</html>
