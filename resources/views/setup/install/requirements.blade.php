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
@section('title', trans('messages.requirements_checking_title'))

@php
	$checkComponents ??= false;
	$components ??= [];
	
	$checkPermissions ??= false;
	$permissions ??= [];
	
	// Get steps URLs & labels
	$previousStepUrl ??= null;
	$previousStepLabel ??= null;
	$formActionUrl ??= request()->fullUrl();
	$nextStepUrl ??= url('/');
	$nextStepLabel ??= trans('messages.next');
@endphp
@section('content')
	
	@if (!$checkComponents)
		<h3 class="title-3">
			<i class="fa-solid fa-list"></i> {{ trans('messages.requirements') }}
		</h3>
		<div class="row">
			<div class="col-md-12">
				<ul class="installation">
					@foreach ($components as $key => $item)
						@continue($item['isOk'])
						<li>
							@if ($item['isOk'])
								<i class="bi bi-check text-success"></i>
							@else
								<i class="bi bi-x text-danger"></i>
							@endif
							<h5 class="title-5 fw-bold">
								{{ $item['name'] }}
							</h5>
							<p>
								{!! ($item['isOk']) ? $item['success'] : $item['warning'] !!}
							</p>
						</li>
					@endforeach
				</ul>
			</div>
		</div>
	@endif
	
	<h3 class="title-3">
		<i class="fa-regular fa-folder"></i> {{ trans('messages.permissions') }}
	</h3>
	<div class="row">
		<div class="col-md-12">
			<ul class="installation">
				@foreach ($permissions as $key => $item)
					<li>
						@if ($item['isOk'])
							<i class="bi bi-check text-success"></i>
						@else
							<i class="bi bi-x text-danger"></i>
						@endif
						<h5 class="title-5 fw-bold">
							{{ $item['name'] }}
						</h5>
						<p>
							{!! ($item['isOk']) ? $item['success'] : $item['warning'] !!}
						</p>
					</li>
				@endforeach
			</ul>
		</div>
	</div>
	
	<div class="text-end">
		@if ($checkComponents && $checkPermissions)
			<a href="{{ $nextStepUrl }}" class="btn btn-primary">
				{!! $nextStepLabel !!} <i class="fa-solid fa-chevron-right position-right"></i>
			</a>
		@else
			<a href="{{ $formActionUrl }}" class="btn btn-primary">
				<i class="fa-solid fa-rotate-right position-right"></i> {!! trans('messages.try_again') !!}
			</a>
		@endif
	</div>

@endsection

@section('after_scripts')
@endsection
