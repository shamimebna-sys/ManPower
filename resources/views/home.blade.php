@extends('layout')

@section('title', 'Home')


@section('header')

    <style>
        .display-none{
            display:none !important;
        }
    </style>

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


                        </div>
                        <!--Hero Content end-->

                    </div>
                </div>
            </div>
        </div>
        <!--Hero Item end-->
    </div>
    <!--slider section end-->


    <!-- Blog Section Start  -->
    <div class="blog-section section pt-115 pt-lg-95 pt-md-75 pt-sm-55 pt-xs-45 pb-90 pb-lg-70 pb-md-50 pb-sm-30 pb-xs-20">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-wrap mb-45">
                        <div class="section-title">
                            <span> FROM OUR BLOG</span>
                            <h3 class="title"> Latest Post</h3>
                        </div>
                        {{--<div class="jetapo-link">
                            <a href="#"> Browse All Articles <i class="lnr lnr-chevron-right"></i></a>
                        </div>--}}
                    </div>
                </div>
            </div>
            <div class="row">

                @foreach($blog as $post)
                <div class="col-lg-4 col-md-6 mb-30">
                    <!-- Single Blog Start -->
                    <div class="single-blog">
                        <div class="blog-image">
                            <a href="#">
                                <img src="{{Voyager::image($post->image)}}" alt="">
                            </a>
                            {{--<div class="blog-cat">
                                <a href="#" rel="category tag">Job Skills</a>
                            </div>--}}
                        </div>
                        <div class="blog-content">
                            <h4 class="title">
                                <a href="#">{{$post->title}}</a>
                            </h4>
                            <div class="blog-meta">
                                @if($post->authorId)
                                <p class="blog-author">
                                    <i class="lnr lnr-user"></i>
                                    <span class="text">Posted by:</span>
                                    <span class="author">{{$post->authorId->name}}</span>
                                </p>
                                @endif
                                <p class="blog-date-post">
                                    <i class="lnr lnr-clock"></i>{{date('M d, Y', strtotime($post->created_at))}}
                                </p>
                            </div>
                            <p class="blog-desc">
                               {{$post->excerpt}}
                            </p>
                            <a href="#" class="read-more">Read More <i class="lnr lnr-chevron-right"></i></a>
                        </div>
                    </div>
                    <!-- Single Blog End -->
                </div>
                @endforeach

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
                            <span class="text theme-color">Success JOB</span>
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
{{--                            <a class="ht-btn lg-btn" href="#"><i class="lnr lnr-cloud-upload"></i>Upload Your Resume</a>--}}
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
