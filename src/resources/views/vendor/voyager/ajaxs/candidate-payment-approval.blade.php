{{--@extends('voyager::master')

@section('content')--}}
<style>
    #myModal .modal-body {
        color: black;
    }

</style>
<div class="panel panel-bordered" style="padding-bottom:5px;">
    <form action="" method="post" id="approval_form">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-12">
                        <!-- form start -->
                        <div class="panel-heading" style="border-bottom:0;">
                            <h3 class="panel-title">Candidate</h3>
                        </div>
                        <div class="panel-body" style="padding-top:0;">
                            <p>({{$candidate->code}}) {{$candidate->name}}</p>
                        </div><!-- panel-body -->
                    </div>

                    <div class="col-md-4">
                        <div class="panel-heading" style="border-bottom:0;">
                            <h3 class="panel-title">Email</h3>
                        </div>
                        <div class="panel-body" style="padding-top:0;">
                            {{$candidate->email}}
                        </div><!-- panel-body -->
                    </div>

                    <div class="col-md-4">
                        <div class="panel-heading" style="border-bottom:0;">
                            <h3 class="panel-title">Mobile</h3>
                        </div>
                        <div class="panel-body" style="padding-top:0;">
                            <p> {{$candidate->mobile}}</p>
                        </div><!-- panel-body -->
                    </div>


                    <div class="col-md-4">
                        <div class="panel-heading" style="border-bottom:0;">
                            <h3 class="panel-title">Passport No.</h3>
                        </div>
                        <div class="panel-body" style="padding-top:0;">
                            <p> {{$candidate->passport_no}}</p>
                        </div><!-- panel-body -->
                    </div>
                </div>

            </div>

            <div class="col-md-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="panel-body" style="padding-top:0;">
                            <img src="{{Voyager::image($candidate->half_photo_file_path)}}" style="width: 200px"/>
                        </div><!-- panel-body -->
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Bill Title.</h3>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    <p> {{$paymentRequest->bill_title}}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Amount (€)</h3>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    <p> {{$paymentRequest->amount}}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title"> Status</h3>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    {!! \App\Helpers\CommonClass::getPaymentStatus($paymentRequest->status) !!}
                </div><!-- panel-body -->
            </div>
            <div class="col-md-12">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Remarks</h3>
                </div>
                <div class="panel-body form-group" style="padding-top:0;">
                    <p>
                        @if($paymentRequest->status=='P')
                            <textarea  class="form-control" name="remarks" required>{{$paymentRequest->remarks}}</textarea>
                        @else
                            {{$paymentRequest->remarks}}
                        @endif
                    </p>
                </div><!-- panel-body -->
            </div>

            @if($paymentRequest->status=='P')
                <div class="col-md-12">
                    <hr style="margin:0;">

                    <div class="panel-body" style="padding-top:0;">
                        <button type="button" class="btn btn-sm btn-primary pull-right edit" onclick="submitForm('{{$paymentRequest->id}}','A')">
                            Approved 
                        </button>
                        &nbsp;

                        <button type="button" class="btn btn-sm btn-danger pull-right edit" onclick="submitForm('{{$paymentRequest->id}}','R')">
                            Rejected
                        </button>
                    </div><!-- panel-body -->
                </div>
            @endif


        </div>
    </form>
</div><!-- table-responsive -->

{{--@stop--}}

<script>
    function submitForm(id, status){
        var data = $('#approval_form').serialize()+'&status='+status;
        $.ajax({
            url:  "{{url('/panel/ajax/candidate-payment-approval')}}/"+id,
            type:"POST",
            data: data,
            beforeSend: function() {
                $('#approval_form button[type="button"]').prop('disabled', true);
            },
            success:function(res){
                if(res.status){
                    toastr.success(res.msg);
                    $('body .close').trigger('click');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);

                }else{
                    toastr.error(res.msg);
                }
                $('#approval_form button[type="button"]').prop('disabled', false);
            },
        });
    }

</script>
