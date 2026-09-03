@extends('voyager::master')
@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp
@section('page_title', __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' Profile')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' Profile' }}
    </h1>
@stop

@section('content')
    <div class="page-content container-fluid">
        <form class="form-edit-add" role="form"
              action=" {{ route('admin.my.profile.update') }}"
              method="POST" enctype="multipart/form-data" autocomplete="off">
            {{ method_field("PUT") }}
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
                                <div style="display: none">
                                    <div class="form-group  col-md-4">
                                        <label for="name">Nationality
                                        </label>
                                        <input type="text" class="form-control" name="nationality" placeholder="Nationality" value="Bangladesh">
                                    </div>
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['blood_group'], 'width'=>'3'])

                                </div>


                                @if($edit)
                                    <div class="form-group  col-md-4">
                                        <label for="name">Code
                                        </label>
                                        <input readonly type="text" class="form-control" name="code" placeholder="Code" value="{{$dataTypeContent->code}}">
                                    </div>
                                @else
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['code'], 'width'=>'4'])
                                @endif

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['name'], 'width'=>'8'])

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['father_name'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mother_name'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['gender'], 'width'=>'4'])

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['dob'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bid'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nid'], 'width'=>'4'])

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_no'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_issue_date'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_expire_date'], 'width'=>'4'])

                                <table style="width: 100%">

                                    <tr>
                                        <td style="width: 33%"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nid_file_path'], 'width'=>'12'])</td>
                                        <td style="width: 33%"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_file_path'], 'width'=>'12'])</td>
                                        <td style="width: 33%"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['cv_file_path'], 'width'=>'12'])</td>
                                    </tr>

                                </table>

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['present_address_house'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['permanent_address_house'], 'width'=>'6'])

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['full_photo_file_path'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['half_photo_file_path'], 'width'=>'3'])

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['basic_info_career'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['basic_info_special'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['other_skills'], 'width'=>'6'])

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="panel panel-bordered panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> Required Information</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                @if($edit)
                                <div class="form-group  col-md-12">
                                    <label for="name">Status
                                        <strong style="color: red;">*</strong>
                                    </label>
                                    @php
                                        if($dataTypeContent->status == 'P'){
                                            $displayStatus = 'Pending';
                                        }elseif ($dataTypeContent->status == 'A'){
                                             $displayStatus = 'Active';
                                        }else{
                                             $displayStatus = 'Inactive';
                                        }
                                    @endphp
                                    <input type="hidden"  name="status"  value="{{$dataTypeContent->status}}">
                                    <input type="text" readonly class="form-control" step="any" placeholder="Status" value="{{$displayStatus}}">
                                </div>
                                <div class="form-group  col-md-12">
                                    <label for="name">Email
                                        <strong style="color: red;">*</strong>
                                    </label>
                                    <input type="email" readonly class="form-control" name="email" data-name="Email" step="any" placeholder="Email" value="{{$dataTypeContent->email}}">
                                </div>
                                @endif
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['secondary_email'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mobile'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['secondary_mobile'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['emergency_mobile'], 'width'=>'12'])

                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_agent_relationship'], 'width'=>'12'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_class_group_relationship'], 'width'=>'12'])
                            </div>

                            <hr>
                            <h4>User Access</h4>
                            <div class="row">
                                <div class="form-group  col-md-12">
                                    <label for="name">Password</label>
                                    <input type="text" class="form-control" name="password" placeholder="Password" value="">
                                </div>

                                <div class="form-group  col-md-12">
                                    <label for="name">Re-Password</label>
                                    <input type="text" class="form-control" name="re-password" placeholder="Password" value="">
                                </div>

                            </div>
                        </div>
                    </div>
                </div>



            </div>

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
@stop
