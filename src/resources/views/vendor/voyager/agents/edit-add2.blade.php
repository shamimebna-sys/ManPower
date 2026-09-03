@extends('voyager::master')
@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp
@section('page_title', __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .step {
            display: none;
        }
        .step.active {
            display: block;
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
            @if(isset($dataTypeContent->id))  {{ method_field("PUT") }}  @endif
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

            <div class="row step active" >
                <div class="col-md-9">
                    <div class="panel panel-bordered panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> Basic Information</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                @if(isset($dataTypeContent->id))
                                    <div class="form-group  col-md-3">
                                        <label for="name">Code
                                            <strong style="color: red;">*</strong>
                                        </label>
                                        <input type="text" readonly class="form-control" name="code" placeholder="Code" value="{{$dataTypeContent->code}}">
                                    </div>
                                @else
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['code'], 'width'=>'3'])
                                @endif

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['name'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['email'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mobile'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['status'], 'width'=>'4', 'isHidden' => true])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['father_name'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mother_name'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['gender'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['blood_group'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nationality'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['present_address_house'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['permanent_address_house'], 'width'=>'12'])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="panel panel-bordered panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> Required Documents</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['full_photo_file_path'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['half_photo_file_path'], 'width'=>'12'])



                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row step" >
                <div class="col-md-3">
                    <div class="panel panel-bordered panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> Documents</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['dob'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bid'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bid_file_path'], 'width'=>'12'])
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="panel panel-bordered panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> Documents</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nid'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nid_file_path'], 'width'=>'12'])

                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="panel panel-bordered panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> Documents</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_no'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_issue_date'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_expire_date'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_file_path'], 'width'=>'12'])
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="panel panel-bordered panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> Documents</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['cv_file_path'], 'width'=>'12'])
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <div class="row step" >
                <div class="col-md-8">
                    <div class="panel panel-bordered panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> Access Information</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">

                                <div class="form-group  col-md-4">
                                    <label for="name">Password</label>
                                    <input type="text" class="form-control" name="password" placeholder="Password" value="">
                                </div>

                                <div class="form-group  col-md-4">
                                    <label for="name">Re-Password</label>
                                    <input type="text" class="form-control" name="re-password" placeholder="Password" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-secondary  pull-right prev-step">Previous</button>
            <button type="button" class="btn btn-primary  pull-right next-step">Next</button>

            <button type="submit" class="btn btn-primary pull-right save">
                {{ __('voyager::generic.save') }}
            </button>
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
        const steps = document.querySelectorAll('.step');
        let currentStep = 0;

        document.querySelectorAll('.next-step').forEach(button => {
            button.addEventListener('click', () => {
                steps[currentStep].classList.remove('active');
                currentStep++;
                steps[currentStep].classList.add('active');
            });
        });

        document.querySelectorAll('.prev-step').forEach(button => {
            button.addEventListener('click', () => {
                steps[currentStep].classList.remove('active');
                currentStep--;
                steps[currentStep].classList.add('active');
            });
        });

        /*document.getElementById('multiStepForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Form submitted!');
            // You can handle the form data here
        });*/
    </script>
@stop
