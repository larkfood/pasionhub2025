@php
	$name ??= 'field_name';
	$classes ??= '';
	$isInvalidClass ??= '';
	
	$value ??= '';
@endphp
<input name="{{ $name }}"
       id="{{ $name }}"
       value="{{ $value }}"
       autocomplete="new-password"
       type="password"
       class="form-control{{ $isInvalidClass.$classes }}"
>
