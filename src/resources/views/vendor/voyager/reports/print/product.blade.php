@extends('vendor.voyager.reports.print.layout')
@section('title', $title)

@section('headers')
@endsection

@section('content')
    @if($data)
        <table style="width: 100%; padding-top: 20px">
            <thead>
            <tr style="border: 1px solid #ddd">
                <th style="padding: 5px;border: 1px solid #ddd; width: 5%">SL.</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 8%">Name</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 15%">Unit</th>
                <th style="padding: 5px;border: 1px solid #ddd; width: 15%">Price ({{\App\Helpers\CommonClass::currencySymbol(false)}})</th>
            </tr>

            </thead>
            <tbody>
                @foreach($data as $s=>$i)
                    <tr style="border: 1px solid #ddd">
                        <td style="padding: 5px;border: 1px solid #ddd;">{{++$s}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->name}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->unit->name}}</td>
                        <td style="padding: 5px;border: 1px solid #ddd;">{{$i->price}}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
            </tfoot>
        </table>
    @else
        <p>No date found.</p>
    @endif
@endsection
