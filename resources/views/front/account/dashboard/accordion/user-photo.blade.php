@php
	$fiTheme = config('larapen.core.fileinput.theme', 'bs5');
	$serverAllowedImageFormatsJson = collect(getServerAllowedImageFormats())->toJson();
@endphp

<div class="card card-default">
	<div class="card-header">
		<h4 class="card-title">
			<a href="#photoPanel" data-bs-toggle="collapse" data-parent="#accordion">{{ t('Photo or Avatar') }}</a>
		</h4>
	</div>
	@php
		$photoPanelClass = '';
		$photoPanelClass = request()->query('panel') == 'photo' ? 'show' : $photoPanelClass;
		$photoPanelClass = (old('panel') == '' || old('panel') == 'photo') ? 'show' : $photoPanelClass;
	@endphp
	<div class="panel-collapse collapse {{ $photoPanelClass }}" id="photoPanel">
		<div class="card-body">
			<form name="photoUpdate" class="form-horizontal" role="form" method="POST" action="{{ url('account/photo') }}">
				<div class="row">
					<div class="col-xl-12 text-center">
						
						@php
							$photoPathError = (isset($errors) && $errors->has('photo_path')) ? ' is-invalid' : '';
						@endphp
						<div class="photo-field">
							<div class="file-loading">
								<input id="photoField" name="photo_path" type="file" class="file {{ $photoPathError }}">
							</div>
						</div>
					
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

@section('after_styles')
	@parent
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
	</style>
	<style>
		/* Avatar Upload */
		.photo-field {
			display: inline-block;
			vertical-align: middle;
		}
		
		.photo-field .krajee-default.file-preview-frame,
		.photo-field .krajee-default.file-preview-frame:hover {
			margin: 0;
			padding: 0;
			border: none;
			box-shadow: none;
			text-align: center;
		}
		
		.photo-field .file-input {
			display: table-cell;
			width: 150px;
		}
		
		.photo-field .krajee-default.file-preview-frame .kv-file-content {
			width: 150px;
			height: 160px;
		}
		
		.kv-reqd {
			color: red;
			font-family: monospace;
			font-weight: normal;
		}
		
		.file-preview {
			padding: 2px;
		}
		
		.file-drop-zone {
			margin: 2px;
			min-height: 100px;
		}
		
		.file-drop-zone .file-preview-thumbnails {
			cursor: pointer;
		}
		
		.krajee-default.file-preview-frame .file-thumbnail-footer {
			height: 30px;
		}
		
		/* Allow clickable uploaded photos (Not possible) */
		.file-drop-zone {
			padding: 20px;
		}
		
		.file-drop-zone .kv-file-content {
			padding: 0
		}
	</style>
@endsection
@section('after_scripts')
	@parent
	<script src="{{ url('assets/plugins/bootstrap-fileinput/js/plugins/sortable.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/js/fileinput.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('common/js/fileinput/locales/' . config('app.locale') . '.js') }}" type="text/javascript"></script>
	<script>
		let defaultAvatarUrl = '{{ thumbParam(config('larapen.media.avatar'))->url() }}';
		let defaultAvatarAlt = '{{ t('Your Photo or Avatar') }}';
		let uploadHint = '<h6 class="text-muted pb-0">{{ t('Click to select') }}</h6>';
		
		let options = {};
		options.theme = '{{ $fiTheme }}';
		options.language = '{{ config('app.locale') }}';
		options.rtl = {{ (config('lang.direction') == 'rtl') ? 'true' : 'false' }};
		options.overwriteInitial = true;
		options.showCaption = false;
		options.showPreview = true;
		options.allowedFileExtensions = {!! $serverAllowedImageFormatsJson !!};
		options.uploadUrl = '{{ url('account/photo') }}';
		options.uploadExtraData = {
			_token: '{{ csrf_token() }}',
			_method: 'PUT'
		};
		options.showClose = false;
		options.showBrowse = true;
		options.browseClass = 'btn btn-primary';
		options.minFileSize = {{ (int)config('settings.upload.min_image_size', 0) }};
		options.maxFileSize = {{ (int)config('settings.upload.max_image_size', 1000) }};
		options.uploadAsync = false;
		options.browseOnZoneClick = true;
		options.minFileCount = 0;
		options.maxFileCount = 1;
		options.validateInitialCount = true;
		options.defaultPreviewContent = '<img src="' + defaultAvatarUrl + '" alt="' + defaultAvatarAlt + '">' + uploadHint;
		options.initialPreview = [];
		options.initialPreviewAsData = true;
		options.initialPreviewFileType = 'image';
		options.initialPreviewConfig = [];
		options.fileActionSettings = {
			showDrag: false,
			showRemove: true,
			removeClass: 'btn btn-outline-danger btn-sm',
			showZoom: true,
			zoomClass: 'btn btn-outline-secondary btn-sm'
		};
		options.elErrorContainer = '#avatarUploadError';
		options.msgErrorClass = 'alert alert-block alert-danger';
		options.layoutTemplates = {
			main2: '{preview}\n<div class="kv-upload-progress hide"></div>\n{browse}',
			footer: '<div class="file-thumbnail-footer pt-2">\n{actions}\n</div>',
			actions: '<div class="file-actions">\n'
				+ '<div class="file-footer-buttons">\n{delete} {zoom}</div>\n'
				+ '<div class="clearfix"></div>\n'
				+ '</div>'
		};
		
		@if (!empty($authUser->photo_path) && isset($disk) && !empty($authUser->photo_url))
			@php
				$photoUrl = $authUser->photo_url;
				$deleteUrl = url('account/photo/delete');
				
				try {
					$fileSize = $disk->exists($authUser->photo_path) ? (int)$disk->size($authUser->photo_path) : 0;
				} catch (\Throwable $e) {
					$fileSize = 0;
				}
			@endphp
			options.initialPreview[0] = '{{ $photoUrl }}';
			options.initialPreviewConfig[0] = {};
			options.initialPreviewConfig[0].key = {{ (int)$authUser->id }};
			options.initialPreviewConfig[0].caption = '{{ basename($authUser->photo_path) }}';
			options.initialPreviewConfig[0].size = {{ $fileSize }};
			options.initialPreviewConfig[0].url = '{{ $deleteUrl }}';
			options.initialPreviewConfig[0].extra = options.uploadExtraData;
		@endif
		
		onDocumentReady((event) => {
			{{-- fileinput --}}
			let photoFieldEl = $('#photoField');
			photoFieldEl.fileinput(options);
			
			/* Auto-upload added file */
			photoFieldEl.on('filebatchselected', function (event, files) {
				$(this).fileinput('upload');
			});
			
			/* Show the upload status message */
			photoFieldEl.on('filebatchpreupload', function (event, data) {
				$('#avatarUploadSuccess').html('<ul></ul>').hide();
			});
			
			/* Show the success upload message */
			photoFieldEl.on('filebatchuploadsuccess', function (event, data) {
				/* Show uploads success messages */
				let out = '';
				$.each(data.files, function (key, file) {
					if (typeof file !== 'undefined') {
						let fileName = file.name;
						fileName = '<strong>' + fileName + '</strong>';
						
						let message = '<li>{!! escapeStringForJs(t('fileinput_file_uploaded_successfully')) !!}</li>';
						message = message.replace('{fileName}', fileName);
						
						out = out + message;
					}
				});
				let avatarUploadSuccessEl = $('#avatarUploadSuccess');
				avatarUploadSuccessEl.find('ul').append(out);
				avatarUploadSuccessEl.fadeIn('slow');
				
				$('#userImg').attr({'src': $('.photo-field .kv-file-content .file-preview-image').attr('src')});
			});
			
			/* Delete picture */
			photoFieldEl.on('filepredelete', function (event, key, jqXHR, data) {
				let abort = true;
				if (confirm("{{ t('Are you sure you want to delete this picture') }}")) {
					abort = false;
				}
				
				return abort;
			});
			
			photoFieldEl.on('filedeleted', function (event, key, jqXHR, data) {
				$('#userImg').attr({'src': defaultAvatarUrl});
				
				let out = "{{ t('Your photo or avatar has been deleted') }}";
				let avatarUploadSuccessEl = $('#avatarUploadSuccess');
				avatarUploadSuccessEl.html('<ul><li></li></ul>').hide();
				avatarUploadSuccessEl.find('ul li').append(out);
				avatarUploadSuccessEl.fadeIn('slow');
			});
		});
	</script>
@endsection
