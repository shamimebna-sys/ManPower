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
    @include('voyager::partials.candidate-partial-top-menu')
    <div class="row">
        @include('voyager::partials.candidate-partial-menu')

        <div class="col-md-{{isset($dataTypeContent->id)?'9':'12'}}">
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
                        <div class="col-md-{{request()->has('stamp')?12:9}}">
                            <div class="panel panel-bordered panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="icon wb-image"></i> {{request()->has('stamp')?'Passport With Stamp':'Basic Information'}} </h3>
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
                                                <label for="name">Code <strong style="color: red;">*</strong></label>
                                                <input readonly type="text" class="form-control" name="code" placeholder="Code" value="{{$dataTypeContent->code}}">
                                            </div>
                                        @else
                                            <div class="form-group  col-md-4">
                                                <label for="name">Code <strong style="color: red;">*</strong></label>
                                                <input readonly type="text" class="form-control" name="code" placeholder="Code" value="#######">
                                            </div>
                                            {{--@include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['code'], 'width'=>'4'])--}}
                                        @endif

                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['name'], 'width'=>'8'])

                                        <div style="display: {{request()->has('stamp')?'none':''}}">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['father_name'], 'width'=>'4'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mother_name'], 'width'=>'4'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['gender'], 'width'=>'4'])

                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['dob'], 'width'=>'4'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bid'], 'width'=>'4'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nid'], 'width'=>'4'])
                                        </div>
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_no'], 'width'=>'4'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_issue_date'], 'width'=>'4'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_expire_date'], 'width'=>'4'])

                                        <table style="width: 100%">
                                            <tr>
                                                <td style="width: 33%; display: {{request()->has('stamp')?'none':''}}"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['cv_file_path'], 'width'=>'12'])</td>
                                                <td style="width: 33%; display: {{request()->has('stamp')?'none':''}}"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nid_file_path'], 'width'=>'12'])</td>
                                                <td style="width: {{request()->has('stamp')?'50%':'33%'}}"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_file_path'], 'width'=>'12'])</td>
                                                <td style="width: 50%; display: {{request()->has('stamp')?'':'none'}}"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['stamp_file_path'], 'width'=>'12'])</td>
                                            </tr>
                                        </table>

                                        <div style="display: {{request()->has('stamp')?'none':''}}">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['present_address_house'], 'width'=>'6'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['permanent_address_house'], 'width'=>'6'])

                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['full_photo_file_path'], 'width'=>'3'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['half_photo_file_path'], 'width'=>'3'])

                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['basic_info_career'], 'width'=>'6'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['basic_info_special'], 'width'=>'6'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['other_skills'], 'width'=>'6'])
                                        </div>
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['remarks'], 'width'=>'12'])

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3" style="display: {{request()->has('stamp')?'none':''}}">
                            <div class="panel panel-bordered panel-warning">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="icon wb-image"></i> Required</h3>
                                    <div class="panel-actions">
                                        <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['status'], 'width'=>'12'])

                                        {{--@if($edit)
                                            <div class="form-group  col-md-12">
                                                <label for="name">Email <strong style="color: red;">*</strong></label>

                                                <input {{in_array(Auth::user()->role_id, [1,2,106])?'':'readonly'}} type="text" class="form-control" name="email" placeholder="Email" value="{{$dataTypeContent->email}}">
                                            </div>
                                        @else
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['email'], 'width'=>'12'])
                                        @endif--}}

                                        <div style="display: {{in_array(\App\Helpers\CommonClass::user()->role_id,[101, 107, 108])?'none':'block'}}">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['email'], 'width'=>'12'])
                                        </div>
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['secondary_email'], 'width'=>'12'])
                                        {{-- @if($edit)
                                             <div class="form-group  col-md-12">
                                                 <label for="name">Mobile <strong style="color: red;">*</strong></label>
                                                 <input readonly type="text" class="form-control" name="mobile" placeholder="Mobile" value="{{$dataTypeContent->mobile}}">
                                             </div>
                                         @else
                                             @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mobile'], 'width'=>'12'])
                                         @endif--}}
                                        <div style="display: {{in_array(\App\Helpers\CommonClass::user()->role_id,[101, 107, 108])?'none':'block'}}">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mobile'], 'width'=>'12'])
                                        </div>
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['secondary_mobile'], 'width'=>'12'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['emergency_mobile'], 'width'=>'12'])

                                        <div style="display: {{in_array(\App\Helpers\CommonClass::user()->role_id,[101, 107, 108, 109])?'none':'block'}}">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_agent_relationship'], 'width'=>'12'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_sub_agent_relationship'], 'width'=>'12'])

                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_class_group_relationship'], 'width'=>'12'])
{{--                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mp_visa_no'], 'width'=>'12'])--}}
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_agencier_relationship'], 'width'=>'12'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_companier_relationship'], 'width'=>'12'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_position_relationship'], 'width'=>'12'])
                                        </div>



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
                placeholder: 'Select Sub Agent',
                allowClear: false,
                minimumInputLength: 0
            });
        }

        $(document).ready(function() {
            $('select[name="agent_id"]').on('change', function() {
                var agentId = $(this).val();
                var newRoute = '/panel/candidates/relation?type=candidate_belongsto_sub_agent_relationship&method=add&page=1&agent_id='+agentId;
                reloadSelect2WithNewRoute($('select[name="sub_agent_id"]'), newRoute);
            });
        });
    </script>

@stop
