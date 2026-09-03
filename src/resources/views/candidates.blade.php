@extends('layout')

@section('title', 'Candidates')


@section('header')


@stop


@section('content')


    <!-- Breadcrumb Section Start -->
    <div class="breadcrumb-section section bg_color--5 pt-60 pt-sm-50 pt-xs-40 pb-60 pb-sm-50 pb-xs-40">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page-breadcrumb-content">
                        <ul class="page-breadcrumb">
                            <li><a href="{{url('/')}}">Home</a></li>
                            <li>Candidates</li>
                        </ul>
                        <h1>Candidates</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb Section Start -->

    <!-- Candidates Listing Section Start -->
    <div class="candidates-listing-section section bg_color--5 pb-120 pb-lg-100 pb-md-75 pb-sm-60 pb-xs-50">
        <div class="container">
            <div class="row g-0">

                <div class="col-lg-4 order-lg-1 order-2 pr-55 pr-md-15 pr-sm-15 pr-xs-15">
                    <div class="sidebar-wrapper-two mt-sm-40 mt-xs-40">
                        <form action="#">
                            <div class="common-sidebar-widget sidebar-two">
                                <h2 class="sidebar-title">Find A Candidate</h2>
                                <div class="sidebar-search-form-two">

                                    <div class="input-group">
                                        <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
                                        <i class="lnr lnr-magnifier"></i>
                                    </div>

                                </div>
                            </div>

                            @if(\Auth::user())
                                <div class="common-sidebar-widget sidebar-two">
                                    <h2 class="sidebar-title">Your Candidates</h2>
                                    <div class="sidebar-category">
                                        <select class="nice-select wide" name="purpose">
                                            <option value="">Select One</option>
                                            <option {{ request('purpose') == 'FAVORITE' ? 'selected' : '' }}  value="FAVORITE">Favorite Candidate</option>
                                            <option {{ request('purpose') == 'RESERVE' ? 'selected' : '' }}  value="RESERVE">Reserve Candidate</option>
                                            <option {{ request('purpose') == 'SELECTED' ? 'selected' : '' }}  value="SELECTED">Selected Candidate</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="common-sidebar-widget sidebar-two">
                                <h2 class="sidebar-title">Position</h2>
                                <div class="sidebar-category">
                                    <select class="nice-select wide" name="group">
                                        <option value="">Select One</option>
                                        @foreach($groups as $group)
                                            <option {{ request('group') == $group->id ? 'selected' : '' }}  value="{{$group->id}}">{{$group->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="common-sidebar-widget sidebar-two">
                                <h2 class="sidebar-title">Gender</h2>
                                <select class="nice-select wide" name="gender">
                                    <option value="">Select One</option>
                                    <option {{ request('gender') == 'M' ? 'selected' : '' }} value="M">Male</option>
                                    <option {{ request('gender') == 'F' ? 'selected' : '' }} value="F">Female</option>
                                </select>
                            </div>
                            {{--<div class="common-sidebar-widget sidebar-two">
                                <h2 class="sidebar-title">Experience</h2>
                                <ul class="sidebar-cbx-list">
                                    <li>
                                        <div class="filter-name-item">
                                            <input type="checkbox" value="" name="expericence[]" id="experience-cbx">
                                            <label for="experience-cbx">> 5 years </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="filter-name-item">
                                            <input type="checkbox" name="expericence[]" id="experience-cbx-two">
                                            <label for="experience-cbx-two"> &lt; 1 year</label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="filter-name-item">
                                            <input type="checkbox" name="expericence[]" id="experience-cbx-three">
                                            <label for="experience-cbx-three">1-3 years </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="filter-name-item">
                                            <input type="checkbox" name="expericence[]" id="experience-cbx-four">
                                            <label for="experience-cbx-four">3-5 years</label>
                                        </div>
                                    </li>
                                </ul>
                            </div>--}}
                            <button type="submit" class="ht-btn theme-btn theme-btn-two w-100">Search</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8 order-lg-2 order-1">
                    {{-- <div class="filter-form"  >
                         <div class="result-sorting">
                             <div class="total-result">
                                 <span class="total">(17)</span>
                                 Results Found
                             </div>
                             <div class="form-left">
                                 <div class="layout-switcher">
                                     <ul class="nav">
                                         <li><a class="active show" data-bs-toggle="tab" href="#list"><i class="fa fa-list"></i></a></li>
                                         <li><a data-bs-toggle="tab" href="#grid"><i class="fa fa-th"></i></a></li>
                                     </ul>
                                 </div>
                             </div>
                         </div>
                     </div>--}}
                    <div class="tab-content">
                        <div id="list" class="tab-pane fade show active">
                            <div class="row">
                                @foreach ($data as $d)
                                    <div class="col-lg-12 mb-20">
                                        <!-- Single Job Start  -->
                                        <div class="single-job style-two">
                                            <div class="info-top align-items-start">
                                                <div class="job-image candidates-image">
                                                    <a href="{{route('web.candidates', $d->code)}}">
                                                        <img src="{{ Storage::disk(config('voyager.storage.disk'))->exists( $d->half_photo_file_path)? Voyager::image( $d->half_photo_file_path) : Voyager::image( setting('admin.icon_image')) }}" alt="logo">
                                                    </a>
                                                </div>
                                                <div class="job-info">
                                                    <div class="job-info-inner">
                                                        <div class="job-info-top">
                                                            @if(Auth::user())
                                                                <div class="saveJob for-listing">
                                                                    {{--                                                            <span class="featured-label mr-20">featured</span>--}}
                                                                    <a class="save-job mr-15" href="javascript:void(0)" >
                                                                        <i style="{{(\App\Helpers\CommonClass::checkEmployerCandidates($d->id, \App\Helpers\CommonClass::user()->employer_id, 'FAVORITE'))?'color: red;':'color: #bbb;'}}"
                                                                           onclick="makeCandidateForMe(this,`{{$d->id}}`, `{{\App\Helpers\CommonClass::user()->employer_id}}`, 'FAVORITE')"
                                                                           class="far fa-heart"></i>
                                                                    </a>
                                                                    <a class="save-job mr-15" href="javascript:void(0)" >
                                                                        <i style="{{(\App\Helpers\CommonClass::checkEmployerCandidates($d->id, \App\Helpers\CommonClass::user()->employer_id, 'RESERVE'))?'color: red;':'color: #bbb;'}}"
                                                                           onclick="makeCandidateForMe(this,`{{$d->id}}`, `{{\App\Helpers\CommonClass::user()->employer_id}}`, 'RESERVE')"
                                                                           class="far fa-star"></i>
                                                                    </a>
                                                                    <a class="save-job mr-15" href="javascript:void(0)" >
                                                                        <i style="{{(\App\Helpers\CommonClass::checkEmployerCandidates($d->id, \App\Helpers\CommonClass::user()->employer_id, 'SELECTED'))?'color: red;':'color: #bbb;'}}"
                                                                           onclick="makeCandidateForMe(this,`{{$d->id}}`, `{{\App\Helpers\CommonClass::user()->employer_id}}`, 'SELECTED')"
                                                                           class="far fa-user"></i>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                            <div class="title-name">
                                                                <h3 class="job-title">
                                                                    <a href="{{route('web.candidates', $d->code)}}">{{$d->name}}</a>
                                                                </h3>
                                                                @if($d->classGroup)
                                                                    <div class="employer-name">
                                                                        <span>{{ucwords(str_replace('rapid interview for ', '', strtolower($d->classGroup->name))) }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="job-meta-three">
                                                            <div class="field-experience-time">
                                                                <i class="lnr lnr-graduation-hat"></i>
                                                                <span class="label">Abord:</span>
                                                                <a href="#">{{($d->latestExamResult)?$d->latestExamResult->abroad_ex:0}} years</a>
                                                            </div>
                                                            <div class="field-experience-time">
                                                                <i class="lnr lnr-map-marker"></i>
                                                                <span class="label">Local:</span>
                                                                <a href="#">{{($d->latestExamResult)?$d->latestExamResult->local_ex:0}} years</a>
                                                            </div>
                                                            <div class="field-experience-time">
                                                                <i class="lnr lnr-thumbs-up"></i>
                                                                <span class="label">English:</span>
                                                                <a href="#">{{($d->latestExamResult)?$d->latestExamResult->english:0}} marks</a>
                                                            </div>

                                                        </div>
                                                        <div>
                                                            <p>
                                                                @if (strlen($d->basic_info_special) > 200)
                                                                    {{substr($d->basic_info_special, 0, 200)}}...;
                                                                @else
                                                                    {{$d->basic_info_special}}
                                                                @endif
                                                            </p>
                                                        </div>
                                                        {{-- <div class="job-skill-tag">
                                                             <a href="#">Android</a>
                                                             <a href="#">app</a>
                                                             <a href="#">ReactJs</a>
                                                             <a href="#">Ruby</a>
                                                         </div>
                                                         <div class="candidates-portfolio">
                                                             <ul>
                                                                 <li><a href="#"><img src="assets/images/portfolio/portfolio1.jpg" alt=""></a></li>
                                                                 <li><a href="#"><img src="assets/images/portfolio/portfolio2.jpg" alt=""></a></li>
                                                                 <li><a href="#"><img src="assets/images/portfolio/portfolio3.jpg" alt=""></a></li>
                                                                 <li><a href="#"><img src="assets/images/portfolio/portfolio4.jpg" alt=""></a></li>
                                                                 <li><a href="#"><img src="assets/images/portfolio/portfolio5.jpg" alt=""></a></li>
                                                                 <li><a href="#"><img src="assets/images/portfolio/portfolio6.jpg" alt=""></a></li>
                                                             </ul>
                                                         </div>--}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Single Job End -->
                                    </div>
                                @endforeach

                            </div>
                            <div class="row">
                                <div class="col-12">
                                    @if ($data->hasPages())
                                        @if ($data->hasPages())
                                            <ul class="page-pagination">

                                                {{-- Previous Page --}}
                                                @if ($data->onFirstPage())
                                                    <li class="disabled"><a href="#"><i class="fa fa-angle-left"></i></a></li>
                                                @else
                                                    <li><a href="{{ $data->previousPageUrl() }}"><i class="fa fa-angle-left"></i></a></li>
                                                @endif

                                                {{-- Page Numbers with Ellipses --}}
                                                @php
                                                    $total = $data->lastPage();
                                                    $current = $data->currentPage();
                                                    $range = 5; // number of pages before and after current
                                                @endphp

                                                {{-- First Page --}}
                                                @if ($current > $range + 5)
                                                    <li><a href="{{ $data->url(1) }}">1</a></li>
                                                    <li class="disabled"><span>...</span></li>
                                                @endif

                                                {{-- Page Range --}}
                                                @for ($i = max(1, $current - $range); $i <= min($total, $current + $range); $i++)
                                                    @if ($i == $current)
                                                        <li class="active"><a href="#">{{ $i }}</a></li>
                                                    @else
                                                        <li><a href="{{ $data->url($i) }}">{{ $i }}</a></li>
                                                    @endif
                                                @endfor

                                                {{-- Last Page --}}
                                                @if ($current < $total - $range - 1)
                                                    <li class="disabled"><span>...</span></li>
                                                    <li><a href="{{ $data->url($total) }}">{{ $total }}</a></li>
                                                @endif

                                                {{-- Next Page --}}
                                                @if ($data->hasMorePages())
                                                    <li><a href="{{ $data->nextPageUrl() }}"><i class="fa fa-angle-right"></i></a></li>
                                                @else
                                                    <li class="disabled"><a href=""><i class="fa fa-angle-right"></i></a></li>
                                                @endif
                                            </ul>
                                        @endif


                                        {{--<ul class="page-pagination">
                                            --}}{{-- Previous Page Link --}}{{--
                                            @if ($data->onFirstPage())
                                                <li class="disabled"><a href="#"><i class="fa fa-angle-left"></i></a></li>
                                            @else
                                                <li><a href="{{ $data->previousPageUrl() }}"><i class="fa fa-angle-left"></i></a></li>
                                            @endif



                                            --}}{{-- Pagination Elements --}}{{--
                                            @foreach ($data->getUrlRange(1, $data->lastPage()) as $page => $url)
                                                @if ($page == $data->currentPage())
                                                    <li class="active"><a href="#">{{ $page }}</a></li>
                                                @else
                                                    <li><a href="{{ $url }}">{{ $page }}</a></li>
                                                @endif
                                            @endforeach

                                            --}}{{-- Next Page Link --}}{{--
                                            @if ($data->hasMorePages())
                                                <li><a href="{{ $data->nextPageUrl() }}"><i class="fa fa-angle-right"></i></a></li>
                                            @else
                                                <li class="disabled"><a href="#"><i class="fa fa-angle-right"></i></a></li>
                                            @endif
                                        </ul>--}}
                                    @endif

                                </div>
                            </div>
                        </div>

                        {{--<div  id="grid" class="tab-pane fade">
                             <div class="row">

                                 <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-30">
                                     <!-- Single Job Start  -->
                                     <div class="single-job style-two candidates-grid">
                                         <div class="info-top align-items-start">
                                             <div class="candidate-job">
                                                 <span class="featured-label mr-20">featured</span>
                                                 <a class="save-job" href="#quick-view-modal-container" data-toggle="modal">
                                                     <i class="far fa-heart"></i>
                                                 </a>
                                             </div>
                                             <div class="job-image candidates-image">
                                                 <a href="{{route('web.candidates', $d->code)}}">
                                                     <img src="{{route('web.candidates', $d->code)}}" alt="logo">
                                                 </a>
                                             </div>
                                             <div class="job-info">
                                                 <div class="job-info-inner">
                                                     <div class="job-info-top mt-30">
                                                         <div class="title-name">
                                                             <h3 class="job-title">
                                                                 <a href="{{route('web.candidates', $d->code)}}">Alizabeth</a>
                                                             </h3>
                                                             <div class="employer-name">
                                                                 <span>Front End Developer</span>
                                                             </div>
                                                         </div>
                                                     </div>
                                                     <div class="job-meta-three">
                                                         <div class="field-experience-time">
                                                             <i class="lnr lnr-graduation-hat"></i>
                                                             <span class="label">Experience</span>
                                                             <a href="#">3-5 years</a>
                                                         </div>
                                                         <div class="field-map"><i class="lnr lnr-map-marker"></i>Chicago, Illinois</div>
                                                         <div class="field-hour-rate"><i class="lnr lnr-thumbs-up"></i>
                                                             <span class="label">Hour Rate</span> $30</div>
                                                     </div>
                                                     <div class="job-skill-tag">
                                                         <a href="#">Android</a>
                                                         <a href="#">app</a>
                                                         <a href="#">ReactJs</a>
                                                         <a href="#">Ruby</a>
                                                     </div>
                                                     <div class="candidates-portfolio">
                                                         <ul>
                                                             <li><a href="#"><img src="assets/images/portfolio/portfolio1.jpg" alt=""></a></li>
                                                             <li><a href="#"><img src="assets/images/portfolio/portfolio2.jpg" alt=""></a></li>
                                                             <li><a href="#"><img src="assets/images/portfolio/portfolio3.jpg" alt=""></a></li>
                                                             <li><a href="#"><img src="assets/images/portfolio/portfolio4.jpg" alt=""></a></li>
                                                             <li><a href="#"><img src="assets/images/portfolio/portfolio5.jpg" alt=""></a></li>
                                                             <li><a href="#"><img src="assets/images/portfolio/portfolio6.jpg" alt=""></a></li>
                                                         </ul>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                     <!-- Single Job End -->
                                 </div>
                             </div>
                             <div class="row">
                                 <div class="col-12">
                                     @if ($data->hasPages())
                                         <ul class="page-pagination">
                                             --}}{{-- Previous Page Link --}}{{--
                                             @if ($data->onFirstPage())
                                                 <li class="disabled"><a href="#"><i class="fa fa-angle-left"></i></a></li>
                                             @else
                                                 <li><a href="{{ $data->previousPageUrl() }}"><i class="fa fa-angle-left"></i></a></li>
                                             @endif

                                             --}}{{-- Pagination Elements --}}{{--
                                             @foreach ($data->getUrlRange(1, $data->lastPage()) as $page => $url)
                                                 @if ($page == $data->currentPage())
                                                     <li class="active"><a href="#">{{ $page }}</a></li>
                                                 @else
                                                     <li><a href="{{ $url }}">{{ $page }}</a></li>
                                                 @endif
                                             @endforeach

                                             --}}{{-- Next Page Link --}}{{--
                                             @if ($data->hasMorePages())
                                                 <li><a href="{{ $data->nextPageUrl() }}"><i class="fa fa-angle-right"></i></a></li>
                                             @else
                                                 <li class="disabled"><a href="#"><i class="fa fa-angle-right"></i></a></li>
                                             @endif
                                         </ul>
                                     @endif
                                 </div>
                             </div>
                         </div>--}}
                    </div>

                </div>

            </div>
        </div>
    </div>
    <!-- Candidates Listing Section End -->




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
                        btn.style.color = "red";
                        toastr.success(res.msg);
                    }else{
                        btn.style.color = "#bbb";
                        toastr.success(res.msg);
                    }
                },
            });
        }

    </script>
@stop
