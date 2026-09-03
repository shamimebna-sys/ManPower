@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp

@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">


@stop

@section('page_title', __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular') }}
    </h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')

    @include('voyager::partials.candidate-partial-top-menu')


    <div class="row">
        @include('voyager::partials.candidate-partial-menu')

        <div class="col-md-9">
            <div class="page-content edit-add container-fluid">
                <div class="row">
                    <div class="col-md-12">


                        <div class="panel panel-bordered panel-primary">

                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="icon wb-image"></i>Add Document</h3>
                                <div class="panel-actions">
                                    <a class="panel-action voyager-angle-down" data-toggle="panel-collapse"
                                       aria-hidden="true"></a>
                                </div>
                            </div>

                            <!-- form start -->
                            <form role="form"
                                  class="form-edit-add"
                                  action="{{ $edit ? route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) : route('voyager.'.$dataType->slug.'.store') }}"
                                  method="POST" enctype="multipart/form-data">
                                <!-- PUT Method if we are editing -->
                            @if($edit)
                                {{ method_field("PUT") }}
                            @endif

                            <!-- CSRF TOKEN -->
                                {{ csrf_field() }}

                                <div class="panel-body">
                                    @if (count($errors) > 0)
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                        <input type="hidden" value="{{request()->get('candidate_id')}}" name="candidate_id">
                                        @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['document_no'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['candidate_document_belongsto_document_type_relationship'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['issue_type'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['issue_date'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['expire_date'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['status'], 'width'=>'4'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['file_path'], 'width'=>'12'])
                                    @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['reason'], 'width'=>'12'])

                                </div><!-- panel-body -->

                                <div class="panel-footer">
                                    @section('submit-buttons')
                                        <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                                    @stop
                                    @yield('submit-buttons')
                                </div>
                            </form>

                            <div style="display:none">
                                <input type="hidden" id="upload_url" value="{{ route('voyager.upload') }}">
                                <input type="hidden" id="upload_type_slug" value="{{ $dataType->slug }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade modal-danger" id="confirm_delete_modal">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"
                                    aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><i class="voyager-warning"></i> {{ __('voyager::generic.are_you_sure') }}</h4>
                        </div>

                        <div class="modal-body">
                            <h4>{{ __('voyager::generic.are_you_sure_delete') }} '<span class="confirm_delete_name"></span>'</h4>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                            <button type="button" class="btn btn-danger" id="confirm_delete">{{ __('voyager::generic.delete_confirm') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Delete File Modal -->

            @if($datatable && count($datatable)> 0)
                <div class="page-content browse container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-bordered  panel-warning">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="icon wb-image"></i>Documents</h3>
                                    <div class="panel-actions">
                                        <a class="panel-action voyager-angle-down" data-toggle="panel-collapse"
                                           aria-hidden="true"></a>
                                    </div>
                                </div>

                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table id="dataTable" class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th >SL No.</th>
                                                <th >Document No.</th>
                                                <th>Document Type</th>
                                                <th>Issue Type</th>
                                                <th>Issue Date</th>
                                                <th>Expire Date</th>
                                                <th>File</th>
                                                <th>Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($datatable as $i=>$data)
                                                <tr>
                                                    <td>  {{++$i}}.  </td>
                                                    <td>  {{$data->document_no}}  </td>
                                                    <td>  {{isset($data->documentType)?$data->documentType->name:'None'}}  </td>
                                                    <td>  {{$data->issue_type}}  </td>
                                                    <td>  {{$data->issue_date}}  </td>
                                                    <td>  {{$data->expire_date}}  </td>
                                                    <td>
                                                        @php
                                                            $files = json_decode($data->file_path);
                                                        @endphp
                                                        @if($files)
                                                            @foreach($files as $file)
                                                                <a href="{{ Voyager::image($file->download_link) }}" target="_blank">
                                                                    {{ $file->original_name }}
                                                                </a><br>
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td>{{$data->status == 'A'?'Active':'Inactive'}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@stop

@section('javascript')


    <script>
        var params = {};
        var $file;

        function deleteHandler(tag, isMulti) {
            return function() {
                $file = $(this).siblings(tag);

                params = {
                    slug:   '{{ $dataType->slug }}',
                    filename:  $file.data('file-name'),
                    id:     $file.data('id'),
                    field:  $file.parent().data('field-name'),
                    multi: isMulti,
                    _token: '{{ csrf_token() }}'
                }

                $('.confirm_delete_name').text(params.filename);
                $('#confirm_delete_modal').modal('show');
            };
        }

        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();

            //Init datepicker for date fields if data-datepicker attribute defined
            //or if browser does not handle date inputs
            $('.form-group input[type=date]').each(function (idx, elt) {
                if (elt.hasAttribute('data-datepicker')) {
                    elt.type = 'text';
                    $(elt).datetimepicker($(elt).data('datepicker'));
                } else if (elt.type != 'date') {
                    elt.type = 'text';
                    $(elt).datetimepicker({
                        format: 'L',
                        extraFormats: [ 'YYYY-MM-DD' ]
                    }).datetimepicker($(elt).data('datepicker'));
                }
            });

            @if ($isModelTranslatable)
            $('.side-body').multilingual({"editing": true});
            @endif

            $('.side-body input[data-slug-origin]').each(function(i, el) {
                $(el).slugify();
            });

            $('.form-group').on('click', '.remove-multi-image', deleteHandler('img', true));
            $('.form-group').on('click', '.remove-single-image', deleteHandler('img', false));
            $('.form-group').on('click', '.remove-multi-file', deleteHandler('a', true));
            $('.form-group').on('click', '.remove-single-file', deleteHandler('a', false));

            $('#confirm_delete').on('click', function(){
                $.post('{{ route('voyager.'.$dataType->slug.'.media.remove') }}', params, function (response) {
                    if ( response
                        && response.data
                        && response.data.status
                        && response.data.status == 200 ) {

                        toastr.success(response.data.message);
                        $file.parent().fadeOut(300, function() { $(this).remove(); })
                    } else {
                        toastr.error("Error removing file.");
                    }
                });

                $('#confirm_delete_modal').modal('hide');
            });
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    <script>
        function reloadCandidateSelect2WithNewRoute($el, newRoute) {
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
                placeholder: 'Select Candidate',
                allowClear: false,
                minimumInputLength: 0
            });
        }

        $(document).ready(function() {
            var agent_id = $('select[name="agent_id"]').val();
            var group_id = $('select[name="group_id"]').val();

            $('select[name="agent_id"]').on('change', function () {
                agent_id = $(this).val();
                var candidateRoute = '/panel/payment-requests/relation?type=payment_request_belongsto_candidate_relationship&method=add&page=1&agent_id='+agent_id+'&group_id='+group_id;
                reloadCandidateSelect2WithNewRoute($('select[name="candidate_id"]'), candidateRoute);
            });

            $('select[name="group_id"]').on('change', function () {
                group_id = $(this).val();
                var candidateRoute = '/panel/payment-requests/relation?type=payment_request_belongsto_candidate_relationship&method=add&page=1&agent_id='+agent_id+'&group_id='+group_id;
                reloadCandidateSelect2WithNewRoute($('select[name="candidate_id"]'), candidateRoute);
            });

        });
    </script>
@stop
