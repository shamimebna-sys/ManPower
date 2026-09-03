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
                <th style="padding: 5px;border: 1px solid #ddd;width: 20% ">NAME</th>
                <th style="padding: 5px;border: 1px solid #ddd; ">MOBILE</th>
                <th style="padding: 5px;border: 1px solid #ddd;">PASPORT NO.</th>
                <th style="padding: 5px;border: 1px solid #ddd;">POSITION</th>
                <th style="padding: 5px;border: 1px solid #ddd;">LOCAL AGENT</th>
                <th style="padding: 5px;border: 1px solid #ddd;">SELECTION DATE</th>
                <th style="padding: 5px;border: 1px solid #ddd; ">AGENCY NAME</th>
                <th style="padding: 5px;border: 1px solid #ddd;">COMPANY NAME</th>
                <th style="padding: 5px;border: 1px solid #ddd;">VISA MP NO.</th>
                <th style="padding: 5px;border: 1px solid #ddd;">FLIGHT DATE</th>
            </tr>

            </thead>
            <tbody>
            @if($data)

                @foreach($data as $s=>$i)
                        <tr style="border: 1px solid #ddd">
                            <td style="padding: 5px;border: 1px solid #ddd;">{{++$s}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd; ">{{ucwords($i->name)}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->mobile}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->passport_no}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{ optional($i->positionRelation)->title ?? 'N/A' }}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{ optional($i->agent)->name ?? 'N/A' }}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{ $i->created_at ? $i->created_at->format('Y-m-d') : 'N/A' }}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{ optional($i->agencier)->name ?? 'N/A' }}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{ optional($i->companier)->name ?? 'N/A' }}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{ optional($i->VisaImmigration->first())->visa_mp_no ?? 'N/A' }}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{ optional($i->flightSchedules->sortByDesc('created_at')->first())->flight_date ?? 'N/A' }}</td>
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
