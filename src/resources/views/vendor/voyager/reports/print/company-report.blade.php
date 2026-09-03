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
                <th style="padding: 6px; border: 1px solid #ddd; width: 25%;">COMPANY INFO</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 20%;">ADDRESS</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">CONTACT</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 15%;">OWNER</th>
                <th style="padding: 6px; border: 1px solid #ddd; width: 10%;">COUNTRY</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: right; width: 5%;">BALANCE</th>
                <th style="padding: 6px; border: 1px solid #ddd; text-align: center; width: 5%;">STATUS</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $s => $i)
                <tr style="border: 1px solid #ddd;">
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: center;">{{ $s + 1 }}</td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        <strong>{{ ucwords($i->name) }}</strong><br>
                        <span style="color: #666; font-size: 8px;">Code: {{ $i->code ?? 'N/A' }} | VAT: {{ $i->vat_no ?? 'N/A' }} | Lic: {{ $i->license_no ?? 'N/A' }}</span>
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ $i->address ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        Email: {{ $i->email ?? 'N/A' }}<br>
                        Mob: {{ $i->mobile ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        Name: {{ $i->owner_name ?? 'N/A' }}<br>
                        Mob: {{ $i->owner_mobile ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd;">
                        {{ optional($i->country)->name ?? 'N/A' }}
                    </td>
                    <td style="padding: 6px; border: 1px solid #ddd; text-align: right;">
                        €{{ number_format($i->balance, 2) }}
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
