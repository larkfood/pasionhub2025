{{-- select2 tagging from array --}}
@php
	$field ??= [];
	
	$field['allows_multiple'] ??= false;
	
	$name = $field['name'];
	$name = $field['allows_multiple'] ? $name . '[]' : $name;
	
	$field['options'] ??= [];
	$field['rules'] ??= [];
	
	$varName = str_replace('[]', '', $name);
	$varName = str_replace('][', '.', $varName);
	$varName = str_replace('[', '.', $varName);
	$varName = str_replace(']', '', $varName);
	
	$fieldRules = $field['rules'][$varName] ?? [];
	$fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
	$fieldRules = is_array($fieldRules) ? $fieldRules : [];
	
	$required = in_array('required', $fieldRules) ? true : '';
	
	$multipleAttr = $field['allows_multiple'] ? ' multiple' : '';
	
	$tags = old('tags', $field['options'] ?? []);
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
			@include('admin.panel.inc.field_attributes', ['default_class' =>  'form-select select2_tagging_from_array'])
			{!! $multipleAttr !!}
	>
		@if (!empty($tags))
			@foreach ($tags as $key => $value)
				<option selected="selected">{{ $value }}</option>
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
	    onDocumentReady((event) => {
			{{-- Trigger select2 for each untriggered select2_tagging_from_array box --}}
			$('.select2_tagging_from_array').each(function (i, obj) {
				if (!$(obj).hasClass("select2-hidden-accessible"))
				{
					$(obj).select2({
						theme: 'bootstrap'
					});
				}
			});
			
			{{-- Tagging with multi-value Select Boxes --}}
			@php
				$tagsLimit = (int)config('settings.listing_form.tags_limit', 15);
				$tagsMinLength = (int)config('settings.listing_form.tags_min_length', 2);
				$tagsMaxLength = (int)config('settings.listing_form.tags_max_length', 30);
			@endphp
			let selectTagging = $('.select2_tagging_from_array').select2({
				theme: 'bootstrap',
				tags: true,
				maximumSelectionLength: {{ $tagsLimit }},
				tokenSeparators: [',', ';', ':', '/', '\\', '#'],
				createTag: function (params) {
					var term = $.trim(params.term);
					
					{{-- Don't offset to create a tag if there is some symbols/characters --}}
					let invalidCharsArray = [',', ';', '_', '/', '\\', '#'];
					let arrayLength = invalidCharsArray.length;
					for (let i = 0; i < arrayLength; i++) {
						let invalidChar = invalidCharsArray[i];
						if (term.indexOf(invalidChar) !== -1) {
							return null;
						}
					}
					
					{{-- Don't offset to create empty tag --}}
					{{-- Return null to disable tag creation --}}
					if (term === '') {
						return null;
					}
					
					{{-- Don't allow tags which are less than 2 characters or more than 50 characters --}}
					if (term.length < {{ $tagsMinLength }} || term.length > {{ $tagsMaxLength }}) {
						return null;
					}
					
					return {
						id: term,
						text: term
					}
				}
			});
			
			{{-- Apply tags limit --}}
			selectTagging.on('change', function(e) {
				if ($(this).val().length > {{ $tagsLimit }}) {
					$(this).val($(this).val().slice(0, {{ $tagsLimit }}));
				}
			});
			
			{{-- select2: If error occured, apply Bootstrap's error class --}}
			@if ($errors->has($varName . '.*'))
				$('select[name^="{{ $varName }}"]').closest('div').addClass('is-invalid');
			@endif
		});
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
