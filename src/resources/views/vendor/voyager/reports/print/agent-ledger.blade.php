@extends('vendor.voyager.reports.print.layout')
@section('title', $title)

@section('headers')
@endsection

@section('content')
    @if($data)
        <table style="width: 100%; margin-bottom: 5px" >
            <tr>
                <th style="text-align: center">
                    <h3>({{$data->code}}) {{ucfirst($data->name)}}, {{$data->mobile}}, {{$data->email}}, {{$data->permanent_address_house}}</h3>
                </th>
            </tr>
            <tr>
                <th style="text-align: center">
                    <span style="color: green">Total Balance ({{($data->balance < 0)?'Advance':'Due'}}):</span>   {{\App\Helpers\CommonClass::currencySymbol(true,2,abs($data->balance))}}
                </th>
            </tr>
        </table>


        <table style="width: 100%; padding-top: 20px">
            <thead>
            <tr style="border: 1px solid #ddd">
                <th style="padding: 5px;border: 1px solid #ddd; width: 5%">SL.</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 5%">DATE</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 5%">INVOICE NO.</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 30%">PARTICULARS</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">DEPOSIT</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">PAYMENT</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">BALANCE ({{\App\Helpers\CommonClass::currencySymbol(true,2,null)}})</th>
                <th style="padding: 5px;border: 1px solid #ddd; width:25% ">REMARKS</th>
            </tr>

            </thead>
            <tbody>
            @php
                $balance = 0;
            @endphp
            @if($data->payments)

                @foreach($data->payments as $s=>$i)
                    @if($i->status =='A')
                        @php
                            if ($i->type === 'CR') {
                            $balance += $i->amount/$i->exchange_rate;
                            } elseif ($i->type  === 'DR') {
                            $balance -= $i->amount/$i->exchange_rate;
                            }
                        @endphp
                        <tr style="border: 1px solid #ddd">
                            <td style="padding: 5px;border: 1px solid #ddd;">{{++$s}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{date('d-m-Y', strtotime($i->payment_date))}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->invoice_no}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->title}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd; text-align: right">{{number_format($i->amount, 2)  }}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;text-align: center">{{($i->type == 'CR')?'Credit':'Debit'}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;text-align: right">{{number_format($balance,2 )}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->remarks}}. Exchange rate: {{$i->exchange_rate}}</td>
                        </tr>
                    @endif
                @endforeach
            @endif
            </tbody>
            <tfoot>
            </tfoot>
        </table>
    @else
        <p>No date found.</p>
    @endif
@endsection
