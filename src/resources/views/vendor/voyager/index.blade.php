@extends('voyager::master')
{{--Google analytics--}}
<?php $google_analytics = true; ?>

@section('content')
    <div class="page-content">
        @include('voyager::alerts')

        @if(in_array(@Auth::user()->role_id, [102]))
            @include('voyager::partials.candidate-partial-top-menu')
        @endif

        @if(!in_array(@Auth::user()->role_id, [101, 102, 107, 108, 109, 110]))
            <!-- Dashboard Stats Cards -->
            <div class="clearfix container-fluid row" style="margin-top: 15px; margin-bottom: 25px;">
                <div class="col-xs-12 col-sm-4" style="margin-bottom: 15px;">
                    <div class="panel widget center bgimage" style="margin-bottom:0; overflow:hidden; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border-radius: 16px; border: none; box-shadow: 0 4px 20px rgba(59, 130, 246, 0.15); padding: 20px 24px; position: relative;">
                        <div class="panel-content" style="display: flex; align-items: center; justify-content: space-between; color: #ffffff;">
                            <div style="text-align: left;">
                                <h3 style="margin: 0; font-weight: 800; font-size: 24px; line-height: 1.2; color: #ffffff !important;">
                                    {{ number_format(\DB::table('agents')->sum('balance'), 2) }}
                                </h3>
                                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-top: 4px; color: #ffffff;">
                                    Agent Balance
                                </div>
                            </div>
                            <div style="background: rgba(255,255,255,0.15); padding: 12px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 28px; height: 28px; color: #ffffff;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-sm-4" style="margin-bottom: 15px;">
                    <div class="panel widget center bgimage" style="margin-bottom:0; overflow:hidden; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; border: none; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15); padding: 20px 24px; position: relative;">
                        <div class="panel-content" style="display: flex; align-items: center; justify-content: space-between; color: #ffffff;">
                            <div style="text-align: left;">
                                <h3 style="margin: 0; font-weight: 800; font-size: 24px; line-height: 1.2; color: #ffffff !important;">
                                    {{ number_format(\DB::table('candidates')->count()) }}
                                </h3>
                                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-top: 4px; color: #ffffff;">
                                    Total Candidates
                                </div>
                            </div>
                            <div style="background: rgba(255,255,255,0.15); padding: 12px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 28px; height: 28px; color: #ffffff;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-sm-4" style="margin-bottom: 15px;">
                    <div class="panel widget center bgimage" style="margin-bottom:0; overflow:hidden; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 16px; border: none; box-shadow: 0 4px 20px rgba(245, 158, 11, 0.15); padding: 20px 24px; position: relative;">
                        <div class="panel-content" style="display: flex; align-items: center; justify-content: space-between; color: #ffffff;">
                            <div style="text-align: left;">
                                <h3 style="margin: 0; font-weight: 800; font-size: 24px; line-height: 1.2; color: #ffffff !important;">
                                    {{ number_format(\DB::table('agents')->count()) }}
                                </h3>
                                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.85; margin-top: 4px; color: #ffffff;">
                                    Total Agents
                                </div>
                            </div>
                            <div style="background: rgba(255,255,255,0.15); padding: 12px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 28px; height: 28px; color: #ffffff;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="clearfix container-fluid row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <style>
                    .slider-container {
                        max-width: 1400px;
                        margin: 0 auto;
                        position: relative;
                        padding: 0 60px;
                    }

                    .card-wrapper {
                        display: none;
                    }

                    .card-wrapper.active {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 20px;
                        animation: fadeIn 0.5s;
                    }

                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                            transform: translateY(20px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }

                    .congrats-card {
                        background: white;
                        border-radius: 20px;
                        padding: 25px;
                        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                        text-align: center;
                        display: flex;
                        flex-direction: column;
                    }

                    .header-badge {
                        background: #5a6c7d;
                        color: white;
                        padding: 12px 30px;
                        border-radius: 50px;
                        font-size: 20px;
                        font-weight: bold;
                        margin-bottom: 20px;
                        letter-spacing: 2px;
                    }

                    .info-section {
                        display: flex;
                        flex-direction: column;
                        gap: 10px;
                        margin-bottom: 20px;
                    }

                    .info-box {
                        background: #5a6c7d;
                        color: white;
                        padding: 12px 15px;
                        border-radius: 50px;
                        font-size: 12px;
                        font-weight: 500;
                    }

                    .profile-section {
                        margin: 20px 0;
                    }

                    .profile-image {
                        width: 120px;
                        height: 120px;
                        border-radius: 50%;
                        border: 4px solid #4a90e2;
                        object-fit: cover;
                        margin: 0 auto 15px;
                        display: block;
                    }

                    .name {
                        font-size: 24px;
                        font-weight: bold;
                        color: #2c3e50;
                        margin: 10px 0;
                        text-transform: uppercase;
                        letter-spacing: 2px;
                        word-break: break-word;
                    }

                    .detail-box {
                        background: #5a6c7d;
                        color: white;
                        padding: 10px 20px;
                        border-radius: 50px;
                        margin: 8px auto;
                        font-size: 13px;
                        max-width: 100%;
                    }

                    .instruction-box {
                        background: #34495e;
                        color: white;
                        padding: 15px;
                        border-radius: 15px;
                        font-size: 10px;
                        line-height: 1.6;
                        margin-top: 15px;
                    }

                    .instruction-box span {
                        color: #e74c3c;
                        font-weight: bold;
                    }

                    .nav-button {
                        position: absolute;
                        top: 50%;
                        transform: translateY(-50%);
                        background: white;
                        border: none;
                        width: 45px;
                        height: 45px;
                        border-radius: 50%;
                        font-size: 24px;
                        cursor: pointer;
                        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                        transition: all 0.3s;
                        z-index: 10;
                    }

                    .nav-button:hover {
                        background: #4a90e2;
                        color: white;
                        transform: translateY(-50%) scale(1.1);
                    }

                    .nav-button.prev {
                        left: 5px;
                    }

                    .nav-button.next {
                        right: 5px;
                    }

                    .dots-container {
                        text-align: center;
                        margin-top: 30px;
                    }

                    .dot {
                        width: 12px;
                        height: 12px;
                        border-radius: 50%;
                        background: rgba(255,255,255,0.5);
                        display: inline-block;
                        margin: 0 5px;
                        cursor: pointer;
                        transition: all 0.3s;
                    }

                    .dot.active {
                        background: white;
                        width: 30px;
                        border-radius: 6px;
                    }

                    /* Tablet */
                    @media (max-width: 992px) {
                        .slider-container {
                            padding: 0 50px;
                        }

                        .congrats-card {
                            padding: 20px;
                        }

                        .header-badge {
                            font-size: 16px;
                            padding: 10px 25px;
                        }

                        .profile-image {
                            width: 100px;
                            height: 100px;
                        }

                        .name {
                            font-size: 20px;
                        }

                        .detail-box {
                            font-size: 12px;
                            padding: 8px 15px;
                        }

                        .instruction-box {
                            font-size: 9px;
                            padding: 12px;
                        }
                    }

                    /* Mobile - Show 1 card at a time */
                    @media (max-width: 768px) {
                        body {
                            padding: 20px 5px;
                        }

                        .slider-container {
                            padding: 0 45px;
                        }

                        .card-wrapper.active {
                            grid-template-columns: 1fr;
                            gap: 20px;
                        }

                        .congrats-card {
                            max-width: 500px;
                            margin: 0 auto;
                            padding: 25px;
                        }

                        .header-badge {
                            font-size: 18px;
                            padding: 12px 30px;
                        }

                        .info-section {
                            flex-direction: row;
                            gap: 10px;
                        }

                        .info-box {
                            font-size: 11px;
                        }

                        .profile-image {
                            width: 130px;
                            height: 130px;
                        }

                        .name {
                            font-size: 26px;
                            letter-spacing: 2px;
                        }

                        .detail-box {
                            font-size: 13px;
                            padding: 10px 20px;
                        }

                        .instruction-box {
                            font-size: 10px;
                            padding: 15px;
                        }

                        .nav-button {
                            width: 40px;
                            height: 40px;
                            font-size: 20px;
                        }
                    }

                    /* Small Mobile */
                    @media (max-width: 480px) {
                        .slider-container {
                            padding: 0 40px;
                        }

                        .congrats-card {
                            padding: 20px;
                        }

                        .header-badge {
                            font-size: 16px;
                            padding: 10px 20px;
                        }

                        .info-section {
                            flex-direction: column;
                        }

                        .info-box {
                            font-size: 10px;
                            padding: 10px 12px;
                        }

                        .profile-image {
                            width: 110px;
                            height: 110px;
                        }

                        .name {
                            font-size: 22px;
                            letter-spacing: 1px;
                        }

                        .detail-box {
                            font-size: 12px;
                            padding: 8px 15px;
                        }

                        .instruction-box {
                            font-size: 9px;
                            padding: 12px;
                        }

                        .nav-button {
                            width: 35px;
                            height: 35px;
                            font-size: 18px;
                        }

                        .nav-button.prev {
                            left: 2px;
                        }

                        .nav-button.next {
                            right: 2px;
                        }
                    }
                </style>

                @php
                    $candidate = \DB::select(" SELECT sc.*, c.name, c.passport_no, c.half_photo_file_path
                             FROM selected_candidates AS sc
                             INNER JOIN candidates AS c  ON c.id = sc.candidate_id
                            ORDER BY sc.id DESC");


                    $sponsors = [];
                    if($candidate){
                        foreach ($candidate as $s){
                            $sponsors[] = [
                                'name' => $s->name,
                                'position' => strtoupper($s->position),
                                'passport_no' =>$s->passport_no,
                                'image' =>!empty($s->half_photo_file_path)?Voyager::image($s->half_photo_file_path):'https://placehold.co/150?text='.$s->name,
                                'website' => '#'
                            ];
                        }
                    }else{
                        $sponsors = [];
                    }

                @endphp
                <div class="slider-container">
                    <button class="nav-button prev" onclick="changeSlide(-1)">‹</button>
                    <button class="nav-button next" onclick="changeSlide(1)">›</button>

                    <div id="slider">
                        <!-- Slide 1 -->
                        @php
                            $chunkedSponsors = array_chunk($sponsors, 3);
                        @endphp

                        @foreach($chunkedSponsors as $index => $sponsorGroup)

                            <div class="card-wrapper {{ $index === 0 ? 'active' : '' }}">
                                @foreach($sponsorGroup as $i=>$sponsor)
                                    <div class="congrats-card">
                                        <div class="header-badge">CONGRATULATIONS 🎉🎉</div>
                                        <div class="info-section">
                                            <div class="info-box">FOR SELECTED FROM</div>
                                            <div class="info-box">CYPRUS REPUTED COMPANY</div>
                                        </div>
                                        <div class="profile-section">
                                            <img src="{{ $sponsor['image'] ?? $sponsor['logo'] }}" alt="{{ $sponsor['name'] }}" alt="Profile" class="profile-image">
                                            <div class="name"> {{ $sponsor['name'] }}</div>
                                            <div class="detail-box">POSITION : {{ $sponsor['position'] ?? '' }}</div>
                                            <div class="detail-box">PASSPORT NO : {{ $sponsor['passport_no'] ?? '' }}</div>
                                        </div>
                                        <div class="instruction-box">
                                            PLEASE COMPLETE YOUR MEDICAL REPORT FROM YOUR OWN CITY AS PER YOUR AGENT INSTRUCTIONS. PLEASE SEND YOUR <span>ORIGINAL PASSPORT</span> BY YOUR AGENT INSTRUCTIONS.
                                        </div>
                                    </div>

                                @endforeach

                            </div>

                        @endforeach
                    </div>

                    <div class="dots-container" id="dotsContainer"></div>
                </div>
                <script>
                    let currentSlide = 0;
                    const slides = document.querySelectorAll('.card-wrapper');
                    const totalSlides = slides.length;

                    // Initialize dots
                    function initDots() {
                        const dotsContainer = document.getElementById('dotsContainer');
                        for (let i = 0; i < totalSlides; i++) {
                            const dot = document.createElement('span');
                            dot.className = 'dot' + (i === 0 ? ' active' : '');
                            dot.onclick = () => goToSlide(i);
                            dotsContainer.appendChild(dot);
                        }
                    }

                    function showSlide(n) {
                        if (n >= totalSlides) currentSlide = 0;
                        if (n < 0) currentSlide = totalSlides - 1;

                        slides.forEach(slide => slide.classList.remove('active'));
                        slides[currentSlide].classList.add('active');

                        const dots = document.querySelectorAll('.dot');
                        dots.forEach(dot => dot.classList.remove('active'));
                        dots[currentSlide].classList.add('active');
                    }

                    function changeSlide(direction) {
                        currentSlide += direction;
                        showSlide(currentSlide);
                    }

                    function goToSlide(n) {
                        currentSlide = n;
                        showSlide(currentSlide);
                    }

                    // Auto-play slider
                    setInterval(() => {
                        changeSlide(1);
                    }, 10000);

                    // Initialize
                    document.addEventListener('DOMContentLoaded', function() {
                        initDots();
                    });

                    // Keyboard navigation
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'ArrowLeft') changeSlide(-1);
                        if (e.key === 'ArrowRight') changeSlide(1);
                    });
                </script>
            </div>
        </div>

        @include('voyager::dimmers')

        <div class="clearfix container-fluid row" style="display:none">

            <div class="col-xs-12 col-sm-6 col-md-3">
                <div class="panel widget center bgimage"
                     style="margin-bottom:0;overflow:hidden; background-color: plum;">
                    <div class="dimmer"></div>
                    <div class="panel-content">
                        <div class="" style="float: left; font-weight: bold;">
                            <h4 class="text-success" style="font-weight: bold;">1000</h4>
                            <h4 style="font-weight: bold;">Student</h4>
                        </div>
                        <i class="icon voyager-people" style="float: right"></i>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-3">
                <div class="panel widget center bgimage"
                     style="margin-bottom:0;overflow:hidden; background-color: springgreen;">
                    <div class="dimmer"></div>
                    <div class="panel-content">
                        <div class="" style="float: left; font-weight: bold;">
                            <h4 class="text-success" style="font-weight: bold;">20</h4>
                            <h4 style="font-weight: bold;">Teacher</h4>
                        </div>
                        <i class="icon voyager-group" style="float: right"></i>
                    </div>
                </div>
            </div>


            <div class="col-xs-12 col-sm-6 col-md-3">
                <div class="panel widget center bgimage"
                     style="margin-bottom:0;overflow:hidden; background-color: chocolate;">
                    <div class="dimmer"></div>
                    <div class="panel-content">
                        <div class="" style="float: left; font-weight: bold;">
                            <h4 class="text-success" style="font-weight: bold;">20</h4>
                            <h4 style="font-weight: bold;">Agent</h4>
                        </div>
                        <i class="icon voyager-people" style="float: right"></i>
                    </div>
                </div>
            </div>


            <div class="col-xs-12 col-sm-6 col-md-3">
                <div class="panel widget center bgimage"
                     style="margin-bottom:0;overflow:hidden; background-color: turquoise;">
                    <div class="dimmer"></div>
                    <div class="panel-content">
                        <div class="" style="float: left; font-weight: bold;">
                            <h3 class="text-success"
                                style="font-weight: bold;">123</h3>
                            <h4 style="font-weight: bold;">Employee</h4>
                        </div>
                        <i class="voyager-basket" style="float: right"></i>
                    </div>
                </div>
            </div>

        </div>

    <!--Google analytics start-->
        @if(!$google_analytics)
            <div class="analytics-container">
                <?php $google_analytics_client_id = Voyager::setting("admin.google_analytics_client_id"); ?>
                @if (isset($google_analytics_client_id) && !empty($google_analytics_client_id))
                    {{-- Google Analytics Embed --}}
                    <div id="embed-api-auth-container"></div>
                @else
                    <p style="border-radius:4px; padding:20px; background:#fff; margin:0; color:#999; text-align:center;">
                        {!! __('voyager::analytics.no_client_id') !!}
                        <a href="https://console.developers.google.com" target="_blank">https://console.developers.google.com</a>
                    </p>
                @endif

                <div class="Dashboard Dashboard--full" id="analytics-dashboard">
                    <header class="Dashboard-header">
                        <ul class="FlexGrid">
                            <li class="FlexGrid-item">
                                <div class="Titles">
                                    <h1 class="Titles-main"
                                        id="view-name">{{ __('voyager::analytics.select_view') }}</h1>
                                    <div class="Titles-sub">{{ __('voyager::analytics.various_visualizations') }}</div>
                                </div>
                            </li>
                            <li class="FlexGrid-item FlexGrid-item--fixed">
                                <div id="active-users-container"></div>
                            </li>
                        </ul>
                        <div id="view-selector-container"></div>
                    </header>

                    <ul class="FlexGrid FlexGrid--halves">
                        <li class="FlexGrid-item">
                            <div class="Chartjs">
                                <header class="Titles">
                                    <h1 class="Titles-main">{{ __('voyager::analytics.this_vs_last_week') }}</h1>
                                    <div class="Titles-sub">{{ __('voyager::analytics.by_users') }}</div>
                                </header>
                                <figure class="Chartjs-figure" id="chart-1-container"></figure>
                                <ol class="Chartjs-legend" id="legend-1-container"></ol>
                            </div>
                        </li>
                        <li class="FlexGrid-item">
                            <div class="Chartjs">
                                <header class="Titles">
                                    <h1 class="Titles-main">{{ __('voyager::analytics.this_vs_last_year') }}</h1>
                                    <div class="Titles-sub">{{ __('voyager::analytics.by_users') }}</div>
                                </header>
                                <figure class="Chartjs-figure" id="chart-2-container"></figure>
                                <ol class="Chartjs-legend" id="legend-2-container"></ol>
                            </div>
                        </li>
                        <li class="FlexGrid-item">
                            <div class="Chartjs">
                                <header class="Titles">
                                    <h1 class="Titles-main">{{ __('voyager::analytics.top_browsers') }}</h1>
                                    <div class="Titles-sub">{{ __('voyager::analytics.by_pageview') }}</div>
                                </header>
                                <figure class="Chartjs-figure" id="chart-3-container"></figure>
                                <ol class="Chartjs-legend" id="legend-3-container"></ol>
                            </div>
                        </li>
                        <li class="FlexGrid-item">
                            <div class="Chartjs">
                                <header class="Titles">
                                    <h1 class="Titles-main">{{ __('voyager::analytics.top_countries') }}</h1>
                                    <div class="Titles-sub">{{ __('voyager::analytics.by_sessions') }}</div>
                                </header>
                                <figure class="Chartjs-figure" id="chart-4-container"></figure>
                                <ol class="Chartjs-legend" id="legend-4-container"></ol>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
    @endif
    <!--Google analytics end-->

    </div>
@stop

@section('javascript')

    <!--Google analytics start-->
    @if($google_analytics & isset($google_analytics_client_id) && !empty($google_analytics_client_id))
        <script>
            (function (w, d, s, g, js, fs) {
                g = w.gapi || (w.gapi = {});
                g.analytics = {
                    q: [], ready: function (f) {
                        this.q.push(f);
                    }
                };
                js = d.createElement(s);
                fs = d.getElementsByTagName(s)[0];
                js.src = 'https://apis.google.com/js/platform.js';
                fs.parentNode.insertBefore(js, fs);
                js.onload = function () {
                    g.load('analytics');
                };
            }(window, document, 'script'));
        </script>

        <script src="{{ asset('admin/Chart.1.1.1.min.js') }}"></script>
        <script src="{{ asset('admin/moment.min.js') }}"></script>
        <script>
            // View Selector 2 JS
            !function (e) {
                function t(r) {
                    if (i[r]) return i[r].exports;
                    var o = i[r] = {exports: {}, id: r, loaded: !1};
                    return e[r].call(o.exports, o, o.exports, t), o.loaded = !0, o.exports
                }

                var i = {};
                return t.m = e, t.c = i, t.p = "", t(0)
            }([function (e, t, i) {
                "use strict";

                function r(e) {
                    return e && e.__esModule ? e : {"default": e}
                }

                var o = i(1), s = r(o);
                gapi.analytics.ready(function () {
                    function e(e, t, i) {
                        e.innerHTML = t.map(function (e) {
                            var t = e.id == i ? "selected " : " ";
                            return "<option " + t + 'value="' + e.id + '">' + e.name + "</option>"
                        }).join("")
                    }

                    function t(e) {
                        return e.ids || e.viewId ? {
                            prop: "viewId",
                            value: e.viewId || e.ids && e.ids.replace(/^ga:/, "")
                        } : e.propertyId ? {prop: "propertyId", value: e.propertyId} : e.accountId ? {
                            prop: "accountId",
                            value: e.accountId
                        } : void 0
                    }

                    gapi.analytics.createComponent("ViewSelector2", {
                        execute: function () {
                            return this.setup_(function () {
                                this.updateAccounts_(), this.changed_ && (this.render_(), this.onChange_())
                            }.bind(this)), this
                        },
                        set: function (e) {
                            if (!!e.ids + !!e.viewId + !!e.propertyId + !!e.accountId > 1) throw new Error('You cannot specify more than one of the following options: "ids", "viewId", "accountId", "propertyId"');
                            if (e.container && this.container) throw new Error("You cannot change containers once a view selector has been rendered on the page.");
                            var t = this.get();
                            return (t.ids != e.ids || t.viewId != e.viewId || t.propertyId != e.propertyId || t.accountId != e.accountId) && (t.ids = null, t.viewId = null, t.propertyId = null, t.accountId = null), gapi.analytics.Component.prototype.set.call(this, e)
                        },
                        setup_: function (e) {
                            function t() {
                                s["default"].get().then(function (t) {
                                    i.summaries = t, i.accounts = i.summaries.all(), e()
                                }, function (e) {
                                    i.emit("error", e)
                                })
                            }

                            var i = this;
                            gapi.analytics.auth.isAuthorized() ? t() : gapi.analytics.auth.on("signIn", t)
                        },
                        updateAccounts_: function () {
                            var e = this.get(), i = t(e), r = void 0, o = void 0, s = void 0;
                            if (!this.summaries.all().length) return this.emit("error", new Error('This user does not have any Google Analytics accounts. You can sign up at "www.google.com/analytics".'));
                            if (i) switch (i.prop) {
                                case"viewId":
                                    r = this.summaries.getProfile(i.value), o = this.summaries.getAccountByProfileId(i.value), s = this.summaries.getWebPropertyByProfileId(i.value);
                                    break;
                                case"propertyId":
                                    s = this.summaries.getWebProperty(i.value), o = this.summaries.getAccountByWebPropertyId(i.value), r = s && s.views && s.views[0];
                                    break;
                                case"accountId":
                                    o = this.summaries.getAccount(i.value), s = o && o.properties && o.properties[0], r = s && s.views && s.views[0]
                            } else o = this.accounts[0], s = o && o.properties && o.properties[0], r = s && s.views && s.views[0];
                            o || s || r ? (o != this.account || s != this.property || r != this.view) && (this.changed_ = {
                                account: o && o != this.account,
                                property: s && s != this.property,
                                view: r && r != this.view
                            }, this.account = o, this.properties = o.properties, this.property = s, this.views = s && s.views, this.view = r, this.ids = r && "ga:" + r.id) : this.emit("error", new Error("This user does not have access to " + i.prop.slice(0, -2) + " : " + i.value))
                        },
                        render_: function () {
                            var t = this.get();
                            this.container = "string" == typeof t.container ? document.getElementById(t.container) : t.container, this.container.innerHTML = t.template || this.template;
                            var i = this.container.querySelectorAll("select"), r = this.accounts,
                                o = this.properties || [{name: "(Empty)", id: ""}],
                                s = this.views || [{name: "(Empty)", id: ""}];
                            e(i[0], r, this.account.id), e(i[1], o, this.property && this.property.id), e(i[2], s, this.view && this.view.id), i[0].onchange = this.onUserSelect_.bind(this, i[0], "accountId"), i[1].onchange = this.onUserSelect_.bind(this, i[1], "propertyId"), i[2].onchange = this.onUserSelect_.bind(this, i[2], "viewId")
                        },
                        onChange_: function () {
                            var e = {
                                account: this.account,
                                property: this.property,
                                view: this.view,
                                ids: this.view && "ga:" + this.view.id
                            };
                            this.changed_ && (this.changed_.account && this.emit("accountChange", e), this.changed_.property && this.emit("propertyChange", e), this.changed_.view && (this.emit("viewChange", e), this.emit("idsChange", e), this.emit("change", e.ids))), this.changed_ = null
                        },
                        onUserSelect_: function (e, t) {
                            var i = {};
                            i[t] = e.value, this.set(i), this.execute()
                        },
                        template: '<div class="ViewSelector2">  <div class="ViewSelector2-item">    <label>Account</label>    <select class="FormField"></select>  </div>  <div class="ViewSelector2-item">    <label>Property</label>    <select class="FormField"></select>  </div>  <div class="ViewSelector2-item">    <label>View</label>    <select class="FormField"></select>  </div></div>'
                    })
                })
            }, function (e, t, i) {
                function r() {
                    var e = gapi.client.request({path: n}).then(function (e) {
                        return e
                    });
                    return new e.constructor(function (t, i) {
                        var r = [];
                        e.then(function o(e) {
                            var c = e.result;
                            c.items ? r = r.concat(c.items) : i(new Error("You do not have any Google Analytics accounts. Go to http://google.com/analytics to sign up.")), c.startIndex + c.itemsPerPage <= c.totalResults ? gapi.client.request({
                                path: n,
                                params: {"start-index": c.startIndex + c.itemsPerPage}
                            }).then(o) : t(new s(r))
                        }).then(null, i)
                    })
                }

                var o, s = i(2), n = "/analytics/v3/management/accountSummaries";
                e.exports = {
                    get: function (e) {
                        return e && (o = null), o || (o = r())
                    }
                }
            }, function (e, t) {
                function i(e) {
                    this.accounts_ = e, this.webProperties_ = [], this.profiles_ = [], this.accountsById_ = {}, this.webPropertiesById_ = this.propertiesById_ = {}, this.profilesById_ = this.viewsById_ = {};
                    for (var t, i = 0; t = this.accounts_[i]; i++) if (this.accountsById_[t.id] = {self: t}, t.webProperties) {
                        r(t, "webProperties", "properties");
                        for (var o, s = 0; o = t.webProperties[s]; s++) if (this.webProperties_.push(o), this.webPropertiesById_[o.id] = {
                            self: o,
                            parent: t
                        }, o.profiles) {
                            r(o, "profiles", "views");
                            for (var n, c = 0; n = o.profiles[c]; c++) this.profiles_.push(n), this.profilesById_[n.id] = {
                                self: n,
                                parent: o,
                                grandParent: t
                            }
                        }
                    }
                }

                function r(e, t, i) {
                    Object.defineProperty ? Object.defineProperty(e, i, {
                        get: function () {
                            return e[t]
                        }
                    }) : e[i] = e[t]
                }

                i.prototype.all = function () {
                    return this.accounts_
                }, r(i.prototype, "all", "allAccounts"), i.prototype.allWebProperties = function () {
                    return this.webProperties_
                }, r(i.prototype, "allWebProperties", "allProperties"), i.prototype.allProfiles = function () {
                    return this.profiles_
                }, r(i.prototype, "allProfiles", "allViews"), i.prototype.get = function (e) {
                    if (!!e.accountId + !!e.webPropertyId + !!e.propertyId + !!e.profileId + !!e.viewId > 1) throw new Error('get() only accepts an object with a single property: either "accountId", "webPropertyId", "propertyId", "profileId" or "viewId"');
                    return this.getProfile(e.profileId || e.viewId) || this.getWebProperty(e.webPropertyId || e.propertyId) || this.getAccount(e.accountId)
                }, i.prototype.getAccount = function (e) {
                    return this.accountsById_[e] && this.accountsById_[e].self
                }, i.prototype.getWebProperty = function (e) {
                    return this.webPropertiesById_[e] && this.webPropertiesById_[e].self
                }, r(i.prototype, "getWebProperty", "getProperty"), i.prototype.getProfile = function (e) {
                    return this.profilesById_[e] && this.profilesById_[e].self
                }, r(i.prototype, "getProfile", "getView"), i.prototype.getAccountByProfileId = function (e) {
                    return this.profilesById_[e] && this.profilesById_[e].grandParent
                }, r(i.prototype, "getAccountByProfileId", "getAccountByViewId"), i.prototype.getWebPropertyByProfileId = function (e) {
                    return this.profilesById_[e] && this.profilesById_[e].parent
                }, r(i.prototype, "getWebPropertyByProfileId", "getPropertyByViewId"), i.prototype.getAccountByWebPropertyId = function (e) {
                    return this.webPropertiesById_[e] && this.webPropertiesById_[e].parent
                }, r(i.prototype, "getAccountByWebPropertyId", "getAccountByPropertyId"), e.exports = i
            }]);
            // DateRange Selector JS
            !function (t) {
                function e(n) {
                    if (a[n]) return a[n].exports;
                    var i = a[n] = {exports: {}, id: n, loaded: !1};
                    return t[n].call(i.exports, i, i.exports, e), i.loaded = !0, i.exports
                }

                var a = {};
                return e.m = t, e.c = a, e.p = "", e(0)
            }([function (t, e) {
                "use strict";
                gapi.analytics.ready(function () {
                    function t(t) {
                        if (n.test(t)) return t;
                        var i = a.exec(t);
                        if (i) return e(+i[1]);
                        if ("today" == t) return e(0);
                        if ("yesterday" == t) return e(1);
                        throw new Error("Cannot convert date " + t)
                    }

                    function e(t) {
                        var e = new Date;
                        e.setDate(e.getDate() - t);
                        var a = String(e.getMonth() + 1);
                        a = 1 == a.length ? "0" + a : a;
                        var n = String(e.getDate());
                        return n = 1 == n.length ? "0" + n : n, e.getFullYear() + "-" + a + "-" + n
                    }

                    var a = /(\d+)daysAgo/, n = /\d{4}\-\d{2}\-\d{2}/;
                    gapi.analytics.createComponent("DateRangeSelector", {
                        execute: function () {
                            var e = this.get();
                            e["start-date"] = e["start-date"] || "7daysAgo", e["end-date"] = e["end-date"] || "yesterday", this.container = "string" == typeof e.container ? document.getElementById(e.container) : e.container, e.template && (this.template = e.template), this.container.innerHTML = this.template;
                            var a = this.container.querySelectorAll("input");
                            return this.startDateInput = a[0], this.startDateInput.value = t(e["start-date"]), this.endDateInput = a[1], this.endDateInput.value = t(e["end-date"]), this.setValues(), this.setMinMax(), this.container.onchange = this.onChange.bind(this), this
                        },
                        onChange: function () {
                            this.setValues(), this.setMinMax(), this.emit("change", {
                                "start-date": this["start-date"],
                                "end-date": this["end-date"]
                            })
                        },
                        setValues: function () {
                            this["start-date"] = this.startDateInput.value, this["end-date"] = this.endDateInput.value
                        },
                        setMinMax: function () {
                            this.startDateInput.max = this.endDateInput.value, this.endDateInput.min = this.startDateInput.value
                        },
                        template: '<div class="DateRangeSelector">  <div class="DateRangeSelector-item">    <label>Start Date</label>     <input type="date">  </div>  <div class="DateRangeSelector-item">    <label>End Date</label>     <input type="date">  </div></div>'
                    })
                })
            }]);
            // Active Users JS
            !function (t) {
                function i(s) {
                    if (e[s]) return e[s].exports;
                    var n = e[s] = {exports: {}, id: s, loaded: !1};
                    return t[s].call(n.exports, n, n.exports, i), n.loaded = !0, n.exports
                }

                var e = {};
                return i.m = t, i.c = e, i.p = "", i(0)
            }([function (t, i) {
                "use strict";
                gapi.analytics.ready(function () {
                    gapi.analytics.createComponent("ActiveUsers", {
                        initialize: function () {
                            this.activeUsers = 0, gapi.analytics.auth.once("signOut", this.handleSignOut_.bind(this))
                        }, execute: function () {
                            this.polling_ && this.stop(), this.render_(), gapi.analytics.auth.isAuthorized() ? this.pollActiveUsers_() : gapi.analytics.auth.once("signIn", this.pollActiveUsers_.bind(this))
                        }, stop: function () {
                            clearTimeout(this.timeout_), this.polling_ = !1, this.emit("stop", {activeUsers: this.activeUsers})
                        }, render_: function () {
                            var t = this.get();
                            this.container = "string" == typeof t.container ? document.getElementById(t.container) : t.container, this.container.innerHTML = t.template || this.template, this.container.querySelector("b").innerHTML = this.activeUsers
                        }, pollActiveUsers_: function () {
                            var t = this.get(), i = 1e3 * (t.pollingInterval || 5);
                            if (isNaN(i) || 5e3 > i) throw new Error("Frequency must be 5 seconds or more.");
                            this.polling_ = !0, gapi.client.analytics.data.realtime.get({
                                ids: t.ids,
                                metrics: "rt:activeUsers"
                            }).then(function (t) {
                                var e = t.result, s = e.totalResults ? +e.rows[0][0] : 0, n = this.activeUsers;
                                this.emit("success", {activeUsers: this.activeUsers}), s != n && (this.activeUsers = s, this.onChange_(s - n)), 1 == this.polling_ && (this.timeout_ = setTimeout(this.pollActiveUsers_.bind(this), i))
                            }.bind(this))
                        }, onChange_: function (t) {
                            var i = this.container.querySelector("b");
                            i && (i.innerHTML = this.activeUsers), this.emit("change", {
                                activeUsers: this.activeUsers,
                                delta: t
                            }), t > 0 ? this.emit("increase", {
                                activeUsers: this.activeUsers,
                                delta: t
                            }) : this.emit("decrease", {activeUsers: this.activeUsers, delta: t})
                        }, handleSignOut_: function () {
                            this.stop(), gapi.analytics.auth.once("signIn", this.handleSignIn_.bind(this))
                        }, handleSignIn_: function () {
                            this.pollActiveUsers_(), gapi.analytics.auth.once("signOut", this.handleSignOut_.bind(this))
                        }, template: '<div class="ActiveUsers">Active Users: <b class="ActiveUsers-value"></b></div>'
                    })
                })
            }]);
        </script>

        <script>
            // == NOTE ==
            // This code uses ES6 promises. If you want to use this code in a browser
            // that doesn't supporting promises natively, you'll have to include a polyfill.

            gapi.analytics.ready(function () {

                /**
                 * Authorize the user immediately if the user has already granted access.
                 * If no access has been created, render an authorize button inside the
                 * element with the ID "embed-api-auth-container".
                 */
                gapi.analytics.auth.authorize({
                    container: 'embed-api-auth-container',
                    clientid: '{{ $google_analytics_client_id }}'
                });


                /**
                 * Create a new ActiveUsers instance to be rendered inside of an
                 * element with the id "active-users-container" and poll for changes every
                 * five seconds.
                 */
                var activeUsers = new gapi.analytics.ext.ActiveUsers({
                    container: 'active-users-container',
                    pollingInterval: 5
                });


                /**
                 * Add CSS animation to visually show the when users come and go.
                 */
                activeUsers.once('success', function () {
                    var element = this.container.firstChild;
                    var timeout;

                    document.getElementById('embed-api-auth-container').style.display = 'none';
                    document.getElementById('analytics-dashboard').style.display = 'block';

                    this.on('change', function (data) {
                        var element = this.container.firstChild;
                        var animationClass = data.delta > 0 ? 'is-increasing' : 'is-decreasing';
                        element.className += (' ' + animationClass);

                        clearTimeout(timeout);
                        timeout = setTimeout(function () {
                            element.className =
                                element.className.replace(/ is-(increasing|decreasing)/g, '');
                        }, 3000);
                    });
                });


                /**
                 * Create a new ViewSelector2 instance to be rendered inside of an
                 * element with the id "view-selector-container".
                 */
                var viewSelector = new gapi.analytics.ext.ViewSelector2({
                    container: 'view-selector-container',
                    propertyId: '{{ Voyager::setting("site.google_analytics_tracking_id")  }}'
                })
                    .execute();


                /**
                 * Update the activeUsers component, the Chartjs charts, and the dashboard
                 * title whenever the user changes the view.
                 */
                viewSelector.on('viewChange', function (data) {
                    var title = document.getElementById('view-name');
                    if (title) {
                        title.innerHTML = data.property.name + ' (' + data.view.name + ')';
                    }

                    // Start tracking active users for this view.
                    activeUsers.set(data).execute();

                    // Render all the of charts for this view.
                    renderWeekOverWeekChart(data.ids);
                    renderYearOverYearChart(data.ids);
                    renderTopBrowsersChart(data.ids);
                    renderTopCountriesChart(data.ids);
                });


                /**
                 * Draw the a chart.js line chart with data from the specified view that
                 * overlays session data for the current week over session data for the
                 * previous week.
                 */
                function renderWeekOverWeekChart(ids) {

                    // Adjust `now` to experiment with different days, for testing only...
                    var now = moment(); // .subtract(3, 'day');

                    var thisWeek = query({
                        'ids': ids,
                        'dimensions': 'ga:date,ga:nthDay',
                        'metrics': 'ga:users',
                        'start-date': moment(now).subtract(1, 'day').day(0).format('YYYY-MM-DD'),
                        'end-date': moment(now).format('YYYY-MM-DD')
                    });

                    var lastWeek = query({
                        'ids': ids,
                        'dimensions': 'ga:date,ga:nthDay',
                        'metrics': 'ga:users',
                        'start-date': moment(now).subtract(1, 'day').day(0).subtract(1, 'week')
                            .format('YYYY-MM-DD'),
                        'end-date': moment(now).subtract(1, 'day').day(6).subtract(1, 'week')
                            .format('YYYY-MM-DD')
                    });

                    Promise.all([thisWeek, lastWeek]).then(function (results) {

                        var data1 = results[0].rows.map(function (row) {
                            return +row[2];
                        });
                        var data2 = results[1].rows.map(function (row) {
                            return +row[2];
                        });
                        var labels = results[1].rows.map(function (row) {
                            return +row[0];
                        });

                        labels = labels.map(function (label) {
                            return moment(label, 'YYYYMMDD').format('ddd');
                        });

                        var data = {
                            labels: labels,
                            datasets: [
                                {
                                    label: '{{ __('voyager::date.last_week') }}',
                                    fillColor: 'rgba(220,220,220,0.5)',
                                    strokeColor: 'rgba(220,220,220,1)',
                                    pointColor: 'rgba(220,220,220,1)',
                                    pointStrokeColor: '#fff',
                                    data: data2
                                },
                                {
                                    label: '{{ __('voyager::date.this_week') }}',
                                    fillColor: 'rgba(151,187,205,0.5)',
                                    strokeColor: 'rgba(151,187,205,1)',
                                    pointColor: 'rgba(151,187,205,1)',
                                    pointStrokeColor: '#fff',
                                    data: data1
                                }
                            ]
                        };

                        new Chart(makeCanvas('chart-1-container')).Line(data);
                        generateLegend('legend-1-container', data.datasets);
                    });
                }


                /**
                 * Draw the a chart.js bar chart with data from the specified view that
                 * overlays session data for the current year over session data for the
                 * previous year, grouped by month.
                 */
                function renderYearOverYearChart(ids) {

                    // Adjust `now` to experiment with different days, for testing only...
                    var now = moment(); // .subtract(3, 'day');

                    var thisYear = query({
                        'ids': ids,
                        'dimensions': 'ga:month,ga:nthMonth',
                        'metrics': 'ga:users',
                        'start-date': moment(now).date(1).month(0).format('YYYY-MM-DD'),
                        'end-date': moment(now).format('YYYY-MM-DD')
                    });

                    var lastYear = query({
                        'ids': ids,
                        'dimensions': 'ga:month,ga:nthMonth',
                        'metrics': 'ga:users',
                        'start-date': moment(now).subtract(1, 'year').date(1).month(0)
                            .format('YYYY-MM-DD'),
                        'end-date': moment(now).date(1).month(0).subtract(1, 'day')
                            .format('YYYY-MM-DD')
                    });

                    Promise.all([thisYear, lastYear]).then(function (results) {
                        var data1 = results[0].rows.map(function (row) {
                            return +row[2];
                        });
                        var data2 = results[1].rows.map(function (row) {
                            return +row[2];
                        });
                        var labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                        // Ensure the data arrays are at least as long as the labels array.
                        // Chart.js bar charts don't (yet) accept sparse datasets.
                        for (var i = 0, len = labels.length; i < len; i++) {
                            if (data1[i] === undefined) data1[i] = null;
                            if (data2[i] === undefined) data2[i] = null;
                        }

                        var data = {
                            labels: labels,
                            datasets: [
                                {
                                    label: '{{ __('voyager::date.last_year') }}',
                                    fillColor: 'rgba(220,220,220,0.5)',
                                    strokeColor: 'rgba(220,220,220,1)',
                                    data: data2
                                },
                                {
                                    label: '{{ __('voyager::date.this_year') }}',
                                    fillColor: 'rgba(151,187,205,0.5)',
                                    strokeColor: 'rgba(151,187,205,1)',
                                    data: data1
                                }
                            ]
                        };

                        new Chart(makeCanvas('chart-2-container')).Bar(data);
                        generateLegend('legend-2-container', data.datasets);
                    })
                        .catch(function (err) {
                            console.error(err.stack);
                        });
                }


                /**
                 * Draw the a chart.js doughnut chart with data from the specified view that
                 * show the top 5 browsers over the past seven days.
                 */
                function renderTopBrowsersChart(ids) {

                    query({
                        'ids': ids,
                        'dimensions': 'ga:browser',
                        'metrics': 'ga:pageviews',
                        'sort': '-ga:pageviews',
                        'max-results': 5
                    })
                        .then(function (response) {

                            var data = [];
                            var colors = ['#4D5360', '#949FB1', '#D4CCC5', '#E2EAE9', '#F7464A'];

                            response.rows.forEach(function (row, i) {
                                data.push({value: +row[1], color: colors[i], label: row[0]});
                            });

                            new Chart(makeCanvas('chart-3-container')).Doughnut(data);
                            generateLegend('legend-3-container', data);
                        });
                }


                /**
                 * Draw the a chart.js doughnut chart with data from the specified view that
                 * compares sessions from mobile, desktop, and tablet over the past seven
                 * days.
                 */
                function renderTopCountriesChart(ids) {
                    query({
                        'ids': ids,
                        'dimensions': 'ga:country',
                        'metrics': 'ga:sessions',
                        'sort': '-ga:sessions',
                        'max-results': 5
                    })
                        .then(function (response) {

                            var data = [];
                            var colors = ['#4D5360', '#949FB1', '#D4CCC5', '#E2EAE9', '#F7464A'];

                            response.rows.forEach(function (row, i) {
                                data.push({
                                    label: row[0],
                                    value: +row[1],
                                    color: colors[i]
                                });
                            });

                            new Chart(makeCanvas('chart-4-container')).Doughnut(data);
                            generateLegend('legend-4-container', data);
                        });
                }


                /**
                 * Extend the Embed APIs `gapi.analytics.report.Data` component to
                 * return a promise the is fulfilled with the value returned by the API.
                 * @param {Object} params The request parameters.
                 * @return {Promise} A promise.
                 */
                function query(params) {
                    return new Promise(function (resolve, reject) {
                        var data = new gapi.analytics.report.Data({query: params});
                        data.once('success', function (response) {
                            resolve(response);
                        })
                            .once('error', function (response) {
                                reject(response);
                            })
                            .execute();
                    });
                }


                /**
                 * Create a new canvas inside the specified element. Set it to be the width
                 * and height of its container.
                 * @param {string} id The id attribute of the element to host the canvas.
                 * @return {RenderingContext} The 2D canvas context.
                 */
                function makeCanvas(id) {
                    var container = document.getElementById(id);
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');

                    container.innerHTML = '';
                    canvas.width = container.offsetWidth;
                    canvas.height = container.offsetHeight;
                    container.appendChild(canvas);

                    return ctx;
                }


                /**
                 * Create a visual legend inside the specified element based off of a
                 * Chart.js dataset.
                 * @param {string} id The id attribute of the element to host the legend.
                 * @param {Array.<Object>} items A list of labels and colors for the legend.
                 */
                function generateLegend(id, items) {
                    var legend = document.getElementById(id);
                    legend.innerHTML = items.map(function (item) {
                        var color = item.color || item.fillColor;
                        var label = item.label;
                        return '<li><i style="background:' + color + '"></i>' + label + '</li>';
                    }).join('');
                }


                // Set some global Chart.js defaults.
                Chart.defaults.global.animationSteps = 60;
                Chart.defaults.global.animationEasing = 'easeInOutQuart';
                Chart.defaults.global.responsive = true;
                Chart.defaults.global.maintainAspectRatio = false;

                // resize to redraw charts
                window.dispatchEvent(new Event('resize'));

            });

        </script>

    @endif
    <!--Google analytics end-->

@stop
