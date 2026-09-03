@extends('voyager::auth.master')
@section('pre_css')

@endsection

@section('content')
    <div class="login-container" style="top: 20% !important;">
        <div style="text-align: center; color: darkblue; padding-bottom: 10%">
           {{-- <?php $admin_logo_img = Voyager::setting('admin.icon_image', ''); ?>
            @if($admin_logo_img == '')
                <img style="max-width: 150px;" class="img-responsive pull-center flip logo hidden-xs animated fadeIn" src="{{ voyager_asset('images/logo-icon-light.png') }}" alt="Logo Icon">
            @else
                <img  style="max-width: 150px;" class="img-responsive pull-center flip logo hidden-xs animated fadeIn" src="{{ Voyager::image($admin_logo_img) }}" alt="Logo Icon">
            @endif--}}
            <a href="{{route('voyager.login')}}">
                <h2>{{ Voyager::setting('admin.title', 'Voyager') }}</h2>
            </a>
            <h4 style="text-align: center">{{ Voyager::setting('admin.description', __('voyager::login.welcome')) }}</h4>
        </div>

        <h3>Login</h3>
        <form action="{{ route('voyager.login') }}" method="POST">
            {{ csrf_field() }}
            <div class="form-group form-group-default" id="emailGroup">
                <label>{{ __('voyager::generic.email') }}</label>
                <div class="controls">
                    <input type="text" name="email" id="email" value="{{ old('email') }}" placeholder="{{ __('voyager::generic.email') }}" class="form-control" required>
                </div>
            </div>

            <div class="form-group form-group-default" id="passwordGroup">
                <label>{{ __('voyager::generic.password') }}</label>
                <div class="controls">
                    <input type="password" name="password" placeholder="{{ __('voyager::generic.password') }}" class="form-control" required>
                </div>
            </div>

            <div class="form-group" id="rememberMeGroup">
                <div class="controls">
                    <input type="checkbox" name="remember" id="remember" value="1"><label for="remember" class="remember-me-text">{{ __('voyager::generic.remember_me') }}</label>
                    <span style=" text-transform: none; float: right; color: red">Forget Password ? <a href="{{route('voyager.forgot')}}"> Click here.</a></span>
                </div>
            </div>

            <button type="submit" class="btn btn-block login-button">
                <span class="signingin hidden"><span class="voyager-refresh"></span> {{ __('voyager::login.loggingin') }}...</span>
                <span class="signin">{{ __('voyager::generic.login') }}</span>
            </button>

            <div style="float: right; padding-top: 5%; display: none">
                <span style=" text-transform: none;">Don't have account ? <a href="{{route('voyager.registration')}}"> Register here.</a></span>
            </div>
        </form>

        <div style="clear:both"></div>

        @if(!$errors->isEmpty())
            <div class="alert alert-red">
                <ul class="list-unstyled" style="font-weight: bolder;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif




    </div> <!-- .login-container -->
@endsection

@section('post_js')

    <script>
        var btn = document.querySelector('button[type="submit"]');
        var form = document.forms[0];
        var email = document.querySelector('[name="email"]');
        var password = document.querySelector('[name="password"]');
        btn.addEventListener('click', function(ev){
            if (form.checkValidity()) {
                btn.querySelector('.signingin').className = 'signingin';
                btn.querySelector('.signin').className = 'signin hidden';
            } else {
                ev.preventDefault();
            }
        });
        email.focus();
        document.getElementById('emailGroup').classList.add("focused");

        // Focus events for email and password fields
        email.addEventListener('focusin', function(e){
            document.getElementById('emailGroup').classList.add("focused");
        });
        email.addEventListener('focusout', function(e){
            document.getElementById('emailGroup').classList.remove("focused");
        });

        password.addEventListener('focusin', function(e){
            document.getElementById('passwordGroup').classList.add("focused");
        });
        password.addEventListener('focusout', function(e){
            document.getElementById('passwordGroup').classList.remove("focused");
        });

    </script>
@endsection
