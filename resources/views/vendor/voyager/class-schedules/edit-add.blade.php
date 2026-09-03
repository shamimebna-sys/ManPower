@php
	$edit = !is_null($dataTypeContent->getKey());
	$add  = is_null($dataTypeContent->getKey());
@endphp

@extends('voyager::master')

@section('css')
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<style>
		.border-white {
			border-color: white !important;;
		}
	</style>
@stop

@section('page_title', __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('page_header')
	<h1 class="page-title">
		<i class="{{ $dataType->icon }}"></i>
		@php
			if(request()->teacher_id && $info = App\Models\Teacher::where('id',request()->teacher_id)->first()){
				echo ' Class schedule of <span class="text-primary">'. '('.$info->code.') '.$info->name.'</span>';
				$listUrl = route('voyager.teachers.index');

			}else{
   				$info = false;
				echo '';
				$listUrl = '#';
			}
		@endphp

{{--				{{ __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular') }}--}}
	</h1>
	<a href="{{$listUrl}}" class="btn btn-success btn-add-new">
		<i class="voyager-list"></i> <span>Go to List</span>
	</a>
	@include('voyager::multilingual.language-selector')
@stop

@section('content')
	<div class="page-content edit-add container-fluid">
		<div class="row">
			<form role="form"
			      class="form-edit-add"
			      action="{{ $edit ? route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) : route('voyager.'.$dataType->slug.'.store') }}"
			      method="POST" enctype="multipart/form-data"
			      id="order-form"
			>
				<!-- PUT Method if we are editing -->
				@if($edit)  {{ method_field("PUT") }} @endif
				{{ csrf_field() }}

				<div class="col-md-12">
					@if (count($errors) > 0)
						<div class="alert alert-danger">
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif
				</div>

				<div class="col-md-12">
					<div class="panel panel-bordered panel-info">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="icon wb-image"></i>Add Schedule</h3>
							<div class="panel-actions">
								<a class="panel-action voyager-angle-down" data-toggle="panel-collapse"
								   aria-hidden="true"></a>
							</div>
						</div>
						<div class="panel-body">
							@if(request()->teacher_id )
								<div class="form-group  col-md-3" style="display: none">
									<label for="supplier_id">Teacher</label>
									<input type="hidden" class="form-control" name="teacher_id" placeholder="Teacher"
									       value="{{ request()->teacher_id }}">
								</div>
							@endif

							<div class="form-group  col-md-3" style="display: none">
								<label for="status">Status</label>
								<input type="hidden" class="form-control" name="status" placeholder="status" value="A">
							</div>

                            @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['class_schedule_belongsto_class_group_relationship'], 'width'=>'8'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['status'], 'width'=>'2'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['subject'], 'width'=>'2'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['start_time'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['end_time'], 'width'=>'4'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['week_day'], 'width'=>'4'])


						</div>
						<div class="panel-footer">
							@section('submit-buttons')
								<button type="submit" id="btn-submit"
								        class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
							@stop
							@yield('submit-buttons')
						</div>
					</div>
				</div>



				<div class="col-md-12">
					<div class="panel panel-bordered panel-warning">
						<div class="panel-heading">
							<h3 class="panel-title">
								<i class="icon wb-image"></i> Schedule Details

							</h3>
							<div class="panel-actions">
								<a class="panel-action voyager-angle-down" data-toggle="panel-collapse"
								   aria-hidden="true"></a>
							</div>
						</div>
						<div class="panel-body ">
							<div class="table-responsive">
								<table class="table table-hover datatable " id="product_table">
									<thead>
									<tr>
										<th style="width: 2%">SL.</th>
										<th style="width: 25%">Group.</th>
										<th style="width: 10%">Subject</th>
										<th style="width: 10%">Time From</th>
										<th style="width: 10%">Time To</th>
										<th style="width: 15%">Day</th>
{{-- 										<th style="width: 5%">Action</th>--}}
									</tr>
									</thead>
									<tbody>
									@if($info && $info->schedules)
										@foreach($info->schedules as $s=>$i)
											<tr>
												<td>{{++$s}}</td>
												<td>{{$i->classGroup[0]->name}}</td>
												<td>{{$i->subject}}</td>
												<td>{{date('h:m A', strtotime($i->start_time))}}</td>
												<td>{{date('h:m A', strtotime($i->end_time))}}</td>
												<td>{{$i->week_day}}</td>
											 {{--<td>
													<a href="javascript:;" title="Delete" class="btn btn-sm btn-danger pull-right delete">
														<i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Delete</span>
													</a>
												</td>
											</tr>--}}
										@endforeach
									@endif
									</tbody>
								</table>
							</div>
						</div>


					</div>
				</div>


			</form>

		</div>
	</div>

@stop

@section('javascript')

@stop
