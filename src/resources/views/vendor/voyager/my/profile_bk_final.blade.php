@extends('voyager::master')
@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp
@section('page_title', __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' Profile / CV')

@section('css')
    <style>
        .form-step .card{
            border:1px solid #e4e4e4 !important;
            border-radius:1% !important;
            margin-bottom: 10px;
        }
        .form-step .card-header{
            padding-top:1%;
            padding-left:1%;
        }
        .sidebar {
            /*display:none;*/
            /*position: fixed;*/
            /*left: 0;*/
            /*top: 20%;*/
            height: calc(100vh - 60px);
            /*width: 250px;*/
            background: #f8f9fa;
            /*padding: 20px;*/
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
            /*z-index: 100;*/
        }

        .main-content {
            /*margin-left: 270px;*/
            /*padding: 20px;*/
        }

        .nav-step {
            padding: 10px 15px;
            margin-bottom: 5px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .nav-step:hover {
            background: #e9ecef;
        }

        .nav-step.active {
            background: #3097d1;
            color: white;
        }

        .nav-step.completed {
            background: #62cb31;
            color: white;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block !important;
        }

        .card-header {
            background: #3097d1;
            color: white;
            border-radius: 5px 5px 0 0;
        }

        .btn-voyager {
            background: #3097d1;
            border-color: #3097d1;
            color: white;
        }

        .btn-voyager:hover {
            background: #2579a9;
            border-color: #2579a9;
            color: white;
        }

        .required-field::after {
            content: " *";
            color: #e74c3c;
        }


        @media (max-width: 768px) {
            .sidebar {
                /*position: relative;*/
                /*width: 100%;*/
                height: auto;
                margin-bottom: 20px;
            }

            .main-content {
                margin-left: 0;
            }

            .nav-step {
                display: inline-block;
                margin-right: 10px;
                padding: 8px 12px;
            }
        }
    </style>
@stop

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' Profile / CV' }}
    </h1>
    <a href="{{route('admin.my.profile.index')}}" class="btn btn-dark btn-sm  pull-right" >
        <i class="voyager-pen"></i> <span>View Profile</span>
    </a>
    <a href="" class="btn btn-warning btn-sm pull-right" style="margin-right: 1%">
        <i class="voyager-file"></i> <span> PDF Export</span>
    </a>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')

        <div class="row">
            <div class="col-md-3">
                <!-- Multi-Step Form -->
                <div class="sidebar">
                    @php
                        $sidebars = [
                            [
                                'stepNo'=>1,
                                'icon'=>'voyager-person',
                                'title'=>'Basic Information',
                                'subTitle'=>'Personal details',
                            ],
                             [
                                'stepNo'=>2,
                                'icon'=>'voyager-company',
                                'title'=>'Employment History',
                                'subTitle'=>'Work history',
                            ],
                             [
                                'stepNo'=>3,
                                'icon'=>'voyager-study',
                                'title'=>'Academic Qualification',
                                'subTitle'=>'Academic background',
                            ],
                             [
                                'stepNo'=>4,
                                'icon'=>'voyager-tools',
                                'title'=>'Training Information',
                                'subTitle'=>'Personal details',
                            ],
                             [
                                'stepNo'=>5,
                                'icon'=>'voyager-forward',
                                'title'=>'Skill Information',
                                'subTitle'=>'Skill details',
                            ],
                             [
                                'stepNo'=>6,
                                'icon'=>'voyager-home',
                                'title'=>'Personal Information',
                                'subTitle'=>'Personal details',
                            ],
                             [
                                'stepNo'=>7,
                                'icon'=>'voyager-forward',
                                'title'=>'Files & Documents',
                                'subTitle'=>'Related files & documents',
                            ]

                        ];
                    @endphp
                    {{--                    <h5 class="mb-3">CV Form Steps</h5>--}}
                    @foreach($sidebars as $i)
                        <div class="nav-step {{($i['stepNo'] == 1) ? 'active':''}} active" data-step="{{$i['stepNo']}}">
                            <div><i class="{{!empty($i['icon'])?$i['icon']:'voyager-forward'}}"></i> {{$i['title']}}</div>
                            @if(!empty($i['subTitle']))<small class="text-light">{{$i['subTitle']}}</small>@endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-md-9">
                <div class="main-content">
                    <div class="panel panel-bordered">
                        <div class="panel-body" style="margin: 0; padding: 0">
                            <form id="cvForm" action="{{ route('admin.my.profile.update') }}" method="POST"
                                  enctype="multipart/form-data">
                                {{ method_field("PUT") }}
                                @csrf

                                <div style="display:none">
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['other_skills'], 'width'=>'12'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_agent_relationship'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_class_group_relationship'], 'width'=>'4'])
                                </div>

                                <!-- Step 1: Basic Information -->
                                <div class="form-step active" data-step="1">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="mb-0" ><i class="voyager-person"></i> Basic Information</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['name'], 'width'=>'6', 'required'=>true])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['position'], 'width'=>'6', 'required'=>true])
                                                {{--<div class="col-md-6 mb-3">
                                                    <label for="name" class="form-label required-field">Name</label>
                                                    <input type="text"
                                                           class="form-control @error('name') is-invalid @enderror"
                                                           id="name" name="name" value="{{ old('name') }}" required>
                                                    @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>--}}

                                            </div>
                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_no'], 'width'=>'6', 'required'=>true])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_issue_date'], 'width'=>'3'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_expire_date'], 'width'=>'3'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nid'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['dob'], 'width'=>'3'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bid'], 'width'=>'3'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['email'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mobile'], 'width'=>'6'])
                                            </div>
                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['abroad_ex'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['local_ex'], 'width'=>'6'])

                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['basic_info_career'], 'width'=>'12'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['basic_info_special'], 'width'=>'12'])
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Employment History -->
                                <div class="form-step" data-step="2">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="mb-0"><i class="voyager-company"></i> Employment History</h4>
                                        </div>
                                        <div class="card-body">
                                            <div id="employment-history">

                                                <!--Start show to saved data -->
                                                @if($expriences)
                                                    @foreach($expriences as $i)
                                                        <div class="card mb-3 position-relative">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label">Designation</label>
                                                                        <input type="text" class="form-control" name="experience_designation[]" value="{{$i['designation']}}">
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label">Company Name</label>
                                                                        <input type="text" class="form-control" name="experience_company_name[]"  value="{{$i['company_name']}}">
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label">Company Location</label>
                                                                        <input type="text" class="form-control" name="experience_company_address[]"  value="{{$i['company_address']}}">
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Employment Period</label>
                                                                        <div class="input-group">
                                                                            <input type="date" name="experience_start_date[]" class="form-control"  value="{{$i['start_date']}}">
                                                                            <div class="input-group-addon">to</div>
                                                                            <input type="date" name="experience_end_date[]" class="form-control"  value="{{$i['end_date']}}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Duration</label>
                                                                        <input type="text" class="form-control" name="experience_duration[]" placeholder="e.g., 3 years"  value="{{$i['duration']}}">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Responsibility</label>
                                                                    <textarea class="form-control" name="experience_responsibilities[]" rows="2">{{$i['responsibilities']}}</textarea>
                                                                </div>
                                                            </div>

                                                            <button type="button" class="btn btn-sm btn-danger position-absolute" aria-label="Remove" style="top: 0px; right: 5px; position: absolute;"><i class="voyager-trash"></i></button></div>
                                                    @endforeach
                                                @endif
                                                <!--End show to saved data -->

                                            </div>
                                            <button type="button" class="btn btn-block btn-warning  mb-3"
                                                    onclick="addEmployment()">
                                                <i class="voyager-plus"></i> Add More Employment
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Academic Qualification -->
                                <div class="form-step" data-step="3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="mb-0"><i class="voyager-study"></i> Academic Qualification</h4>
                                        </div>
                                        <div class="card-body">
                                            <div id="academic-qualification">
                                                <!--Start show to saved data -->
                                                @if($educations)
                                                    @foreach($educations as $i)
                                                        <div class="card mb-3 position-relative">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Course Name</label>
                                                                        <input type="text" class="form-control" name="education_exam_name[]" value="{{$i['exam_name']}}">
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Concentration/Major/Group</label>
                                                                        <input type="text" class="form-control" name="education_subject_group_major[]" value="{{$i['subject_group_major']}}">
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Level of Education</label>
                                                                        <input type="text" class="form-control" name="education_education_label[]" value="{{$i['education_label']}}">
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Institute Name</label>
                                                                        <input type="text" class="form-control" name="education_institute_name[]" value="{{$i['institute_name']}}">
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Board Name</label>
                                                                        <input type="text" class="form-control" name="education_board_name[]" value="{{$i['board_name']}}">
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Result (CGPA)</label>
                                                                        <input type="text" class="form-control" name="education_result[]" value="{{$i['result']}}">
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label">Scale</label>
                                                                        <input type="text" class="form-control" name="education_scale[]" value="{{$i['scale']}}">
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label">Year Of Passing</label>
                                                                        <input type="number" min="1900" max="2025" class="form-control" name="education_passing_year[]" value="{{$i['passing_year']}}">
                                                                    </div>
                                                                    <div class="col-md-4 mb-3">
                                                                        <label class="form-label">Duration (year)</label>
                                                                        <input type="number" min="0" class="form-control" name="education_duration_year[]" value="{{$i['duration_year']}}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-danger position-absolute" aria-label="Remove" style="top: 0px; right: 5px; position: absolute;" disabled=""><i class="voyager-trash"></i></button></div>
                                                    @endforeach
                                                @endif
                                            <!--End show to saved data -->

                                            </div>
                                            <button type="button" class="btn btn-block btn-warning mb-3"
                                                    onclick="addAcademic()">
                                                <i class="voyager-plus"></i> Add More Qualification
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 4: Training -->
                                <div class="form-step" data-step="4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="mb-0"><i class="voyager-tools"></i> Training Information</h4>
                                        </div>
                                        <div class="card-body">
                                            <div id="training">
                                                <!--Start show to saved data -->
                                                @if($trainings)
                                                    @foreach($trainings as $i)
                                                        <div class="card mb-3 position-relative">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Training Title</label>
                                                                        <input type="text" class="form-control" name="training_title[]" value="{{$i['title']}}">
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Institute Name</label>
                                                                        <input type="text" class="form-control" name="training_training_institute[]" value="{{$i['training_institute']}}">
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Address</label>
                                                                        <input type="text" class="form-control" name="training_address[]" value="{{$i['address']}}">
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Country</label>
                                                                        <input type="text" class="form-control" name="training_country_id[]" value="{{$i['country_id']}}">
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Training Period</label>
                                                                        <div class="input-group">
                                                                            <input type="date" name="training_start_date[]" class="form-control" value="" value="{{$i['start_date']}}">
                                                                            <div class="input-group-addon">to</div>
                                                                            <input type="date" name="training_end_date[]" class="form-control" value="" value="{{$i['end_date']}}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Covered Topics</label>
                                                                        <input type="text" class="form-control" name="training_topics[]" value="{{$i['topics']}}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-danger position-absolute" aria-label="Remove" disabled="" style="top: 0px; right: 5px; position: absolute;"><i class="voyager-trash"></i></button></div>

                                                    @endforeach
                                                @endif
                                            <!--End show to saved data -->

                                            </div>
                                            <button type="button" class="btn btn-block btn-warning mb-3"
                                                    onclick="addTraining()">
                                                <i class="voyager-plus"></i> Add More Training
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 5: Skills -->
                                <div class="form-step" data-step="5">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="mb-0"><i class="voyager-tools"></i> Skill Information</h4>
                                        </div>
                                        <div class="card-body">
                                            <div id="skills">

                                                <!--Start show to saved data -->
                                                @if($skills)
                                                    @foreach($skills as $i)
                                                        <div class="card mb-3 position-relative">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Skill Name</label>
                                                                        <input type="text" class="form-control" name="skill_title[]" value="{{$i['title']}}">
                                                                    </div>

                                                                    <div class="col-md-6 mb-3">
                                                                        <label class="form-label">Score</label>
                                                                        <div class="input-group">
                                                                            <input type="number" min="0" name="skill_result_score[]" class="form-control"  value="{{$i['result_score']}}">
                                                                            <div class="input-group-addon">out of</div>
                                                                            <input type="number" min="0" name="skill_exam_score[]" class="form-control"  value="{{$i['exam_score']}}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-12 mb-3">
                                                                        <label class="form-label">Institute Name</label>
                                                                        <input type="text" class="form-control" name="skill_institute_name[]" value="{{$i['institute_name']}}">
                                                                    </div>

                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-12 mb-3">
                                                                        <label class="form-label">Details</label>
                                                                        <textarea class="form-control" name="skill_details[]" rows="2">{{$i['details']}}</textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-danger position-absolute" aria-label="Remove" disabled="" style="top: 0px; right: 5px; position: absolute;"><i class="voyager-trash"></i></button></div>

                                                    @endforeach
                                                @endif
                                            <!--End show to saved data -->


                                            </div>
                                            <button type="button" class="btn btn-block btn-warning mb-3"
                                                    onclick="addSkill()">
                                                <i class="voyager-plus"></i> Add More Skill
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 6: Personal Information -->
                                <div class="form-step" data-step="6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="mb-0"><i class="voyager-home"></i> Personal Information</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['father_name'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mother_name'], 'width'=>'6'])
                                            </div>
                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['gender'], 'width'=>'4'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['blood_group'], 'width'=>'4'])
                                                <div class="form-group  col-md-4">
                                                    <label for="name">Status </label>
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
                                            </div>
                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['secondary_email'], 'width'=>'4'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['secondary_mobile'], 'width'=>'4'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['emergency_mobile'], 'width'=>'4'])
                                            </div>
                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nationality'], 'width'=>'4'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['height'], 'width'=>'4'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['weight'], 'width'=>'4'])
                                            </div>
                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['present_address_house'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['permanent_address_house'], 'width'=>'6'])
                                            </div>

                                            <div class="row">
                                                <hr />
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['drive_link'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['facebook_link'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['youtube_link'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['linkedin_link'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['twitter_link'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['instagram_link'], 'width'=>'6'])
                                                <hr />
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['remarks'], 'width'=>'12'])
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 7: Documents -->
                                <div class="form-step" data-step="7">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="mb-0"><i class="voyager-home"></i>Documents</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <table style="width: 100%">
                                                    <tr>
                                                        <td  style="width: 50%"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nid_file_path'], 'width'=>'12'])</td>
                                                        <td  style="width: 50%"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_file_path'], 'width'=>'12'])</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2"  style="width: 100%"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['cv_file_path'], 'width'=>'12'])</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2"  style="width: 100%"> @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['skill_certificate_file_path'], 'width'=>'12'])</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 50%">  @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['full_photo_file_path'], 'width'=>'12'])</td>
                                                        <td style="width: 50%">  @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['half_photo_file_path'], 'width'=>'12'])</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigation Buttons -->
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-dark" onclick="previousStep()"
                                            id="prevBtn" style="display: none; float: left">
                                        <i class="voyager-back"></i> Previous
                                    </button>
                                    <button type="button" class="btn btn-voyager" onclick="nextStep()" id="nextBtn" style="float: right">
                                        Next <i class="voyager-forward"></i>
                                    </button>
                                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none; float: right">
                                        <i class="voyager-check"></i> Submit CV
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
@stop

@section('javascript')
    {{-- <script>
         $('document').ready(function () {
             $('.toggleswitch').bootstrapToggle();
         });
     </script>--}}

    <script>

        let currentStep = 1;
        const totalSteps = 7;

        function createDeleteButton(containerId) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-danger position-absolute';
            btn.style.top = '0px';
            btn.style.right = '5px';
            btn.style.position = 'absolute';
            btn.innerHTML = '<i class="voyager-trash"></i>';
            btn.setAttribute('aria-label', 'Remove');
            btn.onclick = function () {
                const container = document.getElementById(containerId);
                if (container.childElementCount > 1) {
                    this.parentElement.remove();
                    updateDeleteButtons(containerId);
                }
            };
            return btn;
        }

        function updateDeleteButtons(containerId) {
            const container = document.getElementById(containerId);
            const cards = container.querySelectorAll('.card');

            cards.forEach(card => {
                const btn = card.querySelector('.btn-danger');
                if (btn) btn.disabled = (cards.length === 1);
            });
        }

        function addEmployment() {
            const container = document.getElementById('employment-history');
            const div = document.createElement('div');
            div.className = 'card mb-3 position-relative';
            div.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Designation</label>
                        <input type="text" class="form-control" name="experience_designation[]">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control" name="experience_company_name[]">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Company Location</label>
                        <input type="text" class="form-control" name="experience_company_address[]">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employment Period</label>
                        <div class="input-group">
                            <input type="date" name="experience_start_date[]" class="form-control" value="2012-04-05">
                            <div class="input-group-addon">to</div>
                            <input type="date" name="experience_end_date[]" class="form-control" value="2012-04-19">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Duration</label>
                        <input type="text" class="form-control" name="experience_duration[]" placeholder="e.g., 3 years">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Responsibility</label>
                    <textarea class="form-control" name="experience_responsibilities[]" rows="2"></textarea>
                </div>
            </div>

        `;
            div.appendChild(createDeleteButton('employment-history'));
            container.appendChild(div);
            updateDeleteButtons('employment-history');
        }

        function addAcademic() {
            const container = document.getElementById('academic-qualification');
            const div = document.createElement('div');
            div.className = 'card mb-3 position-relative';
            div.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Course Name</label>
                        <input type="text" class="form-control" name="education_exam_name[]">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Concentration/Major/Group</label>
                        <input type="text" class="form-control" name="education_subject_group_major[]">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Level of Education</label>
                        <input type="text" class="form-control" name="education_education_label[]">
                    </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Institute Name</label>
                        <input type="text" class="form-control" name="education_institute_name[]">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Board Name</label>
                        <input type="text" class="form-control" name="education_board_name[]">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Result (CGPA)</label>
                        <input type="text" class="form-control" name="education_result[]">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Scale</label>
                        <input type="text" class="form-control" name="education_scale[]">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Year Of Passing</label>
                        <input type="number" min="1900" max="2025" class="form-control" name="education_passing_year[]">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Duration (year)</label>
                        <input type="number" min="0" class="form-control" name="education_duration_year[]">
                    </div>
                </div>
            </div>
        `;
            div.appendChild(createDeleteButton('academic-qualification'));
            container.appendChild(div);
            updateDeleteButtons('academic-qualification');
        }

        function addTraining() {
            const container = document.getElementById('training');
            const div = document.createElement('div');
            div.className = 'card mb-3 position-relative';
            div.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Training Title</label>
                        <input type="text" class="form-control" name="training_title[]">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Institute Name</label>
                        <input type="text" class="form-control" name="training_training_institute[]">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="training_address[]">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" class="form-control" name="training_country_id[]">
                    </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Training Period</label>
                        <div class="input-group">
                            <input type="date" name="training_start_date[]" class="form-control" value="">
                            <div class="input-group-addon">to</div>
                            <input type="date" name="training_end_date[]" class="form-control" value="">
                        </div>
                    </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Covered Topics</label>
                        <input type="text" class="form-control" name="training_topics[]">
                    </div>
                </div>
            </div>
        `;
            div.appendChild(createDeleteButton('training'));
            container.appendChild(div);
            updateDeleteButtons('training');
        }

        function addSkill() {
            const container = document.getElementById('skills');
            const div = document.createElement('div');
            div.className = 'card mb-3 position-relative';
            div.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Skill Name</label>
                        <input type="text" class="form-control" name="skill_title[]">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Score</label>
                        <div class="input-group">
                            <input type="number" min="0" name="skill_result_score[]" class="form-control" value="">
                            <div class="input-group-addon">out of</div>
                            <input type="number" min="0" name="skill_exam_score[]" class="form-control" value="">
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Institute Name</label>
                        <input type="text" class="form-control" name="skill_institute_name[]">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Details</label>
                        <textarea class="form-control" name="skill_details[]" rows="2"></textarea>
                    </div>
                </div>
            </div>
        `;
            div.appendChild(createDeleteButton('skills'));
            container.appendChild(div);
            updateDeleteButtons('skills');
        }

        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(el => {
                el.classList.remove('active');
                el.style.display = 'none';
            });

            // Show current step
            const currentStepEl = document.querySelector(`.form-step[data-step="${step}"]`);
            if (currentStepEl) {
                currentStepEl.classList.add('active');
                currentStepEl.style.display = 'block';
            }

            // Update sidebar navigation
            document.querySelectorAll('.nav-step').forEach((el, index) => {
                el.classList.remove('active', 'completed');
                if (index + 1 < step) el.classList.add('completed');
                if (index + 1 === step) el.classList.add('active');
            });

            // Update button visibility
            document.getElementById('prevBtn').style.display = step > 1 ? 'block' : 'none';
            document.getElementById('nextBtn').style.display = step < totalSteps ? 'block' : 'none';
            document.getElementById('submitBtn').style.display = step === totalSteps ? 'block' : 'none';
        }

        function nextStep() {
            if (validateCurrentStep() && currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        }

        function validateCurrentStep() {
            const currentStepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
            const requiredFields = currentStepElement.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                toastr.error('Please fill in all required fields before proceeding.');
            }

            return isValid;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            addEmployment();
            addAcademic();
            addSkill();
            addTraining();
            showStep(1);


            // Add sidebar navigation
            document.querySelectorAll('.nav-step').forEach((el, index) => {
                el.onclick = () => {
                    if (index + 1 <= currentStep || validateCurrentStep()) {
                        currentStep = index + 1;
                        showStep(currentStep);
                    }
                };
            });
        });

        // Form submission with validation
        document.getElementById('cvForm').onsubmit = function (e) {
            if (!validateCurrentStep()) {
                e.preventDefault();
                return false;
            }
        };
    </script>
@stop
