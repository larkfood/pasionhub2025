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

@section('search')
	@parent
	@include('front.pages.contact.intro')
@endsection

@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container">
			<div class="row clearfix">
				
				@if (isset($errors) && $errors->any())
					<div class="col-12">
						<div class="alert alert-danger alert-dismissible">
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ t('Close') }}"></button>
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
				
				<div class="col-md-12">
					<div class="contact-form">
						<h5 class="list-title gray mt-0">
							<strong>{{ t('Contact Us') }}</strong>
						</h5>
						
						<form class="form-horizontal needs-validation" method="post" action="{{ urlGen()->contact() }}">
							{!! csrf_field() !!}
							@honeypot
							<fieldset>
								<div class="row">
									<div class="col-md-6 mb-3">
										@php
											$firstNameError = (isset($errors) && $errors->has('first_name')) ? ' is-invalid' : '';
										@endphp
										<div class="form-floating required">
											<input id="first_name" name="first_name" type="text" placeholder="{{ t('first_name') }}"
												   class="form-control{{ $firstNameError }}" value="{{ old('first_name') }}">
											<label for="first_name">{{ t('first_name') }}</label>
										</div>
									</div>

									<div class="col-md-6 mb-3">
										@php
											$lastNameError = (isset($errors) && $errors->has('last_name')) ? ' is-invalid' : '';
										@endphp
										<div class="form-floating required">
											<input id="last_name" name="last_name" type="text" placeholder="{{ t('last_name') }}"
												   class="form-control{{ $lastNameError }}" value="{{ old('last_name') }}">
											<label for="last_name">{{ t('last_name') }}</label>
										</div>
									</div>

									<div class="col-md-6 mb-3">
										@php
											$companyNameError = (isset($errors) && $errors->has('company_name')) ? ' is-invalid' : '';
										@endphp
										<div class="form-floating">
											<input id="company_name" name="company_name" type="text" placeholder="{{ t('company_name') }}"
												   class="form-control{{ $companyNameError }}" value="{{ old('company_name') }}">
											<label for="company_name">{{ t('company_name') }}</label>
										</div>
									</div>

									<div class="col-md-6 mb-3">
										@php
											$emailError = (isset($errors) && $errors->has('email')) ? ' is-invalid' : '';
										@endphp
										<div class="form-floating required">
											<input id="email"
											       name="email"
											       type="text"
											       data-valid-type="email"
											       placeholder="{{ t('email_address') }}"
											       class="form-control vEmail{{ $emailError }}"
												   value="{{ old('email') }}"
											>
											<label for="email">{{ t('email_address') }}</label>
										</div>
									</div>

									<div class="col-md-12 mb-3">
										@php
											$messageError = (isset($errors) && $errors->has('message')) ? ' is-invalid' : '';
										@endphp
										<div class="form-floating required">
											<textarea class="form-control{{ $messageError }}" id="message" name="message" placeholder="{{ t('Message') }}"
													  rows="7" style="height: 150px">{{ old('message') }}</textarea>
											<label for="message">{{ t('Message') }}</label>
										</div>
									</div>
									
									<div class="col-md-12">
										@include('front.layouts.inc.tools.captcha')
									</div>
									
									<div class="col-md-12 mb-3">
										<button type="submit" class="btn btn-primary btn-lg">{{ t('submit') }}</button>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
	<script>
		onDocumentReady((event) => {
			formValidate("form", formValidateOptions);
		});
	</script>
@endsection
