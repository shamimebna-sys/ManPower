@extends('vendor.voyager.reports.print.layout')
@section('title', $title)

@section('headers')
@endsection

@section('content')
    @if($data)

        <table style="width: 100%; padding-top: 20px">
            <thead>
            <tr style="border: 1px solid #ddd">
                <th  style="padding: 5px;border: 1px solid #ddd">Image</th>
                <th  style="padding: 5px;border: 1px solid #ddd">Product</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $d)
                <tr>
                    <td  style="padding: 0px; border: 1px solid #ddd;">
{{--                        {!! QrCode::size(100)->generate('RemoteStack') !!}--}}
                    </td>
                    <td  style="font-size: small; padding: 3px;border: 1px solid #ddd">{{$d->name}}</td>
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
