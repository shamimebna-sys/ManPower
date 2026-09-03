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
                <th style="padding: 5px;border: 1px solid #ddd;">TOTAL CANDIDATE</th>
                <th style="padding: 5px;border: 1px solid #ddd;">ADMISSION CAND.</th>
                <th style="padding: 5px;border: 1px solid #ddd;">FINAL GROUP CAND.</th>
                <th style="padding: 5px;border: 1px solid #ddd; ">SELECTED CAND.</th>
                <th style="padding: 5px;border: 1px solid #ddd;">VISA UPDATED CAND.</th>
                <th style="padding: 5px;border: 1px solid #ddd;">PAYMENT DUE CAND.</th>
                <th style="padding: 5px;border: 1px solid #ddd;">BALANCE</th>
                <th style="padding: 5px;border: 1px solid #ddd;">DUE BALANCE</th>
            </tr>

            </thead>
            <tbody>
            @if($data)

                @foreach($data as $s=>$i)
                        <tr style="border: 1px solid #ddd">
                            <td style="padding: 5px;border: 1px solid #ddd;">{{++$s}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd; ">{{ucwords($i->name)}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->mobile}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->total_candidate}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->admission_cand}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->final_group}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->selected_cand}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->visa_updated_cand}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->payment_due_cand}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->balance}}</td>
                            <td style="padding: 5px;border: 1px solid #ddd;">{{$i->due_balance}}</td>
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
