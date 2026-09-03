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
                <th style="padding: 6px; border: 1px solid #ddd; width: 30%;">CANDIDATE NAME & CODE</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 20%;">PASSPORT NO</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">VISA MP NO</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 12%;">ISSUE DATE</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 12%;">EXPIRE DATE</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 6%;">STATUS</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $s => $i)
                <tr style="border: 1px solid #ddd;">
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $s + 1 }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        @if($i->candidate)
                            <strong>{{ ucwords($i->candidate->name) }}</strong><br>
                            <span style="color: #666; font-size: 8px;">Code: {{ $i->candidate->code }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ optional($i->candidate)->passport_no ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ $i->visa_mp_no ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">
                        {{ $i->issue_date ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">
                        {{ $i->expire_date ?? 'N/A' }}
                    </td>
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
        <p style="text-align: center; padding: 20px; color: #999;">No data found.</p>
    @endif
@endsection
