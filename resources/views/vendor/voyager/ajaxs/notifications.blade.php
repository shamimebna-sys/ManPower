{{--@extends('voyager::master')

@section('content')--}}
<style>
    #myModal .modal-body{
        color: black;
    }

</style>
<div class="table-responsive text-center">
    @if(count($data)> 0)
        <table class="table table-invoice" style="color: black">
            <tbody id="items">
            @foreach($data as $i=>$v)
                <tr>
                    <td class="text-left">{{++$i}}. <a href="{{route('voyager.notifications.show', $v->id)}}"> {{$v->title}}</a> </td>
{{--                    <td class="text-right"> {{$v->created_at}}  </td>--}}
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <h5>No new notification found</h5>
    @endif
       {{-- <a href="{{route('voyager.notifications.index')}}" class="btn btn-block btn-dark">
            <span class="hidden-xs hidden-sm">Read All Notifications</span>
        </a>--}}
</div><!-- table-responsive -->

{{--@stop--}}
