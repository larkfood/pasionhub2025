{{-- select multiple --}}
@php
	$field ??= [];
	
	$entityEntries = $field['model']::all();
	
	$fieldValue = $field['value'] ?? ($field['default'] ?? null);
	$fieldValue = old($field['name'], $fieldValue);
	
	// Convert the field value to a collection
	$fieldValue = is_string($fieldValue) ? explode(',', $fieldValue) : $fieldValue;
	$fieldValue = is_array($fieldValue) ? collect($fieldValue) : $fieldValue;
	$fieldValue = ($fieldValue instanceof \Illuminate\Support\Collection) ? $fieldValue : collect();
@endphp
<div @include('admin.panel.inc.field_wrapper_attributes') >
    <label class="form-label fw-bolder">
	    {!! $field['label'] !!}
	    @if (isset($field['required']) && $field['required'])
		    <span class="text-danger">*</span>
	    @endif
    </label>
	@include('admin.panel.fields.inc.translatable_icon')
    <select class="form-control" name="{{ $field['name'] }}[]" multiple
        @include('admin.panel.inc.field_attributes', ['default_class' => 'form-select'])
    >
    	<option value="">-</option>
		
	    @foreach ($entityEntries as $entityEntry)
			@php
				$fieldValue = $fieldValue->pluck($entityEntry->getKeyName(), $entityEntry->getKeyName())->toArray();
				$selectedAttr = in_array($entityEntry->getKey(), $fieldValue) ? ' selected' : '';
			@endphp
		    <option value="{{ $entityEntry->getKey() }}"{!! $selectedAttr !!}>
			    {{ $entityEntry->{$field['attribute']} }}
		    </option>
	    @endforeach
	</select>
	
    {{-- HINT --}}
    @if (isset($field['hint']))
        <div class="form-text">{!! $field['hint'] !!}</div>
    @endif
</div>
