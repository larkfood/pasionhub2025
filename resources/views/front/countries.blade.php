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
	$countries ??= collect();
	$countryFlagShape = config('settings.localization.country_flag_shape');
@endphp
@section('header')
	@include('front.layouts.inc.lite.header')
@endsection

@section('search')
	@parent
@endsection

@section('content')
	@include('front.common.spacer')
	<div class="main-container inner-page pb-0">
		
		@if (session()->has('flash_notification'))
			<div class="container">
				<div class="row">
					<div class="col-12">
						@include('flash::message')
					</div>
				</div>
			</div>
		@endif
		
		<div class="container">
			<div class="section-content">
				@php
					$mb = !isSocialSharesEnabled() ? ' mb-4' : '';
				@endphp
				<div class="row{{ $mb }}">
					
					<h1 class="text-center title-1" style="text-transform: none;">
						<strong>{{ t('countries') }}</strong>
					</h1>
					<hr class="center-block small mt-0">
					
					<div class="col-md-12 page-content">
						<div class="inner-box relative">
							
							<h3 class="title-2"><i class="bi bi-geo-alt"></i> {{ t('select_a_country') }}</h3>
							
							@if ($countries->isNotEmpty())
								<div id="countryList" class="row row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-2 m-0">
									@foreach ($countries as $code => $country)
										@php
											$classBorder = (count($countries) == ($loop->index + 1)) ? ' cat-list-border' : '';
											
											$countryUrl = dmUrl($country, '/', true, !config('plugins.domainmapping.installed'));
											$countryName = $country->get('name');
											$countryNameLimited = str($countryName)->limit(26)->toString();
										@endphp
										<div class="col mb-1 cat-list{{ $classBorder }}">
											@if ($countryFlagShape == 'rectangle')
												<img src="{{ url('images/blank.gif') . getPictureVersion() }}"
													 class="flag flag-{{ $country->get('icode') }}"
													 style="margin-bottom: 4px; margin-right: 5px;"
												     alt="{{ $countryNameLimited }}"
												>
											@else
												<img src="{{ $country->get('flag16_url') }}"
												     class=""
												     style="margin-bottom: 4px; margin-right: 5px;"
												     alt="{{ $countryNameLimited }}"
												>
											@endif
											<a href="{{ $countryUrl }}" data-bs-toggle="tooltip" title="{!! $countryName !!}">
												{{ $countryNameLimited }}
											</a>
										</div>
									@endforeach
								</div>
							@else
								<div class="row m-0">
									<div class="col-12 text-center mb-3 text-danger">
										<strong>{{ t('countries_not_found') }}</strong>
									</div>
								</div>
							@endif
							
						</div>
					</div>
					
				</div>
				
				@include('front.layouts.inc.social.horizontal')

			</div>
		</div>
	</div>
@endsection

@section('footer')
	@include('front.layouts.inc.lite.footer')
@endsection

@section('after_scripts')
	<script>
		onDocumentReady((event) => {
			{{-- Reorder the country list --}}
			const countryListReorder = new BsRowColumnsReorder('#countryList', {defaultColumns: 4});
		});
	</script>
@endsection
