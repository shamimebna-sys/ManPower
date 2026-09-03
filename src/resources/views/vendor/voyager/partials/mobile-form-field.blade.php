<input type="text"
       class="form-control"
       name="{{ $row->field }}"
       data-name="{{ $row->display_name }}"
       @if(isset($row->details->validation->rule) && isset($row->details->validation->rule) == "required") required @endif
       step="any"
       maxlength="14"
{{--       oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..?)\../g, '$1').replace('\.', '');"--}}

       oninput="this.value = this.value
           .replace(/(?!^\+)[^\d]/g, '')     // allow only digits, + only at start
           .replace(/^(\+?\d{0,13}).*$/, '$1');"
       placeholder="{{ isset($options->placeholder)? old($row->field, $options->placeholder): $row->display_name }}"
       value="@if(isset($dataTypeContent->{$row->field})){{ old($row->field, $dataTypeContent->{$row->field}) }}@else{{old($row->field)}}@endif">

{{--<input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..?)\../g, '$1');" />--}}



