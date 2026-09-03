<!doctype html>
<html class="no-js" lang="{{ config('app.locale') }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{env('APP_NAME')}} - @yield('title', '')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Place favicon.ico in the root directory -->
    <link href="{{asset('assets/images/logo-new.png')}}" type="img/x-icon" rel="shortcut icon">
    <!-- All css files are included here. -->
    <link rel="stylesheet" href="{{asset('assets/css/vendor/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/vendor/iconfont.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/vendor/helper.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/plugins.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <!-- Modernizr JS -->
    <script src="{{asset('assets/js/vendor/modernizr-3.10.0.min.js')}}"></script>
    <link rel='stylesheet' href='{{ asset("assets/css/toastr.min.css") }}' type='text/css' media='all'>

    {{--<style>
        .header-absolute {
            background: white;
        }

        .header-absolute .main-menu > ul > li > a {
            color: #00243e;
        }

        .header-btn-action {
            display: none !important;
        }
    </style>--}}

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

    @yield('header')

</head>

<body class="template-color-1">

<div id="main-wrapper">



    <!--Header section start-->
    <header class="black-logo-version header-sticky sticky-white d-none d-lg-block">
        <div class="main-header">
            <div class="container-fluid pl-50 pl-lg-15 pl-md-15 pr-0">
                <div class="row align-items-center g-0">

                    <!--Logo start-->
                    <div class="col-xl-2 col-lg-2 col-12">
                        <div class="logo">
                            <a href="#"><img src="{{asset('assets/images/logo-new.png')}}" alt=""></a>
                        </div>
                    </div>
                    <!--Logo end-->

                    <!--Menu start-->
                    <div class="col-xl-7 col-lg-7 col-12">
                        <nav class="main-menu">
                            <ul>
                                <li><a href="{{route('web.home')}}">Home</a>   </li>
                                <li><a href="{{route('web.about')}}">About Us</a>   </li>
                                {{--<li><a href="#">About <small class="icon-arrow"></small></a>
                                    <ul class="sub-menu">
                                        <li><a href="{{route('web.about')}}">Who we are</a></li>
                                        <li><a href="#">Teachers</a></li>
                                        <li><a href="#">Agents</a></li>
                                        <li><a href="#">Employers</a></li>
                                    </ul>
                                </li>
                                <li><a href="#"><span class="label-status hot">hot</span>Jobs <small class="icon-arrow"></small></a>
                                    <ul class="sub-menu">
                                        <li><a href="#">Fuel Station</a></li>
                                        <li><a href="#">Super Market </a></li>
                                        <li><a href="#">Housekeeping </a></li>
                                        <li><a href="#">Kitchen Cleaner</a></li>
                                        <li><a href="#">Cook and Kitchen</a></li>
                                        <li><a href="#">Waiter </a></li>
                                    </ul>
                                </li>--}}

{{--                                <li><a href="#">Blog</a>   </li>--}}
                                <li><a href="{{route('web.faq')}}">FAQS</a>   </li>
                                <li><a href="{{route('web.contact')}}">Contact Us</a>   </li>
                                <li><a href="{{route('web.candidates')}}">Candidates</a>   </li>
{{--                                <li><a href="{{route('web.employers')}}">Employers</a>   </li>--}}

                                {{--@if (!Auth::check())
                                <li><a href="#"><span class="label-status new">new</span> Registration <small class="icon-arrow"></small></a>
                                    <ul class="sub-menu">
                                        <li><a href="{{route('web.registration', 'candidate')}}">Become A Candidate</a></li>
                                        <li><a href="{{route('web.registration', 'agent')}}">Become A Agent</a></li>
                                        <li><a href="{{route('web.registration', 'teacher')}}">Become A Teacher</a></li>
                                        <li><a href="{{route('web.registration', 'employer')}}">Become A Employer</a></li>
                                    </ul>
                                </li>
                                @endif--}}

                                @if (Auth::check())
                                <li><a href="#">{{auth::user()->name}}<small class="icon-arrow"></small></a>
                                    <ul class="sub-menu">
                                        @if(auth::user()->role_id != 104)
                                        <li><a href="{{url('/panel')}}"><i class="lnr lnr-chart-bars"></i> Dashboard</a></li>
                                        @endif
{{--                                        <li><a href="{{route('web.dashboard')}}"><i class="lnr lnr-chart-bars"></i> Dashboard</a></li>--}}
{{--                                        <li><a href="#"><i class="lnr lnr-user"></i> Profile</a></li>--}}
  @if(auth::user()->role_id == 104)
                                        <li><a href="{{route('web.candidates')}}?purpose=FAVORITE"><i class="lnr lnr-heart"></i> Favorite Candidates</a></li>
                                        <li><a href="{{route('web.candidates')}}?purpose=RESERVE"><i class="lnr lnr-star"></i> Reserve Candidates</a></li>
                                        <li><a href="{{route('web.candidates')}}?purpose=SELECTED"><i class="lnr lnr-user"></i> Selected Candidates</a></li>
 @endif
                                        <li><a href="{{route('web.logout')}}"><i class="lnr lnr-exit-up"></i> Logout </a></li>
                                    </ul>
                                </li>

                                @endif

                            </ul>
                        </nav>
                    </div>
                    <!--Menu end-->

                    <!-- Cart & Search Area Start -->
                    <div class="col-xl-3 col-lg-3 col-12" >
                        <div class="header-btn-action d-flex justify-content-end">
                            <div class="btn-action-wrap d-flex">
                                @if (!Auth::check())
                                <div class="jp-author item">
                                    <a href="{{route('web.login')}}">
                                        <i class="lnr lnr-user"></i>
                                        <span>Login</span>
                                    </a>
                                </div>
                                @endif
                                <div class="jp-author-action item" style="display: none">
                                    <a href="#quick-view-modal-container" data-toggle="modal"> <span>Employer</span> <span class="fw-400">Post a job</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Cart & Search Area End -->
                </div>

            </div>
        </div>
    </header>
    <!--Header section end-->

    <!--Header Mobile section start-->
    <header class="header-mobile bg_color--2 d-block d-lg-none">
        <div class="header-bottom menu-right">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="header-mobile-navigation d-block d-lg-none">
                            <div class="row align-items-center">
                                <div class="col-3 col-md-3">
                                    <div class="mobile-navigation text-right">
                                        <div class="header-icon-wrapper">
                                            <ul class="icon-list justify-content-start">
                                                <li class="popup-mobile-click">
                                                    <a href="javascript:void(0)"><i class="lnr lnr-menu"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="header-logo text-center">
                                        <a href="#">
                                            <img src="assets/images/logo-new.png" class="img-fluid" alt="">
                                        </a>
                                    </div>
                                </div>
                                <div class="col-3 col-md-3">
                                    <div class="mobile-navigation text-right">
                                        <div class="header-icon-wrapper">
                                            <ul class="icon-list justify-content-end">
                                                <li>
                                                    <div class="header-cart-icon">
                                                        <a href="#" class="header-search-toggle"><i class="lnr lnr-magnifier"></i></a>
                                                    </div>
                                                    <div class="header-search-form">
                                                        <form action="#">
                                                            <input type="text" placeholder="Type and hit enter">
                                                            <button><i class="lnr lnr-magnifier"></i></button>
                                                        </form>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>
    <!--Header Mobile section end-->

    <!-- Start Popup Menu -->
    <div class="popup-mobile-manu popup-mobile-visiable">
        <div class="inner">
            <div class="mobileheader">
                <div class="logo">
                    <a href="#">
                       <h3 class="text-light">EUJOBBD</h3>
                    </a>
                </div>
                <a class="mobile-close" href="#"></a>
            </div>
            <div class="menu-content">
                <ul class="menulist object-custom-menu">
                    <li><a href="{{url('/')}}"><span>Home</span></a></li>
                    <li><a href="{{route('web.about')}}"><span>About</span></a></li>
                    <li><a href="{{route('web.faq')}}"><span>FAQ</span></a></li>
                    <li><a href="{{route('web.candidates')}}"><span>Candidates</span></a></li>
                    <li><a href="{{route('web.contact')}}"><span>Contact</span></a></li>

                </ul>
            </div>
        </div>
    </div>
    <!-- End Popup Menu -->

    <!-- Bottom Navbar Mobile Start -->
    <div class="bottom-navbar-mobile section d-block d-lg-none">
        <nav>
            <ul class="list-actions">
                <li>
                    <a class="toggle-btn active" href="{{url('/')}}">
                        <span><i class="lnr lnr-home"></i></span>
                        <span class="text">Home</span>
                    </a>
                </li>
                @if (Auth::check() && auth::user()->role_id != 104)
                <li>
                    <a   href="{{url('/panel')}}">
                        <span><i class="lnr lnr-list"></i></span>
                        <span class="text">Dashboard</span>
                    </a>
                </li>
                    @endif
                {{--<li>
                    <a class="toggle-btn toggle-btn-js" data-target="#job-list-mobile-id" href="{{url('/')}}">
                        <span><i class="lnr lnr-list"></i></span>
                        <span class="text">Jobs list</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span><i class="lnr lnr-heart"></i></span>
                        <span class="text">Save</span>
                    </a>
                </li>
                <li>
                    <a class="toggle-btn-two toggle-btn-js" data-target="#notifications-mobile-id" href="#">
                        <span><i class="lnr lnr-alarm"></i></span>
                        <span class="text">Notifications</span>
                    </a>
                </li>--}}
                <li>
                    @if(!Auth::check())
                    <a href="{{route('web.login')}}">
                        <span><i class="lnr lnr-user"></i></span>
                        <span class="text">Login</span>
                    </a>
                    @elseif(Auth::check())
                        <a href="{{route('web.logout')}}">
                            <span><i class="lnr lnr-user"></i></span>
                            <span class="text">Logout</span>
                        </a>
                    @endif
                </li>
            </ul>
        </nav>
    </div>
    <!-- Bottom Navbar Mobile End -->

    <!-- Bottom Navbar Mobile Popup Start -->
    <div class="mobile-popup">
        <div class="job-list-mobile" id="job-list-mobile-id">
            <div class="heading">
                <div class="title">
                    <i class="lnr lnr-list"></i>
                    <h3>All Jobs list</h3>
                </div>
                <a class="view-all" href="#">See all jobs</a>
            </div>
            <div class="content-popup-scroll">
                <ul class="list-item">
                    <li><a href="#"><i class="lnr lnr-printer"></i>Waiter Final Group </a></li>
                    <li><a href="#"><i class="lnr lnr-film-play"></i>Final Cook And Kitchen Asst. Group </a></li>
                    <li><a href="#"><i class="lnr lnr-chart-bars"></i>Housekeeping Job In Cyprus </a></li>
                    <li><a href="#"><i class="lnr lnr-smartphone"></i>Electrician Job In Cyprus </a></li>
                    <li><a href="#"><i class="lnr lnr-picture"></i>Driver Job In Cyprus</a></li>
                    <li><a href="#"><i class="lnr lnr-home"></i>Lifeguard Job In Cyprus </a></li>
                </ul>
            </div>
        </div>
        <div class="notifications-mobile" id="notifications-mobile-id">
            <div class="heading">
                <div class="title">
                    <i class="lnr lnr-list"></i>
                    <h3>All Notifications</h3>
                </div>
                <a class="view-all" href="#">See all jobs</a>
            </div>
            <div class="content-popup-scroll">
                <ul class="list-item">
                    <li><a href="#"><i class="lnr lnr-book"></i><span><b class="highlight">Register now</b> to reach dream jobs easier.</span> </a></li>
                    <li><a href="#"><i class="lnr lnr-book"></i><span><b class="highlight">Job suggestion</b> you might be interested based on your profile.</span> </a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Bottom Navbar Mobile Popup End -->



    @yield('content')

    <!--Footer section start-->
    <footer class="footer-section section">

        <!-- Footer Top Section Start -->
        <div
            class="footer-top-section section pt-115 pt-lg-95 pt-md-75 pt-sm-55 pt-xs-45 pb-90 pb-lg-70 pb-md-40 pb-sm-20 pb-xs-15">
            <hr />

            <div class="container">
                <div class="row">

                    <div class="col-xl-4 col-lg-3 col-md-6">
                        <!-- Footer Widget Start -->
                        <div class="footer-widget mb-30">
                            <h6 class="title">Contact Info</h6>
                            <div class="address">
                                <i class="lnr lnr-map-marker"></i>
                                <span>{{ env('APP_ADDRESS') }}</span>
                            </div>
                            <div class="email">
                                <i class="lnr lnr-envelope"></i>
                                <span>{{ env('APP_EMAIL') }}</span>
                            </div>
                            <div class="phone theme-color">{{ env('APP_PHONE') }}</div>
                        </div>
                        <!-- Footer Widget End -->
                    </div>

                    <div class="col-xl-2 col-lg-2 col-md-6">
                        <!-- Footer Widget Start -->
                        <div class="footer-widget mb-30">
                            <h6 class="title">Useful Links</h6>
                            <div class="footer-widget-link">
                                <ul>
                                    <li><a href="#">Policy Privacy</a></li>
                                    <li><a href="#">Terms And Conditions</a></li>
                                    <li><a href="#">FAQ</a></li>
                                    <li><a href="#">Contact Us</a></li>
                                </ul>
                            </div>
                        </div>
                        <!-- Footer Widget End -->
                    </div>

                    <div class="col-xl-2 col-lg-3 col-md-6">
                        <!-- Footer Widget Start -->
                        <div class="footer-widget mb-30">
                            <h6 class="title">Hot Jobs</h6>
                            <div class="footer-widget-link">
                                <ul>
                                    <li><a href="#">Waiter </a></li>
                                    <li><a href="#">Final Cook And Kitchen</a></li>
                                    <li><a href="#">Housekeeping </a></li>
                                    <li><a href="#">Supermarket </a></li>
                                </ul>
                            </div>
                        </div>
                        <!-- Footer Widget End -->
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <!-- Footer Widget Start -->
                        <div class="footer-widget mb-30">
                            <h6 class="title">Newsletter</h6>
                            <div class="newsletter">
                                <p>Join our email subscription now to get updates on <strong>new jobs</strong> and
                                    <strong>notifications</strong>.</p>
                                <div class="newsletter-form">
                                    <form id="mc-form" class="mc-form">
                                        <input type="email" placeholder="Enter Your email..." required="" name="EMAIL">
                                        <button class="ht-btn small-btn" type="submit" value="submit">Subscribe</button>
                                    </form>
                                </div>
                                <!-- mailchimp-alerts Start -->
                                <div class="mailchimp-alerts">
                                    <div class="mailchimp-submitting"></div><!-- mailchimp-submitting end -->
                                    <div class="mailchimp-success"></div><!-- mailchimp-success end -->
                                    <div class="mailchimp-error"></div><!-- mailchimp-error end -->
                                </div>
                                <!-- mailchimp-alerts end -->
                            </div>
                        </div>
                        <!-- Footer Widget End -->
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer Top Section End -->

        <!--Footer bottom start-->
        <div class="footer-bottom section fb-60">
            <div class="container">
                <div class="row g-0 st-border pt-35 pb-35 align-items-center justify-content-between">
                    <div class="col-lg-6 col-md-6">
                        <div class="copyright">
                            <p>&copy;{{date('Y')}} <a href="{{url('/')}}">EUJOBBD</a>. All rights reserved.</p>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="footer-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-google"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--Footer bottom end-->

    </footer>
    <!--Footer section end-->

    <!-- Modal Area Start -->
    <div class="modal fade quick-view-modal-container" id="quick-view-modal-container" tabindex="-1" role="dialog"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col-xl-12 col-lg-12">
                        <div class="row g-0">

                            <div class="col-lg-4">
                                <div class="login-register-form-area">
                                    <div class="login-tab-menu">
                                        <ul class="nav">
                                            <li><a class="active show" data-bs-toggle="tab" href="#login">Login</a></li>
                                            <li><a data-bs-toggle="tab" href="#register">Register</a></li>
                                        </ul>
                                    </div>
                                    <div class="tab-content">
                                        <div id="login" class="tab-pane fade show active">
                                            <div class="login-register-form">
                                                <form action="#" method="post">
                                                    <p>Login to EUJOBBD with your registered account</p>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="single-input">
                                                                <input type="text" placeholder="Username or Email"
                                                                       name="name">
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="single-input">
                                                                <input type="password" placeholder="Password"
                                                                       name="password">
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="checkbox-input">
                                                                <input type="checkbox" name="login-form-remember"
                                                                       id="login-form-remember">
                                                                <label for="login-form-remember">Remember me</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mb-25">
                                                            <button class="ht-btn">Login</button>
                                                        </div>
                                                    </div>
                                                </form>
                                                <div class="divider">
                                                    <span class="line"></span>
                                                    <span class="circle">or login with</span>
                                                </div>
                                                <div class="social-login">
                                                    <ul class="social-icon">
                                                        <li><a class="facebook" href="#"><i class="fab fa-facebook"></i></a>
                                                        </li>
                                                        <li><a class="twitter" href="#"><i
                                                                    class="fab fa-twitter"></i></a></li>
                                                        <li><a class="linkedin" href="#"><i class="fab fa-linkedin"></i></a>
                                                        </li>
                                                        <li><a class="google" href="#"><i
                                                                    class="fab fa-google-plus"></i></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="register" class="tab-pane fade">
                                            <div class="login-register-form">
                                                <form action="#" method="post">
                                                    <p>Create Your account</p>
                                                    <div class="row row-5">
                                                        <div class="col-12">
                                                            <div class="single-input">
                                                                <input type="text" placeholder="Your Username"
                                                                       name="name">
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="single-input">
                                                                <input type="email" placeholder="Your Email Address"
                                                                       name="emain">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="single-input">
                                                                <input type="password" placeholder="Password"
                                                                       name="password">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="single-input">
                                                                <input type="password" placeholder="Confirm Password"
                                                                       name="conPassword">
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="checkbox-input">
                                                                <input type="checkbox" name="login-form-candidate"
                                                                       id="login-form-candidate">
                                                                <label for="login-form-candidate">I am a
                                                                    candidate</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="checkbox-input">
                                                                <input type="checkbox" name="login-form-employer"
                                                                       id="login-form-employer">
                                                                <label for="login-form-employer">I am a employer</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="register-account">
                                                                <input id="register-terms-conditions" type="checkbox"
                                                                       class="checkbox" checked="" required="">
                                                                <label for="register-terms-conditions">I read and agree
                                                                    to the <a href="#">Terms &amp; Conditions</a> and <a
                                                                        href="#">Privacy Policy</a></label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mb-25">
                                                            <button class="ht-btn">Register</button>
                                                        </div>
                                                    </div>
                                                </form>
                                                <div class="divider">
                                                    <span class="line"></span>
                                                    <span class="circle">or login with</span>
                                                </div>
                                                <div class="social-login">
                                                    <ul class="social-icon">
                                                        <li>
                                                            <a class="facebook" href="#"><i class="fab fa-facebook"></i></a>
                                                        </li>
                                                        <li>
                                                            <a class="twitter" href="#"><i  class="fab fa-twitter"></i></a></li>
                                                        <li>
                                                            <a class="linkedin" href="#"><i class="fab fa-linkedin"></i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="google" href="#"><i class="fab fa-google-plus"></i></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="login-instruction">
                                    <div class="login-instruction-content">
                                        <h3 class="title">Why Login To Us</h3>
                                        <p>It’s important for you to have an account and login in order to have full
                                            access at Jotopa. We need to know your account details in order to allow
                                            work together</p>
                                        <ul class="list-reasons">
                                            <li class="reason">Be alerted to the latest jobs</li>
                                            <li class="reason">Apply for jobs with a single click</li>
                                            <li class="reason">Showcase your CV to thousands of employers</li>
                                            <li class="reason">Keep a record of all your applications</li>
                                        </ul>
                                        <span class="sale-text theme-color border-color">Login today &amp; Get 15% Off Coupon for the first planning purchase</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- Modal Area End -->

</div>

<!-- All jquery file included here -->
<script src="{{asset('assets/js/vendor/jquery-3.5.0.min.js')}}"></script>
<script src="{{asset('assets/js/vendor/jquery-migrate-3.1.0.min.js')}}"></script>
<script src="{{asset('assets/js/vendor/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/js/plugins/plugins.js')}}"></script>
<script src="{{asset('assets/js/main.js')}}"></script>
<script type="text/javascript" src="{{ asset('assets/js/toastr.min.js') }}"></script>
@yield('footer')

</body>
</html>
