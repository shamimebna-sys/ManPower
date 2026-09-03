<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" dir="{{ __('voyager::generic.is_rtl') == 'true' ? 'rtl' : 'ltr' }}">
<head>
    <title>@yield('page_title', setting('admin.title') . " - " . setting('admin.description'))</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="assets-path" content="{{ route('voyager.voyager_assets') }}"/>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">

    <!-- Favicon -->
    <?php $admin_favicon = Voyager::setting('admin.icon_image', ''); ?>
    @if($admin_favicon == '')
        <link rel="shortcut icon" href="{{ voyager_asset('images/logo-icon.png') }}" type="image/png">
    @else
        <link rel="shortcut icon" href="{{ Voyager::image($admin_favicon) }}" type="image/png">
    @endif



    <!-- App CSS -->
    <link rel="stylesheet" href="{{ voyager_asset('css/app.css') }}">

    @yield('css')
    @if(__('voyager::generic.is_rtl') == 'true')
        <link rel="stylesheet" href="{{ asset('admin/bootstrap-rtl.css') }}">
        <link rel="stylesheet" href="{{ voyager_asset('css/rtl.css') }}">
    @endif

    <!-- Few Dynamic Styles -->
    <style type="text/css">
        .voyager .side-menu .navbar-header {
            background:{{ config('voyager.primary_color','#22A7F0') }};
            border-color:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .widget .btn-primary{
            border-color:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .widget .btn-primary:focus, .widget .btn-primary:hover, .widget .btn-primary:active, .widget .btn-primary.active, .widget .btn-primary:active:focus{
            background:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .voyager .breadcrumb a{
            color:{{ config('voyager.primary_color','#22A7F0') }};
        }
    </style>

    @if(!empty(config('voyager.additional_css')))<!-- Additional CSS -->
        @foreach(config('voyager.additional_css') as $css)<link rel="stylesheet" type="text/css" href="{{ asset($css) }}">@endforeach
    @endif

    <!-- Smart Image Error Fallback Script -->
    <script>
        window.addEventListener('error', function (e) {
            if (e.target.tagName === 'IMG') {
                if (!e.target.dataset.fallbackApplied) {
                    e.target.dataset.fallbackApplied = 'true';
                    const name = e.target.alt || 'User';
                    const initials = name.split(' ').filter(Boolean).map(n => n[0]).join('').substring(0, 2).toUpperCase();
                    const colors = ['%234f46e5', '%2306b6d4', '%2310b981', '%23f59e0b', '%23ec4899', '%238b5cf6'];
                    let hash = 0;
                    for (let i = 0; i < name.length; i++) {
                        hash = name.charCodeAt(i) + ((hash << 5) - hash);
                    }
                    const color = colors[Math.abs(hash) % colors.length];
                    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="${color}"/><text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" font-size="38" fill="white">${initials || 'U'}</text></svg>`;
                    e.target.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
                }
            }
        }, true);
    </script>

    @yield('head')
</head>

<body class="voyager @if(isset($dataType) && isset($dataType->slug)){{ $dataType->slug }}@endif">

<div id="voyager-loader">
    <?php $admin_loader_img = Voyager::setting('admin.loader', ''); ?>
    @if($admin_loader_img == '')
        <img src="{{ voyager_asset('images/logo-icon.png') }}" alt="Voyager Loader">
    @else
        <img src="{{ Voyager::image($admin_loader_img) }}" alt="Voyager Loader">
    @endif
</div>

<?php
if (\Illuminate\Support\Str::startsWith(Auth::user()->avatar, 'http://') || \Illuminate\Support\Str::startsWith(Auth::user()->avatar, 'https://')) {
    $user_avatar = Auth::user()->avatar;
} else {
    $user_avatar = Voyager::image(Auth::user()->avatar);
}
?>

<div class="app-container">
    <div class="fadetoblack visible-xs"></div>
    <div class="row content-container">
        @include('voyager::dashboard.navbar')
        @include('voyager::dashboard.sidebar')

        <script>
            (function(){
                    var appContainer = document.querySelector('.app-container'),
                        sidebar = appContainer.querySelector('.side-menu'),
                        navbar = appContainer.querySelector('nav.navbar.navbar-top'),
                        loader = document.getElementById('voyager-loader'),
                        hamburgerMenu = document.querySelector('.hamburger'),
                        sidebarTransition = sidebar.style.transition,
                        navbarTransition = navbar.style.transition,
                        containerTransition = appContainer.style.transition;

                    sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition =
                    appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition =
                    navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = 'none';

                    if (window.innerWidth > 768 && window.localStorage && window.localStorage['voyager.stickySidebar'] == 'true') {
                        appContainer.className += ' expanded no-animation';
                        loader.style.left = (sidebar.clientWidth/2)+'px';
                        hamburgerMenu.className += ' is-active no-animation';
                    }

                   navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = navbarTransition;
                   sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition = sidebarTransition;
                   appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition = containerTransition;
            })();
        </script>
        <!-- Main Content -->
        <div class="container-fluid">
            <div class="side-body padding-top">
                @php
                    $announcements = \App\Models\Announcement::where('status', 'A')
                    ->whereDate('anounce_date', '<=', \Carbon\Carbon::today())
                    ->whereDate('anounce_date_end', '>=', \Carbon\Carbon::today())
                    ->get();
                @endphp
                @if($announcements)

                    <style>
                        .marquee-wrapper {
                            overflow: hidden;
                            position: relative;
                            /*height: 40px;*/
                            display: flex;
                            align-items: center;
                            border-bottom: 1px solid hsl(0deg 29.44% 91.72% / 90%);
                            background: #f8fafc;
                            margin-bottom: 10px;
                        }

                        .marquee-content {
                            display: inline-block;
                            white-space: nowrap;
                            animation: scroll-left 60s linear infinite;
                            padding-left: 100%;
                        }

                        /* Pause animation on hover */
                        .marquee-wrapper:hover .marquee-content {
                            animation-play-state: paused;
                        }

                        @keyframes scroll-left {
                            0%   { transform: translateX(0%); }
                            100% { transform: translateX(-100%); }
                        }

                        .marquee-content a {
                            margin-right: 50px;
                            text-decoration: none;
                            font-weight: bold;
                            color: #0d6efd;
                        }

                        .marquee-content a:hover {
                            text-decoration: underline;
                        }
                    </style>

                <div class="marquee-wrapper">
                    <div class="marquee-content">
                        @foreach($announcements as $a)
                        <a href="{{route('voyager.announcements.show', $a->id)}}"><i class="voyager-double-right"></i>{{$a->topic}}</a>
                        @endforeach
                    </div>
                </div>
                @endif



                @yield('page_header')
                <div id="voyager-notifications"></div>
                @yield('content')
            </div>
        </div>
    </div>
</div>
@include('voyager::partials.app-footer')

<!-- Javascript Libs -->


<script type="text/javascript" src="{{ voyager_asset('js/app.js') }}"></script>
<script src="{{ asset('admin/chart.min.js') }}"></script>

<script>
    @if(Session::has('alerts'))
        let alerts = {!! json_encode(Session::get('alerts')) !!};
        helpers.displayAlerts(alerts, toastr);
    @endif

    @if(Session::has('message'))

    // TODO: change Controllers to use AlertsMessages trait... then remove this
    var alertType = {!! json_encode(Session::get('alert-type', 'info')) !!};
    var alertMessage = {!! json_encode(Session::get('message')) !!};
    var alerter = toastr[alertType];

    if (alerter) {
        alerter(alertMessage);
    } else {
        toastr.error("toastr alert-type " + alertType + " is unknown");
    }
    @endif
</script>
@include('voyager::media.manager')
@yield('javascript')
@stack('javascript')
@if(!empty(config('voyager.additional_js')))<!-- Additional Javascript -->
    @foreach(config('voyager.additional_js') as $js)<script type="text/javascript" src="{{ asset($js) }}"></script>@endforeach
@endif

</body>
</html>
