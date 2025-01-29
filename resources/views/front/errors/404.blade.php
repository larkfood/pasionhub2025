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
@extends('front.errors.layouts.master')

@php
	// Get page error title
	$titleKey = 'global.error_http_404_title';
	$title = trans($titleKey);
	if ($title === $titleKey) {
		$title = 'Page not found';
	}
	
	// Get page error message
	$messageKey = 'global.error_http_404_message';
	$message = trans($messageKey, ['url' => url('/')]);
	if ($message === $messageKey) {
		if (isset($exception) && $exception instanceof \Throwable) {
			$message = $exception->getMessage();
			$message = str_replace(base_path(), '', $message);
		}
	}
@endphp

@section('title', $title)

@section('search')
	@parent
	@include('front.errors.layouts.inc.search')
@endsection

@section('content')
	@include('front.common.spacer')
	<div class="main-container inner-page">
		<div class="container">
			<div class="section-content">
				<div class="row">

					<div class="col-md-12 page-content">
						
						<div class="error-page mt-5 mb-5 ms-0 me-0 pt-5">
							<h1 class="headline text-center" style="font-size: 180px;">404</h1>
							<div class="text-center mt-5">
								<h3 class="m-t-0 color-danger">
									<i class="fa-solid fa-triangle-exclamation"></i> {{ $title }}
								</h3>
								<p>
									{!! $message !!}
								</p>
							</div>
						</div>
						
					</div>

				</div>
			</div>
		</div>
	</div>
@endsection
