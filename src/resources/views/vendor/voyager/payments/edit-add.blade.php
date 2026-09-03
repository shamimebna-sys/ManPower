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
								<table  class="table table-hover  " id="product_table">
{{--								<table class="table table-hover " id="product_table">--}}
									<thead>
									<tr>
										<th style="width: 2%">SL.</th>
                                        <th style="width: 30%">Transaction</th>
                                        <th style="width: 10%">Cheque</th>
                                        <th style="width: 8%">Status</th>
                                        <th style="width: 23%">Amount</th>
                                        <th style="width: 12%">Balance (€)</th>
									</tr>
									</thead>
									<tbody>
									@if($info && $info->payments)
										@foreach($info->payments as $s=>$i)
											<tr style="{{($i->balance <0)? 'background: rgba(249,178,178,0.42)':''}}">
												<td>{{++$s}}</td>
												 <td>
                                                    <p><b>Title:</b> {{$i->title}}</p>
                                                    <p><b>Invoice No.:</b> {{$i->invoice_no}}</p>
                                                    @if(!empty($i->trx_no)) <p><b>Trx No.:</b> {{$i->trx_no}} @if(!empty($i->payment_method)) ({{$i->payment_method}}) @endif</p>@endif

                                                    @if(!empty($i->payment_receiver))<p><b>Payment Receiver:</b> {{$i->payment_receiver}}</p>@endif
                                                    @if(!empty($i->payment_received_location))<p><b>Payment Received Location:</b> {{$i->payment_received_location}}</p>@endif
                                                    @if(!empty($i->description))<p><b>Description:</b> {{ $i->description }}@endif</p>
                                                    @if(!empty($i->remarks))<p><b>Remarks:</b> {{ $i->remarks }}@endif</p>

                                                     <p><b>Payment Date:</b> {{date('Y-m-d h:i A', strtotime($i->payment_date.' '.$i->payment_time))}}</p>
                                                     <p><b>Created At:</b>  {{date('Y-m-d h:i A', strtotime($i->created_at))}}</p>

                                                     @if($i->status !='P')
                                                         <p><b>Updated At:</b>  {{date('Y-m-d h:i A', strtotime($i->updated_at))}}</p>
                                                     @endif
                                                 </td>

                                                <td>
                                                     @if(!empty($i->cheque_file_path))
                                                         @php
                                                             $filePath = $i->cheque_file_path;
                                                             $fileJson = json_decode($filePath, true);
                                                             if (is_array($fileJson) && isset($fileJson[0]['download_link'])) {
                                                                 $filePath = $fileJson[0]['download_link'];
                                                             }
                                                             $filePath = str_replace('\\', '/', $filePath);
                                                         @endphp

                                                         <div style="margin-top: 5px;">
                                                             <a href="#" class="btn btn-xs btn-info preview-btn" data-url="{{Voyager::image($filePath)}}" data-name="{{ isset($fileJson[0]['original_name']) ? $fileJson[0]['original_name'] : 'Cheque Document' }}">
                                                                 <i class="voyager-eye"></i>
                                                             </a>
                                                             &nbsp;
                                                             <a target="_blank" href="{{Voyager::image($filePath)}}" class="btn btn-xs btn-primary">
                                                                 <i class="voyager-download"></i>
                                                             </a>
                                                         </div>
                                                     @endif
                                                </td>
                                                <td>  {!! \App\Helpers\CommonClass::getPaymentStatus($i->status)  !!}</td>
                                                <td>

                                                    @if($i->currency_id !=2 )
                                                        <p><b>Request Amount:</b> {{\App\Helpers\CommonClass::currencySymbol(true,$i->currency_id,$i->amount)}}</p>
                                                        <p><b>Euro Rate:</b> {{\App\Helpers\CommonClass::currencySymbol(true,1,$i->exchange_rate)}}</p>
                                                        <p style="color: {{($i->type == 'DR'?'red':'green')}}"><b>Request Amount ({{$i->type}}):</b>  {{\App\Helpers\CommonClass::currencySymbol(true,2,$i->amount/(($i->exchange_rate==0)?1:$i->exchange_rate))}}</p>
                                                    @else
                                                        <p style="color: {{($i->type == 'DR'?'red':'green')}}">
                                                            <b>Request Amount ({{$i->type}}):</b>  {{\App\Helpers\CommonClass::currencySymbol(true,$i->currency_id,$i->amount)}}
                                                        </p>
                                                    @endif
                                                </td>
                                                <td>{{$i->balance}}</td>
{{--                                                <td>{{($i->balance<0)?'(-)':'(+)'}} {{\App\Helpers\CommonClass::currencySymbol(true,2,abs($i->balance))}}</td>--}}
{{--                                                <td>{{\App\Helpers\CommonClass::currencySymbol(true,2,$i->balance)}}</td>--}}
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

	<!-- Preview Modal -->
	<div class="modal fade" id="filePreviewModal" tabindex="-1" role="dialog" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="filePreviewModalLabel">Document Preview</h4>
				</div>
				<div class="modal-body text-center">
					<div id="preview-content"></div>
				</div>
				<div class="modal-footer">
					<a href="" id="preview-download-btn" target="_blank" class="btn btn-primary">Download File</a>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
@stop

@section('javascript')
	<script>
		$(document).ready(function() {
			$('.preview-btn').on('click', function(e) {
				e.preventDefault();
				var url = $(this).data('url');
				var originalName = $(this).data('name') || 'Document';
				var ext = url.split('.').pop().toLowerCase();

				$('#filePreviewModalLabel').text(originalName);
				$('#preview-download-btn').attr('href', url);

				var content = '';
				if (ext === 'pdf') {
					content = '<iframe src="' + url + '" style="width: 100%; height: 500px;" frameborder="0"></iframe>';
				} else if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].indexOf(ext) !== -1) {
					content = '<img src="' + url + '" class="img-responsive" style="margin: 0 auto; max-height: 500px;">';
				} else {
					content = '<p>Preview not available for this file type.</p>';
				}

				$('#preview-content').html(content);
				$('#filePreviewModal').modal('show');
			});
		});
	</script>

    <script>
        $(document).ready(function () {
            $('#product_table').DataTable({
                pageLength: 500,
                lengthMenu: [
                    [300, 500, 1000, -1],
                    [300, 500, 1000, "All"]
                ]
            });
        });



    </script>
@stop
