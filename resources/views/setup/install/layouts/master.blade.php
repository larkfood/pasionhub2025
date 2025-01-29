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
<!DOCTYPE html>
<html lang="{{ getLangTag(config('app.locale', 'en')) }}">
<head>
	<meta charset="{{ config('larapen.core.charset', 'utf-8') }}">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex,nofollow"/>
	<meta name="googlebot" content="noindex">
	<title>@yield('title')</title>
	
	@yield('before_styles')
	
	<link href="{{ url(mix('dist/front/styles.css')) }}" rel="stylesheet">
	
	@yield('after_styles')
	
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
</head>
<body>
<div id="wrapper">
	
	@section('header')
		@include('setup.install.layouts.inc.header')
	@show
	
	<div class="main-container">
		<div class="container">
			<div class="row">
				<div class="col-sm-12 col-md-12 col-xl-12 mb-lg-4 mb-md-3 mb-3">
					<h1 class="text-center title-1 fw-bold mt-5 mb-3" style="text-transform: none;">
						{{ trans('messages.installer') }}
					</h1>
					
					@include('setup.install.layouts.inc._steps')
				</div>
				
				@if (isset($errors) && $errors->any())
					<div class="col-12">
						<div class="alert alert-danger">
							<h5><strong>{{ t('validation_errors_title') }}</strong></h5>
							<ul class="list list-check">
								@foreach ($errors->all() as $error)
									<li>{!! $error !!}</li>
								@endforeach
							</ul>
						</div>
					</div>
				@endif
				
				@if (session()->has('flash_notification'))
					<div class="col-12">
						<div class="row">
							<div class="col-12">
								@include('flash::message')
							</div>
						</div>
					</div>
				@endif
			</div>
		</div>
		
		<div class="container" style="min-height: 150px;">
			<div class="row">
				<div class="col-sm-12 col-md-12 col-xl-12">
					<div class="inner-box">
						@yield('content')
					</div>
				</div>
			</div>
		</div>
	</div>
	
	@section('footer')
		@include('setup.install.layouts.inc.footer')
	@show
	
</div>

@yield('before_scripts')

<script>
	/* Init. vars */
	var siteUrl = '{{ url('/') }}';
	var languageCode = '{{ config('app.locale') }}';
	var countryCode = '{{ config('country.code', 0) }}';
	
	/* Init. Translation vars */
	var langLayout = {
		'hideMaxListItems': {
			'moreText': "{{ t('View More') }}",
			'lessText': "{{ t('View Less') }}"
		}
	};
</script>

<script src="{{ url(mix('dist/front/scripts.js')) }}"></script>

@php
	$select2LangFilePath = 'assets/plugins/select2/js/i18n/' . config('app.locale') . '.js';
@endphp
@if (file_exists(public_path($select2LangFilePath)))
	<script src="{{ url($select2LangFilePath) }}"></script>
@endif

<script>
	onDocumentReady((event) => {
		{{-- Select Boxes --}}
		$('.selecter').select2({
			language: '{{ config('app.locale', 'en') }}',
			dropdownAutoWidth: 'true'
		});
	});
</script>

@yield('after_scripts')

</body>
</html>
