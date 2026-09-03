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
                            <li>FAQS</li>
                        </ul>
                        <h1>FAQS</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Section Start -->

    <!-- Faq Section Start -->
    <div class="faq-section section bg_color--5 pb-120 pb-lg-100 pb-md-80 pb-sm-60 pb-xs-50">
        <div class="container faq-wrapper">
            <div class="row">
                <div class="col-lg-12 col-md-12`">
                    <div class="row">

                        <div class="col-lg-6 pr-65 pr-md-15 pr-sm-15 pr-xs-15">
                            <!-- Single Faq Start -->
                            <div class="single-faq">
                                <h5>How do I find and apply for a job?</h5>
                                <p><a href="#">Create your profile first</a>. Include as much information about your skills, education and experience as possible. An employer might ask you for more information later on in the process, but you want to make sure you make this as clear and as detailed as possible. Fill out your profile completely to get better matches and stand out to employers.</p>
                                <hr>
                            </div>
                            <!-- Single Faq End -->
                            <!-- Single Faq Start -->
                            <div class="single-faq">
                                <h5>I am already registered as a user in Jopota’s database – can i apply for an open job posting?</h5>
                                <p>Yes! You can use your already existing profile to apply for a new vacancy. Simply just log in to your profile and apply for the vacancy from there.</p>
                                <hr>
                            </div>
                            <!-- Single Faq End -->
                            <!-- Single Faq Start -->
                            <div class="single-faq">
                                <h5>Who can apply for a position at Jotopa?</h5>
                                <p>At Jotopa, we embrace diversity. We encourage everyone to apply for our job openings, no matter who you are or what your background is. So if you see an opening that fits with your skills and competencies, don’t hold back.</p>
                                <hr>
                            </div>
                            <!-- Single Faq End -->
                            <!-- Single Faq Start -->
                            <div class="single-faq">
                                <h5>Where do I find available positions?</h5>
                                <p>You can find <a href="#">all job postings here.</a></p>
                            </div>
                            <!-- Single Faq End -->
                        </div>

                        <div class="col-lg-6 pl-65 pl-md-15 pl-sm-15 pl-xs-15">
                            <!-- Single Faq Start -->
                            <div class="single-faq">
                                <h5>How will EUJOBBD handle my application?</h5>
                                <p>You can apply online by using the “Apply Online” button provide for each job posting. Jopota will confirm by email that we have received your application and give you an estimated time line for finalising the application process</p>
                                <hr>
                            </div>
                            <!-- Single Faq End -->
                            <!-- Single Faq Start -->
                            <div class="single-faq">
                                <h5>When can I expect to hear from you?</h5>
                                <p>The recruitment process usually takes around 6 to 8 weeks. You will hear from us within 6 weeks.</p>
                                <hr>
                            </div>
                            <!-- Single Faq End -->
                            <!-- Single Faq Start -->
                            <div class="single-faq">
                                <h5>Do you have any guidelines for my application?</h5>
                                <p>At Jotopa, we embrace diversity. We encourage everyone to apply for our job openings, no matter who you are or what your background is.</p>
                                <ul>
                                    <li>So if you see an opening that fits with your skills and competencies, don’t hold back.</li>
                                    <li>Where do I find available positions?</li>
                                    <li>You can find all job postings here.</li>
                                </ul>
                                <hr>
                            </div>
                            <!-- Single Faq End -->
                        </div>

                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-12 pt-25 pb-15 mt-40 mt-xs-0">
                    <div class="faq-border"></div>
                    <div class="faq-contact">
                        <p>For further questions, please contact us via: <a href="#">{{env('APP_EMAIL')}}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Faq Section End -->



@stop



@section('footer')

@stop
