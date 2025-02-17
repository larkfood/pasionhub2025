{{-- text input --}}

<?php

// the field should work whether or not Laravel attribute casting is used
if (isset($field['value']) && (is_array($field['value']) || is_object($field['value']))) {
    $field['value'] = json_encode($field['value']);
}

?>

<div @include('admin.panel.inc.field_wrapper_attributes') >
    <label class="form-label fw-bolder">
        {!! $field['label'] !!}
        @if (isset($field['required']) && $field['required'])
            <span class="text-danger">*</span>
        @endif
    </label>
    @include('admin.panel.fields.inc.translatable_icon')
    <input type="hidden" value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}" name="{{ $field['name'] }}">

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <div class="input-group-addon">{!! $field['prefix'] !!}</div> @endif
        @if(isset($field['store_as_json']) && $field['store_as_json'])
        <input
            type="text"
            data-address="{&quot;field&quot;: &quot;{{$field['name']}}&quot;, &quot;full&quot;: {{isset($field['store_as_json']) && $field['store_as_json'] ? 'true' : 'false'}} }"
            @include('admin.panel.inc.field_attributes')
        >
        @else
        <input
            type="text"
            data-address="{&quot;field&quot;: &quot;{{$field['name']}}&quot;, &quot;full&quot;: {{isset($field['store_as_json']) && $field['store_as_json'] ? 'true' : 'false'}} }"
            name="{{ $field['name'] }}"
            value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
            @include('admin.panel.inc.field_attributes')
        >
        @endif
        @if(isset($field['suffix'])) <div class="input-group-addon">{!! $field['suffix'] !!}</div> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <div class="form-text">{!! $field['hint'] !!}</div>
    @endif
</div>

{{-- Note: you can use  to only load some CSS/JS once, even though there are multiple instances of it --}}

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($xPanel->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <style>
            .ap-input-icon.ap-icon-pin {
                right: 5px !important; }
            .ap-input-icon.ap-icon-clear {
                right: 10px !important; }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script src="{{ asset('assets/plugins/places.js/1.19.0/places.min.js') }}"></script>
    <script>
        onDocumentReady((event) => {
            window.AlgoliaPlaces = window.AlgoliaPlaces || {};

            $('[data-address]').each(function() {

                var $this      = $(this),
                $addressConfig = $this.data('address'),
                $field = $('[name="'+$addressConfig.field+'"]'),
                $place = places({
                    container: $this[0]
                });

                function clearInput() {
                    if( !$this.val().length ){
                        $field.val('');
                    }
                }

                if ($addressConfig.full) {

                    $place.on('change', function(e){
                        var result = JSON.parse(JSON.stringify(e.suggestion));
                        delete(result.highlight); delete(result.hit); delete(result.hitIndex);
                        delete(result.rawAnswer); delete(result.query);
                        $field.val( JSON.stringify(result) );
                    });

                    $this.on('change blur', clearInput);
                    $place.on('clear', clearInput);

                    if( $field.val().length ){
                        var existingData = JSON.parse($field.val());
                        $this.val(existingData.value);
                    }
                }

                window.AlgoliaPlaces[ $addressConfig.field ] = $place;
            });
        });
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
