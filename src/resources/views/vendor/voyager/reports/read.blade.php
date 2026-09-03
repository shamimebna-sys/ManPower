@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp

@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .border-white {
            border-color: white !important;;
        }
    </style>
@stop

@section('page_title', __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        Report of {{$dataTypeContent->name}}
    </h1>
    <a href="{{url('/panel/reports')}}" class="btn btn-success btn-add-new">
        <i class="voyager-list"></i> <span>Go to List</span>
    </a>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            @if(!Route::has('admin.reports.generate.'.$dataTypeContent->report_uri))
                <div class="alert alert-danger">
                    <p>
                        Route not found 'admin.reports.generate.{{$dataTypeContent->report_uri}}
                    </p>
                </div>
            @else
                <form role="form" target="_blank" class="form-edit-add"
                      action="{{route('admin.reports.generate.'.$dataTypeContent->report_uri)}}" method="POST"
                      enctype="multipart/form-data"
                      id="order-form">
                    {{ csrf_field() }}

                    <div class="col-md-12">
                        <div class="panel panel-bordered panel-info">

                            <div class="panel-body">
                                {{--                            {"customer_id":"Customer","supplier_id":"Supplier","from_date":"From Date", "to_date":"To Date", "product_id":"Product", "color_id":"Color", "size_id":"Size", "weight_id": "Weight", "brand_id":"Brand", "stock_id":"Stock"}--}}
                                @php
                                    $params = json_decode($dataTypeContent->params);
                                @endphp

                                <div style="display: none">
                                    <input type="text" name="title" value="{{$dataTypeContent->name}}">
                                </div>

                                @if(!empty($dataTypeContent->params) && \App\Helpers\CommonClass::jsonValidation($dataTypeContent->params))
                                    @foreach(json_decode($dataTypeContent->params) as $field)


                                        @if(!in_array($field->key, ['supplier_id', 'customer_id', 'from_date', 'to_date', 'product_id', 'color_id','size_id','weight_id','brand_id','stock_id','branch_id','role_id','designation_id','blood_group_id','sales_type', 'agent_id', 'candidate_id', 'agencier_id', 'companier_id', 'payment_status', 'teacher_id', 'class_group_id', 'week_day', 'exam_id', 'result', 'position_id', 'gender', 'status', 'payment_method']) && strpos($field->key, 'date') === false)
                                            <div class="form-group  col-md-3">
                                                <label for="{{$field->key}}">{{$field->label}}</label>
                                                <input type="text" class="form-control" name="{{$field->key}}"
                                                       {{($field->required == 'true'?'required':'')}}
                                                       placeholder="{{$field->label}}" value="">
                                            </div>

                                        @elseif($field->key == 'from_date' || $field->key == 'to_date' || strpos($field->key, 'date') !== false)
                                            <div class="form-group  col-md-3">
                                                <label for="name">{{$field->label}}</label>
                                                <input type="date" class="form-control" name="{{$field->key}}"
                                                       {{($field->required == 'true'?'required':'')}}
                                                       placeholder="{{$field->label}}" value="">
                                            </div>

                                        @elseif($field->key == 'supplier_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="supplier_id"
                                                    id="supplier_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/purchases/relation'}}"
                                                    data-get-items-field="purchase_belongsto_supplier_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'customer_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="customer_id"
                                                    id="customer_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/sales/relation'}}"
                                                    data-get-items-field="sale_belongsto_customer_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'product_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="product_id"
                                                    id="product_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/stocks/relation'}}"
                                                    data-get-items-field="stock_belongsto_product_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'color_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="color_id"
                                                    id="color_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/stocks/relation'}}"
                                                    data-get-items-field="stock_belongsto_color_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'size_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="size_id"
                                                    id="size_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/stocks/relation'}}"
                                                    data-get-items-field="stock_belongsto_size_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'weight_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="weight_id"
                                                    id="weight_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/stocks/relation'}}"
                                                    data-get-items-field="stock_belongsto_weight_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'brand_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="brand_id"
                                                    id="brand_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/stocks/relation'}}"
                                                    data-get-items-field="stock_belongsto_brand_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'stock_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select class="select2" name="stock_id" id="stock_id">
                                                    <option value="">All</option>
                                                    @foreach(\App\Entities\Stock::all() as $s)
                                                        <option value="{{$s->id}}">{{$s->sku}}
                                                            -{{$s->product->name}}{{($s->color)?'-'.$s->color->name:''}}{{($s->size)?'-'.$s->size->name:''}}{{($s->weight)?'-'.$s->weight->name:''}}{{($s->brand)?'-'.$s->brand->name:''}}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                        @elseif($field->key == 'branch_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="branch_id"
                                                    id="branch_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/users/relation'}}"
                                                    data-get-items-field="user_belongsto_branch_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'role_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="role_id"
                                                    id="role_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/users/relation'}}"
                                                    data-get-items-field="user_belongsto_role_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'designation_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="designation_id"
                                                    id="designation_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/users/relation'}}"
                                                    data-get-items-field="user_belongsto_designation_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'blood_group_id')
                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="blood_group_id"
                                                    id="blood_group_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/users/relation'}}"
                                                    data-get-items-field="user_belongsto_blood_group_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>

                                        @elseif($field->key == 'sales_type')
                                            <div class="  form-group  col-md-3">
                                                <label for="{{$field->key}}" style="float: left">{{$field->label}}</label>
                                                <select class="form-control select2" name="{{$field->key}}" data-select2-id="1" tabindex="-1"   aria-hidden="true">
                                                    <option value="NEW">New Sales</option>
                                                    <option value="RETURN" >Sales Return</option>
                                                </select>
                                            </div>
                                        @elseif($field->key == 'agent_id')

                                            <div class="  form-group  col-md-3">
                                                <label for="name" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="{{$field->key}}"
                                                    id="{{$field->key}}"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/panel/candidates/relation'}}"
                                                    data-get-items-field="candidate_belongsto_agent_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>
                                        @elseif($field->key == 'candidate_id')

                                            <div class="  form-group  col-md-3">
                                                <label for="candidate_id" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="candidate_id"
                                                    id="candidate_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/panel/exam-results/relation'}}"
                                                    data-get-items-field="exam_result_belongsto_candidate_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>
                                        @elseif($field->key == 'agencier_id')

                                            <div class="  form-group  col-md-3">
                                                <label for="agencier_id" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="agencier_id"
                                                    id="agencier_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/panel/candidates/relation'}}"
                                                    data-get-items-field="candidate_belongsto_agencier_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>
                                        @elseif($field->key == 'companier_id')

                                            <div class="  form-group  col-md-3">
                                                <label for="companier_id" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="companier_id"
                                                    id="companier_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/panel/candidates/relation'}}"
                                                    data-get-items-field="candidate_belongsto_companier_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>
                                        @elseif($field->key == 'payment_status')

                                            <div class="  form-group  col-md-3">
                                                <label for="payment_status" style="float: left">{{$field->label}}</label>
                                                <select class="form-control select2" name="payment_status" id="payment_status">
                                                    <option value="">All</option>
                                                    <option value="Done">Done</option>
                                                    <option value="Pending">Pending</option>
                                                    <option value="VISA UPDATED">Visa Updated</option>
                                                    <option value="VISA NOT UPDATED">Visa Not Updated</option>
                                                </select>
                                            </div>
                                        @elseif($field->key == 'teacher_id')

                                            <div class="  form-group  col-md-3">
                                                <label for="teacher_id" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="teacher_id"
                                                    id="teacher_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/admin/class-schedules/relation'}}"
                                                    data-get-items-field="class_schedule_belongsto_teacher_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>
                                        @elseif($field->key == 'class_group_id')

                                            <div class="  form-group  col-md-3">
                                                <label for="class_group_id" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="class_group_id"
                                                    id="class_group_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/panel/exam-results/relation'}}"
                                                    data-get-items-field="exam_result_belongsto_class_group_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>
                                        @elseif($field->key == 'week_day')

                                            <div class="  form-group  col-md-3">
                                                <label for="week_day" style="float: left">{{$field->label}}</label>
                                                <select class="form-control select2" name="week_day" id="week_day">
                                                    <option value="">All</option>
                                                    <option value="Saturday">Saturday</option>
                                                    <option value="Sunday">Sunday</option>
                                                    <option value="Monday">Monday</option>
                                                    <option value="Tuesday">Tuesday</option>
                                                    <option value="Wednesday">Wednesday</option>
                                                    <option value="Thursday">Thursday</option>
                                                    <option value="Friday">Friday</option>
                                                </select>
                                            </div>
                                        @elseif($field->key == 'exam_id')

                                            <div class="  form-group  col-md-3">
                                                <label for="exam_id" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="exam_id"
                                                    id="exam_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/panel/exam-results/relation'}}"
                                                    data-get-items-field="exam_result_belongsto_exam_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>
                                        @elseif($field->key == 'result')

                                            <div class="  form-group  col-md-3">
                                                <label for="result" style="float: left">{{$field->label}}</label>
                                                <select class="form-control select2" name="result" id="result">
                                                    <option value="">All</option>
                                                    <option value="PASS">PASS</option>
                                                    <option value="FAIL">FAIL</option>
                                                </select>
                                            </div>
                                        @elseif($field->key == 'position_id')

                                            <div class="  form-group  col-md-3">
                                                <label for="position_id" style="float: left">{{$field->label}}</label>
                                                <select
                                                    class="select2-ajax" name="position_id"
                                                    id="position_id"
                                                    {{($field->required == 'true'?'required':'')}}
                                                    data-get-items-route="{{url('/').'/panel/candidates/relation'}}"
                                                    data-get-items-field="candidate_belongsto_position_relationship"
                                                    data-method="add"
                                                >
                                                </select>
                                            </div>
                                        @elseif($field->key == 'gender')

                                            <div class="  form-group  col-md-3">
                                                <label for="gender" style="float: left">{{$field->label}}</label>
                                                <select class="form-control select2" name="gender" id="gender">
                                                    <option value="">All</option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                </select>
                                            </div>
                                        @elseif($field->key == 'status')

                                            <div class="  form-group  col-md-3">
                                                <label for="status" style="float: left">{{$field->label}}</label>
                                                <select class="form-control select2" name="status" id="status">
                                                    <option value="">All</option>
                                                    <option value="A">Active</option>
                                                    <option value="I">Inactive</option>
                                                </select>
                                            </div>
                                        @elseif($field->key == 'payment_method')

                                            <div class="  form-group  col-md-3">
                                                <label for="payment_method" style="float: left">{{$field->label}}</label>
                                                <select class="form-control select2" name="payment_method" id="payment_method">
                                                    <option value="">All</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Bank">Bank</option>
                                                    <option value="Cheque">Cheque</option>
                                                </select>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif

                            </div>
                            <div class="panel-footer">
                                <div class="form-group col-md-3" style=" display: flex;">
                                    <label for="name" style=" margin-right: 10px; margin-bottom: 0; white-space: nowrap;">Report Type</label>
                                    <select class="form-control" name="report_type">
                                        <option value="WEB" selected>Web View</option>
                                        <option value="PDF-VIEW">PDF View</option>
                                        <option value="PDF-DOWNLOAD">PDF Download</option>
{{--                                        <option value="CSV">Excel CSV</option>--}}
                                    </select>
                                </div>


                                @section('submit-buttons')
                                    <button type="submit" id="btn-submit" class="btn btn-primary save">Generate</button>
                                @stop
                                @yield('submit-buttons')
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

@stop

@section('javascript')

@stop
