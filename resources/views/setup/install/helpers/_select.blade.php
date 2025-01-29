@php
	$name ??= 'field_name';
	$classes ??= '';
	$class ??= '';
	$isInvalidClass ??= '';
	
	$multiple ??= false;
	$value ??= '';
	$value = old($name, $value);
	
	$multipleAttr = (isset($multiple) && $multiple) ? ' multiple="multiple"' : '';
	
@endphp
<select name="{{ $name }}" class="form-select select-search selecter{{ $isInvalidClass.$classes.$class }}"{!! $multipleAttr !!}>
	@if (isset($include_blank))
		<option value="">{{ $include_blank }}</option>
	@endif
	@foreach($options as $option)
		@php
			$optionValue = $option['value'] ?? '';
			$optionLabel = $option['text'] ?? '';
			$selectedAttr = in_array($optionValue, explode(',', $value)) ? ' selected' : '';
		@endphp
		<option value="{{ $optionValue }}"{!! $selectedAttr !!}>{{ $optionLabel }}</option>
	@endforeach
</select>
