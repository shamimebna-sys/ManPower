<input type="url"
       class="form-control"
       name="{{ $row->field }}"
       data-name="{{ $row->display_name }}"
       @if(isset($row->details->validation->rule) && isset($row->details->validation->rule) == "required") required @endif
       step="any"
       onblur="this.value=this.value && !/^https?:\/\//i.test(this.value)? 'http://'+this.value : this.value"
       placeholder="{{ isset($options->placeholder)? old($row->field, $options->placeholder): $row->display_name }}"
       value="@if(isset($dataTypeContent->{$row->field})){{ old($row->field, $dataTypeContent->{$row->field}) }}@else{{old($row->field)}}@endif">

