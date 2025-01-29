{{-- configurable color picker --}}
<div @include('admin.panel.inc.field_wrapper_attributes') >
    <label class="form-label fw-bolder">
        {!! $field['label'] !!}
        @if (isset($field['required']) && $field['required'])
            <span class="text-danger">*</span>
        @endif
    </label>
    @include('admin.panel.fields.inc.translatable_icon')
    <div class="input-group">
        @php
            $default = $field['value'] ?? ($field['default'] ?? '' );
        @endphp
        <input
                type="text"
                name="{{ $field['name'] }}"
                value="{{ old($field['name'], $default) }}" data-coloris
                @include('admin.panel.inc.field_attributes')
        >
    </div>
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
        <link rel="stylesheet" href="{{ asset('assets/plugins/coloris/0.24.0/coloris.min.css') }}" />
        <style>
            .coloris {
                /* display: flex; /* Buggy in v0.24.0 */
                /* flex-wrap: wrap; /* Buggy in v0.24.0 */
                flex-shrink: 0;
                margin-bottom: 30px;
            }
            
            .coloris input {
                width: 100%;
                height: 32px;
                padding: 0 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                font-family: inherit;
                font-size: inherit;
                font-weight: inherit;
                box-sizing: border-box;
            }
            
            .clr-field  {
                width: 100%;
            }
            
            .square .clr-field button,
            .circle .clr-field button {
                width: 22px;
                height: 22px;
                left: 5px;
                right: auto;
                border-radius: 5px;
            }
    
            .square .clr-field input,
            .circle .clr-field input {
                padding-left: 36px;
            }
    
            .circle .clr-field button {
                border-radius: 50%;
            }
    
            .full .clr-field button {
                width: 100%;
                height: 100%;
                border-radius: 5px;
            }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script type="text/javascript" src="{{ asset('assets/plugins/coloris/0.24.0/coloris.min.js') }}"></script>
    @endpush

@endif

@push('crud_fields_scripts')
<script type="text/javascript">
    onDocumentReady((event) => {
        /* https://github.com/mdbassit/Coloris */
        let defaultConfig = {
            theme: 'pill',
            themeMode: 'dark',
            formatToggle: true,
            closeButton: true,
            clearButton: true,
        };
        let config = {};
        @if (isset($field['colorpicker_options']))
                config = {!! json_encode($field['colorpicker_options']) !!};
        @endif
        document.querySelector('[name="{{ $field['name'] }}"]').addEventListener('click', e => {
            Coloris(!isEmpty(config) ? config : defaultConfig);
        });
    });
</script>
@endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
