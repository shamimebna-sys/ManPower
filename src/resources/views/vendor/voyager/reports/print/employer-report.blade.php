@extends('vendor.voyager.reports.print.layout')
@section('title', $title)

@section('headers')
@endsection

@section('content')
    @if($data)
        <table style="width: 100%; padding-top: 20px">
            <thead>
            <tr style="border: 1px solid #ddd">
                <th style="padding: 5px;border: 1px solid #ddd; ">SL.</th>
                <th style="padding: 5px;border: 1px solid #ddd;width: 20% ">APPLICANT</th>
                <th style="padding: 5px;border: 1px solid #ddd; ">PASSPORT NO.</th>
                <th style="padding: 5px;border: 1px solid #ddd;">MOBILE NO.</th>
                <th style="padding: 5px;border: 1px solid #ddd;">STAGE</th>
                <th style="padding: 5px;border: 1px solid #ddd;">POSITION</th>
                <th style="padding: 5px;border: 1px solid #ddd;">AGENCY</th>
                <th style="padding: 5px;border: 1px solid #ddd; ">RESUME</th>
                <th style="padding: 5px;border: 1px solid #ddd;">EMPLOYER</th>
                <th style="padding: 5px;border: 1px solid #ddd;">SCORE</th>
                <th style="padding: 5px;border: 1px solid #ddd;">NOTES</th>
            </tr>

            </thead>
            <tbody>
            @if($data)

                @foreach($data as $s=>$i)
                        <tr style="border: 1px solid #ddd">
                            <td style="padding: 5px;border: 1px solid #ddd;">{{++$s}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd; ">{{ucwords($i->name)}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->passport_no}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->mobile}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{optional($i->classGroup)->name ?? 'N/A'}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{optional($i->positionRelation)->title ?? 'N/A'}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{optional($i->agencier)->name ?? 'N/A'}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->cv_file_path ? 'Yes' : 'No'}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{optional($i->companier)->name ?? 'N/A'}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{ optional($i->latestExamResultAny)->result ?? 'N/A' }} (S:{{ optional($i->latestExamResultAny)->skill ?? '-' }} E:{{ optional($i->latestExamResultAny)->english ?? '-' }} B:{{ optional($i->latestExamResultAny)->bl ?? '-' }})</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->remarks ?? 'N/A'}}</td>
                        </tr>
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
