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

@php
	$authUserIsAdmin ??= true;
@endphp
@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container">
			<div class="row">
				
				<div class="col-md-3 page-sidebar">
					@include('front.account.inc.sidebar')
				</div>
				
				<div class="col-md-9 page-content">
					@include('flash::message')
					
					<div class="inner-box">
						<h2 class="title-2"><i class="fa-solid fa-circle-xmark"></i> {{ t('Close account') }} </h2>
						<p>{{ t('you_are_sure_you_want_to_close_your_account') }}</p>

						@if ($authUserIsAdmin)
							<div class="alert alert-danger" role="alert">
								{{ t('Admin users can not be deleted by this way') }}
							</div>
						@else
							<form role="form" method="POST" action="{{ url('account/close') }}">
								{!! csrf_field() !!}
								
								<div class="form-group row">
									<div class="col-md-12">
										<div class="form-check form-check-inline pt-2">
											<label class="form-check-label">
												<input class="form-check-input"
													   type="radio"
													   name="close_account_confirmation"
													   id="closeAccountConfirmation1"
													   value="1"
												> {{ t('Yes') }}
											</label>
										</div>
										<div class="form-check form-check-inline pt-2">
											<label class="form-check-label">
												<input class="form-check-input"
													   type="radio"
													   name="close_account_confirmation"
													   id="closeAccountConfirmation0"
													   value="0" checked
												> {{ t('No') }}
											</label>
										</div>
									</div>
								</div>
								
								<div class="form-group row">
									<div class="col-md-12">
										<button type="submit" class="btn btn-primary">{{ t('submit') }}</button>
									</div>
								</div>
							</form>
						@endif

					</div>
				</div>

			</div>
		</div>
	</div>
@endsection
