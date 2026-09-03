@extends('vendor.voyager.reports.print.layout')
@section('title', $title)

@section('headers')
@endsection

@section('content')
    @if($data)
        <table style="width: 100%; padding-top: 20px">
            <thead>
            <tr style="border: 1px solid #ddd">
                <th style="padding: 5px;border: 1px solid #ddd; width: 5%">SL.</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 8%">Invoice No.</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 15%">Date</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 20%">Customer</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">Type</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">Subtotal ({{\App\Helpers\CommonClass::currencySymbol(false)}})</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">Discount ({{\App\Helpers\CommonClass::currencySymbol(false)}})</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">Total ({{\App\Helpers\CommonClass::currencySymbol(false)}})</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">Paid ({{\App\Helpers\CommonClass::currencySymbol(false)}})</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 10%">Due ({{\App\Helpers\CommonClass::currencySymbol(false)}})</th>
            </tr>

            </thead>
            <tbody>
            @php
            $totalDiscount = 0;
            $totalAmount = 0;
            $totalPaid = 0;
            $totalDue = 0;
            @endphp
                @foreach($data as $s=>$i)
                    <tr style="border: 1px solid #ddd">
                        <td style="padding: 5px;border: 1px solid #ddd;">{{++$s}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->invoice_no}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{date('d-m-Y', strtotime($i->sales_date))}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->customer->name}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{($i->type == 'NEW')?'New Sales':'Sales Return'}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->sub_total}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->discount}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->total}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->paid}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->due}}</td>
                    </tr>

                    @php
                        $totalDiscount = $totalDiscount + $i->discount;
                        $totalAmount = $totalAmount + $i->total;
                        $totalPaid = $totalPaid +$i->paid;
                        $totalDue = $totalDue + $i->due;
                    @endphp
                @endforeach
            </tbody>
            <tfoot>
            <tr style="border: 1px solid #ddd">
                <th style="text-align: right" colspan="6">Total:</th>
                <th style="padding: 5px;text-align: left">{{$totalDiscount}}</th>
                <th style="padding: 5px;text-align: left">{{$totalAmount}}</th>
                <th style="padding: 5px;text-align: left">{{$totalPaid}}</th>
                <th style="padding: 5px;text-align: left">{{$totalDue}}</th>
            </tr>
            </tfoot>
        </table>
    @else
        <p>No date found.</p>
    @endif
@endsection
