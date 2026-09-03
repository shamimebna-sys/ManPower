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
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">NAME & CODE</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">PASSPORT NO.</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">MOBILE NO.</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 7%;">GENDER</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 12%;">STAGE (GROUP)</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 12%;">POSITION</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">AGENT</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">AGENCY</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">COMPANY</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 5%;">STATUS</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $s => $i)
                <tr style="border: 1px solid #ddd;">
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $s + 1 }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        <strong>{{ ucwords($i->name) }}</strong><br>
                        <span style="color: #666; font-size: 8px;">Code: {{ $i->code }}</span>
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ $i->passport_no ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ $i->mobile ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ $i->gender ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ optional($i->classGroup)->name ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ optional($i->positionRelation)->title ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ optional($i->agent)->name ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ optional($i->agencier)->name ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ optional($i->companier)->name ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">
                        @if($i->status == 'A')
                            <span style="color: green; font-weight: bold;">Active</span>
                        @else
                            <span style="color: red; font-weight: bold;">Inactive</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #999;">No candidates found.</p>
    @endif
@endsection
