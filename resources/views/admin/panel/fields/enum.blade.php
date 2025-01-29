{{-- enum --}}
@php
	$field ??= [];
	
	$entityModel = !empty($xPanel) ? $xPanel->model : null;
	$isNullAllowed = is_null($entityModel) || $entityModel::isColumnNullable($field['name']);
	$possibleEnumValues = $entityModel::getPossibleEnumValues($field['name']);
	
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
        @include('admin.panel.inc.field_attributes', ['default_class' =>  'form-select'])
    >
        @if ($isNullAllowed)
            <option value="">-</option>
        @endif
		@if (count($possibleEnumValues))
			@foreach ($possibleEnumValues as $possibleValue)
		        @php
			        $selectedAttr = ($possibleValue == $fieldValue) ? ' selected' : '';
		        @endphp
				<option value="{{ $possibleValue }}"{!! $selectedAttr !!}>{{ $possibleValue }}</option>
			@endforeach
		@endif
	</select>
	
    {{-- HINT --}}
    @if (isset($field['hint']))
        <div class="form-text">{!! $field['hint'] !!}</div>
    @endif
</div>
