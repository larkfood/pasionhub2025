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

@section('wizard')
    @include('front.post.createOrEdit.multiSteps.inc.wizard')
@endsection

@php
	$post ??= [];
	$packages ??= collect();
	$paymentMethods ??= collect();
	
	$selectedPackage ??= null;
	$currentPackagePrice = $selectedPackage->price ?? 0;
	
	$authUser = auth()->check() ? auth()->user() : null;
	
	// Get steps URLs & labels
	$previousStepUrl ??= null;
	$previousStepLabel ??= null;
	$formActionUrl ??= request()->fullUrl();
	$nextStepUrl ??= '/';
	$nextStepLabel ??= t('submit');
@endphp
@section('content')
	@include('front.common.spacer')
    <div class="main-container">
        <div class="container">
            <div class="row">
    
                @include('front.post.inc.notification')
                
                <div class="col-md-12 page-content">
                    <div class="inner-box">
						
                        <h2 class="title-2">
							<strong>
								@if (!empty($selectedPackage))
									<i class="fa-solid fa-wallet"></i> {{ t('Payment') }}
								@else
									<i class="fa-solid fa-tags"></i> {{ t('Pricing') }}
								@endif
							</strong>
	                        @php
		                        try {
									if (!empty($authUser)) {
										if (doesUserHavePermission($authUser, \App\Models\Permission::getStaffPermissions())) {
											$postLink = '-&nbsp;<a href="' . urlGen()->post($post) . '"
													  class=""
													  data-bs-placement="top"
													  data-bs-toggle="tooltip"
													  title="' . data_get($post, 'title') . '"
											>' . str(data_get($post, 'title'))->limit(45) . '</a>';
											
											echo $postLink;
										}
									}
								} catch (\Throwable $e) {}
	                        @endphp
						</h2>
						
                        <div class="row">
                            <div class="col-sm-12">
                                <form class="form" id="payableForm" method="POST" action="{{ $formActionUrl }}">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="payable_id" value="{{ data_get($post, 'id') }}">
                                    <fieldset>
										
										@if (!empty($selectedPackage))
											@include('front.payment.packages.selected')
										@else
											@include('front.payment.packages')
                                        @endif
										
                                        <div class="row">
                                            <div class="col-md-12 text-center mt-4">
												<a id="skipBtn" href="{{ $previousStepUrl }}" class="btn btn-default btn-lg">
													{{ $previousStepLabel }}
												</a>
                                                <button id="payableFormSubmitButton" class="btn btn-success btn-lg payableFormSubmitButton">
	                                                {{ $nextStepLabel }}
                                                </button>
                                            </div>
                                        </div>
                                    
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after_styles')
@endsection

@section('after_scripts')
	<script>
		const packageType = 'promotion';
		const formType = 'multiStep';
		const isCreationFormPage = {{ request()->segment(2) == 'create' ? 'true' : 'false' }};
	</script>
	@include('front.common.js.payment-scripts')
	@include('front.common.js.payment-js')
@endsection
