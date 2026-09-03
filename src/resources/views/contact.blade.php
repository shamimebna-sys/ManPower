@extends('layout')

@section('title', 'FAQ')


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
                            <li>Contact Us</li>
                        </ul>
                        <h1>Contact Us</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Section Start -->

    <!-- Contact Section Start -->
    <div class="contact-section section bg_color--5 pb-120 pb-lg-100 pb-md-80 pb-sm-60 pb-xs-50">
        <div class="container contact-wrapper">
            <div class="row row-30">

                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Map Area Start -->
                            <div class="contact-info mb-30">
                                <h2 class="title">Location</h2>
                                <div class="contact-map-area">
                                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d58411.561045817354!2d90.35506521810623!3d23.792891789122375!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c70030f72dad%3A0xb1e04b25c323f66e!2sBangladesh%20National%20Parliament%20House!5e0!3m2!1sen!2sbd!4v1753424221292!5m2!1sen!2sbd" width="650" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>   </div>
                            </div>
                            <!-- Map Area End -->
                        </div>

                        <div class="col-lg-12">
                            <!-- Contact info Start -->
                            <div class="contact-info mb-30">
                                <h2 class="title">Contact info</h2>
                                <div class="contact-information">
                                    <div class="info">
                                        <span class="title">Email:</span>
                                        <span class="text">{{env('APP_EMAIL')}}</span>
                                    </div>
                                    <div class="info">
                                        <span class="title">Phone:</span>
                                        <span class="text phone">{{env('APP_PHONE')}}</span>
                                    </div>
                                    <div class="info">
                                        <span class="title">Address:</span>
                                        <span class="text">{{env('APP_ADDRESS')}}</span>
                                    </div>
                                    <div class="info">
                                        <span class="title">Follow us:</span>
                                        <ul class="social-icon">
                                            <li><a class="facebook" href="#"><i class="fab fa-facebook"></i></a></li>
                                            <li><a class="twitter" href="#"><i class="fab fa-twitter"></i></a></li>
                                            <li><a class="linkedin" href="#"><i class="fab fa-linkedin"></i></a></li>
                                            <li><a class="youtube" href="#"><i class="fab fa-youtube"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- Contact info End -->
                        </div>
                    </div>
                </div>


                <div class="col-lg-4">
                    <!-- Contact info Start -->
                    <div class="contact-info mb-30">
                        <h2 class="title">Contact form</h2>
                        <div class="contact-form">
                            <form action="https://htmldemo.net/jetapo/jetapo/assets/php/contact-mail.php" id="contact-form" method="post">
                                <p>Please send us a message by filling out the form below and we will get back with you</p>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="single-input">
                                            <input type="text" placeholder="Your Name *" name="name">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="single-input">
                                            <input type="email" placeholder="Email *" name="email">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="single-input">
                                            <textarea name="message" placeholder="Message"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-40">
                                        <button class="ht-btn">Send</button>
                                        <p class="form-messege"></p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Contact info End -->
                </div>

            </div>
        </div>
    </div>
    <!-- Contact Section End -->



@stop



@section('footer')

@stop
