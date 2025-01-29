@php
	$field ??= [];
	
	$fieldName = str_replace('[]', '', $field['name']);
	$fieldName = str_replace('][', '.', $fieldName);
	$fieldName = str_replace('[', '.', $fieldName);
	$fieldName = str_replace(']', '', $fieldName);
	
	$fieldRules = $field['rules'][$fieldName] ?? [];
	$fieldRules = !is_array($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
	$required = in_array('required', $fieldRules) ? true : '';
	
	$isInvalidClass = (isset($errors) && $errors->has($fieldName)) ? ' is-invalid' : '';
	
	// Get Attributes Output
	$attr = '';
	if (isset($field['attributes'])) {
		foreach ($field['attributes'] as $attribute => $value) {
			if (is_string($attribute)) {
				$attr .= $attribute . '="' . $value;
				if ($attribute == 'class') {
					$attr .= $isInvalidClass;
				}
				$attr .= '"';
			}
		}
		if (!isset($field['attributes']['class'])) {
			$attr .= 'class="';
			if (isset($default_class)) {
				$attr .= $default_class . $isInvalidClass;
			} else {
				$attr .= 'form-control' . $isInvalidClass;
			}
			$attr .= '"';
		}
	} else {
		$attr .= 'class="';
		if (isset($default_class)) {
			$attr .= $default_class . $isInvalidClass;
		} else {
			$attr .= 'form-control' . $isInvalidClass;
		}
		$attr .= '"';
	}
@endphp
{!! $attr !!}
