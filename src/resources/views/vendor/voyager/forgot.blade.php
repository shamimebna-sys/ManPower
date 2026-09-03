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

        <h3>Forget Password</h3>
        <form action="{{ route('voyager.forgot') }}" method="POST">
            {{ csrf_field() }}
            <div class="form-group form-group-default" id="emailGroup">
                <label>{{ __('voyager::generic.email') }}</label>
                <div class="controls">
                    <input type="text" name="email" id="email" value="{{ old('email') }}" placeholder="{{ __('voyager::generic.email') }}" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-block login-button">
                <span class="signingin hidden"><span class="voyager-refresh"></span> {{ __('voyager::login.loggingin') }}...</span>
                <span class="signin">Submit</span>
            </button>

            <div style="float: right; padding-top: 5%;">
                <span style=" text-transform: none;">Have account ? <a href="{{route('voyager.login')}}"> Login here.</a></span>
            </div>
        </form>

        <div style="clear:both"></div>

        @if(!$errors->isEmpty())
            <div class="alert alert-red">
                <ul class="list-unstyled">
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

        email.addEventListener('focusin', function(e){
            document.getElementById('emailGroup').classList.add("focused");
        });
        email.addEventListener('focusout', function(e){
            document.getElementById('emailGroup').classList.remove("focused");
        });

    </script>
@endsection
