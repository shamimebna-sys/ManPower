@extends('vendor.voyager.reports.print.layout')
@section('title', $title)

@section('headers')
<style>
    th, td {
        font-size: 10px;
        text-align: left;
    }
</style>
@endsection

@section('content')
    @if($data && count($data) > 0)
        <table style="width: 100%; padding-top: 20px; border-collapse: collapse;">
            <thead>
            <tr style="border: 1px solid #ddd; background-color: #f5f5f5;">
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 5%;">SL.</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">RECEIPT INFO</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">INVOICE NO</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">TYPE</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 25%;">RECEIVED FROM</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 12%;">PAYMENT METHOD</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 13%;">TRANSACTION NO</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: right; width: 10%;">AMOUNT</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $s => $i)
                <tr style="border: 1px solid #ddd;">
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $s + 1 }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        <strong>No: {{ $i->receipt_no }}</strong><br>
                        <span style="color: #666; font-size: 8px;">Date: {{ $i->receipt_date ? date('Y-m-d', strtotime($i->receipt_date)) : 'N/A' }}</span>
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ optional($i->invoice)->invoice_no ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        <span style="font-weight: bold; color: {{ $i->type == 'Ticket' ? '#0066cc' : '#009933' }};">
                            {{ $i->type }}
                        </span>
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ $i->received_from ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ $i->payment_method ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ $i->transaction_no ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: right; font-weight: bold;">
                        €{{ number_format($i->amount, 2) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #999;">No data found.</p>
    @endif
@endsection
