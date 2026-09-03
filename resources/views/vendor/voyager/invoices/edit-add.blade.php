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

                                </div>
                                @if($edit)
                                    <div class="form-group  col-md-4">
                                        <label for="name">Invoice No <strong style="color: red;">*</strong></label>
                                        <input readonly type="text" class="form-control" name="invoice_no" placeholder="invoice_no" value="{{$dataTypeContent->invoice_no}}">
                                    </div>
                                @else
                                    <div class="form-group  col-md-4">
                                        <label for="name">Invoice No <strong style="color: red;">*</strong></label>
{{--                                        <input readonly type="text" class="form-control" name="invoice_no" placeholder="invoice_no" value="{{\App\Helpers\CommonClass::invoiceNo()}}">--}}

                                        @php
                                            /*$invoiceMaxNo = DB::table('invoices')
                                                ->where('agenciers_id', \App\Helpers\CommonClass::user()->profile_id)
                                                ->max('invoice_no');*/

                                            $invoiceMaxNo = DB::table('invoices')
                                                ->where('agenciers_id', \App\Helpers\CommonClass::user()->profile_id)
                                                ->selectRaw('MAX(CAST(invoice_no AS UNSIGNED)) AS max_invoice_no')
                                                ->value('max_invoice_no');

                                            $invoiceNo = !empty($invoiceMaxNo)? $invoiceMaxNo+1: 1;
                                        @endphp
                                        <input type="number" {{!empty($invoiceMaxNo)?'readonly':''}} min="1" required class="form-control" placeholder="invoice_no" name="invoice_no"  value="{{$invoiceNo}}">

                                    </div>
                                @endif

                                @if(\App\Helpers\CommonClass::user()->role_id == 1)
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['invoice_belongsto_agencier_relationship'], 'width'=>'4'])
                                @else
{{--                                    @dd(\App\Helpers\CommonClass::user())--}}
                                    <input type="hidden" name="agenciers_id" value="{{\App\Helpers\CommonClass::user()->agencier_id}}">
                                @endif

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['issue_date'], 'width'=>'4'])

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['invoice_belongsto_companier_relationship'], 'width'=>'4'])

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['owner_name'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['service'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['status'], 'width'=>'4'])

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['invoice_belongsto_country_relationship'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['invoice_belongstomany_candidate_relationship'], 'width'=>'12'])

                               {{-- <div class="form-group  col-md-12">
                                    <label for="invoice_belongstomany_candidate_relationship[]">Candidates</label>
                                    <select class="form-control select2-ajax taggable select2-hidden-accessible"
                                            id="invoice_belongstomany_candidate_relationship"
                                            name="invoice_belongstomany_candidate_relationship[]"
                                            multiple=""
                                            data-get-items-route="https://eujobbd.com/panel/invoices/relation"
                                            data-get-items-field="invoice_belongstomany_candidate_relationship"
                                            data-method="add" data-route="https://eujobbd.com/panel/candidates"
                                            data-label="candidate_details_info"
                                            data-error-message="Sorry it appears there may have been a problem creating the record. Please make sure your table has defaults for other fields."
                                            required=""
                                            data-select2-id="8"
                                            tabindex="-1"
                                            aria-hidden="true">
                                    </select>
                                </div>--}}

                                <div class="col-md-12" style="display:none">
                                    <h4>Bank Details</h4>
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_account_name'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_account_no'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_name'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_branch_name'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_iban_no'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_swift_no'], 'width'=>'4'])
                                </div>


                                <div class="col-md-12">
                                    <h4>Invoice Details</h4>
                                    <table  id="invoiceTable" class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th style="width: 50%">Description</th>
                                            <th style="text-align: right">Quantity</th>
                                            <th style="text-align: right">Unit Price</th>
                                            <th style="text-align: right">Sub Total</th>
                                            <th style="text-align: right">VAT Rate(%)</th>
                                            <th style="text-align: right">VAT Amount</th>
                                            <th style="text-align: right">Sub Total</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @if($edit)
                                            @foreach($dataTypeContent->invoiceLines as $i=>$head)
                                                <tr>
                                                    <td style=" padding: 0">
                                                        <input type="hidden" name="line_head_id[]" value="{{$head->invoice_head_id}}">
                                                        <input type="hidden" name="line_id[]" value="{{$head->id}}">
                                                        <input type="hidden" name="line_description[]" value="{{$head->description}}">
                                                        {{++$i}}. {{$head->description}}
                                                    </td>
                                                    <td style="padding: 0;"><input  type="number" class="line_quantity" name="line_quantity[]" value="{{$head->quantity}}" style="text-align: right"></td>
                                                    <td style="padding: 0;"><input  type="number" class="line_unit_price" name="line_unit_price[]" value="{{$head->unit_price}}" style="text-align: right"></td>
                                                    <td style="padding: 0"><input  type="number" readonly class="line_sub_total_amount" name="line_sub_total_amount[]" value="{{$head->sub_total_amount}}" style="text-align: right; border: none"></td>
                                                    <td style="padding: 0"><input  type="number" readonly class="line_vat_rate" name="line_vat_rate[]" value="{{$head->vat_rate}}" style="text-align: right; border: none"></td>
                                                    <td style="padding: 0"><input  type="number" readonly class="line_vat_amount" name="line_vat_amount[]" value="{{$head->vat_amount}}" style="text-align: right; border: none"></td>
                                                    <td style="padding: 0"><input  type="number" readonly class="line_total_amount" name="line_total_amount[]" value="{{$head->total_amount}}" style="text-align: right; border: none"></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            @foreach(\App\Models\InvoiceHead::where('status', 'A')->get() as $i=>$head)
                                                <tr>
                                                    <td style=" padding: 0">
                                                        <input type="hidden" name="line_head_id[]" value="{{$head->id}}">
                                                        <input type="hidden" name="line_id[]" value="">
                                                        <input type="hidden" name="line_description[]" value="{{$head->description}}">
                                                        {{++$i}}. {{$head->description}}
                                                    </td>
                                                    <td style="padding: 0;"><input  type="number" class="line_quantity" name="line_quantity[]" value="{{ old('line_quantity.' . $i) }}" style="text-align: right"></td>
                                                    <td style="padding: 0;"><input  type="number" class="line_unit_price" name="line_unit_price[]" value="{{ old('line_unit_price.' . $i) }}" style="text-align: right"></td>
                                                    <td style="padding: 0"><input  type="number" readonly class="line_sub_total_amount" name="line_sub_total_amount[]" value="{{ old('line_sub_total_amount.' . $i, '0.00') }}" style="text-align: right; border: none"></td>
                                                    <td style="padding: 0"><input  type="number" readonly class="line_vat_rate" name="line_vat_rate[]" value="{{$head->vat_rate}}" style="text-align: right; border: none"></td>
                                                    <td style="padding: 0"><input  type="number" readonly class="line_vat_amount" name="line_vat_amount[]" value="{{ old('line_vat_amount.' . $i, '0.00') }}" style="text-align: right; border: none"></td>
                                                    <td style="padding: 0"><input  type="number" readonly class="line_total_amount" name="line_total_amount[]" value="{{ old('line_total_amount.' . $i, '0.00') }}" style="text-align: right; border: none"></td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        </tbody>

                                        <tfoot>
                                        <tr class="bg-warning" style="font-weight: bolder;">
                                            <td style="font-size: larger; text-align: right" ></td>
                                            <td style="font-size: larger; text-align: right" ></td>
                                            <td style="font-size: larger; text-align: right" >Total:</td>
                                            <td style="font-size: larger; text-align: right"><span id="line_sub_total_amount"></span></td>
                                            <td style="font-size: larger; text-align: right"></td>
                                            <td style="font-size: larger; text-align: right" ><span id="line_vat_amount"></span></td>
                                            <td style="font-size: larger; text-align: right" ><span id="line_total_amount"></span></td>
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
                let line_sub_total_amount = qty * price;
                $(row).find(".line_sub_total_amount").val(line_sub_total_amount.toFixed(2));

                let line_vat_rate = parseFloat($(row).find(".line_vat_rate").val()) || 0;
                let line_vat_amount = parseFloat(line_sub_total_amount * line_vat_rate/100) || 0;
                $(row).find(".line_vat_amount").val(line_vat_amount.toFixed(2));

                let line_total_amount = line_sub_total_amount+line_vat_amount;
                $(row).find(".line_total_amount").val(line_total_amount.toFixed(2));



                return {
                    'line_sub_total_amount':line_sub_total_amount,
                    'line_vat_amount':line_vat_amount,
                    'line_total_amount':line_total_amount,
                };
            }

            function calculateTotal() {
                let line_sub_total_amount = 0;
                let line_vat_amount = 0;
                let line_total_amount = 0;
                $("#invoiceTable tbody tr").each(function(){
                    let data = calculateRow(this);
                    line_sub_total_amount += data.line_sub_total_amount;
                    line_vat_amount += data.line_vat_amount;
                    line_total_amount += data.line_total_amount;
                });
                $("#line_sub_total_amount").html(line_sub_total_amount.toFixed(2));
                $("#line_vat_amount").html(line_vat_amount.toFixed(2));
                $("#line_total_amount").html(line_total_amount.toFixed(2));
            }

            // Trigger calculation on input change
            $(document).on("input", ".line_quantity, .line_unit_price", function(){
                calculateTotal();
            });

            // Initial calculation
            calculateTotal();
        });
    </script>


    <script>
        function reloadSelect2WithNewRoute($el, newRoute) {
            $el.attr('data-get-items-route', newRoute);
            $el.val(null).trigger('change');
            $el.select2('destroy');
            $el.select2({
                ajax: {
                    url: newRoute,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results,
                            pagination: data.pagination
                        };
                    }
                },
                placeholder: 'Select Company',
                allowClear: false,
                minimumInputLength: 0
            });
        }

        $(document).ready(function() {
            $('select[name="companier_id"]').on('change', function() {
                var companyId = $(this).val();
                var newRoute = '/panel/invoices/relation?type=invoice_belongstomany_candidate_relationship&method=add&page=1&company_id='+companyId;
                reloadSelect2WithNewRoute($('select[name="invoice_belongstomany_candidate_relationship[]"]'), newRoute);
            });
        });
    </script>
@stop
