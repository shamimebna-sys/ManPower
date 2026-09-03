@extends('layout')

@section('title', 'Home')


@section('header')

@stop


@section('content')

    <!--slider section start-->
    <div class="hero-section section position-relative">
        <!--Hero Item start-->
        <div class="hero-item bg_image--1">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">

                        <!--Hero Content start-->
                        <div class="hero-content-2 left">
                            <h2 class="title">Top Recruiting Website</h2>
                            <h3 class="sub-title">For Professionals</h3>
                            <p>Jobs & Job search. Find jobs in global. Executive jobs & work. Employment</p>

                            <div class="job-search-wrap mt-90 mt-md-70 mt-sm-50 mt-xs-30">
                                <div class="job-search-form">
                                    <form action="#">
                                        <div class="row row-5">
                                            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                                                <!-- Single Field Item Start  -->
                                                <div class="single-field-item">
                                                    <i class="lnr lnr-magnifier"></i>
                                                    <input placeholder="What jobs you want?" name="keyword" type="text">
                                                </div>
                                                <!-- Single Field Item End  -->
                                            </div>
                                            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                                <!-- Single Field Item Start  -->
                                                <div class="single-field-item">
                                                    <i class="lnr lnr-list"></i>
                                                    <select class="nice-select wide">
                                                        <option value="1">All categories</option>
                                                        <option value="2">Accounting</option>
                                                        <option value="3">Broadcasting</option>
                                                        <option value="4">Customer Service</option>
                                                        <option value="5">Digital Marketing</option>
                                                        <option value="6">Finance & Accounting</option>
                                                        <option value="7">Game Mobile</option>
                                                        <option value="8">Graphics & Design</option>
                                                        <option value="9">Human Resources</option>
                                                        <option value="10">Medical Doctor</option>
                                                        <option value="11">Restaurant</option>
                                                        <option value="12">Sale & Marketing</option>
                                                        <option value="13">Sale Assistance</option>
                                                        <option value="14">Science & Analitycs</option>
                                                        <option value="15">Teachers</option>
                                                        <option value="16">Web & Software Dev</option>
                                                        <option value="17">Writing & Translations</option>
                                                    </select>
                                                </div>
                                                <!-- Single Field Item End  -->
                                            </div>
                                            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                                <!-- Single Field Item Start  -->
                                                <div class="single-field-item">
                                                    <i class="lnr lnr-map-marker"></i>
                                                    <input class="input-field input-field-location" placeholder="Location" name="location" type="text">
                                                    <span class="btn-action-location">
                                                        <i class="far fa-dot-circle"></i>
                                                    </span>
                                                </div>
                                                <!-- Single Field Item End  -->
                                            </div>
                                            <div class="col-lg-2 col-md-6 col-sm-6 col-12">
                                                <div class="submit-btn">
                                                    <button class="ht-btn" type="submit"> Search</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="trending-keywords">
                                    <div class="keywords">
                                        <span class="title">Trending Keywords</span>
                                        <ul>
                                            <li><a href="#">Account Manager</a></li>
                                            <li><a href="#">Administrative</a></li>
                                            <li><a href="#">Android</a></li>
                                            <li><a href="#">Angular</a></li>
                                            <li><a href="#">app</a></li>
                                            <li><a href="#">ASP.NET</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!--Hero Content end-->

                    </div>
                </div>
            </div>
        </div>
        <!--Hero Item end-->
    </div>
    <!--slider section end-->

    <!-- Feature Section Start -->
    <div class="feature-section section bg-image-proparty bg_image--2 pt-40 pb-10">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <!-- Single Feature Start -->
                    <div class="single-feature mb-30">
                        <div class="feature-icon">
                            <i class="far fa-address-card"></i>
                        </div>
                        <div class="feature-content">
                            <h3 class="title">Complete Resume</h3>
                            <p>Create resume free & get a job</p>
                        </div>
                    </div>
                    <!-- Single Feature End -->
                </div>
                <div class="col-lg-4">
                    <!-- Single Feature Start -->
                    <div class="single-feature mb-30">
                        <div class="feature-icon">
                            <i class="far fa-file-alt"></i>
                        </div>
                        <div class="feature-content">
                            <h3 class="title">Post Job Free</h3>
                            <p>Approach a top million resumes</p>
                        </div>
                    </div>
                    <!-- Single Feature End -->
                </div>
                <div class="col-lg-4">
                    <!-- Single Feature Start -->
                    <div class="single-feature mb-30">
                        <div class="feature-icon">
                            <i class="fa fa-search-location"></i>
                        </div>
                        <div class="feature-content">
                            <h3 class="title">Find Work</h3>
                            <p>Get the best jobs in your area</p>
                        </div>
                    </div>
                    <!-- Single Feature End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Feature Section End -->

    <!-- Featured Employer Start -->
    <div class="featured-employer section pt-115 pt-lg-95 pt-md-75 pt-sm-55 pt-xs-45">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-wrap mb-45">
                        <div class="section-title">
                            <span>JOB FROM EMPLOYERS</span>
                            <h3 class="title">Companies Spotlight</h3>
                        </div>
                        <div class="jetapo-link">
                            <a href="#">Browse All Employers <i class="lnr lnr-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row row-five-column g-0 border-top-left">
                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Employer Item Start -->
                    <div class="employer-item item-border-bottom">
                        <span class="featured-employer-label">Featured</span>
                        <div class="employer-image">
                            <img src="assets/images/companies_logo/logo-1.jpg" alt="">
                        </div>
                        <div class="employer-info-top">
                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  Beijing, Beijing</span>
                            <h3 class="employer-name"><a href="employer-details.html">Digital Asset</a></h3>
                        </div>
                    </div>
                    <!-- Single Employer Item End -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Employer Item Start -->
                    <div class="employer-item item-border-bottom">
                        <div class="employer-image">
                            <img src="assets/images/companies_logo/logo-2.jpg" alt="">
                        </div>
                        <div class="employer-info-top">
                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  Victoria</span>
                            <h3 class="employer-name"><a href="employer-details.html">Liquididea Design</a></h3>
                        </div>
                    </div>
                    <!-- Single Employer Item End -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Employer Item Start -->
                    <div class="employer-item item-border-bottom">
                        <span class="featured-employer-label">Featured</span>
                        <div class="rating">
                            <span class="rating-point">4.3</span>
                        </div>
                        <div class="employer-image">
                            <img src="assets/images/companies_logo/logo-3.jpg" alt="">
                        </div>
                        <div class="employer-info-top">
                            <span class="employer-location"><i class="lnr lnr-map-marker"></i> Osaka, Osaka</span>
                            <h3 class="employer-name"><a href="employer-details.html">Shippo Company</a></h3>
                        </div>
                    </div>
                    <!-- Single Employer Item End -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Employer Item Start -->
                    <div class="employer-item item-border-bottom">
                        <span class="featured-employer-label">Featured</span>
                        <div class="rating">
                            <span class="rating-point">1.7</span>
                        </div>
                        <div class="employer-image">
                            <img src="assets/images/companies_logo/logo-4.jpg" alt="">
                        </div>
                        <div class="employer-info-top">
                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  Chicago, California</span>
                            <h3 class="employer-name"><a href="employer-details.html">Alpha Investing <i class="fas fa-check-circle"></i></a></h3>
                        </div>
                    </div>
                    <!-- Single Employer Item End -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Employer Item Start -->
                    <div class="employer-item item-border-bottom">
                        <span class="featured-employer-label">Featured</span>
                        <div class="rating">
                            <span class="rating-point">5.0</span>
                        </div>
                        <div class="employer-image">
                            <img src="assets/images/companies_logo/logo-5.jpg" alt="">
                        </div>
                        <div class="employer-info-top">
                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  New York, New York</span>
                            <h3 class="employer-name"><a href="employer-details.html">Radio Game</a></h3>
                        </div>
                    </div>
                    <!-- Single Employer Item End -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Employer Item Start -->
                    <div class="employer-item item-border-bottom">
                        <div class="rating">
                            <span class="rating-point">4.0</span>
                        </div>
                        <div class="employer-image">
                            <img src="assets/images/companies_logo/logo-6.jpg" alt="">
                        </div>
                        <div class="employer-info-top">
                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>Seville, Andalusia</span>
                            <h3 class="employer-name"><a href="employer-details.html">Digital Vine <i class="fas fa-check-circle"></i></a></h3>
                        </div>
                    </div>
                    <!-- Single Employer Item End -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Employer Item Start -->
                    <div class="employer-item item-border-bottom">
                        <span class="featured-employer-label">Featured</span>
                        <div class="rating">
                            <span class="rating-point">5.0</span>
                        </div>
                        <div class="employer-image">
                            <img src="assets/images/companies_logo/logo-7.jpg" alt="">
                        </div>
                        <div class="employer-info-top">
                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  London, England</span>
                            <h3 class="employer-name"><a href="employer-details.html">Vsmarttech</a></h3>
                        </div>
                    </div>
                    <!-- Single Employer Item End -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Employer Item Start -->
                    <div class="employer-item item-border-bottom">
                        <div class="rating">
                            <span class="rating-point">3.9</span>
                        </div>
                        <div class="employer-image">
                            <img src="assets/images/companies_logo/logo-8.jpg" alt="">
                        </div>
                        <div class="employer-info-top">
                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  Hamburg, Hamburg</span>
                            <h3 class="employer-name"><a href="employer-details.html">BowThemes</a></h3>
                        </div>
                    </div>
                    <!-- Single Employer Item End -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Employer Item Start -->
                    <div class="employer-item item-border-bottom">
                        <span class="featured-employer-label">Featured</span>
                        <div class="rating">
                            <span class="rating-point">5.0</span>
                        </div>
                        <div class="employer-image">
                            <img src="assets/images/companies_logo/logo-9.jpg" alt="">
                        </div>
                        <div class="employer-info-top">
                            <span class="employer-location"><i class="lnr lnr-map-marker"></i> Dhaka, Bangladesh</span>
                            <h3 class="employer-name"><a href="employer-details.html">HasThemes</a></h3>
                        </div>
                    </div>
                    <!-- Single Employer Item End -->
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Employer Item Start -->
                    <div class="employer-item item-border-bottom">
                        <div class="rating">
                            <span class="rating-point">4.9</span>
                        </div>
                        <div class="employer-image">
                            <img src="assets/images/companies_logo/logo-10.jpg" alt="">
                        </div>
                        <div class="employer-info-top">
                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  Hanoi, Hà Nội</span>
                            <h3 class="employer-name"><a href="employer-details.html">HasTech</a></h3>
                        </div>
                    </div>
                    <!-- Single Employer Item End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Featured Employer End -->

    <!-- Job Location Section Start -->
    <div class="job-location section pt-110 pt-lg-95 pt-md-75 pt-sm-55 pt-xs-45 pb-120 pb-lg-100 pb-md-80 pb-sm-60 pb-xs-50">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-wrap mb-45">
                        <div class="section-title">
                            <span>JOB BY LOCATIONS</span>
                            <h3 class="title">Top Places to Work</h3>
                        </div>
                        <div class="jetapo-link">
                            <a href="#">Browse All Locations <i class="lnr lnr-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="job-location-slider">

                        <div class="col-lg-3">
                            <!-- Single Job Location Start -->
                            <div class="single-job-location bg-image" data-bg="assets/images/location/petaling.jpg">
                                <div class="overlay-gradient"></div>
                                <div class="location-info">
                                    <a class="city-name" href="job-with-map.html">Chicago</a>
                                    <span class="count-job">2 Open Jobs</span>
                                </div>
                            </div>
                            <!-- Single Job Location End -->
                        </div>

                        <div class="col-lg-3">
                            <!-- Single Job Location Start -->
                            <div class="single-job-location bg-image" data-bg="assets/images/location/jacksonville.jpg">
                                <div class="overlay-gradient"></div>
                                <div class="location-info">
                                    <a class="city-name" href="job-with-map.html">New York</a>
                                    <span class="count-job">1 Open Jobs</span>
                                </div>
                            </div>
                            <!-- Single Job Location End -->
                        </div>

                        <div class="col-lg-3">
                            <!-- Single Job Location Start -->
                            <div class="single-job-location bg-image" data-bg="assets/images/location/houston.jpg">
                                <div class="overlay-gradient"></div>
                                <div class="location-info">
                                    <a class="city-name" href="job-with-map.html">Da Nang</a>
                                    <span class="count-job">1 Open Jobs</span>
                                </div>
                            </div>
                            <!-- Single Job Location End -->
                        </div>

                        <div class="col-lg-3">
                            <!-- Single Job Location Start -->
                            <div class="single-job-location bg-image" data-bg="assets/images/location/Seville.jpg">
                                <div class="overlay-gradient"></div>
                                <div class="location-info">
                                    <a class="city-name" href="job-with-map.html">Seville</a>
                                    <span class="count-job">1 Open Jobs</span>
                                </div>
                            </div>
                            <!-- Single Job Location End -->
                        </div>

                        <div class="col-lg-3">
                            <!-- Single Job Location Start -->
                            <div class="single-job-location bg-image" data-bg="assets/images/location/india.jpg">
                                <div class="overlay-gradient"></div>
                                <div class="location-info">
                                    <a class="city-name" href="job-with-map.html">Hanoi</a>
                                    <span class="count-job">1 Open Jobs</span>
                                </div>
                            </div>
                            <!-- Single Job Location End -->
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Job Location Section End -->

    <!-- Job Section Start -->
    <div class="job-section section bg-image-proparty bg_image--2 pt-115 pt-lg-95 pt-md-75 pt-sm-55 pt-xs-45 pb-120 pb-lg-100 pb-md-80 pb-sm-60 pb-xs-50">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-wrap mb-45">
                        <div class="section-title">
                            <span>SEASON FEATURE JOBS</span>
                            <h3 class="title">Job Will You Love</h3>
                        </div>
                        <div class="jetapo-tab-menu">
                            <ul class="nav">
                                <li><a class="active show" data-bs-toggle="tab" href="#fullTime"> Full Time </a></li>
                                <li><a data-bs-toggle="tab" href="#partTime"> Part Time </a></li>
                                <li><a data-bs-toggle="tab" href="#remote"> Remote </a></li>
                                <li><a data-bs-toggle="tab" href="#freelancer"> Freelancer </a></li>
                                <li><a data-bs-toggle="tab" href="#internship"> Internship </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="tab-content">
                        <div id="fullTime" class="tab-pane fade show active">
                            <div class="row">

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo1.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Chief Accountant</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Shippo Company</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$700 - $900</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">Ho Chi Minh City</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Nov 7, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo3.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Senior Data Engineer</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Radio Game</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$900 - $1,200</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">Houston</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo4.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Construction Worker</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Digital Vine</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$1,000 - $1,500</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">New York</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo2.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Tax Manager</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Vsmarttech</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$1,500 - $2,000</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">Los Angeles</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo5.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Receptionist</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Digital Vine</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$900 - $1,200</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">New York</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo1.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Jr. Developer Shopify</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">InwaveThemes</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$1,000 - $1,500</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">New York</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                            </div>
                        </div>
                        <div id="partTime" class="tab-pane fade">
                            <div class="row">

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo4.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Construction Worker</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Digital Vine</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$1,000 - $1,500</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">New York</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo2.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Tax Manager</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Vsmarttech</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$1,500 - $2,000</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">Los Angeles</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo5.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Receptionist</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Digital Vine</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$900 - $1,200</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">New York</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo1.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Jr. Developer Shopify</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">InwaveThemes</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$1,000 - $1,500</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">New York</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                            </div>
                        </div>
                        <div id="remote" class="tab-pane fade">
                            <div class="row">

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo3.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Senior Data Engineer</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Radio Game</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$900 - $1,200</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">Houston</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="#">
                                                    <img src="assets/images/companies_logo/logo-big/logo4.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Construction Worker</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Digital Vine</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$1,000 - $1,500</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">New York</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo5.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Receptionist</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Digital Vine</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$900 - $1,200</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">New York</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                            </div>
                        </div>
                        <div id="freelancer" class="tab-pane fade">
                            <div class="row">

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo1.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Jr. Developer Shopify</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">InwaveThemes</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$1,000 - $1,500</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">New York</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                            </div>
                        </div>
                        <div id="internship" class="tab-pane fade">
                            <div class="row">

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo4.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Construction Worker</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Digital Vine</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$1,000 - $1,500</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">New York</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-6 mb-30">
                                    <!-- Single Job Start  -->
                                    <div class="single-job">
                                        <div class="info-top">
                                            <div class="job-image">
                                                <a href="job-details.html">
                                                    <img src="assets/images/companies_logo/logo-big/logo2.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="job-info">
                                                <div class="job-info-inner">
                                                    <div class="job-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label">featured</span>
                                                            <a class="save-job ml-20" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="job-title">
                                                                <a href="job-details.html">Tax Manager</a>
                                                            </h3>
                                                            <div class="employer-name">
                                                                <a href="employer-details.html">Vsmarttech</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="job-salary">$1,500 - $2,000</span>
                                                    <div class="job-meta">
                                                        <div class="job-location"><i class="lnr lnr-map-marker"></i><a href="#">Los Angeles</a></div>

                                                        <div class="job-type"><i class="lnr lnr-briefcase"></i><a class="def-color" href="#">Full Time</a></div>

                                                        <div class="job-date"><i class="lnr lnr-clock"></i>Sep 12, 2022</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="all-link-button text-center mt-15">
                        <a class="ht-btn lg-btn" href="#">Browse All Featured Jobs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Job Section End -->

    <!-- Blog Section Start  -->
    <div class="blog-section section pt-115 pt-lg-95 pt-md-75 pt-sm-55 pt-xs-45 pb-90 pb-lg-70 pb-md-50 pb-sm-30 pb-xs-20">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-wrap mb-45">
                        <div class="section-title">
                            <span> FROM OUR BLOG</span>
                            <h3 class="title"> Latest News & Post</h3>
                        </div>
                        <div class="jetapo-link">
                            <a href="#"> Browse All Articles <i class="lnr lnr-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="col-lg-4 col-md-6 mb-30">
                    <!-- Single Blog Start -->
                    <div class="single-blog">
                        <div class="blog-image">
                            <a href="#">
                                <img src="assets/images/blog/blog1.jpg" alt="">
                            </a>
                            <div class="blog-cat">
                                <a href="#" rel="category tag">Job Skills</a>
                            </div>
                        </div>
                        <div class="blog-content">
                            <h4 class="title">
                                <a href="#">The Reason Why Software Developer Repeats As ‘Best Job’</a>
                            </h4>
                            <div class="blog-meta">
                                <p class="blog-author">
                                    <i class="lnr lnr-user"></i>
                                    <span class="text">Posted:</span>
                                    <span class="author">Hien Tran</span>
                                </p>
                                <p class="blog-date-post">
                                    <i class="lnr lnr-clock"></i>Oct 24, 2022
                                </p>
                            </div>
                            <p class="blog-desc">
                                The news comes just after "full stack software developer" was named the
                            </p>
                            <a href="#" class="read-more">Read More <i class="lnr lnr-chevron-right"></i></a>
                        </div>
                    </div>
                    <!-- Single Blog End -->
                </div>

                <div class="col-lg-4 col-md-6 mb-30">
                    <!-- Single Blog Start -->
                    <div class="single-blog">
                        <div class="blog-image">
                            <a href="#">
                                <img src="assets/images/blog/blog2.jpg" alt="">
                            </a>
                            <div class="blog-cat">
                                <a href="#" rel="category tag">News & Update</a>
                            </div>
                        </div>
                        <div class="blog-content">
                            <h4 class="title">
                                <a href="#">7 Answers to the Most Frequently Asked Questions About Job</a>
                            </h4>
                            <div class="blog-meta">
                                <p class="blog-author">
                                    <i class="lnr lnr-user"></i>
                                    <span class="text">Posted:</span>
                                    <span class="author">Hien Tran</span>
                                </p>
                                <p class="blog-date-post">
                                    <i class="lnr lnr-clock"></i>Oct 24, 2022
                                </p>
                            </div>
                            <p class="blog-desc">
                                The news comes just after "full stack software developer" was named the
                            </p>
                            <a href="#" class="read-more">Read More <i class="lnr lnr-chevron-right"></i></a>
                        </div>
                    </div>
                    <!-- Single Blog End -->
                </div>

                <div class="col-lg-4 col-md-6 mb-30">
                    <!-- Single Blog Start -->
                    <div class="single-blog">
                        <div class="blog-image">
                            <a href="#">
                                <img src="assets/images/blog/blog3.jpg" alt="">
                            </a>
                            <div class="blog-cat">
                                <a href="#" rel="category tag">Career Advice</a>
                            </div>
                        </div>
                        <div class="blog-content">
                            <h4 class="title">
                                <a href="#">The Question Everyone Working in Job Should Know to Answer</a>
                            </h4>
                            <div class="blog-meta">
                                <p class="blog-author">
                                    <i class="lnr lnr-user"></i>
                                    <span class="text">Posted:</span>
                                    <span class="author">Hien Tran</span>
                                </p>
                                <p class="blog-date-post">
                                    <i class="lnr lnr-clock"></i>Oct 24, 2022
                                </p>
                            </div>
                            <p class="blog-desc">
                                The news comes just after "full stack software developer" was named the
                            </p>
                            <a href="#" class="read-more">Read More <i class="lnr lnr-chevron-right"></i></a>
                        </div>
                    </div>
                    <!-- Single Blog End -->
                </div>

            </div>
        </div>
    </div>
    <!-- Blog Section End -->

    <!-- Testimonial Section Start -->
    <div class="testimonial-section section bg-image-proparty bg_image--2 pt-115 pt-lg-95 pt-md-75 pt-sm-55 pt-xs-45 pb-180 pb-lg-160 pb-md-140 pb-sm-60 pb-xs-30">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-wrap mb-45">
                        <div class="section-title">
                            <span> OUR HAPPY CLIENTS</span>
                            <h3 class="title"> From Testimonials</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial-slider">

                <div class="col-lg-6">
                    <!-- Single Testimonial Start -->
                    <div class="single-testimonial">
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">
                                <img src="assets/images/testimonial/testimonial-1.png" alt="">
                            </div>
                            <div class="testimonial-meta">
                                <h5 class="name">Kilian Sanjeev</h5>
                                <p class="text">
                                    <span class="position">CEO at</span>
                                    <span class="company theme-color">Alpha Investing</span>
                                </p>
                            </div>
                            <span class="icon-quote theme-color">
                                <i class="fas fa-quote-right"></i>
                            </span>
                        </div>
                        <div class="testimonial-comment">
                            <p>"I am so pleased with this product. Jopota Recruitment has completely surpassed our expectations. If you aren't sure, always go for Jopota Recruitment. Jopota Recruitment is the real deal!"</p>
                        </div>
                    </div>
                    <!-- Single Testimonial End -->
                </div>

                <div class="col-lg-6">
                    <!-- Single Testimonial Start -->
                    <div class="single-testimonial">
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">
                                <img src="assets/images/testimonial/testimonial-2.png" alt="">
                            </div>
                            <div class="testimonial-meta">
                                <h5 class="name">Arina Sebastian</h5>
                                <p class="text">
                                    <span class="position">Freelance Designer</span>
                                </p>
                            </div>
                            <span class="icon-quote theme-color">
                                <i class="fas fa-quote-right"></i>
                            </span>
                        </div>
                        <div class="testimonial-comment">
                            <p>"I am so pleased with this product. Jopota Recruitment has completely surpassed our expectations. If you aren't sure, always go for Jopota Recruitment. Jopota Recruitment is the real deal!"</p>
                        </div>
                    </div>
                    <!-- Single Testimonial End -->
                </div>

                <div class="col-lg-6">
                    <!-- Single Testimonial Start -->
                    <div class="single-testimonial">
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">
                                <img src="assets/images/testimonial/testimonial-3.jpg" alt="">
                            </div>
                            <div class="testimonial-meta">
                                <h5 class="name">Kilian Sanjeev</h5>
                                <p class="text">
                                    <span class="position">Freelance Designer at</span>
                                    <span class="company theme-color">Alpha</span>
                                </p>
                            </div>
                            <span class="icon-quote theme-color">
                                <i class="fas fa-quote-right"></i>
                            </span>
                        </div>
                        <div class="testimonial-comment">
                            <p>"I am so pleased with this product. Jopota Recruitment has completely surpassed our expectations. If you aren't sure, always go for Jopota Recruitment. Jopota Recruitment is the real deal!"</p>
                        </div>
                    </div>
                    <!-- Single Testimonial End -->
                </div>

            </div>
        </div>
    </div>
    <!-- Testimonial Section End -->

    <!-- Funfact Section Start  -->
    <div class="funfact-section section mt--60 pb-50 pb-sm-0 pb-xs-0">
        <div class="container">
            <div class="row g-0 border-top-left">

                <div class="col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Funfact Start -->
                    <div class="single-funfact">
                        <div class="icon-img">
                            <img src="assets/images/icons/candidates.png" alt="">
                        </div>
                        <div class="funfact-content">
                            <span class="counter">87,360</span>
                            <span class="text theme-color">Candidates</span>
                        </div>
                    </div>
                    <!-- Single Funfact End -->
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Funfact Start -->
                    <div class="single-funfact">
                        <div class="icon-img">
                            <img src="assets/images/icons/total-jobs.png" alt="">
                        </div>
                        <div class="funfact-content">
                            <span class="counter">20,258</span>
                            <span class="text theme-color">Total Jobs</span>
                        </div>
                    </div>
                    <!-- Single Funfact End -->
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Funfact Start -->
                    <div class="single-funfact">
                        <div class="icon-img">
                            <img src="assets/images/icons/employers.png" alt="">
                        </div>
                        <div class="funfact-content">
                            <span class="counter">8,650</span>
                            <span class="text theme-color">Employers</span>
                        </div>
                    </div>
                    <!-- Single Funfact End -->
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6">
                    <!-- Single Funfact Start -->
                    <div class="single-funfact">
                        <div class="icon-img">
                            <img src="assets/images/icons/job-applications.png" alt="">
                        </div>
                        <div class="funfact-content">
                            <span class="counter">50,299</span>
                            <span class="text theme-color">job applications</span>
                        </div>
                    </div>
                    <!-- Single Funfact End -->
                </div>

            </div>
        </div>
    </div>
    <!-- Funfact Section Start  -->

    <!-- CTA Section Start  -->
    <div class="cta-section section bg_image--3 height-100vh pt-50 pt-xs-40">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="cta-wrap mb-45">
                        <div class="cta-info">
                            <span> Getting started to work</span>
                            <h3 class="title"> Don’t just find. Be found. Put your <br> CV in front of great employers</h3>
                            <p>It helps you to increase your chances of finding a suitable job and let recruiters <br> contact you about jobs that are not needed to pay for advertising.</p>
                            <a class="ht-btn lg-btn" href="#"><i class="lnr lnr-cloud-upload"></i>Upload Your Resume</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CTA Section End  -->

    <!-- Brand Section Start  -->
    <div class="brand-section section bg_color--3 pt-40 pb-40">
        <div class="container">
            <div class="row row-five-column align-items-center">

                <div class="col-xl-2 col-20">
                    <div class="brand-heading">
                        <h3 class="title">We are <br> trusted by</h3>
                    </div>
                </div>

                <div class="col-xl-2 col-20">
                    <!-- Single Brand Start  -->
                    <div class="single-brand">
                        <a href="#"><img src="assets/images/brand/brand-1.png" alt=""></a>
                    </div>
                    <!-- Single Brand End -->
                </div>

                <div class="col-xl-2 col-20">
                    <!-- Single Brand Start  -->
                    <div class="single-brand">
                        <a href="#"><img src="assets/images/brand/brand-2.png" alt=""></a>
                    </div>
                    <!-- Single Brand End -->
                </div>

                <div class="col-xl-2 col-20">
                    <!-- Single Brand Start  -->
                    <div class="single-brand">
                        <a href="#"><img src="assets/images/brand/brand-3.png" alt=""></a>
                    </div>
                    <!-- Single Brand End -->
                </div>

                <div class="col-xl-2 col-20">
                    <!-- Single Brand Start  -->
                    <div class="single-brand">
                        <a href="#"><img src="assets/images/brand/brand-4.png" alt=""></a>
                    </div>
                    <!-- Single Brand End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Brand Section End  -->

@stop



@section('footer')

@stop
