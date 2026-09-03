<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <p>Dear {{$user->name}},</p>
                <p>We hope this message finds you well.</p>
                <p>This is to inform you about the following:</p>

                <div class="card-header"><b>Exam: </b>{{ ucfirst($notification->title) }}</div>

                @if(isset($notification->redirect_url))
                <div class="card-header"><b>Link: </b><a href="{{$notification->redirect_url}}">{{$notification->redirect_url}}</a> </div>
                @endif

                <div class="card-body">{!! $notification->message !!}</div>

                <br>
                <br>
                <p>If you have any questions or need assistance, feel free to contact us. </p>
                <p>Thank you for your attention. </p>
                <br>
                <br>
                <p>Best regards,</p>
                <p style="padding:0; margin: 0 ">{{env('APP_NAME')}}</p>
                <p style="padding:0; margin: 0 ">{{env('APP_URL')}}</p>
            </div>
        </div>
    </div>
</div>
