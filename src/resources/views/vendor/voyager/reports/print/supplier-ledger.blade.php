@extends('vendor.voyager.reports.print.layout')
@section('title', $title)

@section('headers')
@endsection

@section('content')
    @if($data)
        <table style="width: 100%; margin-bottom: 5px" >
            <tr>
                <th style="text-align: center">
                    <h3>{{ucfirst($data->name)}}, {{$data->mobile}}, {{$data->email}}, {{$data->office_address}}</h3>
                </th>
            </tr>
            <tr>
                <th style="text-align: center">
                    <span style="color: green">Total Purchase:</span>  {{$data->purchase}}{{\App\Helpers\CommonClass::currencySymbol(false)}}
                    <span style="color: blue">Total Paid:</span> {{abs($data->paid)}}{{\App\Helpers\CommonClass::currencySymbol(false)}}
                    <span style="color: red">Total {{($data->due < 0)?'Advance':'Due'}}:</span> {{abs($data->due)}}{{\App\Helpers\CommonClass::currencySymbol(false)}}
                </th>
            </tr>
        </table>


        <table style="width: 100%; padding-top: 20px">
            <thead>
            <tr style="border: 1px solid #ddd">
                <th style="padding: 5px;border: 1px solid #ddd; width: 5%">SL.</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 8%">Invoice No.</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 15%">Transaction Date</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 20%">Title</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">Type</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">Amount ({{\App\Helpers\CommonClass::currencySymbol(false)}})</th>
                <th style="padding: 5px;border: 1px solid #ddd; width:25% ">Details</th>
                <th style="padding: 5px;border: 1px solid #ddd; width:10% ">Created At</th>
            </tr>

            </thead>
            <tbody>
            @if($data->ledger)
                @foreach($data->ledger as $s=>$i)
                    <tr style="border: 1px solid #ddd">
                        <td style="padding: 5px;border: 1px solid #ddd;">{{++$s}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->invoice_no}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{date('d-m-Y', strtotime($i->payment_date))}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->title}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{($i->type == 'IN')?'Credit':'Debit'}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->amount}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">
                            @if($i->bank_name)<p style="padding-top: 0; margin: 0">
                                Bank: {{$i->bank_name}}</p>@endif
                            @if($i->acc_no)<p style="padding-top: 0; margin: 0">
                                A/C: {{$i->acc_no}} </p>@endif
                            @if($i->trx_no)<p style="padding-top: 0; margin: 0">Trx
                                No.: {{$i->trx_no}}</p>@endif
                        </td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{date('d-m-Y h:m A', strtotime($i->created_at))}}</td>
                    </tr>
                @endforeach
            @endif

{{--            @foreach($data as $d)
                <tr>
                    <td  style="padding: 0px; border: 1px solid #ddd;">
--}}{{--                        {!! QrCode::size(100)->generate('RemoteStack') !!}--}}{{--
                    </td>
                    <td  style="font-size: small; padding: 3px;border: 1px solid #ddd">{{$d->name}}</td>
                </tr>
            @endforeach--}}
            </tbody>
            <tfoot>
            </tfoot>
        </table>
    @else
        <p>No date found.</p>
    @endif
@endsection
