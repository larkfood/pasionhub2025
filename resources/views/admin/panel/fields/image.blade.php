@php
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Number;
	
	$field ??= [];
@endphp
<div data-preview="#{{ $field['name'] }}"
	data-aspectRatio="{{ $field['aspect_ratio'] ?? 0 }}"
	data-crop="{{ $field['crop'] ?? false }}"
	@include('admin.panel.inc.field_wrapper_attributes')
>
	<div class="col-12 text-center p-2 border border-1 border-light rounded-2" style="height: 100%;">
		
		<div class="text-start">
			<label class="form-label fw-bolder">{!! $field['label'] !!}</label>
			@include('admin.panel.fields.inc.translatable_icon')
		</div>
		{{-- Wrap the image or canvas element with a block element (container) --}}
		<div class="row d-flex justify-content-center mt-3 mb-3">
			<div class="col-sm-6 text-center">
				@php
					$diskName = $field['disk'] ?? null;
					
					// Get default picture & its URL
					$defaultPicture = config('larapen.media.picture');
					$defaultPictureUrl = Storage::disk($diskName)->url($defaultPicture);
					
					// Get default value (Need to be sent/filled as URL)
					$defaultValue = $field['default'] ?? null;
					$defaultValue = (!empty($defaultValue) && is_string($defaultValue)) ? $defaultValue : $defaultPictureUrl;
					
					// Get value (Sent/filled as storage path)
					$value = $field['value'] ?? null;
					
					// Get the picture's URL
					$pictureUrl = (!empty($value) && is_string($value)) ? Storage::disk($diskName)->url($value) : $defaultValue;
					// $pictureUrl = is_string($value) ? thumbParam($value)->setOption('picture-md')->url() : $defaultValue;
					$pictureUrl = old($field['name'], $pictureUrl);
					
					// Get the picture's URL with prefix path (If filled)
					$prefix = $field['prefix'] ?? '';
					$pictureUrl = $prefix . $pictureUrl;
					
					// Get picture display dimensions
					$width = $field['width'] ?? 'auto';
					$height = $field['height'] ?? 'auto';
					if (is_numeric($width)) {
						$width = Number::clamp($width, min: 100, max: 800);
					}
					if (is_numeric($height)) {
						$height = Number::clamp($height, min: 100, max: 800);
					}
					$width = is_numeric($width) ? $width . 'px' : $width;
					$height = is_numeric($height) ? $height . 'px' : $height;
					
					// Dimensions style
					$dimensionStyle = (str_ends_with($width, 'px') ? 'max-' : '')  . 'width:' . $width . ';';
					$dimensionStyle .= (str_ends_with($height, 'px') ? 'max-' : '') . 'height:' . $height . ';';
				@endphp
				<img id="mainImage" class="rounded" src="{{ url($pictureUrl) }}" style="{!! $dimensionStyle !!}">
			</div>
			@if (isset($field['crop']) && $field['crop'])
				<div class="col-sm-3 text-center">
					<div class="docs-preview clearfix">
						<div id="{{ $field['name'] }}" class="img-preview preview-lg">
							<img src=""
								 style="display: block; min-width: 0 !important; min-height: 0 !important; max-width: none !important; max-height: none !important; margin-left: -32.875px; margin-top: -18.4922px; transform: none;"
							>
						</div>
					</div>
				</div>
			@endif
		</div>
		
		<div class="btn-group">
			<label class="btn btn-primary btn-file mb-0">
				{{ trans('admin.choose_file') }}
				<input type="file"
                        accept="image/*"
                        id="uploadImage" @include('admin.panel.inc.field_attributes', ['default_class' => 'hide'])
				>
				<input type="hidden" id="hiddenImage" name="{{ $field['name'] }}">
			</label>
			@if (isset($field['crop']) && $field['crop'])
				<button class="btn btn-secondary" id="rotateLeft" type="button" style="display: none;"><i class="fa-solid fa-rotate-left"></i></button>
				<button class="btn btn-secondary" id="rotateRight" type="button" style="display: none;"><i class="fa-solid fa-rotate-right"></i></button>
				<button class="btn btn-secondary" id="zoomIn" type="button" style="display: none;"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
				<button class="btn btn-secondary" id="zoomOut" type="button" style="display: none;"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
				<button class="btn btn-warning" id="reset" type="button" style="display: none;"><i class="fa-solid fa-xmark"></i></button>
			@endif
			<button class="btn btn-danger" id="remove" type="button"><i class="fa-regular fa-trash-can"></i></button>
		</div>
		
		{{-- HINT --}}
		@if (isset($field['hint']))
			<br>
			<div class="form-text">{!! $field['hint'] !!}</div>
		@endif
		
	</div>
</div>


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($xPanel->checkIfFieldIsFirstOfItsType($field, $fields))
    
    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    {{-- YOUR CSS HERE --}}
    <link href="{{ asset('assets/plugins/cropper/dist/cropper.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .hide {
            display: none;
        }
        .image .btn-group {
            margin-top: 10px;
        }
        img {
            max-width: 100%; /* This rule is very important, please do not ignore this! */
        }
        .img-container, .img-preview {
            width: 100%;
            text-align: center;
        }
        .img-preview {
            float: left;
            margin-right: 10px;
            margin-bottom: 10px;
            overflow: hidden;
        }
        .preview-lg {
            width: 263px;
            height: 148px;
        }
        
        .btn-file {
            position: relative;
            overflow: hidden;
        }
        .btn-file input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: inherit;
            display: block;
        }
    </style>
    @endpush
    
    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    {{-- YOUR JS HERE --}}
    <script src="{{ asset('assets/plugins/cropper/dist/cropper.min.js') }}"></script>
    <script>
	    onDocumentReady((event) => {
			// Loop through all instances of the image field
			$('form div.image').each(function(index){
				// Find DOM elements under this form-group element
				var $mainImage = $(this).find('#mainImage');
				var $uploadImage = $(this).find("#uploadImage");
				var $hiddenImage = $(this).find("#hiddenImage");
				var $rotateLeft = $(this).find("#rotateLeft")
				var $rotateRight = $(this).find("#rotateRight")
				var $zoomIn = $(this).find("#zoomIn")
				var $zoomOut = $(this).find("#zoomOut")
				var $reset = $(this).find("#reset")
				var $remove = $(this).find("#remove")
				// Options either global for all image type fields, or use 'data-*' elements for options passed in via the CRUD controller
				var options = {
					viewMode: 2,
					checkOrientation: false,
					autoCropArea: 1,
					responsive: true,
					preview : $(this).attr('data-preview'),
					aspectRatio : $(this).attr('data-aspectRatio')
				};
				var crop = $(this).attr('data-crop');
				
				// Hide 'Remove' button if there is no image saved
				if (!$mainImage.attr('src')){
					$remove.hide();
				}
				// Initialise hidden form input in case we submit with no change
				$hiddenImage.val($mainImage.attr('src'));
				
				
				// Only initialize cropper plugin if crop is set to true
				if(crop){
					
					$remove.click(function() {
						$mainImage.cropper("destroy");
						$mainImage.attr('src','');
						$hiddenImage.val('');
						$rotateLeft.hide();
						$rotateRight.hide();
						$zoomIn.hide();
						$zoomOut.hide();
						$reset.hide();
						$remove.hide();
					});
				} else {
					
					$(this).find("#remove").click(function() {
						$mainImage.attr('src','');
						$hiddenImage.val('');
						$remove.hide();
					});
				}
				
				$uploadImage.change(function() {
					var fileReader = new FileReader(),
						files = this.files,
						file;
					
					if (!files.length) {
						return;
					}
					file = files[0];
					
					if (/^image\/\w+$/.test(file.type)) {
						fileReader.readAsDataURL(file);
						fileReader.onload = function () {
							$uploadImage.val("");
							if(crop){
								$mainImage.cropper(options).cropper("reset", true).cropper("replace", this.result);
								// Override form submit to copy canvas to hidden input before submitting
								$('form').submit(function() {
									var imageURL = $mainImage.cropper('getCroppedCanvas').toDataURL(file.type);
									$hiddenImage.val(imageURL);
									return true; // return false to cancel form action
								});
								$rotateLeft.click(function() {
									$mainImage.cropper("rotate", 90);
								});
								$rotateRight.click(function() {
									$mainImage.cropper("rotate", -90);
								});
								$zoomIn.click(function() {
									$mainImage.cropper("zoom", 0.1);
								});
								$zoomOut.click(function() {
									$mainImage.cropper("zoom", -0.1);
								});
								$reset.click(function() {
									$mainImage.cropper("reset");
								});
								$rotateLeft.show();
								$rotateRight.show();
								$zoomIn.show();
								$zoomOut.show();
								$reset.show();
								$remove.show();
								
							} else {
								$mainImage.attr('src',this.result);
								$hiddenImage.val(this.result);
								$remove.show();
							}
						};
					} else {
						alert("Please choose an image file.");
					}
				});
				
			});
		});
    </script>
    @endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
