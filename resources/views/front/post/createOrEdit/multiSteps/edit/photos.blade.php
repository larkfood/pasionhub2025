{{--
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
--}}
@extends('front.layouts.master')

@section('wizard')
    @include('front.post.createOrEdit.multiSteps.inc.wizard')
@endsection

@php
	$post ??= [];
	
	$picturesLimit ??= 0;
	$picturesLimit = is_numeric($picturesLimit) ? $picturesLimit : 0;
	$picturesLimit = ($picturesLimit > 0) ? $picturesLimit : 1;
	
	// Get the listing pictures (by applying the picture limit)
	$pictures = data_get($post, 'pictures', []);
	$pictures = collect($pictures)->slice(0, $picturesLimit)->all();
	
	$fiTheme = config('larapen.core.fileinput.theme', 'bs5');
	$serverAllowedImageFormatsJson = collect(getServerAllowedImageFormats())->toJson();
	
	$authUser = auth()->check() ? auth()->user() : null;
	
	// Get steps URLs & labels
	$previousStepUrl ??= null;
	$previousStepLabel ??= null;
	$formActionUrl ??= request()->fullUrl();
	$nextStepUrl ??= '/';
	$nextStepLabel ??= t('submit');
@endphp
@section('content')
	@include('front.common.spacer')
    <div class="main-container">
        <div class="container">
            <div class="row">
    
                @include('front.post.inc.notification')
                
                <div class="col-md-12 page-content">
                    <div class="inner-box">
						
                        <h2 class="title-2">
							<strong><i class="fa-solid fa-camera"></i> {{ t('Photos') }}</strong>
	                        @php
		                        try {
									if (!empty($authUser)) {
										if (doesUserHavePermission($authUser, \App\Models\Permission::getStaffPermissions())) {
											$postLink = '-&nbsp;<a href="' . urlGen()->post($post) . '"
													  class=""
													  data-bs-placement="top"
													  data-bs-toggle="tooltip"
													  title="' . data_get($post, 'title') . '"
											>' . str(data_get($post, 'title'))->limit(45) . '</a>';
											
											echo $postLink;
										}
									}
								} catch (\Throwable $e) {}
	                        @endphp
						</h2>
						
                        <div class="row">
                            <div class="col-md-12">
                                <form class="form-horizontal"
                                      id="payableForm"
                                      method="POST"
                                      action="{{ $formActionUrl }}"
                                      enctype="multipart/form-data"
                                >
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="post_id" value="{{ data_get($post, 'id') }}">
                                    <fieldset>
                                        @if (isset($picturesLimit) && is_numeric($picturesLimit) && $picturesLimit > 0)
											{{-- pictures --}}
	                                        @php
		                                        $picturesError = (isset($errors) && $errors->has('pictures')) ? ' is-invalid' : '';
	                                        @endphp
                                            <div id="picturesBloc" class="input-group row">
												<div class="col-md-3 form-label{{ $picturesError }}"> {{ t('pictures') }} </div>
												<div class="col-md-8"></div>
												<div class="col-md-12 text-center pt-2" style="position: relative; float: {!! (config('lang.direction')=='rtl') ? 'left' : 'right' !!};">
													<div {!! (config('lang.direction')=='rtl') ? 'dir="rtl"' : '' !!} class="file-loading">
														<input id="pictureField"
														       name="pictures[]"
														       type="file"
														       multiple
														       class="file picimg{{ $picturesError }}"
														>
													</div>
													<div class="form-text text-muted">
														@php
															$pictureHint = t('add_up_to_x_pictures_text', ['pictures_number' => $picturesLimit])
																. '<br>' . t('file_types', ['file_types' => getAllowedFileFormatsHint('image')]);
														@endphp
														{!! $pictureHint !!}
													</div>
												</div>
                                            </div>
                                        @endif
                                        <div id="uploadError" class="mt-2" style="display: none;"></div>
                                        <div id="uploadSuccess" class="alert alert-success fade show mt-2" style="display: none;"></div>
										
										{{-- button --}}
										<div class="input-group row mt-4">
											<div class="col-md-12 text-center">
												<a href="{{ $previousStepUrl }}" class="btn btn-default btn-lg">{{ $previousStepLabel }}</a>
												<a id="nextStepAction"
													href="{{ $nextStepUrl }}"
													class="btn btn-default btn-lg"
													onclick="this.className += ' disabled'; return true;"
												>{{ $nextStepLabel }}</a>
											</div>
										</div>
                                    
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.page-content -->
            </div>
        </div>
    </div>
@endsection

@section('after_styles')
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
@endsection

@php
	$postId = data_get($post, 'id');
	
	/* Get Upload Url */
	$uploadUrl = url('posts/' . data_get($post, 'id') . '/photos/');
	$uploadUrl = urlQuery($uploadUrl)->setParameters(request()->only(['packageId']))->toString();
@endphp

@section('after_scripts')
    <script src="{{ url('assets/plugins/bootstrap-fileinput/js/plugins/sortable.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/plugins/bootstrap-fileinput/js/fileinput.min.js') }}" type="text/javascript"></script>
	<script src="{{ url('assets/plugins/bootstrap-fileinput/themes/' . $fiTheme . '/theme.js') }}" type="text/javascript"></script>
	<script src="{{ url('common/js/fileinput/locales/' . config('app.locale') . '.js') }}" type="text/javascript"></script>
    
    @include('front.post.createOrEdit.multiSteps.inc.photos-alert-js')
    
    <script>
		let options = {};
		options.theme = '{{ $fiTheme }}';
		options.language = '{{ config('app.locale') }}';
		options.rtl = {{ (config('lang.direction') == 'rtl') ? 'true' : 'false' }};
		options.overwriteInitial = false;
		options.showCaption = false;
		options.showPreview = true;
		options.allowedFileExtensions = {!! $serverAllowedImageFormatsJson !!};
		options.uploadUrl = '{{ $uploadUrl }}';
		options.uploadAsync = false;
		options.showCancel = false;
		options.showUpload = false;
		options.showRemove = false;
		options.showBrowse = true;
		options.browseClass = 'btn btn-primary';
		options.minFileSize = {{ (int)config('settings.upload.min_image_size', 0) }};
		options.maxFileSize = {{ (int)config('settings.upload.max_image_size', 1000) }};
		options.browseOnZoneClick = true;
		options.minFileCount = 0;
		options.maxFileCount = {{ $picturesLimit }};
		options.validateInitialCount = true;
		options.initialPreview = [];
		options.initialPreviewAsData = true;
		options.initialPreviewFileType = 'image';
		options.initialPreviewConfig = [];
		options.fileActionSettings = {
			showRotate: false,
			showUpload: false,
			showDrag: true,
			showRemove: true,
			removeClass: 'btn btn-outline-danger btn-sm',
			showZoom: true,
			zoomClass: 'btn btn-outline-secondary btn-sm',
		};
		options.elErrorContainer = '#uploadError';
		options.msgErrorClass = 'alert alert-block alert-danger';
		
		@if (!empty($pictures))
			@foreach($pictures as $idx => $picture)
				@php
					$pictureId = data_get($picture, 'id');
					$pictureUrl = data_get($picture, 'url.medium');
					$deleteUrl = url('posts/' . $postId . '/photos/' . $pictureId . '/delete');
					$filePath = data_get($picture, 'file_path');
					try {
						$fileExists = (isset($disk) && !empty($filePath) && $disk->exists($filePath));
						$fileSize = $fileExists ? (int)$disk->size($filePath) : 0;
					} catch (\Throwable $e) {
						$fileSize = 0;
					}
				@endphp
				options.initialPreview[{{ $idx }}] = '{{ $pictureUrl }}';
				options.initialPreviewConfig[{{ $idx }}] = {};
				options.initialPreviewConfig[{{ $idx }}].key = {{ (int)($pictureId ?? $idx) }};
				options.initialPreviewConfig[{{ $idx }}].caption = '{{ basename($filePath) }}';
				options.initialPreviewConfig[{{ $idx }}].size = {{ $fileSize }};
				options.initialPreviewConfig[{{ $idx }}].url = '{{ $deleteUrl }}';
			@endforeach
		@endif
		
		onDocumentReady((event) => {
			{{-- fileinput --}}
			let pictureFieldEl = $('#pictureField');
			pictureFieldEl.fileinput(options);
			
			/* Reset the upload status message */
			pictureFieldEl.on('filebatchpreupload', function (event, data) {
				$('#uploadSuccess').html('<ul></ul>').hide();
			});
			
			/* Auto-upload files */
			pictureFieldEl.on('filebatchselected', function (event, files) {
				$(this).fileinput('upload');
			});
			
			/* Show the upload success message */
			pictureFieldEl.on('filebatchuploadsuccess', function (event, data) {
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
				let uploadSuccessEl = $('#uploadSuccess');
				uploadSuccessEl.find('ul').append(out);
				uploadSuccessEl.fadeIn('slow');
				
				/* Change button label */
				$('#nextStepAction').html('{{ $nextStepLabel }}').removeClass('btn-default').addClass('btn-primary');
			});
			
			/* Show upload error message */
			pictureFieldEl.on('filebatchuploaderror', function (event, data, msg) {
				showErrorMessage(msg);
			});
			
			/* Before deletion */
			pictureFieldEl.on('filepredelete', function (event, key, jqXHR, data) {
				let abort = true;
				if (confirm("{{ t('Are you sure you want to delete this picture') }}")) {
					abort = false;
				}
				
				return abort;
			});
			
			/* Show the deletion success message */
			pictureFieldEl.on('filedeleted', function (event, key, jqXHR, data) {
				/* Check local vars */
				if (typeof jqXHR.responseJSON === 'undefined') {
					return false;
				}
				
				let obj = jqXHR.responseJSON;
				if (typeof obj.status === 'undefined' || typeof obj.message === 'undefined') {
					return false;
				}
				
				/* Deletion Notification */
				if (parseInt(obj.status) === 1) {
					showSuccessMessage(obj.message);
				} else {
					showErrorMessage(obj.message);
				}
			});
			
			/* Show deletion error message */
			pictureFieldEl.on('filedeleteerror', function (event, data, msg) {
				showErrorMessage(msg);
			});
			
			/* Reorder (Sort) files */
			pictureFieldEl.on('filesorted', function (event, params) {
				reorderPictures(params);
			});
		});

		/**
		 * Reorder (Sort) pictures
		 * @param params
		 * @returns {boolean}
		 */
		function reorderPictures(params)
		{
			if (typeof params.stack === 'undefined') {
				return false;
			}
			
			waitingDialog.show('{{ t('Processing') }}...');
			
			let postId = '{{ request()->segment(2) }}';
			
			let ajax = $.ajax({
				method: 'POST',
				url: siteUrl + '/posts/' + postId + '/photos/reorder',
				data: {
					'params': params,
					'_token': $('input[name=_token]').val()
				}
			});
			ajax.done(function(data) {
				
				setTimeout(function() {
					waitingDialog.hide();
				}, 250);
				
				if (typeof data.status === 'undefined') {
					return false;
				}
				
				/* Reorder Notification */
				if (parseInt(data.status) === 1) {
					showSuccessMessage(data.message);
				} else {
					showErrorMessage(data.message);
				}
				
				return false;
			});
			ajax.fail(function (xhr, textStatus, errorThrown) {
				let message = getErrorMessageFromXhr(xhr);
				if (message !== null) {
					showErrorMessage(message);
				}
			});
			
			return false;
		}
    </script>
    
@endsection
