{{-- select --}}
@php
	$field ??= [];
	
	$field['fake'] ??= false;
	$field['allows_null'] ??= false;
	
	$entityModel = !empty($xPanel) ? $xPanel->model : null;
	$isNullAllowed = is_null($entityModel) || $entityModel::isColumnNullable($field['name']);
	
	$entityEntries = $field['model']::all();
	
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
		@if (!$field['fake'])
			@if ($isNullAllowed)
				<option value="">-</option>
			@endif
		@else
			@if ($field['allows_null'])
				<option value="">-</option>
			@endif
		@endif
		
		@foreach ($entityEntries as $entityEntry)
			@php
				$selectedAttr = ($entityEntry->getKey() == $fieldValue) ? ' selected' : '';
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
