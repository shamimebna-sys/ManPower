@extends('voyager::master')

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
            content: "�";
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
            content: "?";
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
            content: "�";
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

                        <!-- Header -->
                        <div class="row">
                            <h3>CV-876876</h3>
                            <div class="col-md-3">

                                <img src="{{ Voyager::image(Voyager::setting('admin.icon_image', ''))}}" alt="{{$dataTypeContent->name}}"
                                     style="width: 150px; height: 150px; object-fit: cover;  border-radius: 0%; ">
                            </div>
                            <div class="col-md-6">
                                <div class="header-name">REZAUL KARIM</div>
                                <div class="header-position">Position: Waiter</div>
                                <div class="header-info">
                                    Passport No-A08768517<br>
                                    Mobile No- +8801917890870

                                </div>
                            </div>
                            <div class="col-md-3" style="float: right">
                                <img src="{{ Voyager::image($dataTypeContent->half_photo_file_path)}}" alt="{{$dataTypeContent->name}}"
                                     style="width: 150px; height: 150px; object-fit: cover; float: right">


                            </div>

                        </div>

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-4 left-column">
                                <!-- Contact -->
                                <div class="section-header">CONTACT</div>
                                <div class="contact-item">
                                    <span class="contact-label">�</span> Mobile +8801917890870
                                </div>
                                <div class="contact-item">
                                    <span class="contact-label">�</span> Email : shaon030@gmail.com
                                </div>
                                <div class="contact-item">
                                    <span class="contact-label">�</span> Passport No- A08768517
                                </div>

                                <!-- Languages -->
                                <div class="section-header">LANGUAGES</div>
                                <div class="contact-item">� Bengali</div>
                                <div class="contact-item">� English</div>
                                <div class="contact-item">� Hindi</div>

                                <!-- Skills -->
                                <div class="section-header">SKILLS</div>
                                <ul class="skills-list">
                                    <li>Presentation skill prompt and friendly service.</li>
                                    <li>Knowledge & experience using point of sale systems.</li>
                                    <li>Food safety.</li>
                                    <li>A desire to help people.</li>
                                    <li>Active listening skills.</li>
                                    <li>A Good memory.</li>
                                    <li>Attentive listening skills.</li>
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
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-8 right-column">
                                <!-- About Me -->
                                <div class="section-header">ABOUT ME</div>
                                <p style="text-align: justify; font-size: 11px; margin-bottom: 15px;">
                                    Friendly and dedicated waiter with 10 years of experience in fast-paced restaurant environments. Known for providing excellent customer service, maintaining attention to detail, and ensuring a pleasant dining experience for every guest. Skilled in taking and delivering accurate orders, handling busy shifts with professionalism, and working as part of a team. Passionate about hospitality and always ready to go the extra mile to exceed customer expectations.
                                </p>

                                <!-- Employment History -->
                                <div class="section-header">EMPLOYMENT HISTORY</div>

                                <div class="job-title">SENIOR WAITER</div>
                                <div class="company-name">Lakeshore Grand <span class="job-duration">(May 2022 to Present)</span></div>
                                <div class="job-location">Location: 46, Road No: 41, Gulshan, Dhaka 1212</div>
                                <ul class="job-responsibilities">
                                    <li>Provided exceptional customer service by promptly attending to guests' needs, ensuring a positive dining experience.</li>
                                    <li>Took and delivered orders accurately and efficiently, maintaining a thorough knowledge of menu items and daily specials.</li>
                                    <li>Handled cash, credit card transactions, and managed bills with precision, ensuring accurate records and customer satisfaction.</li>
                                    <li>Collaborated with kitchen staff to ensure timely and accurate preparation of meals.</li>
                                    <li>Resolved guest concerns professionally, enhancing overall customer satisfaction and retention.</li>
                                    <li>Assisted in maintaining cleanliness and organization of the dining area, complying with health and safety standards.</li>
                                    <li>Trained and mentored new team members to uphold service quality and restaurant policies.</li>
                                </ul>

                                <div class="job-title">SENIOR WAITER</div>
                                <div class="company-name">Chilis Restaurant <span class="job-duration">(January 2020 to April 2022)</span></div>
                                <div class="job-location">Location: 76/a Rd No. 11, Dhaka 1207</div>
                                <ul class="job-responsibilities">
                                    <li>Greeted and seated customers, provided menu recommendations, and ensured a welcoming dining environment.</li>
                                    <li>Accurately took orders and delivered food and beverages promptly, maintaining attention to detail.</li>
                                    <li>Managed cash and card transactions efficiently, ensuring error-free billing and payments.</li>
                                    <li>Maintained cleanliness of dining areas, ensuring compliance with health and safety standards.</li>
                                    <li>Collaborated with team members to provide seamless service during peak hours.</li>
                                    <li>Resolved guest complaints tactfully, ensuring customer satisfaction and repeat business.</li>
                                </ul>

                                <div class="job-title">WAITER</div>
                                <div class="company-name">Hotel Sarina Dhaka <span class="job-duration">March 2015 � October 2019</span></div>
                                <div class="job-location">Location: Plot #27 Road No. 17, Dhaka 1213</div>
                                <ul class="job-responsibilities">
                                    <li>Greeted and seated customers, provided menu recommendations, and ensured a welcoming dining environment.</li>
                                    <li>Accurately took orders and delivered food and beverages promptly, maintaining attention to detail.</li>
                                    <li>Managed cash and card transactions efficiently, ensuring error-free billing and payments.</li>
                                    <li>Maintained cleanliness of dining areas, ensuring compliance with health and safety standards.</li>
                                    <li>Collaborated with team members to provide seamless service during peak hours.</li>
                                    <li>Resolved guest complaints tactfully, ensuring customer satisfaction and repeat business.</li>
                                </ul>

                                <!-- Academic Qualifications -->
                                <div class="section-header">ACADEMIC QUALIFICATIONS</div>

                                <div class="education-item">
                                    <div class="education-title">Diploma In Engineering</div>
                                    <div class="education-details">
                                        ? Institute : Dhaka Polytechnic.<br>
                                        ? Passing Year : 2015<br>
                                        ? Result : CGPA- 2.80 (Out of 4.00)<br>
                                        ? Bord : Dhaka
                                    </div>
                                </div>

                                <div class="education-item">
                                    <div class="education-title">Secondary School Certificate (S.S.C.)</div>
                                    <div class="education-details">
                                        ? Institute : Falda Ramshundar High School<br>
                                        ? Passing Year : 2009<br>
                                        ? Result : GPA- 4.13 (Out of 5.00)<br>
                                        ? Bord : Dhaka.
                                    </div>
                                </div>

                                <!-- Training Summary -->
                                <div class="section-header">TRAINING SUMMARY</div>
                                <div class="education-item">
                                    <div class="education-title">Bangladesh Hotel Management & Tourism Training Institute (BHMTTI)</div>
                                    <div class="education-details">
                                        Training Name: Food and Beverage<br>
                                        Result: Very Good
                                    </div>
                                </div>

                                <!-- Personal Information -->
                                <div class="section-header">PERSONAL INFORMATION</div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Name :</span> REZAUL KARIM
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Father's Name :</span> MD. SHAHINUR RAHMAN
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Mother's Name :</span> RINA RAHMAN
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Date of Birth :</span> 31<sup>th</sup> January 1994
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Nationality :</span> Bangladeshi (by Birth)
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Police station :</span> BHUAPUR
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Religion :</span> Islam
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Blood Group :</span> A (+Ve)
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Sex :</span> Male
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">National ID No. :</span> 9321903000016
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Passport Number :</span> A08768517
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Height :</span> 5 Feet 9 Inch
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Weight :</span> 78 kg
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Marital status :</span> Unmarried.
                                </div>
                                <div class="personal-info-item">
                                    <span class="personal-label">Permanent Address :</span> MIA BARI, BAHADIPUR, BHUAPUR,<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BHUAPUR - 1960, TANGAIL.
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
                    data[i + 3] = 80; // opacity (0�255) ? 80 � 30% transparent
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
