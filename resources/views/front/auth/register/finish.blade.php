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

@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container">
			<div class="row">

				@if (session()->has('flash_notification'))
					<div class="col-12">
						<div class="row">
							<div class="col-12">
								@include('flash::message')
							</div>
						</div>
					</div>
				@endif

				<div class="col-md-12 page-content">

					@if (session()->has('message'))
						<div class="inner-box">
							<div class="row">
								<div class="col-12">
									<div class="alert alert-success pgray alert-lg mb-0" role="alert">
										<h2 class="no-padding mb20">&#10004; {{ t('congratulations') }}</h2>
										<p class="mb-0">
											{{ session('message') }} <a href="{{ url('/') }}">{{ t('Homepage') }}</a>
										</p>
									</div>
								</div>
							</div>
						</div>
					@endif

				</div>
			</div>
		</div>
	</div>
@endsection
@php
	if (!session()->has('emailVerificationSent') && !session()->has('phoneVerificationSent')) {
		if (session()->has('message')) {
			session()->forget('message');
		}
	}
@endphp
