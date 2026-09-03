@extends('layout')

@section('title', 'Login')

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
                            <li>Login</li>
                        </ul>
                        <h1>Login</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Section Start -->

    @livewire('login')



@stop



@section('footer')
    @livewireScripts
@stop
