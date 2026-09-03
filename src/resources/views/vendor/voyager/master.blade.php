<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" dir="{{ __('voyager::generic.is_rtl') == 'true' ? 'rtl' : 'ltr' }}">
<head>
    <title>@yield('page_title', setting('admin.title') . " - " . setting('admin.description'))</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="assets-path" content="{{ route('voyager.voyager_assets') }}"/>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">

    <!-- Favicon -->
    <?php $admin_favicon = Voyager::setting('admin.icon_image', ''); ?>
    @if($admin_favicon == '')
        <link rel="shortcut icon" href="{{ voyager_asset('images/logo-icon.png') }}" type="image/png">
    @else
        <link rel="shortcut icon" href="{{ Voyager::image($admin_favicon) }}" type="image/png">
    @endif



    <!-- App CSS -->
    <link rel="stylesheet" href="{{ voyager_asset('css/app.css') }}">

    @yield('css')
    @if(__('voyager::generic.is_rtl') == 'true')
        <link rel="stylesheet" href="{{ asset('admin/bootstrap-rtl.css') }}">
        <link rel="stylesheet" href="{{ voyager_asset('css/rtl.css') }}">
    @endif

    <!-- Few Dynamic Styles -->
    <style type="text/css">
        .voyager .side-menu .navbar-header {
            background:{{ config('voyager.primary_color','#22A7F0') }};
            border-color:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .widget .btn-primary{
            border-color:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .widget .btn-primary:focus, .widget .btn-primary:hover, .widget .btn-primary:active, .widget .btn-primary.active, .widget .btn-primary:active:focus{
            background:{{ config('voyager.primary_color','#22A7F0') }};
        }
        .voyager .breadcrumb a{
            color:{{ config('voyager.primary_color','#22A7F0') }};
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
        .custom-attachment-bar .remove-multi-image,
        .custom-attachment-bar .remove-single-file,
        .custom-attachment-bar .remove-multi-file {
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
        .custom-attachment-bar .remove-multi-image:hover,
        .custom-attachment-bar .remove-single-file:hover,
        .custom-attachment-bar .remove-multi-file:hover {
            transform: scale(1.15);
        }

        /* Global datatable file and image preview styles */
        .global-file-preview-container {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            transition: all 0.2s ease;
            margin: 2px 0;
            max-width: 100%;
        }
        .global-file-preview-container:hover {
            border-color: #94a3b8;
            background: #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .global-file-preview-container a {
            color: #475569 !important;
            text-decoration: none !important;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .global-file-preview-container a:hover {
            color: {{ config('voyager.primary_color', '#22A7F0') }} !important;
            text-decoration: underline !important;
        }
        .btn-table-preview {
            background: none;
            border: none;
            padding: 2px;
            cursor: pointer;
            color: #475569;
            display: inline-flex;
            align-items: center;
            transition: color 0.2s;
            outline: none !important;
        }
        .btn-table-preview:hover {
            color: {{ config('voyager.primary_color', '#22A7F0') }} !important;
        }
        .global-image-container {
            display: inline-block;
            position: relative;
            margin: 4px 0;
            border: 1px solid #cbd5e1;
            padding: 4px;
            border-radius: 6px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
        }
        .global-image-container:hover {
            border-color: #94a3b8;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
    </style>

    @if(!empty(config('voyager.additional_css')))<!-- Additional CSS -->
        @foreach(config('voyager.additional_css') as $css)<link rel="stylesheet" type="text/css" href="{{ asset($css) }}">@endforeach
    @endif

    <!-- Smart Image Error Fallback Script -->
    <script>
        window.addEventListener('error', function (e) {
            if (e.target.tagName === 'IMG') {
                if (!e.target.dataset.fallbackApplied) {
                    e.target.dataset.fallbackApplied = 'true';
                    const name = e.target.alt || 'User';
                    const initials = name.split(' ').filter(Boolean).map(n => n[0]).join('').substring(0, 2).toUpperCase();
                    const colors = ['%234f46e5', '%2306b6d4', '%2310b981', '%23f59e0b', '%23ec4899', '%238b5cf6'];
                    let hash = 0;
                    for (let i = 0; i < name.length; i++) {
                        hash = name.charCodeAt(i) + ((hash << 5) - hash);
                    }
                    const color = colors[Math.abs(hash) % colors.length];
                    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="${color}"/><text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" font-family="system-ui, -apple-system, sans-serif" font-weight="bold" font-size="38" fill="white">${initials || 'U'}</text></svg>`;
                    e.target.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
                }
            }
        }, true);
    </script>

    @yield('head')
</head>

<body class="voyager @if(isset($dataType) && isset($dataType->slug)){{ $dataType->slug }}@endif">

<div id="voyager-loader">
    <?php $admin_loader_img = Voyager::setting('admin.loader', ''); ?>
    @if($admin_loader_img == '')
        <img src="{{ voyager_asset('images/logo-icon.png') }}" alt="Voyager Loader">
    @else
        <img src="{{ Voyager::image($admin_loader_img) }}" alt="Voyager Loader">
    @endif
</div>

<?php
if (\Illuminate\Support\Str::startsWith(Auth::user()->avatar, 'http://') || \Illuminate\Support\Str::startsWith(Auth::user()->avatar, 'https://')) {
    $user_avatar = Auth::user()->avatar;
} else {
    $user_avatar = Voyager::image(Auth::user()->avatar);
}
?>

<div class="app-container">
    <div class="fadetoblack visible-xs"></div>
    <div class="row content-container">
        @include('voyager::dashboard.navbar')
        @include('voyager::dashboard.sidebar')

        <script>
            (function(){
                    var appContainer = document.querySelector('.app-container'),
                        sidebar = appContainer.querySelector('.side-menu'),
                        navbar = appContainer.querySelector('nav.navbar.navbar-top'),
                        loader = document.getElementById('voyager-loader'),
                        hamburgerMenu = document.querySelector('.hamburger'),
                        sidebarTransition = sidebar.style.transition,
                        navbarTransition = navbar.style.transition,
                        containerTransition = appContainer.style.transition;

                    sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition =
                    appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition =
                    navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = 'none';

                    if (window.innerWidth > 768 && window.localStorage && window.localStorage['voyager.stickySidebar'] == 'true') {
                        appContainer.className += ' expanded no-animation';
                        loader.style.left = (sidebar.clientWidth/2)+'px';
                        hamburgerMenu.className += ' is-active no-animation';
                    }

                   navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = navbarTransition;
                   sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition = sidebarTransition;
                   appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition = containerTransition;
            })();
        </script>
        <!-- Main Content -->
        <div class="container-fluid">
            <div class="side-body padding-top">
                @php
                    $announcements = \App\Models\Announcement::where('status', 'A')
                    ->whereDate('anounce_date', '<=', \Carbon\Carbon::today())
                    ->whereDate('anounce_date_end', '>=', \Carbon\Carbon::today())
                    ->get();
                @endphp
                @if($announcements)

                    <style>
                        .marquee-wrapper {
                            overflow: hidden;
                            position: relative;
                            /*height: 40px;*/
                            display: flex;
                            align-items: center;
                            border-bottom: 1px solid hsl(0deg 29.44% 91.72% / 90%);
                            background: #f8fafc;
                            margin-bottom: 10px;
                        }

                        .marquee-content {
                            display: inline-block;
                            white-space: nowrap;
                            animation: scroll-left 60s linear infinite;
                            padding-left: 100%;
                        }

                        /* Pause animation on hover */
                        .marquee-wrapper:hover .marquee-content {
                            animation-play-state: paused;
                        }

                        @keyframes scroll-left {
                            0%   { transform: translateX(0%); }
                            100% { transform: translateX(-100%); }
                        }

                        .marquee-content a {
                            margin-right: 50px;
                            text-decoration: none;
                            font-weight: bold;
                            color: #0d6efd;
                        }

                        .marquee-content a:hover {
                            text-decoration: underline;
                        }
                    </style>

                <div class="marquee-wrapper">
                    <div class="marquee-content">
                        @foreach($announcements as $a)
                        <a href="{{route('voyager.announcements.show', $a->id)}}"><i class="voyager-double-right"></i>{{$a->topic}}</a>
                        @endforeach
                    </div>
                </div>
                @endif



                @yield('page_header')
                <div id="voyager-notifications"></div>
                @yield('content')
            </div>
        </div>
    </div>
</div>
@include('voyager::partials.app-footer')

<!-- Javascript Libs -->


<script type="text/javascript" src="{{ voyager_asset('js/app.js') }}"></script>
<script src="{{ asset('admin/chart.min.js') }}"></script>

<script>
    @if(Session::has('alerts'))
        let alerts = {!! json_encode(Session::get('alerts')) !!};
        helpers.displayAlerts(alerts, toastr);
    @endif

    @if(Session::has('message'))

    // TODO: change Controllers to use AlertsMessages trait... then remove this
    var alertType = {!! json_encode(Session::get('alert-type', 'info')) !!};
    var alertMessage = {!! json_encode(Session::get('message')) !!};
    var alerter = toastr[alertType];

    if (alerter) {
        alerter(alertMessage);
    } else {
        toastr.error("toastr alert-type " + alertType + " is unknown");
    }
    @endif
</script>
@include('voyager::media.manager')

<!-- Dynamic File Preview Modal & Script Injection -->
<script>
    // General modal-based preview function for all document and image types
    function openGlobalModalPreview(fileUrl, label, filesList) {
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
        
        var $thumbnails = $('#galleryThumbnailsContainer');
        $thumbnails.empty();
        
        if (filesList && filesList.length > 1) {
            filesList.forEach(function(item) {
                var isImg = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].indexOf(item.ext) !== -1;
                var isPdf = item.ext === 'pdf';
                var isActive = (item.url === fileUrl);
                
                var $thumb = $('<div class="gallery-thumb-item" title="' + item.name + '" style="width: 50px; height: 50px; border-radius: 8px; overflow: hidden; border: 2px solid ' + (isActive ? '{{ config("voyager.primary_color", "#22A7F0") }}' : '#e2e8f0') + '; cursor: pointer; display: flex; align-items: center; justify-content: center; background: #ffffff; transition: all 0.2s ease; box-shadow: ' + (isActive ? '0 0 0 2px rgba(34, 167, 240, 0.15)' : 'none') + ';"></div>');
                
                if (isImg) {
                    $thumb.append('<img src="' + item.url + '" style="width: 100%; height: 100%; object-fit: cover;">');
                } else if (isPdf) {
                    $thumb.append('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 22px; height: 22px; color: #ef4444;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>');
                } else {
                    $thumb.append('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 22px; height: 22px; color: #64748b;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>');
                }
                
                $thumb.hover(
                    function() { if (!isActive) $(this).css('border-color', '#94a3b8'); },
                    function() { if (!isActive) $(this).css('border-color', '#e2e8f0'); }
                );
                
                $thumb.on('click', function(e) {
                    e.preventDefault();
                    openGlobalModalPreview(item.url, label, filesList);
                });
                
                $thumbnails.append($thumb);
            });
            $thumbnails.show();
        } else {
            $thumbnails.hide();
        }
        
        $('#imageGalleryModal').modal('show');
    }

    $(document).ready(function() {
        // Dynamically inject the preview modal if it doesn't exist in DOM (unconditionally on all layouts)
        if ($('#imageGalleryModal').length === 0) {
            var modalHtml = `
                <div class="modal fade" id="imageGalleryModal" tabindex="-1" role="dialog" aria-labelledby="imageGalleryModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15); z-index: 1050;">
                            <div class="modal-header" style="background: #ffffff; border-bottom: 1px solid #f1f5f9; padding: 16px 24px; position: relative; min-height: 56px;">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 24px; line-height: 1; color: #94a3b8; cursor: pointer; float: right; margin-top: -2px; opacity: 0.8; outline: none; padding: 0;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="imageGalleryModalLabel" style="font-weight: 700; color: #0f172a; margin: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.5; float: left;">Preview</h4>
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
                                <div id="galleryThumbnailsContainer" style="display: flex; justify-content: center; gap: 12px; margin-top: 18px; flex-wrap: wrap;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
            
            // Clear preview elements on modal close to prevent memory/resource leaks
            $('#imageGalleryModal').on('hidden.bs.modal', function () {
                $('#galleryActiveIframe').attr('src', '');
                $('#galleryActiveImage').attr('src', '');
                $('#galleryFallbackLink').attr('href', '');
            });
        }

        // Global DataTable & Table Cell File/Image Decorator logic
        function isFileLink(url, text) {
            if (!url) return false;
            if (url.indexOf('javascript:') === 0) return false;
            
            var ext = url.split('.').pop().toLowerCase().split('?')[0].split('#')[0];
            var fileExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'txt', 'csv'];
            if (fileExtensions.indexOf(ext) !== -1) {
                return true;
            }
            if (url.indexOf('/storage/') !== -1 || url.indexOf('/uploads/') !== -1) {
                return true;
            }
            return false;
        }

        function getFileIconSvg(ext) {
            if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].indexOf(ext) !== -1) {
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px; color: #3b82f6; flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>';
            } else if (ext === 'pdf') {
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px; color: #ef4444; flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
            } else {
                return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px; color: #64748b; flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>';
            }
        }

        function decorateTableFilesAndImages() {
            // 1. Process document and file links
            $('#dataTable tbody td a, .table tbody td a').each(function() {
                var $link = $(this);
                if ($link.hasClass('global-decorated') || $link.closest('.global-file-preview-container').length > 0 || $link.hasClass('btn') || $link.hasClass('delete') || $link.hasClass('edit')) {
                    return;
                }
                
                var href = $link.attr('href');
                var text = $link.text().trim();
                
                if (isFileLink(href, text)) {
                    $link.addClass('global-decorated');
                    
                    var fileName = text;
                    if (!fileName || fileName.toLowerCase() === 'download' || fileName.toLowerCase() === 'view') {
                        fileName = href.split('/').pop().split('?')[0];
                    }
                    
                    var displayLabel = fileName;
                    if (displayLabel.length > 20) {
                        displayLabel = displayLabel.substring(0, 17) + '...';
                    }
                    
                    var ext = href.split('.').pop().toLowerCase().split('?')[0];
                    var isPreviewable = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf'].indexOf(ext) !== -1;
                    
                    var $container = $('<div class="global-file-preview-container"></div>');
                    $container.append(getFileIconSvg(ext));
                    
                    var $textSpan = $('<a href="' + href + '" target="_blank" title="' + fileName + '">' + displayLabel + '</a>');
                    $container.append($textSpan);
                    
                    var $actions = $('<div style="display: inline-flex; gap: 6px; align-items: center; margin-left: 4px;"></div>');
                    
                    if (isPreviewable) {
                        var $previewBtn = $('<button type="button" class="btn-table-preview" title="Preview File"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>');
                        $previewBtn.on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            openGlobalModalPreview(href, fileName);
                        });
                        $actions.append($previewBtn);
                    }
                    
                    var $downloadBtn = $('<a href="' + href + '" download="' + fileName + '" title="Download File" class="btn-table-preview" style="color: #475569;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 13px; height: 13px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg></a>');
                    $actions.append($downloadBtn);
                    
                    $container.append($actions);
                    $link.replaceWith($container);
                }
            });
            
            // 2. Process image elements
            $('#dataTable tbody td img, .table tbody td img').each(function() {
                var $img = $(this);
                if ($img.hasClass('global-decorated') || $img.closest('.global-image-container').length > 0) {
                    return;
                }
                
                var src = $img.attr('src');
                if (!src) return;
                
                $img.addClass('global-decorated');
                
                var imgName = src.split('/').pop().split('?')[0];
                
                var $wrapper = $('<div class="global-image-container"></div>');
                $img.wrap($wrapper);
                
                var $actions = $('<div style="display: flex; gap: 8px; justify-content: center; margin-top: 4px; border-top: 1px solid #f1f5f9; padding-top: 4px;"></div>');
                
                var $previewBtn = $('<button type="button" class="btn-table-preview" title="Preview Image"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>');
                $previewBtn.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openGlobalModalPreview(src, 'Image');
                });
                $actions.append($previewBtn);
                
                var $downloadBtn = $('<a href="' + src + '" download="' + imgName + '" title="Download Image" class="btn-table-preview" style="color: #475569;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 13px; height: 13px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg></a>');
                $actions.append($downloadBtn);
                
                $img.after($actions);
            });
        }

        // Run decoration initially
        decorateTableFilesAndImages();
        
        // Listen to DataTable draw events for live updates (pagination/searching/sorting)
        if (typeof $ !== 'undefined' && $('#dataTable').length > 0) {
            $('#dataTable').on('draw.dt', function() {
                decorateTableFilesAndImages();
            });
        }
        
        // Listen to AJAX complete events in case content is loaded dynamically
        $(document).ajaxComplete(function() {
            decorateTableFilesAndImages();
        });

        // Only run edit/add transformations if form edit-add wrapper exists
        if ($('.form-edit-add').length > 0) {
            // Transform standard file inputs
            $('.form-edit-add div[data-field-name]').each(function() {
                var $activeDiv = $(this);
                if ($activeDiv.find('.custom-attachment-bar').length > 0 || $activeDiv.hasClass('custom-attachment-bar')) return;
                
                var $fileLink = $activeDiv.find('.fileType');
                var $removeBtn = $activeDiv.find('.remove-multi-file, .remove-single-file');
                
                if ($fileLink.length > 0) {
                    var fileUrl = $fileLink.attr('href');
                    var fileName = $fileLink.text().trim();
                    var fieldLabel = $activeDiv.closest('.form-group').find('label').first().text().replace(/\*|:/g, '').trim() || 'File';
                    
                    if (!fileName || fileName === 'Download' || fileName === 'download') {
                        fileName = fieldLabel + '.' + fileUrl.split('.').pop().split('?')[0];
                    }
                    
                    // Build list of files for modal gallery preview (all files under the same form-group)
                    var filesList = [];
                    $activeDiv.closest('.form-group').find('div[data-field-name] .fileType').each(function(index, el) {
                        var $fl = $(el);
                        var fUrl = $fl.attr('href');
                        var ext = fUrl.split('.').pop().toLowerCase().split('?')[0];
                        var name = $fl.text().trim();
                        if (!name || name === 'Download' || name === 'download') {
                            name = fieldLabel + ' (' + (index + 1) + ')';
                        }
                        filesList.push({ url: fUrl, ext: ext, name: name });
                    });
                    
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
                    
                    $bar.append($removeBtn);
                    $activeDiv.empty().append($bar);
                    
                    $bar.find('.btn-custom-preview-trigger').on('click', function(e) {
                        e.preventDefault();
                        openGlobalModalPreview(fileUrl, fieldLabel, filesList);
                    });
                }
            });

            // Transform standard image inputs (single and multiple)
            $('.form-edit-add div[data-field-name], .form-edit-add .img_settings_container[data-field-name]').each(function() {
                var $container = $(this);
                if ($container.find('.custom-attachment-bar').length > 0 || $container.hasClass('custom-attachment-bar')) return;
                if ($container.find('.fileType').length > 0) return; // Already handled by file inputs
                
                var $img = $container.find('img');
                var $removeBtn = $container.find('.remove-single-image, .remove-multi-image');
                
                if ($img.length > 0) {
                    $img.hide(); // Hide the inline image
                    var imgUrl = $img.attr('src');
                    var fieldLabel = $container.closest('.form-group').find('label').first().text().replace(/\*|:/g, '').trim() || 'Image';
                    var imgName = imgUrl.split('/').pop().split('?')[0];
                    
                    // Build list of all images in the same form-group for gallery
                    var filesList = [];
                    $container.closest('.form-group').find('img').each(function(index, el) {
                        var $im = $(el);
                        var url = $im.attr('src');
                        var ext = url.split('.').pop().toLowerCase().split('?')[0];
                        filesList.push({ url: url, ext: ext, name: fieldLabel + ' (' + (index + 1) + ')' });
                    });
                    
                    var $bar = $('<div class="custom-attachment-bar">' +
                                 '  <a href="' + imgUrl + '" target="_blank" class="attachment-download-btn">' +
                                 '    <svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>' +
                                 '    <span>' + imgName + '</span>' +
                                 '  </a>' +
                                 '  <button type="button" class="btn-custom-preview-trigger">' +
                                 '    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>' +
                                 '    <span>Preview</span>' +
                                 '  </button>' +
                                 '</div>');
                    
                    $bar.append($removeBtn);
                    $container.append($bar); // Append it next to the hidden image
                    
                    $bar.find('.btn-custom-preview-trigger').on('click', function(e) {
                        e.preventDefault();
                        openGlobalModalPreview(imgUrl, fieldLabel, filesList);
                    });
                }
            });
        }
    });
</script>

@yield('javascript')
@stack('javascript')
@if(!empty(config('voyager.additional_js')))<!-- Additional Javascript -->
    @foreach(config('voyager.additional_js') as $js)<script type="text/javascript" src="{{ asset($js) }}"></script>@endforeach
@endif

</body>
</html>
