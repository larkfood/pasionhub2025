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
							<h5><strong>{{ t('validation_errors_title') }}</strong></h5>
							<ul class="list list-check">
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
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
				
				<div class="col-md-8 page-content">
					<div class="inner-box">
						<h2 class="title-2">
							<strong><i class="fa-solid fa-user-plus"></i> {{ t('create_your_account_it_is_quick') }}</strong>
						</h2>
						
						@include('front.auth.login.inc.social')
						
						@php
							$mtAuth = !isSocialAuthEnabled() ? ' mt-5' : ' mt-4';
						@endphp
						<div class="row{{ $mtAuth }}">
							<div class="col-12">
								<form id="signupForm" class="form-horizontal" method="POST" action="{{ url()->current() }}">
									{!! csrf_field() !!}
									@honeypot
									<fieldset>
										
										{{-- name --}}
										@php
											$nameError = (isset($errors) && $errors->has('name')) ? ' is-invalid' : '';
										@endphp
										<div class="row mb-3 required">
											<label class="col-md-3 col-form-label">{{ t('Name') }} <sup>*</sup></label>
											<div class="col-md-9 col-lg-6">
												<input name="name"
												       placeholder="{{ t('Name') }}"
												       class="form-control input-md{{ $nameError }}"
												       type="text"
												       value="{{ old('name') }}"
												>
											</div>
										</div>
										
										{{-- country_code --}}
										@if (empty(config('country.code')))
											@php
												$countryCodeError = (isset($errors) && $errors->has('country_code')) ? ' is-invalid' : '';
												$countryCodeValue = (!empty(config('ipCountry.code'))) ? config('ipCountry.code') : 0;
											@endphp
											<div class="row mb-3 required">
												<label class="col-md-3 col-form-label{{ $countryCodeError }}" for="country_code">
													{{ t('your_country') }} <sup>*</sup>
												</label>
												<div class="col-md-9 col-lg-6">
													<select id="countryCode"
													        name="country_code"
													        class="form-control large-data-selecter{{ $countryCodeError }}"
													>
														<option value="0" @selected(empty(old('country_code')))>
															{{ t('Select') }}
														</option>
														@foreach ($countries as $code => $item)
															<option value="{{ $code }}" @selected(old('country_code', $countryCodeValue) == $code)>
																{{ $item->get('name') }}
															</option>
														@endforeach
													</select>
												</div>
											</div>
										@else
											<input id="countryCode" name="country_code" type="hidden" value="{{ config('country.code') }}">
										@endif
										
										{{-- auth_field (as notification channel) --}}
										@php
											$authFields = getAuthFields(true);
											$authFieldError = (isset($errors) && $errors->has('auth_field')) ? ' is-invalid' : '';
											$usersCanChooseNotifyChannel = isUsersCanChooseNotifyChannel();
											$authFieldValue = ($usersCanChooseNotifyChannel) ? (old('auth_field', getAuthField())) : getAuthField();
										@endphp
										@if ($usersCanChooseNotifyChannel)
											<div class="row mb-3 required">
												<label class="col-md-3 col-form-label" for="auth_field">
													{{ t('notifications_channel') }} <sup>*</sup>
												</label>
												<div class="col-md-9">
													@foreach ($authFields as $iAuthField => $notificationType)
														<div class="form-check form-check-inline pt-2">
															<input name="auth_field"
																   id="{{ $iAuthField }}AuthField"
																   value="{{ $iAuthField }}"
																   class="form-check-input auth-field-input{{ $authFieldError }}"
																   type="radio" @checked($authFieldValue == $iAuthField)
															>
															<label class="form-check-label mb-0" for="{{ $iAuthField }}AuthField">
																{{ $notificationType }}
															</label>
														</div>
													@endforeach
													<div class="form-text text-muted">
														{{ t('notifications_channel_hint') }}
													</div>
												</div>
											</div>
										@else
											<input id="{{ $authFieldValue }}AuthField" name="auth_field" type="hidden" value="{{ $authFieldValue }}">
										@endif
										
										@php
											$forceToDisplay = isBothAuthFieldsCanBeDisplayed() ? ' force-to-display' : '';
										@endphp
										
										{{-- email --}}
										@php
											$emailError = (isset($errors) && $errors->has('email')) ? ' is-invalid' : '';
											$emailRequiredClass = (getAuthField() == 'email') ? ' required' : '';
										@endphp
										<div class="row mb-3 auth-field-item{{ $emailRequiredClass . $forceToDisplay }}">
											<label class="col-md-3 col-form-label pt-0" for="email">{{ t('email') }}
												@if (getAuthField() == 'email')
													<sup>*</sup>
												@endif
											</label>
											<div class="col-md-9 col-lg-6">
												<div class="input-group{{ $emailError }}">
													<span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
													<input id="email" name="email"
														   type="email"
														   data-valid-type="email"
														   class="form-control{{ $emailError }}"
														   placeholder="{{ t('email_address') }}"
														   value="{{ old('email') }}"
													>
												</div>
											</div>
										</div>
										
										{{-- phone --}}
										@php
											$phoneError = (isset($errors) && $errors->has('phone')) ? ' is-invalid' : '';
											$phoneCountryValue = config('country.code');
											$phoneRequiredClass = (getAuthField() == 'phone') ? ' required' : '';
										@endphp
										<div class="row mb-3 auth-field-item{{ $phoneRequiredClass . $forceToDisplay }}">
											<label class="col-md-3 col-form-label pt-0" for="phone">{{ t('phone_number') }}
												@if (getAuthField() == 'phone')
													<sup>*</sup>
												@endif
											</label>
											<div class="col-md-9 col-lg-6">
												<input id="phone" name="phone"
													   class="form-control input-md{{ $phoneError }}"
													   type="tel"
													   value="{{ phoneE164(old('phone'), old('phone_country', $phoneCountryValue)) }}"
													   autocomplete="off"
												>
												<input name="phone_country" type="hidden" value="{{ old('phone_country', $phoneCountryValue) }}">
											</div>
										</div>
										
										{{-- username --}}
										@php
											$usernameIsEnabled = !config('larapen.core.disable.username');
										@endphp
										@if ($usernameIsEnabled)
											<?php $usernameError = (isset($errors) && $errors->has('username')) ? ' is-invalid' : ''; ?>
											<div class="row mb-3">
												<label class="col-md-3 col-form-label" for="username">{{ t('Username') }}</label>
												<div class="col-md-9 col-lg-6">
													<div class="input-group{{ $usernameError }}">
														<span class="input-group-text"><i class="fa-regular fa-user"></i></span>
														<input id="username"
															   name="username"
															   type="text"
															   class="form-control{{ $usernameError }}"
															   placeholder="{{ t('Username') }}"
															   value="{{ old('username') }}"
														>
													</div>
												</div>
											</div>
										@endif
										
										{{-- password --}}
										@php
											$passwordError = (isset($errors) && $errors->has('password')) ? ' is-invalid' : '';
										@endphp
										<div class="row mb-3 required">
											<label class="col-md-3 col-form-label" for="password">{{ t('password') }} <sup>*</sup></label>
											<div class="col-md-9 col-lg-6 toggle-password-wrapper">
												<div class="input-group{{ $passwordError }} mb-2">
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
												<input id="passwordConfirmation" name="password_confirmation"
													   type="password"
													   class="form-control{{ $passwordError }}"
													   placeholder="{{ t('Password Confirmation') }}"
													   autocomplete="off"
												>
												<div class="form-text text-muted">
													{{ t('at_least_num_characters', ['num' => config('settings.security.password_min_length', 6)]) }}
												</div>
											</div>
										</div>
										
										@include('front.layouts.inc.tools.captcha', ['colLeft' => 'col-md-3', 'colRight' => 'col-md-6'])
										
										{{-- accept_terms --}}
										@php
											$acceptTermsError = (isset($errors) && $errors->has('accept_terms')) ? ' is-invalid' : '';
										@endphp
										<div class="row mb-1 required">
											<label class="col-md-3 col-form-label"></label>
											<div class="col-md-9">
												<div class="form-check">
													<input name="accept_terms" id="acceptTerms"
														   class="form-check-input{{ $acceptTermsError }}"
														   value="1"
														   type="checkbox" @checked(old('accept_terms') == '1')
													>
													<label class="form-check-label" for="acceptTerms" style="font-weight: normal;">
														{!! t('accept_terms_label', ['attributes' => getUrlPageByType('terms')]) !!}
													</label>
												</div>
												<div style="clear:both"></div>
											</div>
										</div>
										
										{{-- accept_marketing_offers --}}
										@php
											$acceptMarketingOffersError = (isset($errors) && $errors->has('accept_marketing_offers')) ? ' is-invalid' : '';
										@endphp
										<div class="row mb-3 required">
											<label class="col-md-3 col-form-label"></label>
											<div class="col-md-9">
												<div class="form-check">
													<input name="accept_marketing_offers" id="acceptMarketingOffers"
														   class="form-check-input{{ $acceptMarketingOffersError }}"
														   value="1"
														   type="checkbox" @checked(old('accept_marketing_offers') == '1')
													>
													<label class="form-check-label" for="acceptMarketingOffers" style="font-weight: normal;">
														{!! t('accept_marketing_offers_label') !!}
													</label>
												</div>
												<div style="clear:both"></div>
											</div>
										</div>

										{{-- Button --}}
										<div class="row mb-3">
											<label class="col-md-3 col-form-label"></label>
											<div class="col-md-7">
												<button type="submit" id="signupBtn" class="btn btn-primary btn-lg"> {{ t('register') }} </button>
											</div>
										</div>

										<div class="mb-4"></div>

									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-4 reg-sidebar">
					<div class="reg-sidebar-inner text-center">
						<div class="promo-text-box">
							<i class="fa-regular fa-image fa-4x icon-color-1"></i>
							<h3><strong>{{ t('create_new_listing') }}</strong></h3>
							<p>
								{{ t('do_you_have_something_text', ['appName' => config('app.name')]) }}
							</p>
						</div>
						<div class="promo-text-box">
							<i class="fa-solid fa-square-pen fa-4x icon-color-2"></i>
							<h3><strong>{{ t('create_and_manage_items') }}</strong></h3>
							<p>{{ t('become_a_best_seller_or_buyer_text') }}</p>
						</div>
						<div class="promo-text-box"><i class="fa-solid fa-heart fa-4x icon-color-3"></i>
							<h3><strong>{{ t('create_your_favorite_listings_list') }}</strong></h3>
							<p>{{ t('create_your_favorite_listings_list_text') }}</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
@endsection
