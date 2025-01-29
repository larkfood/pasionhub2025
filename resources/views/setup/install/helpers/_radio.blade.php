@php
    $name ??= 'field_name';
    $classes ??= '';
	$isInvalidClass ??= '';
	
	$value ??= '';
	$value = old($name, $value);
@endphp
@foreach($options as $option)
    @php
        $optionValue = $option['value'] ?? '';
		$optionLabel = $option['text'] ?? '';
        $checkedAttr = ($optionValue == $value)  ? ' checked' : '';
    @endphp
    <div class="radio">
        <label>
            <input type="radio"
                   id="{{ $name }}"
                   name="{{ $name }}"
                   value="{{ $optionValue }}"
                   class="styled{{ $isInvalidClass }}"
                    {!! $checkedAttr !!}
            >
            {{ $optionLabel }}
        </label>
    </div>
@endforeach
