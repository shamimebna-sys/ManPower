@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .border-white {
            border-color: white !important;;
        }
    </style>
@stop

@section('page_title', 'Payment Requests')

@section('page_header')
    <h1 class="page-title"> <i class="voyager-double-right"></i> Payment Requests </h1>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-bordered panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="icon wb-image"></i>Payment Requests for Candidate
                        </h3>
                        <div class="panel-actions">
                            <a class="panel-action voyager-angle-down" data-toggle="panel-collapse"
                               aria-hidden="true"></a>
                        </div>
                    </div>
                    <div class="panel-body ">
                        <div class="table-responsive">
                            <table class="table table-hover datatable " id="product_table">
                                <thead>
                                <tr>
                                    <th style="width: 2%">SL.</th>
                                    <th style="width: 10%">Bill Title</th>
                                    <th style="width: 10%">Candidate</th>
                                    <th style="width: 10%">Amount</th>
                                    <th style="width: 10%">Status</th>
                                    <th style="width: 5%">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($paymentRequests)
                                    @foreach($paymentRequests as $s=>$i)
                                        <tr>
                                            <td>{{++$s}}</td>
                                            <td>{{$i->bill_title}}</td>
                                            <td>{{($i->candidate)?$i->candidate->candidate_details_info:''}} </td>
                                            <td>{{$i->amount}} </td>
                                            <td>  {!! \App\Helpers\CommonClass::getPaymentStatus($i->status)  !!}</td>
                                            <td>
                                                @if($i->status == 'P')
                                                    <a href="#" onclick="openModal('{{route("admin.ajax.candidate-payment-approval", $i->id)}}', false, 'modal-lg', 'Candidate Payment Request Approval');" title="Approve" class="btn btn-sm btn-primary pull-right ">
                                                        <i class="voyager-add"></i> <span class="hidden-xs hidden-sm">Approval</span>
                                                    </a>
                                                @elseif($i->status == 'A')
                                                    <a href="#" onclick="openModal('{{route("admin.ajax.candidate-payment-approval", $i->id)}}', false, 'modal-lg', 'Candidate Payment Request Approval');" title="Approve" class="btn btn-sm btn-success pull-right ">
                                                        <i class="voyager-add"></i> <span class="hidden-xs hidden-sm">Invoice</span>
                                                    </a>
                                                @else
                                                    <a href="#" onclick="openModal('{{route("admin.ajax.candidate-payment-approval", $i->id)}}', false, 'modal-lg', 'Candidate Payment Request Approval');" title="Approve" class="btn btn-sm btn-danger pull-right ">
                                                        <i class="voyager-add"></i> <span class="hidden-xs hidden-sm">Details</span>
                                                    </a>
                                                @endif

                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>


                </div>
            </div>

        </div>
    </div>

@stop

@section('javascript')

@stop
