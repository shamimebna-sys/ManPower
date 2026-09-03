@extends('layout')

@section('title', 'About')


@section('header')


@stop


@section('content')

    <!-- Start Popup Menu -->
    <div class="popup-mobile-manu popup-mobile-visiable">
        <div class="inner">
            <div class="mobileheader">
                <div class="logo">
                    <a href="{{url('/')}}">
                        <img src="assets/images/logo-mobile.png" alt="Multipurpose">
                    </a>
                </div>
                <a class="mobile-close" href="#"></a>
            </div>
            <div class="menu-content">
                <ul class="menulist object-custom-menu">
                    <li class="has-mega-menu"><a href="#"><span>Home</span></a>
                        <!-- Start Dropdown Menu -->
                        <ul class="object-submenu">
                            <li><a href="index-2.html"><span>Home V1</span></a></li>
                            <li><a href="index-3.html"><span>Home V2</span></a></li>
                            <li><a href="index-4.html"><span>Home V3</span></a></li>
                            <li><a href="index-5.html"><span>Home V4</span></a></li>
                        </ul>
                        <!-- End Dropdown Menu -->
                    </li>

                    <li class="has-mega-menu"><a href="#"><span>Jobs</span></a>
                        <!-- Start Dropdown Menu -->
                        <ul class="object-submenu">
                            <li><a href="job-listing.html"><span>Jobs Listing</span></a></li>
                            <li><a href="job-with-map.html"><span>Jobs With Map</span></a></li>
                            <li><a href="job-details.html"><span>Job Detail V1</span></a></li>
                            <li><a href="job-details-two.html"><span>Job Detail V2</span></a></li>
                        </ul>
                        <!-- End Dropdown Menu -->
                    </li>

                    <li class="has-mega-menu"><a href="#"><span>Candidates</span></a>
                        <!-- Start Dropdown Menu -->
                        <ul class="object-submenu">
                            <li><a href="candidates-listing.html"><span>Candidates Listing</span></a></li>
                            <li><a href="candidate-details.html"><span>Candidate Details V1</span></a></li>
                            <li><a href="candidate-details-two.html"><span>Candidate Details V2</span></a></li>
                        </ul>
                        <!-- End Dropdown Menu -->
                    </li>

                    <li class="has-mega-menu"><a href="#"><span>Employers</span></a>
                        <!-- Start Dropdown Menu -->
                        <ul class="object-submenu">
                            <li><a href="employer-listing.html"><span>Employers Listing</span></a></li>
                            <li><a href="employer-details.html"><span>Employer Detail V1</span></a></li>
                            <li><a href="employer-details-two.html"><span>Employer Detail V2</span></a></li>
                        </ul>
                        <!-- End Dropdown Menu -->
                    </li>

                    <li class="has-mega-menu"><a href="#"><span>Blog</span></a>
                        <!-- Start Dropdown Menu -->
                        <ul class="object-submenu">
                            <li><a href="blog.html"><span>Blog</span></a></li>
                            <li><a href="blog-details.html"><span>Blog Details</span></a></li>
                        </ul>
                        <!-- End Dropdown Menu -->
                    </li>

                    <li class="has-mega-menu"><a href="#"><span>Pages</span></a>
                        <!-- Start Dropdown Menu -->
                        <ul class="object-submenu">
                            <li><a href="about.html"><span>About Us</span></a></li>
                            <li><a href="contact.html"><span>Contact Us</span></a></li>
                            <li><a href="faq.html"><span>FAQS</span></a></li>
                            <li><a href="pricing.html"><span>Pricing & Plan</span></a></li>
                            <li><a href="login-register.html"><span>Login / Register</span></a></li>
                            <li><a href="dashboard.html"><span>Dashboard</span></a></li>
                            <li><a href="shop.html"><span>Shop</span></a></li>
                            <li><a href="product-details.html"><span>Product Details</span></a></li>
                            <li><a href="cart.html"><span>Cart</span></a></li>
                            <li><a href="checkout.html"><span>Checkout</span></a></li>
                            <li><a href="wishlist.html"><span>Wishlist</span></a></li>
                            <li><a href="404.html"><span>404 Error</span></a></li>
                        </ul>
                        <!-- End Dropdown Menu -->
                    </li>
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
                    <a class="toggle-btn active" href="index-2.html">
                        <span><i class="lnr lnr-home"></i></span>
                        <span class="text">Home</span>
                    </a>
                </li>
                <li>
                    <a class="toggle-btn toggle-btn-js" data-target="#job-list-mobile-id" href="#">
                        <span><i class="lnr lnr-list"></i></span>
                        <span class="text">Jobs list</span>
                    </a>
                </li>
                <li>
                    <a href="login-register.html">
                        <span><i class="lnr lnr-heart"></i></span>
                        <span class="text">Save</span>
                    </a>
                </li>
                <li>
                    <a class="toggle-btn-two toggle-btn-js" data-target="#notifications-mobile-id" href="#">
                        <span><i class="lnr lnr-alarm"></i></span>
                        <span class="text">Notifications</span>
                    </a>
                </li>
                <li>
                    <a href="login-register.html">
                        <span><i class="lnr lnr-user"></i></span>
                        <span class="text">Account</span>
                    </a>
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
                    <li><a href="job-listing.html"><i class="lnr lnr-printer"></i>Accounting </a></li>
                    <li><a href="job-listing.html"><i class="lnr lnr-film-play"></i>Broadcasting </a></li>
                    <li><a href="job-listing.html"><i class="lnr lnr-phone"></i>Customer Service </a></li>
                    <li><a href="job-listing.html"><i class="lnr lnr-bullhorn"></i>Digital Marketing </a></li>
                    <li><a href="job-listing.html"><i class="lnr lnr-chart-bars"></i>Finance & Accounting </a></li>
                    <li><a href="job-listing.html"><i class="lnr lnr-smartphone"></i>Game Mobile </a></li>
                    <li><a href="job-listing.html"><i class="lnr lnr-picture"></i>Graphics & Design </a></li>
                    <li><a href="job-listing.html"><i class="lnr lnr-home"></i>Graphics & Design </a></li>
                    <li><a href="job-listing.html"><i class="lnr lnr-database"></i>Medical Doctor </a></li>
                    <li><a href="job-listing.html"><i class="lnr lnr-dinner"></i>Restaurant </a></li>
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
                    <li><a href="login-register.html"><i class="lnr lnr-book"></i><span><b class="highlight">Register now</b> to reach dream jobs easier.</span> </a></li>
                    <li><a href="job-with-map.html"><i class="lnr lnr-book"></i><span><b class="highlight">Job suggestion</b> you might be interested based on your profile.</span> </a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Bottom Navbar Mobile Popup End -->

    <!-- Page Banner Section Start -->
    <div class="page-banner-section section">
        <div class="banner-image">
            <img src="assets/images/about/bg-top-about-us.jpg" alt="">
        </div>
    </div>
    <!-- Page Banner Section End -->

    <!-- Breadcrumb Section Start -->
    <div class="breadcrumb-section section pt-60 pt-sm-50 pt-xs-40 pb-60 pb-sm-50 pb-xs-40">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page-breadcrumb-content">
                        <ul class="page-breadcrumb">
                            <li><a href="index-2.html">Home</a></li>
                            <li>About us</li>
                        </ul>
                        <h1>About Us</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Section Start -->

    <!-- About Content Section Start -->
    <div class="about-content-section section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="about-content">
                        <p class="text-bold">Hewett Recruitment has been established since 2009. We have developed and expanded our services by listening to the needs of both our Clients and Candidates over the years, becoming one of the leading recruitment companies in the area. Hewett Recruitment has been established since 2009. </p>
                        <p class="normal-text">Hewett Recruitment has been established since 2009. We have developed and expanded our services by listening to the needs of both our Clients and Candidates over the years, becoming one of the leading recruitment companies in the area. Hewett Recruitment has been established since 2009.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About Content Section End -->

    <!-- Funfact Section Start  -->
    <div class="funfact-section section pt-110 pt-lg-90 pt-md-70 pt-sm-50 pt-xs-40 pb-85 pb-lg-65 pb-md-45 pb-sm-25 pb-xs-15">
        <div class="container">
            <div class="row row-five-column justify-content-lg-between">

                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-4">
                    <!-- Single Funfact Start -->
                    <div class="single-funfact funfact-style-two text-center justify-content-center width-100 mb-30">
                        <div class="funfact-content">
                            <span class="counter theme-color">30,124</span>
                            <span class="text">Total registed users</span>
                        </div>
                    </div>
                    <!-- Single Funfact End -->
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-4">
                    <!-- Single Funfact Start -->
                    <div class="single-funfact funfact-style-two text-center justify-content-center width-100 mb-30">
                        <div class="funfact-content">
                            <span class="counter theme-color">11,112</span>
                            <span class="text">Total Jobs</span>
                        </div>
                    </div>
                    <!-- Single Funfact End -->
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-4">
                    <!-- Single Funfact Start -->
                    <div class="single-funfact funfact-style-two text-center justify-content-center width-100 mb-30">
                        <div class="funfact-content">
                            <span class="counter theme-color">5,251</span>
                            <span class="text">Employers</span>
                        </div>
                    </div>
                    <!-- Single Funfact End -->
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-4">
                    <!-- Single Funfact Start -->
                    <div class="single-funfact funfact-style-two text-center justify-content-center width-100 mb-30">
                        <div class="funfact-content">
                            <span class="counter theme-color">25,514</span>
                            <span class="text">Job applications</span>
                        </div>
                    </div>
                    <!-- Single Funfact End -->
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-4">
                    <!-- Single Funfact Start -->
                    <div class="single-funfact funfact-style-two text-center justify-content-center width-100 mb-30">
                        <div class="funfact-content">
                            <span class="counter theme-color">35</span>
                            <span class="text">Global Branchs</span>
                        </div>
                    </div>
                    <!-- Single Funfact End -->
                </div>

            </div>
        </div>
    </div>
    <!-- Funfact Section Start  -->

    <!-- Personal Skill Section Start -->
    <div class="personal-skill-section section bg-image-proparty bg_image--2 pt-120 pt-lg-100 pt-md-80 pt-sm-60 pt-xs-50 pb-120 pb-lg-100 pb-md-80 pb-sm-60 pb-xs-50">
        <div class="container">
            <div class="row g-0 border-top-left">

                <div class="col-lg-4 col-md-6 col-sm-6 d-flex">
                    <!-- Single Personal Skill Start -->
                    <div class="single-personal-skill">
                        <div class="skill-icon">
                            <img src="assets/images/icons/about1.png" alt="">
                        </div>
                        <div class="personal-skill-content">
                            <h4 class="title">Knowledge</h4>
                            <p>We have a policy of re-investing in our people offering in-house and external training and development backed up.</p>
                        </div>
                    </div>
                    <!-- Single Personal Skill End -->
                </div>

                <div class="col-lg-4 col-md-6 col-sm-6 d-flex">
                    <!-- Single Personal Skill Start -->
                    <div class="single-personal-skill">
                        <div class="skill-icon">
                            <img src="assets/images/icons/about2.png" alt="">
                        </div>
                        <div class="personal-skill-content">
                            <h4 class="title">Integrity</h4>
                            <p>We are consistent in the way we act and how we treat our clients, <strong>candidates and each other</strong>.</p>
                        </div>
                    </div>
                    <!-- Single Personal Skill End -->
                </div>

                <div class="col-lg-4 col-md-6 col-sm-6 d-flex">
                    <!-- Single Personal Skill Start -->
                    <div class="single-personal-skill">
                        <div class="skill-icon">
                            <img src="assets/images/icons/about3.png" alt="">
                        </div>
                        <div class="personal-skill-content">
                            <h4 class="title">Adaptability</h4>
                            <p>We are not set in our ways. In this changing market place we move swiftly to meet the shifting demands</p>
                        </div>
                    </div>
                    <!-- Single Personal Skill End -->
                </div>

                <div class="col-lg-4 col-md-6 col-sm-6 d-flex">
                    <!-- Single Personal Skill Start -->
                    <div class="single-personal-skill">
                        <div class="skill-icon">
                            <img src="assets/images/icons/about4.png" alt="">
                        </div>
                        <div class="personal-skill-content">
                            <h4 class="title">Innovation</h4>
                            <p>We continuously refine what we find smarter, more effective ways and better processes.</p>
                        </div>
                    </div>
                    <!-- Single Personal Skill End -->
                </div>

                <div class="col-lg-4 col-md-6 col-sm-6 d-flex">
                    <!-- Single Personal Skill Start -->
                    <div class="single-personal-skill">
                        <div class="skill-icon">
                            <img src="assets/images/icons/about5.png" alt="">
                        </div>
                        <div class="personal-skill-content">
                            <h4 class="title">The Passion</h4>
                            <p>We care passionately about the professional service we provide to our clients and candidates.</p>
                        </div>
                    </div>
                    <!-- Single Personal Skill End -->
                </div>

                <div class="col-lg-4 col-md-6 col-sm-6 d-flex">
                    <!-- Single Personal Skill Start -->
                    <div class="single-personal-skill">
                        <div class="skill-icon">
                            <img src="assets/images/icons/about6.png" alt="">
                        </div>
                        <div class="personal-skill-content">
                            <h4 class="title">Excellence</h4>
                            <p>We strive to achieve The Standard in everything we do.</p>
                        </div>
                    </div>
                    <!-- Single Personal Skill End -->
                </div>

            </div>
        </div>
    </div>
    <!-- Personal Skill Section End -->

    <!-- Company History Section Start -->
    <div class="company-history-section section pt-110 pt-lg-90 pt-md-70 pt-sm-50 pt-xs-40 pb-60 pb-lg-40 pb-md-20 pb-sm-0 pb-xs-45">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title-four mb-40">
                        <h3>Our story</h3>
                        <p>Hewett Recruitment has been established since 2009. We have developed and expanded our services by listening to the needs of both our Clients and Candidates over the years, becoming one of the leading recruitment companies in the area.</p>
                    </div>
                </div>
            </div>
            <div class="company-history-slider row">

                <div class="col-12">
                    <!-- Single Company History Start -->
                    <div class="single-company-history">
                        <div class="item-avatar">
                            <img src="assets/images/testimonial/testimonial-1.png" alt="">
                        </div>
                        <div class="item-meta">
                            <div class="name">May 24, 2009</div>
                            <div class="item-comment">
                                <p>Our years of experience in the region mean that we can provide informed local market intelligence to support people planning, business growth strategy, employer branding and salary benchmarking. Our average length of service for consultants is 8 years.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Single Company History End -->
                </div>

                <div class="col-12">
                    <!-- Single Company History Start -->
                    <div class="single-company-history">
                        <div class="item-avatar">
                            <img src="assets/images/testimonial/testimonial-2.png" alt="">
                        </div>
                        <div class="item-meta">
                            <div class="name">Jan 30, 2010</div>
                            <div class="item-comment">
                                <p>Our years of experience in the region mean that we can provide informed local market intelligence to support people planning, business growth strategy, employer branding and salary benchmarking. Our average length of service for consultants is 8 years.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Single Company History End -->
                </div>

                <div class="col-12">
                    <!-- Single Company History Start -->
                    <div class="single-company-history">
                        <div class="item-avatar">
                            <img src="assets/images/testimonial/testimonial-1.png" alt="">
                        </div>
                        <div class="item-meta">
                            <div class="name">May 24, 2009</div>
                            <div class="item-comment">
                                <p>Our years of experience in the region mean that we can provide informed local market intelligence to support people planning, business growth strategy, employer branding and salary benchmarking. Our average length of service for consultants is 8 years.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Single Company History End -->
                </div>

                <div class="col-12">
                    <!-- Single Company History Start -->
                    <div class="single-company-history">
                        <div class="item-avatar">
                            <img src="assets/images/testimonial/testimonial-2.png" alt="">
                        </div>
                        <div class="item-meta">
                            <div class="name">Jan 30, 2010</div>
                            <div class="item-comment">
                                <p>Our years of experience in the region mean that we can provide informed local market intelligence to support people planning, business growth strategy, employer branding and salary benchmarking. Our average length of service for consultants is 8 years.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Single Company History End -->
                </div>

                <div class="col-12">
                    <!-- Single Company History Start -->
                    <div class="single-company-history">
                        <div class="item-avatar">
                            <img src="assets/images/testimonial/testimonial-1.png" alt="">
                        </div>
                        <div class="item-meta">
                            <div class="name">May 24, 2009</div>
                            <div class="item-comment">
                                <p>Our years of experience in the region mean that we can provide informed local market intelligence to support people planning, business growth strategy, employer branding and salary benchmarking. Our average length of service for consultants is 8 years.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Single Company History End -->
                </div>

                <div class="col-12">
                    <!-- Single Company History Start -->
                    <div class="single-company-history">
                        <div class="item-avatar">
                            <img src="assets/images/testimonial/testimonial-2.png" alt="">
                        </div>
                        <div class="item-meta">
                            <div class="name">Jan 30, 2010</div>
                            <div class="item-comment">
                                <p>Our years of experience in the region mean that we can provide informed local market intelligence to support people planning, business growth strategy, employer branding and salary benchmarking. Our average length of service for consultants is 8 years.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Single Company History End -->
                </div>

                <div class="col-12">
                    <!-- Single Company History Start -->
                    <div class="single-company-history">
                        <div class="item-avatar">
                            <img src="assets/images/testimonial/testimonial-1.png" alt="">
                        </div>
                        <div class="item-meta">
                            <div class="name">May 24, 2009</div>
                            <div class="item-comment">
                                <p>Our years of experience in the region mean that we can provide informed local market intelligence to support people planning, business growth strategy, employer branding and salary benchmarking. Our average length of service for consultants is 8 years.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Single Company History End -->
                </div>

            </div>
            <div class="action">
                <a href="#" data-slide="1">2010</a>
                <a href="#" data-slide="2">2020</a>
                <a href="#" data-slide="3">2021</a>
                <a href="#" data-slide="4">2022</a>
                <a href="#" data-slide="5">2023</a>
                <a href="#" data-slide="6">2024</a>
                <a class="mr-0" href="#" data-slide="7">2025</a>
            </div>
        </div>
    </div>
    <!-- Company History Section Start -->

    <!-- Video Section Start -->
    <div class="video-section section bg_image--4 pt-120 pt-lg-100 pt-md-80 pt-sm-60 pt-xs-50 pb-120 pb-lg-100 pb-md-80 pb-sm-60 pb-xs-50">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="about-video-popup">
                        <h4 class="sub-title">Let's See How We Works</h4>
                        <h2 class="title">We help you to find your next job and advance your career today</h2>
                        <div class="video-popup-content d-flex justify-content-center">
                            <a class="link-video venobox d-flex" data-autoplay="true" data-vbtype="video" href="https://www.youtube.com/watch?v=9No-FiEInLA&amp;t=1s">
                                    <span class="icon d-flex align-items-center justify-content-center flex-grow-1">
                            <i class="fa fa-play" aria-hidden="true"></i>
                            </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Video Section Start -->

    <!-- Team Section Start -->
    <div class="team-section section pt-120 pt-lg-100 pt-md-80 pt-sm-60 pt-xs-50 pb-120 pb-lg-100 pb-md-80 pb-sm-60 pb-xs-50">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title-four mb-40">
                        <h3>Leadership Team</h3>
                        <p>As a former HR professional, our founder Aaron Matos views talent & recruiting as the most important part of a business. Because building the right team of people can be the difference between an organization winning or losing. All of our tools at Recruiting.com are built through this unending passion to help our clients recruit the right candidates.</p>
                    </div>
                </div>
            </div>
            <div class="row g-0 border-top-left">

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <!-- Single Team Start -->
                    <div class="single-team team-style-two">
                        <div class="team-image">
                            <img src="assets/images/team/ab-team1.jpg" alt="">
                        </div>
                        <div class="team-content">
                            <h4 class="team-title">
                                <a href="#">Sidney Wilcox</a>
                            </h4>
                            <p class="team-headline">CEO/Founder</p>
                        </div>
                        <div class="item-box transition-theme">
                            <div class="team-info-social">
                                <ul class="social-link-hover">
                                    <li><a class="facebook" href="#"><i class="fab fa-facebook-square"></i></a></li>
                                    <li><a class="twitter" href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a class="google-plus" href="#"><i class="fab fa-google-plus-g"></i></a></li>
                                    <li><a class="youtube" href="#"><i class="fab fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Single Team End -->
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <!-- Single Team Start -->
                    <div class="single-team team-style-two">
                        <div class="team-image">
                            <img src="assets/images/team/ab-team2.jpg" alt="">
                        </div>
                        <div class="team-content">
                            <h4 class="team-title">
                                <a href="#">Nora Buchanan </a>
                            </h4>
                            <p class="team-headline">Financial Officer</p>
                        </div>
                        <div class="item-box transition-theme">
                            <div class="team-info-social">
                                <ul class="social-link-hover">
                                    <li><a class="facebook" href="#"><i class="fab fa-facebook-square"></i></a></li>
                                    <li><a class="twitter" href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a class="google-plus" href="#"><i class="fab fa-google-plus-g"></i></a></li>
                                    <li><a class="youtube" href="#"><i class="fab fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Single Team End -->
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <!-- Single Team Start -->
                    <div class="single-team team-style-two">
                        <div class="team-image">
                            <img src="assets/images/team/ab-team3.jpg" alt="">
                        </div>
                        <div class="team-content">
                            <h4 class="team-title">
                                <a href="#">Jenny Hobbs </a>
                            </h4>
                            <p class="team-headline">Compensation Analyst</p>
                        </div>
                        <div class="item-box transition-theme">
                            <div class="team-info-social">
                                <ul class="social-link-hover">
                                    <li><a class="facebook" href="#"><i class="fab fa-facebook-square"></i></a></li>
                                    <li><a class="twitter" href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a class="google-plus" href="#"><i class="fab fa-google-plus-g"></i></a></li>
                                    <li><a class="youtube" href="#"><i class="fab fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Single Team End -->
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <!-- Single Team Start -->
                    <div class="single-team team-style-two">
                        <div class="team-image">
                            <img src="assets/images/team/team4.jpg" alt="">
                        </div>
                        <div class="team-content">
                            <h4 class="team-title">
                                <a href="#">Daniel Nguyen </a>
                            </h4>
                            <p class="team-headline">Senior UI / UX Designer</p>
                        </div>
                        <div class="item-box transition-theme">
                            <div class="team-info-social">
                                <ul class="social-link-hover">
                                    <li><a class="facebook" href="#"><i class="fab fa-facebook-square"></i></a></li>
                                    <li><a class="twitter" href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a class="google-plus" href="#"><i class="fab fa-google-plus-g"></i></a></li>
                                    <li><a class="youtube" href="#"><i class="fab fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Single Team End -->
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <!-- Single Team Start -->
                    <div class="single-team team-style-two">
                        <div class="team-image">
                            <img src="assets/images/team/ab-team2.jpg" alt="">
                        </div>
                        <div class="team-content">
                            <h4 class="team-title">
                                <a href="#">Sidney Wilcox</a>
                            </h4>
                            <p class="team-headline">Financial Reporting Manager</p>
                        </div>
                        <div class="item-box transition-theme">
                            <div class="team-info-social">
                                <ul class="social-link-hover">
                                    <li><a class="facebook" href="#"><i class="fab fa-facebook-square"></i></a></li>
                                    <li><a class="twitter" href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a class="google-plus" href="#"><i class="fab fa-google-plus-g"></i></a></li>
                                    <li><a class="youtube" href="#"><i class="fab fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Single Team End -->
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <!-- Single Team Start -->
                    <div class="single-team team-style-two">
                        <div class="team-image">
                            <img src="assets/images/team/ab-team1.jpg" alt="">
                        </div>
                        <div class="team-content">
                            <h4 class="team-title">
                                <a href="#">Nora Buchanan</a>
                            </h4>
                            <p class="team-headline">Financial Officer</p>
                        </div>
                        <div class="item-box transition-theme">
                            <div class="team-info-social">
                                <ul class="social-link-hover">
                                    <li><a class="facebook" href="#"><i class="fab fa-facebook-square"></i></a></li>
                                    <li><a class="twitter" href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a class="google-plus" href="#"><i class="fab fa-google-plus-g"></i></a></li>
                                    <li><a class="youtube" href="#"><i class="fab fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Single Team End -->
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <!-- Single Team Start -->
                    <div class="single-team team-style-two">
                        <div class="team-image">
                            <img src="assets/images/team/ab-team3.jpg" alt="">
                        </div>
                        <div class="team-content">
                            <h4 class="team-title">
                                <a href="#">Jenny Hobbs</a>
                            </h4>
                            <p class="team-headline">Compensation Analyst</p>
                        </div>
                        <div class="item-box transition-theme">
                            <div class="team-info-social">
                                <ul class="social-link-hover">
                                    <li><a class="facebook" href="#"><i class="fab fa-facebook-square"></i></a></li>
                                    <li><a class="twitter" href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a class="google-plus" href="#"><i class="fab fa-google-plus-g"></i></a></li>
                                    <li><a class="youtube" href="#"><i class="fab fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Single Team End -->
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <!-- Single Team Start -->
                    <div class="single-team team-style-two">
                        <div class="team-image">
                            <img src="assets/images/team/ab-team5.jpg" alt="">
                        </div>
                        <div class="team-content">
                            <h4 class="team-title">
                                <a href="#">Pamela Hood</a>
                            </h4>
                            <p class="team-headline">HR Coordinator</p>
                        </div>
                        <div class="item-box transition-theme">
                            <div class="team-info-social">
                                <ul class="social-link-hover">
                                    <li><a class="facebook" href="#"><i class="fab fa-facebook-square"></i></a></li>
                                    <li><a class="twitter" href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a class="google-plus" href="#"><i class="fab fa-google-plus-g"></i></a></li>
                                    <li><a class="youtube" href="#"><i class="fab fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Single Team End -->
                </div>

            </div>
        </div>
    </div>
    <!-- Team Section End -->

    <!-- Sponsors Section Start -->
    <div class="sponsors-section section bg-image-proparty bg_image--2 pt-120 pt-lg-100 pt-md-80 pt-sm-60 pt-xs-50 pb-120 pb-lg-100 pb-md-80 pb-sm-60 pb-xs-50">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title-four mb-40">
                        <h3>Media & Sponsors</h3>
                        <p>The National Online Recruitment Awards are entirely dedicated to the continual improvement and evolution of the Candidate-Experience. Throughout the past 19 years we have seen the focus of progressive employers, recruitment firms and job boards change to recognise that.</p>
                    </div>
                </div>
            </div>
            <div class="sponsors-slider row">

                <div class="col-12">
                    <!-- Single Company History Start -->
                    <div class="single-sponsors">
                        <div class="item-comment">
                            <p>"I love Jopota Recruitment. I STRONGLY recommend Jopota Recruitment to EVERYONE interested in running a successful online business! I am completely blown away.</p>
                        </div>
                        <div class="item-meta">
                            <p class="text">
                                <span class="name">Celestine F. </span>
                                <span class="company">Fotex Agency </span>
                            </p>
                        </div>
                    </div>
                    <!-- Single Company History End -->
                </div>

                <div class="col-12">
                    <!-- Single Company History Start -->
                    <div class="single-sponsors">
                        <div class="item-comment">
                            <p>"Wow what great service, I love it! Jopota Recruitment is both attractive and highly adaptable. Not able to tell you how happy I am with Jopota Recruitment."</p>
                        </div>
                        <div class="item-meta">
                            <p class="text">
                                <span class="name">Tamarah Z. </span>
                                <span class="company">Royal Group </span>
                            </p>
                        </div>
                    </div>
                    <!-- Single Company History End -->
                </div>

                <div class="col-12">
                    <!-- Single Company History Start -->
                    <div class="single-sponsors">
                        <div class="item-comment">
                            <p>"Jopota IT Recruitment has got everything I need. The best on the net! I don't know what else to say. I would like to personally thank you for your outstanding product. They are all trully awesome for us to say that."</p>
                        </div>
                        <div class="item-meta">
                            <p class="text">
                                <span class="name">Elicia H.</span>
                                <span class="company">iquid Design </span>
                            </p>
                        </div>
                    </div>
                    <!-- Single Company History End -->
                </div>

            </div>
        </div>
    </div>
    <!-- Sponsors Section Start -->

@stop



@section('footer')

@stop
