@php
    $dataTypeRows = $dataType->{($edit ? 'editRows' : 'addRows' )};
@endphp

@foreach($dataTypeRows as $row)
    @if(!in_array($row->field, $fields) && $formField == 'exclude')
        @php
            $display_options = $row->details->display ?? NULL;

            if(empty($width)){
                if(isset($display_options->width)){
                    $width = 'col-md-' . $display_options->width;
                }
            }else{
                 $width = 'col-md-' . $width;
            }
        @endphp
        @if (isset($row->details->formfields_custom))
            @include('voyager::formfields.custom.' . $row->details->formfields_custom)
        @else
            <div class="form-group @if($row->type == 'hidden' || (isset($isHidden) && $isHidden)) hidden @endif {{$width}}" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                {{ $row->slugify }}
                <label for="name">{{ $row->getTranslatedAttribute('display_name') }}
                    @php
                        if(isset($row->details->validation->rule)){
                               $required = isset($row->details->validation->rule);
                        }else{
                            $required = isset($required)?$required:'';
                        }
                    @endphp
                    @if($required)
                        <strong style="color: red;">*</strong>
                    @endif
                </label>
                @include('voyager::multilingual.input-hidden-bread-edit-add')
                @if($row->type == 'relationship')
                    @include('voyager::formfields.relationship', ['options' => $row->details])
                @else
                    {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                @endif

                @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                    {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                @endforeach
            </div>
        @endif
    @endif

    @if(in_array($row->field, $fields) && $formField == 'include')

        @php
            $display_options = $row->details->display ?? NULL;
            if(empty($width)){
                if(isset($display_options->width)){
                    $width = 'col-md-' . $display_options->width;
                }
            }else{
                 $width = 'col-md-' . $width;
            }

        @endphp
        @if (isset($row->details->formfields_custom))
            @include('voyager::formfields.custom.' . $row->details->formfields_custom)

        @else
            <div class="form-group @if($row->type == 'hidden'  || (isset($isHidden) && $isHidden)) hidden @endif {{$width}}" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                {{ $row->slugify }}
                <label for="name">{{ $row->getTranslatedAttribute('display_name') }}

                    @php
                        if(isset($row->details->validation->rule)){
                               $required = isset($row->details->validation->rule);
                        }else{
                            $required = isset($required)?$required:'';
                        }
                    @endphp
                    @if($required=='required'  )
                        <strong style="color: red;">*</strong>
                    @endif
                </label>
                @include('voyager::multilingual.input-hidden-bread-edit-add')
                @if($row->type == 'relationship')
                    @include('voyager::formfields.relationship', ['options' => $row->details])

                @else
                    {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}

                @endif

                @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                    {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                @endforeach
            </div>
        @endif
    @endif


@endforeach
