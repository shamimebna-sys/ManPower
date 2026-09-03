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

            <div class="col-md-6">
                <!-- form start -->
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">{{(\App\Helpers\CommonClass::user()->role_id == 101)?'Sub':''}} Agent</h3>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    @if(\App\Helpers\CommonClass::user()->role_id == 101)
                        <p>({{$data->subAgent->code}}) {{$data->subAgent->name}}</p>
                        @else
                        <p>({{$data->agent_code}}) {{$data->agent_name}}</p>
                    @endif
                </div><!-- panel-body -->
                <hr style="margin:0;">
            </div>

            <div class="col-md-6">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Title</h3>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    <p>{{$data->title}} </p>
                </div><!-- panel-body -->
                <hr style="margin:0;">
            </div>


            <div class="col-md-4">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Payment At</h3>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    {{$data->payment_date}}   {{$data->payment_time}}
                </div><!-- panel-body -->
                <hr style="margin:0;">
            </div>

            <div class="col-md-4">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Invoice No</h3>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    <p> {{$data->invoice_no}}</p>
                </div><!-- panel-body -->
                <hr style="margin:0;">
            </div>

            @if($data->trx_no)
                <div class="col-md-4">
                    <div class="panel-heading" style="border-bottom:0;">
                        <h3 class="panel-title">Trx No</h3>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <p> {{$data->trx_no}}</p>
                    </div><!-- panel-body -->
                    <hr style="margin:0;">
                </div>
            @endif

            @if($data->payment_method=='CASH')
                <div class="col-md-4">
                    <div class="panel-heading" style="border-bottom:0;">
                        <h3 class="panel-title">Received By</h3>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <p> {{$data->payment_receiver}}</p>
                    </div><!-- panel-body -->
                    <hr style="margin:0;">
                </div>
                <div class="col-md-4">
                    <div class="panel-heading" style="border-bottom:0;">
                        <h3 class="panel-title">Payment Location</h3>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <p> {{$data->payment_received_location}}</p>
                    </div><!-- panel-body -->
                    <hr style="margin:0;">
                </div>
            @else
                <div class="col-md-4">
                    <div class="panel-heading" style="border-bottom:0;">
                        <h3 class="panel-title">Bank Name</h3>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <p> {{$data->bank_name}}</p>
                    </div><!-- panel-body -->
                    <hr style="margin:0;">
                </div>
                <div class="col-md-4">
                    <div class="panel-heading" style="border-bottom:0;">
                        <h3 class="panel-title">A\C Name</h3>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <p> {{!empty($data->account_name)?$data->account_name:'N/A'}}</p>
                    </div><!-- panel-body -->
                    <hr style="margin:0;">
                </div>
                <div class="col-md-4">
                    <div class="panel-heading" style="border-bottom:0;">
                        <h3 class="panel-title">A\C No.</h3>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <p> {{$data->acc_no}}</p>
                    </div><!-- panel-body -->
                    <hr style="margin:0;">
                </div>
            @endif
            <div class="col-md-3">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Document</h3>
                </div>

                <div class="panel-body" style="padding-top:0;">
                    <a target="_blank" href="{{Voyager::image($data->cheque_file_path)}}">
                        Download
                    </a>
                </div><!-- panel-body -->
                <hr style="margin:0;">
            </div>

            <div class="col-md-4">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Amount</h3>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    <p> {{\App\Helpers\CommonClass::currencySymbol(true, $data->currency_id, $data->amount)}}</p>
                </div><!-- panel-body -->
                <hr style="margin:0;">
            </div>

            <div class="col-md-4">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Status</h3>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    {!! \App\Helpers\CommonClass::getPaymentStatus($data->status) !!}
                </div><!-- panel-body -->
                <hr style="margin:0;">
            </div>
            <div class="col-md-12">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Description</h3>
                </div>
                <div class="panel-body" style="padding-top:0;">
                    {!! $data->description !!}
                </div><!-- panel-body -->
                <hr style="margin:0;">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6" style="display: {{($data->currency_id==2)?'none':'block'}}">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Exchange Rate
                        <span class="text-danger">({{\App\Helpers\CommonClass::currencySymbol(false, 2, 1)}} = ? {{\App\Helpers\CommonClass::currencySymbol(false, $data->currency_id, null)}})</span>
                    </h3>
                </div>
                <div class="panel-body form-group" style="padding-top:0;">
                    <p>
                        @if($data->status=='P')
                            <input type="text" class="form-control" name="exchange_rate" required value="{{($data->currency_id !=2 )?'':$data->exchange_rate}}">
                        @else
                            {{$data->exchange_rate}}
                        @endif
                    </p>
                </div><!-- panel-body -->
            </div>

            <div class="col-md-6">
                <div class="panel-heading" style="border-bottom:0;">
                    <h3 class="panel-title">Remarks</h3>
                </div>
                <div class="panel-body form-group" style="padding-top:0;">
                    <p>
                        @if($data->status=='P')
                            <input type="text" class="form-control" name="remarks" required value="{{$data->remarks}}">
                        @else
                            {{$data->remarks}}
                        @endif
                    </p>
                </div><!-- panel-body -->
            </div>


            @if($data->status=='P' && !in_array(\App\Helpers\CommonClass::user()->role_id, [1011, 109]))
                <div class="col-md-12">
                    <div class="panel-body" style="padding-top:0;">
                        <button type="button" class="btn btn-sm btn-primary pull-right edit" onclick="submitForm('{{$data->id}}','A')">
                            Approved
                        </button>
                        &nbsp;

                        <button type="button" class="btn btn-sm btn-danger pull-right edit" onclick="submitForm('{{$data->id}}','R')">
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
            url:  "{{url('/panel/ajax/payment-approval')}}/"+id,
            type:"POST",
            data: data,
            beforeSend: function() {
                $('#approval_form button[type="button"]').prop('disabled', true);
            },
            success:function(res){
                if(res.status){
                    toastr.success(res.msg);
                    $('body .close').trigger('click');

                }else{
                    toastr.error(res.msg);
                }
                $('#approval_form button[type="button"]').prop('disabled', false);
            },
        });
    }

</script>
