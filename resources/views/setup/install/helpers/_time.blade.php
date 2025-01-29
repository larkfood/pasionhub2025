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
<div class="input-icon-right">
    <input name="{{ $name }}"
           id="{{ $name }}"
           placeholder="{{ $placeholder }}"
           value="{{ $value }}"
           type="text"
           class="form-control pickatime{{ $isInvalidClass.$classes.$class }}"
            {!! $disabledAttr !!}
    >
    <span class=""><i class="fa-regular fa-bell"></i></span>
</div>
