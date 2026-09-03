@extends('layout')

@section('title', 'Agent Registration')

@section('header')
    @livewireStyles
@stop



@section('content')

    <!-- Breadcrumb Section Start -->
    <div class="breadcrumb-section section bg_color--5 pt-60 pt-sm-50 pt-xs-40 pb-60 pb-sm-50 pb-xs-40">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page-breadcrumb-content">
                        <ul class="page-breadcrumb">
                            <li><a href="index-2.html">Home</a></li>
                            <li>Agent Registration</li>
                        </ul>
                        <h1>Agent Registration</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Section Start -->

    <div>

        <!-- Login Register Section Start -->
        <div class="login-register-section section bg_color--5 pb-120 pb-lg-100 pb-md-80 pb-sm-60 pb-xs-50">
            <div class="container">
                <div class="row g-0">

                    <div class="col-lg-12">
                        <div class="login-register-form-area">
                            <form action="#" class="checkout-form">
                                <div class="profile-applications-main-block">
                                    <div class="profile-applications-form">
                                        <form action="#">
                                            <div class="row mb-30">
                                                <div class="col-lg-2">
                                                    <div class="profile-avatar mb-30">
                                                        <label class="d-block"><span>Avatar</span></label>
                                                        <img src="assets/images/author/author1.jpg" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-lg-10">
                                                    <div class="row">

                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                                            <!-- Single Input Start -->
                                                            <div class="single-input mb-25">
                                                                <label for="first-name">First Name <span>*</span></label>
                                                                <input type="text" id="first-name" name="first-name" placeholder="First Name" value="Jhon">
                                                            </div>
                                                            <!-- Single Input End -->
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                                            <!-- Single Input Start -->
                                                            <div class="single-input mb-25">
                                                                <label for="last-name">Last Name <span>*</span></label>
                                                                <input type="text" id="last-name" name="last-name" placeholder="Last Name" value="anna">
                                                            </div>
                                                            <!-- Single Input End -->
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                                            <!-- Single Input Start -->
                                                            <div class="single-input mb-25">
                                                                <label for="email">Email <span>*</span></label>
                                                                <input type="email" id="email" name="email" placeholder="Enter your Email" value="candidate@localhost.com">
                                                            </div>
                                                            <!-- Single Input End -->
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                                            <!-- Single Input Start -->
                                                            <div class="single-input mb-25">
                                                                <label for="url">Url <span>*</span></label>
                                                                <input type="url" id="url" name="url" placeholder="Enter your Url" value="https://bootstrap.hasthemes.com/">
                                                            </div>
                                                            <!-- Single Input End -->
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                                            <!-- Single Input Start -->
                                                            <div class="single-input mb-25">
                                                                <label for="address-one">Address line 1</label>
                                                                <input type="text" id="address-one" name="address-one" placeholder="Enter your Address" value="">
                                                            </div>
                                                            <!-- Single Input End -->
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                                            <!-- Single Input Start -->
                                                            <div class="single-input mb-25">
                                                                <label for="address-two">Address line 2</label>
                                                                <input type="text" id="address-two" name="address-two" placeholder="Enter your Address" value="">
                                                            </div>
                                                            <!-- Single Input End -->
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                                            <!-- Single Input Start -->
                                                            <div class="single-input mb-25">
                                                                <label for="new-password">New Password</label>
                                                                <input type="password" id="new-password" name="new-password" placeholder="" value="">
                                                            </div>
                                                            <!-- Single Input End -->
                                                        </div>

                                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                                                            <!-- Single Input Start -->
                                                            <div class="single-input mb-25">
                                                                <label for="confirm-password">Confirm Password</label>
                                                                <input type="password" id="confirm-password" name="confirm-password" placeholder="" value="">
                                                            </div>
                                                            <!-- Single Input End -->
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="profile-action-btn d-flex flex-wrap align-content-center justify-content-between">
                                                        <button class="ht-btn theme-btn theme-btn-two mb-xs-20">Update Profile</button>
                                                        <button class="ht-btn theme-btn theme-btn-two transparent-btn-two">Delete Account</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </form>



                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- Login Register Section End -->
    </div>




@stop



@section('footer')
    @livewireScripts
@stop
