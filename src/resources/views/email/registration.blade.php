<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <p>Hi,</p>
                <p>Welcome to {{env('APP_NAME')}}</p>
                <p>We’re excited to have you on board. You can now access your account using the details below:</p>

                <div class="card-header"><b>Access Information</b></div>
                <div class="card-header"><b>Website: </b>{{env('APP_URL')}}</div>
                <div class="card-header"><b>Username / Email: </b>{{$username}}</div>
                <div class="card-header"><b>Password: </b>{{$password}}</div>
                <br>
                <br>
                <p>NIf you have any trouble accessing your account or need assistance, feel free to contact our support.</p>
                <p>Thanks again, and we hope you enjoy using {{env('APP_NAME')}} </p>
                <br>
                <br>
                <p>Best regards,</p>
                <p style="padding:0; margin: 0 ">{{env('APP_NAME')}}</p>
                <p style="padding:0; margin: 0 ">{{env('APP_URL')}}</p>
            </div>
        </div>
    </div>
</div>
