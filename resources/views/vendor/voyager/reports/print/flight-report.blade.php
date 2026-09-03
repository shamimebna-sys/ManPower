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
                <th style="padding: 5px;border: 1px solid #ddd;width: 20% ">CANDIDATE NAME</th>
                <th style="padding: 5px;border: 1px solid #ddd; ">PASSPORT NO.</th>
                <th style="padding: 5px;border: 1px solid #ddd;">MOBILE NO.</th>
                <th style="padding: 5px;border: 1px solid #ddd;">COMPANY NAME</th>
                <th style="padding: 5px;border: 1px solid #ddd;">FLIGHT NAME	</th>
                <th style="padding: 5px;border: 1px solid #ddd;">FLIGHT DATE	</th>
                <th style="padding: 5px;border: 1px solid #ddd; ">DEPARTURE TIME</th>
                <th style="padding: 5px;border: 1px solid #ddd;">ARRIVAL TIME</th>
            </tr>

            </thead>
            <tbody>
            @if($data)

                @foreach($data as $s=>$i)
                        <tr style="border: 1px solid #ddd">
                            <td style="padding: 5px;border: 1px solid #ddd;">{{++$s}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd; ">{{$i->name}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->passport_no}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->mobile}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->position}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->position}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->position}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->position}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->position}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->position}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->position}}</td>
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
