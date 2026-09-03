@extends('voyager::master')
@php
    $edit = true;
@endphp
@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @page {
            size: A4;
            margin: 0.5in;
        }

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
            font-size: 16px;
            text-align: center;
            margin-bottom: 5px;
            color: #666;
        }

        .header-info {
            font-size: 13px;
            text-align: center;
            margin-bottom: 15px;
            color: #666;
        }

        .section-header {
            background-color: #4a4a4a;
            color: white;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 14px;
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
            font-size: 13px;
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
            font-size: 13px;
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
            font-size: 14px;
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
            font-size: 12px;
            color: #666;
            margin-bottom: 2px;
        }

        .job-location {
            font-size: 12px;
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
            font-size: 13px;
            margin-bottom: 3px;
        }

        .education-details {
            font-size: 10px;
            line-height: 1.3;
        }

        .personal-info-item {
            margin-bottom: 3px;
            font-size: 13px;
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
            font-size: 13px;
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
            font-size: 13px;
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .cv-number {
            position: absolute;
            bottom: 10px;
            left: 15px;
            font-size: 12px;
            color: #666;
        }

        .custom-list ul, .custom-list ol {
            padding-left: 4%;
        }
        .custom-list li {
            margin-bottom: 8px;
            font-size: 13px;
            position: relative;
            padding-left: 0px;
        }

        .custom-list li:before {
            content: "";
            position: absolute;
            left: 0;
        }
    </style>

@stop

@section('page_title', 'My Profile')

@section('page_header')
    @include('voyager::partials.candidate-partial-top-menu', ['candidate_id' => $dataTypeContent->id])
    
    <h1 class="page-title"> <i class="voyager-double-right"></i> My Profile </h1>
    <a  href="{{route('admin.my.profile.edit')}}" class="btn btn-dark btn-sm  pull-right" >
        <i class="voyager-pen"></i> <span>Edit Profile</span>
    </a>
    <a href="#" class="btn btn-warning btn-sm pull-right" style="margin-right: 1%"  onclick="savePDF()">
        <i class="voyager-documentation"></i> <span> PDF Export</span>
    </a>
@stop

@section('content')

    <div class="page-content browse container-fluid" id="printArea">

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
                                @if($languageList)
                                    <div class="section-header">LANGUAGES</div>
                                    @foreach($languageList as $i)
                                        <div class="contact-item">- {{$i->language_name}}: {{$i->language_status}}</div>
                                    @endforeach
                                @endif

                                <div class="section-header">Exprience Year</div>
                                <div class="custom-list">
                                <ul>
                                    <li> Aboard: {{$dataTypeContent->abroad_ex}} Years</li>
                                    <li> Local: {{$dataTypeContent->local_ex}} Years</li>
                                </ul>
                                </div>

                                <div class="section-header">EXPERTISE</div>
                                <div class="custom-list">
                                {!! $dataTypeContent->expertise !!}
                                </div>

                                <div class="section-header">SKILLS</div>
                                <div class="custom-list">
                                    {!! $dataTypeContent->skills !!}
                                </div>

                                <div class="section-header">ATTRIBUTES</div>
                                <div class="custom-list">
                                    {!! $dataTypeContent->attribute !!}
                                </div>

                                <div class="section-header">EXTRA CURRICULAR</div>
                                <div class="custom-list">
                                    {!! $dataTypeContent->extra_curricular !!}
                                </div>
                                <div class="section-header">INTEREST</div>
                                <div class="custom-list">
                                    {!! $dataTypeContent->interest !!}
                                </div>


                                <div style="display: none">
                                <!--expertise-->
                                @if($expertiseList)
                                    <div class="section-header">EXPERTISE</div>
                                    <ul class="skills-list">
                                        @foreach($expertiseList as $v)
                                            <li>{{$v->expertise_name}}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                <!-- Skills -->

                                @if($skillList)
                                    <div class="section-header">SKILLS</div>
                                    <ul class="skills-list">
                                        @foreach($skillList as $v)
                                            <li>{{$v->skill_name}}</li>
                                        @endforeach
                                    </ul>
                                @endif


                                <!-- Attributes -->
                                @if($attributeList)
                                    <div class="section-header">ATTRIBUTES</div>
                                    <ul class="attributes-list">
                                        @foreach($attributeList as $i)
                                            <li>{{$i->attribute_name}}</li>
                                        @endforeach

                                    </ul>
                                @endif

                                <!-- Extra Curricular -->
                                @if($curricularList)
                                    <div class="section-header">EXTRA CURRICULAR</div>
                                    <ul class="attributes-list">
                                        @foreach($curricularList as $i)
                                            <li>{{$i->curricular_name}}</li>
                                        @endforeach
                                    </ul>
                                @endif

                                <!-- Interest -->
                                @if($interestList)
                                    <div class="section-header">INTEREST</div>
                                    <ul class="attributes-list">
                                        @foreach($interestList as $i)
                                            <li>{{$i->interest_name}}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                </div>
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

                            <!-- Other skill  -->
                                @if($skills)
                                    <div class="section-header">OTHER SKILLS</div>
                                    @foreach($skills as $v)
                                        <div class="education-item">
                                            <div class="education-title">{{$v->title}}</div>
                                            <div class="education-details">
                                                ➢ Institute : {{$v->institute_name}}<br>
                                                ➢ Result : {{$v->result_score}}/{{$v->exam_score}} <br>
                                                ➢ Details : {{$v->details}}
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
                                    <span class="personal-label">Date of Birth :</span> {{date('d M Y', strtotime($dataTypeContent->dob))}}
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Nationality :</span> {{$dataTypeContent->nationality}} {{--(by Birth)--}}
                                </div>
                                {{--<div class="personal-info-item">
                                    <span class="personal-label">Religion :</span> Islam
                                </div>--}}
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
                                 <div class="personal-info-item">
                                     <span class="personal-label">Height :</span> {{$dataTypeContent->height}} Centimeter
                                 </div>
                                 <div class="personal-info-item">
                                     <span class="personal-label">Weight :</span> {{$dataTypeContent->weight}} kg
                                 </div>
                                 <div class="personal-info-item">
                                     <span class="personal-label">Marital status :</span> {{$dataTypeContent->marital_status}}
                                 </div>
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
@stop

@section('javascript')
    <!-- JS Libraries -->
    <script src="{{ asset('admin/html2canvas.min.js') }}"></script>
    <script src="{{ asset('admin/jspdf.umd.min.js') }}"></script>

    <script>
        async function savePDF() {
            $("#voyager-loader").show();
            const { jsPDF } = window.jspdf;

            // Capture the div as a canvas
            const element = document.getElementById("printArea");
            const canvas = await html2canvas(element, { scale: 2 });
            const imgData = canvas.toDataURL("image/png");

            // Create PDF
            const pdf = new jsPDF("p", "mm", "a4");
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();

            // Calculate dimensions
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pageWidth;
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
            pdf.save("cv.pdf");
            $("#voyager-loader").hide();
        }

        async function savePDFW() {
            const { jsPDF } = window.jspdf;

            // Capture the div
            const element = document.getElementById("printArea");
            const canvas = await html2canvas(element, { scale: 2 });
            const imgData = canvas.toDataURL("image/png");

            const pdf = new jsPDF("p", "mm", "a4");
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();

            // Add main content
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pageWidth;
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
            pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);

            // Load watermark image
            const watermark = new Image();
            watermark.crossOrigin = "Anonymous"; // allow external image
            watermark.src = "http://eujobbd.test/storage/settings/April2025/3ZRR7DhAlCqdnf2yeGkF.png"; // replace with your watermark

            watermark.onload = function () {
                // Create canvas to modify watermark
                const wmCanvas = document.createElement("canvas");
                const ctx = wmCanvas.getContext("2d");
                wmCanvas.width = watermark.width;
                wmCanvas.height = watermark.height;

                // Draw in grayscale
                ctx.drawImage(watermark, 0, 0);
                let imageData = ctx.getImageData(0, 0, wmCanvas.width, wmCanvas.height);
                let data = imageData.data;

                for (let i = 0; i < data.length; i += 4) {
                    let avg = (data[i] + data[i + 1] + data[i + 2]) / 3; // grayscale
                    data[i] = data[i + 1] = data[i + 2] = avg;
                    data[i + 3] = 80; // opacity (0–255) → 80 ≈ 30% transparent
                }
                ctx.putImageData(imageData, 0, 0);

                // Convert back to base64
                const watermarkData = wmCanvas.toDataURL("image/png");

                // Add watermark to PDF (centered)
                pdf.addImage(
                    watermarkData,
                    "PNG",
                    pageWidth / 4,
                    pageHeight / 3,
                    pageWidth / 2,
                    pageHeight / 3
                );

                pdf.save("invoice-with-watermark.pdf");
            };
        }
    </script>



@stop
