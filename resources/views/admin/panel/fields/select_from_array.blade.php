{{-- select --}}
@php
	$field ??= [];
	
	$field['options'] ??= [];
	$field['allows_null'] ??= false;
	
	$fieldValue = $field['value'] ?? ($field['default'] ?? null);
	$fieldValue = old($field['name'], $fieldValue);
@endphp
<div @include('admin.panel.inc.field_wrapper_attributes') >
    <label class="form-label fw-bolder">
	    {!! $field['label'] !!}
	    @if (isset($field['required']) && $field['required'])
		    <span class="text-danger">*</span>
	    @endif
    </label>
	@include('admin.panel.fields.inc.translatable_icon')
    <select name="{{ $field['name'] }}"
        @include('admin.panel.inc.field_attributes', ['default_class' => 'form-select'])
    >
        @if ($field['allows_null'])
            <option value="">-</option>
        @endif
		@if (!empty($field['options']))
			@foreach ($field['options'] as $key => $value)
		        @php
			        $selectedAttr = ($key == $fieldValue) ? ' selected' : '';
		        @endphp
				<option value="{{ $key }}"{!! $selectedAttr !!}>{{ $value }}</option>
			@endforeach
		@endif
	</select>
	
    {{-- HINT --}}
    @if (isset($field['hint']))
        <div class="form-text">{!! $field['hint'] !!}</div>
    @endif
</div>
