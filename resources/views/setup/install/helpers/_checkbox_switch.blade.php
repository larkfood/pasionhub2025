@php
	$name ??= 'field_name';
	$classes ??= '';
	$class ??= '';
	$isInvalidClass ??= '';
	$default ??= null;
	$value ??= '';
	$checked ??= false;
	$disabled ??= false;
	
	$checkedAttr = (!(isset($errors) && $errors->any()) && $default == '1') ? ' checked' : '';
	$checkedAttr = $checked ? ' checked' : $checkedAttr;
	
	$disabledAttr = $disabled ? ' disabled="disabled"' : '';
@endphp
<div class="form-check form-switch mt-4 mb-3">
	<input type="checkbox"
		id="{{ $name }}"
		name="{{ $name }}"
		value="1"
		class="form-check-input{{ $isInvalidClass.$classes.$class }}"
		style="cursor: pointer;"
		{!! $checkedAttr.$disabledAttr !!}
	>
	<label class="form-check-label">
		@if (!empty($label))
			{!! $label !!}
		@endif
	</label>
	
	@if (isset($hint) && !empty($hint))
		<div class="form-text">{!! $hint !!}</div>
	@endif
</div>
