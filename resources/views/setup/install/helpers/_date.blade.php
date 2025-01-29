@php
	$name ??= 'field_name';
	$classes ??= '';
	$class ??= '';
	$isInvalidClass ??= '';
	
	$placeholder ??= '';
    $disabled ??= false;
	$value ??= '';
	$value = old($name, $value);
    
    $disabledAttr = (isset($disabled) && $disabled) ? ' disabled="disabled"' : '';
@endphp
<div class="input-icon-right">
    <input name="{{ $name }}"
           id="{{ $name }}"
           placeholder="{{ $placeholder }}"
           value="{{ $value }}"
           type="text"
           class="form-control pickadate{{ $isInvalidClass.$classes.$class }}"
            {{ $disabledAttr }}
    >
    <span class=""><i class="fa-regular fa-calendar"></i></span>
</div>
