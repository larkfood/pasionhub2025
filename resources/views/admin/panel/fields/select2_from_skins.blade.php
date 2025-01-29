{{-- select2 from array --}}
@php
	$field ??= [];
	
	$field['allows_null'] ??= false;
	$field['allows_multiple'] ??= false;
	
	$name = $field['name'];
	$name = $field['allows_multiple'] ? $name . '[]' : $name;
	
	$field['options'] ??= [];
	
	$multipleAttr = $field['allows_multiple'] ? ' multiple' : '';
	
	$fieldValue = $field['value'] ?? ($field['default'] ?? null);
	$fieldValue = old($field['name'], $fieldValue);
@endphp
<div @include('admin.panel.inc.field_wrapper_attributes') >
	<label class="form-label fw-bolder">
		{!! $field['label'] !!}
		@if (isset($field['required']) && $field['required'])
			<span class="text-danger">*</span>
		@endif
	</label>
	@include('admin.panel.fields.inc.translatable_icon')
	<select name="{{ $name }}" style="width: 100%"
			@include('admin.panel.inc.field_attributes', ['default_class' => 'form-select select2_from_skins'])
			{!! $multipleAttr !!}
	>
		@if ($field['allows_null'])
			<option value="">-</option>
		@endif
		@if (!empty($field['options']))
			@foreach ($field['options'] as $key => $value)
				@php
					$selectedAttr = ($key == $fieldValue || (is_array($fieldValue) && in_array($key, $fieldValue))) ? ' selected' : '';
				@endphp
				<option value="{{ $key }}"{!! $selectedAttr !!}>{!! $value !!}</option>
			@endforeach
		@endif
	</select>
	
	{{-- HINT --}}
	@if (isset($field['hint']))
		<div class="form-text">{!! $field['hint'] !!}</div>
	@endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($xPanel->checkIfFieldIsFirstOfItsType($field, $fields))
	
	{{-- FIELD CSS - will be loaded in the after_styles section --}}
	@push('crud_fields_styles')
	{{-- include select2 css--}}
	<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('assets/plugins/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
	@endpush
	
	{{-- FIELD JS - will be loaded in the after_scripts section --}}
	@push('crud_fields_scripts')
	{{-- include select2 js--}}
	<script src="{{ asset('assets/plugins/select2/js/select2.js') }}"></script>
	<script>
		var skins = jQuery.parseJSON('{!! $field['skins'] !!}');
		
		onDocumentReady((event) => {
			// trigger select2 for each untriggered select2 box
			$('.select2_from_skins').each(function (i, obj) {
				if (!$(obj).hasClass("select2-hidden-accessible"))
				{
					$(obj).select2({
						theme: "bootstrap",
						templateResult: formatColor,
						templateSelection: formatColor
					});
				}
			});
		});
		
		function formatColor (color) {
			if (!color.id) {
				return color.text;
			}
			
			let hex = '#000000';
			if (typeof skins[color.id] !== 'undefined' && typeof skins[color.id].color !== 'undefined' && skins[color.id].color != null) {
				hex = skins[color.id].color;
			}
			if (color.id == 'default') {
				hex = '#CCCCCC';
			}
			
			let colorIcon = '<div style="display: inline-block; width: 30px; height: 20px; background-color: ' + hex + ';"></div>';
			let colorText = '&nbsp;' + color.text + '';
			
			var formattedColor = $(
				'<div style="display: flex; align-items: center;">' + colorIcon + ' ' + colorText + '</div>'
			);
			
			return formattedColor;
		}
	</script>
	@endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
