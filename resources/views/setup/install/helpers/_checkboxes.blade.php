@php
    $name ??= 'field_name';
    $classes ??= '';
	$class ??= '';
	$isInvalidClass ??= '';
	
    $options ??= [];
	$value ??= '';
	$value = old($name, $value);
@endphp
@foreach($options as $option)
    @php
        $optionValue = $option['value'] ?? '';
		$optionLabel = $option['text'] ?? '';
        $checkedAttr = in_array($optionValue, explode(',', $value))  ? ' checked' : '';
    @endphp
    <div class="form-check">
        <label class="form-check-label">
            <input type="checkbox"
                   id="{{ $name }}"
                   name="{{ $name }}"
                   value="{{ $optionValue }}"
                   class="form-check-input{{ $isInvalidClass }}"
                    {!! $checkedAttr !!}
            >
            {{ $optionLabel }}
        </label>
    </div>
@endforeach
