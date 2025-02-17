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
				
				@if (isset($errors) && $errors->any())
					<div class="col-12">
						<div class="alert alert-danger alert-dismissible">
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ t('Close') }}"></button>
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
						@include('flash::message')
					</div>
				@endif
				
				@include('front.auth.login.inc.social', ['boxedCol' => 8])
				
				@php
					$mtAuth = !isSocialAuthEnabled() ? ' mt-2' : ' mt-1';
				@endphp
				<div class="col-lg-5 col-md-8 col-sm-10 col-12 login-box{{ $mtAuth }}">
					<form id="loginForm" role="form" method="POST" action="{{ url()->current() }}">
						{!! csrf_field() !!}
						@honeypot
						<input type="hidden" name="country" value="{{ config('country.code') }}">
						<div class="card card-default">
							
							<div class="panel-intro">
								<div class="d-flex justify-content-center">
									<h2 class="logo-title"><strong>{{ t('log_in') }}</strong></h2>
								</div>
							</div>
							
							<div class="card-body px-4">
								{{-- email --}}
								@php
									$emailError = (isset($errors) && $errors->has('email')) ? ' is-invalid' : '';
									$emailValue = (session()->has('email')) ? session('email') : old('email');
								@endphp
								<div class="mb-3 auth-field-item">
									<div class="row">
										@php
											$col = (config('settings.sms.enable_phone_as_auth_field') == '1') ? 'col-6' : 'col-12';
										@endphp
										<label class="form-label {{ $col }} m-0 py-2 text-left" for="email">{{ t('email') }}:</label>
										@if (config('settings.sms.enable_phone_as_auth_field') == '1')
											<div class="col-6 py-2 text-right">
												<a href="" class="auth-field" data-auth-field="phone">{{ t('login_with_phone') }}</a>
											</div>
										@endif
									</div>
									<div class="input-group{{ $emailError }}">
										<span class="input-group-text"><i class="fa-solid fa-user"></i></span>
										<input id="email" name="email"
											   type="text"
											   placeholder="{{ t('email_or_username') }}"
											   class="form-control{{ $emailError }}"
											   value="{{ $emailValue }}"
										>
									</div>
								</div>
								
								{{-- phone --}}
								@if (config('settings.sms.enable_phone_as_auth_field') == '1')
									@php
										$phoneError = (isset($errors) && $errors->has('phone')) ? ' is-invalid' : '';
										$phoneValue = (session()->has('phone')) ? session('phone') : old('phone');
										$phoneCountryValue = config('country.code');
									@endphp
									<div class="mb-3 auth-field-item">
										<div class="row">
											<label class="form-label col-6 m-0 py-2 text-left" for="phone">{{ t('phone_number') }}:</label>
											<div class="col-6 py-2 text-right">
												<a href="" class="auth-field" data-auth-field="email">{{ t('login_with_email') }}</a>
											</div>
										</div>
										<input id="phone" name="phone"
											   type="tel"
											   class="form-control{{ $phoneError }}"
											   value="{{ phoneE164($phoneValue, old('phone_country', $phoneCountryValue)) }}"
										>
										<input name="phone_country" type="hidden" value="{{ old('phone_country', $phoneCountryValue) }}">
									</div>
								@endif
								
								{{-- auth_field --}}
								<input name="auth_field" type="hidden" value="{{ old('auth_field', getAuthField()) }}">
								
								{{-- password --}}
								@php
									$passwordError = (isset($errors) && $errors->has('password')) ? ' is-invalid' : '';
								@endphp
								<div class="mb-3">
									<label for="password" class="col-form-label">{{ t('password') }}:</label>
									<div class="input-group required toggle-password-wrapper{{ $passwordError }}">
										<span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
										<input id="password" name="password"
											   type="password"
											   class="form-control{{ $passwordError }}"
											   placeholder="{{ t('password') }}"
											   autocomplete="new-password"
										>
										<span class="input-group-text">
											<a class="toggle-password-link" href="#">
												<i class="fa-regular fa-eye-slash"></i>
											</a>
										</span>
									</div>
								</div>
								
								@include('front.layouts.inc.tools.captcha', ['noLabel' => true])
								
								{{-- Submit --}}
								<div class="mb-1">
									<button type="submit" id="loginBtn" class="btn btn-primary btn-block"> {{ t('log_in') }} </button>
								</div>
							</div>
							
							<div class="card-footer px-4">
								<label class="checkbox float-start mt-2 mb-2" for="rememberMe">
									<input type="checkbox" value="1" name="remember_me" id="rememberMe">
									<span class="custom-control-indicator"></span>
									<span class="custom-control-description"> {{ t('keep_me_logged_in') }}</span>
								</label>
								<div class="text-center float-end mt-2 mb-2">
									<a href="{{ url('password/reset') }}"> {{ t('lost_your_password') }} </a>
								</div>
								<div style=" clear:both"></div>
							</div>
						</div>
					</form>
					
					<div class="login-box-btm text-center">
						<p>
							{{ t('do_not_have_an_account') }}<br>
							<a href="{{ urlGen()->register() }}"><strong>{{ t('sign_up') }} !</strong></a>
						</p>
					</div>
				</div>
				
			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
@endsection
