{{-- select2 from ajax multiple --}}
@php
	$field ??= [];
	
	$connectedEntity = new $field['model'];
	$connectedEntityKeyName = $connectedEntity->getKeyName();
	
	$oldValue = $field['value'] ?? ($field['default'] ?? false);
	$oldValue = old($field['name'], $oldValue);
@endphp

<div @include('admin.panel.inc.field_wrapper_attributes') >
	<label class="form-label fw-bolder">
		{!! $field['label'] !!}
		@if (isset($field['required']) && $field['required'])
			<span class="text-danger">*</span>
		@endif
	</label>
	@include('admin.panel.fields.inc.translatable_icon')
	<select
			name="{{ $field['name'] }}[]"
			style="width: 100%"
			id="select2_ajax_multiple_{{ $field['name'] }}"
			@include('admin.panel.inc.field_attributes', ['default_class' => 'form-control'])
			multiple>
		
		@if ($oldValue)
			@foreach ($oldValue as $resultKey)
				@php
					$item = $connectedEntity->find($resultKey);
				@endphp
				@if ($item)
					<option value="{{ $item->getKey() }}" selected>
						{{ $item->{$field['attribute']} }}
					</option>
				@endif
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
	@endpush

@endif

{{-- include field specific select2 js--}}
@push('crud_fields_scripts')
<script>
	onDocumentReady((event) => {
		// trigger select2 for each untriggered select2 box
		$("#select2_ajax_multiple_{{ $field['name'] }}").each(function (i, obj) {
			if (!$(obj).hasClass("select2-hidden-accessible"))
			{
				$(obj).select2({
					theme: 'bootstrap',
					multiple: true,
					placeholder: "{{ $field['placeholder'] }}",
					minimumInputLength: "{{ $field['minimum_input_length'] }}",
					ajax: {
						url: "{{ $field['data_source'] }}",
						dataType: 'json',
						quietMillis: 250,
						data: function (params) {
							return {
								q: params.term, // search term
								page: params.page
							};
						},
						processResults: function (data, params) {
							params.page = params.page || 1;
							
							return {
								results: $.map(data.data, function (item) {
									return {
										text: item["{{$field['attribute']}}"],
										id: item["{{ $connectedEntityKeyName }}"]
									}
								}),
								more: data.current_page < data.last_page
							};
						},
						cache: true
					},
				});
			}
		});
	});
</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
