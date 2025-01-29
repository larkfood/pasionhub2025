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
@php
	$htmlLang = getLangTag(config('app.locale'));
	$htmlDir = (config('lang.direction') == 'rtl') ? ' dir="rtl"' : '';
	$htmlTheme = isDarkModeEnabledForCurrentUser() ? ' theme="dark"' : '';
	
	$plugins = array_keys((array)config('plugins'));
@endphp
<!DOCTYPE html>
<html lang="{{ $htmlLang }}"{!! $htmlDir . $htmlTheme !!}>
<head>
	<meta charset="{{ config('larapen.core.charset', 'utf-8') }}">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	@include('front.common.meta-robots')
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="{{ config('settings.app.favicon_url') }}">
	<title>{!! MetaTag::get('title') !!}</title>
	{!! MetaTag::tag('description') !!}{!! MetaTag::tag('keywords') !!}
	<link rel="canonical" href="{{ request()->fullUrl() }}"/>
	{{-- Specify a default target for all hyperlinks and forms on the page --}}
	<base target="_top"/>
	@if (isset($post))
		@if (isVerifiedPost($post))
			@if (config('services.facebook.client_id'))
				<meta property="fb:app_id" content="{{ config('services.facebook.client_id') }}" />
			@endif
			{!! $og->renderTags() !!}
			{!! MetaTag::twitterCard() !!}
		@endif
	@else
		@if (config('services.facebook.client_id'))
			<meta property="fb:app_id" content="{{ config('services.facebook.client_id') }}" />
		@endif
		{!! $og->renderTags() !!}
		{!! MetaTag::twitterCard() !!}
	@endif
	@include('feed::links')
	{!! seoSiteVerification() !!}
	
	@if (file_exists(public_path('manifest.json')))
		<link rel="manifest" href="{{ url()->asset('manifest.json') }}">
	@endif
	
	@stack('before_styles_stack')
    @yield('before_styles')
	
	{{-- App CSS files (Handled by Mix) --}}
	@if (config('lang.direction') == 'rtl')
		<link href="https://fonts.googleapis.com/css?family=Cairo|Changa" rel="stylesheet">
		<link href="{{ url(mix('dist/front/styles.rtl.css')) }}" rel="stylesheet">
	@else
		<link href="{{ url(mix('dist/front/styles.css')) }}" rel="stylesheet">
	@endif
	
	{{-- AdsBlocker Plugin CSS --}}
	@if (config('plugins.detectadsblocker.installed'))
		<link href="{{ url('plugins/detectadsblocker/assets/css/style.css') . getPictureVersion() }}" rel="stylesheet">
	@endif
	
	{{-- Generated CSS from Settings (Handled by FileController) --}}
	@php
		$skinQs = request()->filled('skin') ? '?skin=' . request()->query('skin') : null;
		if (request()->filled('display')) {
			$skinQs .= !empty($skinQs) ? '&' : '?';
			$skinQs .= 'display=' . request()->query('display');
		}
		$styleCssUrl = url('common/css/style.css') . $skinQs . getPictureVersion(!empty($skinQs));
	@endphp
	<link href="{{ $styleCssUrl }}" rel="stylesheet">
	
	{{-- Generated CSS from Home Section --}}
	@php
		$homeStyle = '';
		if (isset($searchFormOptions) && is_array($searchFormOptions)) {
			$homeStyle = view('front.common.css.homepage', ['searchFormOptions', $searchFormOptions])->render();
		}
	@endphp
	{!! $homeStyle !!}
	
	{{-- Custom CSS --}}
	<link href="{{ url()->asset('dist/front/custom.css') . getPictureVersion() }}" rel="stylesheet">
	
	@stack('after_styles_stack')
    @yield('after_styles')
	
	@if (!empty($plugins))
		@foreach($plugins as $plugin)
			@yield($plugin . '_styles')
		@endforeach
	@endif
    
    @if (config('settings.style.custom_css'))
		{!! printCss(config('settings.style.custom_css')) . "\n" !!}
    @endif
	
	@if (config('settings.other.js_code'))
		{!! printJs(config('settings.other.js_code')) . "\n" !!}
	@endif
	
	@include('front.common.js.document')
	
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->
 
	<script>
		paceOptions = {
			elements: true
		};
	</script>
	<script src="{{ url()->asset('assets/plugins/pace/0.4.17/pace.min.js') }}"></script>
	<script src="{{ url()->asset('assets/plugins/modernizr/modernizr-custom.js') }}"></script>
	
	@yield('captcha_head')
	@section('recaptcha_head')
		@php
			$captcha = config('settings.security.captcha');
			$reCaptchaVersion = config('recaptcha.version', 'v2');
			$isReCaptchaEnabled = (
				$captcha == 'recaptcha'
				&& !empty(config('recaptcha.site_key'))
				&& !empty(config('recaptcha.secret_key'))
				&& in_array($reCaptchaVersion, ['v2', 'v3'])
			);
		@endphp
		@if ($isReCaptchaEnabled)
			<style>
				.is-invalid .g-recaptcha iframe,
				.has-error .g-recaptcha iframe {
					border: 1px solid #f85359;
				}
			</style>
			@if ($reCaptchaVersion == 'v3')
				<script type="text/javascript">
					function myCustomValidation(token){
						/* read HTTP status */
						/* console.log(token); */
						let gRecaptchaResponseEl = $('#gRecaptchaResponse');
						if (gRecaptchaResponseEl.length) {
							gRecaptchaResponseEl.val(token);
						}
					}
				</script>
				{!! recaptchaApiV3JsScriptTag([
					'action' 		    => request()->path(),
					'custom_validation' => 'myCustomValidation'
				]) !!}
			@else
				{!! recaptchaApiJsScriptTag() !!}
			@endif
		@endif
	@show
</head>
<body class="skin">
<div id="wrapper">
	
	@section('header')
		@include('front.layouts.inc.header')
	@show
	
	@section('search')
	@show
	
	@section('wizard')
	@show
	
	@if (isset($siteCountryInfo))
		<div class="p-0 mt-lg-4 mt-md-3 mt-3"></div>
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="alert alert-warning alert-dismissible mb-3">
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ t('Close') }}"></button>
						{!! $siteCountryInfo !!}
					</div>
				</div>
			</div>
		</div>
	@endif
	
	@yield('content')
	
	@section('info')
	@show
	
	@include('front.layouts.inc.advertising.auto')
	
	@section('footer')
		@include('front.layouts.inc.footer')
	@show
	
</div>

@section('modal_location')
@show
@section('modal_languages')
@show
@section('modal_abuse')
@show
@section('modal_message')
@show

@includeWhen(!auth()->check(), 'front.auth.login.inc.modal')
@include('front.layouts.inc.modal.countries')
@include('front.layouts.inc.modal.error')
@include('cookie-consent::index')

@if (config('plugins.detectadsblocker.installed'))
	@if (view()->exists('detectadsblocker::modal'))
		@include('detectadsblocker::modal')
	@endif
@endif

@include('front.common.js.init')

<script>
	var countryCode = '{{ config('country.code', 0)  }}';
	var timerNewMessagesChecking = {{ (int)config('settings.other.timer_new_messages_checking', 0)  }};
	
	{{-- Dark Mode --}}
	var isSettingsAppDarkModeEnabled = {{ isSettingsAppDarkModeEnabled() ? 'true' : 'false' }};
	var isDarkModeEnabledForCurrentUser = {{ isDarkModeEnabledForCurrentUser() ? 'true' : 'false' }};
	var isDarkModeEnabledForCurrentDevice = {{ isDarkModeEnabledForCurrentDevice() ? 'true' : 'false' }};
	
	{{-- The app's default auth field --}}
	var defaultAuthField = '{{ old('auth_field', getAuthField()) }}';
	var phoneCountry = '{{ config('country.code') }}';
	
	{{-- Others global variables --}}
	var fakeLocationsResults = "{{ config('settings.listings_list.fake_locations_results', 0) }}";
</script>

@stack('before_scripts_stack')
@yield('before_scripts')

{{-- Country List for Intl Tel Input --}}
<script src="{{ url('common/js/intl-tel-input/countries.js') . getPictureVersion() }}"></script>

{{-- App JS files (Handled by Mix) --}}
<script src="{{ url(mix('dist/front/scripts.js')) }}"></script>

{{-- Lazy Loading JS --}}
@if (config('settings.optimization.lazy_loading_activation') == 1)
	<script src="{{ url()->asset('assets/plugins/lazysizes/lazysizes.min.js') }}" async=""></script>
@endif

{{-- Select2 Locale File --}}
@php
	$select2LangFilePath = 'assets/plugins/select2/js/i18n/' . config('app.locale') . '.js';
@endphp
@if (file_exists(public_path($select2LangFilePath)))
	<script src="{{ url()->asset($select2LangFilePath) }}"></script>
@endif

{{-- AdsBlocker Plugin JS --}}
@if (config('plugins.detectadsblocker.installed'))
	<script src="{{ url('plugins/detectadsblocker/assets/js/script.js') . getPictureVersion() }}"></script>
@endif

<script>
	onDocumentReady((event) => {
		{{-- Searchable Select Boxes --}}
		let largeDataSelect2Params = {
			width: '100%',
			dropdownAutoWidth: 'true'
		};
		{{-- Simple Select Boxes --}}
		let select2Params = {...largeDataSelect2Params};
		{{-- Hiding the search box --}}
		select2Params.minimumResultsForSearch = Infinity;
		
		if (typeof langLayout !== 'undefined' && typeof langLayout.select2 !== 'undefined') {
			select2Params.language = langLayout.select2;
			largeDataSelect2Params.language = langLayout.select2;
		}
		
		$('.selecter').select2(select2Params);
		$('.large-data-selecter').select2(largeDataSelect2Params);
		
		{{-- Social Media Share --}}
		SocialShare.init({width: 640, height: 480});
		
		{{-- Modal Login --}}
		@if (isset($errors) && $errors->any())
			@if ($errors->any() && old('quickLoginForm')=='1')
				{{-- Re-open the modal if error occured --}}
				openLoginModal();
			@endif
		@endif
		
		{{-- Reorder the modal country list --}}
		const modalCountryListReorder = new BsRowColumnsReorder('#modalCountryList', {defaultColumns: 4});
	});
</script>

@stack('after_scripts_stack')
@yield('after_scripts')
@yield('captcha_footer')

@if (!empty($plugins))
	@foreach($plugins as $plugin)
		@yield($plugin . '_scripts')
	@endforeach
@endif

@if (config('settings.footer.tracking_code'))
	{!! printJs(config('settings.footer.tracking_code')) . "\n" !!}
@endif
</body>
</html>
