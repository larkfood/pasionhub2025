@php
	$name ??= 'field_name';
	$classes ??= '';
	$isInvalidClass ??= '';
	
	$placeholder ??= '';
	$disabled ??= false;
	$value ??= '';
	$value = old($name, $value);
	
	$disabledAttr = $disabled ? ' disabled="disabled"' : '';
@endphp
<input name="{{ $name }}"
       id="{{ $name }}"
       placeholder="{{ $placeholder }}"
       value="{{ $value }}"
       type="text"
       class="form-control number numeric{{ $isInvalidClass.$classes }}"
		{{ $disabledAttr }}
>
