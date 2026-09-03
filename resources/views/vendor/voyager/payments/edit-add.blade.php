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
        .table p{
            margin: 0;
            padding: 0;
        }
    </style>
@stop

@section('page_title', __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('page_header')
	<h1 class="page-title">
		<i class="{{ $dataType->icon }}"></i>
		@php
			if(request()->agent_id && $info = App\Models\Agent::where('id',request()->agent_id)->first()){
				echo ' Payments of Agent: <span class="text-primary">'. '('.$info->code.') '.$info->name.'</span>';
				$listUrl = route('voyager.agents.index');

			}elseif(request()->teacher_id && $info = App\Models\Teacher::where('id',request()->teacher_id)->first()){
				echo ' Payments of Teacher: <span class="text-primary">'. '('.$info->code.') '.$info->name.'</span>';
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
							<h3 class="panel-title"><i class="icon wb-image"></i>Add Payment</h3>
							<div class="panel-actions">
								<a class="panel-action voyager-angle-down" data-toggle="panel-collapse"
								   aria-hidden="true"></a>
							</div>
						</div>
						<div class="panel-body">
							@if(request()->agent_id )
								<div class="form-group  col-md-3" style="display: none">
									<label for="agent_id">Agents</label>
									<input type="hidden" class="form-control" name="agent_id" placeholder="Agent" value="{{ request()->agent_id }}">
								</div>
                            @endif
                                @if(request()->teacher_id )
                                    <div class="form-group  col-md-3" style="display: none">
                                        <label for="teacher_id">Teacher</label>
                                        <input type="hidden" class="form-control" name="teacher_id" placeholder="Teacher" value="{{ request()->teacher_id }}">
                                    </div>
                                @endif
                                @if(request()->candidate_id )
                                    <div class="form-group  col-md-3" style="display: none">
                                        <label for="candidate_id">Candidate</label>
                                        <input type="hidden" class="form-control" name="candidate_id" placeholder="candidate" value="{{ request()->candidate_id }}">
                                    </div>
                                @endif
                                <div class="form-group  col-md-3">
                                    <label for="name">Invoice No.</label>
                                    <input type="text" readonly class="form-control" name="invoice_no"
                                           placeholder="Order No." value="{{\App\Helpers\CommonClass::invoiceNo()}}">
                                </div>


                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['title'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['type'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['amount'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['payment_belongsto_currency_relationship'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['exchange_rate'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['bank_name'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['acc_no'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['account_name'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['trx_no'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['payment_date'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['status'], 'width'=>'3'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['remarks'], 'width'=>'9'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['description'], 'width'=>'6'])
                                @include('vendor.voyager.partials.form-field', ['formField'=>'include', 'fields'=>['cheque_file_path'], 'width'=>'6'])


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



                @if(isset($info->balance))
				<div class="col-md-12">
					<div class="panel panel-bordered panel-warning">
						<div class="panel-heading">
							<h3 class="panel-title">
								<i class="icon wb-image"></i>Payment Ledger

                                <span style="float: right; padding-right: inherit;">
									 Total Balance ({{($info->balance < 0)?'Advance':'Due'}}):  {{\App\Helpers\CommonClass::currencySymbol(true,2,abs($info->balance))}}
									&nbsp;&nbsp;
								</span>

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
{{--										<th style="width: 10%">Trx No.</th>--}}
{{--										<th style="width: 10%">Invoice No.</th>--}}
{{--										<th style="width: 20%">Title</th>--}}
{{--										<th style="width: 10%">Type</th>--}}
{{--                                        <th style="width: 15%">Payment Date</th>--}}
                                        <th style="width: 35%">Transaction</th>
                                        <th style="width: 15%">Amount</th>
                                        <th style="width: 10%">Cheque</th>

										<th style="width: 8%">Status</th>
{{-- 										<th style="width: 5%">Action</th>--}}
                                        <th style="width: 12%">Balance</th>
									</tr>
									</thead>
									<tbody>
									@if($info && $info->payments)
										@foreach($info->payments as $s=>$i)
											<tr>
												<td>{{++$s}}</td>
												 <td>
                                                    <p><b>Title:</b> {{$i->title}}</p>
                                                    <p><b>Invoice No.:</b> {{$i->invoice_no}}</p>
                                                    @if(!empty($i->trx_no)) <p><b>Trx No.:</b> {{$i->trx_no}}</p>@endif
                                                    <p><b>Type:</b> {{$i->type}} @if(!empty($i->payment_method)) by {{$i->payment_method}} @endif</p>
                                                    <p><b>Payment Date:</b> {{$i->payment_date}} {{$i->payment_time}}</p>
                                                    @if(!empty($i->payment_receiver))<p><b>Payment Receiver:</b> {{$i->payment_receiver}}</p>@endif
                                                    @if(!empty($i->payment_received_location))<p><b>Payment Received Location:</b> {{$i->payment_received_location}}</p>@endif
                                                </td>
{{--												<td>{{$i->trx_no}}</td>--}}
{{--												<td>{{$i->invoice_no}}</td>--}}
{{--												<td>{{$i->title}}</td>--}}
{{--												<td>{{$i->type}}</td>--}}
                                                {{--												<td>{{$i->payment_date}}</td>--}}
                                                <td>
                                                    <p><b>Request Amount:</b> {{\App\Helpers\CommonClass::currencySymbol(true,$i->currency_id,$i->amount)}}</p>
                                                    @if($i->status !='P' )
                                                        @if($i->currency_id !=2 )
                                                            <p><b>Euro Rate:</b> {{\App\Helpers\CommonClass::currencySymbol(true,1,$i->exchange_rate)}}</p>
                                                        @endif

                                                        {{--                                                        <p><b>Approved Amount:</b> {{\App\Helpers\CommonClass::currencySymbol(true,2,($i->amount/$i->exchange_rate))}}</p>--}}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($i->cheque_file_path))
                                                        <a target="_blank" href="{{Voyager::image($i->cheque_file_path)}}">
                                                            <img src="{{Voyager::image($i->cheque_file_path)}}" style="width: 100px">
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>  {!! \App\Helpers\CommonClass::getPaymentStatus($i->status)  !!}</td>
											{{-- <td>
													<a href="javascript:;" class="btn btn-sm btn-danger pull-right " onclick="openModal('{{route("admin.ajax.notifications")}}', false, 'modal-lg', 'Payment')">
														<i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Approve</span>
													</a>
												</td>--}}
                                                <td>{{\App\Helpers\CommonClass::currencySymbol(true,2,abs($i->balance))}}</td>
											</tr>
										@endforeach
									@endif
									</tbody>
								</table>
							</div>
						</div>


					</div>
				</div>
                @endif

			</form>

		</div>
	</div>

@stop

@section('javascript')

@stop
