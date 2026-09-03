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

@section('page_title', 'My Wallet')

@section('page_header')
    <h1 class="page-title"> <i class="voyager-double-right"></i> My Wallet </h1>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <form role="form"
                  class="form-edit-add"
                  action="{{ route('admin.my.wallet.store') }}"
                  method="POST" enctype="multipart/form-data"
                  id="order-form"
            >
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
                            <div class="form-group  col-md-3">
                                <label for="name">Invoice No.</label>
                                <input type="text" readonly class="form-control" name="invoice_no"
                                       placeholder="Order No." value="{{\App\Helpers\CommonClass::invoiceNo()}}">
                            </div>

                            <div class="form-group  col-md-3">
                                <label for="name">Amount
                                </label>
                                <input type="number" class="form-control" name="amount" step="any" placeholder="Amount" value="{{old('amount', 0)}}">
                            </div>
                            <div class="  form-group  col-md-3">
                                <label for="name" style="float: left">Currency</label>
                                <select
                                    class="select2" name="currency_id"
                                    id="currency_id" required
                                >
                                    @foreach($currencies as $i)
                                        <option  {{old('currency_id', '') == $i->id?'selected':'' }} value="{{$i->id}}">{{$i->short_name}} ({{$i->symbol}})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="  form-group  col-md-3">
                                <label for="name" style="float: left">Payment Method</label>
                                <select  name="payment_method"  class="select2"
                                         id="payment_method" required
                                >
                                    <option {{old('payment_method', 'ONLINE') =='ONLINE'?'selected':'' }} value="ONLINE">Online Transaction</option>
                                    <option  {{old('payment_method', 'CHEQUE') =='CHEQUE'?'selected':'' }}  value="CHEQUE">Cheque</option>
                                    <option  {{old('payment_method', 'CASH') =='CASH'?'selected':'' }}  value="CASH">Cash Payment</option>
                                </select>
                            </div>

                            <div class="form-group  col-md-3">
                                <label for="acc_no">A/C No.
                                </label>
                                <input type="text" class="form-control" name="acc_no" placeholder="Acc No" value="{{old('acc_no', '')}}">
                            </div>

                            <div class="form-group  col-md-3">
                                <label for="account_name">A/C Name
                                </label>
                                <input type="text"  class="form-control" name="account_name" placeholder="A/C No." value="{{old('account_name', '')}}">
                            </div>

                            <div class="form-group  col-md-3">
                                <label for="name">Bank Name
                                </label>
                                <input type="text" class="form-control" name="bank_name" placeholder="Bank Name" value="{{old('bank_name', '')}}">
                            </div>


                            <div class="form-group  col-md-3">
                                <label for="name">Trx No
                                </label>
                                <input type="number" class="form-control" name="trx_no" step="any" placeholder="Trx No" value="{{old('trx_no', '')}}">
                            </div>

                            <div class="form-group  col-md-3">
                                <label for="name">Payment Date
                                </label>
                                <input type="date" class="form-control" name="payment_date" placeholder="Payment Date" value="{{old('payment_date', '')}}" max="{{date('Y-m-d')}}">
                            </div>

                            <div class="form-group  col-md-3">
                                <label for="name">Payment Time
                                </label>
                                <input type="time" class="form-control" name="payment_time" placeholder="Payment Time" value="{{old('payment_time', '')}}">
                            </div>

                            <div class="form-group  col-md-3">
                                <label for="name">Payment Receiver
                                </label>
                                <input type="text" class="form-control" name="payment_receiver" placeholder="Payment Receiver" value="{{old('payment_receiver', '')}}">
                            </div>

                            <div class="form-group  col-md-3">
                                <label for="name">Payment Received Location
                                </label>
                                <input type="text" class="form-control" name="payment_received_location" placeholder="Payment Received Location" value="{{old('payment_received_location', '')}}">
                            </div>

                            <div class="form-group  col-md-6">
                                <label for="name">Payment Document (Cheque)
                                </label>
                                <input  type="file" name="cheque_file_path"  value="{{old('cheque_file_path', '')}}">
                            </div>

                            <div class="form-group  col-md-6">
                                <label for="name">Description  </label>
                                <textarea rows="3" class="form-control" name="description" placeholder="Description">{{old('description', '')}}</textarea>
                            </div>

                        </div>
                        <div class="panel-footer">
                            @section('submit-buttons')
                                <button type="submit" id="btn-submit"
                                        class="btn btn-primary save">Save</button>
                            @stop
                            @yield('submit-buttons')
                        </div>
                    </div>
                </div>

 

                <div class="col-md-12">
                    <div class="panel panel-bordered panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <i class="icon wb-image"></i>Payment Ledger

                                <span style="float: right; padding-right: inherit;">
									 Current Balance: {{\App\Helpers\CommonClass::currencySymbol(true,2,abs($info->balance))}}
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
                                        <th style="width: 25%">Transaction</th>
                                        <th style="width: 20%">Description</th>
                                        <th style="width: 23%">Amount</th>
                                        <th style="width: 10%">Payment Doc.</th>
                                        <th style="width: 10%">Status</th>
                                        {{-- 										<th style="width: 5%">Action</th>--}}
                                        <th style="width: 10%">Balance</th>
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
                                                <td>
                                                    {{ $i->description }}
                                                </td>
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
                                                        @php
                                                            $filePath = $i->cheque_file_path;
                                                            $fileJson = json_decode($filePath, true);
                                                            if (is_array($fileJson) && isset($fileJson[0]['download_link'])) {
                                                                $filePath = $fileJson[0]['download_link'];
                                                            }
                                                            $filePath = str_replace('\\', '/', $filePath);
                                                        @endphp
                                                        <a href="#" class="btn btn-xs btn-info preview-btn" data-url="{{Voyager::image($filePath)}}" data-name="{{ isset($fileJson[0]['original_name']) ? $fileJson[0]['original_name'] : 'Payment Document' }}">
                                                           Preview
                                                        </a>
                                                        &nbsp;
                                                        <a target="_blank" href="{{Voyager::image($filePath)}}" class="btn btn-xs btn-primary">
                                                           Download
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>  {!! \App\Helpers\CommonClass::getPaymentStatus($i->status)  !!}</td>
                                            {{--<td>
                                                   <a href="javascript:;" title="Delete" class="btn btn-sm btn-danger pull-right delete">
                                                       <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Delete</span>
                                                   </a>
                                               </td>
                                           </tr>--}}
                                                <td>{{\App\Helpers\CommonClass::currencySymbol(true,2,abs($i->balance))}}</td>
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
@stop
