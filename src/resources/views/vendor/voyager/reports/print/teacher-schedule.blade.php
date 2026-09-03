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
                <th style="padding: 6px; border: 1px solid #ddd; width: 25%;">TEACHER NAME & CODE</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 20%;">CLASS GROUP</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">SUBJECT</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">WEEK DAY</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 10%;">START TIME</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 10%;">END TIME</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $s => $i)
                <tr style="border: 1px solid #ddd;">
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $s + 1 }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        @if($i->teacher)
                            <strong>{{ ucwords($i->teacher->name) }}</strong><br>
                            <span style="color: #666; font-size: 8px;">Code: {{ $i->teacher->code }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ optional($i->classGroupRelation)->group_info ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ $i->subject ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ $i->week_day ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $i->start_time ?? 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $i->end_time ?? 'N/A' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #999;">No data found.</p>
    @endif
@endsection
