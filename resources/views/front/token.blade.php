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
						<div class="alert alert-danger">
							<ul class="list list-check">
								@foreach ($errors->all() as $error)
									<li>{!! $error !!}</li>
								@endforeach
							</ul>
						</div>
					</div>
				@endif

				@if (session('code'))
					<div class="col-12">
						<div class="alert alert-danger">
							<p>{{ session('code') }}</p>
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
				
				<div class="col-12">
					<div class="alert alert-info">
						{{ getTokenMessage() }}:
					</div>
				</div>

				<div class="col-lg-5 col-md-8 col-sm-10 col-12 login-box mt-2">
					<div class="card card-default">
						
						<div class="panel-intro">
							<div class="d-flex justify-content-center">
								<h2 class="logo-title"><strong>{{ t('Code') }}</strong></h2>
							</div>
						</div>
						
						<div class="card-body">
							<form id="tokenForm" role="form" method="POST" action="{{ url(getRequestPath('.*/verify/.*')) }}">
								{!! csrf_field() !!}
								@honeypot
								
								{{-- code --}}
								@php
									$codeError = (isset($errors) && $errors->has('code')) ? ' is-invalid' : '';
								@endphp
								<div class="mb-3">
									<label for="code" class="col-form-label">{{ getTokenLabel() }}:</label>
									<div class="input-group required">
										<span class="input-group-text">
											<i class="bi bi-envelope-exclamation"></i>
										</span>
										<input id="code" name="code"
											   type="text"
											   placeholder="{{ t('Enter the validation code') }}"
											   class="form-control{{ $codeError }}"
											   value="{{ old('code') }}"
											   autocomplete="one-time-code"
										>
									</div>
								</div>
								
								<div class="mb-3">
									<button type="submit" id="tokenBtn" class="btn btn-primary btn-lg btn-block">{{ t('submit') }}</button>
								</div>
							</form>
						</div>
						
						<div class="card-footer text-center">
							&nbsp;
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
@endsection
