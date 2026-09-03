@extends('layout')

@section('title', $form.' Registration ')

@section('header')
    @livewireStyles
@stop



@section('content')
    <div class="page-banner-section section">
        <div class="banner-image">
            <img src="{{asset('assets/images/about/bg-top-about-us.jpg')}}" alt="">
        </div>
    </div>


    <div class="breadcrumb-section section pt-60 pt-sm-50 pt-xs-40 pb-60 pb-sm-50 pb-xs-40">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page-breadcrumb-content">
                        <ul class="page-breadcrumb">
                            <li><a href="{{url('/')}}">Home</a></li>
                            <li>Become A {{$form}}</li>
                        </ul>
                        <h1>{{$form}} Registration</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="about-content-section section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="about-content">
                        @livewire('agent-registration')
                    </div>
            </div>
        </div>
    </div>


@stop



@section('footer')
    @livewireScripts
@stop
