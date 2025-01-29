@php
	$prefix ??= '';
	$suffix ??= '';
	$name ??= 'field_name';
	$type ??= 'text';
	$rules ??= []; // No longer used
	$classes ??= '';
	$hint ??= '';
	$value ??= '';
	$required ??= false;
	$class = !empty($class) ? ' ' . $class : '';
	$label = $label ?? (trans()->has('messages.' . $name) ? trans('messages.' . $name) : '');
	
	// Get field name
	$fieldName = str($name)
		->replace('[]', '')
		->replace('][', '.')
		->replace('[', '.')
		->replace(']', '')
		->toString();
	
	if (empty($label)) {
		$labelKey = str($fieldName)->replace('.', '-')->toString();
		$label = trans()->has('messages.' . $labelKey) ? trans('messages.' . $labelKey) : '';
		
		if (empty($label)) {
			$labelKey = str($fieldName)->afterLast('.')->toString();
			$label = trans()->has('messages.' . $labelKey) ? trans('messages.' . $labelKey) : '';
		}
	}
	
	// Get group class
	$groupClass = (!empty($prefix) || !empty($suffix)) ? ' input-group' : '';
	
	// Get field rules (No longer used)
	if (!empty($rules)) {
		$fieldRules = $rules[$fieldName] ?? [];
		$fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : (is_array($fieldRules) ? $fieldRules : []);
		$fieldRules = collect($fieldRules)
			->reject(function ($item) {
				return (str_contains($item, ' ') || str_contains($item, '\\') || str_contains($item, 'new '));
			})->toArray();
		
		// Get the field rules as classes names
		$classes = !empty($fieldRules) ? ' ' . implode(' ', $fieldRules) : '';
		$classes = str_replace(['required', 'email'], '', $classes);
		
		// Check if the field is required
		$required = !$required ? in_array('required', $fieldRules) : $required;
	}
	
	// Get eventual field's error message
	$isInvalidClass = (isset($errors) && $errors->has($fieldName)) ? ' is-invalid' : '';
@endphp
@if ($type == 'checkbox' || $type == 'checkbox_switch')
	
	@include('setup.install.helpers._' . $type, ['isInvalidClass' => $isInvalidClass])

@else
	<div class="mb-3{{ $groupClass }}">
		@if (!empty($label))
			<label class="form-label">
				{!! $label !!}
				@if ($required)
					<span class="text-danger">*</span>
				@endif
			</label>
		@endif
		
		@if ($type == 'textarea')
			@if ($errors->has($fieldName))
				<span class="invalid-feedback">
					<strong>{!! $errors->first($fieldName) !!}</strong>
				</span>
			@endif
		@endif
		
		@if (!empty($prefix))
			<span class="input-group-text">
				{!! $prefix !!}
			</span>
		@endif
		
		@include('setup.install.helpers._' . $type, ['isInvalidClass' => $isInvalidClass])
		
		@if (!empty($suffix))
			<span class="input-group-text">
				{!! $suffix !!}
			</span>
		@endif
		
		@if (!empty($hint))
			<div class="form-text">
				{!! $hint !!}
			</div>
		@endif
		
		@if ($type != 'textarea')
			@if ($errors->has($fieldName))
				<span class="invalid-feedback">
					<strong>{!! $errors->first($fieldName) !!}</strong>
				</span>
			@endif
		@endif
	</div>
@endif
