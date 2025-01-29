@php
	$name ??= 'field_name';
	$classes ??= '';
	$class ??= '';
	$isInvalidClass ??= '';
	
	$placeholder ??= '';
	$value ??= '';
	$value = old($name, $value);
	$disabled ??= false;
	
	$disabledAttr = $disabled ? ' disabled="disabled"' : '';
@endphp
<input name="{{ $name }}"
       id="{{ $name }}"
       placeholder="{{ $placeholder }}"
       value="{{ $value }}"
       type="text"
       class="form-control{{ $isInvalidClass.$classes.$class }}"
		{!! $disabledAttr !!}
>
