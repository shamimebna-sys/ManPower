@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .border-white {
            border-color: white !important;;
        }
    </style>
@stop

@section('page_title', 'My Candidates')

@section('page_header')
    <h1 class="page-title"> <i class="voyager-double-right"></i> My Candidates </h1>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-bordered panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="icon wb-image"></i>Candidates
                        </h3>
                        <div class="panel-actions">
                            <a class="panel-action voyager-angle-down" data-toggle="panel-collapse"
                               aria-hidden="true"></a>
                        </div>
                    </div>
                    <div class="panel-body ">
                        <div class="table-responsive">
                            <table class="table table-hover datatable " id="product_table">
                                <thead>
                                <tr>
                                    <th style="width: 30%">Candidate</th>
                                    <th style="width: 5%">Passport</th>
                                    <th style="width: 5%">Status</th>
                                    @if(\App\Helpers\CommonClass::user()->role_id != 108)
                                    <th style="width: 10%;">Agent</th>
                                    <th style="width: 10%;">Group </th>
                                    @endif

                                    @if(\App\Helpers\CommonClass::user()->role_id != 101)
                                    <th style="width: 10%">Agency</th>
                                    <th style="width: 10%">Company</th>
                                    @endif
                                    <th style="width: 5%">ARC No.</th>
                                    <th style="width: 5%">MP/VISA</th>
                                    <th style="width: 5%">Live Stutus</th>

                                    <th style="width: 5%">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($candidates)
                                    @foreach($candidates as $s=>$i)
                                        <tr>
                                            <td>
                                                <a href="#">
                                                    <h5 style="padding:0; margin: 0"><b> ({{$i->code}}) {{$i->name}}</b></h5>
                                                </a>
                                                <p  style="padding:0; margin: 0"><b>Email:</b> {{$i->email}}</p>
                                                <p  style="padding:0; margin: 0"><b>Mobile:</b> {{$i->mobile}}</p>
                                            </td>
                                            <td>{{$i->passport_no}}</td>
                                            <td>  {!! \App\Helpers\CommonClass::getPaymentStatus($i->status)  !!}</td>
                                            @if(\App\Helpers\CommonClass::user()->role_id != 108)
                                            <td >
                                                @if(isset($i->agent) && !empty($i->agent))
                                                    ({{$i->agent->code}}) {{$i->agent->name}}
                                                @endif
                                            </td>
                                            <td>({{$i->classGroup->code}}) {{$i->classGroup->name}}</td>
                                            @endif
                                            @if(\App\Helpers\CommonClass::user()->role_id != 101)
                                            <td>
                                                @if(isset($i->agencier) && !empty($i->agencier))
                                                   {{$i->agencier->name}}
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($i->companier) && !empty($i->companier))
                                                    {{$i->companier->name}}
                                                @endif
                                            </td>
                                            @endif
                                            <td>  {!! \App\Helpers\CommonClass::arcNo($i->id) !!}</td>
                                            <td> @if(isset($i->VisaImmigration) && count($i->VisaImmigration)> 0)
                                                    {{$i->VisaImmigration[0]->visa_mp_no}}
                                                @endif</td>
                                            <td> {!! \App\Helpers\CommonClass::liveStatus($i->id) !!}</td>
                                             
                                            <td>
                                                @if($i->status == 'P')
                                                    <a href="#" onclick="openModal('{{route("admin.ajax.candidate-approval", $i->id)}}', false, 'modal-lg', 'Candidate Approval');" title="Approve" class="btn btn-sm btn-success pull-right ">
                                                        <i class="voyager-add"></i> <span class="hidden-xs hidden-sm">Approval</span>
                                                    </a>
                                                @endif
                                                    <a href="{{route("admin.my.candidate.edit", $i->id)}}" title="Edit" class="btn btn-sm btn-primary pull-right ">
                                                        <i class="voyager-add"></i> <span class="hidden-xs hidden-sm">Edit</span>
                                                    </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>


                </div>
            </div>

        </div>
    </div>

@stop

@section('javascript')

@stop
