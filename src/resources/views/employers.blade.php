@extends('layout')

@section('title', 'Employers')


@section('header')


@stop


@section('content')


    <!-- Breadcrumb Section Start -->
    <div class="breadcrumb-section section bg_color--5 pt-60 pt-sm-50 pt-xs-40 pb-60 pb-sm-50 pb-xs-40">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page-breadcrumb-content">
                        <ul class="page-breadcrumb">
                            <li><a href="{{url('/')}}">Home</a></li>
                            <li>Employers</li>
                        </ul>
                        <h1>Employers</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Section Start -->


    <!-- Employers Listing Section Start -->
    <div class="employers-listing-section section bg_color--5 pb-120 pb-lg-100 pb-md-75 pb-sm-60 pb-xs-50">
        <div class="container">
            <div class="row g-0">

                <div class="col-lg-4 order-lg-1 order-2 pr-55 pr-md-15 pr-sm-15 pr-xs-15">
                    <div class="sidebar-wrapper-two mt-sm-40 mt-xs-40">
                        <div class="common-sidebar-widget sidebar-two">
                            <h2 class="sidebar-title">Find A Candidate</h2>
                            <div class="sidebar-search-form-two">
                                <form action="#">
                                    <div class="input-group">
                                        <input type="text" name="search" placeholder="Search...">
                                        <i class="lnr lnr-magnifier"></i>
                                    </div>
                                    <button type="submit" class="ht-btn theme-btn theme-btn-two w-100">Search</button>
                                </form>
                            </div>
                        </div>
                        <div class="common-sidebar-widget sidebar-two">
                            <h2 class="sidebar-title">Search by first letter</h2>
                            <div class="sidebar-search-letter">
                                <ul class="search-by-first-letter">
                                    <li><a href="#">A</a></li>
                                    <li><a href="#">B</a></li>
                                    <li><a href="#">C</a></li>
                                    <li><a href="#">D</a></li>
                                    <li><a href="#">E</a></li>
                                    <li><a href="#">F</a></li>
                                    <li><a href="#">G</a></li>
                                    <li><a href="#">H</a></li>
                                    <li><a href="#">I</a></li>
                                    <li><a href="#">J</a></li>
                                    <li><a href="#">K</a></li>
                                    <li><a href="#">L</a></li>
                                    <li><a href="#">M</a></li>
                                    <li><a href="#">N</a></li>
                                    <li><a href="#">O</a></li>
                                    <li><a href="#">P</a></li>
                                    <li><a href="#">Q</a></li>
                                    <li><a href="#">R</a></li>
                                    <li><a href="#">S</a></li>
                                    <li><a href="#">T</a></li>
                                    <li><a href="#">U</a></li>
                                    <li><a href="#">V</a></li>
                                    <li><a href="#">W</a></li>
                                    <li><a href="#">X</a></li>
                                    <li><a href="#">Y</a></li>
                                    <li><a href="#">Z</a></li>
                                    <li><a href="#">0-9</a></li>
                                    <li><a href="#">#</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="common-sidebar-widget sidebar-two">
                            <h2 class="sidebar-title">Location</h2>
                            <div class="sidebar-location-form">
                                <form action="#">
                                    <div class="input-group">
                                        <input type="text" name="search" placeholder="Enter location">
                                        <i class="far fa-dot-circle"></i>
                                    </div>
                                </form>
                            </div>
                            <div class="sidebar-location-range mt-10">
                                <div class="location-range">
                                    <label for="amount">Radius:</label>
                                    <input type="text" id="amount" readonly>
                                </div>
                                <div id="slider-range-min"></div>
                            </div>
                        </div>
                        <div class="common-sidebar-widget sidebar-two">
                            <h2 class="sidebar-title">Category</h2>
                            <div class="sidebar-category">
                                <select class="nice-select wide">
                                    <option value="1">Choose Category</option>
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
                        </div>
                        <div class="common-sidebar-widget sidebar-two">
                            <h2 class="sidebar-title">Rating</h2>
                            <ul class="sidebar-cbx-list">
                                <li>
                                    <div class="filter-name-item">
                                        <input type="checkbox" name="expericence" id="filter-cbx">
                                        <label for="filter-cbx"> 5 Star</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="filter-name-item">
                                        <input type="checkbox" name="expericence" id="filter-cbx-two">
                                        <label for="filter-cbx-two"> 4 Star</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="filter-name-item">
                                        <input type="checkbox" name="expericence" id="filter-cbx-three">
                                        <label for="filter-cbx-three">3 Star</label>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="common-sidebar-widget sidebar-two">
                            <h2 class="sidebar-title">Skills</h2>
                            <ul class="sidebar-tag">
                                <li><a href="#">Account Manager</a></li>
                                <li><a href="#">Administrative</a></li>
                                <li><a href="#">Android</a></li>
                                <li><a href="#">Angular</a></li>
                                <li><a href="#">app</a></li>
                                <li><a href="#">ASP.NET</a></li>
                                <li><a href="#">Automotive</a></li>
                                <li><a href="#">Banner</a></li>
                            </ul>
                        </div>
                        <div class="common-sidebar-widget sidebar-two">
                            <div class="sidbar-image">
                                <a href="#">
                                    <img src="assets/images/banner/ads-three.jpg" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 order-lg-2 order-1">
                    <div class="filter-form">
                        <div class="result-sorting">
                            <div class="total-result">
                                <span class="total">10</span>
                                Results Found
                            </div>
                            <div class="form-left">
                                <div class="sort-by">
                                    <form action="#">
                                        <label class="text-sortby">Sort by:</label>
                                        <select class="nice-select">
                                            <option value="1">Title</option>
                                            <option value="2">Date</option>
                                            <option value="3">Salary</option>
                                        </select>
                                    </form>
                                </div>
                                <div class="layout-switcher">
                                    <ul class="nav">
                                        <li><a class="active show" data-bs-toggle="tab" href="#list"><i class="fa fa-list"></i></a></li>
                                        <li><a data-bs-toggle="tab" href="#grid"><i class="fa fa-th"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div id="list" class="tab-pane fade show active">
                            <div class="row">

                                <div class="col-lg-12 mb-20">
                                    <!-- Single Job Start  -->
                                    <div class="single-employer-list style-two">
                                        <div class="info-top align-items-start">
                                            <div class="employer-image">
                                                <a href="employer-details.html">
                                                    <img src="assets/images/companies_logo/logo-100/logo1.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="employer-info">
                                                <div class="employer-info-inner">
                                                    <div class="employer-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label mr-20">featured</span>
                                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="employer-title">
                                                                <a href="employer-details.html">Digital Asset</a>
                                                            </h3>
                                                            <div class="employer-rate">
                                                                <div class="star">
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                </div>
                                                                <span class="total">1 Ratings </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="employer-meta">
                                                        <div class="field-map"><i class="lnr lnr-map-marker"></i>Hanoi, Hanoi</div>
                                                    </div>
                                                    <div class="field-description">
                                                        <p>The leader in the marketing of commercial industry by providing enhanced services, relationship and profitability.…</p>
                                                    </div>
                                                    <div class="employer-bottom">
                                                        <div class="employer-skill-tag">
                                                            <a href="#">Android</a>
                                                            <a href="#">app</a>
                                                            <a href="#">ReactJs</a>
                                                            <a href="#">Ruby</a>
                                                        </div>
                                                        <div class="openjobs">
                                                            <a href="#">1 Open Job </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-12 mb-20">
                                    <!-- Single Job Start  -->
                                    <div class="single-employer-list style-two">
                                        <div class="info-top align-items-start">
                                            <div class="employer-image">
                                                <a href="employer-details.html">
                                                    <img src="assets/images/companies_logo/logo-100/logo3.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="employer-info">
                                                <div class="employer-info-inner">
                                                    <div class="employer-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label mr-20">featured</span>
                                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="employer-title">
                                                                <a href="employer-details.html">Radio Game</a>
                                                            </h3>
                                                            <div class="employer-rate">
                                                                <div class="star">
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                </div>
                                                                <span class="total">1 Ratings </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="employer-meta">
                                                        <div class="field-companysize">
                                                            <i class="lnr lnr-users"></i>
                                                            <span class="label">Size</span>40-200
                                                        </div>
                                                        <div class="field-map"><i class="lnr lnr-map-marker"></i>Chicago, Illinois</div>
                                                    </div>
                                                    <div class="field-description">
                                                        <p>The leader in the marketing of commercial industry by providing enhanced services, relationship and profitability.…</p>
                                                    </div>
                                                    <div class="employer-bottom">
                                                        <div class="employer-skill-tag">
                                                            <a href="#">Android</a>
                                                            <a href="#">app</a>
                                                            <a href="#">ReactJs</a>
                                                            <a href="#">Ruby</a>
                                                        </div>
                                                        <div class="openjobs">
                                                            <a href="#">1 Open Job </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-12 mb-20">
                                    <!-- Single Job Start  -->
                                    <div class="single-employer-list style-two">
                                        <div class="info-top align-items-start">
                                            <div class="employer-image">
                                                <a href="employer-details.html">
                                                    <img src="assets/images/companies_logo/logo-100/logo2.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="employer-info">
                                                <div class="employer-info-inner">
                                                    <div class="employer-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label mr-20">featured</span>
                                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="employer-title">
                                                                <a href="employer-details.html">Inwave Studio</a>
                                                            </h3>
                                                            <div class="employer-rate">
                                                                <div class="star">
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                </div>
                                                                <span class="total">1 Ratings </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="employer-meta">
                                                        <div class="field-companysize">
                                                            <i class="lnr lnr-users"></i>
                                                            <span class="label">Size</span>40-200
                                                        </div>
                                                        <div class="field-map"><i class="lnr lnr-map-marker"></i>Hanoi, Hanoi</div>
                                                    </div>
                                                    <div class="field-description">
                                                        <p>The leader in the marketing of commercial industry by providing enhanced services, relationship and profitability.…</p>
                                                    </div>
                                                    <div class="employer-bottom">
                                                        <div class="employer-skill-tag">
                                                            <a href="#">ReactJs</a>
                                                            <a href="#">Ruby</a>
                                                        </div>
                                                        <div class="openjobs">
                                                            <a href="#">1 Open Job </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-12 mb-20">
                                    <!-- Single Job Start  -->
                                    <div class="single-employer-list style-two">
                                        <div class="info-top align-items-start">
                                            <div class="employer-image">
                                                <a href="employer-details.html">
                                                    <img src="assets/images/companies_logo/logo-100/logo4.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="employer-info">
                                                <div class="employer-info-inner">
                                                    <div class="employer-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label mr-20">featured</span>
                                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="employer-title">
                                                                <a href="employer-details.html">Shippo Company</a>
                                                            </h3>
                                                            <div class="employer-rate">
                                                                <div class="star">
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                </div>
                                                                <span class="total">1 Ratings </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="employer-meta">
                                                        <div class="field-map"><i class="lnr lnr-map-marker"></i>Osaka, Osaka</div>
                                                    </div>
                                                    <div class="field-description">
                                                        <p>The leader in the marketing of commercial industry by providing enhanced services, relationship and profitability.…</p>
                                                    </div>
                                                    <div class="employer-bottom">
                                                        <div class="employer-skill-tag">
                                                            <a href="#">Android</a>
                                                            <a href="#">app</a>
                                                            <a href="#">ReactJs</a>
                                                            <a href="#">Ruby</a>
                                                        </div>
                                                        <div class="openjobs">
                                                            <a href="#">1 Open Job </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-12 mb-20">
                                    <!-- Single Job Start  -->
                                    <div class="single-employer-list style-two">
                                        <div class="info-top align-items-start">
                                            <div class="employer-image">
                                                <a href="employer-details.html">
                                                    <img src="assets/images/companies_logo/logo-100/logo5.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="employer-info">
                                                <div class="employer-info-inner">
                                                    <div class="employer-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label mr-20">featured</span>
                                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="employer-title">
                                                                <a href="employer-details.html">Vsmarttech</a>
                                                            </h3>
                                                            <div class="employer-rate">
                                                                <div class="star">
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                </div>
                                                                <span class="total">1 Ratings </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="employer-meta">
                                                        <div class="field-companysize">
                                                            <i class="lnr lnr-users"></i>
                                                            <span class="label">Size</span>40-200
                                                        </div>
                                                        <div class="field-map"><i class="lnr lnr-map-marker"></i>Hanoi, Hanoi</div>
                                                    </div>
                                                    <div class="field-description">
                                                        <p>The leader in the marketing of commercial industry by providing enhanced services, relationship and profitability.…</p>
                                                    </div>
                                                    <div class="employer-bottom">
                                                        <div class="employer-skill-tag">
                                                            <a href="#">Android</a>
                                                            <a href="#">app</a>
                                                            <a href="#">ReactJs</a>
                                                            <a href="#">Ruby</a>
                                                        </div>
                                                        <div class="openjobs">
                                                            <a href="#">1 Open Job </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                                <div class="col-lg-12 mb-20">
                                    <!-- Single Job Start  -->
                                    <div class="single-employer-list style-two">
                                        <div class="info-top align-items-start">
                                            <div class="employer-image">
                                                <a href="employer-details.html">
                                                    <img src="assets/images/companies_logo/logo-100/logo6.jpg" alt="logo">
                                                </a>
                                            </div>
                                            <div class="employer-info">
                                                <div class="employer-info-inner">
                                                    <div class="employer-info-top">
                                                        <div class="saveJob for-listing">
                                                            <span class="featured-label mr-20">featured</span>
                                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                                <i class="far fa-heart"></i>
                                                            </a>
                                                        </div>
                                                        <div class="title-name">
                                                            <h3 class="employer-title">
                                                                <a href="employer-details.html">Alpha Investing</a>
                                                            </h3>
                                                            <div class="employer-rate">
                                                                <div class="star">
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                    <i class="fa fa-star"></i>
                                                                </div>
                                                                <span class="total">1 Ratings </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="employer-meta">
                                                        <div class="field-map"><i class="lnr lnr-map-marker"></i>Chicago, California</div>
                                                    </div>
                                                    <div class="field-description">
                                                        <p>The leader in the marketing of commercial industry by providing enhanced services, relationship and profitability.…</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Single Job End -->
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <ul class="page-pagination">
                                        <li><a href="#"><i class="fa fa-angle-left"></i></a></li>
                                        <li class="active"><a href="#">1</a></li>
                                        <li><a href="#">2</a></li>
                                        <li><a href="#">3</a></li>
                                        <li><a href="#"><i class="fa fa-angle-right"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div id="grid" class="tab-pane fade">
                            <div class="row">

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-30">
                                    <!-- Single Employer Item Start -->
                                    <div class="employer-item border">
                                        <div class="saveJob for-grid">
                                            <span class="featured-label mr-20">featured</span>
                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                <i class="far fa-heart"></i>
                                            </a>
                                        </div>
                                        <div class="employer-image">
                                            <img src="assets/images/companies_logo/logo-1.jpg" alt="">
                                        </div>
                                        <div class="employer-info-top">
                                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  Beijing, Beijing</span>
                                            <h3 class="employer-name"><a href="employer-details.html">Digital Asset</a></h3>
                                            <div class="employer-info-rate d-flex align-items-center">
                                                <div class="star">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                </div>
                                                <span class="total">1 Ratings </span>
                                            </div>
                                            <a href="#" class="link-openjobs">2 Jobs</a>
                                        </div>
                                    </div>
                                    <!-- Single Employer Item End -->
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-30">
                                    <!-- Single Employer Item Start -->
                                    <div class="employer-item border">
                                        <div class="saveJob for-grid">
                                            <span class="featured-label mr-20">featured</span>
                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                <i class="far fa-heart"></i>
                                            </a>
                                        </div>
                                        <div class="employer-image">
                                            <img src="assets/images/companies_logo/logo-2.jpg" alt="">
                                        </div>
                                        <div class="employer-info-top">
                                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  Victoria</span>
                                            <h3 class="employer-name"><a href="employer-details.html">Liquididea Design</a></h3>
                                            <div class="employer-info-rate d-flex align-items-center">
                                                <div class="star">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                </div>
                                                <span class="total">2 Ratings </span>
                                            </div>
                                            <a href="#" class="link-openjobs">3 Jobs</a>
                                        </div>
                                    </div>
                                    <!-- Single Employer Item End -->
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-30">
                                    <!-- Single Employer Item Start -->
                                    <div class="employer-item border">
                                        <div class="saveJob for-grid">
                                            <span class="featured-label mr-20">featured</span>
                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                <i class="far fa-heart"></i>
                                            </a>
                                        </div>
                                        <div class="employer-image">
                                            <img src="assets/images/companies_logo/logo-3.jpg" alt="">
                                        </div>
                                        <div class="employer-info-top">
                                            <span class="employer-location"><i class="lnr lnr-map-marker"></i> Osaka, Osaka</span>
                                            <h3 class="employer-name"><a href="employer-details.html">Shippo Company</a></h3>
                                            <div class="employer-info-rate d-flex align-items-center">
                                                <div class="star">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                </div>
                                                <span class="total">1 Ratings </span>
                                            </div>
                                            <a href="#" class="link-openjobs">1 Jobs</a>
                                        </div>
                                    </div>
                                    <!-- Single Employer Item End -->
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-30">
                                    <!-- Single Employer Item Start -->
                                    <div class="employer-item border">
                                        <span class="featured-employer-label color-green">Featured</span>
                                        <div class="employer-image">
                                            <img src="assets/images/companies_logo/logo-4.jpg" alt="">
                                        </div>
                                        <div class="employer-info-top">
                                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  Chicago, California</span>
                                            <h3 class="employer-name"><a href="employer-details.html">Alpha Investing <i class="fas fa-check-circle"></i></a></h3>
                                            <div class="employer-info-rate d-flex align-items-center">
                                                <div class="star">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                </div>
                                                <span class="total">1 Ratings </span>
                                            </div>
                                            <a href="#" class="link-openjobs">2 Jobs</a>
                                        </div>
                                    </div>
                                    <!-- Single Employer Item End -->
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-30">
                                    <!-- Single Employer Item Start -->
                                    <div class="employer-item border">
                                        <div class="saveJob for-grid">
                                            <span class="featured-label mr-20">featured</span>
                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                <i class="far fa-heart"></i>
                                            </a>
                                        </div>
                                        <div class="employer-image">
                                            <img src="assets/images/companies_logo/logo-5.jpg" alt="">
                                        </div>
                                        <div class="employer-info-top">
                                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>  New York, New York</span>
                                            <h3 class="employer-name"><a href="employer-details.html">Radio Game</a></h3>
                                            <div class="employer-info-rate d-flex align-items-center">
                                                <div class="star">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                </div>
                                                <span class="total">3 Ratings </span>
                                            </div>
                                            <a href="#" class="link-openjobs">4 Jobs</a>
                                        </div>
                                    </div>
                                    <!-- Single Employer Item End -->
                                </div>

                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-30">
                                    <!-- Single Employer Item Start -->
                                    <div class="employer-item border">
                                        <div class="saveJob for-grid">
                                            <span class="featured-label mr-20">featured</span>
                                            <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                <i class="far fa-heart"></i>
                                            </a>
                                        </div>
                                        <div class="employer-image">
                                            <img src="assets/images/companies_logo/logo-6.jpg" alt="">
                                        </div>
                                        <div class="employer-info-top">
                                            <span class="employer-location"><i class="lnr lnr-map-marker"></i>Seville, Andalusia</span>
                                            <h3 class="employer-name"><a href="employer-details.html">Digital Vine <i class="fas fa-check-circle"></i></a></h3>
                                            <div class="employer-info-rate d-flex align-items-center">
                                                <div class="star">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                </div>
                                                <span class="total">3 Ratings </span>
                                            </div>
                                            <a href="#" class="link-openjobs">3 Jobs</a>
                                        </div>
                                    </div>
                                    <!-- Single Employer Item End -->
                                </div>


                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <ul class="page-pagination">
                                        <li><a href="#"><i class="fa fa-angle-left"></i></a></li>
                                        <li class="active"><a href="#">1</a></li>
                                        <li><a href="#">2</a></li>
                                        <li><a href="#">3</a></li>
                                        <li><a href="#"><i class="fa fa-angle-right"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <!-- Employers Listing Section End -->



@stop



@section('footer')

@stop
