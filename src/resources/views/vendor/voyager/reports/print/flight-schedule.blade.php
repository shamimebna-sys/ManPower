@extends('vendor.voyager.reports.print.layout')
@section('title', $title)

@section('headers')
<style>
    th, td {
        font-size: 9px;
        text-align: left;
    }
</style>
@endsection

@section('content')
    @if($data && count($data) > 0)
        <table style="width: 100%; padding-top: 20px; border-collapse: collapse;">
            <thead>
                <tr style="border: 1px solid #ddd; background-color: #f5f5f5;">
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 4%;">SL.</th>
                    <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">CANDIDATE NAME</th>
                    <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">PASSPORT NO.</th>
                    <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">MOBILE NO.</th>
                    <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">AGENT</th>
                    <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">AGENCY NAME</th>
                    <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">COMPANY NAME</th>
                    <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">FLIGHT NAME</th>
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 7%;">FLIGHT DATE</th>
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 7%;">DEPARTURE</th>
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 7%;">ARRIVAL</th>
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 10%;">PAYMENT STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                    @php
                        $latestFlight = $item->flightSchedules->sortByDesc('created_at')->first();
                    @endphp
                    <tr style="border: 1px solid #ddd;">
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $index + 1 }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd;">
                            <strong>{{ ucwords($item->name) }}</strong><br>
                            <span style="color: #666; font-size: 8px;">Code: {{ $item->code }}</span>
                        </td>
                        <td style="padding: 6px; border: 1px solid #ddd;">{{ $item->passport_no ?? 'N/A' }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd;">{{ $item->mobile ?? 'N/A' }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd;">
                            @if($item->agent)
                                ({{ $item->agent->code }}) {{ $item->agent->name }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td style="padding: 6px; border: 1px solid #ddd;">{{ optional($item->agencier)->name ?? 'N/A' }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd;">{{ optional($item->companier)->name ?? 'N/A' }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd;">{{ optional($latestFlight)->airlinece_name ?? 'N/A' }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ optional($latestFlight)->flight_date ?? 'N/A' }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ optional($latestFlight)->departure_time ?? 'N/A' }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ optional($latestFlight)->arrival_time ?? 'N/A' }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">
                            {!! \App\Helpers\CommonClass::mpVisaAndPaymentStatus($item->id) !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #999;">No data found.</p>
    @endif
@endsection
