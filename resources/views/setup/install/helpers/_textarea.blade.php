@php
	$name ??= 'field_name';
	$classes ??= '';
	$class ??= '';
	$isInvalidClass ??= '';
	
	$value ??= '';
	$value = old($name, $value);
	$readonly ??= false;
	
	$readonlyAttr = $readonly ? ' readonly="readonly"' : '';
@endphp
<textarea name="{{ $name }}"
          id="{{ $name }}"
          type="text"
          class="form-control{{ $isInvalidClass.$classes.$class }}"
		{!! $readonlyAttr !!}
>{{ $value }}</textarea>
