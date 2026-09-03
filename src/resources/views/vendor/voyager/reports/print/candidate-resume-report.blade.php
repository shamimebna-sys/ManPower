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
                <th style="padding: 6px; border: 1px solid #ddd; width: 25%;">APPLICANT (NAME & CODE)</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">PASSPORT NO.</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 10%;">RESUME / CV</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">DRIVE LINK</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 10%;">ABROAD EXP</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 10%;">LOCAL EXP</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">REMARKS</th>
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
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">
                        @if($i->cv_file_path)
                            <span style="color: green; font-weight: bold;">Yes</span>
                        @else
                            <span style="color: red;">No</span>
                        @endif
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        @if($i->drive_link)
                            <a href="{{ $i->drive_link }}" target="_blank" style="font-size: 8px; word-break: break-all;">Link</a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $i->abroad_ex ? $i->abroad_ex . ' Yrs' : 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $i->local_ex ? $i->local_ex . ' Yrs' : 'N/A' }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">{{ $i->remarks ?? 'N/A' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #999;">No resumes found.</p>
    @endif
@endsection
