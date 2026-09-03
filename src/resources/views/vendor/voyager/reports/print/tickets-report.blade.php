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
                <th style="padding: 6px; border: 1px solid #ddd; width: 12%;">INVOICE INFO</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">FLIGHT INFO</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 12%;">ROUTE</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 18%;">CANDIDATE</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">COMPANY</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 13%;">AGENCY</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: right; width: 5%;">TOTAL</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 5%;">STATUS</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $s => $i)
                <tr style="border: 1px solid #ddd;">
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $s + 1 }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        <strong>No: {{ $i->invoice_no }}</strong><br>
                        <span style="color: #666; font-size: 8px;">Date: {{ $i->invoice_date }}</span>
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        Air: {{ $i->airline ?? 'N/A' }}<br>
                        Tkt: {{ $i->ticket_no ?? 'N/A' }}<br>
                        Flight: {{ $i->flight_date ?? 'N/A' }} {{ $i->flight_time ?? '' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        From: {{ $i->route_from ?? 'N/A' }}<br>
                        To: {{ $i->route_to ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        @if($i->candidate)
                            <strong>{{ ucwords($i->candidate->name) }}</strong><br>
                            <span style="color: #666; font-size: 8px;">Code: {{ $i->candidate->code }} | PP: {{ $i->candidate->passport_no }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ optional($i->companier)->name ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ optional($i->agencier)->name ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: right; font-weight: bold;">
                        €{{ number_format($i->total, 2) }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">
                        <span style="font-weight: bold;">{{ strtoupper($i->status) }}</span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #999;">No data found.</p>
    @endif
@endsection
