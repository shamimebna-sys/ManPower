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
                            <h3 class="panel-title"><i class="icon wb-image"></i> License</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>

                        <div class="panel-body">
                            <div class="row">
                                <div style="display: none">

                                </div>

                                {{--@if($edit)
                                    <div class="form-group  col-md-4">
                                        <label for="name">License No <strong style="color: red;">*</strong></label>
                                        <input readonly type="text" class="form-control" name="license_no" placeholder="license_no" value="{{$dataTypeContent->license_no}}">
                                    </div>
                                @else
                                    <div class="form-group  col-md-4">
                                        <label for="name">License No <strong style="color: red;">*</strong></label>
--}}{{--                                        <input readonly type="text" class="form-control" name="license_no" placeholder="license_no" value="{{\App\Helpers\CommonClass::invoiceNo()}}">--}}{{--

                                        @php

                                            $licenseMaxNo = DB::table('licenses')
                                               // ->where('agenciers_id', \App\Helpers\CommonClass::user()->profile_id)
                                                ->selectRaw('MAX(CAST(license_no AS UNSIGNED)) AS max_license_no')
                                                ->value('max_license_no');

                                            $licenseNo = !empty($licenseMaxNo)? $licenseMaxNo+1: 1;
                                        @endphp
                                        <input type="number" {{!empty($licenseMaxNo)?'readonly':''}} min="1" required class="form-control" placeholder="license_no" name="license_no"  value="{{$licenseNo}}">

                                    </div>
                                @endif--}}
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['license_no'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['status'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['license_start_date'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['license_expire_date'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['license_belongsto_companier_relationship'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['license_file_path'], 'width'=>'4'])


                                <div class="col-md-12">
                                    <h4>Position Details</h4>
                                    <table  id="invoiceTable" class="table table-hover ">
                                        <thead>
                                        <tr>
                                            <th style="width: 80%">Title</th>
                                            <th style="text-align: right">Quantity</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @if($edit)
                                          @php
                                          $ex_position_ids=[];
                                          $ex_index = 0;
                                          @endphp
                                            @foreach($dataTypeContent->licensePositions as $i=>$head)
                                                @php
                                                    $ex_position_ids[]=$head->position_id;
                                                    $ex_index=$i;
                                                @endphp
                                                <tr>
                                                    <td style=" padding: 0; width: 90%">
                                                        <input type="hidden" name="license_position_id[]" value="{{$head->position_id}}">
                                                        {{++$i}}. {{$head->position->title}}
                                                    </td>
                                                    <td style="padding: 0;"><input  type="number" class="license_position_quantity[]" name="license_position_quantity[]" value="{{$head->quantity}}" style="text-align: right"></td>
                                                </tr>
                                            @endforeach

                                           @foreach(\App\Models\Position::where('status', 'A')->whereNotIn('id', $ex_position_ids)->orderBy('title', 'ASC')->get() as $i=>$head)
                                              <tr>
                                                  <td style=" padding: 0; width:90%">
                                                      <input type="hidden" name="license_position_id[]" value="{{$head->id}}">
                                                      {{$ex_index+$i+1}}. {{$head->title}}
                                                  </td>
                                                  <td style="padding: 0;">
                                                      <input min="0"  type="number" class="license_position_quantity" name="license_position_quantity[]" value="{{ old('license_position_quantity.' . $i) }}" style="text-align: right; float:right">
                                                  </td>
                                              </tr>
                                          @endforeach

                                        @else

                                            @foreach(\App\Models\Position::where('status', 'A')->orderBy('title', 'ASC')->get() as $i=>$head)
                                                <tr>
                                                    <td style=" padding: 0; width:90%">
                                                        <input type="hidden" name="license_position_id[]" value="{{$head->id}}">
                                                        {{++$i}}. {{$head->title}}
                                                    </td>
                                                    <td style="padding: 0;">
                                                        <input min="0"  type="number" class="license_position_quantity" name="license_position_quantity[]" value="{{ old('license_position_quantity.' . $i) }}" style="text-align: right; float:right">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        </tbody>

                                        <tfoot style="display: none">
                                        <tr class="bg-warning" style="font-weight: bolder;">
                                            <td style="font-size: larger; text-align: right; width:90%" >Total:</td>
                                            <td style="font-size: larger; text-align: right"><span id="license_position_quantity_total"></span></td>
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
                return   parseFloat($(row).find(".license_position_quantity").val()) || 0;
                $(row).find(".license_position_quantity_total").val(line_total_amount);
            }

            function calculateTotal() {
                let total_quantity = 0;
                $("#invoiceTable tbody tr").each(function(){
                     total_quantity += calculateRow(this);
                });
                $("#license_position_quantity_total").html(total_quantity);
            }

            // Trigger calculation on input change
            $(document).on("input", ".license_position_quantity", function(){
                calculateTotal();
            });

            // Initial calculation
            calculateTotal();
        });
    </script>

    <script>
        $(document).ready(function () {
            var table = $('#invoiceTable').DataTable({!! json_encode(
                    array_merge([
                        "pageLength"=> 200,
                        "paging"=> false,
                        "lengthChange"=> false,
                        "order" => 0,
                        "columnDefs" => [
                            ['targets' => 'dt-not-orderable', 'searchable' =>  false, 'orderable' => false],
                        ],
                    ],
                    config('voyager.dashboard.data_tables', []))
                , true) !!});
        });
        </script>

@stop
