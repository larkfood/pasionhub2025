<form role="form" novalidate>
    {{-- Show the erros, if any --}}
    @if ($errors->any())
		<div class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert">
			<h4 class="alert-heading">{{ trans('admin.please_fix') }}</h4>
			<ul>
				@foreach($errors->all() as $error)
					<li>{!! $error !!}</li>
				@endforeach
			</ul>
		</div>
    @endif

    {{-- Show the inputs --}}
	<div class="card mb-0">
		<div class="row">
			@foreach ($fields as $field)
				@include('admin.panel.fields.' . $field['type'], ['field' => $field])
				
				{{--
					The fields 'newline' element can be:
					- true (boolean) for both of 'create' or 'update' forms
					- 'create' (string) for create form only
					- 'update' (string) for update form only
				--}}
				@if (array_key_exists('newline', $field))
					@if (is_bool($field['newline']) && $field['newline'])
						<div style="clear: both; margin: 0; padding: 0;"></div>
					@endif
					@if (isset($form))
						@if (is_string($field['newline']) && $field['newline'] == $form)
							<div style="clear: both; margin: 0; padding: 0;"></div>
						@endif
					@endif
				@endif
			@endforeach
		</div>
	</div>

</form>

{{-- Define blade stacks so css and js can be pushed from the fields to these sections. --}}

@section('after_styles')
	@parent
	
    <!-- CRUD FORM CONTENT - crud_fields_styles stack -->
    @stack('crud_fields_styles')
@endsection

@section('after_scripts')
	@parent
	
    <!-- CRUD FORM CONTENT - crud_fields_scripts stack -->
    @stack('crud_fields_scripts')

    <script>
	    onDocumentReady((event) => {
			// Save button has multiple actions: save and exit, save and edit, save and new
			var saveActions = $('#saveActions'),
				crudForm = saveActions.parents('form'),
				saveActionField = $('[name="save_action"]');
			
			saveActions.on('click', '.dropdown-menu a', function() {
				var saveAction = $(this).data('value');
				saveActionField.val( saveAction );
				crudForm.submit();
			});
			
            // Ctrl+S and Cmd+S trigger Save button click
            $(document).keydown(function(e) {
                if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey))
                {
                    e.preventDefault();
                    // alert("Ctrl-s pressed");
                    $("button[type=submit]").trigger('click');
                    return false;
                }
                return true;
            });

            @if($xPanel->autoFocusOnFirstField)
                // Focus on first field
            @php
                $focusField = \Illuminate\Support\Arr::first($fields, function($field) {
                    return isset($field['auto_focus']) && $field['auto_focus'] == true;
                })
            @endphp

            @if($focusField)
                window.focusField = $('[name="{{$focusField['name']}}"]').eq(0),
            @else
            	var focusField = $('form').find('input, textarea, select').not('[type="hidden"]').eq(0),
            @endif

			fieldOffset = focusField.offset().top,
			scrollTolerance = $(window).height() / 2;

            focusField.trigger('focus');

            if( fieldOffset > scrollTolerance ){
                $('html, body').animate({scrollTop: (fieldOffset - 30)});
            }
            @endif
        });
    </script>
@endsection
