@extends('voyager::master')
@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp
@section('page_title', __('voyager::generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style type="text/css">
        .page-content {
            background: #f8fafc !important;
            padding: 24px 0 !important;
        }

        .panel.panel-bordered {
            border-radius: 16px !important;
            box-shadow: 0 4px 24px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.02) !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            overflow: hidden;
            background: #ffffff !important;
            margin-bottom: 24px !important;
            transition: box-shadow 0.3s ease;
        }

        .panel.panel-bordered:hover {
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.03) !important;
        }

        .panel-heading {
            background: #ffffff !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 18px 24px !important;
        }

        .panel-title {
            color: #0f172a !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-title-icon {
            width: 18px;
            height: 18px;
            color: {{ config('voyager.primary_color', '#22A7F0') }};
        }

        .panel-body {
            padding: 24px 24px 12px 24px !important;
        }

        .form-group {
            margin-bottom: 22px !important;
        }

        label {
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #475569 !important;
            margin-bottom: 8px !important;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            display: inline-block;
        }

        .form-control {
            border-radius: 8px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 10px 14px !important;
            height: auto !important;
            font-size: 13px !important;
            color: #1e293b !important;
            box-shadow: none !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        }

        .form-control:focus {
            border-color: {{ config('voyager.primary_color', '#22A7F0') }} !important;
            box-shadow: 0 0 0 3px rgba(34, 167, 240, 0.15) !important;
            outline: none !important;
        }

        /* Select2 Dropdown Styling */
        .select2-container .select2-selection--single {
            height: 42px !important;
            border-radius: 8px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.2s ease !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px !important;
            padding-left: 14px !important;
            color: #1e293b !important;
            font-size: 13px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }

        .select2-container--open .select2-selection--single {
            border-color: {{ config('voyager.primary_color', '#22A7F0') }} !important;
            box-shadow: 0 0 0 3px rgba(34, 167, 240, 0.15) !important;
        }

        /* File Upload Styles */
        input[type="file"] {
            background: #f8fafc !important;
            border: 1px dashed #cbd5e1 !important;
            border-radius: 8px !important;
            padding: 8px !important;
            width: 100% !important;
            font-size: 12px !important;
            transition: border-color 0.2s ease, background-color 0.2s ease !important;
        }

        input[type="file"]:hover {
            border-color: {{ config('voyager.primary_color', '#22A7F0') }} !important;
            background: rgba(34, 167, 240, 0.01) !important;
        }

        /* Existing File Preview Chips */
        .form-group input[type="file"] {
            margin-top: 6px;
        }

        /* Unified custom attachment bar styling for all files and photos */
        .custom-attachment-bar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            width: 100%;
            box-sizing: border-box;
            margin-top: 6px;
            transition: all 0.2s ease;
        }

        .custom-attachment-bar:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.02);
        }

        .custom-attachment-bar a.attachment-download-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #334155 !important;
            text-decoration: none !important;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 60%;
        }

        .custom-attachment-bar a.attachment-download-btn:hover {
            color: {{ config('voyager.primary_color', '#22A7F0') }} !important;
        }

        .custom-attachment-bar a.attachment-download-btn .attachment-icon {
            width: 14px;
            height: 14px;
            color: #64748b;
            flex-shrink: 0;
        }

        .btn-custom-preview-trigger {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            margin-left: auto; /* Push preview button to the right */
            transition: all 0.2s ease;
            outline: none !important;
        }

        .btn-custom-preview-trigger:hover {
            background: #f1f5f9;
            color: #1e293b;
            border-color: #94a3b8;
        }

        .btn-custom-preview-trigger svg {
            width: 12px;
            height: 12px;
        }

        /* Voyager delete cross button styling inside the bar */
        .custom-attachment-bar .remove-single-image,
        .custom-attachment-bar .remove-multi-file,
        .custom-attachment-bar .remove-single-file {
            position: static !important;
            color: #ef4444 !important;
            font-size: 16px;
            line-height: 1;
            text-decoration: none !important;
            margin-left: 8px;
            transition: transform 0.2s ease;
            cursor: pointer;
            flex-shrink: 0;
            display: inline-block;
        }

        .custom-attachment-bar .remove-single-image:hover,
        .custom-attachment-bar .remove-multi-file:hover,
        .custom-attachment-bar .remove-single-file:hover {
            transform: scale(1.15);
        }

        /* Hidden inline preview images styling */
        div[data-field-name="full_photo_file_path"] img,
        div[data-field-name="half_photo_file_path"] img {
            display: none !important;
        }

        hr {
            border-top: 1px solid #f1f5f9 !important;
            margin: 24px 0 !important;
        }

        /* User Access Section Divider */
        .section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-header-icon {
            width: 16px;
            height: 16px;
            color: #64748b;
        }

        .section-header h4 {
            margin: 0 !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #475569 !important;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        /* Submit Button Styling */
        .save {
            background: linear-gradient(135deg, {{ config('voyager.primary_color', '#22A7F0') }} 0%, #1d4ed8 100%) !important;
            border: none !important;
            color: #ffffff !important;
            padding: 12px 36px !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            text-transform: uppercase;
        }

        .save:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35) !important;
        }

        .save:active {
            transform: translateY(0) !important;
        }

        /* Form validation errors highlight */
        .has-error .form-control {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .has-error label {
            color: #ef4444 !important;
        }

        #imageGalleryModal .close {
            transition: all 0.2s ease;
        }

        #imageGalleryModal .close:hover {
            color: #ef4444 !important;
            opacity: 1 !important;
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
                                    <h3 class="panel-title">
                                        @if(request()->has('stamp'))
                                            <svg class="panel-title-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            Passport With Stamp
                                        @else
                                            <svg class="panel-title-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Basic Information
                                        @endif
                                    </h3>
                                    <div class="panel-actions">
                                        <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div style="display: none">
                                            <div class="form-group  col-md-4">
                                                <label for="name">Nationality</label>
                                                <input type="text" class="form-control" name="nationality" placeholder="Nationality" value="Bangladesh">
                                            </div>
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['blood_group'], 'width'=>'3'])
                                        </div>

                                        <div class="row">
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
                                             @endif

                                             @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['name'], 'width'=>'8'])
                                         </div>

                                         @if(!request()->has('stamp'))
                                             <div class="row">
                                                 @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['father_name'], 'width'=>'4'])
                                                 @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mother_name'], 'width'=>'4'])
                                                 @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['gender'], 'width'=>'4'])
                                             </div>
                                             <div class="row">
                                                 @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['dob'], 'width'=>'4'])
                                                 @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bid'], 'width'=>'4'])
                                                 @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nid'], 'width'=>'4'])
                                             </div>
                                         @endif

                                         <div class="row">
                                             @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_no'], 'width'=>'4'])
                                             @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_issue_date'], 'width'=>'4'])
                                             @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_expire_date'], 'width'=>'4'])
                                         </div>

                                        <div class="col-md-12" style="margin-bottom: 15px;">
                                            <div class="row">
                                                @if(!request()->has('stamp'))
                                                    <div class="col-md-4">
                                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['cv_file_path'], 'width'=>'12'])
                                                    </div>
                                                    <div class="col-md-4">
                                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['nid_file_path'], 'width'=>'12'])
                                                    </div>
                                                    <div class="col-md-4">
                                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_file_path'], 'width'=>'12'])
                                                    </div>
                                                @else
                                                    <div class="col-md-6">
                                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['passport_file_path'], 'width'=>'12'])
                                                    </div>
                                                    <div class="col-md-6">
                                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['stamp_file_path'], 'width'=>'12'])
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div style="display: {{request()->has('stamp')?'none':''}}; width: 100%;">
                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['present_address_house'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['permanent_address_house'], 'width'=>'6'])
                                            </div>

                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['full_photo_file_path'], 'width'=>'3'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['half_photo_file_path'], 'width'=>'3'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['basic_info_career'], 'width'=>'6'])
                                            </div>

                                            <div class="row">
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['basic_info_special'], 'width'=>'6'])
                                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['other_skills'], 'width'=>'6'])
                                            </div>
                                        </div>
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['remarks'], 'width'=>'12'])

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3" style="display: {{request()->has('stamp')?'none':''}}">
                            <div class="panel panel-bordered panel-warning">
                                <div class="panel-heading">
                                    <h3 class="panel-title">
                                        <svg class="panel-title-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Required & Relation
                                    </h3>
                                    <div class="panel-actions">
                                        <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['status'], 'width'=>'12'])

                                        <div style="display: {{in_array(\App\Helpers\CommonClass::user()->role_id,[101, 107, 108])?'none':'block'}}">
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['email'], 'width'=>'12'])
                                        </div>
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['secondary_email'], 'width'=>'12'])

                                        <div style="display: {{in_array(\App\Helpers\CommonClass::user()->role_id,[101, 107, 108])?'none':'block'}}">
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['mobile'], 'width'=>'12'])
                                        </div>
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['secondary_mobile'], 'width'=>'12'])
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['emergency_mobile'], 'width'=>'12'])

                                        <div style="display: {{in_array(\App\Helpers\CommonClass::user()->role_id,[101, 107, 108, 109])?'none':'block'}}">
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_agent_relationship'], 'width'=>'12'])
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_sub_agent_relationship'], 'width'=>'12'])
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_class_group_relationship'], 'width'=>'12'])
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_agencier_relationship'], 'width'=>'12'])
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_companier_relationship'], 'width'=>'12'])
                                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_belongsto_position_relationship'], 'width'=>'12'])
                                        </div>
                                    </div>

                                    <div class="section-header">
                                        <svg class="section-header-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <h4>User Access</h4>
                                    </div>
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

    <!-- Image Gallery Modal -->
    <div class="modal fade" id="imageGalleryModal" tabindex="-1" role="dialog" aria-labelledby="imageGalleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background: #ffffff; border-bottom: 1px solid #f1f5f9; padding: 16px 24px; position: relative; min-height: 56px;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; color: #94a3b8; cursor: pointer; float: right; margin-top: -2px; opacity: 0.8; outline: none; padding: 0;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="imageGalleryModalLabel" style="font-weight: 700; color: #0f172a; margin: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.5; float: left;">Image Preview</h4>
                    <div style="clear: both;"></div>
                </div>
                <div class="modal-body" style="background: #f8fafc; padding: 24px; text-align: center;">
                    <div id="modalPreviewContainer" style="min-height: 250px; display: flex; align-items: center; justify-content: center; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                        <img id="galleryActiveImage" src="" style="max-height: 450px; max-width: 100%; object-fit: contain; border-radius: 8px; display: none;">
                        <iframe id="galleryActiveIframe" src="" style="width: 100%; height: 480px; border: none; display: none; border-radius: 8px;"></iframe>
                        <div id="galleryFallbackMsg" style="display: none; padding: 40px; color: #64748b; font-weight: 600;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:48px; height:48px; margin: 0 auto 12px auto; display:block; color:#94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Preview is not supported for this file type.</span>
                            <a id="galleryFallbackLink" href="" target="_blank" class="btn btn-default btn-xs" style="display:block; margin-top: 12px; font-weight: bold; border-radius: 6px; padding: 5px 12px;">Download File</a>
                        </div>
                    </div>
                    <!-- Gallery Thumbnails Index -->
                    <div id="galleryThumbnailsContainer" style="display: flex; justify-content: center; gap: 12px; margin-top: 18px; flex-wrap: wrap;">
                        <!-- Filled dynamically via JS -->
                    </div>
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

        // General modal-based preview function for all document and image types
        function openModalPreview(fileUrl, label) {
            var ext = fileUrl.split('.').pop().toLowerCase().split('?')[0];
            
            // Hide all layout blocks first
            $('#galleryActiveImage').hide();
            $('#galleryActiveIframe').hide();
            $('#galleryFallbackMsg').hide();
            
            $('#imageGalleryModalLabel').text(label + ' Preview');
            
            if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].indexOf(ext) !== -1) {
                $('#galleryActiveImage').attr('src', fileUrl).show();
            } else if (ext === 'pdf') {
                $('#galleryActiveIframe').attr('src', fileUrl).show();
            } else {
                $('#galleryFallbackLink').attr('href', fileUrl);
                $('#galleryFallbackMsg').show();
            }
            
            $('#galleryThumbnailsContainer').hide();
            $('#imageGalleryModal').modal('show');
        }

        $(document).ready(function() {
            $('select[name="agent_id"]').on('change', function() {
                var agentId = $(this).val();
                var newRoute = '/panel/candidates/relation?type=candidate_belongsto_sub_agent_relationship&method=add&page=1&agent_id='+agentId;
                reloadSelect2WithNewRoute($('select[name="sub_agent_id"]'), newRoute);
            });

            // Clear preview elements on modal close to prevent memory/resource leaks
            $('#imageGalleryModal').on('hidden.bs.modal', function () {
                $('#galleryActiveIframe').attr('src', '');
                $('#galleryActiveImage').attr('src', '');
            });

            // Enforce "show only last uploaded" for file attachments and wrap in custom-attachment-bars
            var docFields = ['cv_file_path', 'nid_file_path', 'passport_file_path', 'stamp_file_path'];
            docFields.forEach(function(fieldName) {
                var $divs = $('div[data-field-name="' + fieldName + '"]');
                if ($divs.length > 0) {
                    // Hide all but the last uploaded element
                    $divs.not(':last').hide();
                    
                    var $activeDiv = $divs.last();
                    var $fileLink = $activeDiv.find('.fileType');
                    var $removeBtn = $activeDiv.find('.remove-multi-file, .remove-single-file');
                    
                    if ($fileLink.length > 0) {
                        var fileUrl = $fileLink.attr('href');
                        var fileName = $fileLink.text().trim();
                        var fieldLabel = fieldName === 'cv_file_path' ? 'CV' :
                                         fieldName === 'nid_file_path' ? 'NID' :
                                         fieldName === 'passport_file_path' ? 'Passport Scan' : 'Stamp Copy';
                        
                        if (!fileName || fileName === 'Download') {
                            fileName = fieldLabel + '.' + fileUrl.split('.').pop().split('?')[0];
                        }
                        
                        // Build custom attachment bar
                        var $bar = $('<div class="custom-attachment-bar">' +
                                     '  <a href="' + fileUrl + '" target="_blank" class="attachment-download-btn">' +
                                     '    <svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>' +
                                     '    <span>' + fileName + '</span>' +
                                     '  </a>' +
                                     '  <button type="button" class="btn-custom-preview-trigger">' +
                                     '    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>' +
                                     '    <span>Preview</span>' +
                                     '  </button>' +
                                     '</div>');
                        
                        // Append delete/remove button inside the bar
                        $bar.append($removeBtn);
                        
                        // Empty out the container and replace with our bar
                        $activeDiv.empty().append($bar);
                        
                        // Hook up click preview action
                        $bar.find('.btn-custom-preview-trigger').on('click', function(e) {
                            e.preventDefault();
                            openModalPreview(fileUrl, fieldLabel);
                        });
                    }
                }
            });

            // Convert photo image displays into custom-attachment-bars with modal-based previews
            $('div[data-field-name="full_photo_file_path"], div[data-field-name="half_photo_file_path"]').each(function() {
                var $container = $(this);
                var $img = $container.find('img');
                var $removeBtn = $container.find('.remove-single-image');
                
                if ($img.length > 0) {
                    $img.hide(); // Hide the inline image completely
                    var imgUrl = $img.attr('src');
                    var fieldName = $container.data('field-name');
                    var fieldLabel = fieldName === 'full_photo_file_path' ? 'Full Photo' : 'Half Photo';
                    
                    // Build styled attachment bar
                    var $bar = $('<div class="custom-attachment-bar">' +
                                 '  <a href="' + imgUrl + '" target="_blank" class="attachment-download-btn">' +
                                 '    <svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>' +
                                 '    <span>' + fieldLabel + '.jpg</span>' +
                                 '  </a>' +
                                 '  <button type="button" class="btn-custom-preview-trigger">' +
                                 '    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>' +
                                 '    <span>Preview</span>' +
                                 '  </button>' +
                                 '</div>');
                    
                    // Reposition original Voyager remove anchor inside custom bar
                    $bar.append($removeBtn);
                    $container.empty().append($bar).append($img); // keep the hidden img tag in DOM for reference
                    
                    // Show single modal preview
                    $bar.find('.btn-custom-preview-trigger').on('click', function(e) {
                        e.preventDefault();
                        openModalPreview(imgUrl, fieldLabel);
                    });
                }
            });
        });
    </script>
@stop
