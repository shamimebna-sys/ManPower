@extends('voyager::master')
@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp
@section('page_title', __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_header')
    <h1 class="page-title" style="float: left">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular') }}

    </h1>
    <h1 class="page-title" style="float: right">Invoice No.:{{$invoice->invoice_no}} &nbsp; Invoice Date:{{$invoice->invoice_date}}</h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content container-fluid">
        <form class="form-edit-add" role="form"
              action="@if(!is_null($dataTypeContent->getKey())){{ route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) }}@else{{ route('voyager.'.$dataType->slug.'.store') }}@endif"
              method="POST" enctype="multipart/form-data" autocomplete="off">
            <!-- PUT Method if we are editing -->
            @if(isset($dataTypeContent->id))
                {{ method_field("PUT") }}
            @endif
            {{ csrf_field() }}

            @if (count($errors) > 0)
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-bordered panel-primary">

                        <div class="panel-body">
                            <div class="row">
                                <div style="display: none">
                                    <input type="text"   required class="form-control" name="ticket_invoice_id"  value="{{$invoice->id}}">
                                </div>

                                <div class="form-group  col-md-4">
                                    <label for="name">Receipt No <strong style="color: red;">*</strong></label>
                                    @php
                                       /* $maxReceiptNo = DB::table('ticket_invoice_money_receipts')
                                            ->join('ticket_invoices', 'ticket_invoices.id', '=', 'ticket_invoice_money_receipts.invoice_id')
//                                            ->where('invoices.agenciers_id', \App\Helpers\CommonClass::user()->profile_id)
    //                                        ->where('invoices.agenciers_id', 1)
                                          //  ->max('invoice_money_receipts.receipt_no');
                                          ->selectRaw('MAX(CAST(invoice_money_receipts.receipt_no AS UNSIGNED)) AS max_invoice_no')
                                            ->value('max_invoice_no');*/

                                             $maxReceiptNo = DB::table('ticket_invoice_money_receipts')
                                            ->join('ticket_invoices', 'ticket_invoices.id', '=', 'ticket_invoice_money_receipts.ticket_invoice_id')
                                            ->selectRaw('MAX(CAST(ticket_invoice_money_receipts.receipt_no AS UNSIGNED)) AS max_invoice_no')
                                            ->value('max_invoice_no');

                                        $receiptNo = !empty($maxReceiptNo)? $maxReceiptNo+1: 1;

                                    @endphp
                                    <input type="number" {{!empty($maxReceiptNo)?'readonly':''}} min="1" required class="form-control" name="receipt_no"  value="{{$receiptNo}}">
                                </div>

                                <div class="form-group  col-md-4">
                                    <label for="name">Amount <strong style="color: red;">*</strong></label>
                                    <input readonly type="text" class="form-control" name="amount" placeholder="amount" value="{{$invoice->total}}">
                                </div>

                                @if(!empty($invoice->bill_to_name))
                                    <div class="form-group  col-md-4">
                                        <label for="name">Receive From <strong style="color: red;">*</strong></label>
                                        <input readonly type="text" class="form-control" name="received_from" placeholder="received_from" value="{{$invoice->bill_to_name}}">
                                    </div>
                                @else
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['received_from'], 'width'=>'4'])
                                @endif

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['hotel'], 'width'=>'4'])


                                {{--@if(isset($invoice->service) && !empty($invoice->service))
                                    <div class="form-group  col-md-4">
                                        <label for="name">Receive For <strong style="color: red;">*</strong></label>
                                        <input readonly type="text" class="form-control" name="received_for" placeholder="received_for" value="{{$invoice->service}}">
                                    </div>
                                @else
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['received_for'], 'width'=>'4'])
                                @endif--}}
                                <div class="form-group  col-md-4">
                                    <label for="name">Receive For <strong style="color: red;">*</strong></label>
                                    <input readonly type="text" class="form-control" name="received_for" placeholder="received_for" value="Air Ticket">
                                </div>



                                <div class="form-group  col-md-4">
                                    <label for="name">Receipt Date <strong style="color: red;">*</strong></label>
                                    {{--@php
                                        $totalAmount = 0;
                                        foreach($invoice->invoiceLines as $v){
                                            $totalAmount = $totalAmount + $v->total_amount;
                                        }
                                    @endphp--}}
                                    <input type="date" class="form-control" name="receipt_date" placeholder="receipt_date" value="{{date('Y-m-d')}}">
                                </div>

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['recipient'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['payment_method'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['transaction_no'], 'width'=>'4'])

                                <button type="submit" class="btn btn-primary pull-right save">
                                    {{ __('voyager::generic.save') }}
                                </button>

                            </div>
                        </div>


                    </div>

                </div>



            </div>


        </form>
        <div style="display:none">
            <input type="hidden" id="upload_url" value="{{ route('voyager.upload') }}">
            <input type="hidden" id="upload_type_slug" value="{{ $dataType->slug }}">
        </div>
    </div>
@stop

@section('javascript')
    <script>
        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();
        });
    </script>

@stop
