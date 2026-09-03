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

        <h3>Registration</h3>
        <form action="{{ route('voyager.registration') }}" method="POST">
            {{ csrf_field() }}
            <div class="form-group form-group-default" id="businessNameGroup">
                <label>Business Name</label>
                <div class="controls">
                    <input type="text" name="business_name" id="business_name" value="{{ old('business_name') }}" placeholder="Business Name" class="form-control" required>
                </div>
            </div>

            <div class="input-group form-group form-group-default"  id="subdomainGroup">
                <label>Subdomain</label>
                <input type="text" name="sub_domain" id="sub_domain" value="{{ old('sub_domain') }}" placeholder="domain" class="form-control " required>
                <span class="input-group-addon" style="background-color: unset; border: none;">.{{env('APP_NAME')}}</span>
            </div>


            <div class="form-group form-group-default" id="emailGroup">
                <label>{{ __('voyager::generic.email') }}</label>
                <div class="controls">
                    <input type="text" name="email" id="email" value="{{ old('email') }}" placeholder="{{ __('voyager::generic.email') }}" class="form-control" required>
                </div>
            </div>

            <div class="form-group form-group-default" id="mobileGroup">
                <label>Mobile</label>
                <div class="controls">
                    <input type="text" name="mobile" id="mobile" value="{{ old('mobile') }}" placeholder="Mobile" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-block login-button">
                <span class="signingin hidden"><span class="voyager-refresh"></span> processing...</span>
                <span class="signin">Register</span>
            </button>

            <div style="float: right; padding-top: 5%;">
                <span style=" text-transform: none;">Have account ? <a href="{{route('voyager.login')}}"> Login now.</a></span>
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
        var business_name = document.querySelector('[name="business_name"]');
        var sub_domain = document.querySelector('[name="sub_domain"]');
        var email = document.querySelector('[name="email"]');
        var mobile = document.querySelector('[name="mobile"]');
        btn.addEventListener('click', function(ev){
            if (form.checkValidity()) {
                btn.querySelector('.signingin').className = 'signingin';
                btn.querySelector('.signin').className = 'signin hidden';
            } else {
                ev.preventDefault();
            }
        });
        business_name.focus();
        document.getElementById('businessNameGroup').classList.add("focused");

        business_name.addEventListener('focusin', function(e){
            document.getElementById('businessNameGroup').classList.add("focused");
        });
        business_name.addEventListener('focusout', function(e){
            document.getElementById('businessNameGroup').classList.remove("focused");
        });

        sub_domain.addEventListener('focusin', function(e){
            document.getElementById('subdomainGroup').classList.add("focused");
        });
        sub_domain.addEventListener('focusout', function(e){
            document.getElementById('subdomainGroup').classList.remove("focused");
        });

        email.addEventListener('focusin', function(e){
            document.getElementById('emailGroup').classList.add("focused");
        });
        email.addEventListener('focusout', function(e){
            document.getElementById('emailGroup').classList.remove("focused");
        });

        mobile.addEventListener('focusin', function(e){
            document.getElementById('mobileGroup').classList.add("focused");
        });
        mobile.addEventListener('focusout', function(e){
            document.getElementById('mobileGroup').classList.remove("focused");
        });

    </script>
@endsection
