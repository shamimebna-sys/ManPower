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
                    <th style="padding: 6px; border: 1px solid #ddd; width: 5%;">SL.</th>
                    <th style="padding: 6px; border: 1px solid #ddd; width: 20%;">Candidate</th>
                    <th style="padding: 6px; border: 1px solid #ddd; width: 20%;">Exam</th>
                    <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">Group</th>
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 8%;">Abroad Exp</th>
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 8%;">Local Exp</th>
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 6%;">BL</th>
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 6%;">Skill</th>
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 6%;">English</th>
                    <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 8%;">Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                    <tr style="border: 1px solid #ddd;">
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $index + 1 }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd;">
                            @if($item->candidate)
                                <strong>{{ ucwords($item->candidate->name) }}</strong><br/>
                                <span style="color: #555; font-size: 9px;">Passport: {{ $item->candidate->passport_no ?? 'N/A' }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td style="padding: 6px; border: 1px solid #ddd;">
                            {{ optional($item->exam)->name ?? 'N/A' }}
                        </td>
                        <td style="padding: 6px; border: 1px solid #ddd;">
                            {{ optional($item->classGroup)->group_info ?? 'N/A' }}
                        </td>
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $item->abroad_ex }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $item->local_ex }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $item->bl }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $item->skill }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $item->english }}</td>
                        <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">
                            @if($item->result == 'PASS')
                                <span style="color: green; font-weight: bold;">PASS</span>
                            @elseif($item->result == 'FAIL')
                                <span style="color: red; font-weight: bold;">FAIL</span>
                            @else
                                <span style="color: #777;">Pending</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; font-size: 14px; color: #777;">No exam results found.</p>
    @endif
@endsection
