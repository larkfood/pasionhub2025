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
@extends('setup.install.layouts.master')
@section('title', trans('messages.cron_jobs_title'))

@php
	// Get steps URLs & labels
	$previousStepUrl ??= null;
	$previousStepLabel ??= null;
    $formActionUrl ??= request()->fullUrl();
    $nextStepUrl ??= url('/');
    $nextStepLabel ??= trans('messages.next');
@endphp
@section('content')
	
	@include('setup.install.helpers._cron_jobs')
	
	<div class="text-end">
		<a href="{{ $nextStepUrl }}" class="btn btn-primary bg-teal">
			{!! $nextStepLabel !!} <i class="fa-solid fa-chevron-right position-right"></i>
		</a>
	</div>
	
@endsection

@section('after_scripts')
@endsection
