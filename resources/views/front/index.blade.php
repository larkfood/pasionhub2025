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
@endsection

@section('content')
	<div class="main-container" id="homepage">
		
		@if (session()->has('flash_notification'))
			@include('front.common.spacer')
			@php
				$paddingTopExists = true;
			@endphp
			<div class="container">
				<div class="row">
					<div class="col-12">
						@include('flash::message')
					</div>
				</div>
			</div>
		@endif
		
		@if (!empty($sections))
			@foreach($sections as $section)
				@php
					$section ??= [];
					$sectionView = data_get($section, 'view');
					$sectionData = (array)data_get($section, 'data');
				@endphp
				@if (!empty($sectionView) && view()->exists($sectionView))
					@include($sectionView, [
						'sectionData'  => $sectionData,
						'firstSection' => $loop->first
					])
				@endif
			@endforeach
		@endif
		
	</div>
@endsection

@section('after_scripts')
@endsection
