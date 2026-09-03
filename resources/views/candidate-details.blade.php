@extends('layout')

@section('title',  $data->name)


@section('header')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /*
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        */

        #new-profile{


            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                overflow: hidden;
                display: flex;
                min-height: 80vh;
            }

            /* Left Panel */
            .left-panel {
                width: 350px;
                background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
                padding: 30px 25px;
                display: flex;
                flex-direction: column;
                align-items: center;
                position: relative;
            }

            .profile-image {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="%23667eea"/><circle cx="50" cy="35" r="15" fill="white"/><ellipse cx="50" cy="75" rx="20" ry="25" fill="white"/></svg>');
                background-size: cover;
                margin-bottom: 20px;
                border: 4px solid white;
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                transition: transform 0.3s ease;
            }

            .profile-image:hover {
                transform: scale(1.05);
            }

            .edit-icon {
                position: absolute;
                top: 140px;
                right: 120px;
                width: 25px;
                height: 25px;
                background: #667eea;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 12px;
                cursor: pointer;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }

            .profile-name {
                font-size: 28px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 5px;
                text-align: center;
            }

            .profile-id {
                color: #6c757d;
                font-size: 14px;
                margin-bottom: 8px;
            }

            .profile-role {
                color: #667eea;
                font-size: 16px;
                font-weight: 500;
                margin-bottom: 30px;
            }

            .basic-info {
                width: 100%;
            }

            .basic-info h3 {
                font-size: 18px;
                color: #2c3e50;
                margin-bottom: 20px;
                border-bottom: 2px solid #667eea;
                padding-bottom: 8px;
            }

            .info-item {
                display: flex;
                align-items: center;
                margin-bottom: 18px;
                padding: 8px 0;
            }

            .info-icon {
                width: 20px;
                height: 20px;
                margin-right: 15px;
                color: #6c757d;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .info-content {
                flex: 1;
            }

            .info-label {
                font-size: 12px;
                color: #6c757d;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 2px;
            }

            .info-value {
                font-size: 14px;
                color: #2c3e50;
                font-weight: 500;
            }

            /* Right Panel */
            .right-panel {
                flex: 1;
                background: white;
            }

            /* Tab Navigation */
            .tab-navigation {
                display: flex;
                background: #f8f9fa;
                border-bottom: 1px solid #e9ecef;
                padding: 0 20px;
            }

            .tab {
                padding: 15px 20px;
                background: #6c757d;
                color: white;
                border: none;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                margin-right: 2px;
                border-radius: 8px 8px 0 0;
                transition: all 0.3s ease;
            }

            .tab.active {
                background: #2c3e50;
                transform: translateY(-2px);
            }

            .tab:hover {
                background: #495057;
                transform: translateY(-2px);
            }

            /* Content Area */
            .content-area {
                padding: 30px;
                height: calc(100% - 60px);
                overflow-y: auto;
            }

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
                animation: fadeIn 0.4s ease-out;
            }

            .section {
                background: white;
                border-radius: 12px;
                padding: 25px;
                margin-bottom: 25px;
                border: 1px solid #e9ecef;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                transition: all 0.3s ease;
            }

            .section:hover {
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                transform: translateY(-2px);
            }

            .section-header {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
            }

            .section-icon {
                width: 35px;
                height: 35px;
                border-radius: 8px;
                margin-right: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
            }

            .professional-icon {
                background: linear-gradient(45deg, #667eea, #764ba2);
            }

            .address-icon {
                background: linear-gradient(45deg, #f093fb, #f5576c);
            }

            .tax-icon {
                background: linear-gradient(45deg, #4facfe, #00f2fe);
            }

            .section-title {
                font-size: 20px;
                font-weight: 600;
                color: #2c3e50;
                flex: 1;
            }

            .edit-section {
                color: #6c757d;
                cursor: pointer;
                font-size: 16px;
                transition: color 0.3s ease;
            }

            .edit-section:hover {
                color: #667eea;
            }

            .field-group {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 20px;
            }

            .field {
                display: flex;
                flex-direction: column;
            }

            .field-label {
                font-size: 14px;
                color: #6c757d;
                margin-bottom: 8px;
                font-weight: 500;
            }

            .field-value {
                font-size: 16px;
                color: #2c3e50;
                font-weight: 500;
                padding: 8px 0;
            }

            /* Animations */
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Responsive */
            @media (max-width: 768px) {
                .container {
                    flex-direction: column;
                }

                .left-panel {
                    width: 100%;
                }

                .field-group {
                    grid-template-columns: 1fr;
                }
            }
        }

    </style>

@stop


@section('content')


    <!-- Breadcrumb Section Start -->
    <div class="breadcrumb-section section bg_color--5 pt-60 pt-sm-50 pt-xs-40 pb-60 pb-sm-50 pb-xs-40">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page-breadcrumb-content mb-40">
                        <ul class="page-breadcrumb">
                            <li><a href="{{url('/')}}">Home</a></li>
                            <li><a href="{{route('web.candidates')}}">Candidates</a></li>
                            <li>{{$data->name}}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="candidate-head-info">
                        <div class="candidate-logo">
                            <a href="#">
                                <img src="{{ Storage::disk(config('voyager.storage.disk'))->exists( $data->half_photo_file_path)? Voyager::image( $data->half_photo_file_path) : Voyager::image( setting('admin.icon_image')) }}" alt="">
                            </a>
                        </div>
                        <div class="candidate-content">
                            <h1 class="candidate-name">{{$data->name}} <i class="fas fa-check-circle"></i></h1>
                            <ul class="candidate-meta">
                                @if($data->position)<li class="candidate-headline">{{$data->position}}</li>@endif
                                <li class="view-candidate"><i class="lnr lnr-graduation-hat"></i>Abord: {{($data->latestExamResult)?$data->latestExamResult->abroad_ex:0}} years</li>
                                <li class="view-candidate"><i class="lnr lnr-map-marker"></i>Local: {{($data->latestExamResult)?$data->latestExamResult->local_ex:0}} years</li>
                                {{--                                <li class="view-candidate"><i class="lnr lnr-thumbs-up"></i>English: {{($data->latestExamResult)?$data->latestExamResult->english:0}} Marks</li>--}}
                                {{--<li class="date-publish">
                                    <i class="lnr lnr-clock"></i>
                                    Candidate Since: <label class="theme-color">{{$data->created_at}}</label>
                                </li>--}}
                            </ul>
                        </div>
                    </div>
                </div>
                @if(Auth::user())
                    <div class="col-lg-5">
                        <div class="sidebar-wrapper-three">
                            <div class="common-sidebar-widget sidebar-three mb-0 pb-0">
                                <div class="sidebar-job-share">
                                    <div class="job-share candidate-action">
                                        <ul>
                                            <li style="{{(\App\Helpers\CommonClass::checkEmployerCandidates($data->id, \App\Helpers\CommonClass::user()->employer_id, 'FAVORITE'))?'background: green;color: white;':'background: white;'}}">
                                                <a href="#" onclick="makeCandidateForMe(this,`{{$data->id}}`, `{{\App\Helpers\CommonClass::user()->employer_id}}`, 'FAVORITE')"><i class="lnr lnr-heart"></i> <span class="text">Favorite  </span></a>
                                            </li>
                                            <li  style="{{(\App\Helpers\CommonClass::checkEmployerCandidates($data->id, \App\Helpers\CommonClass::user()->employer_id, 'RESERVE'))?'background: green;color: white;':'background: white;'}}">
                                                <a href="#" onclick="makeCandidateForMe(this,`{{$data->id}}`, `{{\App\Helpers\CommonClass::user()->employer_id}}`, 'RESERVE')"><i class="lnr lnr-star"></i> <span class="text">Reserve  </span></a>
                                            </li>
                                            <li  style="{{(\App\Helpers\CommonClass::checkEmployerCandidates($data->id, \App\Helpers\CommonClass::user()->employer_id, 'SELECTED'))?'background: green;color: white;':'background: white;'}}">
                                                <a href="#" onclick="makeCandidateForMe(this,`{{$data->id}}`, `{{\App\Helpers\CommonClass::user()->employer_id}}`, 'SELECTED')"><i class="lnr lnr-user"></i> <span class="text">Selected </span></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Breadcrumb Section Start -->

    <!-- Job Details Section Start -->
    <div id="new-profile" class="job-details-section section pt-120 pt-lg-100 pt-md-80 pt-sm-50 pt-xs-40 pb-120 pb-lg-100 pb-md-80 pb-sm-60 pb-xs-50">

        <div class="container">
            <!-- Left Panel -->
            <div class="left-panel">
                {{--<div class="profile-image"></div>--}}
                {{--                <div class="edit-icon">✏️</div>--}}

                {{--<h2 class="profile-name">{{$data->name}}</h2>
                <div class="profile-id">{{$data->code}} 🔗</div>
                <div class="profile-role">{{$data->position}}</div>--}}

                <div class="basic-info">
                    <h3>Basic Information</h3>
                    <div class="info-item">
                        <div class="info-icon">✉️</div>
                        <div class="info-content">
                            <div class="info-label">Email</div>
                            <div class="info-value">{{$data->email}}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">📱</div>
                        <div class="info-content">
                            <div class="info-label">Mobile Phone</div>
                            <div class="info-value">{{$data->mobile}}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">🌍</div>
                        <div class="info-content">
                            <div class="info-label">Nationality</div>
                            <div class="info-value">{{$data->nationality}}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">👤</div>
                        <div class="info-content">
                            <div class="info-label">Gender</div>
                            <div class="info-value">{{($data->gender=='M')?'Male':'Female'}}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">🎂</div>
                        <div class="info-content">
                            <div class="info-label">Date Of Birth</div>
                            <div class="info-value">{{$data->dob}}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">📚</div>
                        <div class="info-content">
                            <div class="info-label">Class Group</div>
                            <div class="info-value">{{($data->classGroup)?str_replace('Rapid Interview For','',$data->classGroup->name):''}}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">📚</div>
                        <div class="info-content">
                            <div class="info-label">Passport No.</div>
                            <div class="info-value">{{$data->passport_no}}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">💼</div>
                        <div class="info-content">
                            <div class="info-label">Status</div>
                            <div class="info-value">{{($data->status == 'A')?'Active':'Pending'}}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">💼</div>
                        <div class="info-content">
                            <div class="info-label">Experience Year</div>
                            <div class="info-value">
                                <b>Aboard:</b> {{$data->abroad_ex}} Years, <b>Local:</b> {{$data->local_ex}} Years
                            </div>
                        </div>
                    </div>

                    @if($languageList)
                        <div class="info-item">
                            <div class="info-icon">📚</div>
                            <div class="info-content">
                                <div class="info-label">Languages</div>
                                @foreach($languageList as $i)
                                    <div class="info-value">* {{$i->language_name}}: {{$i->language_status}}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="info-item">
                        <div class="info-content">
                            <div class="info-label">Expertises</div>
                            {!! $data->expertise !!}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-content">
                            <div class="info-label">Skills</div>
                            {!! $data->skills !!}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-content">
                            <div class="info-label">Attributes</div>
                            {!! $data->attribute !!}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-content">
                            <div class="info-label">Extra Curricular</div>
                            {!! $data->extra_curricular !!}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-content">
                            <div class="info-label">Interest</div>
                            {!! $data->interest !!}
                        </div>
                    </div>

                    <div style="display: none">
                    @if($expertiseList)
                        <div class="info-item">
                            <div class="info-content">
                                <div class="info-label">Expertises</div>
                                {!! $data->expertise !!}
                                @foreach($expertiseList as $i)
                                    <div class="info-value">* {{$i->expertise_name}}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($skillList)
                        <div class="info-item">
                            <div class="info-content">
                                <div class="info-label">Skills</div>
                                @foreach($skillList as $i)
                                    <div class="info-value">* {{$i->skill_name}}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($attributeList)
                        <div class="info-item">
                            <div class="info-content">
                                <div class="info-label">Attributes</div>
                                @foreach($attributeList as $i)
                                    <div class="info-value">* {{$i->attribute_name}}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($curricularList)
                        <div class="info-item">
                            <div class="info-content">
                                <div class="info-label">Extra Curricular</div>
                                @foreach($curricularList as $i)
                                    <div class="info-value">* {{$i->curricular_name}}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($interestList)
                        <div class="info-item">
                            <div class="info-content">
                                <div class="info-label">Interest</div>
                                @foreach($interestList as $i)
                                    <div class="info-value">* {{$i->interest_name}}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    </div>

                    <div class="info-item">
                        <div class="info-content">
                            <div class="info-label"># Social Links</div>
                            <ul class="attributes-list">
                                <li><a href="{{$data->drive_link}}">Google Drive Skills</a></li>
                                <li><a  href="{{$data->facebook_link}}" >Facebook</a></li>
                                <li><a  href="{{$data->youtube_link}}" >Youtube</a></li>
                                <li> <a href="{{$data->linkedin_link}}" >Linkedin</a></li>
                                <li>  <a  href="{{$data->twitter_link}}" >Twitter</a> </li>
                                <li>  <a href="{{$data->instagram_link}}" >Instagram</a></li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right Panel -->
            <div class="right-panel">
                <!-- Tab Navigation -->
                <div class="tab-navigation">
                    <button class="tab active" onclick="showTab('basic-info')">Basic Info.</button>
                    <button class="tab " onclick="showTab('employment-history')">Employment</button>
                    <button class="tab" onclick="showTab('academic-qualification')">Academic</button>
                    <button class="tab" onclick="showTab('training')">Training</button>
                    <button class="tab" onclick="showTab('skill')">Skill</button>
                    <button class="tab" onclick="showTab('personal-info')">Personal Info.</button>
{{--                    <button class="tab" onclick="showTab('document')">Document</button>--}}
                </div>

                <!-- Content Area -->
                <div class="content-area">
                    <!-- Basic Info Tab Content -->
                    <div class="tab-content active" id="basic-info">
                        <div class="section">
                            <div class="section-header">
                                <div class="section-icon professional-icon">💼</div>
                                <h3 class="section-title">About Me</h3>
                                {{--                                <div class="edit-section">✏️</div>--}}
                            </div>
                            <p> {!! $data->basic_info_career !!}</p>
                        </div>
                    </div>

                    <!-- Employment History Tab Content -->
                    <div class="tab-content" id="employment-history">
                        <div class="section">
                            <div class="section-header">
                                <div class="section-icon professional-icon">💼</div>
                                <h3 class="section-title">Employment History</h3>
                                {{--                                <div class="edit-section">✏️</div>--}}
                            </div>


                            @if($expriences)
                                @foreach($expriences as $v)
                                    <div class="field-group">
                                        <div class="field">
                                            <div class="field-label">Designation</div>
                                            <div class="field-value">{{$v->designation}}</div>
                                        </div>
                                        <div class="field">
                                            <div class="field-label">Company Name</div>
                                            <div class="field-value">{{$v->company_name}}</div>
                                        </div>
                                    </div>

                                    <div class="field-group">
                                        <div class="field">
                                            <div class="field-label">Duration</div>
                                            <div class="field-value">{{date('M, Y', strtotime($v->start_date))}} - {{!empty($v->end_date)?date('M, Y', strtotime($v->end_date)):'Present'}}</div>
                                        </div>
                                        <div class="field">
                                            <div class="field-label">Location</div>
                                            <div class="field-value">{{$v->company_address}}</div>
                                        </div>
                                    </div>
                                    <hr />
                                @endforeach
                            @endif

                        </div>
                    </div>

                    <!-- Academic Qualification Tab Content -->
                    <div class="tab-content" id="academic-qualification">
                        <div class="section">
                            <div class="section-header">
                                <div class="section-icon professional-icon">🎓</div>
                                <h3 class="section-title">Educational Background</h3>
                                {{--                                <div class="edit-section">✏️</div>--}}
                            </div>

                            @if($educations)
                                @foreach($educations as $v)
                                    <div class="field-group">
                                        <div class="field">
                                            <div class="field-label">Level of Education</div>
                                            <div class="field-value">{{$v->exam_name}}</div>
                                        </div>
                                        <div class="field">
                                            <div class="field-label">Board</div>
                                            <div class="field-value">{{$v->board_name}}</div>
                                        </div>
                                    </div>

                                    <div class="field-group">
                                        <div class="field">
                                            <div class="field-label">Institution</div>
                                            <div class="field-value">{{$v->institute_name}}</div>
                                        </div>
                                        <div class="field">
                                            <div class="field-label">Passing Year</div>
                                            <div class="field-value"> {{$v->passing_year}}</div>
                                        </div>
                                    </div>

                                    <div class="field-group">
                                        <div class="field">
                                            <div class="field-label">GPA/CGPA</div>
                                            <div class="field-value">{{$v->result}}</div>
                                        </div>
                                        <div class="field">
                                            <div class="field-label">Major/Group</div>
                                            <div class="field-value">{{$v->subject_group_major}}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                        </div>
                    </div>

                    <!-- Training Tab Content -->
                    <div class="tab-content" id="training">
                        <div class="section">
                            <div class="section-header">
                                <div class="section-icon address-icon">📚</div>
                                <h3 class="section-title">Training</h3>
                                {{--                                <div class="edit-section">✏️</div>--}}
                            </div>

                            @if($trainings)
                                @foreach($trainings as $v)
                                    <div class="field-group">
                                        <div class="field">
                                            <div class="field-label">Certification</div>
                                            <div class="field-value">{{$v->title}}</div>
                                        </div>
                                        <div class="field">
                                            <div class="field-label">Institute</div>
                                            <div class="field-value">{{$v->institute_name}}</div>
                                        </div>

                                    </div>


                            <div class="field-group">
                                <div class="field">
                                    <div class="field-label">Topics</div>
                                    <div class="field-value">{{$v->topics}}</div>
                                </div>
                                <div class="field">
                                    <div class="field-label">Period</div>
                                    <div class="field-value">{{date('M, Y', strtotime($v->start_date))}} - {{!empty($v->end_date)?date('M, Y', strtotime($v->end_date)):'Present'}}</div>
                                </div>
                            </div>
 @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Skill Tab Content -->
                    <div class="tab-content" id="skill">
                        <div class="section">
                            <div class="section-header">
                                <div class="section-icon tax-icon">⚡</div>
                                <h3 class="section-title">Skills</h3>
{{--                                <div class="edit-section">✏️</div>--}}
                            </div>

                            @if($skills)
                                @foreach($skills as $v)
                                    <div class="field-group">
                                        <div class="field">
                                            <div class="field-label">Title</div>
                                            <div class="field-value">{{$v->title}}</div>
                                        </div>
                                        <div class="field">
                                            <div class="field-label">Institute</div>
                                            <div class="field-value">{{$v->institute_name}}</div>
                                        </div>
                                    </div>
                                    <div class="field-group">
                                        <div class="field">
                                            <div class="field-label">Result</div>
                                            <div class="field-value">{{$v->result_score}}/{{$v->exam_score}}</div>
                                        </div>
                                        <div class="field">
                                            <div class="field-label">Details</div>
                                            <div class="field-value"> {{$v->details}}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif


                        </div>
                    </div>

                    <!-- Personal Info Tab Content -->
                    <div class="tab-content" id="personal-info">
                        <div class="section">
                            <div class="section-header">
                                <div class="section-icon address-icon">🏠</div>
                                <h3 class="section-title">Personal Info</h3>
{{--                                <div class="edit-section">✏️</div>--}}
                            </div>

                            <div class="field-group">
                                <div class="field">
                                    <div class="field-label">Father's Name</div>
                                    <div class="field-value">{{$data->father_name}}</div>
                                </div>
                                <div class="field">
                                    <div class="field-label">Mother's Name</div>
                                    <div class="field-value">{{$data->mother_name}}</div>
                                </div>
                            </div>

                            <div class="field-group">
                                <div class="field">
                                    <div class="field-label">Blood Group</div>
                                    <div class="field-value">{{$data->blood_group}}</div>
                                </div>
                                <div class="field">
                                    <div class="field-label">BID No.</div>
                                    <div class="field-value">{{$data->bid}}</div>
                                </div>
                            </div>
                        </div>

                        <div class="section">
                            <div class="section-header">
                                <div class="section-icon tax-icon">💰</div>
                                <h3 class="section-title">Address</h3>
{{--                                <div class="edit-section">✏️</div>--}}
                            </div>

                            <div class="field-group">
                                <div class="field">
                                    <div class="field-label">Present Address</div>
                                    <div class="field-value">{{$data->present_address_house}}</div>
                                </div>
                                <div class="field">
                                    <div class="field-label">Permanent Address </div>
                                    <div class="field-value">{{$data->permanent_address_house}}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Tab Content -->
                    {{--<div class="tab-content" id="document">
                        <div class="section">
                            <div class="section-header">
                                <div class="section-icon professional-icon">📄</div>
                                <h3 class="section-title">Documents</h3>
--}}{{--                                <div class="edit-section">✏️</div>--}}{{--
                            </div>

                            <div class="field-group">
                                <div class="field">
                                    <div class="field-label">Passport Number</div>
                                    <div class="field-value">SG1234567</div>
                                </div>
                                <div class="field">
                                    <div class="field-label">Expiry Date</div>
                                    <div class="field-value">December 2030</div>
                                </div>
                            </div>

                            <div class="field-group">
                                <div class="field">
                                    <div class="field-label">Work Permit</div>
                                    <div class="field-value">WP-2023-AA1234</div>
                                </div>
                                <div class="field">
                                    <div class="field-label">Status</div>
                                    <div class="field-value">Valid</div>
                                </div>
                            </div>
                        </div>
                    </div>--}}


                </div>
            </div>
        </div>


    </div>
    <!-- Job Details Section End -->



@stop



@section('footer')

    <script>
        function makeCandidateForMe(btn, candidate_id,employer_id, purpose){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                //url:  "{{url('/panel/ajax/employer-candidate/add-remove')}}",
                url:  "{{route('web.ajax.employer-candidate-add-remove')}}",
                type:"POST",
                data: {
                    'candidate_id':candidate_id,
                    'employer_id':employer_id,
                    'purpose':purpose
                },
                beforeSend: function() {
                },
                success:function(res){
                    if(res.status){
                        btn.style.backgroundColor = "green";
                        btn.style.color = "white";
                        toastr.success(res.msg);
                    }else{
                        btn.style.backgroundColor = "white";
                        btn.style.color = "green";
                        toastr.success(res.msg);
                    }
                },
            });
        }

    </script>

    <script>
        function showTab(tabId) {
            // Hide all tab contents
            const allTabContents = document.querySelectorAll('.tab-content');
            allTabContents.forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all tabs
            const allTabs = document.querySelectorAll('.tab');
            allTabs.forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content
            const selectedContent = document.getElementById(tabId);
            if (selectedContent) {
                selectedContent.classList.add('active');
            }

            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Edit button functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('edit-section') || e.target.classList.contains('edit-icon')) {
                e.target.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    e.target.style.transform = 'scale(1)';
                }, 200);
            }
        });

        // Section hover animations
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('.section');

            sections.forEach(section => {
                section.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                });

                section.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
@stop
