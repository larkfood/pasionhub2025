@php
	$name ??= 'field_name';
	$classes ??= '';
	$isInvalidClass ??= '';
	
	$placeholder ??= '';
	$disabled ??= false;
	$required ??= false;
	$value ??= '';
	$value = old($name, $value);
	
	$requiredClass = $required ? ' required' : '';
	$disabledAttr = $disabled ? ' disabled="disabled"' : '';
@endphp
<input name="{{ $name }}"
       id="{{ $name }}"
       placeholder="{{ $placeholder }}"
       value="{{ $value }}"
       type="file"
       class="file-styled-primary{{ $isInvalidClass.$classes.$requiredClass }}"
		{!! $disabledAttr !!}
>
