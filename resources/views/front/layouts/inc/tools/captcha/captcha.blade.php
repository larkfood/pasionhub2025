@php
	$captcha = config('settings.security.captcha');
	$isSimpleCaptchaEnabled = (
		in_array($captcha, ['default', 'math', 'flat', 'mini', 'inverse', 'custom'])
		&& !empty(config('captcha.option'))
	);
	
	$hideClass = 'd-none';
@endphp
@if ($isSimpleCaptchaEnabled)
	@php
		$prefix = isAdminPanel() ? config('larapen.admin.route', 'admin') . '/' : '';
		$captchaUrl = captcha_src(config('settings.security.captcha'));
		$captchaReloadUrl = url($prefix . 'captcha/' . config('settings.security.captcha'));
		$blankImage = url('images/blank.gif');
		
		$captchaImage = '<img src="' . $blankImage . '" style="cursor: pointer;">';
		$captchaHint = '<div class="form-text text-muted ' . $hideClass . '" style="margin-bottom: 2px;">' . t('captcha_hint') . '</div>';
		$captchaWidth = config('captcha.' . config('settings.security.captcha') . '.width', 150);
		$styleCss = ' style="width: ' . $captchaWidth . 'px;"';
		
		$captchaReloadBtn = '<a rel="nofollow" href="javascript:;" class="' . $hideClass . '" title="' . t('captcha_reload_hint') . '">';
		$captchaReloadBtn .= '<button type="button" class="btn btn-primary btn-refresh"><i class="fa-solid fa-rotate"></i></button>';
		$captchaReloadBtn .= '</a>';
		
		// DEBUG
		// The generated key need to be un-hashed before to be stored in session
		// dump(session('captcha.key'));
	@endphp
	@if (isAdminPanel())
		
		@php
			$captchaDivError = (isset($errors) && $errors->has('captcha')) ? ' has-danger' : '';
			$captchaError = (isset($errors) && $errors->has('captcha')) ? ' form-control-danger' : '';
			$captchaField = '<input type="text" name="captcha" autocomplete="off" class="' . $hideClass . ' form-control' . $captchaError . '"' . $styleCss . '>';
		@endphp
		
		<div class="captcha-div form-group mb-3 required{{ $captchaDivError }}">
			<div class="no-label">
				{!! $captchaReloadBtn !!}
				{!! $captchaHint !!}
				{!! $captchaField !!}
			</div>
			
			@if (isset($errors) && $errors->has('captcha'))
				<div class="invalid-feedback {{ $hideClass }}">{{ $errors->first('captcha') }}</div>
			@endif
		</div>
		
	@else
		
		@php
			$captchaError = (isset($errors) && $errors->has('captcha')) ? ' is-invalid' : '';
			$captchaField = '<input type="text" name="captcha" autocomplete="off" class="' . $hideClass . ' form-control' . $captchaError . '"' . $styleCss . '>';
		@endphp
		
		@if (isset($colLeft) && isset($colRight))
			<div class="captcha-div row mb-3 required{{ $captchaError }}">
				<label class="{{ $colLeft }} col-form-label {{ $hideClass }}" for="captcha">
					@if (isset($label) && $label == true)
						{{ t('captcha_label') }}
					@endif
				</label>
				<div class="{{ $colRight }}">
					{!! $captchaReloadBtn !!}
					{!! $captchaHint !!}
					{!! $captchaField !!}
				</div>
			</div>
		@else
			@if (isset($label) && $label == true)
				<div class="captcha-div row mb-3 required{{ $captchaError }}">
					<label class="control-label {{ $hideClass }}" for="captcha">{{ t('captcha_label') }}</label>
					<div>
						{!! $captchaReloadBtn !!}
						{!! $captchaHint !!}
						{!! $captchaField !!}
					</div>
				</div>
			@elseif (isset($noLabel) && $noLabel == true)
				<div class="captcha-div row mb-3 required{{ $captchaError }}">
					<div class="no-label">
						{!! $captchaReloadBtn !!}
						{!! $captchaHint !!}
						{!! $captchaField !!}
					</div>
				</div>
			@else
				<div class="captcha-div row mb-3 required{{ $captchaError }}">
					<div>
						{!! $captchaReloadBtn !!}
						{!! $captchaHint !!}
						{!! $captchaField !!}
					</div>
				</div>
			@endif
		@endif
		
	@endif
@endif

@section('captcha_head')
@endsection

@section('captcha_footer')
	@if ($isSimpleCaptchaEnabled)
		@php
			$captchaDelay = (int)config('settings.security.captcha_delay', 1000);
		@endphp
		<script>
			let captchaImage = '{!! $captchaImage !!}';
			let captchaUrl = '{{ $captchaReloadUrl }}';
			let hideClass = '{{ trim($hideClass) }}';
			
			onDocumentReady((event) => {
				/* Load the captcha image */
				{{--
				 * Load the captcha image N ms after the page is loaded
				 *
				 * Admin panel: 0ms
				 * Front:
				 * Chrome: 600ms
				 * Edge: 600ms
				 * Safari: 500ms
				 * Firefox: 100ms
				--}}
				let stTimeout = {{ $captchaDelay }};
				setTimeout(() => loadCaptchaImage(captchaImage, captchaUrl), stTimeout);
				
				/*
				 * Handle captcha image click
				 * Reload the captcha image on by clicking on it
				 */
				onDomElementsAdded('.captcha-div img', (elements) => {
					if (elements.length <= 0) {
						return false;
					}
					elements.forEach((element) => {
						element.addEventListener('click', (e) => {
							e.preventDefault();
							reloadCaptchaImage(e.target, captchaUrl);
						});
					});
				});
				
				/*
				 * Handle captcha reload link click
				 * Reload the captcha image on by clicking on the reload link
				 */
				const captchaLinkEl = document.querySelector('.captcha-div a');
				captchaLinkEl.addEventListener('click', (e) => {
					e.preventDefault();
					
					const captchaImage = document.querySelector('.captcha-div img');
					if (captchaImage) {
						reloadCaptchaImage(captchaImage, captchaUrl);
					}
				});
			});
			
			function loadCaptchaImage(captchaImage, captchaUrl) {
				captchaUrl = getTimestampedUrl(captchaUrl);
				
				captchaImage = captchaImage.replace(/src="[^"]*"/gi, 'src="' + captchaUrl + '"');
				
				/* Remove existing <img> */
				let captchaImageEls = document.querySelectorAll('.captcha-div img');
				if (captchaImageEls.length > 0) {
					captchaImageEls.forEach((element) => element.remove());
				}
				
				/* Add the <img> tag in the DOM */
				let captchaDivEls = document.querySelectorAll('.captcha-div > div');
				if (captchaDivEls.length > 0) {
					captchaDivEls.forEach((element) => element.insertAdjacentHTML('afterbegin', captchaImage));
				}
				
				/* Show the captcha's div only when the image src is fully loaded */
				let newCaptchaImageEls = document.querySelectorAll('.captcha-div img');
				if (newCaptchaImageEls.length > 0) {
					newCaptchaImageEls.forEach((element) => {
						element.addEventListener('load', () => {
							const captchaSelectors = [
								'.captcha-div label',
								'.captcha-div a',
								'.captcha-div div',
								'.captcha-div small',
								'.captcha-div input'
							];
							toggleElementsClass(captchaSelectors, 'remove', hideClass);
						});
						
						element.addEventListener('error', () => {
							console.error('Error loading captcha image');
						});
					});
				}
			}
			
			function reloadCaptchaImage(captchaImageEl, captchaUrl) {
				captchaUrl = getTimestampedUrl(captchaUrl);
				captchaImageEl.src = captchaUrl;
			}
			
			function getTimestampedUrl(captchaUrl) {
				if (captchaUrl.indexOf('?') !== -1) {
					return captchaUrl;
				}
				
				let timestamp = new Date().getTime();
				let queryString = '?t=' + timestamp;
				captchaUrl = captchaUrl + queryString;
				
				return captchaUrl;
			}
		</script>
	@endif
@endsection
