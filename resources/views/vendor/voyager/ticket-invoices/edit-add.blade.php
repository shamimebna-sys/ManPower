@extends('voyager::master')
@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp
@section('page_title', __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        #invoiceTable .form-group{
            margin: 0;
        }
    </style>
@stop

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular') }}
    </h1>
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
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> Invoice</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>

                        <div class="panel-body">
                            <div class="row">
                                <div style="display: none">
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['ticket_company_id'], 'width'=>'4'])
                                </div>

                                @if($edit)
                                    <div class="form-group  col-md-3">
                                        <label for="name">Invoice No <strong style="color: red;">*</strong></label>
                                        <input readonly type="text" class="form-control" name="invoice_no" placeholder="invoice_no" value="{{$dataTypeContent->invoice_no}}">
                                    </div>
                                @else
                                    <div class="form-group  col-md-3">
                                        <label for="name">Invoice No <strong style="color: red;">*</strong></label>
                                        @php
                                            $invoiceMaxNo = DB::table('ticket_invoices')
                                                //->where('agenciers_id', \App\Helpers\CommonClass::user()->profile_id)
                                                ->selectRaw('MAX(CAST(invoice_no AS UNSIGNED)) AS max_invoice_no')
                                                ->value('max_invoice_no');

                                            $invoiceNo = !empty($invoiceMaxNo)? $invoiceMaxNo+1: 1;
                                        @endphp
                                        <input type="number" {{!empty($invoiceMaxNo)?'readonly':''}} min="1" required class="form-control" placeholder="invoice_no" name="invoice_no"  value="{{$invoiceNo}}">

                                    </div>
                                @endif

{{--                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['invoice_no'], 'width'=>'3'])--}}


                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['invoice_date'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['status'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['currency'], 'width'=>'3'])

                                <div class="col-md-6" style="border-right: 1px solid black">
                                    <h4>Billing Information</h4>
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bill_to'], 'width'=>'6'])
                                    <div id="OTHER" style="display: {{($edit)?($dataTypeContent->bill_to == 'OTHER'?'block':'none'):'none'}}; padding: 0; margin:0">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bill_to_code'], 'width'=>'6'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bill_to_name'], 'width'=>'12'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bill_to_email'], 'width'=>'6'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bill_to_mobile'], 'width'=>'6'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bill_to_address'], 'width'=>'12'])
                                    </div>
                                    <div id="AGENCIER" style="display: {{($edit)?($dataTypeContent->bill_to == 'AGENCIER'?'block':'none'):'none'}}; padding: 0; margin:0">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['ticket_invoice_belongsto_agencier_relationship'], 'width'=>'12'])
                                    </div>
                                    <div id="AGENT" style="display: {{($edit)?($dataTypeContent->bill_to == 'AGENT'?'block':'none'):'none'}}; padding: 0; margin:0">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['ticket_invoice_belongsto_agent_relationship'], 'width'=>'12'])
                                    </div>
                                    <div id="COMPANIER" style="display: {{($edit)?($dataTypeContent->bill_to == 'AGENT'?'block':'none'):'block'}}; padding: 0; margin:0">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['ticket_invoice_belongsto_companier_relationship'], 'width'=>'12'])
                                    </div>
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bill_to_terms'], 'width'=>'12'])
                                </div>

                                <div class="col-md-6">
                                    <h4>Ticket Information</h4>
                                    @if($edit)
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['route_from'], 'width'=>'6'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['route_to'], 'width'=>'6'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['airline'], 'width'=>'6'])
                                    @else
                                        <div class="form-group  col-md-6">
                                            <label for="name">Route From</label>
                                            <input list="route_from_list" type="text" class="form-control" name="route_from" placeholder="Route From" value="">
                                            <datalist id="route_from_list">
                                                @foreach(\App\Models\TicketInvoice::select('route_from')->groupBy('route_from')->get() as $route_list)
                                                <option value="{{$route_list->route_from}}">
                                                @endforeach
                                            </datalist>
                                        </div>
                                        <div class="form-group  col-md-6">
                                            <label for="name">Route To</label>
                                            <input list="route_to_list"  type="text" class="form-control" name="route_to" placeholder="Route To" value="">
                                            <datalist id="route_to_list">
                                                @foreach(\App\Models\TicketInvoice::select('route_to')->groupBy('route_to')->get() as $route_list)
                                                    <option value="{{$route_list->route_to}}">
                                                @endforeach
                                            </datalist>
                                        </div>
                                        <div class="form-group  col-md-6">
                                            <label for="name">Airline</label>
                                            <input list="airline_list" type="text" class="form-control" name="airline" placeholder="Airline" value="">
                                            <datalist id="airline_list">
                                                @foreach(\App\Models\TicketInvoice::select('airline')->groupBy('airline')->get() as $route_list)
                                                    <option value="{{$route_list->airline}}">
                                                @endforeach
                                            </datalist>
                                        </div>
                                    @endif


                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['ticket_no'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['flight_date'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['flight_time'], 'width'=>'6'])
                                </div>



                                <div class="col-md-12">
                                    <h4>Invoice Details</h4>
                                    <table  id="invoiceTable" class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th style="width: 5%">SL</th>
                                            <th style="width: 10%">Type</th>
                                            <th>Description</th>
                                            <th style="text-align: right; width: 5%">Quantity</th>
                                            <th style="text-align: right; width: 10%">Unit Price</th>
                                            <th style="text-align: right; width: 5%">Amount</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @if($edit)


                                            @foreach($dataTypeContent->invoiceLines as $i=>$head)

                                                <tr>
                                                    <td style=" padding: 0">
                                                        <input type="hidden" name="ticket_invoice_line_id[]" value="{{$head->invoice_head_id}}">
                                                        {{$i+1   }}.
                                                    </td>
                                                    <td style=" padding: 0;">
                                                        <select class="is_candidate_ticket form-control" name="ticket_invoice_line_is_candidate[]">
                                                            <option value="CANDIDATE" {{($head->is_candidate == 'CANDIDATE')?'selected':''}}>Candidate </option>
                                                            <option value="OTHER" {{($head->is_candidate == 'OTHER')?'selected':''}}>Other</option>
                                                        </select>
                                                    </td>

                                                    <td style="padding: 0;">
                                                        <div class="candidate_ticket" style="display: {{($head->is_candidate == 'CANDIDATE')?'block !important':'none'}}">
                                                            <select
                                                                class="form-control voyager-candidate-select"
                                                                name="ticket_invoice_line_candidate_id[]"
                                                                data-url="{{url('/panel/ticket-invoices/relation')}}?type=ticket_invoice_belongsto_candidate_relationship&method=add&page=1"
                                                                style="width:100%">
                                                                {{--                                                                <option value="">-- Select --</option>--}}
                                                                <option value="{{$head->candidate_id}}">{{$head->name. ' ('.$head->passport_no.')'}}</option>
                                                            </select>
                                                        </div>
                                                        <input class="form-control other_ticket" type="text" placeholder="Full Name" name="ticket_invoice_line_name[]" value="{{ old('ticket_invoice_line_name.'.$i, $head->name) }}" style="width: 65%; float: left; display: {{($head->is_candidate == 'OTHER')?'block !important':'none'}}" >
                                                        <input class="form-control other_ticket" type="text" placeholder="Passport" name="ticket_invoice_line_passport_no[]" value="{{ old('ticket_invoice_line_passport_no'.$i, $head->passport_no) }}" style="width: 35%; float: right; display: {{($head->is_candidate == 'OTHER')?'block !important':'none'}}">
                                                    </td>
                                                    <td style="padding: 0;"><input  min="0" type="number" class="form-control line_quantity" name="ticket_invoice_line_quantity[]" value="{{ old('ticket_invoice_line_quantity.'.$i, $head->quantity) }}" style="text-align: right"></td>
                                                    <td style="padding: 0;"><input  min="0" type="number" class="form-control line_unit_price" name="ticket_invoice_line_unit_price[]" value="{{ old('ticket_invoice_line_unit_price.'.$i, $head->unit_price) }}" style="text-align: right"></td>
                                                    <td style="padding: 0"><input  type="number" readonly class="form-control line_total_amount" name="ticket_invoice_line_total_amount[]" value="{{ old('line_total_amount.'.$i ,  $head->total_amount) }}" style="text-align: right; border: none"></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            @for($i= 1; $i<=10;  $i++)
                                                <tr>
                                                    <td style=" padding: 0">
                                                        <input type="hidden" name="ticket_invoice_line_id[]" value="">
                                                        {{$i}}.
                                                    </td>
                                                    <td style=" padding: 0;">
                                                        <select class="is_candidate_ticket form-control" name="ticket_invoice_line_is_candidate[]">
                                                            <option value="CANDIDATE">Candidate</option>
                                                            <option value="OTHER">Other</option>
                                                        </select>
                                                    </td>

                                                    <td style="padding: 0;">
                                                        <div class="candidate_ticket">
                                                            <select
                                                                class="form-control voyager-candidate-select"
                                                                name="ticket_invoice_line_candidate_id[]"
                                                                data-url="{{url('/panel/ticket-invoices/relation')}}?type=ticket_invoice_belongsto_candidate_relationship&method=add&page=1"
                                                                style="width:100%">
                                                                <option value="">-- Select --</option>
                                                            </select>
                                                        </div>
                                                        <input class="form-control other_ticket" type="text" placeholder="Full Name" name="ticket_invoice_line_name[]" value="{{ old('ticket_invoice_line_name.'.$i) }}" style="width: 65%; float: left; display: none" >
                                                        <input class="form-control other_ticket" type="text" placeholder="Passport" name="ticket_invoice_line_passport_no[]" value="{{ old('ticket_invoice_line_passport_no'.$i) }}" style="width: 35%; float: right; display: none">
                                                    </td>
                                                    <td style="padding: 0;"><input  type="number" min="0" class="form-control line_quantity" name="ticket_invoice_line_quantity[]" value="{{ old('ticket_invoice_line_quantity.'.$i) }}" style="text-align: right"></td>
                                                    <td style="padding: 0;"><input  type="number" min="0" class="form-control line_unit_price" name="ticket_invoice_line_unit_price[]" value="{{ old('ticket_invoice_line_unit_price.'.$i) }}" style="text-align: right"></td>
                                                    <td style="padding: 0"><input  type="number" readonly class="form-control line_total_amount" name="ticket_invoice_line_total_amount[]" value="{{ old('ticket_invoice_line_total_amount.'.$i , '0.00') }}" style="text-align: right; border: none"></td>
                                                </tr>
                                            @endfor
                                        @endif

                                        </tbody>

                                        <tfoot>
                                        <tr class="bg-warning" style="font-weight: bolder; border: none">
                                            <td style="font-size: larger; text-align: right" colspan="5" >
                                                Subtotal
                                            </td>
                                            <td style="font-size: larger; text-align: right" >
                                                <input style=" text-align: right; background: #fcf8e3;  border: none" readonly type="number" id="subtotal" name="subtotal" value="{{($edit)?$dataTypeContent->subtotal:old('subtotal', 0)}}">
                                            </td>
                                        </tr>

                                        <tr class="bg-warning" style="font-weight: bolder; border: none">
                                            <td style="font-size: larger; text-align: right" colspan="5" >
                                                Tax Rate (%)
                                            </td>
                                            <td style="font-size: larger; text-align: right" >
                                                <input style=" text-align: right; " min="0" max="100" type="number" id="tax_rate" name="tax_rate" value="{{($edit)?$dataTypeContent->tax_rate:old('tax_rate', 0)}}">
                                            </td>
                                        </tr>

                                        <tr class="bg-warning" style="font-weight: bolder; border: none">
                                            <td style="font-size: larger; text-align: right" colspan="5" >
                                                Tax Amount
                                            </td>
                                            <td style="font-size: larger; text-align: right" >
                                                <input style=" text-align: right; background: #fcf8e3;  border: none" readonly type="number" id="tax_amount" name="tax_amount" value="{{($edit)?$dataTypeContent->tax_amount:old('tax_amount', 0)}}">
                                            </td>
                                        </tr>

                                        <tr class="bg-warning" style="font-weight: bolder; border: none">
                                            <td style="font-size: larger; text-align: right" colspan="5" >
                                                Total
                                            </td>
                                            <td style="font-size: larger; text-align: right" >
                                                <input style=" text-align: right; background: #fcf8e3;  border: none" readonly type="number" id="total" name="total" value="{{($edit)?$dataTypeContent->total:old('total', 0)}}">
                                            </td>
                                        </tr>

                                        </tfoot>
                                    </table>
                                </div>
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

    <script>
        $(document).ready(function(){
            function calculateRow(row) {
                let qty = parseFloat($(row).find(".line_quantity").val()) || 0;
                let price = parseFloat($(row).find(".line_unit_price").val()) || 0;
                let line_total_amount = qty * price;
                $(row).find(".line_total_amount").val(line_total_amount.toFixed(2));

                return {
                    'line_total_amount':line_total_amount,
                };
            }

            function calculateTotal() {
                let line_sub_total_amount = 0;
                $("#invoiceTable tbody tr").each(function(){
                    let data = calculateRow(this);
                    line_sub_total_amount += data.line_total_amount;
                });
                $("#subtotal").val(line_sub_total_amount.toFixed(2));
                var tax_rate = $("#tax_rate").val();
                var tax_amount = line_sub_total_amount.toFixed(2)*  tax_rate/100;
                $("#tax_amount").val(tax_amount.toFixed(2))
                line_sub_total_amount = line_sub_total_amount+tax_amount
                $("#total").val(line_sub_total_amount.toFixed(2));
            }

            // Trigger calculation on input change
            $(document).on("input", ".line_quantity, .line_unit_price, #tax_rate", function(){
                calculateTotal();
            });

            // Initial calculation
            calculateTotal();
        });
    </script>


    <script>
        $(document).ready(function() {
            $('.is_candidate_ticket').on('change', function() {
                const $row = $(this).closest('tr');
                if ($(this).val() == 'CANDIDATE') {
                    $row.find('.candidate_ticket').show();
                    $row.find('.other_ticket').hide();
                    $row.find('.other_ticket').val('');
                } else {
                    $row.find('.candidate_ticket').hide();
                    $row.find('.voyager-candidate-select').val(null).trigger('change');;
                    $row.find('.other_ticket').show();
                }
            });


            $('.voyager-candidate-select').select2({
                placeholder: '',
                allowClear: true,
                width: '100%',
                ajax: {
                    url: function () {
                        return $(this).data('url');
                    },
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {search: params.term};
                    },
                    processResults: function (data, params) {
                        return {
                            results: data.results,
                            pagination: data.pagination
                        };
                    }
                }
            });


            $('select[name="bill_to"]').on('change', function() {
                var billTo = $(this).val();
                $('#OTHER').hide();
                $('#AGENCIER').hide();
                $('#COMPANIER').hide();
                $('#AGENT').hide();

                $('#'+billTo).show();
            });
        });
    </script>
@stop
