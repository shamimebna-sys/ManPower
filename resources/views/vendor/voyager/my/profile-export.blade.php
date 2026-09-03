<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{--    <link rel="stylesheet" href="{{url('/panel/voyager-assets?path=css%2Fapp.css')}}">--}}
    <link href="{{ asset('admin/bootstrap.5.3.0.min.css') }}" rel="stylesheet">
    <style>

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.2;
            color: #333;
            background: white;
        }

        .header-name {
            font-size: 32px;
            font-weight: bold;
            color: #000;
            text-align: center;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }

        .header-position {
            font-size: 14px;
            text-align: center;
            margin-bottom: 5px;
            color: #666;
        }

        .header-info {
            font-size: 11px;
            text-align: center;
            margin-bottom: 15px;
            color: #666;
        }

        .section-header {
            background-color: #4a4a4a;
            color: white;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .section-header:first-of-type {
            margin-top: 0;
        }

        .left-column {
            background-color: #f8f8f8;
            padding: 15px;
            min-height: 100vh;
        }

        .right-column {
            padding: 15px;
        }

        .contact-item {
            margin-bottom: 5px;
            font-size: 11px;
        }

        .contact-label {
            font-weight: bold;
            display: inline-block;
            width: 50px;
        }

        .skills-list {
            list-style: none;
            padding: 0;
        }

        .skills-list li {
            margin-bottom: 8px;
            font-size: 11px;
            position: relative;
            padding-left: 8px;
        }

        .skills-list li:before {
            content: "•";
            position: absolute;
            left: 0;
        }

        .job-title {
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .company-name {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .job-duration {
            font-style: italic;
            font-size: 10px;
            color: #666;
            margin-bottom: 2px;
        }

        .job-location {
            font-size: 10px;
            color: #666;
            margin-bottom: 8px;
        }

        .job-responsibilities {
            list-style: none;
            padding: 0;
            margin-bottom: 15px;
        }

        .job-responsibilities li {
            margin-bottom: 3px;
            font-size: 11px;
            position: relative;
            padding-left: 12px;
        }

        .job-responsibilities li:before {
            content: "➢";
            position: absolute;
            left: 0;
            color: #666;
        }

        .education-item {
            margin-bottom: 15px;
        }

        .education-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .education-details {
            font-size: 10px;
            line-height: 1.3;
        }

        .personal-info-item {
            margin-bottom: 3px;
            font-size: 11px;
        }

        .personal-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .attributes-list {
            list-style: none;
            padding: 0;
        }

        .attributes-list li {
            margin-bottom: 5px;
            font-size: 11px;
            position: relative;
            padding-left: 8px;
        }

        .attributes-list li:before {
            content: "•";
            position: absolute;
            left: 0;
        }

        .declaration {
            text-align: justify;
            font-size: 11px;
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .cv-number {
            position: absolute;
            bottom: 10px;
            left: 15px;
            font-size: 10px;
            color: #666;
        }
    </style>
    {{--<style>
        body {
            margin: 0;
            font-family: sans-serif;
            font-size: small;
        }

        @page {
            footer: page-footer;
            /*margin: 0;*/
            margin-top: 35pt;
            margin-bottom: 50pt;
            margin-footer: 18pt;
        }

        @page :first {
            /*margin-top: 0;*/
        }
        .page-break-before { page-break-before: always; }
        .clearfix {
            clear: both;
        }

        table {
            border-collapse: collapse;
        }

    </style>--}}

</head>
<body>

{{--<htmlpageheader name="page-header">
</htmlpageheader>

<htmlpagefooter name="page-footer" class="text-center">
    <p style="text-align: center; font-weight: bold">Page-{PAGENO}</p>
</htmlpagefooter>--}}

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-body">
                    <!-- Resume Content -->

                    <!-- Header -->
                    <div class="row">
                        <div class="col-md-4" style="padding-bottom: 0">
                            <img src="{{ Voyager::image($dataTypeContent->half_photo_file_path)}}"
                                 alt="{{$dataTypeContent->name}}"
                                 style="width: 150px; height: 150px; object-fit: cover; border:1px solid #87878a; border-radius: 50%">
                        </div>
                        <div class="col-md-4 " style="padding-bottom: 0">
                            <div class="header-name">{{$dataTypeContent->name}}</div>
                            <div class="header-position">Position: {{$dataTypeContent->position}}</div>
                            <div class="header-info">
                                Passport No-{{$dataTypeContent->passport_no}}<br>
                                Mobile No- {{$dataTypeContent->mobile}}<br>
                                CV No- {{!empty($dataTypeContent->code)?$dataTypeContent->code:'Unknown'}}
                            </div>
                        </div>
                        <div class="col-md-4 " style="padding-bottom: 0">
                            <img src="{{ Voyager::image(Voyager::setting('admin.icon_image', ''))}}"
                                 alt="{{$dataTypeContent->name}}"
                                 style="width: 150px; height: 150px; object-fit: cover;  border-radius: 0%; float: right">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-4 left-column">

                            <div class="section-header">BASIC INFO.</div>

                            <div class="contact-item">
                                - Code: {!! !empty($dataTypeContent->code)?$dataTypeContent->code:'<span class="text-danger">Unknown</span>' !!}
                            </div>
                            <div class="contact-item">
                                @php
                                    if($dataTypeContent->status == 'P'){
                                        $displayStatus = 'Pending';
                                    }elseif ($dataTypeContent->status == 'A'){
                                         $displayStatus = 'Active';
                                    }else{
                                         $displayStatus = '<span class="text-danger">Unknown</span>';
                                    }

                                $agent = \App\Models\Agent::select('name', 'code')->find(($dataTypeContent->agent_id)?$dataTypeContent->agent_id:0);
                                $classGroup = \App\Models\ClassGroup::select('name', 'code')->find(($dataTypeContent->class_group_id)?$dataTypeContent->class_group_id:0);
                                @endphp
                                <b>- Status:</b> {!! $displayStatus !!}
                            </div>
                            <div class="contact-item">
                                <b>- Agent:</b> {!! ($agent)?'('.$agent->code.') '.$agent->name: '<span class="text-danger">Unknown</span>' !!}
                            </div>
                            <div class="contact-item">
                                <b>- Class Group:</b> {!! ($classGroup)?'('.$classGroup->code.') '.$classGroup->name: '<span class="text-danger">Unknown</span>' !!}
                            </div>

                            <!-- Contact -->
                            <div class="section-header">CONTACT</div>
                            <div class="contact-item">
                                - Mobile {{$dataTypeContent->mobile}}
                            </div>
                            <div class="contact-item">
                                - Email : {{$dataTypeContent->email}}
                            </div>
                            <div class="contact-item">
                                - Passport No- {{$dataTypeContent->passport_no}}
                            </div>

                            <!-- Languages -->
                            <div class="section-header">LANGUAGES</div>
                            <div class="contact-item">- Bengali</div>
                            <div class="contact-item">- English</div>

                            <!-- Skills -->
                            @if($skills)
                                <div class="section-header">SKILLS</div>
                                <ul class="skills-list">
                                    @foreach($skills as $v)
                                        <li>{{$v->title}}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="section-header">Exprience Year</div>
                            <ul class="skills-list">
                                <li> Aboard: {{$dataTypeContent->abroad_ex}} Years</li>
                                <li> Local: {{$dataTypeContent->local_ex}} Years</li>
                            </ul>

                            <!-- Attributes -->
                            <div class="section-header">ATTRIBUTES</div>
                            <ul class="attributes-list">
                                <li>Greet Customers and hand out menus.</li>
                                <li>Take meal and beverage Orders from customers and place these orders in the kitchen.</li>
                                <li>Make menu recommendations and inform patrons of any specials.</li>
                            </ul>

                            <!-- Extra Curricular -->
                            <div class="section-header">EXTRA CURRICULAR</div>
                            <ul class="attributes-list">
                                <li>Deliver meals and beverages to tables when they have been prepared.</li>
                                <li>Check that customers are satisfied with their meal.</li>
                                <li>Prepare the bill and ensure that the correct amount has been paid.</li>
                                <li>Administer change to tables if needed.</li>
                                <li>Prepare tables by setting up linens, silverware about the day's specials.</li>
                                <li>Offer menu recommendations upon request.</li>
                            </ul>

                            <!-- Interest -->
                            <div class="section-header">INTEREST</div>
                            <ul class="attributes-list">
                                <li>Reading Book</li>
                                <li>Traveling</li>
                                <li>Playing</li>
                                <li>Reading Newspaper</li>
                                <li>Internet Browsing</li>
                            </ul>

                            <!-- Social Links -->
                            <div class="section-header">Social Links</div>
                            <ul class="attributes-list">
                                <li><a href="{{$dataTypeContent->drive_link}}">Google Drive Skills</a></li>
                                <li><a  href="{{$dataTypeContent->facebook_link}}" >Facebook</a></li>
                                <li><a  href="{{$dataTypeContent->youtube_link}}" >Youtube</a></li>
                                <li> <a href="{{$dataTypeContent->linkedin_link}}" >Linkedin</a></li>
                                <li>  <a  href="{{$dataTypeContent->twitter_link}}" >Twitter</a> </li>
                                <li>  <a href="{{$dataTypeContent->instagram_link}}" >Instagram</a></li>
                            </ul>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-8 right-column">
                            <!-- About Me -->
                            <div class="section-header">ABOUT ME</div>
                            <p style="text-align: justify; font-size: 11px; margin-bottom: 15px;">
                                {!! $dataTypeContent->basic_info_career !!}
                            </p>

                            <!-- Employment History -->
                            @if($expriences)
                                <div class="section-header">EMPLOYMENT HISTORY</div>
                                @foreach($expriences as $v)
                                    <div class="job-title">{{$v->designation}}</div>
                                    <div class="company-name">{{$v->company_name}} <span class="job-duration">({{date('M, Y', strtotime($v->start_date))}} - {{!empty($v->end_date)?date('M, Y', strtotime($v->end_date)):'Present'}})</span></div>
                                    <div class="job-location">Location: {{$v->company_address}}</div>

                                    {{--<ul class="job-responsibilities">
                                        <li>experience.</li>
                                    </ul>--}}

                                    {{--<div  class="job-description">
                                        {!! $v->descriptions !!}
                                    </div>--}}
                                @endforeach
                            @endif


                        <!-- Academic Qualifications -->
                            @if($educations)
                                <div class="section-header">ACADEMIC QUALIFICATIONS</div>

                                @foreach($educations as $v)
                                    <div class="education-item">
                                        <div class="education-title">{{$v->exam_name}}</div>
                                        <div class="education-details">
                                            ➢ Institute : {{$v->institute_name}}<br>
                                            ➢ Passing Year : {{$v->passing_year}}<br>
                                            ➢ Major/Group : {{$v->subject_group_major}} <br>
                                            ➢ Result : CGPA-{{$v->result}} <br>
                                            ➢ Bord : {{$v->board_name}}
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                        <!-- Training Summary -->
                            @if($trainings)
                                <div class="section-header">TRAINING SUMMARY</div>
                                @foreach($trainings as $v)
                                    <div class="education-item">
                                        <div class="education-title"> {{$v->title}} </div>
                                        <div class="job-location">Period: {{date('M, Y', strtotime($v->start_date))}} - {{!empty($v->end_date)?date('M, Y', strtotime($v->end_date)):'Present'}}</div>
                                        <div class="education-details">
                                            Institute Name: {{$v->institute_name}}<br>
                                            Location: {{$v->address}}<br>
                                            Topics: {{$v->topics}}<br>
                                            @if(!empty($v->descriptions))
                                                Description: {{$v->descriptions}}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                        <!-- Personal Information -->
                            <div class="section-header">PERSONAL INFORMATION</div>
                            <div class="personal-info-item">
                                <span class="personal-label">Name :</span> {{$dataTypeContent->name}}
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">Father's Name :</span> {{$dataTypeContent->father_name}}
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">Mother's Name :</span> {{$dataTypeContent->mother_name}}
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">Date of Birth :</span> {{date('d M Y', strtotime($dataTypeContent->birth_date))}}
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">Nationality :</span> {{$dataTypeContent->nationality}} {{--(by Birth)--}}
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">Religion :</span> Islam
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">Blood Group :</span> {{$dataTypeContent->blood_group}}
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">Sex :</span> {{($dataTypeContent->gender == 'M')?'Male':'female'}}
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">BID No. :</span> {{$dataTypeContent->bid}}
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">National ID No. :</span> {{$dataTypeContent->nid}}
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">Passport Number :</span> {{$dataTypeContent->passport_no}}
                            </div>
                            {{-- <div class="personal-info-item">
                                 <span class="personal-label">Height :</span> 5 Feet 9 Inch
                             </div>
                             <div class="personal-info-item">
                                 <span class="personal-label">Weight :</span> 78 kg
                             </div>
                             <div class="personal-info-item">
                                 <span class="personal-label">Marital status :</span> Unmarried.
                             </div>--}}
                            <div class="personal-info-item">
                                <span class="personal-label">Present Address :</span>  {{$dataTypeContent->present_address_house}}
                            </div>
                            <div class="personal-info-item">
                                <span class="personal-label">Permanent Address :</span> {{$dataTypeContent->permanent_address_house}}
                            </div>

                            <!-- Declaration -->
                            <div class="section-header">DECLARATION</div>
                            <div class="declaration">
                                I affirm that to the best of my knowledge and belief, this resume correctly describes my qualification and me.
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<div style="border-bottom: 1px solid black; padding-top: 3px; padding-bottom: 10px"></div>

<table style="width: 100%; padding-top: 15px" >
    <tr>
        <td style="text-align: center">

            <p>Report Generated By Automatic System at : {{date('Y-m-d H:i A')}}</p>
        </td>
    </tr>
</table>
<br />

</body>
</html>
