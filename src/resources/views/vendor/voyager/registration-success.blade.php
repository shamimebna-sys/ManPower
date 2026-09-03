@extends('voyager::auth.master')
@section('pre_css')
@endsection

@section('content')
    <div class="login-container" style="top: 20% !important;">
        <div style="text-align: center; color: darkblue; padding-bottom: 10%">
            <a href="{{route('voyager.login')}}">
            <h2>{{ Voyager::setting('admin.title', 'Voyager') }}</h2>
            </a>
            <h4 style="text-align: center">{{ Voyager::setting('admin.description', __('voyager::login.welcome')) }}</h4>
        </div>


        <div style="clear:both"></div>
        <div class="alert alert-secondary" style="text-align: center; ">
            <div style="border-radius:200px; height:200px; width:200px; background: #F8FAF5; margin:0 auto; text-align: center">
                <i class="checkmark" style="color: #9ABC66;  font-size: 100px;  line-height: 200px; margin-left:-15px;">✓</i>
            </div>
            <h3 class="text-success">Success</h3>
            <h6 class="text-center text-info">Email has been sent to your email. </h6>
            <h6>Please check email <b class="text-danger">inbox</b> & <b class="text-danger">spam</b> for further access.</h6>

            <a href="{{route('voyager.login')}}"><button class="btn btn-primary">Login Now</button></a>
        </div>



        <div class="alert alert-info">
            <ul class="list-unstyled" style="font-weight: bolder;">
                <li>For support-</li>
                <li>Phone: {{env('APP_PHONE')}} </li>
                <li>Email:  <a href="{{url('/')}}#contact">{{env('APP_EMAIL')}} </a> </li>
            </ul>
        </div>


    </div> <!-- .login-container -->
@endsection

@section('post_js')

@endsection
