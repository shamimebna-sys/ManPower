@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @media print {
            .page-title,
            .page-actions,
            .btn,
            .navbar,
            .sidebar {
                display: none !important;
            }

            .panel {
                border: none !important;
                box-shadow: none !important;
            }

            body {
                background: white !important;
            }
        }

        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .job-card {
            transition: all 0.3s ease;
        }

        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .col-md-4[style*="background"] {
                margin-left: 0 !important;
                margin-bottom: 20px;
            }

            div[style*="display: flex"] {
                display: block !important;
            }

            span[style*="color: #6c757d"] {
                display: block;
                margin-top: 10px;
            }
        }
    </style>

@stop

@section('page_title', 'My Profile')

@section('page_header')
    <h1 class="page-title"> <i class="voyager-double-right"></i> My Profile </h1>
    <a href="{{route('admin.my.profile.edit')}}" class="btn btn-dark btn-sm  pull-right" >
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
                        <div class="row">
                            <!-- Sidebar -->
                            <div class="col-md-4" style="background: linear-gradient(135deg, #6a994e, #7cb342); color: white; min-height: 100vh; margin-left: -15px; margin-right: 15px; padding: 20px;">
                                <!-- Profile Section -->
                                <div class="text-center" style="margin-bottom: 30px;">
                                    <img src="{{ Voyager::image($dataTypeContent->half_photo_file_path)}}" alt="{{$dataTypeContent->name}}"
                                         style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 50%; margin-bottom: 15px;">
                                </div>

                                <!-- Contact -->
                                <div style="background: rgba(255,255,255,0.1); border-radius: 10px; padding: 15px; margin-bottom: 20px;">
                                    <h6 style="color: white; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                                        <i class="voyager-telephone"></i> CONTACT
                                    </h6>
                                    <div style="margin-bottom: 10px;">
                                        Mobile: <span>+8801917008670</span>
                                    </div>
                                    <div style="margin-bottom: 10px;">
                                         Email:  <span>shamol39@gmail.com</span>
                                    </div>
                                </div>

                                <!-- Languages -->
                                <div style="margin-bottom: 30px;">
                                    <h6 style="color: white; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                                        <i class="voyager-world"></i> LANGUAGES
                                    </h6>
                                    <ul style="list-style: none; padding-left: 0;">
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                                            <span style="position: absolute; left: 0; color: #a8dadc;">▸</span>
                                            Bangla
                                        </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                                            <span style="position: absolute; left: 0; color: #a8dadc;">▸</span>
                                            English
                                        </li>
                                    </ul>
                                </div>

                                <!-- Skills -->
                                @if($skills)
                                <div style="margin-bottom: 30px;">
                                    <h6 style="color: white; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                                        <i class="voyager-tools"></i> SKILLS
                                    </h6>
                                    <ul style="list-style: none; padding-left: 0;">
                                        @foreach($skills as $v)
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                                            <span style="position: absolute; left: 0; color: #a8dadc;">▸</span>
                                            {{$v->title}}
                                        </li>
                                         @endforeach
                                    </ul>
                                </div>
                                    @endif

                            <!-- Local and aboard Exprience Info-->
                                <div style="margin-bottom: 30px;">
                                    <h6 style="color: white; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                                        <i class="voyager-tools"></i> Exprience Year
                                    </h6>
                                    <ul style="list-style: none; padding-left: 0;">
                                            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;"> <span style="position: absolute; left: 0; color: #a8dadc;">▸ Aboard</span>  {{$dataTypeContent->abroad_ex}} Years </li>
                                            <li style="margin-bottom: 8px; padding-left: 20px; position: relative;"> <span style="position: absolute; left: 0; color: #a8dadc;">▸ Local</span>  {{$dataTypeContent->local_ex}} Years </li>
                                    </ul>
                                </div>

                                <!-- Personal Info-->
                                <div style="margin-bottom: 30px;">
                                    <h6 style="color: white; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                                        <i class="voyager-users"></i> Personal Information
                                    </h6>
                                    <ul style="list-style: none; padding-left: 0;">
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">  <span style="position: absolute; left: 0; color: #a8dadc;">▸</span>Father Name:  {{$dataTypeContent->father_name}} </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">  <span style="position: absolute; left: 0; color: #a8dadc;">▸</span><b>Mother Name:</b>  {{$dataTypeContent->mother_name}} </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">  <span style="position: absolute; left: 0; color: #a8dadc;">▸</span><b>Nationality:</b>  {{$dataTypeContent->nationality}} </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">  <span style="position: absolute; left: 0; color: #a8dadc;">▸</span><b>Gender:</b>  {{($dataTypeContent->gender == 'M')?'Male':'female'}} </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">  <span style="position: absolute; left: 0; color: #a8dadc;">▸</span><b>Present Address:</b>  {{$dataTypeContent->present_address_house}} </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">  <span style="position: absolute; left: 0; color: #a8dadc;">▸</span><b>Permanent Address:</b>  {{$dataTypeContent->permanent_address_house}} </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">  <span style="position: absolute; left: 0; color: #a8dadc;">▸</span><b>Permanent Address:</b>  {{$dataTypeContent->permanent_address_house}} </li>
                                    </ul>
                                </div>

                                <!-- Social Info-->
                                <div style="margin-bottom: 30px;">
                                    <h6 style="color: white; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                                        <i class="voyager-tools"></i> Social Links
                                    </h6>
                                    <ul style="list-style: none; padding-left: 0;">
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;"> <span style="position: absolute; left: 0; color: #a8dadc;">▸</span> <a href="{{$dataTypeContent->drive_link}}" style="color: white;">Google Drive Skils</a> </li>
                                        <li style="  margin-bottom: 8px; padding-left: 20px; position: relative;"> <span style="position: absolute; left: 0; color: #a8dadc;">▸</span> <a style="color: white;" href="{{$dataTypeContent->facebook_link}}" >Facebook</a> </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;"> <span style="position: absolute; left: 0; color: #a8dadc;">▸</span> <a style="color: white;" href="{{$dataTypeContent->youtube_link}}" >Youtube</a> </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;"> <span style="position: absolute; left: 0; color: #a8dadc;">▸</span> <a style="color: white;" href="{{$dataTypeContent->linkedin_link}}" >Linkedin</a> </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;"> <span style="position: absolute; left: 0; color: #a8dadc;">▸</span> <a style="color: white;" href="{{$dataTypeContent->twitter_link}}" >Twitter</a> </li>
                                        <li style="margin-bottom: 8px; padding-left: 20px; position: relative;"> <span style="position: absolute; left: 0; color: #a8dadc;">▸</span> <a style="color: white;" href="{{$dataTypeContent->instagram_link}}" >Instagram</a> </li>
                                    </ul>
                                </div>

                            </div>

                            <!-- Main Content -->
                            <div class="col-md-8">
                                <!-- Header -->
                                <div style="background: linear-gradient(135deg, #2d5016, #6a994e); color: white; padding: 30px; text-align: center; margin-bottom: 30px;">
                                    <h1>{{$dataTypeContent->name}}</h1>
                                    <h4>Position: {{$dataTypeContent->position}} </h4>
                                    <h5> Passport No.: {{$dataTypeContent->passport_no}}, CV-{{$dataTypeContent->code}} </h5>
                                    <div style="margin-top: 15px;">
                                        <span class="label label-light"></span>
                                    </div>
                                </div>

                                <!-- About Me -->
                                <div style="margin-bottom: 40px;">
                                    <h3 style="color: #2d5016; border-bottom: 2px solid #6a994e; padding-bottom: 5px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;">
                                        About Me
                                    </h3>
                                    <p style="font-size: 16px; line-height: 1.6; color: #495057;">
                                        {!! $dataTypeContent->basic_info_career !!}
                                    </p>
                                </div>

                                <!-- Employment History -->
                                @if($expriences)
                                <div>
                                    <h3 style="color: #2d5016; border-bottom: 2px solid #6a994e; padding-bottom: 5px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;">
                                        EMPLOYMENT HISTORY
                                    </h3>

                                    <!-- Job 1 -->
                                    @foreach($expriences as $v)
                                    <div style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-left: 4px solid #6a994e;">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                            <div>
                                                <h5 style="color: #6a994e; font-weight: bold; margin-bottom: 5px;">{{$v->designation}}</h5>
                                                <p style="color: #495057; font-style: italic; margin-bottom: 5px;">{{$v->company_name}}</p>
                                                <p style="margin-bottom: 0; font-size: 14px;">{{$v->company_address}}</p>
                                            </div>
                                            <span style="color: #6c757d; font-size: 14px;">{{date('M d, Y', strtotime($v->start_date))}} - {{!empty($v->end_date)?date('M d, Y', strtotime($v->end_date)):'Present'}}</span>
                                        </div>
                                         <p>{!! $v->descriptions !!}</p>
                                    </div>
                                    @endforeach

                                </div>
                               @endif

                                  <!-- Education History -->
                                @if($educations)
                                <div>
                                    <h3 style="color: #2d5016; border-bottom: 2px solid #6a994e; padding-bottom: 5px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;">
                                        ACADEMIC QUALIFICATIONS
                                    </h3>

                                    <!-- Job 1 -->
                                    @foreach($educations as $v)
                                    <div style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-left: 4px solid #6a994e;">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                            <div>
                                                <h5 style="color: #6a994e; font-weight: bold; margin-bottom: 5px;">{{$v->exam_name}}</h5>
                                                <p style="color: #495057; font-style: italic; margin-bottom: 5px;"><b>Institute:</b> {{$v->institute_name}}</p>
                                                <p style="color: #495057; font-style: italic; margin-bottom: 5px;"><b><b>Board:</b> {{$v->board_name}}</p>
                                                <p style="color: #495057; font-style: italic; margin-bottom: 5px;"><b>Passing Year:</b> {{$v->passing_year}}</p>
                                                <p style="color: #495057; font-style: italic; margin-bottom: 5px;"><b>CGPA:</b>  {{$v->result}}</p>
                                                <p style="color: #495057; font-style: italic; margin-bottom: 5px;"><b>Major:</b> {{$v->subject_group_major}}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

                                </div>
                               @endif


                            <!-- Training History -->
                                @if($trainings)
                                    <div>
                                        <h3 style="color: #2d5016; border-bottom: 2px solid #6a994e; padding-bottom: 5px; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;">
                                            TRAINING SUMMARY
                                        </h3>

                                        <!-- Job 1 -->
                                        @foreach($trainings as $v)
                                            <div style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-left: 4px solid #6a994e;">
                                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                                    <div>
                                                        <h5 style="color: #6a994e; font-weight: bold; margin-bottom: 5px;">{{$v->title}}</h5>
                                                        <p style="color: #495057; font-style: italic; margin-bottom: 5px;">{{$v->institute_name}}</p>
                                                        <p style="color: #495057; font-style: italic; margin-bottom: 5px;">{{$v->address}}</p>
                                                        <p style="margin-bottom: 0; font-size: 14px;"><b>Topics: </b>{{$v->topics}}</p>
                                                    </div>
                                                    <span style="color: #6c757d; font-size: 14px;">{{date('M d, Y', strtotime($v->start_date))}} - {{!empty($v->end_date)?date('M d, Y', strtotime($v->end_date)):'Present'}}</span>
                                                </div>
                                                <p>{!! $v->descriptions !!}</p>
                                            </div>
                                        @endforeach

                                    </div>
                                @endif

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
