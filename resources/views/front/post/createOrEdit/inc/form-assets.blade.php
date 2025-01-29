@php
	$postInput ??= [];
	$post ??= [];
	$admin ??= [];
	
	$isSingleStepCreateForm = (isSingleStepFormEnabled() && request()->segment(1) == 'create');
	$isSingleStepEditForm = (isSingleStepFormEnabled() && request()->segment(1) == 'edit');
	
	$picturesLimit ??= 0;
	$picturesLimit = is_numeric($picturesLimit) ? $picturesLimit : 0;
	$picturesLimit = ($picturesLimit > 0) ? $picturesLimit : 1;
	
	$pictures = [];
	if ($isSingleStepEditForm) {
		$pictures = data_get($post, 'pictures', []);
		$pictures = collect($pictures)->slice(0, (int)$picturesLimit)->all();
	}
	
	$postId = data_get($post, 'id') ?? '';
	$postTypeId = data_get($post, 'post_type_id') ?? data_get($postInput, 'post_type_id', 0);
	$countryCode = data_get($post, 'country_code') ?? data_get($postInput, 'country_code', config('country.code', 0));
	
	$adminType = config('country.admin_type', 0);
	
	$selectedAdminCode = data_get($postInput, 'admin_code', 0);
	$selectedAdminCode = data_get($admin, 'code', $selectedAdminCode);
	
	$cityId = data_get($postInput, 'city_id');
	$cityId = data_get($post, 'city_id', $cityId);
	
	$fiTheme = config('larapen.core.fileinput.theme', 'bs5');
	$serverAllowedImageFormatsJson = collect(getServerAllowedImageFormats())->toJson();
	
	$errors ??= getEmptyViewErrors();
	$errors = ($errors instanceof \Illuminate\Support\Collection) ? $errors : collect($errors->toArray());
	$errorsJson = addslashes($errors->toJson());
	
	$cfOldInput = data_get($postInput, 'cf');
	$cfOldInput = session()->getOldInput('cf', $cfOldInput);
	$cfOldInputJson = addslashes(collect($cfOldInput)->toJson());
@endphp
@section('modal_location')
	@include('front.layouts.inc.modal.location')
@endsection

@push('after_styles_stack')
	@include('front.layouts.inc.tools.wysiwyg.css')
	
	{{-- Single Step Form --}}
	@if (isSingleStepFormEnabled())
		<link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet">
		@if (config('lang.direction') == 'rtl')
			<link href="{{ url('assets/plugins/bootstrap-fileinput/css/fileinput-rtl.min.css') }}" rel="stylesheet">
		@endif
		@if (str_starts_with($fiTheme, 'explorer'))
			<link href="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.min.css') }}" rel="stylesheet">
		@endif
		<style>
			.krajee-default.file-preview-frame:hover:not(.file-preview-error) {
				box-shadow: 0 0 5px 0 #666666;
			}
			.file-loading:before {
				content: " {{ t('loading_wd') }}";
			}
			/* Preview Frame Size */
			.krajee-default.file-preview-frame .kv-file-content {
				height: auto;
			}
			.krajee-default.file-preview-frame .file-thumbnail-footer {
				height: 30px;
			}
		</style>
	@endif
	
	<link href="{{ url('assets/plugins/bootstrap-daterangepicker/3.1/daterangepicker.css') }}" rel="stylesheet">
@endpush

@push('after_scripts_stack')
	@include('front.layouts.inc.tools.wysiwyg.js')
	@include('front.common.js.payment-scripts')
	
	{{-- Single Step Form --}}
	@if (isSingleStepFormEnabled())
		<script src="{{ url('assets/plugins/bootstrap-fileinput/js/plugins/sortable.min.js') }}" type="text/javascript"></script>
		<script src="{{ url('assets/plugins/bootstrap-fileinput/js/fileinput.min.js') }}" type="text/javascript"></script>
		<script src="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.js') }}" type="text/javascript"></script>
		<script src="{{ url('common/js/fileinput/locales/' . config('app.locale') . '.js') }}" type="text/javascript"></script>
	@endif
	
	<script src="{{ url('assets/plugins/momentjs/2.18.1/moment.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-daterangepicker/3.1/daterangepicker.js') }}" type="text/javascript"></script>
	
	<script>
		/* Translation */
		var lang = {
			'select': {
				'country': "{{ t('select_a_country') }}",
				'admin': "{{ t('select_a_location') }}",
				'city': "{{ t('select_a_city') }}"
			},
			'price': "{{ t('price') }}",
			'salary': "{{ t('Salary') }}",
			'nextStepBtnLabel': {
				'next': "{{ t('Next') }}",
				'submit': "{{ t('Update') }}"
			}
		};
		
		var stepParam = 0;
		
		/* Category */
		/* Custom Fields */
		var errors = '{!! $errorsJson !!}';
		var oldInput = '{!! $cfOldInputJson !!}';
		var postId = '{{ $postId }}';
		
		/* Permanent Posts */
		var permanentPostsEnabled = '{{ config('settings.listing_form.permanent_listings_enabled', 0) }}';
		var postTypeId = '{{ old('post_type_id', $postTypeId) }}';
		
		/* Locations */
		var countryCode = '{{ old('country_code', $countryCode) }}';
		var adminType = '{{ $adminType }}';
		var selectedAdminCode = '{{ old('admin_code', $selectedAdminCode) }}';
		var cityId = '{{ old('city_id', $cityId) }}';
		
		/* Packages */
		var packageIsEnabled = false;
		@if (isset($packages, $paymentMethods) && $packages->count() > 0 && $paymentMethods->count() > 0)
			packageIsEnabled = true;
		@endif
	</script>
	<script>
		onDocumentReady((event) => {
			
			{{-- fileinput Options --}}
			let options = {};
			options.theme = '{{ $fiTheme }}';
			options.language = '{{ config('app.locale') }}';
			options.rtl = {{ (config('lang.direction') == 'rtl') ? 'true' : 'false' }};
			options.dropZoneEnabled = false;
			options.overwriteInitial = true;
			options.showCaption = true;
			options.showPreview = true;
			options.showClose = true;
			options.showUpload = false;
			options.showRemove = false;
			options.previewFileType = 'image';
			options.allowedFileExtensions = {!! $serverAllowedImageFormatsJson !!};
			options.minFileSize = {{ (int)config('settings.upload.min_image_size', 0) }};
			options.maxFileSize = {{ (int)config('settings.upload.max_image_size', 1000) }};
			options.initialPreview = [];
			options.initialPreviewConfig = [];
			options.fileActionSettings = {
				showRotate: false,
				showUpload: false,
				showDrag: false,
				showRemove: true,
				removeClass: 'btn btn-outline-danger btn-sm',
				showZoom: true,
				zoomClass: 'btn btn-outline-secondary btn-sm',
			};
			
			{{-- Single Step Form --}}
			@if (isSingleStepFormEnabled())
				@if ($isSingleStepCreateForm)
					{{-- Create Form --}}
					{{-- fileinput --}}
					$('.post-picture').fileinput(options);
				@else
					{{-- Edit Form --}}
					@for($i = 0; $i <= $picturesLimit-1; $i++)
						options.initialPreview = [];
						options.initialPreviewConfig = [];
						@php
							$picture = data_get($pictures, $i);
						@endphp
						@if (!empty($picture))
							@php
								$postId = data_get($post, 'id');
								$pictureId = data_get($picture, 'id');
								$pictureUrl = data_get($picture, 'url.medium');
								$filePath = data_get($picture, 'file_path');
								$deleteUrl = url('posts/' . $postId . '/photos/' . $pictureId . '/delete');
								try {
									$fileExists = (isset($disk) && !empty($filePath) && $disk->exists($filePath));
									$fileSize = $fileExists ? (int)$disk->size($filePath) : 0;
								} catch (\Throwable $e) {
									$fileSize = 0;
								}
							@endphp
							options.initialPreview[0] = '<img src="{{ $pictureUrl }}" class="file-preview-image">';
							options.initialPreviewConfig[0] = {};
							options.initialPreviewConfig[0].key = {{ (int)($pictureId ?? $i) }};
							options.initialPreviewConfig[0].caption = '{{ basename($filePath) }}';
							options.initialPreviewConfig[0].size = {{ $fileSize }};
							options.initialPreviewConfig[0].url = '{{ $deleteUrl }}';
						@endif
						
						{{-- fileinput --}}
						$('#picture{{ $i }}').fileinput(options);
						
						/* Delete picture */
						$('#picture{{ $i }}').on('filepredelete', function (event, key, jqXHR, data) {
							let abort = true;
							if (confirm("{{ t('Are you sure you want to delete this picture') }}")) {
								abort = false;
							}
							return abort;
						});
					@endfor
				@endif
			@endif
			
			{{-- select2: If error occured, apply Bootstrap's error class --}}
			@if (config('settings.listing_form.city_selection') == 'select')
				@if ($errors->has('admin_code'))
					$('select[name="admin_code"]').closest('div').addClass('is-invalid');
				@endif
			@endif
			@if ($errors->has('city_id'))
				$('select[name="city_id"]').closest('div').addClass('is-invalid');
			@endif
			
			{{-- Tagging with multi-value Select Boxes --}}
			@php
				$tagsLimit = (int)config('settings.listing_form.tags_limit', 15);
				$tagsMinLength = (int)config('settings.listing_form.tags_min_length', 2);
				$tagsMaxLength = (int)config('settings.listing_form.tags_max_length', 30);
			@endphp
			let selectTagging = $('.tags-selecter').select2({
				language: langLayout.select2,
				width: '100%',
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
			@if ($errors->has('tags.*'))
				$('select[name^="tags"]').next('.select2.select2-container').addClass('is-invalid');
			@endif
			
		});
	</script>
	
	<script src="{{ url('assets/js/app/d.modal.category.js') . vTime() }}"></script>
	@if (config('settings.listing_form.city_selection') == 'select')
		<script src="{{ url('assets/js/app/d.select.location.js') . vTime() }}"></script>
	@else
		<script src="{{ url('assets/js/app/browse.locations.js') . vTime() }}"></script>
		<script src="{{ url('assets/js/app/d.modal.location.js') . vTime() }}"></script>
	@endif
	
@endpush
