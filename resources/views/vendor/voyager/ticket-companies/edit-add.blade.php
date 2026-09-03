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
                            <h3 class="panel-title"><i class="icon wb-image"></i> Ticket Company</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>

                        <div class="panel-body">
                            <div class="row">
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['name'], 'width'=>'9'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['status'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['email'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mobile'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['address'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['logo'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['signature'], 'width'=>'3'])

                                <div class="col-md-6" style="border-right: 1px solid black">
                                    <h4>Payment Method-1</h4>
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_account_name_1'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_account_no_1'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_name_1'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_branch_name_1'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_swift_no_1'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_iban_no_1'], 'width'=>'6'])
                                </div>

                                <div class="col-md-6">
                                    <h4>Payment Method-2</h4>
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_account_name_2'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_account_no_2'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_name_2'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_branch_name_2'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_swift_no_2'], 'width'=>'6'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_iban_no_2'], 'width'=>'6'])
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


@stop
